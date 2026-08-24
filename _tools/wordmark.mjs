// Свой сериализатор пути: opentype.js 2.0.0 в toPathData() выдаёт NaN на части команд,
// хотя сами команды корректны (проверено: 256 команд, 0 NaN).
import { createRequire } from 'node:module';
import fs from 'node:fs';
const require = createRequire(import.meta.url);
const opentype = require('opentype.js');

const raw = fs.readFileSync(process.env.LOCALAPPDATA + '/Microsoft/Windows/Fonts/Bowler.ttf');
const font = opentype.parse(raw.buffer.slice(raw.byteOffset, raw.byteOffset + raw.byteLength));
const SIZE = 100;
const bb = font.getPath('Артдом', 0, 0, SIZE).getBoundingBox();
const p = font.getPath('Артдом', -bb.x1, -bb.y1, SIZE);

const n = (v) => {
  const s = (Math.round(v * 100) / 100).toString();
  return s === '-0' ? '0' : s;
};
let d = '';
for (const c of p.commands) {
  if (c.type === 'M') d += 'M' + n(c.x) + ' ' + n(c.y);
  else if (c.type === 'L') d += 'L' + n(c.x) + ' ' + n(c.y);
  else if (c.type === 'Q') d += 'Q' + n(c.x1) + ' ' + n(c.y1) + ' ' + n(c.x) + ' ' + n(c.y);
  else if (c.type === 'C') d += 'C' + n(c.x1) + ' ' + n(c.y1) + ' ' + n(c.x2) + ' ' + n(c.y2) + ' ' + n(c.x) + ' ' + n(c.y);
  else if (c.type === 'Z') d += 'Z';
}
if (d.includes('NaN') || d.includes('undefined')) { console.error('ВСЁ ЕЩЁ ЕСТЬ NaN — не пишу'); process.exit(1); }

const w = (bb.x2 - bb.x1), h = (bb.y2 - bb.y1);
const svg = '<svg class="logo__word" viewBox="0 0 ' + n(w) + ' ' + n(h) + '" fill="currentColor" aria-hidden="true"><path d="' + d + '"/></svg>';

const f = 'D:/AI/Artdom/static/index.html';
let html = fs.readFileSync(f, 'utf8');
const a = html.indexOf('<svg class="logo__word"');
if (a < 0) { console.error('не нашёл слово в разметке'); process.exit(1); }
const b = html.indexOf('</svg>', a) + 6;
fs.writeFileSync(f, html.slice(0, a) + svg + html.slice(b), 'utf8');

console.log('слово переписано: ' + d.length + ' символов, viewBox 0 0 ' + n(w) + ' ' + n(h));
console.log('NaN в файле: ' + (fs.readFileSync(f, 'utf8').split('NaN').length - 1));
