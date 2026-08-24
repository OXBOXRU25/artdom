import fs from 'node:fs';
const htm = 'D:/AI/Artdom/static/index.html';
const css = 'D:/AI/Artdom/static/css/style.css';
let h = fs.readFileSync(htm, 'utf8');
let s = fs.readFileSync(css, 'utf8');
const log = [];

const oldHero = '      <img draggable="false" src="img/hero.webp" alt="" width="1920" height="1040" fetchpriority="high" decoding="async">';
const newHero =
'      <!-- Постер вырезан из этого же ролика, поэтому подмены кадра не видно.\n' +
'           На телефоне ни один source не подходит по media, качать нечего — остаётся постер.\n' +
'           display:none видео не спасает: preload тянет файл независимо от отрисовки. -->\n' +
'      <video class="hero__video" autoplay muted loop playsinline\n' +
'             poster="img/hero-poster.webp" preload="metadata"\n' +
'             width="1920" height="1080" aria-hidden="true" tabindex="-1">\n' +
'        <source src="video/hero.webm" type="video/webm" media="(min-width: 768px)">\n' +
'        <source src="video/hero.mp4"  type="video/mp4"  media="(min-width: 768px)">\n' +
'      </video>';

if (!h.includes(oldHero)) { console.log('НЕ НАЙДЕНА картинка hero'); process.exit(1); }
h = h.split(oldHero).join(newHero);
log.push('hero: картинка -> видео с постером');

const a = '.hero__bg img { width: 100%; height: 100%; object-fit: cover; }';
const b = '.hero__bg img, .hero__bg video { width: 100%; height: 100%; object-fit: cover; }';
if (!s.includes(a)) { console.log('НЕ НАЙДЕНО правило .hero__bg img'); process.exit(1); }
s = s.split(a).join(b);
log.push('css: object-fit распространён на video');

fs.writeFileSync(htm, h, 'utf8');
fs.writeFileSync(css, s, 'utf8');
console.log(log.join('\n'));
