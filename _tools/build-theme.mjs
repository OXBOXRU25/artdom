// Переносит статику в шаблоны темы: шапку, подвал и секции главной.
// Разрезаем по тем же границам, что видны в разметке, чтобы ничего не потерять,
// и подменяем пути к ассетам на функции WordPress.
import fs from 'node:fs';

const SRC = 'D:/AI/Artdom/static/index.html';
const T = 'D:/AI/Artdom/theme/artdom';
const html = fs.readFileSync(SRC, 'utf8').split('\n');

const at = (needle, from = 0) => {
  const i = html.findIndex((l, n) => n >= from && l.includes(needle));
  if (i < 0) throw new Error('не найдено: ' + needle);
  return i;
};

const slice = (a, b) => html.slice(a, b).join('\n');

/* пути к ассетам -> функции темы */
const uri = (s) => s
  .replace(/(src|href)="(img|video|fonts)\//g, '$1="<?php echo esc_url( $u ); ?>/$2/')
  .replace(/srcset="(img|video)\//g, 'srcset="<?php echo esc_url( $u ); ?>/$1/');

const iSprite = at('<svg class="vh"');
const iSpriteEnd = at('</svg>', iSprite);
const iHdr = at('<header class="hdr"');
const iHdrEnd = at('</header>');
const iMenu = at('<div class="menu"');
const iMain = at('<main>');
const iMainEnd = at('</main>');
const iFtr = at('<footer class="ftr"');
const iFtrEnd = at('</footer>');
const iCursor = at('<div class="cursor"');

const head = [
  '<?php',
  '/**',
  ' * Шапка документа и сайта.',
  ' *',
  ' * viewport без maximum-scale: в базе oxboxwise он был и запрещал щипковое',
  ' * увеличение — провал доступности и замечание Lighthouse.',
  ' * Короткие теги <? из базы тоже убраны: при выключенном short_open_tag',
  ' * они вываливаются на страницу сырым текстом.',
  ' *',
  ' * @package artdom',
  ' */',
  '',
  '$u = get_template_directory_uri();',
  '?>',
  '<!DOCTYPE html>',
  '<html <?php language_attributes(); ?>>',
  '<head>',
  '<meta charset="<?php bloginfo( \'charset\' ); ?>">',
  '<meta name="viewport" content="width=device-width, initial-scale=1">',
  '<?php wp_head(); ?>',
  '</head>',
  '',
  '<body <?php body_class(); ?>>',
  '<?php wp_body_open(); ?>',
  '',
  slice(iSprite - 1, iSpriteEnd + 1),
  '',
  uri(slice(iHdr - 1, iHdrEnd + 1)),
  '',
  uri(slice(iMenu - 1, iMenu + (at('</div>', iMenu) - iMenu) + 1)),
  '',
].join('\n');

fs.writeFileSync(T + '/header.php', head, 'utf8');
console.log('header.php: ' + head.split('\n').length + ' строк');

const foot = [
  '<?php',
  '/**',
  ' * Подвал сайта и закрытие документа.',
  ' *',
  ' * @package artdom',
  ' */',
  '',
  '$u = get_template_directory_uri();',
  '?>',
  '',
  uri(slice(iFtr - 1, iFtrEnd + 1)),
  '',
  slice(iCursor, iCursor + 1),
  '',
  '<?php wp_footer(); ?>',
  '</body>',
  '</html>',
].join('\n');

fs.writeFileSync(T + '/footer.php', foot, 'utf8');
console.log('footer.php: ' + foot.split('\n').length + ' строк');

/* Секции главной — каждая своим файлом в template-parts */
const marks = [];
for (let n = iMain; n < iMainEnd; n++) {
  const m = html[n].match(/<!-- =+ (.+?) =+/);
  if (m) marks.push([n, m[1].trim()]);
}
const names = ['hero', 'services', 'objects', 'about', 'guaranty', 'stats', 'reviews'];
fs.mkdirSync(T + '/template-parts/main', { recursive: true });

marks.forEach(([line, title], i) => {
  const end = i + 1 < marks.length ? marks[i + 1][0] : iMainEnd;
  const body = uri(slice(line, end)).replace(/\s+$/, '');
  const file = T + '/template-parts/main/' + names[i] + '.php';
  fs.writeFileSync(file,
    '<?php\n/**\n * Секция: ' + title.replace(/-+$/, '').trim() + '\n *\n * @package artdom\n */\n\n' +
    "$u = get_template_directory_uri();\n?>\n" + body + '\n', 'utf8');
  console.log('  ' + names[i] + '.php  <- ' + title.slice(0, 42));
});

const tpl = [
  '<?php',
  '/**',
  ' * Template Name: Главная',
  ' * Template Post Type: page',
  ' *',
  ' * @package artdom',
  ' */',
  '',
  'get_header();',
  '?>',
  '',
  '<main>',
  ...names.map((n) => "\t<?php get_template_part( 'template-parts/main/" + n + "' ); ?>"),
  '</main>',
  '',
  '<?php',
  'get_footer();',
].join('\n');

fs.mkdirSync(T + '/templates', { recursive: true });
fs.writeFileSync(T + '/templates/template-mainpage.php', tpl, 'utf8');
console.log('template-mainpage.php: ' + names.length + ' секций');
