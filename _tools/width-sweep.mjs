// Прогон по ширинам: ищем, где раскладка ломается между брейкпоинтами.
//
//   node _tools/width-sweep.mjs [адрес] [ширины через запятую]
//
// Зачем отдельно от mobile-check: тот смотрит один телефон (375), а дефекты
// заказчик поймал на ~930 — между нашими 899 и 1023. Промежуточные ширины
// никто не проверял, потому что щуп ходил по краям диапазона.
//
// Что ищет:
//   1. Переполнение по горизонтали (элемент вылез за окно).
//   2. Ребёнок ВЫЛЕЗ ЗА РОДИТЕЛЯ — то, чего не видит проверка по окну:
//      кнопка шире своей панели остаётся внутри экрана, но торчит из блока.
//   3. Колонки одной сетки разной ширины — признак поехавшего grid.
//   4. Текст в колонке уже 12 знаков — верстка сжалась до нечитаемого.
import { spawn } from 'node:child_process';
import { mkdtempSync, rmSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join } from 'node:path';

const BASE = (process.argv[2] || 'https://artdom.oxboxdigital.ru').replace(/\/$/, '');
const ШИРИНЫ = (process.argv[3] || '768,834,900,960,1024,1100,1180,1280').split(',').map(Number);
const CHROME = 'C:/Program Files/Google/Chrome/Application/chrome.exe';

const PAGES = [
  ['Главная', '/'],
  ['Объект', '/objects/rezidenciya-v-sadovyh-kvartalah/'],
  ['О компании', '/about/'],
  ['Услуги', '/services/'],
  ['Контакты', '/contacts/'],
];

const profile = mkdtempSync(join(tmpdir(), 'sweep-'));
const chrome = spawn(CHROME, ['--headless=new', '--disable-gpu', '--hide-scrollbars',
  '--remote-debugging-port=0', '--user-data-dir=' + profile, 'about:blank'], { stdio: ['ignore', 'pipe', 'pipe'] });
const wsUrl = await new Promise((res, rej) => {
  let buf = '';
  const t = setTimeout(() => rej(new Error('Chrome не отдал адрес отладки')), 15000);
  chrome.stderr.on('data', (d) => { buf += d; const m = buf.match(/ws:\/\/[^\s]+/); if (m) { clearTimeout(t); res(m[0]); } });
});
const ws = new WebSocket(wsUrl);
await new Promise((r) => ws.addEventListener('open', r, { once: true }));
let id = 0; const pending = new Map();
const send = (m, p, s) => new Promise((res, rej) => { const n = ++id; pending.set(n, { res, rej }); ws.send(JSON.stringify({ id: n, method: m, params: p || {}, sessionId: s })); });
ws.addEventListener('message', (ev) => { const m = JSON.parse(ev.data); if (m.id && pending.has(m.id)) { const p = pending.get(m.id); pending.delete(m.id); m.error ? p.rej(new Error(m.error.message)) : p.res(m.result); } });
const { targetId } = await send('Target.createTarget', { url: 'about:blank' });
const { sessionId } = await send('Target.attachToTarget', { targetId, flatten: true });
const S = (m, p) => send(m, p, sessionId);
await S('Page.enable'); await S('Runtime.enable');

const evalJs = async (e) => {
  const r = await S('Runtime.evaluate', { expression: e, returnByValue: true, awaitPromise: true });
  if (r.exceptionDetails) throw new Error(r.exceptionDetails.text);
  return r.result.value;
};

