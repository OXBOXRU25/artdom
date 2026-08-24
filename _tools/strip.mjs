// Длинную мобильную страницу режет на колонки и кладёт рядом — контактный лист.
import { createRequire } from 'node:module';
import fs from 'node:fs';
const require = createRequire(import.meta.url);
const { createCanvas, loadImage } = require('@napi-rs/canvas');
const img = await loadImage(fs.readFileSync(process.argv[2]));
const cols = Number(process.argv[4] || 4), k = Number(process.argv[5] || 0.45);
const seg = Math.ceil(img.height / cols);
const W = Math.round(img.width * k), H = Math.round(seg * k);
const cv = createCanvas(cols * (W + 12) + 12, H + 24); const c = cv.getContext('2d');
c.fillStyle = '#6b6f76'; c.fillRect(0, 0, cv.width, cv.height);
for (let i = 0; i < cols; i++) {
  const sh = Math.min(seg, img.height - i * seg);
  c.drawImage(img, 0, i * seg, img.width, sh, 12 + i * (W + 12), 18, W, Math.round(sh * k));
  c.fillStyle = '#fff'; c.font = '11px sans-serif';
  c.fillText((i * seg) + '–' + (i * seg + sh), 12 + i * (W + 12), 12);
}
fs.writeFileSync(process.argv[3], cv.toBuffer('image/png'));
console.log('ok ' + cv.width + 'x' + cv.height);
