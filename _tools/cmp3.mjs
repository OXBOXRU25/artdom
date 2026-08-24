import { createRequire } from 'node:module';
import fs from 'node:fs';
const require = createRequire(import.meta.url);
const { createCanvas, loadImage } = require('@napi-rs/canvas');
const S = process.argv[2];
const names = ['orig', 'h264', 'vp9'];
const labels = ['ОРИГИНАЛ HEVC 3.6МБ', 'H.264 517КБ', 'VP9 167КБ'];
const imgs = []; for (const n of names) imgs.push(await loadImage(fs.readFileSync(S + '/f-' + n + '.png')));
// кроп центральной части в 1:1, чтобы видеть реальные пиксели
const CW = 560, CH = 300, ox = 700, oy = 420;
const cv = createCanvas(CW + 20, (CH + 26) * 3 + 10); const c = cv.getContext('2d');
c.fillStyle = '#5b5f66'; c.fillRect(0, 0, cv.width, cv.height);
imgs.forEach((i, n) => {
  c.drawImage(i, ox, oy, CW, CH, 10, 22 + n * (CH + 26), CW, CH);
  c.fillStyle = '#fff'; c.font = '13px sans-serif'; c.fillText(labels[n], 10, 15 + n * (CH + 26));
});
fs.writeFileSync(S + '/video-quality.png', cv.toBuffer('image/png'));
console.log('ok ' + cv.width + 'x' + cv.height + '  (кроп 1:1 из центра кадра)');
