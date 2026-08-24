import { createRequire } from 'node:module';
import fs from 'node:fs';
const require = createRequire(import.meta.url);
const { createCanvas, loadImage } = require('@napi-rs/canvas');
const S = process.argv[2];
const shot = await loadImage(fs.readFileSync(S + '/logo-mine.png'));   // dpr 3
const png = await loadImage(fs.readFileSync('D:/AI/Artdom/logo.png')); // 192x40
const K = 3;
const W = 192 * K, H = 40 * K;
const cv = createCanvas(W + 40, (H + 34) * 2 + 20); const c = cv.getContext('2d');
c.fillStyle = '#3a3f47'; c.fillRect(0, 0, cv.width, cv.height);
c.imageSmoothingEnabled = false;
// мой: логотип в шапке на 44,42 при dpr 3
c.drawImage(shot, 44 * 3, 42 * 3, W, H, 20, 26, W, H);
c.drawImage(png, 0, 0, 192, 40, 20, 26 + H + 34, W, H);
c.fillStyle = '#fff'; c.font = '14px sans-serif';
c.fillText('МОЙ (SVG)', 20, 18);
c.fillText('ТВОЙ (PNG)', 20, 18 + H + 34);
fs.writeFileSync(S + '/logo-cmp.png', cv.toBuffer('image/png'));
console.log('ok ' + cv.width + 'x' + cv.height);
