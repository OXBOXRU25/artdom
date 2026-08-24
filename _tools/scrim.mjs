// Контраст белого текста к фону под ним: берём среднюю яркость области заголовка.
import { createRequire } from 'node:module';
import fs from 'node:fs';
const require = createRequire(import.meta.url);
const { createCanvas, loadImage } = require('@napi-rs/canvas');
const lin = (v) => { v /= 255; return v <= 0.04045 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4); };
const S = process.argv[2];
for (let i = 0; i < 3; i++) {
  const img = await loadImage(fs.readFileSync(S + '/g-' + i + '.png'));
  const cv = createCanvas(img.width, img.height); const c = cv.getContext('2d');
  c.drawImage(img, 0, 0);
  // полоса под заголовком, но мимо самих букв: берём строки чуть выше и ниже
  const bands = [[560, 430, 800, 22], [560, 560, 800, 22]];
  let sum = 0, n = 0, worst = 1;
  for (const [x, y, w, h] of bands) {
    const d = c.getImageData(x, y, w, h).data;
    for (let p = 0; p < d.length; p += 4) {
      const L = 0.2126 * lin(d[p]) + 0.7152 * lin(d[p + 1]) + 0.0722 * lin(d[p + 2]);
      sum += L; n++;
      const ratio = 1.05 / (L + 0.05);
      if (ratio < worst) worst = ratio;
    }
  }
  const avgL = sum / n;
  console.log('  кадр ' + i + ':  средний контраст белого ' + (1.05 / (avgL + 0.05)).toFixed(2) +
    ':1,  в худшей точке ' + worst.toFixed(2) + ':1   ' + (worst >= 3 ? 'ок для крупного' : 'ПРОВАЛ'));
}
