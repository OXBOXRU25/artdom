// Щуп мобильной вёрстки: обходит страницы на 375x812 с эмуляцией тача и ищет
// то, что на десктопе не видно.
//
//   node _tools/mobile-check.mjs [адрес стенда]
//
// Что смотрит:
//   1. Переполнение по горизонтали — элемент выходит за 375.
//   2. Зоны нажатия мельче 44px (правило проекта).
//   3. Коробки с заданной числом высотой, в которые содержимое не влезло:
//      реальные поля меньше объявленных — текст прижат к краям.
//   4. Обрезку текста по -webkit-line-clamp.
//   5. Правила :hover в стилях, не закрытые @media (hover: hover) —
//      на тач-экране такой ховер залипает после тапа.
import { spawn } from 'node:child_process';
import { mkdtempSync, rmSync, readFileSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join } from 'node:path';

const BASE = (process.argv[2] || 'http://127.0.0.1:8080').replace(/\/$/, '');
const CHROME = 'C:/Program Files/Google/Chrome/Application/chrome.exe';

const PAGES = [
  ['Главная', '/'],
  ['Объекты', '/objects/'],
  ['Услуги', '/services/'],
  ['О компании', '/about/'],
  ['Контакты', '/contacts/'],
  ['Блог', '/blog/'],
  ['Отзывы', '/reviews/'],
];

const profile = mkdtempSync(join(tmpdir(), 'mobcheck-'));
const chrome = spawn(CHROME, [
  '--headless=new', '--disable-gpu', '--hide-scrollbars',
  '--remote-debugging-port=0', '--user-data-dir=' + profile,
  'about:blank',
], { stdio: ['ignore', 'pipe', 'pipe'] });

const wsUrl = await new Promise((res, rej) => {
  let buf = '';
  const t = setTimeout(() => rej(new Error('Chrome не отдал адрес отладки')), 15000);
  chrome.stderr.on('data', (d) => {
    buf += d.toString();
    const m = buf.match(/ws:\/\/[^\s]+/);
    if (m) { clearTimeout(t); res(m[0]); }
  });
});

const browser = new WebSocket(wsUrl);
await new Promise((r) => browser.addEventListener('open', r, { once: true }));
let id = 0;
const pending = new Map();
const send = (method, params, sessionId) => new Promise((res, rej) => {
  const n = ++id;
  pending.set(n, { res, rej });
  browser.send(JSON.stringify({ id: n, method, params: params || {}, sessionId }));
});
browser.addEventListener('message', (ev) => {
  const m = JSON.parse(ev.data);
  if (m.id && pending.has(m.id)) {
    const p = pending.get(m.id); pending.delete(m.id);
    m.error ? p.rej(new Error(m.error.message)) : p.res(m.result);
  }
});

const { targetId } = await send('Target.createTarget', { url: 'about:blank' });
const { sessionId } = await send('Target.attachToTarget', { targetId, flatten: true });
const S = (m, p) => send(m, p, sessionId);
await S('Page.enable');
await S('Runtime.enable');
await S('Emulation.setDeviceMetricsOverride', { width: 375, height: 812, deviceScaleFactor: 2, mobile: true });
await S('Emulation.setTouchEmulationEnabled', { enabled: true, maxTouchPoints: 5 });
await S('Emulation.setEmitTouchEventsForMouse', { enabled: true, configuration: 'mobile' });

const evalJs = async (expr) => {
  const r = await S('Runtime.evaluate', { expression: expr, returnByValue: true, awaitPromise: true });
  if (r.exceptionDetails) throw new Error(r.exceptionDetails.text + ' :: ' + JSON.stringify(r.exceptionDetails.exception || {}));
  return r.result.value;
};

