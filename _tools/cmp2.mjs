// Эталон и вёрстка рядом во всю высоту, каждый в своём масштабе по ширине.
import { createRequire } from 'node:module';
import fs from 'node:fs';
const require = createRequire(import.meta.url);
const { createCanvas, loadImage } = require('@napi-rs/canvas');
const ref = await loadImage(fs.readFileSync(process.argv[2]));
const our = await loadImage(fs.readFileSync(process.argv[3]));
const k = Number(process.argv[5]);
const W = Math.round(1920 * k);
const H = Math.round(Math.max(ref.height, our.height) * k);
const cv = createCanvas(W * 2 + 30, H + 26); const c = cv.getContext('2d');
c.fillStyle = '#6b6f76'; c.fillRect(0, 0, cv.width, cv.height);
c.drawImage(ref, 0, 0, ref.width, ref.height, 10, 20, W, Math.round(ref.height * k));
c.drawImage(our, 0, 0, our.width, our.height, W + 20, 20, W, Math.round(our.height * k));
c.fillStyle = '#fff'; c.font = '12px sans-serif';
c.fillText('СТАТИКА ' + ref.height, 12, 13);
c.fillText('WORDPRESS ' + our.height, W + 22, 13);
fs.writeFileSync(process.argv[4], cv.toBuffer('image/png'));
console.log('ok ' + cv.width + 'x' + cv.height);
