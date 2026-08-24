import fs from 'node:fs';
const css = 'D:/AI/Artdom/static/css/style.css';
let s = fs.readFileSync(css, 'utf8');
const log = [];
const sub = (from, to) => {
  if (!s.includes(from)) { log.push('НЕ НАЙДЕНО: ' + from.slice(0, 60)); return; }
  s = s.split(from).join(to); log.push('ok  ' + from.slice(0, 56));
};

// Правая колонка hero = 580 (ширина карточки), лид внутри неё 533
sub('grid-template-columns: minmax(0, 1fr) minmax(0, calc(533 * var(--k)));',
    'grid-template-columns: minmax(0, 1fr) minmax(0, calc(580 * var(--k)));');
sub('.hero__aside { display: flex; flex-direction: column; align-items: flex-start; gap: calc(48 * var(--k)); }',
    '.hero__aside { display: flex; flex-direction: column; align-items: flex-start; gap: calc(48 * var(--k)); }\n' +
    '.hero__aside .lead { max-width: calc(533 * var(--k)); }');

// Зоны нажатия: логотип и короткие ссылки в подвале не дотягивали до 44
sub('.logo { display: inline-flex; align-items: center; gap: calc(13 * var(--k)); color: inherit; }',
    '.logo { display: inline-flex; align-items: center; gap: calc(13 * var(--k)); color: inherit;\n' +
    '        min-height: 48px; margin-block: calc((40 * var(--k) - 48px) / 2); }');
sub('.ftr a { min-height: 48px; display: inline-flex; align-items: center; padding-inline: 8px; margin-inline: -8px;',
    '.ftr a { min-height: 48px; min-width: 44px; display: inline-flex; align-items: center; padding-inline: 8px; margin-inline: -8px;');

fs.writeFileSync(css, s, 'utf8');
console.log(log.join('\n'));
