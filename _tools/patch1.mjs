import fs from 'node:fs';
const css = 'D:/AI/Artdom/static/css/style.css';
const htm = 'D:/AI/Artdom/static/index.html';
let s = fs.readFileSync(css, 'utf8');
let h = fs.readFileSync(htm, 'utf8');
const rep = [];
const sub = (file, from, to) => {
  const t = file === 'css' ? s : h;
  if (!t.includes(from)) { rep.push('НЕ НАЙДЕНО: ' + from.slice(0, 60)); return; }
  const n = t.split(from).length - 1;
  if (file === 'css') s = s.split(from).join(to); else h = h.split(from).join(to);
  rep.push('ok x' + n + '  ' + from.slice(0, 52));
};

// 1. Радиусы — замерены по PSD: большая кнопка 9, малая 17, чип 3, фото 0
sub('css', '  --radius:   999px;',
        '  --r-btn:    max(6px, calc(9 * var(--k)));    /* замер по PSD: 291x90, R=9 */\n' +
        '  --r-btn-sm: max(12px, calc(17 * var(--k)));  /* замер по PSD: 213x43, R=17 */\n' +
        '  --r-chip:   3px;                             /* замер по PSD: 128x33, R=2..3 */');
sub('css', '  border-radius: var(--radius);\n  font-size: var(--t-mid); line-height: 1.22;',
        '  border-radius: var(--r-btn);\n  font-size: var(--t-mid); line-height: 1.22;');
sub('css', 'font-size: var(--t-body);\n  gap: calc(16 * var(--k));\n}',
        'font-size: var(--t-body);\n  gap: calc(16 * var(--k));\n  border-radius: var(--r-btn-sm);\n}');
sub('css', 'padding: calc(9 * var(--k)) calc(16 * var(--k)); border-radius: var(--radius); background: var(--c-chip);',
        'padding: calc(9 * var(--k)) calc(16 * var(--k)); border-radius: var(--r-chip); background: var(--c-chip);');

// 2. Фото без скругления — в макете углы прямые
sub('css', '.card__media { position: relative; aspect-ratio: 580 / 395; overflow: hidden; border-radius: 2px; }',
        '.card__media { position: relative; aspect-ratio: 580 / 395; overflow: hidden; }');
sub('css', '.services__media img { width: 100%; height: auto; border-radius: 2px; }',
        '.services__media img { width: 100%; height: auto; }');

// 3. Колонки hero: правая ровно 533 макетных, левая — остаток (было 1fr/533fr = 3px)
sub('css', '  display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 533fr);',
        '  display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, calc(533 * var(--k)));');

// 4. Большая кнопка: в макете 291x90, текст слева, стрелка прижата вправо
sub('css', '.btn--ghost { --btn-bg: var(--c-white); --btn-fg: var(--c-accent); }',
        '.btn--wide {\n' +
        '  width: calc(291 * var(--k)); min-width: 232px;\n' +
        '  min-height: calc(90 * var(--k));\n' +
        '  justify-content: space-between;\n' +
        '  padding: calc(26 * var(--k)) calc(27 * var(--k)) calc(26 * var(--k)) calc(25 * var(--k));\n' +
        '}\n' +
        '.btn--ghost { --btn-bg: var(--c-white); --btn-fg: var(--c-accent); }');

// 5. Класс на все большие кнопки в разметке
h = h.split('<a class="btn" href=').join('<a class="btn btn--wide" href=');
rep.push('ok  btn--wide проставлен в разметке');

fs.writeFileSync(css, s, 'utf8');
fs.writeFileSync(htm, h, 'utf8');
console.log(rep.join('\n'));
const open = (s.match(/\/\*/g) || []).length, close = (s.match(/\*\//g) || []).length;
console.log('\nбаланс комментариев в CSS: ' + open + ' / ' + close + (open === close ? '  ок' : '  РАСХОЖДЕНИЕ'));
