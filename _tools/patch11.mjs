import fs from 'node:fs';
const css = 'D:/AI/Artdom/static/css/style.css';
const htm = 'D:/AI/Artdom/static/index.html';
let s = fs.readFileSync(css, 'utf8');
let h = fs.readFileSync(htm, 'utf8');
const log = [];
const c = (a, b) => { if (!s.includes(a)) { log.push('CSS НЕ НАЙДЕНО: ' + a.slice(0, 54)); return; } s = s.split(a).join(b); log.push('css ok  ' + a.slice(0, 54)); };

/* ---- 1. РАДИУСЫ ПО ЗАМЕРУ КАЖДОГО УГЛА ОТДЕЛЬНО --------------------------
   Большая кнопка: скруглены только левый верхний и правый нижний, по диагонали.
   Малая: полная пилюля. Раньше я мерил один угол и распространял на все четыре. */
c('  --r-btn:    max(6px, calc(9 * var(--k)));    /* замер по PSD: 291x90, R=9 */\n' +
  '  --r-btn-sm: max(12px, calc(17 * var(--k)));  /* замер по PSD: 213x43, R=17 */',
  '  --r-btn:    max(6px, calc(9 * var(--k)));    /* 291x90: скруглены ЛВ и ПН, два других прямые */\n' +
  '  --r-btn-sm: 999px;                           /* 213x43: полная пилюля */');
c('  border-radius: var(--r-btn);\n  font-size: var(--t-mid); line-height: 1.22;',
  '  border-radius: var(--r-btn) 0 var(--r-btn) 0;   /* по диагонали, как в макете */\n' +
  '  font-size: var(--t-mid); line-height: 1.22;');

/* ---- 2. НОВЫЙ ХОВЕР: стрелка улетает за правый край и приходит снова ------
   Окно обрезки продлеваем паддингом до края кнопки и гасим его отрицательным
   маргином — раскладка не двигается, а улетать стрелке есть куда.
   Режем только по горизонтали: overflow: hidden срезал бы стрелку по высоте. */
c('.btn__arrow {\n  flex: none; width: calc(23 * var(--k)); min-width: 18px;\n  transition: transform .5s var(--ease);\n}\n' +
  '.btn:hover .btn__arrow { transform: translateX(5px); }',
  '.btn__arrow {\n' +
  '  flex: none; position: relative; display: block;\n' +
  '  width: calc(23 * var(--k)); min-width: 18px;\n' +
  '  height: calc(6 * var(--k)); min-height: 5px;\n' +
  '  overflow-x: clip; overflow-y: visible;\n' +
  '  padding-inline-end: max(20px, calc(27 * var(--k)));\n' +
  '  margin-inline-end: calc(0px - max(20px, calc(27 * var(--k))));\n' +
  '}\n' +
  '.btn__arrow svg {\n' +
  '  position: absolute; inset-block: 0; inset-inline-start: 0;\n' +
  '  width: calc(23 * var(--k)); min-width: 18px; height: 100%;\n' +
  '  transition: transform .55s var(--ease);\n' +
  '}\n' +
  '.btn__arrow svg:nth-child(2) { transform: translateX(-150%); }\n' +
  '.btn:hover .btn__arrow svg:nth-child(1) { transform: translateX(calc(100% + max(20px, calc(30 * var(--k))))); }\n' +
  '.btn:hover .btn__arrow svg:nth-child(2) { transform: translateX(0); }');

/* фон больше не темнеет: ховер теперь несёт стрелка */
c('.btn:hover { background: color-mix(in srgb, var(--btn-bg) 86%, #000); }', '');
c('.btn--ghost:hover { background: color-mix(in srgb, var(--c-white) 90%, var(--c-accent)); }', '');

/* ---- 3. РАЗМЕТКА: две копии стрелки ---------------------------------------- */
const from = '<svg class="btn__arrow" viewBox="0 0 23 6" aria-hidden="true"><use href="#i-arrow"></use></svg>';
const to = '<span class="btn__arrow" aria-hidden="true">' +
  '<svg viewBox="0 0 23 6"><use href="#i-arrow"></use></svg>' +
  '<svg viewBox="0 0 23 6"><use href="#i-arrow"></use></svg></span>';
const n = h.split(from).length - 1;
h = h.split(from).join(to);
log.push('стрелок удвоено: ' + n);

fs.writeFileSync(css, s, 'utf8');
fs.writeFileSync(htm, h, 'utf8');
const o = (s.match(/\/\*/g) || []).length, cl = (s.match(/\*\//g) || []).length;
console.log(log.join('\n') + '\nбаланс комментариев: ' + o + '/' + cl + (o === cl ? '  ок' : '  РАСХОЖДЕНИЕ'));
