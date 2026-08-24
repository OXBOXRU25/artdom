// Стенд для проверки перетаскивания ленты: настоящие события мыши через CDP
// и покадровая запись scrollLeft. Синтетический click прошёл бы мимо всей
// механики указателя и сказал бы "работает" на сломанном коде.
import { spawn } from 'node:child_process';
import { mkdtempSync, rmSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join } from 'node:path';

const URL_ = process.argv[2] || 'http://localhost:4321/';
const CHROME = 'C:/Program Files/Google/Chrome/Application/chrome.exe';
const profile = mkdtempSync(join(tmpdir(), 'dragtest-'));

const chrome = spawn(CHROME, [
  '--headless=new', '--disable-gpu', '--hide-scrollbars',
  '--remote-debugging-port=0', '--user-data-dir=' + profile,
  '--window-size=1920,1000', 'about:blank',
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
const send = (ws, method, params, sessionId) => new Promise((res, rej) => {
  const n = ++id;
  pending.set(n, { res, rej });
  ws.send(JSON.stringify({ id: n, method, params: params || {}, sessionId }));
});
const onMsg = (ev) => {
  const m = JSON.parse(ev.data);
  if (m.id && pending.has(m.id)) {
    const p = pending.get(m.id); pending.delete(m.id);
    m.error ? p.rej(new Error(m.error.message)) : p.res(m.result);
  }
};
browser.addEventListener('message', onMsg);

const { targetId } = await send(browser, 'Target.createTarget', { url: 'about:blank' });
const { sessionId } = await send(browser, 'Target.attachToTarget', { targetId, flatten: true });
const S = (method, params) => send(browser, method, params, sessionId);

await S('Page.enable');
await S('Runtime.enable');
await S('Emulation.setDeviceMetricsOverride', { width: 1920, height: 1000, deviceScaleFactor: 1, mobile: false });
await S('Page.navigate', { url: URL_ });
await new Promise((r) => setTimeout(r, 2200));

const evalJs = async (expr) => {
  const r = await S('Runtime.evaluate', { expression: expr, returnByValue: true, awaitPromise: true });
  if (r.exceptionDetails) throw new Error(r.exceptionDetails.text + ' :: ' + JSON.stringify(r.exceptionDetails.exception || {}));
  return r.result.value;
};

const setup = await evalJs(`(function(){
  document.documentElement.classList.add('rise-failsafe');
  var sl = document.querySelector('.objects .slider');
  var tr = sl.querySelector('.slider__track');
  document.querySelector('#objects').scrollIntoView({behavior:'auto', block:'start'});
  tr.scrollLeft = 0;
  window.__s = [];
  (function rec(){
    window.__s.push([Math.round(performance.now()), Math.round(tr.scrollLeft)]);
    if (window.__s.length < 400) requestAnimationFrame(rec);
  })();
  var r = tr.getBoundingClientRect();
  return { hasJs: sl.classList.contains('has-js'),
           snap: getComputedStyle(tr).scrollSnapType,
           step: Math.round(tr.children[1].getBoundingClientRect().left - tr.children[0].getBoundingClientRect().left),
           x0: Math.round(r.left + r.width * 0.75), x1: Math.round(r.left + r.width * 0.2),
           y: Math.round(r.top + 150) };
})()`);
console.log('has-js: ' + setup.hasJs + ', snap: ' + setup.snap + ', шаг карточки: ' + setup.step);

// настоящая протяжка: нажали, десяток движений с паузой в кадр, отпустили
const mouse = (type, x, y, button) => S('Input.dispatchMouseEvent', {
  type, x, y, button: button || 'none', buttons: type === 'mouseReleased' ? 0 : (button ? 1 : 0), clickCount: button ? 1 : 0,
});

await mouse('mousePressed', setup.x0, setup.y, 'left');
const STEPS = 12;
for (let i = 1; i <= STEPS; i++) {
  const x = Math.round(setup.x0 + (setup.x1 - setup.x0) * (i / STEPS));
  await mouse('mouseMoved', x, setup.y, 'left');
  await new Promise((r) => setTimeout(r, 16));
}
await mouse('mouseReleased', setup.x1, setup.y, 'left');

await new Promise((r) => setTimeout(r, 1500));

const s = await evalJs('window.__s');
const tr0 = s[0][0];
const moving = s.filter((p, i) => i === 0 || p[1] !== s[i - 1][1]);
console.log('\nход прокрутки (мс от старта -> scrollLeft), только кадры с изменением:');
const shown = moving.filter((_, i) => i % 2 === 0).slice(0, 34);
for (const [t, v] of shown) console.log('  ' + String(t - tr0).padStart(5) + '  ' + String(v).padStart(5) + '  ' + '#'.repeat(Math.round(v / 20)));

const last = s[s.length - 1][1];
const target = Math.round(last / setup.step) * setup.step;
console.log('\nитог: ' + last + ', ближайшая карточка: ' + target + ', промах: ' + Math.abs(last - target) + 'px');

// плавность: считаем прирост между соседними кадрами и его разброс
const deltas = [];
for (let i = 1; i < s.length; i++) { const d = s[i][1] - s[i - 1][1]; if (d !== 0) deltas.push(d); }
const maxD = Math.max(...deltas.map(Math.abs));
console.log('кадров с движением: ' + deltas.length + ', наибольший прыжок за кадр: ' + maxD + 'px');

browser.close();
chrome.kill();
try { rmSync(profile, { recursive: true, force: true }); } catch {}
