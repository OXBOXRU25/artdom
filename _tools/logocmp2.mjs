import { createRequire } from 'node:module';
import fs from 'node:fs';
const require = createRequire(import.meta.url);
const { createCanvas, loadImage } = require('@napi-rs/canvas');
const S = process.argv[2];
const shot = await loadImage(fs.readFileSync(S + '/logo-new2.png'));   // dpr 3
const old = await loadImage(fs.readFileSync(S + '/logo-new.png'));   // старая обводка, dpr 3
const png = await loadImage(fs.readFileSync('D:/AI/Artdom/logo.png'));
const K = 6, W = 40 * K, H = 40 * K;
const cv = createCanvas(W * 3 + 40, H + 34); const c = cv.getContext('2d');
c.fillStyle = '#2b2e33'; c.fillRect(0, 0, cv.width, cv.height);
c.imageSmoothingEnabled = false;
c.drawImage(png, 0, 0, 40, 40, 10, 26, W, H);
c.drawImage(old, 44 * 3, 42 * 3, 40 * 3, 40 * 3, 20 + W, 26, W, H);
c.drawImage(shot, 44 * 3, 42 * 3, 40 * 3, 40 * 3, 30 + W * 2, 26, W, H);
c.fillStyle = '#fff'; c.font = '13px sans-serif';
c.fillText('ТВОЙ PNG', 10, 18);
c.fillText('СТАЛО eps5 (3.1%)', 20 + W, 18);
c.fillText('СТАЛО eps1.5 (1.8%)', 30 + W * 2, 18);
fs.writeFileSync(S + '/logo-final.png', cv.toBuffer('image/png'));
console.log('ok ' + cv.width + 'x' + cv.height);
