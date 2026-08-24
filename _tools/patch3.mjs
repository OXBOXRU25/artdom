import fs from 'node:fs';
const css = 'D:/AI/Artdom/static/css/style.css';
const htm = 'D:/AI/Artdom/static/index.html';
let s = fs.readFileSync(css, 'utf8');
let h = fs.readFileSync(htm, 'utf8');
const log = [];
const inCss = (a, b) => { if (!s.includes(a)) { log.push('CSS НЕ НАЙДЕНО: ' + a.slice(0,50)); return; } s = s.split(a).join(b); log.push('css ok  ' + a.slice(0,50)); };
const inHtml = (a, b) => { const n = h.split(a).length - 1; if (!n) { log.push('HTML НЕ НАЙДЕНО: ' + a.slice(0,50)); return; } h = h.split(a).join(b); log.push('html x' + n + '  ' + a.slice(0,50)); };

// 1. viewBox на внешние svg — иначе у них нет собственной пропорции и высота уезжает в 150px
inHtml('<svg class="btn__arrow" aria-hidden="true">', '<svg class="btn__arrow" viewBox="0 0 23 6" aria-hidden="true">');
inHtml('<span class="acc__icon" aria-hidden="true"><svg>', '<span class="acc__icon" aria-hidden="true"><svg viewBox="0 0 12 12">');
inHtml('<svg aria-hidden="true"><use href="#i-star">', '<svg viewBox="0 0 20 19" aria-hidden="true"><use href="#i-star">');
inHtml('<svg aria-hidden="true"><use href="#i-burger">', '<svg viewBox="0 0 26 16" aria-hidden="true"><use href="#i-burger">');
inHtml('<svg aria-hidden="true"><use href="#i-close">', '<svg viewBox="0 0 22 22" aria-hidden="true"><use href="#i-close">');

// 2. О компании: блок в макете вставлен внутрь, 249..1656, колонки 505 и 902
inCss('.about__in { margin-block-start: calc(72 * var(--k)); display: grid; grid-template-columns: 505fr 132fr 1122fr; align-items: start; }',
      '.about__in {\n' +
      '  margin-block-start: calc(72 * var(--k));\n' +
      '  max-width: calc(1407 * var(--k)); margin-inline: auto;   /* в макете 249..1656 */\n' +
      '  display: grid; grid-template-columns: calc(505 * var(--k)) minmax(0, calc(902 * var(--k)));\n' +
      '  align-items: start;\n' +
      '}');
inCss('.about__main { grid-column: 3; }', '.about__main { grid-column: 2; }');
inCss('.about__quote { margin-block-start: calc(38 * var(--k)); font-size: var(--t-title); line-height: 1.2; }',
      '.about__quote { margin-block-start: calc(38 * var(--k)); max-width: calc(373 * var(--k)); font-size: var(--t-title); line-height: 1.2; }');

// 3. Зоны нажатия 48px без раздувания макета: коробка по-прежнему меряет свой шрифт,
//    цель торчит наружу отрицательным маргином ровно на прирост.
inCss('.ftr a { min-height: 48px; min-width: 44px; display: inline-flex; align-items: center; padding-inline: 8px; margin-inline: -8px; transition: opacity var(--dur) var(--ease); }',
      '.ftr a {\n' +
      '  min-height: 48px; min-width: 44px; display: inline-flex; align-items: center;\n' +
      '  padding-inline: 8px; margin-inline: -8px;\n' +
      '  margin-block: calc((1.5em - 48px) / 2);   /* цель 48, а ритм считается по шрифту */\n' +
      '  transition: opacity var(--dur) var(--ease);\n' +
      '}');
inCss('  min-height: 48px; display: inline-flex; align-items: center;\n  padding-inline: 8px; margin-inline: -8px;\n  transition: opacity var(--dur) var(--ease);\n}',
      '  min-height: 48px; display: inline-flex; align-items: center;\n  padding-inline: 8px; margin-inline: -8px;\n  margin-block: calc((1.5em - 48px) / 2);\n  transition: opacity var(--dur) var(--ease);\n}');

fs.writeFileSync(css, s, 'utf8'); fs.writeFileSync(htm, h, 'utf8');
console.log(log.join('\n'));