// Щуп страницы. Возвращает четыре списка находок.
const PROBE = String.raw`(function(){
  var W = 375, out = { over: [], tap: [], squeeze: [], clamp: [] };
  var name = function (el) {
    var s = el.tagName.toLowerCase();
    if (el.id) s += '#' + el.id;
    var c = (el.getAttribute('class') || '').trim().split(/\s+/).filter(Boolean).slice(0, 3).join('.');
    if (c) s += '.' + c;
    var t = (el.textContent || '').replace(/\s+/g, ' ').trim().slice(0, 26);
    return s + (t ? ' [' + t + ']' : '');
  };
  var visible = function (el, r) {
    if (r.width < 1 || r.height < 1) return false;
    var cs = getComputedStyle(el);
    return cs.display !== 'none' && cs.visibility !== 'hidden' && cs.opacity !== '0';
  };
  /* Из проверки переполнения исключаем то, что за край уезжает нарочно:
     прокручиваемые ленты (там огрызок следующей карточки — это приём) и всё
     под предком, который обрезает вылет (у закреплённой сцены снимок шире
     окна и ездит параллаксом внутри overflow: hidden). Полоса прокрутки от
     этого не появляется, страницу это не ломает. */
  var inScroller = function (el) {
    for (var p = el; p; p = p.parentElement) {
      var cs = getComputedStyle(p);
      if ((cs.overflowX === 'auto' || cs.overflowX === 'scroll') && p.scrollWidth > p.clientWidth + 1) return true;
      if (cs.overflowX === 'hidden' || cs.overflowX === 'clip') return true;
    }
    return false;
  };
  // Ловушка для спам-роботов стоит за экраном намеренно.
  var honeypot = function (el) { return !!el.closest('.modal__trap, [class*="trap"]'); };
  var candidates = [];
  var all = document.querySelectorAll('body *');
  for (var i = 0; i < all.length; i++) {
    var el = all[i], r = el.getBoundingClientRect();
    if (!visible(el, r)) continue;
    var cs = getComputedStyle(el);

    if (!inScroller(el) && !honeypot(el) && (r.left < -1 || r.right > W + 1)) {
      out.over.push({ n: name(el), x: Math.round(r.left), w: Math.round(r.width) });
    }

    var tag = el.tagName;
    var interactive = tag === 'A' || tag === 'BUTTON' || tag === 'SUMMARY' ||
      (tag === 'INPUT' && el.type !== 'hidden') || tag === 'SELECT' || tag === 'TEXTAREA';
    if (interactive && !honeypot(el) && (r.height < 44 || r.width < 44)) candidates.push(el);

    if (cs.overflowY !== 'auto' && cs.overflowY !== 'scroll' && el.children.length) {
      var pt = parseFloat(cs.paddingTop) || 0, pb = parseFloat(cs.paddingBottom) || 0;
      if (pt + pb > 8) {
        var first = null, last = null;
        for (var k = 0; k < el.children.length; k++) {
          var ch = el.children[k], ccs = getComputedStyle(ch);
          if (ccs.position === 'absolute' || ccs.position === 'fixed') continue;
          var cr = ch.getBoundingClientRect();
          if (cr.height < 1) continue;
          if (first === null || cr.top < first) first = cr.top;
          if (last === null || cr.bottom > last) last = cr.bottom;
        }
        if (first !== null) {
          var realTop = Math.round(first - r.top), realBot = Math.round(r.bottom - last);
          if (realTop < pt - 2 || realBot < pb - 2) {
            out.squeeze.push({ n: name(el), h: Math.round(r.height),
              padObj: Math.round(pt) + '/' + Math.round(pb), padReal: realTop + '/' + realBot });
          }
        }
      }
    }

    if (cs.webkitLineClamp && cs.webkitLineClamp !== 'none') {
      out.clamp.push({ n: name(el), lines: cs.webkitLineClamp, h: Math.round(r.height) });
    }
  }

  /* Второй проход по подозреваемым. Коробка элемента - НЕ зона нажатия: её
     расширяют псевдоэлементом или отрицательным полем, и по
     getBoundingClientRect этого не видно. Меряем тем способом, каким её
     находит палец: тычем в точку и смотрим, кто под ней.
     Элемент для этого надо привести в окно - elementFromPoint работает только
     по видимой части, а без прокрутки вся страница ниже первого экрана дала бы
     "проверок нет, значит всё хорошо". */
  for (var j = 0; j < candidates.length; j++) {
    var c = candidates[j];
    c.scrollIntoView({ block: 'center', behavior: 'instant' });
    var cr = c.getBoundingClientRect();
    var cx = cr.left + cr.width / 2, cy = cr.top + cr.height / 2;
    var попал = function (el2, x, y) {
      if (x < 0 || y < 0 || x > W || y > innerHeight) return null;   // судить не по чему
      var t = document.elementFromPoint(x, y);
      if (!t) return false;
      return t === el2 || el2.contains(t) || t.parentElement === el2;
    };
    var ось = function (dx, dy, размер, r) {
      var пол = размер / 2 - 1;
      if ((dx ? cr.width : cr.height) >= размер) return true;
      return попал(c, cx + dx * пол, cy + dy * пол) !== false &&
             попал(c, cx - dx * пол, cy - dy * пол) !== false;
    };
    var hOk = ось(0, 1, 44), wOk = ось(1, 0, 44);
    if (hOk && wOk) continue;
    /* Провал стандарта или только нашего норматива — судим по ТОЙ ЖЕ мерке,
       а не по коробке: у кнопки-многоточия коробка 12px, а палец попадает в 34,
       потому что расширение обрезано краем ленты. */
    var стандарт = ось(0, 1, 24) && ось(1, 0, 24);
    /* Осознанные отступления печатаем отдельным списком, а НЕ глушим:
       отключённое правило через месяц неотличимо от отсутствия проблемы. */
    var why = null;
    if (c.closest('.ftr__soc, .ftr__legal') && cr.height >= 24) {
      why = 'подвал: стопка ссылок, 30px по решению от 03.09 - с отрицательным полем соседние цели налезали друг на друга; минимум WCAG 2.5.8 (24) пройден';
    } else if (c.classList.contains('clip__more') && c.getAttribute('aria-hidden') === 'true') {
      why = 'хвостик-многоточие в карточке: ссылка-дубль заголовка, спрятана от скринридера и от таба';
    }
    out.tap.push({
      n: name(c), w: Math.round(cr.width), h: Math.round(cr.height), why: why,
      /* Меньше 24 по узкой стороне - провал WCAG 2.5.8 (AA). От 24 до 44 -
         стандарту соответствует, нашему нормативу нет. */
      hard: !стандарт,
    });
  }
  window.scrollTo(0, 0);
  return out;
})()`;

