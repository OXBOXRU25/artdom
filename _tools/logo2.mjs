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

const seen = new Uint8Array(W * H); const comps = [];
for (let y = 0; y < H; y++) for (let x = 0; x < W; x++) {
  if (!on(x, y) || seen[y * W + x]) continue;
  const st = [[x, y]]; const pts = []; seen[y * W + x] = 1;
  while (st.length) { const [cx, cy] = st.pop(); pts.push([cx, cy]);
    for (const [dx, dy] of [[1,0],[-1,0],[0,1],[0,-1]]) { const nx=cx+dx, ny=cy+dy;
      if (on(nx,ny) && !seen[ny*W+nx]) { seen[ny*W+nx]=1; st.push([nx,ny]); } } }
  if (pts.length > 12) comps.push(pts);
}
comps.sort((a,b) => Math.min(...a.map(p=>p[1])) - Math.min(...b.map(p=>p[1])));

const fit = (rows) => { // least squares x = a*y + b
  const n = rows.length; let sy=0, sx=0, syy=0, sxy=0;
  for (const [y,x] of rows) { sy+=y; sx+=x; syy+=y*y; sxy+=x*y; }
  const a = (n*sxy - sy*sx) / (n*syy - sy*sy);
  return [a, (sx - a*sy) / n];
};

const paths = [];
comps.forEach((pts, i) => {
  const byRow = new Map();
  for (const [x,y] of pts) { const r = byRow.get(y) || [1e9,-1e9]; byRow.set(y, [Math.min(r[0],x), Math.max(r[1],x)]); }
  const ys = [...byRow.keys()].sort((a,b)=>a-b);
  const y0 = ys[0], y1 = ys[ys.length-1];
  const inner = ys.slice(2, -2);                       // без сглаженных краёв
  const L = fit(inner.map(y => [y, byRow.get(y)[0]]));
  const R = fit(inner.map(y => [y, byRow.get(y)[1] + 1]));
  const at = ([a,b], y) => a*y + b;
  const q = [[at(L,y0), y0], [at(R,y0), y0], [at(R,y1+1), y1+1], [at(L,y1+1), y1+1]];
  const p = 'M' + q.map(([x,y]) => x.toFixed(1) + ' ' + y.toFixed(1)).join('L') + 'Z';
  paths.push(p);
  console.log('  штрих ' + (i+1) + ': наклон лев ' + L[0].toFixed(3) + ' прав ' + R[0].toFixed(3) + '  ' + p);
});
fs.writeFileSync('mark.txt', paths.join('\n'));

const font = opentype.parse(fs.readFileSync(process.env.LOCALAPPDATA + '/Microsoft/Windows/Fonts/Bowler.ttf').buffer);
const SIZE = 100;
const bb = font.getPath('Артдом', 0, 0, SIZE).getBoundingBox();
const sp = font.getPath('Артдом', -bb.x1, -bb.y1, SIZE);
console.log('\nслово "Артдом" Bowler@' + SIZE + ': чернила ' + (bb.x2-bb.x1).toFixed(1) + ' x ' + (bb.y2-bb.y1).toFixed(1) +
  '  (в макете кегль 28 -> ширина ' + ((bb.x2-bb.x1)*28/SIZE).toFixed(1) + ', в PSD слой 141x20)');
fs.writeFileSync('wordmark.txt', '0 0 ' + (bb.x2-bb.x1).toFixed(2) + ' ' + (bb.y2-bb.y1).toFixed(2) + '\n' + sp.toPathData(2));
console.log('  путь: ' + sp.toPathData(2).length + ' символов -> _tools/wordmark.txt');
