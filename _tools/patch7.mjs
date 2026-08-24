// Карточки объектов: кнопка "Узнать больше" убирается, ссылкой становятся
// заголовок и фотография. Заголовок оборачивается в две копии под перекат.
import fs from 'node:fs';

const f = 'D:/AI/Artdom/static/index.html';
let h = fs.readFileSync(f, 'utf8');
const before = h.length;
const log = [];

// 1. Фотография объекта -> ссылка. Для скринридера прячем: тот же адрес уже
//    есть на заголовке, две одинаковые ссылки подряд ему только мешают.
const mediaFrom = '<div class="card__media" data-rise="shutter">';
const mediaTo = '<a class="card__media" data-rise="shutter" href="#" draggable="false" data-cursor tabindex="-1" aria-hidden="true">';
const nMedia = h.split(mediaFrom).length - 1;
h = h.split(mediaFrom).join(mediaTo);
log.push('фото -> ссылка: ' + nMedia);

// закрывающий тег у медиа стоит в той же строке
h = h.split('\n').map((line) =>
  line.includes('class="card__media"') && line.trimEnd().endsWith('</div>')
    ? line.replace(/<\/div>\s*$/, '</a>')
    : line
).join('\n');

// 2. Заголовок -> ссылка с двумя копиями текста под перекат
let nTitle = 0;
h = h.split('\n').map((line) => {
  const m = line.match(/^(\s*)<h3 class="card__title">(.+)<\/h3>\s*$/);
  if (!m) return line;
  nTitle++;
  const [, pad, text] = m;
  return pad + '<h3 class="card__title"><a class="roll" href="#" draggable="false">' +
         '<span class="roll__a">' + text + '</span>' +
         '<span class="roll__b" aria-hidden="true">' + text + '</span></a></h3>';
}).join('\n');
log.push('заголовков под перекат: ' + nTitle);

// 3. Убираем кнопку из карточек объектов (в отзывах её и не было)
const btnBlock = /\n\s*<a class="btn btn--sm" href="#" draggable="false">\n\s*<span>Узнать больше<\/span>\n\s*<svg class="btn__arrow"[^\n]*\n\s*<\/a>(?=\n\s*<\/article>)/g;
const nBtn = (h.match(btnBlock) || []).length;
h = h.replace(btnBlock, '');
log.push('кнопок в карточках убрано: ' + nBtn);

fs.writeFileSync(f, h, 'utf8');
log.push('размер ' + before + ' -> ' + h.length);

// проверки
const check = fs.readFileSync(f, 'utf8');
log.push('осталось <div class="card__media": ' + (check.split('<div class="card__media"').length - 1) + ' (должно 0)');
log.push('осталось "Узнать больше" всего: ' + (check.split('Узнать больше').length - 1) + ' (услуги 5 + о компании 1 = 6)');
log.push('пар roll__a/roll__b: ' + (check.split('roll__a').length - 1) + ' / ' + (check.split('roll__b').length - 1));
console.log(log.join('\n'));
