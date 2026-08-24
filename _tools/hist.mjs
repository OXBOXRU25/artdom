// Гистограмма по области: показывает, есть ли у цвета плато (истинный цвет) или это шум JPEG.
import { createRequire } from 'node:module';
import fs from 'node:fs';
const require = createRequire(import.meta.url);
const { createCanvas, loadImage } = require('@napi-rs/canvas');

const img = await loadImage(fs.readFileSync(process.argv[2]));
const cv = createCanvas(img.width, img.height);
const ctx = cv.getContext('2d');
ctx.drawImage(img, 0, 0);
const hx = (n) => n.toString(16).padStart(2, '0');

for (const [name, x, y, w, h] of JSON.parse(process.argv[3])) {
  const d = ctx.getImageData(x, y, w, h).data;
  const m = new Map();
  for (let i = 0; i < d.length; i += 4) {
    const k = d[i] + ',' + d[i + 1] + ',' + d[i + 2];
    m.set(k, (m.get(k) || 0) + 1);
  }
  const lum = (k) => { const [r, g, b] = k.split(',').map(Number); return 0.2126 * r + 0.7152 * g + 0.0722 * b; };
  const top = [...m.entries()].sort((a, b) => b[1] - a[1]).slice(0, 12).sort((a, b) => lum(a[0]) - lum(b[0]));
  console.log('--- ' + name + ' (' + w + 'x' + h + ') ---');
  for (const [k, c] of top) {
    const t = '#' + k.split(',').map((n) => hx(+n)).join('');
    console.log('   ' + t + '  ' + String(c).padStart(6) + '  ' + '#'.repeat(Math.min(50, Math.round((c / (w * h)) * 200))));
  }
}
