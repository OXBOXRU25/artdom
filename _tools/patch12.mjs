import fs from 'node:fs';
const css = 'D:/AI/Artdom/static/css/style.css';
const htm = 'D:/AI/Artdom/static/index.html';
let s = fs.readFileSync(css, 'utf8');
let h = fs.readFileSync(htm, 'utf8');
const log = [];
const c = (a, b) => { if (!s.includes(a)) { log.push('CSS НЕ НАЙДЕНО: ' + a.slice(0, 54)); return; } s = s.split(a).join(b); log.push('css ok  ' + a.slice(0, 54)); };

/* ---- 1. ОТСТУП ОТ СТРЕЛКИ -------------------------------------------------
   Прошлой правкой я продлил окно обрезки паддингом и погасил отрицательным
   маргином. Маргин утащил стрелку вправо и съел отступ кнопки — Павлон увидел.
   Окно снова по размеру стрелки, улёт в пределах своего окна. */
c('  overflow-x: clip; overflow-y: visible;\n' +
  '  padding-inline-end: max(20px, calc(27 * var(--k)));\n' +
  '  margin-inline-end: calc(0px - max(20px, calc(27 * var(--k))));\n' +
  '}',
  '  overflow-x: clip; overflow-y: visible;\n}');
c('.btn__arrow svg {\n  position: absolute; inset-block: 0; inset-inline-start: 0;\n  width: calc(23 * var(--k)); min-width: 18px; height: 100%;',
  '.btn__arrow svg {\n  position: absolute; inset-block: 0; inset-inline-start: 0;\n  width: 100%; height: 100%;');
c('.btn__arrow svg:nth-child(2) { transform: translateX(-150%); }\n' +
  '.btn:hover .btn__arrow svg:nth-child(1) { transform: translateX(calc(100% + max(20px, calc(30 * var(--k))))); }',
  '.btn__arrow svg:nth-child(2) { transform: translateX(-165%); }\n' +
  '.btn:hover .btn__arrow svg:nth-child(1) { transform: translateX(165%); }');

/* ---- 2. ПЕРЕКАТ ПЕРЕЕЗЖАЕТ ИЗ ЗАГОЛОВКОВ В ОБЩИЙ ПРИМИТИВ ----------------- */
c('.card__title .roll {\n' +
  '  display: block; position: relative;\n' +
  '  overflow-y: clip; overflow-x: visible;\n' +
  '  padding-block: .14em; margin-block: -.14em;\n' +
  '  transition: color var(--dur) var(--ease);\n' +
  '}',
  '.roll {\n' +
  '  display: block; position: relative;\n' +
  '  overflow-y: clip; overflow-x: visible;\n' +
  '  padding-block: .14em; margin-block: -.14em;\n' +
  '}\n' +
  '.card__title .roll { transition: color var(--dur) var(--ease); }');
c('.card:hover .roll__a { transform: translateY(calc(-100% - .3em)); }\n' +
  '.card:hover .roll__b { transform: translateY(0); }',
  '.card:hover .roll__a, .btn:hover .roll__a { transform: translateY(calc(-100% - .3em)); }\n' +
  '.card:hover .roll__b, .btn:hover .roll__b { transform: translateY(0); }');

/* ---- 3. РАЗМЕТКА: подписи кнопок в две копии ------------------------------ */
const lines = h.split('\n');
let inBtn = false, wrapped = 0;
for (let i = 0; i < lines.length; i++) {
  if (/<a class="btn[^"]*"/.test(lines[i]) || /<button class="btn/.test(lines[i])) { inBtn = true; continue; }
  if (!inBtn) continue;
  const m = lines[i].match(/^(\s*)<span>(.+)<\/span>\s*$/);
  if (m) {
    lines[i] = m[1] + '<span class="roll"><span class="roll__a">' + m[2] + '</span>' +
               '<span class="roll__b" aria-hidden="true">' + m[2] + '</span></span>';
    wrapped++;
    inBtn = false;
    continue;
  }
  if (lines[i].includes('</a>') || lines[i].includes('</button>')) inBtn = false;
}
h = lines.join('\n');
log.push('подписей кнопок под перекат: ' + wrapped);

fs.writeFileSync(css, s, 'utf8');
fs.writeFileSync(htm, h, 'utf8');
const o = (s.match(/\/\*/g) || []).length, cl = (s.match(/\*\//g) || []).length;
console.log(log.join('\n') + '\nбаланс комментариев: ' + o + '/' + cl + (o === cl ? '  ок' : '  РАСХОЖДЕНИЕ'));
