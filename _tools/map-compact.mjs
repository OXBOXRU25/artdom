// Ужимает точечную карту: 28 784 отдельных <circle> в один <path>.
//
// Приём: подпуть нулевой длины со скруглённым концом линии рисуется как
// точка. То есть "m6 0h0" — это целый кружок в шесть байт, тогда как
// <circle cx="627.6" cy="931.3" r="2.2"/> занимает тридцать восемь.
//
// Зачем: nginx на боевом не сжимает image/svg+xml, файл едет как есть, и
// gzip нам не помогает. Значит важен сырой размер, а не сжатый.
//
// Запуск: node map-compact.mjs исходник.svg результат.svg [цвет]
import fs from 'node:fs';

const [src, dst, color = '#1b1d22'] = process.argv.slice(2);
if (!src || !dst) {
  console.log('нужно: node map-compact.mjs исходник.svg результат.svg [цвет]');
  process.exit(1);
}

const s = fs.readFileSync(src, 'utf8');

const vb = s.match(/viewBox="0 0 ([\d.]+) ([\d.]+)"/);
if (!vb) { console.log('не нашёл viewBox'); process.exit(1); }
const W = parseFloat(vb[1]);
const H = parseFloat(vb[2]);

const dots = [...s.matchAll(/cx="([\d.-]+)"\s+cy="([\d.-]+)"(?:\s+r="([\d.]+)")?/g)]
  .map((m) => [parseFloat(m[1]), parseFloat(m[2]), m[3] ? parseFloat(m[3]) : null]);

if (!dots.length) { console.log('не нашёл ни одного кружка'); process.exit(1); }

const r0 = dots.find((d) => d[2] !== null);
const radius = r0 ? r0[2] : 2.2;

/* Холст ужимаем вдвое и округляем координаты до целых. При показе около
   1000px это смещает точку максимум на четверть пикселя — незаметно, зато
   числа становятся вдвое короче. */
const K = 0.5;
const scaled = dots.map(([x, y]) => [Math.round(x * K), Math.round(y * K)]);

/* Сортировка по строкам: тогда почти все переходы получаются "m<dx> 0",
   то есть без второго числа длиннее одного знака. */
scaled.sort((a, b) => (a[1] - b[1]) || (a[0] - b[0]));

let d = '';
let px = 0;
let py = 0;
scaled.forEach(([x, y], i) => {
  if (i === 0) {
    d += 'M' + x + ' ' + y + 'h0';
  } else {
    const dx = x - px;
    const dy = y - py;
    d += 'm' + dx + ' ' + dy + 'h0';
  }
  px = x;
  py = y;
});

const out =
  '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' +
  Math.round(W * K) + ' ' + Math.round(H * K) + '">' +
  '<path fill="none" stroke="' + color + '" stroke-width="' + (radius * 2 * K) +
  '" stroke-linecap="round" d="' + d + '"/></svg>';

fs.writeFileSync(dst, out);

const было = Buffer.byteLength(s) / 1024;
const стало = Buffer.byteLength(out) / 1024;
console.log('точек: ' + dots.length + ', радиус: ' + radius);
console.log('было:  ' + было.toFixed(0) + ' КБ');
console.log('стало: ' + стало.toFixed(0) + ' КБ  (в ' + (было / стало).toFixed(1) + ' раза меньше)');
