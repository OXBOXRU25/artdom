// Щуп ссылок: куда они ведут и живы ли адреса.
//
//   node _tools/links-check.mjs [адрес]
//
// Две проверки, обе на КЛАСС, а не на перечисленные заглушки:
//
//   1. Кнопка-ссылка, ведущая на ЯКОРЬ (href начинается с #), — подозрение.
//      Кнопка призыва должна открывать раздел или модалку; якорь на той же
//      странице почти всегда остаток посева. Модалки исключены по признаку
//      data-form-open, а не по имени.
//   2. Любая внутренняя ссылка проверяется запросом: не 404 ли.
//
// Почему именно так. 13.09.2026 я искал мёртвые кнопки, перечислив заглушки
// поимённо («#», «», «#objects»), — и щуп сказал «нет», пока заказчик
// показывал кнопку с href="#contacts". Перечисление исключений по памяти и
// есть признак частного отбора.
const BASE = (process.argv[2] || 'https://artdom.oxboxdigital.ru').replace(/\/$/, '');

const PAGES = [
  ['Главная', '/'],
  ['Объекты', '/objects/'],
  ['Услуги', '/services/'],
  ['О компании', '/about/'],
  ['Контакты', '/contacts/'],
  ['Блог', '/blog/'],
  ['Отзывы', '/reviews/'],
];

const тег = /<a\b[^>]*>/gi;
const атр = (t, имя) => {
  const m = t.match(new RegExp(имя + '="([^"]*)"', 'i'));
  return m ? m[1] : null;
};

const якорные = [];
const адреса = new Map();   // адрес -> где встретился

for (const [имя, path] of PAGES) {
  const html = await (await fetch(BASE + path, { cache: 'no-store' })).text();
  const теги = html.match(тег) || [];
  for (const t of теги) {
    const href = атр(t, 'href');
    if (href === null) continue;
    const кнопка = /class="[^"]*\bbtn\b/.test(t);
    const модалка = /data-form-open/.test(t);

    if (кнопка && !модалка && (href === '' || href.startsWith('#'))) {
      /* Текст кнопки лежит за тегом — вытаскиваем кусок после него. */
      const i = html.indexOf(t);
      const хвост = html.slice(i + t.length, i + t.length + 220)
        .replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim().slice(0, 30);
      якорные.push({ стр: имя, href: href || '(пусто)', текст: хвост });
    }

    if (/^https?:\/\//i.test(href) && !href.startsWith(BASE)) continue;   // чужие не трогаем
    if (href.startsWith('#') || href.startsWith('mailto:') || href.startsWith('tel:') || href === '') continue;
    const полный = href.startsWith('http') ? href : BASE + (href.startsWith('/') ? href : '/' + href);
    if (!адреса.has(полный)) адреса.set(полный, имя);
  }
}

console.log('=== КНОПКИ, ВЕДУЩИЕ НА ЯКОРЬ (не модалки) ===');
if (!якорные.length) {
  console.log('  нет — все кнопки открывают раздел или окно');
} else {
  for (const я of якорные) {
    console.log('  ' + я.стр.padEnd(12) + я.href.padEnd(14) + '«' + я.текст + '»');
  }
}

console.log('\n=== ПРОВЕРКА АДРЕСОВ (' + адреса.size + ' штук) ===');
let битых = 0;
for (const [url, где] of адреса) {
  const r = await fetch(url, { method: 'GET', cache: 'no-store', redirect: 'follow' });
  if (r.status >= 400) {
    битых++;
    console.log('  ' + r.status + '  ' + url.replace(BASE, '') + '   (со страницы: ' + где + ')');
  }
}
if (!битых) console.log('  битых нет');

console.log('\nИТОГО: кнопок на якорь ' + якорные.length + ', битых адресов ' + битых);
