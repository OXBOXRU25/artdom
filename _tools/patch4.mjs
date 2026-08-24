import fs from 'node:fs';
const css='D:/AI/Artdom/static/css/style.css', htm='D:/AI/Artdom/static/index.html';
let s=fs.readFileSync(css,'utf8'), h=fs.readFileSync(htm,'utf8');
const log=[];
const c=(a,b)=>{ if(!s.includes(a)){log.push('CSS НЕ НАЙДЕНО: '+a.slice(0,50));return;} s=s.split(a).join(b); log.push('css ok  '+a.slice(0,50)); };
const t=(a,b)=>{ const n=h.split(a).length-1; if(!n){log.push('HTML НЕ НАЙДЕНО: '+a.slice(0,50));return;} h=h.split(a).join(b); log.push('html x'+n+'  '+a.slice(0,50)); };

// 1. Отступы секций врозь: в макете сверху 46 до ЧЕРНИЛ заголовка, снизу 54 до края.
//    Коробка строки выше своих букв примерно на 7.5px при кегле 40, отсюда 39 сверху.
c('  --pad-sec:  max(48px, calc(64 * var(--k)));',
  '  --pad-top:  max(40px, calc(39 * var(--k)));   /* чернила H2 садятся на 46, как в макете */\n' +
  '  --pad-bot:  max(48px, calc(54 * var(--k)));');
c('.sec { padding-block: var(--pad-sec); }', '.sec { padding-block: var(--pad-top) var(--pad-bot); }');
c(':root { --k: calc(1180px / 1920); --pad-sec: 56px; --gutter: 20px; --gap-card: 20px; }',
  ':root { --k: calc(1180px / 1920); --pad-top: 48px; --pad-bot: 56px; --gutter: 20px; --gap-card: 20px; }');

// 2. Цифры: линейка в макете ровно 348 и тянется во всю высоту блока,
//    воздух сверху/снизу принадлежит СЕКЦИИ, а не блоку.
t('<section class="sec--white">\n    <div class="wrap">\n      <ul class="stats">',
  '<section class="sec--white stats-sec">\n    <div class="wrap">\n      <ul class="stats">');
c('.stats { display: grid; grid-template-columns: repeat(4, 1fr); }',
  '.stats-sec { padding-block: calc(65 * var(--k)) calc(59 * var(--k)); }\n' +
  '.stats { display: grid; grid-template-columns: repeat(4, 1fr); }');
c('  padding: calc(64 * var(--k)) calc(42 * var(--k)) calc(58 * var(--k));\n  display: flex; flex-direction: column;\n  min-height: calc(348 * var(--k));',
  '  padding-inline: calc(42 * var(--k));\n  display: flex; flex-direction: column;\n  min-height: calc(348 * var(--k));');

// 3. О компании: основной текст в макете 18/28, а не 16/24
c('.about__main p + p { margin-block-start: calc(26 * var(--k)); }',
  '.about__main .body { font-size: var(--t-mid); line-height: 1.556; }   /* 18/28 по PSD */\n' +
  '.about__main p + p { margin-block-start: calc(26 * var(--k)); }');

// 4. Большая кнопка ровно 90 в высоту
c('  min-height: calc(90 * var(--k));\n  justify-content: space-between;',
  '  height: calc(90 * var(--k)); min-height: 48px;\n  justify-content: space-between;');

// 5. Полоса слайдера: в макете 66 от низа карточек
c('  margin: calc(48 * var(--k)) auto 0;', '  margin: calc(66 * var(--k)) auto 0;');

fs.writeFileSync(css,s,'utf8'); fs.writeFileSync(htm,h,'utf8');
console.log(log.join('\n'));