const PROBE = String.raw`(function(){
  var W = innerWidth, out = { over: [], escape: [], grid: [], narrow: [], known: [], dbl: [], слепых: 0, осмотрено: 0 };
  var имя = function (el) {
    var s = el.tagName.toLowerCase();
    var c = (el.getAttribute('class') || '').trim().split(/\s+/).filter(Boolean).slice(0, 2).join('.');
    if (c) s += '.' + c;
    var t = (el.textContent || '').replace(/\s+/g, ' ').trim().slice(0, 22);
    return s + (t ? ' [' + t + ']' : '');
  };
  var видим = function (el, r) {
    if (r.width < 1 || r.height < 1) return false;
    var cs = getComputedStyle(el);
    return cs.display !== 'none' && cs.visibility !== 'hidden' && cs.opacity !== '0';
  };
  var подРезкой = function (el) {
    for (var p = el; p; p = p.parentElement) {
      var cs = getComputedStyle(p);
      if (cs.overflowX === 'hidden' || cs.overflowX === 'clip') return true;
      if ((cs.overflowX === 'auto' || cs.overflowX === 'scroll') && p.scrollWidth > p.clientWidth + 1) return true;
    }
    return false;
  };
  var ловушка = function (el) { return !!el.closest('[class*="trap"]'); };

  var все = document.querySelectorAll('body *');
  for (var i = 0; i < все.length; i++) {
    var el = все[i], r = el.getBoundingClientRect();
    /* Сколько блоков появления ОСТАЛИСЬ погашенными вопреки страховке. Это
       счётчик слепоты самого щупа, а не дефект вёрстки: пока он больше нуля,
       любая зелёная строка ниже врёт — см. заметку про переходы у впрыска. */
    if (el.hasAttribute('data-rise') && getComputedStyle(el).opacity === '0') out.слепых++;
    if (!видим(el, r) || ловушка(el)) continue;
    out.осмотрено++;
    var cs = getComputedStyle(el);

    if (!подРезкой(el) && (r.left < -1 || r.right > W + 1)) {
      out.over.push({ n: имя(el), x: Math.round(r.left), w: Math.round(r.width) });
    }

    /* Ребёнок вылез за родителя. Считаем только там, где родитель НЕ режет
       вылет и сам не позиционирован абсолютно — иначе посыплются законные
       приёмы вроде отрицательных полей у сеток. */
    var p = el.parentElement;
    if (p && p !== document.body) {
      var pcs = getComputedStyle(p), pr = p.getBoundingClientRect();
      var режет = pcs.overflow !== 'visible' || pcs.overflowX !== 'visible';
      var абс = cs.position === 'absolute' || cs.position === 'fixed';
      var отриц = parseFloat(cs.marginLeft) < 0 || parseFloat(cs.marginRight) < 0;
      if (!режет && !абс && !отриц && pr.width > 1) {
        var вылет = Math.round(Math.max(pr.left - r.left, r.right - pr.right));
        if (вылет > 2) out.escape.push({ n: имя(el), род: имя(p).split(' [')[0], вылет: вылет });
      }
    }

    /* Сетка с колонками разной ширины. Смотрим только прямых детей грида в
       ПЕРВОЙ строке — иначе последний ряд с неполным числом ячеек даст ложь. */
    if (cs.display === 'grid' && el.children.length > 1) {
      var первые = [], верх = null;
      for (var k = 0; k < el.children.length; k++) {
        var ch = el.children[k], cr = ch.getBoundingClientRect();
        if (cr.height < 1) continue;
        if (верх === null) верх = Math.round(cr.top);
        if (Math.round(cr.top) !== верх) break;
        первые.push(Math.round(cr.width));
      }
      if (первые.length > 1) {
        var мин = Math.min.apply(null, первые), макс = Math.max.apply(null, первые);
        /* Колонки разной ширины сами по себе НЕ дефект: 342/188/188 у снимков
           услуги и 637/202 у карточки объекта — это композиция автора.
           Настоящий дефект другой: детей в строке БОЛЬШЕ, чем объявлено
           колонок, — значит лишние попали в НЕЯВНУЮ колонку и взяли ширину
           по содержимому. Ровно это было у подборки блога: объявлено две,
           карточек три, третья вышла 429 против 204.
           Осознанные отступления печатаем отдельно, а не глушим. */
        var объявлено = (cs.gridTemplateColumns || '').trim().split(/\s+/).filter(function (x) { return x && x !== 'none'; }).length;
        var запись = { n: имя(el).split(' [')[0], колонки: первые, объявлено: объявлено };
        /* Лишние дети в неявной дорожке — дефект только если они РАЗНОЙ
           ширины: значит дорожка взяла размер по содержимому (204/204/429 у
           подборки блога). Когда все равны, автор просто разложил ряд
           неявным потоком и всё сошлось — это композиция, а не поломка.
           Отбор по признаку, а не по имени элемента: перечислять исключения
           поимённо — тот самый частный признак, который завтра промахнётся. */
        if (объявлено && первые.length > объявлено && макс - мин > 4) out.grid.push(запись);
        else if (макс - мин > 4) out.known.push(запись);
      }
    }

    /* Двойной разделитель: у элемента И своя граница, И псевдоэлемент с
       линией. Так бывает, когда правила старой раскладки продолжают раздавать
       border, а новая рисует волосок псевдоэлементом: у карточки блога вышло
       две линии подряд, и увидел это заказчик, а не щуп. */
    var бордер = parseFloat(cs.borderLeftWidth) || 0;
    if (бордер > 0 && cs.borderLeftStyle !== "none") {
      var пс = getComputedStyle(el, "::before");
      var естьЛиния = пс.content !== "none" && (parseFloat(пс.width) || 0) > 0 && (parseFloat(пс.width) || 0) < 4;
      if (естьЛиния) out.dbl.push({ n: имя(el) });
    }

    /* Узкая текстовая колонка: меньше 12 знаков в строке читать невозможно. */
    var свой = '';
    for (var j = 0; j < el.childNodes.length; j++) if (el.childNodes[j].nodeType === 3) свой += el.childNodes[j].nodeValue;
    свой = свой.replace(/\s+/g, ' ').trim();
    if (свой.length > 40) {
      var кегль = parseFloat(cs.fontSize) || 16;
      var знаков = r.width / (кегль * 0.5);        /* грубо: полукегельная ширина знака */
      if (знаков < 12) out.narrow.push({ n: имя(el), ш: Math.round(r.width), знаков: Math.round(знаков) });
    }
  }
  return out;
})()`;

