import { createRequire } from 'node:module';
import fs from 'node:fs';
const require = createRequire(import.meta.url);
const { createCanvas, loadImage } = require('@napi-rs/canvas');
const lin = (v) => { v /= 255; return v <= 0.04045 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4); };
const S = process.argv[2];
console.log('  область под заголовком, белый текст, порог для крупного 3.0:1');
for (let i = 0; i < 3; i++) {
  const img = await loadImage(fs.readFileSync(S + '/gb-' + i + '.png'));
  const cv = createCanvas(img.width, img.height); const c = cv.getContext('2d');
  c.drawImage(img, 0, 0);
  const d = c.getImageData(500, 425, 920, 150).data;
  const rs = [];
  for (let p = 0; p < d.length; p += 4) {
    const L = 0.2126 * lin(d[p]) + 0.7152 * lin(d[p + 1]) + 0.0722 * lin(d[p + 2]);
    rs.push(1.05 / (L + 0.05));
  }
  rs.sort((a, b) => a - b);
  const avg = rs.reduce((a, b) => a + b, 0) / rs.length;
  const p5 = rs[Math.floor(rs.length * 0.05)];
  console.log('  кадр ' + i + ':  средний ' + avg.toFixed(2) + ':1,  худшие 5% площади ' + p5.toFixed(2) + ':1   ' +
    (p5 >= 3 ? 'ок' : 'НЕ ПРОХОДИТ'));
}
