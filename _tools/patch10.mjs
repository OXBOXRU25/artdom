import fs from 'node:fs';
const css = 'D:/AI/Artdom/static/css/style.css';
const htm = 'D:/AI/Artdom/static/index.html';
let s = fs.readFileSync(css, 'utf8');
let h = fs.readFileSync(htm, 'utf8');
const log = [];
const c = (a, b) => { if (!s.includes(a)) { log.push('CSS НЕ НАЙДЕНО: ' + a.slice(0, 54)); return; } s = s.split(a).join(b); log.push('css ok  ' + a.slice(0, 54)); };

/* ---- 1. ЗНАК ЛОГОТИПА: обводка по logo.png от Павлона --------------------- */
const paths = fs.readFileSync('D:/AI/Artdom/_tools/mark.txt', 'utf8').trim().split('\n');
const a1 = h.indexOf('<svg class="logo__mark"');
if (a1 < 0) { console.log('знак в разметке не найден'); process.exit(1); }
const b1 = h.indexOf('</svg>', a1) + 6;
const newMark = '<svg class="logo__mark" viewBox="0 0 38 40" fill="currentColor" aria-hidden="true">' +
  paths.map((p) => '<path d="' + p + '"/>').join('') + '</svg>';
h = h.slice(0, a1) + newMark + h.slice(b1);
log.push('знак переобведён: ' + paths.length + ' контура, ' + newMark.length + ' символов');

/* ---- 2. ФУТЕР: голубой WhatsApp был не аномалией, а показанным ховером ----- */
h = h.split('<a class="ftr__wa" href="#">WhatsApp</a>').join('<a href="#">WhatsApp</a>');
log.push('постоянный синий с WhatsApp снят: ' + !h.includes('ftr__wa'));

c('  --c-wa:         #7595ff;',
  '  --c-accent-dark: #7595ff;   /* акцент на тёмном: 7.50:1, тогда как #4d6dd9 даёт ровно 4.50 */');
c('.ftr__wa { color: var(--c-wa); }', '');

/* подчёркивание оставляем только в шапке — в подвале сигнал другой, цветом */
c('.nav a, .hdr__tel, .ftr__nav a, .ftr__legal a, .ftr__soc a { position: relative; }',
  '.nav a, .hdr__tel { position: relative; }');
c('.nav a::after, .hdr__tel::after, .ftr__nav a::after, .ftr__legal a::after, .ftr__soc a::after {',
  '.nav a::after, .hdr__tel::after {');
c('.nav a:hover::after, .hdr__tel:hover::after,\n.ftr__nav a:hover::after, .ftr__legal a:hover::after, .ftr__soc a:hover::after { transform: scaleX(1); }',
  '.nav a:hover::after, .hdr__tel:hover::after { transform: scaleX(1); }');

c('.ftr a.ftr__tel:hover, .ftr a.ftr__mail:hover { opacity: .65; }',
  '.ftr a:hover { color: var(--c-accent-dark); }   /* ссылки в подвале синеют — так задумано в макете */');
c('  transition: opacity var(--dur) var(--ease);\n}\n.ftr a.ftr__tel:hover',
  '  transition: color var(--dur) var(--ease);\n}\n.ftr a.ftr__tel:hover');
c('.ftr a {\n  min-height: 48px;', '.ftr a {\n  transition: color var(--dur) var(--ease);\n  min-height: 48px;');

fs.writeFileSync(css, s, 'utf8');
fs.writeFileSync(htm, h, 'utf8');
console.log(log.join('\n'));
console.log('осталось упоминаний --c-wa: ' + (s.split('--c-wa').length - 1));
