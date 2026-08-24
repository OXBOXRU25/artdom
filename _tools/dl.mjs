import fs from 'node:fs';
const jobs = [
  ['https://ru.wordpress.org/latest-ru_RU.zip', 'D:/AI/Artdom/_dl/wordpress.zip'],
  ['https://downloads.wordpress.org/plugin/sqlite-database-integration.zip', 'D:/AI/Artdom/_dl/sqlite.zip'],
];
for (const [url, out] of jobs) {
  const t0 = Date.now();
  const r = await fetch(url, { redirect: 'follow' });
  if (!r.ok) { console.log('ОШИБКА ' + r.status + '  ' + url); continue; }
  const buf = Buffer.from(await r.arrayBuffer());
  fs.writeFileSync(out, buf);
  console.log(out.split('/').pop().padEnd(18) + Math.round(buf.length / 1024 / 1024 * 10) / 10 + ' МБ   за ' + ((Date.now() - t0) / 1000).toFixed(1) + 'с');
}
