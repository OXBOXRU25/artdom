import fs from 'node:fs';
const r = JSON.parse(fs.readFileSync(process.argv[2], 'utf8'));
const pct = (v) => v === null ? '—' : Math.round(v * 100);
console.log('=== ' + (r.configSettings.formFactor || '?') + ' ===');
for (const [k, c] of Object.entries(r.categories)) console.log('  ' + c.title.padEnd(16) + String(pct(c.score)).padStart(4));
console.log('\n--- метрики ---');
for (const id of ['first-contentful-paint', 'largest-contentful-paint', 'total-blocking-time', 'cumulative-layout-shift', 'speed-index']) {
  const a = r.audits[id]; if (a) console.log('  ' + a.title.padEnd(30) + (a.displayValue || '').padStart(10) + '   ' + Math.round((a.score ?? 0) * 100));
}
console.log('\n--- что просело ---');
const bad = Object.values(r.audits).filter((a) => a.score !== null && a.score < 0.95 && a.scoreDisplayMode !== 'informative' && a.scoreDisplayMode !== 'notApplicable');
bad.sort((a, b) => a.score - b.score);
for (const a of bad.slice(0, 18)) {
  const save = a.details && a.details.overallSavingsMs ? '  экономия ' + Math.round(a.details.overallSavingsMs) + 'мс' : '';
  console.log('  ' + String(Math.round(a.score * 100)).padStart(3) + '  ' + a.title.slice(0, 68) + save);
}
