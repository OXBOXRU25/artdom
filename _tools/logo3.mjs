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
const on = (x, y) => x>=0 && y>=0 && x<W && y<H && d[(y*W+x)*4+3] > 128;

const seen = new Uint8Array(W*H); const comps = [];
for (let y=0;y<H;y++) for (let x=0;x<W;x++) {
  if (!on(x,y) || seen[y*W+x]) continue;
  const st=[[x,y]]; const pts=[]; seen[y*W+x]=1;
  while (st.length) { const [cx,cy]=st.pop(); pts.push([cx,cy]);
    for (const [dx,dy] of [[1,0],[-1,0],[0,1],[0,-1]]) { const nx=cx+dx, ny=cy+dy;
      if (on(nx,ny) && !seen[ny*W+nx]) { seen[ny*W+nx]=1; st.push([nx,ny]); } } }
  if (pts.length>12) comps.push(pts);
}
comps.sort((a,b)=>Math.min(...a.map(p=>p[1]))-Math.min(...b.map(p=>p[1])));

const perp = (p,a,b) => { const [x,y]=p,[x1,y1]=a,[x2,y2]=b;
  const dx=x2-x1, dy=y2-y1, L=Math.hypot(dx,dy);
  return L===0 ? Math.hypot(x-x1,y-y1) : Math.abs(dy*x-dx*y+x2*y1-y2*x1)/L; };
const rdp = (pts,eps) => { if (pts.length<3) return pts;
  let mi=0, md=0;
  for (let i=1;i<pts.length-1;i++){ const dd=perp(pts[i],pts[0],pts[pts.length-1]); if(dd>md){md=dd;mi=i;} }
  return md>eps ? [...rdp(pts.slice(0,mi+1),eps).slice(0,-1), ...rdp(pts.slice(mi),eps)] : [pts[0],pts[pts.length-1]]; };

const out = [];
comps.forEach((pts,i)=>{
  const rows=new Map();
  for (const [x,y] of pts){ const r=rows.get(y)||[1e9,-1e9]; rows.set(y,[Math.min(r[0],x),Math.max(r[1],x)]); }
  const ys=[...rows.keys()].sort((a,b)=>a-b);
  const left = ys.map(y=>[rows.get(y)[0], y]);
  const right = ys.slice().reverse().map(y=>[rows.get(y)[1]+1, y+1]);
  const poly = rdp([...left, ...right, left[0]], Number(process.env.EPS||0.6));
  const p = 'M'+poly.slice(0,-1).map(([x,y])=>(+x).toFixed(1)+' '+(+y).toFixed(1)).join('L')+'Z';
  out.push(p);
  console.log('  штрих '+(i+1)+': точек '+(poly.length-1)+'  '+p);
});
fs.writeFileSync('mark.txt', out.join('\n'));

const raw = fs.readFileSync(process.env.LOCALAPPDATA + '/Microsoft/Windows/Fonts/Bowler.ttf');
const font = opentype.parse(raw.buffer.slice(raw.byteOffset, raw.byteOffset + raw.byteLength));
const SIZE = 100;
const bb = font.getPath('Артдом', 0, 0, SIZE).getBoundingBox();
const sp = font.getPath('Артдом', -bb.x1, -bb.y1, SIZE);
const w = bb.x2-bb.x1, h = bb.y2-bb.y1;
console.log('\nслово "Артдом" Bowler@100: чернила '+w.toFixed(1)+' x '+h.toFixed(1));
console.log('  при кегле 28 -> '+(w*0.28).toFixed(1)+' x '+(h*0.28).toFixed(1)+'   (слой в PSD: 141x20)');
fs.writeFileSync('wordmark.txt', '0 0 '+w.toFixed(2)+' '+h.toFixed(2)+'\n'+sp.toPathData(2));
console.log('  путь записан, '+sp.toPathData(2).length+' символов');
