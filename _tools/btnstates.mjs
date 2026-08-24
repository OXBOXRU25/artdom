import { createRequire } from 'node:module';
import fs from 'node:fs';
const require = createRequire(import.meta.url);
const { createCanvas, loadImage } = require('@napi-rs/canvas');
const S = process.argv[2];
const files = ['bs-rest', 'bs-170', 'bs-330', 'bs-700'];
const labels = ['ПОКОЙ', 'НАВЕДЕНИЕ 170мс', '330мс', 'КОНЕЦ'];
const imgs = []; for (const f of files) imgs.push(await loadImage(fs.readFileSync(S + '/' + f + '.png')));
const X = 1290, Y = 855, W = 305, H = 104, K = 1.25;
const cv = createCanvas(W * K + 24, (H * K + 26) * imgs.length + 10); const c = cv.getContext('2d');
c.fillStyle = '#3a3f47'; c.fillRect(0, 0, cv.width, cv.height);
imgs.forEach((i, n) => {
  c.drawImage(i, X, Y, W, H, 12, 22 + n * (H * K + 26), W * K, H * K);
  c.fillStyle = '#fff'; c.font = '13px sans-serif';
  c.fillText(labels[n], 12, 15 + n * (H * K + 26));
});
fs.writeFileSync(S + '/btn-new.png', cv.toBuffer('image/png'));
console.log('ok ' + cv.width + 'x' + cv.height);
