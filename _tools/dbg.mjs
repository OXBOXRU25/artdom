import { createRequire } from 'node:module';
import fs from 'node:fs';
const require = createRequire(import.meta.url);
const opentype = require('opentype.js');
const raw = fs.readFileSync(process.env.LOCALAPPDATA + '/Microsoft/Windows/Fonts/Bowler.ttf');
const font = opentype.parse(raw.buffer.slice(raw.byteOffset, raw.byteOffset + raw.byteLength));
const p = font.getPath('Артдом', 0, 0, 100);
let bad = 0;
p.commands.forEach((c, i) => {
  const nums = ['x','y','x1','y1','x2','y2'].filter(k => k in c).map(k => [k, c[k]]);
  if (nums.some(([,v]) => !Number.isFinite(v))) {
    bad++;
    if (bad <= 6) console.log('команда #' + i + ' ' + c.type + '  ' + nums.map(([k,v]) => k + '=' + v).join(' '));
  }
});
console.log('команд всего: ' + p.commands.length + ', с NaN: ' + bad);
console.log('версия opentype.js: ' + require('opentype.js/package.json').version);
