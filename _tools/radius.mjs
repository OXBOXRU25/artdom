// Радиус скругления по смещению левого края вниз от угла: строка, где край выходит на 0, и есть R.
import { createRequire } from 'node:module';
import fs from 'node:fs';
const require = createRequire(import.meta.url);
const { createCanvas, loadImage } = require('@napi-rs/canvas');
for (const file of process.argv.slice(2)) {
  const img = await loadImage(fs.readFileSync(file));
  const W = img.width, H = img.height;
  const cv = createCanvas(W, H); const c = cv.getContext('2d');
  c.drawImage(img, 0, 0);
  const d = c.getImageData(0, 0, W, H).data;
  let R = null;
  const edge = [];
  for (let y = 0; y < Math.min(H, 60); y++) {
    let x = 0; while (x < W && d[(y * W + x) * 4 + 3] < 128) x++;
    edge.push(x);
    if (R === null && x === 0) R = y;
  }
  const name = file.split('/').pop();
  console.log(name + '  ' + W + 'x' + H + '  R=' + R + '  (пилюля была бы ' + (H / 2) + ')  край: ' + edge.slice(0, 12).join(','));
}
