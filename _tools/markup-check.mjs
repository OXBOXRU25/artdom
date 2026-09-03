/* Проверка разметки боевых страниц настоящим валидатором (html-validate,
   правила из HTML Living Standard), а не глазами.

   Берём страницы с работающего стенда, а не файлы шаблонов: валидировать
   .php бессмысленно — там нет ни одной целой страницы, а ошибки вложенности
   рождаются как раз на стыке шаблонов.

   Запуск:  node _tools/markup-check.mjs [адрес стенда]
   По умолчанию http://127.0.0.1:8080 — тот же, что поднимает launch.json.
*/
import { HtmlValidate, esmResolver } from 'html-validate';

const base = (process.argv[2] || 'http://127.0.0.1:8080').replace(/\/$/, '');

/* Каждый шаблон темы должен быть представлен хотя бы одной страницей:
   ошибка живёт в шаблоне, а видна только на странице. */
const pages = [
  ['Главная',            '/'],
  ['Каталог объектов',   '/objects/'],
  ['Услуги',             '/services/'],
  ['О компании',         '/about/'],
  ['Отзывы',             '/reviews/'],
  ['Блог — лента',       '/blog/'],
  ['Контакты',           '/contacts/'],
  ['Поиск',              '/?s=дом'],
  ['Страница не найдена','/такой-страницы-нет/'],
];

const validator = new HtmlValidate({
  root: true,
  extends: ['html-validate:recommended'],
  rules: {
    /* Свои отступы в шаблонах WordPress перемешаны с его собственными —
       ругань на них закрыла бы настоящие ошибки. */
    'void-style': 'off',
    'no-trailing-whitespace': 'off',
    'attr-quotes': 'off',
    'no-inline-style': 'off',
  },
});

/* Одну статью и один объект добавляем из ленты: их адреса зависят от посева. */
async function firstLink(path, selector) {
  const html = await (await fetch(base + path)).text();
  const m = html.match(new RegExp(`<a[^>]+class="[^"]*${selector}[^"]*"[^>]+href="([^"]+)"`))
        || html.match(new RegExp(`href="([^"]+)"[^>]*class="[^"]*${selector}[^"]*"`));
  return m ? m[1].replace(base, '') : null;
}

const post = await firstLink('/blog/', 'pcard');
if (post) pages.push(['Блог — статья', post]);
const object = await firstLink('/objects/', 'card');
if (object) pages.push(['Карточка объекта', object]);


/* Осознанные отступления. Не «отключить и забыть»: щуп печатает их
   отдельным списком, чтобы решение оставалось на виду и пересматривалось. */
const known = {
  'no-redundant-role':
    'role="list" на <ul> оставлен намеренно: list-style: none убирает у списка '
    + 'семантику в Safari с VoiceOver, и без роли скринридер не объявит «список из пяти пунктов».',
  'no-autoplay':
    'автозапуск видео на первом экране — против WCAG 2.2.2, ждёт решения заказчика.',
};
let total = 0;
const seenKnown = new Map();

for (const [name, path] of pages) {
  const res = await fetch(base + path);
  const html = await res.text();
  const report = await validator.validateString(html, path);

  const messages = report.results.flatMap((r) => r.messages);
  const real = [];
  for (const m of messages) {
    if (known[m.ruleId]) {
      seenKnown.set(m.ruleId, (seenKnown.get(m.ruleId) || 0) + 1);
    } else {
      real.push(m);
    }
  }
  total += real.length;

  const head = `${name.padEnd(22)} ${String(res.status).padEnd(4)} ${path}`;
  if (!real.length) {
    console.log(`ok   ${head}`);
    continue;
  }
  console.log(`ОШИБ ${head}  — ${real.length}`);
  for (const m of real) {
    console.log(`       ${m.line}:${m.column}  ${m.ruleId}  ${m.message}`);
  }
}

if (seenKnown.size) {
  console.log('\nОсознанные отступления (каждое — решение, а не недосмотр):');
  for (const [rule, count] of seenKnown) {
    console.log(`  ${rule} — ${count} раз: ${known[rule]}`);
  }
}

console.log(total ? `\nНастоящих замечаний: ${total}` : '\nРазметка чистая на всех страницах.');
process.exit(total ? 1 : 0);