const pad = (s, n) => String(s).padEnd(n);
const totals = { over: 0, tap: 0, squeeze: 0, clamp: 0 };
const exceptions = new Map();

for (const [label, path] of PAGES) {
  const url = BASE + path;
  await S('Page.navigate', { url });
  await new Promise((r) => setTimeout(r, 1500));
  await evalJs(`document.documentElement.classList.add('rise-failsafe');
    document.querySelectorAll('[data-rise]').forEach(function(e){e.classList.add('is-in')});
    var s=document.createElement('style');
    s.textContent='[data-rise]{opacity:1!important;transform:none!important;transition:none!important;animation:none!important}';
    document.head.appendChild(s); 1`);
  await new Promise((r) => setTimeout(r, 300));
  const res = await evalJs(PROBE);
  const known = res.tap.filter((o) => o.why);
  res.tap = res.tap.filter((o) => !o.why);
  known.forEach((o) => { const k = o.why + ' || ' + o.n.split(' [')[0]; exceptions.set(k, (exceptions.get(k) || 0) + 1); });
  const n = res.over.length + res.tap.length + res.squeeze.length + res.clamp.length;
  console.log('\n=== ' + label + '  ' + path + '  - находок: ' + n + ' ===');
  for (const k of Object.keys(totals)) totals[k] += res[k].length;
  if (res.over.length) {
    console.log('  ПЕРЕПОЛНЕНИЕ по горизонтали (окно 375):');
    res.over.slice(0, 10).forEach((o) => console.log('    x=' + pad(o.x, 6) + 'ш=' + pad(o.w, 6) + o.n));
    if (res.over.length > 10) console.log('    ...ещё ' + (res.over.length - 10));
  }
  if (res.tap.length) {
    const hard = res.tap.filter((o) => o.hard), soft = res.tap.filter((o) => !o.hard);
    if (hard.length) {
      console.log('  ЗОНА НАЖАТИЯ меньше 24 - провал WCAG 2.5.8:');
      hard.slice(0, 10).forEach((o) => console.log('    ' + pad(o.w + 'x' + o.h, 12) + o.n));
      if (hard.length > 10) console.log('    ...ещё ' + (hard.length - 10));
    }
    if (soft.length) {
      console.log('  ЗОНА НАЖАТИЯ от 24 до 44 - ниже норматива проекта:');
      soft.slice(0, 10).forEach((o) => console.log('    ' + pad(o.w + 'x' + o.h, 12) + o.n));
      if (soft.length > 10) console.log('    ...ещё ' + (soft.length - 10));
    }
  }
  if (res.squeeze.length) {
    console.log('  ПОЛЯ СХЛОПНУЛИСЬ (высота задана числом, содержимое не влезло):');
    res.squeeze.slice(0, 10).forEach((o) => console.log('    h=' + pad(o.h, 6) + 'объявлено ' + pad(o.padObj, 9) + 'по факту ' + pad(o.padReal, 9) + o.n));
    if (res.squeeze.length > 10) console.log('    ...ещё ' + (res.squeeze.length - 10));
  }
  if (res.clamp.length) {
    console.log('  ТЕКСТ ОБРЕЗАН:');
    res.clamp.slice(0, 10).forEach((o) => console.log('    ' + pad(o.lines + ' стр.', 10) + 'h=' + pad(o.h, 6) + o.n));
    if (res.clamp.length > 10) console.log('    ...ещё ' + (res.clamp.length - 10));
  }
}

