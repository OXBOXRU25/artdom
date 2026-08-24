// Вытаскивает растровые слои PSD в PNG с прозрачностью.
// Берём только крупные слои — мелочь (стрелки, линии) делаем в CSS/SVG.
import { createRequire } from 'node:module';
import fs from 'node:fs';
const require = createRequire(import.meta.url);
const { readPsd, initializeCanvas } = require('ag-psd');
const { createCanvas } = require('@napi-rs/canvas');

initializeCanvas(createCanvas);

const src = process.argv[2];
const outDir = process.argv[3];
const minPx = Number(process.argv[4] || 20000);
fs.mkdirSync(outDir, { recursive: true });

const psd = readPsd(fs.readFileSync(src), {
  skipCompositeImageData: true,
  skipThumbnail: true,
  logMissingFeatures: false,
});

let n = 0;
const safe = (s) => s.replace(/[^0-9A-Za-zА-Яа-яёЁ _-]/g, '').replace(/\s+/g, '_').slice(0, 40);

function walk(ls, pathParts) {
  for (const l of ls) {
    const w = (l.right ?? 0) - (l.left ?? 0);
    const h = (l.bottom ?? 0) - (l.top ?? 0);
    if (l.children) { walk(l.children, pathParts.concat(safe(l.name))); continue; }
    if (l.text || !l.canvas) continue;
    if (w * h < minPx) continue;
    const name = (pathParts.length ? pathParts.join('-') + '-' : '') + safe(l.name) + '_' + l.left + 'x' + l.top + '_' + w + 'x' + h + '.png';
    fs.writeFileSync(outDir + '/' + name, l.canvas.toBuffer('image/png'));
    console.log(String(++n).padStart(3) + '  ' + name + (l.clipping ? '   [обтравка]' : '') + (l.hidden ? '   [скрыт]' : ''));
  }
}
walk(psd.children || [], []);
console.log('\nвсего: ' + n);
