import { createRequire } from 'node:module';
import fs from 'node:fs';
const require = createRequire(import.meta.url);
const { createCanvas, loadImage } = require('@napi-rs/canvas');
const S = process.argv[2], K = 0.31;
const imgs = []; for (let i = 0; i < 3; i++) imgs.push(await loadImage(fs.readFileSync(S + '/g-' + i + '.png')));
const W = Math.round(1920 * K), H = Math.round(1000 * K);
const cv = createCanvas(W + 20, (H + 24) * 3 + 8); const c = cv.getContext('2d');
c.fillStyle = '#4a4f57'; c.fillRect(0, 0, cv.width, cv.height);
imgs.forEach((im, n) => {
  c.drawImage(im, 0, 0, 1920, 1000, 10, 20 + n * (H + 24), W, H);
  c.fillStyle = '#fff'; c.font = '12px sans-serif';
  c.fillText('состояние ' + (n + 1) + ' из 3', 10, 14 + n * (H + 24));
});
fs.writeFileSync(S + '/guaranty-3.png', cv.toBuffer('image/png'));
console.log('ok ' + cv.width + 'x' + cv.height);
