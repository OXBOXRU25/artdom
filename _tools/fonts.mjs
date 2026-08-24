// Подрезает системные Inter под нужный набор символов и кладёт woff2 в проект.
// Берём ровно те начертания и оптические размеры, что стоят в макете.
import { createRequire } from 'node:module';
import fs from 'node:fs';
const require = createRequire(import.meta.url);
const subsetFont = require('subset-font');

const SRC = process.env.LOCALAPPDATA + '/Microsoft/Windows/Fonts/';
const OUT = 'D:/AI/Artdom/static/fonts';
fs.mkdirSync(OUT, { recursive: true });

// набор символов: ASCII + кириллица + типографика, которая реально нужна
let chars = '';
for (let c = 0x20; c <= 0x7e; c++) chars += String.fromCharCode(c);
for (let c = 0x410; c <= 0x44f; c++) chars += String.fromCharCode(c);
chars += 'ЁёЇїІіЄєҐґ';
chars += '\u00a0\u00ab\u00bb\u00b0\u00b2\u00b3\u00b7\u00d7\u2010\u2011\u2013\u2014\u2018\u2019\u201c\u201d\u201e\u2026\u2116\u20bd\u20ac\u00a9\u00ae\u2122\u2605\u2606\u2713\u2192\u2191\u2193\u2190\u002b\u2212';

const jobs = [
  ['Inter_18pt-Regular.ttf',  'inter18-400'],
  ['Inter_18pt-Medium.ttf',   'inter18-500'],
  ['Inter_18pt-SemiBold.ttf', 'inter18-600'],
  ['Inter_28pt-Regular.ttf',  'inter28-400'],
];

for (const [file, name] of jobs) {
  const src = fs.readFileSync(SRC + file);
  const out = await subsetFont(src, chars, { targetFormat: 'woff2' });
  fs.writeFileSync(OUT + '/' + name + '.woff2', out);
  console.log(name.padEnd(14) + Math.round(src.length / 1024) + ' КБ ttf  ->  ' + Math.round(out.length / 1024) + ' КБ woff2');
}
