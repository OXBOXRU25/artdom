// Радиус чипа по превью: для каждой строки ищем, где начинается заливка чипа.
import { createRequire } from 'node:module';
import fs from 'node:fs';
const require = createRequire(import.meta.url);
const { createCanvas, loadImage } = require('@napi-rs/canvas');
const img = await loadImage(fs.readFileSync('D:/AI/Artdom/artdom.jpg'));
const cv = createCanvas(img.width, img.height); const c = cv.getContext('2d');
c.drawImage(img, 0, 0);
for (const [name, X, Y, W, H] of JSON.parse(process.argv[2])) {
  const d = c.getImageData(X - 4, Y - 3, W + 8, H + 6).data;
  const w = W + 8;
  const fill = (x, y) => { const i = (y * w + x) * 4; return d[i] < 245 && d[i+1] < 247; }; // не белое
  const edges = [];
  for (let y = 0; y < H + 6; y++) { let x = 0; while (x < w && !fill(x, y)) x++; edges.push(x < w ? x : null); }
  const inner = edges.filter(v => v !== null);
  const base = Math.min(...inner);
  let R = 0; for (let i = 0; i < edges.length; i++) if (edges[i] === base) { R = i; break; }
  const top = edges.findIndex(v => v !== null);
  console.log(name.padEnd(18) + H + 'px высотой,  край по строкам: ' + edges.slice(top, top + 12).join(',') +
    '   ->  R ' + (R - top) + '  (полупилюля была бы ' + (H / 2) + ')');
}