console.log('\n=== ИТОГО по страницам ===');
console.log('  переполнение: ' + totals.over + ', зоны нажатия: ' + totals.tap +
            ', схлопнутые поля: ' + totals.squeeze + ', обрезанный текст: ' + totals.clamp);

if (exceptions.size) {
  console.log('\n=== ОСОЗНАННЫЕ ОТСТУПЛЕНИЯ (не глушим, держим на виду) ===');
  for (const [k, v] of exceptions) {
    const [why, what] = k.split(' || ');
    console.log('  x' + pad(v, 4) + what + '\n        ' + why);
  }
}

// --- 5. ховеры в стилях -----------------------------------------------------
const cssPath = new URL('../theme/artdom/css/style.css', import.meta.url);
const css = readFileSync(cssPath, 'utf8');
const guarded = [];
const re = /@media[^{]*\(\s*hover\s*:\s*hover\s*\)[^{]*\{/g;
let m;
while ((m = re.exec(css))) {
  let depth = 1, i = m.index + m[0].length;
  for (; i < css.length && depth > 0; i++) {
    if (css[i] === '{') depth++;
    else if (css[i] === '}') depth--;
  }
  guarded.push([m.index, i]);
}
const inGuard = (pos) => guarded.some(([a, b]) => pos >= a && pos < b);
const hovers = [];
const hre = /:hover\b/g;
while ((m = hre.exec(css))) {
  if (inGuard(m.index)) continue;
  const line = css.slice(0, m.index).split('\n').length;
  const sel = css.slice(css.lastIndexOf('\n', m.index) + 1, css.indexOf('{', m.index)).trim();
  hovers.push(line + ': ' + sel);
}
console.log('\n=== ХОВЕРЫ БЕЗ ЗАЩИТЫ (@media (hover: hover)) ===');
console.log('  всего правил с :hover - ' + (css.match(/:hover\b/g) || []).length + ', не закрыто - ' + hovers.length);
hovers.forEach((h) => console.log('    ' + h));

chrome.kill();
// Профиль Chrome освобождает не сразу — уборка не должна ронять проверку.
try { rmSync(profile, { recursive: true, force: true }); }
catch (e) { console.log('\n(временный профиль ' + profile + ' остался: ' + e.code + ')'); }
process.exit(0);
