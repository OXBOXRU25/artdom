import fs from 'node:fs';
const css='D:/AI/Artdom/static/css/style.css', htm='D:/AI/Artdom/static/index.html';
let s=fs.readFileSync(css,'utf8'), h=fs.readFileSync(htm,'utf8');
const log=[];
const c=(a,b)=>{ if(!s.includes(a)){log.push('CSS НЕ НАЙДЕНО: '+a.slice(0,52));return;} s=s.split(a).join(b); log.push('css ok  '+a.slice(0,52)); };
const t=(a,b)=>{ const n=h.split(a).length-1; if(!n){log.push('HTML НЕ НАЙДЕНО: '+a.slice(0,52));return;} h=h.split(a).join(b); log.push('html x'+n+'  '+a.slice(0,52)); };

/* ---- 1. ПОЛ У ПАДДИНГОВ ------------------------------------------------
   Где у коробки есть пол (min-width/min-height) или у шрифта есть пол в clamp,
   у отступа он тоже обязан быть — иначе коробка держится, а текст упирается в край. */
c('  min-height: 48px;\n  padding: calc(22 * var(--k)) calc(26 * var(--k));',
  '  min-height: 48px;\n  padding: max(14px, calc(22 * var(--k))) max(18px, calc(26 * var(--k)));');
c('  display: inline-flex; align-items: center; gap: calc(24 * var(--k));',
  '  display: inline-flex; align-items: center; gap: max(14px, calc(24 * var(--k)));');
c('  padding: calc(11 * var(--k)) calc(20 * var(--k));\n  font-size: var(--t-body);\n  gap: calc(16 * var(--k));',
  '  padding: max(10px, calc(11 * var(--k))) max(16px, calc(20 * var(--k)));\n  font-size: var(--t-body);\n  gap: max(12px, calc(16 * var(--k)));');
c('  padding: calc(26 * var(--k)) calc(27 * var(--k)) calc(26 * var(--k)) calc(25 * var(--k));',
  '  padding: max(18px, calc(26 * var(--k))) max(20px, calc(27 * var(--k)))\n           max(18px, calc(26 * var(--k))) max(20px, calc(25 * var(--k)));');
c('  padding: calc(9 * var(--k)) calc(16 * var(--k)); border-radius: var(--r-chip);',
  '  padding: max(6px, calc(9 * var(--k))) max(11px, calc(16 * var(--k))); border-radius: var(--r-chip);');

/* ---- 2. СТРЕЛКА УЕЗЖАЕТ ПРИ НАВЕДЕНИИ ---------------------------------- */
c('.btn__arrow { flex: none; width: calc(23 * var(--k)); min-width: 18px; }',
  '.btn__arrow {\n  flex: none; width: calc(23 * var(--k)); min-width: 18px;\n  transition: transform .5s var(--ease);\n}\n' +
  '.btn:hover .btn__arrow { transform: translateX(5px); }');

/* ---- 3. ПОДЧЁРКИВАНИЕ ССЫЛОК ------------------------------------------
   Псевдоэлементом, а не border-bottom: не трогает раскладку и переживает
   перенос строки. Затемнение убрано — один сигнал читается лучше двух. */
c('.nav a:hover, .hdr__tel:hover { opacity: .62; }',
  '.nav a, .hdr__tel, .ftr__nav a, .ftr__legal a, .ftr__soc a { position: relative; }\n' +
  '.nav a::after, .hdr__tel::after, .ftr__nav a::after, .ftr__legal a::after, .ftr__soc a::after {\n' +
  '  content: ""; position: absolute; inset-inline: 8px;\n' +
  '  inset-block-start: 50%; margin-block-start: .62em; height: var(--dp);\n' +
  '  background: currentColor;\n' +
  '  transform: scaleX(0); transform-origin: left center;\n' +
  '  transition: transform .5s var(--ease);\n' +
  '}\n' +
  '.nav a:hover::after, .hdr__tel:hover::after,\n' +
  '.ftr__nav a:hover::after, .ftr__legal a:hover::after, .ftr__soc a:hover::after { transform: scaleX(1); }');
c('.ftr a:hover { opacity: .65; }', '.ftr a.ftr__tel:hover, .ftr a.ftr__mail:hover { opacity: .65; }');

/* ---- 4. АККОРДЕОН: ПЛЮС ПОВОРАЧИВАЕТСЯ --------------------------------- */
c('  display: grid; place-items: center; color: var(--c-white);\n  transition: background-color var(--dur) var(--ease);',
  '  display: grid; place-items: center; color: var(--c-white);\n  transition: background-color var(--dur) var(--ease), transform .5s var(--ease);');
c('.acc__btn:hover { color: var(--c-accent); }',
  '.acc__btn:hover { color: var(--c-accent); }\n.acc__btn:hover .acc__icon { transform: rotate(90deg); }');

/* ---- 5. ШТОРКА НА ФОТО ВМЕСТО ВСПЛЫВАНИЯ ------------------------------- */
c('.js [data-rise] { opacity: 0; transform: translateY(22px); }',
  '.js [data-rise]:not([data-rise="shutter"]) { opacity: 0; transform: translateY(22px); }\n' +
  '.js [data-rise="shutter"] { clip-path: inset(0 0 100% 0); }');
c('.js [data-rise].is-in,\nhtml.rise-failsafe.rise-failsafe [data-rise] { opacity: 1; transform: none; transition: opacity .8s var(--ease), transform .8s var(--ease); }',
  '.js [data-rise]:not([data-rise="shutter"]).is-in,\n' +
  'html.rise-failsafe.rise-failsafe [data-rise]:not([data-rise="shutter"]) {\n' +
  '  opacity: 1; transform: none;\n  transition: opacity .8s var(--ease), transform .8s var(--ease);\n}\n' +
  '.js [data-rise="shutter"].is-in,\n' +
  'html.rise-failsafe.rise-failsafe [data-rise="shutter"] {\n' +
  '  clip-path: inset(0);\n  transition: clip-path .95s var(--ease);\n}');

/* ---- 6. ВХОД HERO ------------------------------------------------------
   Вешаем на контейнеры, а не на кнопку: анимация с fill both перебила бы
   её же transform в :active, и нажатие перестало бы отзываться. */
c('.hero__title { max-width: calc(760 * var(--k)); }',
  '@keyframes heroIn { from { opacity: 0; transform: translateY(28px); } to { opacity: 1; transform: none; } }\n' +
  '.hero__title { max-width: calc(760 * var(--k)); animation: heroIn 1s var(--ease) both; }');
c('.hero__aside { display: flex; flex-direction: column; align-items: flex-start; gap: calc(48 * var(--k)); }',
  '.hero__aside {\n  display: flex; flex-direction: column; align-items: flex-start; gap: calc(48 * var(--k));\n  animation: heroIn 1s var(--ease) .15s both;\n}');

/* ---- 7. ШТОРКА В РАЗМЕТКУ ---------------------------------------------- */
t('<figure class="services__media" data-rise>', '<figure class="services__media" data-rise="shutter">');
t('<div class="card__media">', '<div class="card__media" data-rise="shutter">');

fs.writeFileSync(css,s,'utf8'); fs.writeFileSync(htm,h,'utf8');
const o=(s.match(/\/\*/g)||[]).length, cl=(s.match(/\*\//g)||[]).length;
console.log(log.join('\n')+'\n\nбаланс комментариев: '+o+'/'+cl+(o===cl?'  ок':'  РАСХОЖДЕНИЕ'));
