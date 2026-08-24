// Снимает цвет по областям: моду по всей области (для плоских заливок)
// и моду по самым тёмным 8% (для текста — там антиалиасинг, ядро букв держит истинный цвет).
import { createRequire } from 'node:module';
import fs from 'node:fs';
const require = createRequire(import.meta.url);
const { createCanvas, loadImage } = require('@napi-rs/canvas');

const img = await loadImage(fs.readFileSync(process.argv[2]));
const regions = JSON.parse(process.argv[3]);

const cv = createCanvas(img.width, img.height);
const ctx = cv.getContext('2d');
ctx.drawImage(img, 0, 0);

const hx = (n) => n.toString(16).padStart(2, '0');

for (const [name, x, y, w, h] of regions) {
  const d = ctx.getImageData(x, y, w, h).data;
  const all = new Map();
  const px = [];
  for (let i = 0; i < d.length; i += 4) {
    const key = d[i] + ',' + d[i + 1] + ',' + d[i + 2];
    all.set(key, (all.get(key) || 0) + 1);
    px.push([d[i] + d[i + 1] + d[i + 2], key]);
  }
  const mode = [...all.entries()].sort((a, b) => b[1] - a[1])[0];
  px.sort((a, b) => a[0] - b[0]);
  const darkCount = Math.max(1, Math.floor(px.length * 0.08));
  const dark = new Map();
  for (let i = 0; i < darkCount; i++) dark.set(px[i][1], (dark.get(px[i][1]) || 0) + 1);
  const darkMode = [...dark.entries()].sort((a, b) => b[1] - a[1])[0];
  const toHex = (k) => '#' + k.split(',').map((n) => hx(+n)).join('');
  const pct = (c) => Math.round((c / (w * h)) * 100);
  console.log(
    name.padEnd(28) +
    ' мода ' + toHex(mode[0]) + ' (' + pct(mode[1]) + '%)' +
    '   тёмное ядро ' + toHex(darkMode[0])
  );
}
