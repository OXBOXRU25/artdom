import fs from 'node:fs';
const mark = fs.readFileSync('mark.txt','utf8').trim().split('\n');
const wm = fs.readFileSync('wordmark.txt','utf8').split('\n');
const vb = wm[0].trim(), wpath = wm.slice(1).join('').trim();

const svg =
  '<svg class="logo__mark" viewBox="0 0 38 40" fill="currentColor" aria-hidden="true">' +
    mark.map(p => '<path d="' + p + '"/>').join('') +
  '</svg>' +
  '<svg class="logo__word" viewBox="' + vb + '" fill="currentColor" aria-hidden="true">' +
    '<path d="' + wpath + '"/>' +
  '</svg>';

const f = 'D:/AI/Artdom/static/index.html';
let html = fs.readFileSync(f, 'utf8');
if (!html.includes('{{LOGO}}')) { console.log('плейсхолдер уже заменён — пропускаю'); process.exit(0); }
fs.writeFileSync(f, html.replace('{{LOGO}}', svg), 'utf8');
console.log('логотип вставлен: знак ' + mark.length + ' контура, слово ' + wpath.length + ' символов, всего ' + svg.length);
