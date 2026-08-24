// Показывает эффекты конкретных текстовых слоёв целиком, включая общий флаг "эффекты включены".
import { createRequire } from 'node:module';
import fs from 'node:fs';
const require = createRequire(import.meta.url);
const { readPsd } = require('ag-psd');

const psd = readPsd(fs.readFileSync(process.argv[2]), {
  skipLayerImageData: true, skipCompositeImageData: true, skipThumbnail: true, logMissingFeatures: false,
});
const needle = process.argv[3];
const hex = (c) => c ? '#' + [c.r, c.g, c.b].map((n) => Math.round(n).toString(16).padStart(2, '0')).join('') : null;

function walk(ls) {
  for (const l of ls) {
    if (l.text && (l.text.text || '').includes(needle)) {
      console.log('СЛОЙ: ' + l.name);
      console.log('  fillColor стиля : ' + hex(l.text.style?.fillColor));
      console.log('  кегль/интерл    : ' + l.text.style?.fontSize + ' / ' + (l.text.style?.autoLeading ? 'auto' : l.text.style?.leading));
      console.log('  effects raw     : ' + JSON.stringify(l.effects));
    }
    if (l.children) walk(l.children);
  }
}
walk(psd.children || []);
