// Режет длинный макет на куски по границам секций и уменьшает вдвое — чтобы можно было смотреть.
import { createRequire } from 'node:module';
import fs from 'node:fs';
const require = createRequire(import.meta.url);
const { createCanvas, loadImage } = require('@napi-rs/canvas');

const src = process.argv[2];
const outDir = process.argv[3];
const scale = Number(process.argv[4] || 0.5);
const cuts = JSON.parse(process.argv[5]);

fs.mkdirSync(outDir, { recursive: true });
const img = await loadImage(fs.readFileSync(src));

for (const [name, y0, y1] of cuts) {
  const h = y1 - y0;
  const cw = Math.round(img.width * scale);
  const ch = Math.round(h * scale);
  const cv = createCanvas(cw, ch);
  const ctx = cv.getContext('2d');
  ctx.drawImage(img, 0, y0, img.width, h, 0, 0, cw, ch);
  const file = outDir + '/' + name + '.png';
  fs.writeFileSync(file, cv.toBuffer('image/png'));
  console.log(file, cw + 'x' + ch, 'из y' + y0 + '-' + y1);
}
