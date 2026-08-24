import fs from 'node:fs';
const css = 'D:/AI/Artdom/static/css/style.css';
const htm = 'D:/AI/Artdom/static/index.html';
let s = fs.readFileSync(css, 'utf8');
let h = fs.readFileSync(htm, 'utf8');
const log = [];
const c = (a, b) => { if (!s.includes(a)) { log.push('CSS НЕ НАЙДЕНО: ' + a.slice(0, 50)); return; } s = s.split(a).join(b); log.push('css ok  ' + a.slice(0, 50)); };
const t = (a, b) => { const n = h.split(a).length - 1; if (!n) { log.push('HTML НЕ НАЙДЕНО: ' + a.slice(0, 50)); return; } h = h.split(a).join(b); log.push('html x' + n + '  ' + a.slice(0, 50)); };

/* ---- 1. ПЕРЕКАТ ЗАГОЛОВКА -------------------------------------------------
   Две копии текста в окне обрезки. Режем ТОЛЬКО по вертикали: overflow: hidden
   отрезал бы и по горизонтали, а там при отрицательном трекинге пропадает
   последняя литера. Окно шире чернил на .14em, иначе срезает выносные буквы,
   поэтому уезжать надо на 100% + .3em, чтобы копия ушла за край полностью. */
c('.card__title { font-size: var(--t-title); line-height: 1.15; margin-block-start: calc(24 * var(--k)); transition: color var(--dur) var(--ease); }',
  '.card__title { font-size: var(--t-title); line-height: 1.15; margin-block-start: calc(24 * var(--k)); }\n' +
  '.card__title .roll {\n' +
  '  display: block; position: relative;\n' +
  '  overflow-y: clip; overflow-x: visible;\n' +
  '  padding-block: .14em; margin-block: -.14em;\n' +
  '  transition: color var(--dur) var(--ease);\n' +
  '}\n' +
  '.roll__a, .roll__b { display: block; transition: transform .6s var(--ease); }\n' +
  '.roll__b {\n' +
  '  position: absolute; inset-block-start: .14em; inset-inline-start: 0; width: 100%;\n' +
  '  transform: translateY(calc(100% + .3em));\n' +
  '}\n' +
  '.card:hover .roll__a { transform: translateY(calc(-100% - .3em)); }\n' +
  '.card:hover .roll__b { transform: translateY(0); }\n' +
  'a[draggable="false"] { -webkit-user-drag: none; }');
c('.card:hover .card__title { color: var(--c-accent); }',
  '.card:hover .card__title .roll { color: var(--c-accent); }\n' +
  '.card__media { display: block; }');

/* ---- 2. КРУГЛЫЙ КУРСОР ----------------------------------------------------
   Позиция живёт в отдельном свойстве translate, а НЕ внутри transform:
   свойства применяются в порядке translate -> rotate -> scale -> transform,
   поэтому смещение внутри transform умножалось бы на scale и при затухании
   кружок стягивало бы в левый верхний угол экрана.
   Центрируем отрицательным маргином по той же причине. */
c('/* Контакты и цены копируют — оставляем выделяемыми явно */',
  '.cursor {\n' +
  '  position: fixed; inset-block-start: 0; inset-inline-start: 0;\n' +
  '  width: 86px; height: 86px; margin: -43px 0 0 -43px;\n' +
  '  border-radius: 50%; background: var(--c-accent); color: var(--c-white);\n' +
  '  display: grid; place-items: center;\n' +
  '  pointer-events: none; z-index: 60;\n' +
  '  scale: 0; opacity: 0;\n' +
  '  transition: scale .45s var(--ease), opacity .45s var(--ease);\n' +
  '}\n' +
  '.cursor[data-on="true"] { scale: 1; opacity: 1; }\n' +
  '.cursor svg { width: 30px; }\n' +
  '@media (hover: none), (pointer: coarse) { .cursor { display: none; } }\n\n' +
  '/* Контакты и цены копируют — оставляем выделяемыми явно */');

/* ---- 3. МОБИЛКА ----------------------------------------------------------- */
c('  .slider__track { grid-auto-columns: 82%; }',
  '  .slider__track { grid-auto-columns: 100%; }   /* один объект на экран целиком, без огрызков */');
c('  .ftr__cta, .ftr__mid { grid-template-columns: 1fr; gap: 44px; }',
  '  /* Экспертные решения: сперва основной текст, портрет с цитатой под ним */\n' +
  '  .about__main { order: 1; }\n' +
  '  .about__side { order: 2; margin-block-start: 40px; }\n' +
  '  .about__quote { max-width: none; }\n' +
  '  /* Цифры: линейка не должна лежать вплотную к числу и подписи */\n' +
  '  .stats__item { padding-block: 28px 30px; }\n' +
  '  .ftr__cta, .ftr__mid { grid-template-columns: 1fr; gap: 44px; }');
c('  .stats__label { margin-block-start: 18px; }', '  .stats__label { margin-block-start: 12px; }');

/* ---- 4. РАЗМЕТКА: слой курсора и крупная стрелка --------------------------- */
t('    <symbol id="i-close" viewBox="0 0 22 22">',
  '    <symbol id="i-arrow-lg" viewBox="0 0 30 10"><path d="M0 5h27M22.5 1.2 28.4 5l-5.9 3.8" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></symbol>\n' +
  '    <symbol id="i-close" viewBox="0 0 22 22">');
t('<script src="js/main.js"></script>',
  '<div class="cursor" data-cursor-el data-on="false" aria-hidden="true"><svg viewBox="0 0 30 10"><use href="#i-arrow-lg"></use></svg></div>\n\n<script src="js/main.js"></script>');

fs.writeFileSync(css, s, 'utf8');
fs.writeFileSync(htm, h, 'utf8');
const o = (s.match(/\/\*/g) || []).length, cl = (s.match(/\*\//g) || []).length;
console.log(log.join('\n') + '\n\nбаланс комментариев: ' + o + '/' + cl + (o === cl ? '  ок' : '  РАСХОЖДЕНИЕ'));
