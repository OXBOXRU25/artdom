// Читает PSD и печатает структуру: дерево слоёв, боксы, текст, шрифты, цвета, эффекты.
// Полный дамп кладёт в JSON рядом. Пиксели не трогаем — только метаданные, поэтому быстро.
import { createRequire } from 'node:module';
import fs from 'node:fs';
import path from 'node:path';

const require = createRequire(import.meta.url);
const { readPsd } = require('ag-psd');

const src = process.argv[2];
const buf = fs.readFileSync(src);
const psd = readPsd(buf, {
  skipLayerImageData: true,
  skipCompositeImageData: true,
  skipThumbnail: true,
  logMissingFeatures: false,
});

const hex = (c) => {
  if (!c) return null;
  const v = (n) => Math.round(n).toString(16).padStart(2, '0');
  if ('r' in c) return '#' + v(c.r) + v(c.g) + v(c.b);
  return JSON.stringify(c);
};

const lines = [];
const fonts = new Map();
const colors = new Map();
const texts = [];

const bump = (map, key) => { if (key) map.set(key, (map.get(key) || 0) + 1); };

function walk(layers, depth, parentPath) {
  for (const l of layers) {
    const pad = '  '.repeat(depth);
    const w = (l.right ?? 0) - (l.left ?? 0);
    const h = (l.bottom ?? 0) - (l.top ?? 0);
    const full = parentPath ? parentPath + ' / ' + l.name : l.name;
    const flags = [];
    if (l.hidden) flags.push('СКРЫТ');
    if (l.opacity !== undefined && l.opacity < 1) flags.push('opacity ' + Math.round(l.opacity * 100) + '%');
    if (l.blendMode && l.blendMode !== 'normal') flags.push('blend ' + l.blendMode);
    if (l.clipping) flags.push('clip');
    if (l.mask) flags.push('маска');

    let kind = 'растр';
    if (l.children) kind = 'ГРУППА';
    else if (l.text) kind = 'ТЕКСТ';
    else if (l.vectorFill || l.vectorStroke || l.vectorMask) kind = 'вектор';
    else if (l.adjustment) kind = 'коррекция';

    let head = pad + '[' + kind + '] ' + l.name;
    if (!l.children) head += '  @ ' + l.left + ',' + l.top + '  ' + w + 'x' + h;
    if (flags.length) head += '  (' + flags.join(', ') + ')';
    lines.push(head);

    if (l.text) {
      const st = l.text.style || {};
      const runs = l.text.styleRuns || [];
      const fname = st.font?.name;
      bump(fonts, fname);
      bump(colors, hex(st.fillColor));
      const size = st.fontSize;
      const lead = st.autoLeading ? 'auto' : st.leading;
      const trk = st.tracking;
      lines.push(pad + '   текст: ' + JSON.stringify((l.text.text || '').slice(0, 160)));
      lines.push(pad + '   шрифт: ' + fname + '  кегль ' + size + '  интерл ' + lead + '  трекинг ' + trk + '  цвет ' + hex(st.fillColor));
      if (runs.length > 1) {
        lines.push(pad + '   прогонов стиля: ' + runs.length);
        for (const r of runs) {
          const s = r.style || {};
          if (s.font?.name) bump(fonts, s.font.name);
          if (s.fillColor) bump(colors, hex(s.fillColor));
          lines.push(pad + '     len ' + r.length + ': ' + (s.font?.name ?? '=') + ' ' + (s.fontSize ?? '=') + ' ' + (s.fillColor ? hex(s.fillColor) : '='));
        }
      }
      texts.push({ layer: full, text: l.text.text, font: fname, size, leading: lead, tracking: trk, color: hex(st.fillColor), left: l.left, top: l.top, width: w, height: h });
    }

    if (l.vectorFill?.color) { bump(colors, hex(l.vectorFill.color)); lines.push(pad + '   заливка: ' + hex(l.vectorFill.color)); }
    if (l.vectorFill?.type) lines.push(pad + '   заливка тип: ' + l.vectorFill.type);
    if (l.vectorStroke) lines.push(pad + '   обводка: ' + JSON.stringify(l.vectorStroke.lineWidth ?? '') + ' ' + hex(l.vectorStroke.fillColor?.color));

    if (l.effects && !l.effects.disabled) {
      const e = l.effects;
      const parts = [];
      if (e.dropShadow) for (const d of e.dropShadow) parts.push('тень ' + hex(d.color) + ' d' + (d.distance?.value ?? 0) + ' b' + (d.size?.value ?? 0) + ' op' + Math.round((d.opacity ?? 1) * 100));
      if (e.innerShadow) for (const d of e.innerShadow) parts.push('внутр.тень ' + hex(d.color));
      if (e.stroke) for (const d of e.stroke) parts.push('обводка ' + (d.size?.value ?? '') + ' ' + hex(d.color));
      if (e.solidFill) for (const d of e.solidFill) parts.push('наложение цвета ' + hex(d.color));
      if (e.gradientOverlay) for (const d of e.gradientOverlay) {
        const stops = (d.gradient?.colorStops || []).map((s) => hex(s.color) + '@' + Math.round((s.location / 4096) * 100) + '%');
        parts.push('градиент [' + stops.join(', ') + '] угол ' + (d.angle?.value ?? ''));
      }
      if (e.outerGlow) parts.push('внешнее свечение ' + hex(e.outerGlow.color));
      if (parts.length) lines.push(pad + '   эффекты: ' + parts.join(' | '));
    }

    if (l.children) walk(l.children, depth + 1, full);
  }
}

lines.push('ХОЛСТ: ' + psd.width + ' x ' + psd.height + '  bits ' + psd.bitsPerChannel + '  режим ' + psd.colorMode);
lines.push('слоёв верхнего уровня: ' + (psd.children?.length ?? 0));
lines.push('');
walk(psd.children || [], 0, '');

lines.push('');
lines.push('=== ШРИФТЫ ===');
for (const [k, v] of [...fonts.entries()].sort((a, b) => b[1] - a[1])) lines.push('  ' + k + '  x' + v);
lines.push('');
lines.push('=== ЦВЕТА ТЕКСТА И ЗАЛИВОК ===');
for (const [k, v] of [...colors.entries()].sort((a, b) => b[1] - a[1])) lines.push('  ' + k + '  x' + v);

const out = lines.join('\n');
console.log(out);

const dir = path.dirname(src);
fs.writeFileSync(path.join(dir, '_tools', 'psd-structure.txt'), out, 'utf8');
fs.writeFileSync(path.join(dir, '_tools', 'psd-texts.json'), JSON.stringify(texts, null, 2), 'utf8');