const pad = (s, n) => String(s).padEnd(n);
const осознанные = new Set();
let всего = 0;
let слепота = 0;

for (const W of ШИРИНЫ) {
  await S('Emulation.setDeviceMetricsOverride', { width: W, height: 900, deviceScaleFactor: 1, mobile: false });
  console.log('\n████ ШИРИНА ' + W + ' ████');
  for (const [имя, path] of PAGES) {
    await S('Page.navigate', { url: BASE + path });
    await new Promise((r) => setTimeout(r, 1400));
    /* transition/animation: none в этом впрыске — не украшение, а он и есть
       страховка. Переход в каскаде CSS стоит ВЫШЕ важных объявлений автора,
       поэтому `opacity: 1 !important` его не перебивает; а вкладка у нас
       фоновая, переходы в ней не проигрываются — и значение навсегда залипает
       на стартовом 0. С 15.09 по 15.09.2026 из-за этого все три щупа не видели
       ни одного блока `data-rise`, то есть почти всей страницы, и зеленели на
       пустом множестве. Гасим переходы — и важное объявление применяется. */
    await evalJs(`document.documentElement.classList.add('rise-failsafe');
      document.querySelectorAll('[data-rise]').forEach(function(e){e.classList.add('is-in')});
      var s=document.createElement('style');
      s.textContent='[data-rise]{opacity:1!important;transform:none!important;transition:none!important;animation:none!important}';
      document.head.appendChild(s); 1`);
    const r = await evalJs(PROBE);
    if (r.слепых) {
      console.log('  ‼ ' + имя + ': ' + r.слепых + ' блоков появления остались на opacity 0 —');
      console.log('     щуп их НЕ смотрел, любая зелёная строка ниже ничего не значит.');
      слепота += r.слепых;
    }
    const n = r.over.length + r.escape.length + r.grid.length + r.narrow.length + r.dbl.length;
    r.known.forEach((o) => осознанные.add(o.n + '  ' + o.колонки.join(' / ')));
    всего += n;
    if (!n) continue;
    console.log('  — ' + имя);
    r.over.slice(0, 4).forEach((o) => console.log('      за окно:      x=' + pad(o.x, 6) + 'ш=' + pad(o.w, 6) + o.n));
    r.escape.slice(0, 5).forEach((o) => console.log('      из родителя:  +' + pad(o.вылет, 5) + pad(o.n, 40) + '  внутри ' + o.род));
    r.grid.slice(0, 4).forEach((o) => console.log('      в неявной:    ' + pad(o.колонки.join(' / '), 26) + pad('объявлено ' + o.объявлено, 14) + o.n));
    r.dbl.slice(0, 4).forEach((o) => console.log("      ДВЕ ЛИНИИ:    " + o.n));
    r.narrow.slice(0, 4).forEach((o) => console.log('      узкий текст:  ' + pad(o.знаков + ' зн.', 9) + pad('ш=' + o.ш, 8) + o.n));
  }
}

console.log('\nВСЕГО НАХОДОК: ' + всего);
if (слепота) {
  console.log('ПРОГОН НЕДЕЙСТВИТЕЛЕН: ' + слепота + ' погашенных блоков прошли мимо проверок.');
  chrome.kill();
  process.exit(2);
}
chrome.kill();
try { rmSync(profile, { recursive: true, force: true }); } catch (e) { /* профиль занят */ }
process.exit(0);
