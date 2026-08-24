import { createRequire } from 'node:module';
import fs from 'node:fs';
const require = createRequire(import.meta.url);
const { createCanvas, loadImage } = require('@napi-rs/canvas');
const png = await loadImage(fs.readFileSync('D:/AI/Artdom/logo.png'));
const cv0 = createCanvas(png.width, png.height); const c0 = cv0.getContext('2d');
c0.drawImage(png, 0, 0);
const d = c0.getImageData(0, 0, png.width, png.height).data;
// границы знака: первый разрыв по пустым столбцам
let cols = [];
for (let x = 0; x < png.width; x++) { let n = 0; for (let y = 0; y < png.height; y++) if (d[(y*png.width+x)*4+3] > 20) n++; cols.push(n); }
let end = 0; for (let x = 5; x < png.width; x++) if (cols[x] === 0 && cols[x-1] === 0 && cols[x-2] === 0) { end = x - 2; break; }
console.log('знак занимает x 0..' + end + ', слово начинается позже');
const K = 11, W = end + 2;
const cv = createCanvas(W*K, png.height*K); const c = cv.getContext('2d');
c.fillStyle = '#2b2e33'; c.fillRect(0,0,cv.width,cv.height);
c.imageSmoothingEnabled = false;
c.drawImage(png, 0, 0, W, png.height, 0, 0, W*K, png.height*K);
fs.writeFileSync(process.env.OUT, cv.toBuffer('image/png'));
console.log('ok ' + cv.width + 'x' + cv.height);
