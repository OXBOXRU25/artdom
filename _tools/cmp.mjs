// Кладёт эталон и наш кадр рядом: слева макет, справа вёрстка.
import { createRequire } from 'node:module';
import fs from 'node:fs';
const require = createRequire(import.meta.url);
const { createCanvas, loadImage } = require('@napi-rs/canvas');
const [refFile, ourFile, out, ry, rh, k] = [process.argv[2], process.argv[3], process.argv[4], +process.argv[5], +process.argv[6], +process.argv[7]];
const ref = await loadImage(fs.readFileSync(refFile));
const our = await loadImage(fs.readFileSync(ourFile));
const W = Math.round(1920 * k), H = Math.round(rh * k);
const cv = createCanvas(W * 2 + 30, H + 34); const c = cv.getContext('2d');
c.fillStyle = '#5b5f66'; c.fillRect(0, 0, cv.width, cv.height);
c.drawImage(ref, 0, ry, 1920, rh, 10, 24, W, H);
c.drawImage(our, 0, 0, our.width, Math.min(our.height, rh), W + 20, 24, W, H);
c.fillStyle = '#fff'; c.font = '13px sans-serif';
c.fillText('МАКЕТ', 12, 16); c.fillText('НАША ВЁРСТКА', W + 22, 16);
fs.writeFileSync(out, cv.toBuffer('image/png'));
console.log('ok ' + cv.width + 'x' + cv.height);
