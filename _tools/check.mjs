// 1) Достаёт эффекты "наложение цвета" с их прозрачностью и режимом — чтобы знать истинный цвет текста.
// 2) Считает контраст ключевых пар по WCAG.
import { createRequire } from 'node:module';
import fs from 'node:fs';
const require = createRequire(import.meta.url);
const { readPsd } = require('ag-psd');

const psd = readPsd(fs.readFileSync(process.argv[2]), {
  skipLayerImageData: true, skipCompositeImageData: true, skipThumbnail: true, logMissingFeatures: false,
});

const hex = (c) => c ? '#' + [c.r, c.g, c.b].map((n) => Math.round(n).toString(16).padStart(2, '0')).join('') : null;
const seen = new Map();

function walk(ls) {
  for (const l of ls) {
    const sf = l.effects?.solidFill;
    if (sf) for (const f of sf) {
      const key = hex(f.color) + ' | op ' + Math.round((f.opacity ?? 1) * 100) + '% | ' + (f.blendMode ?? 'normal') + ' | вкл ' + (f.enabled !== false);
      seen.set(key, (seen.get(key) || 0) + 1);
    }
    if (l.children) walk(l.children);
  }
}
walk(psd.children || []);

console.log('=== НАЛОЖЕНИЯ ЦВЕТА (эффект слоя) ===');
for (const [k, v] of [...seen.entries()].sort((a, b) => b[1] - a[1])) console.log('  x' + String(v).padEnd(3) + k);

// --- контраст ---
const lin = (v) => { v /= 255; return v <= 0.04045 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4); };
const lum = (h) => {
  const n = parseInt(h.slice(1), 16);
  return 0.2126 * lin((n >> 16) & 255) + 0.7152 * lin((n >> 8) & 255) + 0.0722 * lin(n & 255);
};
const ratio = (a, b) => {
  const [x, y] = [lum(a), lum(b)].sort((p, q) => q - p);
  return (x + 0.05) / (y + 0.05);
};

const pairs = [
  ['серый текст 16px', '#9c9eab', '#edeff4'],
  ['серый текст 16px (без наложения)', '#6a6d81', '#edeff4'],
  ['серый текст 16px на белом', '#9c9eab', '#ffffff'],
  ['чип «Новостройка» 14px', '#6a6d81', '#edeef4'],
  ['синяя ссылка/заголовок', '#4d6dd9', '#ffffff'],
  ['белый текст на синей кнопке', '#ffffff', '#4d6dd9'],
  ['заголовки', '#000003', '#edeff4'],
  ['серый текст в футере', '#9c9eab', '#000000'],
  ['белый текст в футере', '#ffffff', '#000000'],
  ['цифры 90px', '#4d6dd9', '#ffffff'],
];

console.log('\n=== КОНТРАСТ (WCAG AA: 4.5 обычный текст, 3.0 крупный от 24px) ===');
for (const [name, fg, bg] of pairs) {
  const r = ratio(fg, bg);
  const verdict = r >= 4.5 ? 'OK' : r >= 3 ? 'только для крупного' : 'ПРОВАЛ';
  console.log('  ' + name.padEnd(36) + fg + ' на ' + bg + '  =  ' + r.toFixed(2) + ':1   ' + verdict);
}
