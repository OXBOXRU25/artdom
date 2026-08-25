/* Контраст белого логотипа против фотографии под ним.
   Берём область логотипа, считаем светлоту по WCAG, за фон берём 35-й
   перцентиль (тёмная часть фото), за чернила — 97-й (белые штрихи).
   Одиночная пипетка тут врёт: и фон, и логотип пятнистые. */
import { createRequire } from 'node:module';
import fs from 'node:fs';
const require = createRequire(import.meta.url);
const { createCanvas, loadImage } = require('@napi-rs/canvas');

const [file, x, y, w, h] = [process.argv[2], ...process.argv.slice(3).map(Number)];
const img = await loadImage(fs.readFileSync(file));
const cv = createCanvas(img.width, img.height);
const ctx = cv.getContext('2d');
ctx.drawImage(img, 0, 0);
const d = ctx.getImageData(x, y, w, h).data;

const lum = [];
for (let i = 0; i < d.length; i += 4) {
  const s = [d[i], d[i + 1], d[i + 2]].map((v) => {
    v /= 255;
    return v <= 0.04045 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
  });
  lum.push(0.2126 * s[0] + 0.7152 * s[1] + 0.0722 * s[2]);
}
lum.sort((a, b) => a - b);
const bg = lum[Math.floor(lum.length * 0.35)];
const fg = lum[Math.floor(lum.length * 0.97)];
console.log(
  'фон L=' + bg.toFixed(3) + ', чернила L=' + fg.toFixed(3) +
  ' -> контраст ' + ((fg + 0.05) / (bg + 0.05)).toFixed(2) + ':1'
);
