import { createRequire } from 'node:module';
import fs from 'node:fs';
const require = createRequire(import.meta.url);
const { createCanvas, loadImage, Path2D } = require('@napi-rs/canvas');

const paths = fs.readFileSync('mark.txt','utf8').trim().split('\n');
const img = await loadImage(fs.readFileSync(process.argv[2]));
const W = img.width, H = img.height;

const a = createCanvas(W,H); const ac = a.getContext('2d');
ac.drawImage(img,0,0);
const A = ac.getImageData(0,0,W,H).data;

const b = createCanvas(W,H); const bc = b.getContext('2d');
bc.fillStyle = '#000';
for (const p of paths) bc.fill(new Path2D(p));
const B = bc.getImageData(0,0,W,H).data;

let same=0, diff=0, ink=0;
for (let i=0;i<W*H;i++){
  const oa = A[i*4+3] > 128, ob = B[i*4+3] > 128;
  if (oa) ink++;
  if (oa === ob) same++; else diff++;
}
console.log('пикселей всего ' + (W*H) + ', чернил в оригинале ' + ink);
console.log('совпало ' + same + ', разошлось ' + diff + '  =  ' + (diff/ink*100).toFixed(1) + '% от площади знака');

// картинка для глаз: оригинал | обводка | разница
const k = 8;
const cv = createCanvas(W*k*3 + 60, H*k + 40); const c = cv.getContext('2d');
c.fillStyle='#f0f0f2'; c.fillRect(0,0,cv.width,cv.height);
c.imageSmoothingEnabled=false;
c.drawImage(a,0,0,W,H,20,20,W*k,H*k);
c.drawImage(b,0,0,W,H,40+W*k,20,W*k,H*k);
for (let y=0;y<H;y++) for (let x=0;x<W;x++){
  const i=y*W+x, oa=A[i*4+3]>128, ob=B[i*4+3]>128;
  if (oa!==ob){ c.fillStyle = oa ? '#d02020' : '#20a020'; c.fillRect(60+W*k*2+x*k, 20+y*k, k, k); }
  else if (oa){ c.fillStyle='#c8c8cc'; c.fillRect(60+W*k*2+x*k, 20+y*k, k, k); }
}
fs.writeFileSync(process.env.OUTP, cv.toBuffer('image/png'));
console.log('красный = потеряли, зелёный = добавили лишнего');
