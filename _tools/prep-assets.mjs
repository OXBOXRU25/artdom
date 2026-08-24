// Готовит фотографии для сайта: режет извлечённые слои по рамке обтравки
// (в PSD фото лежит крупнее, а видно из него только окно нижнего слоя).
import { createRequire } from 'node:module';
import fs from 'node:fs';
const require = createRequire(import.meta.url);
const { createCanvas, loadImage } = require('@napi-rs/canvas');

const L = process.argv[2];   // папка со слоями
const OUT = process.argv[3]; // куда класть
fs.mkdirSync(OUT, { recursive: true });

// [файл слоя, левый-верх слоя, видимая рамка (x,y,w,h) в координатах холста, имя]
const jobs = [
  ['001-Слой_6_0x0_1920x1040.png',            [0, 0],       [0, 0, 1920, 1040],       'hero'],
  ['002-Слой_14_1064x944_911x1154.png',       [1064, 944],  [1166, 1085, 710, 722],   'uslugi'],
  ['003-1-Слой_22_-80x1462_956x1210.png',     [-80, 1462],  [44, 2055, 580, 395],     'object'],
  ['005-Слой_29_-59x3884_2034x963.png',       [-59, 3884],  [0, 3884, 1920, 963],     'garanty'],
  ['004-Слой_26_181x3149_335x298.png',        [181, 3149],  [249, 3176, 181, 181],    'founder'],
];

const enc = (cv) => {
  try { return ['webp', cv.toBuffer('image/webp', 82)]; }
  catch { return ['jpg', cv.toBuffer('image/jpeg', 88)]; }
};

for (const [file, [lx, ly], [vx, vy, vw, vh], name] of jobs) {
  const img = await loadImage(fs.readFileSync(L + '/' + file));
  const sx = vx - lx, sy = vy - ly;
  const cv = createCanvas(vw, vh);
  const ctx = cv.getContext('2d');
  ctx.drawImage(img, sx, sy, vw, vh, 0, 0, vw, vh);
  const [ext, buf] = enc(cv);
  const out = OUT + '/' + name + '.' + ext;
  fs.writeFileSync(out, buf);
  console.log(
    name.padEnd(9) + vw + 'x' + vh +
    '   из слоя ' + img.width + 'x' + img.height + ' со смещением ' + sx + ',' + sy +
    '   ' + Math.round(buf.length / 1024) + ' КБ  ' + ext
  );
}
