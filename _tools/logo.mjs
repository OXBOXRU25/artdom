// 1) Разбирает знак логотипа на связные фигуры и находит углы каждой (это четырёхугольники).
// 2) Выводит слово "Артдом" шрифтом Bowler в кривые — чтобы шрифт на сайт не тащить.
import { createRequire } from 'node:module';
import fs from 'node:fs';
const require = createRequire(import.meta.url);
const { createCanvas, loadImage } = require('@napi-rs/canvas');
const opentype = require('opentype.js');

const img = await loadImage(fs.readFileSync(process.argv[2]));
const W = img.width, H = img.height;
const cv = createCanvas(W, H); const ctx = cv.getContext('2d');
ctx.drawImage(img, 0, 0);
const d = ctx.getImageData(0, 0, W, H).data;
const on = (x, y) => x >= 0 && y >= 0 && x < W && y < H && d[(y * W + x) * 4 + 3] > 128;

const seen = new Uint8Array(W * H);
const comps = [];
for (let y = 0; y < H; y++) for (let x = 0; x < W; x++) {
  if (!on(x, y) || seen[y * W + x]) continue;
  const st = [[x, y]]; const pts = []; seen[y * W + x] = 1;
  while (st.length) {
    const [cx, cy] = st.pop(); pts.push([cx, cy]);
    for (const [dx, dy] of [[1,0],[-1,0],[0,1],[0,-1]]) {
      const nx = cx + dx, ny = cy + dy;
      if (on(nx, ny) && !seen[ny * W + nx]) { seen[ny * W + nx] = 1; st.push([nx, ny]); }
    }
  }
  if (pts.length > 12) comps.push(pts);
}

console.log('знак ' + W + 'x' + H + ', фигур: ' + comps.length);
comps.sort((a, b) => Math.min(...a.map(p=>p[1])) - Math.min(...b.map(p=>p[1])));
comps.forEach((pts, i) => {
  const best = (f) => pts.reduce((a, p) => f(p) > f(a) ? p : a);
  const c = [best(p=>-(p[0]+p[1])), best(p=>p[0]-p[1]), best(p=>p[0]+p[1]), best(p=>-(p[0]-p[1]))];
  const xs = pts.map(p=>p[0]), ys = pts.map(p=>p[1]);
  console.log('  #' + (i+1) + '  пикселей ' + String(pts.length).padStart(4) +
    '  бокс ' + Math.min(...xs) + ',' + Math.min(...ys) + ' ' + (Math.max(...xs)-Math.min(...xs)+1) + 'x' + (Math.max(...ys)-Math.min(...ys)+1) +
    '  углы ' + c.map(p=>p[0]+','+p[1]).join(' '));
  console.log('       path: M' + c.map(p=>p[0]+' '+p[1]).join(' L') + ' Z');
});

// --- слово ---
const font = opentype.loadSync(process.env.LOCALAPPDATA + '/Microsoft/Windows/Fonts/Bowler.ttf');
const SIZE = 100;
const path = font.getPath('Артдом', 0, 0, SIZE);
const bb = path.getBoundingBox();
console.log('\nслово "Артдом" в Bowler при кегле ' + SIZE);
console.log('  бокс чернил: x ' + bb.x1.toFixed(1) + '..' + bb.x2.toFixed(1) + '  y ' + bb.y1.toFixed(1) + '..' + bb.y2.toFixed(1));
console.log('  ширина ' + (bb.x2-bb.x1).toFixed(1) + '  высота ' + (bb.y2-bb.y1).toFixed(1) + '  advance ' + font.getAdvanceWidth('Артдом', SIZE).toFixed(1));
const shifted = font.getPath('Артдом', -bb.x1, -bb.y1, SIZE);
fs.writeFileSync('D:/AI/Artdom/_tools/wordmark.txt',
  'viewBox="0 0 ' + (bb.x2-bb.x1).toFixed(2) + ' ' + (bb.y2-bb.y1).toFixed(2) + '"\n' + shifted.toPathData(2));
console.log('  путь записан в _tools/wordmark.txt (' + shifted.toPathData(2).length + ' символов)');
