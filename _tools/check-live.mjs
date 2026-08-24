// Проверка боевого сайта по адресу: разметка, ассеты, вес, заголовки кеша.
const BASE = process.argv[2] || 'https://artdom.oxboxdigital.ru/';

const t0 = Date.now();
const r = await fetch(BASE, { redirect: 'follow' });
const html = await r.text();
const ttfb = Date.now() - t0;

console.log('=== СТРАНИЦА ===');
console.log('  статус ' + r.status + ', ' + (html.length / 1024).toFixed(1) + ' КБ, ответ за ' + ttfb + ' мс');
console.log('  сервер: ' + (r.headers.get('server') || '-') + ', сжатие: ' + (r.headers.get('content-encoding') || 'НЕТ'));

const has = (re, name) => console.log('  ' + (re.test(html) ? 'да ' : 'НЕТ') + '  ' + name);
console.log('\n=== СЕКЦИИ ===');
has(/hero__title/, 'первый экран');
has(/class="acc"/, 'аккордеон услуг');
has(/class="card"/, 'карточки объектов');
has(/about__quote/, 'о компании');
has(/data-guaranty/, 'закреплённая сцена');
has(/stats__num/, 'цифры');
has(/class="review"/, 'отзывы');
has(/id="form-lead"/, 'форма заявки');
has(/id="form-subscribe"/, 'форма подписки');
has(/logo__mark/, 'логотип в кривых');

const cards = (html.match(/class="card"/g) || []).length;
const revs = (html.match(/class="review"/g) || []).length;
const slides = (html.match(/guaranty__title/g) || []).length;
console.log('\n  карточек объектов: ' + cards + ', отзывов: ' + revs + ', кадров в сцене: ' + slides);

// ассеты
const urls = new Set();
  for (const m of html.matchAll(/(?:src|href)=["']([^"']+\.(?:css|js|woff2|webp|jpg|png|svg|mp4|webm))(?:\?[^"']*)?["']/g)) {
  urls.add(new URL(m[1], BASE).href);
}

console.log('\n=== АССЕТЫ (' + urls.size + ') ===');
let total = html.length, bad = 0;
for (const u of urls) {
  const t = Date.now();
  const a = await fetch(u, { method: 'GET' });
  const buf = await a.arrayBuffer();
  const ms = Date.now() - t;
  total += buf.byteLength;
  const ok = a.status === 200;
  if (!ok) bad++;
  const name = u.split('/').slice(-2).join('/');
  console.log('  ' + (ok ? '   ' : 'БИТО') + ' ' + String(a.status) + '  ' +
    String(Math.round(buf.byteLength / 1024)).padStart(5) + ' КБ  ' + String(ms).padStart(4) + ' мс  ' +
    (a.headers.get('cache-control') || '-').padEnd(22) + name);
}
console.log('\n  битых: ' + bad + ', вес страницы со всем: ' + (total / 1024).toFixed(0) + ' КБ');

// безопасность и мелочи
console.log('\n=== ПРОЧЕЕ ===');
console.log('  HTTPS: ' + (BASE.startsWith('https') ? 'да' : 'нет'));
const rr = await fetch(BASE.replace('https://', 'http://'), { redirect: 'manual' });
console.log('  http -> https: ' + (rr.status >= 300 && rr.status < 400 ? 'да (' + rr.status + ')' : 'НЕТ, статус ' + rr.status));
const wpjson = await fetch(new URL('/wp-json/', BASE).href);
console.log('  REST API открыт: ' + (wpjson.status === 200 ? 'да' : 'нет (' + wpjson.status + ')'));
const xml = await fetch(new URL('/xmlrpc.php', BASE).href, { method: 'POST' });
console.log('  xmlrpc закрыт: ' + (xml.status === 403 ? 'да' : 'НЕТ, статус ' + xml.status));
