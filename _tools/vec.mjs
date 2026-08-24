import { createRequire } from 'node:module';
import fs from 'node:fs';
const require = createRequire(import.meta.url);
const { readPsd } = require('ag-psd');
const psd = readPsd(fs.readFileSync('D:/AI/Artdom/004.psd'), {
  skipLayerImageData: true, skipCompositeImageData: true, skipThumbnail: true, logMissingFeatures: false,
});
function walk(ls, d) {
  for (const l of ls) {
    const w = (l.right ?? 0) - (l.left ?? 0), h = (l.bottom ?? 0) - (l.top ?? 0);
    const small = w <= 60 && h <= 60 && w > 10 && h > 10;
    if (small || /Слой 5|логотип|logo/i.test(l.name)) {
      const vm = l.vectorMask;
      console.log('  ' + l.name.padEnd(28) + w + 'x' + h + ' @' + l.left + ',' + l.top +
        '  vectorMask: ' + (vm ? (vm.paths ? vm.paths.length + ' путей' : 'есть, без paths') : 'НЕТ') +
        '  vectorFill: ' + (l.vectorFill ? 'есть' : 'нет'));
      if (vm && vm.paths) {
        for (const p of vm.paths) console.log('      путь: ' + (p.knots ? p.knots.length + ' узлов' : JSON.stringify(p).slice(0, 80)));
      }
    }
    if (l.children) walk(l.children, d + 1);
  }
}
walk(psd.children || [], 0);
