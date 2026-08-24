import fs from 'node:fs';
const css='D:/AI/Artdom/static/css/style.css';
let s=fs.readFileSync(css,'utf8'); const log=[];
const c=(a,b)=>{ if(!s.includes(a)){log.push('НЕ НАЙДЕНО: '+a.slice(0,54));return;} s=s.split(a).join(b); log.push('ok  '+a.slice(0,54)); };

// Телефон и почта — ссылки, а общее правило подвала делает их inline-flex,
// поэтому они вставали в одну строку. Ставим на свою строку.
c('.ftr__tel { font-size: var(--t-h2); line-height: 1.1; }',
  '.ftr__tel { display: flex; width: fit-content; font-size: var(--t-h2); line-height: 1.1; }');
c('.ftr__mail { font-size: var(--t-mail); line-height: 1.3; margin-block-start: calc(10 * var(--k)); }',
  '.ftr__mail { display: flex; width: fit-content; font-size: var(--t-mail); line-height: 1.3; margin-block-start: calc(10 * var(--k)); }');

// Ритм подвала: в макете строки меню идут через 48.57, правовые и соцсети — через 30.
// Общий отрицательный маргин по 1.5em стягивал и то, и другое.
c('.ftr__nav { display: grid; gap: calc(6 * var(--k)); justify-items: start;',
  '.ftr__nav a { margin-block: 0; }                       /* в макете строки и так по 48.5 */\n' +
  '.ftr__soc a, .ftr__legal a { margin-block: -9px; }     /* строка 30, цель 48 */\n' +
  '.ftr__nav { display: grid; gap: 0; justify-items: start;');
c('.ftr__soc { margin-block-start: calc(40 * var(--k)); display: grid; gap: calc(4 * var(--k)); justify-items: start; }',
  '.ftr__soc { margin-block-start: calc(40 * var(--k)); display: grid; gap: 0; justify-items: start; }');
c('.ftr__legal { margin-block-start: calc(46 * var(--k)); display: grid; gap: calc(4 * var(--k)); justify-items: start;',
  '.ftr__legal { margin-block-start: calc(54 * var(--k)); display: grid; gap: 0; justify-items: start;');

fs.writeFileSync(css,s,'utf8'); console.log(log.join('\n'));
