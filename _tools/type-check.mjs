// Щуп кеглей: что на самом деле набрано на телефоне.
//
//   node _tools/type-check.mjs [адрес] [ширина]
//
// Норматив (скил oxbox-web, раздел про Material 3):
//   12px — пол, ниже нельзя вообще;
//   14-16px — корпусный текст на телефоне.
// Lighthouse валит страницу, где меньше 60% текста крупнее 12.
//
// Мерим ВИДИМЫЙ текст, а не правила в стилях: кегль приезжает из clamp,
// из наследования и из медиазапросов, и по исходнику его не прочитать.
// Группируем по «тег + классы», иначе в отчёт лезут сотни одинаковых строк.
import { spawn } from 'node:child_process';
import { mkdtempSync, rmSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join } from 'node:path';

const BASE = (process.argv[2] || 'https://artdom.oxboxdigital.ru').replace(/\/$/, '');
const W = Number(process.argv[3] || 375);
const CHROME = 'C:/Program Files/Google/Chrome/Application/chrome.exe';

const PAGES = [
  ['Главная', '/'],
  ['Объекты', '/objects/'],
  ['Услуги', '/services/'],
  ['Услуга', '/services/pokupka-i-prodazha/'],
  ['О компании', '/about/'],
  ['Контакты', '/contacts/'],
  ['Блог', '/blog/'],
  ['Отзывы', '/reviews/'],
];

const profile = mkdtempSync(join(tmpdir(), 'type-'));
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
await S('Emulation.setDeviceMetricsOverride', { width: W, height: 812, deviceScaleFactor: 2, mobile: true });
await S('Emulation.setTouchEmulationEnabled', { enabled: true, maxTouchPoints: 5 });

const evalJs = async (e) => {
  const r = await S('Runtime.evaluate', { expression: e, returnByValue: true, awaitPromise: true });
  if (r.exceptionDetails) throw new Error(r.exceptionDetails.text);
  return r.result.value;
};

const PROBE = String.raw`(function(){
  var карта = {};
  var всегоЗнаков = 0, мелкихЗнаков = 0;
  var узлы = document.querySelectorAll('body *');
  for (var i = 0; i < узлы.length; i++) {
    var el = узлы[i];
    // только элементы с собственным текстом, иначе считаем обёртки
    var свой = '';
    for (var j = 0; j < el.childNodes.length; j++) {
      if (el.childNodes[j].nodeType === 3) свой += el.childNodes[j].nodeValue;
    }
    свой = свой.replace(/\s+/g, ' ').trim();
    if (!свой) continue;
    var r = el.getBoundingClientRect();
    if (r.width < 1 || r.height < 1) continue;
    var cs = getComputedStyle(el);
    if (cs.display === 'none' || cs.visibility === 'hidden' || cs.opacity === '0') continue;
    var кегль = Math.round(parseFloat(cs.fontSize) * 10) / 10;
    всегоЗнаков += свой.length;
    if (кегль < 12) мелкихЗнаков += свой.length;
    var имя = el.tagName.toLowerCase();
    var кл = (el.getAttribute('class') || '').trim().split(/\s+/).filter(Boolean).slice(0, 2).join('.');
    if (кл) имя += '.' + кл;
    var ключ = имя + ' @' + кегль;
    if (!карта[ключ]) карта[ключ] = { имя: имя, кегль: кегль, n: 0, интерлиньяж: cs.lineHeight, пример: свой.slice(0, 34) };
    карта[ключ].n++;
  }
  var список = [];
  for (var k in карта) список.push(карта[k]);
  список.sort(function (a, b) { return a.кегль - b.кегль; });
  return { список: список, всегоЗнаков: всегоЗнаков, мелкихЗнаков: мелкихЗнаков };
})()`;

const pad = (s, n) => String(s).padEnd(n);
let провалов = 0, нижеНормы = 0;

for (const [label, path] of PAGES) {
  await S('Page.navigate', { url: BASE + path });
  await new Promise((r) => setTimeout(r, 1500));
  await evalJs(`document.documentElement.classList.add('rise-failsafe');
    document.querySelectorAll('[data-rise]').forEach(function(e){e.classList.add('is-in')});
    var s=document.createElement('style');
    s.textContent='[data-rise]{opacity:1!important;transform:none!important;transition:none!important;animation:none!important}';
    document.head.appendChild(s); 1`);
  const res = await evalJs(PROBE);

  const мелкие = res.список.filter((x) => x.кегль < 14);
  провалов += res.список.filter((x) => x.кегль < 12).length;
  нижеНормы += мелкие.length;

  console.log('\n=== ' + label + '  ' + path + ' ===');
  if (!мелкие.length) {
    console.log('  всё крупнее 14 — норматив выдержан');
  } else {
    for (const x of мелкие) {
      const метка = x.кегль < 12 ? 'ПОЛ 12 ПРОБИТ' : 'ниже 14';
      console.log('  ' + pad(x.кегль + 'px', 8) + pad('x' + x.n, 5) + pad(метка, 15) + pad(x.имя, 30) + '«' + x.пример + '»');
    }
  }
  const доля = res.всегоЗнаков ? (100 * (res.всегоЗнаков - res.мелкихЗнаков) / res.всегоЗнаков) : 100;
  console.log('  знаков крупнее 12px: ' + доля.toFixed(1) + '%' + (доля < 60 ? '  ← Lighthouse валит страницу' : ''));
}

console.log('\n=== ИТОГО при ширине ' + W + ' ===');
console.log('  ролей мельче 12px: ' + провалов + ', мельче 14px: ' + нижеНормы);
console.log('  норматив (скил oxbox-web): 12 — пол, 14-16 — корпусный текст на телефоне');

chrome.kill();
try { rmSync(profile, { recursive: true, force: true }); } catch (e) { /* профиль занят, не беда */ }
process.exit(0);
