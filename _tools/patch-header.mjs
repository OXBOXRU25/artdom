// Шапка: зашитые ссылки меняем на меню WordPress, телефон — на поле настроек.
import fs from 'node:fs';

const f = 'D:/AI/Artdom/theme/artdom/header.php';
const src = fs.readFileSync(f, 'utf8').split('\n');

const nav = (loc, cls, label) => [
  "      <?php",
  "      wp_nav_menu(",
  "        array(",
  "          'theme_location'  => '" + loc + "',",
  "          'container'       => 'nav',",
  "          'container_class' => '" + cls + "',",
  "          'container_aria_label' => '" + label + "',",
  "          'menu_class'      => '',",
  "          'items_wrap'      => '%3$s',",
  "          'depth'           => 1,",
  "          'fallback_cb'     => false,",
  "        )",
  "      );",
  "      ?>",
].join('\n');

const tel = (cls, pad) =>
  pad + '<?php $artdom_phone = artdom_field( \'opt_phone\', true ); ?>\n' +
  pad + '<a class="' + cls + '" href="tel:<?php echo esc_attr( artdom_tel( $artdom_phone ) ); ?>">' +
  '<?php echo esc_html( str_replace( \' \', "\\u{00a0}", $artdom_phone ) ); ?></a>';

/* верхнее меню: строки 46..51 (1-based) */
const navStart = src.findIndex((l) => l.includes('<nav class="nav"'));
const navEnd = src.findIndex((l, i) => i > navStart && l.includes('</nav>'));
const telLine = src.findIndex((l) => l.includes('hdr__tel'));

/* меню на телефоне */
const mStart = src.findIndex((l) => l.includes('class="menu"'));
const mFirst = src.findIndex((l, i) => i > mStart && l.includes('href="#about"'));
const mTel = src.findIndex((l, i) => i > mStart && l.includes('href="tel:'));

if ([navStart, navEnd, telLine, mStart, mFirst, mTel].some((i) => i < 0)) {
  console.log('не нашёл нужные блоки'); process.exit(1);
}

const out = [];
for (let i = 0; i < src.length; i++) {
  if (i === navStart) { out.push(nav('menu_main', 'nav', 'Основная навигация')); i = navEnd; continue; }
  if (i === telLine) { out.push(tel('hdr__tel selectable', '      ')); continue; }
  if (i === mFirst) {
    out.push(nav('menu_main', 'menu__nav', 'Меню'));
    i = mTel - 1;                        // пропускаем зашитые пункты
    continue;
  }
  if (i === mTel) { out.push(tel('selectable', '  ')); continue; }
  out.push(src[i]);
}

fs.writeFileSync(f, out.join('\n'), 'utf8');
console.log('шапка переведена на меню WordPress и поле телефона');
