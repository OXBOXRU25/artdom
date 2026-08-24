// Обводит знак логотипа из logo.png в SVG.
// Хитрость: сначала увеличиваем в K раз со сглаживанием. Полупрозрачные пиксели
// по краю несут субпиксельную информацию о положении грани, и после увеличения
// порог по альфе даёт куда более точный контур, чем обводка по сетке 1:1.
import { createRequire } from 'node:module';
import fs from 'node:fs';
const require = createRequire(import.meta.url);
const { createCanvas, loadImage, Path2D } = require('@napi-rs/canvas');

const K = 8;
const MARK_W = 38, MARK_H = 40;
const EPS = Number(process.env.EPS || 3.0);   // в увеличенных пикселях

const png = await loadImage(fs.readFileSync('D:/AI/Artdom/logo.png'));
const W = MARK_W * K, H = MARK_H * K;
const cv = createCanvas(W, H);
const ctx = cv.getContext('2d');
ctx.imageSmoothingEnabled = true;
ctx.imageSmoothingQuality = 'high';
ctx.drawImage(png, 0, 0, MARK_W, MARK_H, 0, 0, W, H);
const d = ctx.getImageData(0, 0, W, H).data;
const on = (x, y) => x >= 0 && y >= 0 && x < W && y < H && d[(y * W + x) * 4 + 3] > 127;

// связные компоненты
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
  if (pts.length > K * K * 2) comps.push(pts);
}
comps.sort((a, b) => Math.min(...a.map(p => p[1])) - Math.min(...b.map(p => p[1])));

const perp = (p, a, b) => {
  const [x, y] = p, [x1, y1] = a, [x2, y2] = b;
  const dx = x2 - x1, dy = y2 - y1, L = Math.hypot(dx, dy);
  return L === 0 ? Math.hypot(x - x1, y - y1) : Math.abs(dy * x - dx * y + x2 * y1 - y2 * x1) / L;
};
const rdp = (pts, eps) => {
  if (pts.length < 3) return pts;
  let mi = 0, md = 0;
  for (let i = 1; i < pts.length - 1; i++) { const dd = perp(pts[i], pts[0], pts[pts.length - 1]); if (dd > md) { md = dd; mi = i; } }
  return md > eps ? [...rdp(pts.slice(0, mi + 1), eps).slice(0, -1), ...rdp(pts.slice(mi), eps)] : [pts[0], pts[pts.length - 1]];
};

const paths = [];
comps.forEach((pts, i) => {
  const rows = new Map();
  for (const [x, y] of pts) { const r = rows.get(y) || [1e9, -1e9]; rows.set(y, [Math.min(r[0], x), Math.max(r[1], x)]); }
  const ys = [...rows.keys()].sort((a, b) => a - b);
  const left = ys.map(y => [rows.get(y)[0], y]);
  const right = ys.slice().reverse().map(y => [rows.get(y)[1] + 1, y + 1]);
  const poly = rdp([...left, ...right, left[0]], EPS);
  const p = 'M' + poly.slice(0, -1).map(([x, y]) => (x / K).toFixed(2) + ' ' + (y / K).toFixed(2)).join('L') + 'Z';
  paths.push(p);
  console.log('  штрих ' + (i + 1) + ': точек ' + (poly.length - 1));
});

// проверка утверждением: рисуем обводку обратно и считаем расхождение с альфой
const back = createCanvas(W, H); const bc = back.getContext('2d');
bc.scale(K, K);
bc.fillStyle = '#000';
for (const p of paths) bc.fill(new Path2D(p));
const b = bc.getImageData(0, 0, W, H).data;
let ink = 0, diff = 0;
for (let i = 0; i < W * H; i++) {
  const oa = d[i * 4 + 3] > 127, ob = b[i * 4 + 3] > 127;
  if (oa) ink++;
  if (oa !== ob) diff++;
}
console.log('расхождение: ' + (diff / ink * 100).toFixed(2) + '% от площади знака (порог ' + EPS + ')');
fs.writeFileSync('D:/AI/Artdom/_tools/mark.txt', paths.join('\n'));
