// Радиус КАЖДОГО из четырёх углов отдельно: по смещению края внутрь от угла.
import { createRequire } from 'node:module';
import fs from 'node:fs';
const require = createRequire(import.meta.url);
const { createCanvas, loadImage } = require('@napi-rs/canvas');

for (const file of process.argv.slice(2)) {
  const img = await loadImage(fs.readFileSync(file));
  const W = img.width, H = img.height;
  const cv = createCanvas(W, H); const c = cv.getContext('2d');
  c.drawImage(img, 0, 0);
  const d = c.getImageData(0, 0, W, H).data;
  const on = (x, y) => d[(y * W + x) * 4 + 3] > 127;

  const scan = (name, rows, dir) => {
    const edges = [];
    for (const y of rows) {
      let k = 0;
      if (dir === 'left')  { let x = 0;     while (x < W && !on(x, y)) { x++; k++; } }
      else                 { let x = W - 1; while (x >= 0 && !on(x, y)) { x--; k++; } }
      edges.push(k);
    }
    const base = Math.min(...edges);
    let r = 0; while (r < edges.length && edges[r] > base) r++;
    return name + ' R=' + r + ' [' + edges.slice(0, 14).join(',') + ']';
  };

  const top = Array.from({ length: Math.min(20, H) }, (_, i) => i);
  const bot = Array.from({ length: Math.min(20, H) }, (_, i) => H - 1 - i);
  console.log(file.split('/').pop() + '  ' + W + 'x' + H);
  console.log('   ' + scan('лев-верх ', top, 'left'));
  console.log('   ' + scan('прав-верх', top, 'right'));
  console.log('   ' + scan('лев-низ  ', bot, 'left'));
  console.log('   ' + scan('прав-низ ', bot, 'right'));
}
