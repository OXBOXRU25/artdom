import { createRequire } from 'node:module';
import fs from 'node:fs';
const require = createRequire(import.meta.url);
const { createCanvas, loadImage } = require('@napi-rs/canvas');
const files = process.argv.slice(3);
const k = Number(process.argv[2]);
const imgs = [];
for (const f of files) imgs.push(await loadImage(fs.readFileSync(f)));
const W = imgs.reduce((s,i)=>s+i.width*k+20, 20);
const H = Math.max(...imgs.map(i=>i.height*k)) + 40;
const cv = createCanvas(W,H); const ctx = cv.getContext('2d');
ctx.fillStyle='#f0f0f2'; ctx.fillRect(0,0,W,H);
ctx.imageSmoothingEnabled=false;
let x=20;
for (const i of imgs){ ctx.drawImage(i,0,0,i.width,i.height,x,20,i.width*k,i.height*k); x+=i.width*k+20; }
fs.writeFileSync(process.env.ZOUT, cv.toBuffer('image/png'));
console.log('ok', W+'x'+H);
