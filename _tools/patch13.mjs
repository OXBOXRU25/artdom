// Блок "Надёжность" превращается в закреплённую сцену: три экрана прокрутки,
// на каждом своя фотография и свой заголовок.
import fs from 'node:fs';

const htm = 'D:/AI/Artdom/static/index.html';
const css = 'D:/AI/Artdom/static/css/style.css';
let h = fs.readFileSync(htm, 'utf8');
let s = fs.readFileSync(css, 'utf8');
const log = [];

const oldBlock = `  <!-- ============ Надёжность ============ -->
  <section class="guaranty">
    <div class="guaranty__bg">
      <img draggable="false" src="img/garanty.webp" alt="" width="1920" height="963" loading="lazy" decoding="async">
    </div>
    <h2 class="h2--big wrap">Надежность и гарантии</h2>
  </section>`;

/* ВНИМАНИЕ: фотографии 2 и 3 и заголовки 2 и 3 — временные, их в макете нет.
   Взяты из уже имеющихся на сайте, чтобы механику было видно. Заменить. */
const slides = [
  ['img/garanty.webp', 'Надежность и гарантии'],
  ['img/uslugi.webp', 'Проверяем объект<br>до внесения задатка'],
  ['img/object.webp', 'Сопровождаем<br>до регистрации права'],
];

const newBlock = `  <!-- ============ Надёжность: закреплённая сцена на три экрана ============
       Каркас держит высоту (три экрана), сцена внутри липкая и не двигается,
       пока каркас проезжает мимо. Слои меняются по доле прокрутки внутри каркаса.
       ФОТО 2-3 И ЗАГОЛОВКИ 2-3 ВРЕМЕННЫЕ — в макете их нет, заменить. -->
  <section class="guaranty" data-guaranty aria-label="Надежность и гарантии">
    <div class="guaranty__stage">
      <div class="guaranty__bg">
${slides.map(([src], i) => `        <img draggable="false" src="${src}" alt="" width="1920" height="963" ${i ? 'loading="lazy" ' : ''}decoding="async" data-slide="${i}"${i === 0 ? ' data-on="true"' : ''}>`).join('\n')}
      </div>
      <div class="guaranty__text wrap">
${slides.map(([, t], i) => `        <${i === 0 ? 'h2' : 'p'} class="h2--big guaranty__title" data-slide="${i}"${i === 0 ? ' data-on="true"' : ''}>${t}</${i === 0 ? 'h2' : 'p'}>`).join('\n')}
      </div>
      <div class="guaranty__steps" aria-hidden="true">
${slides.map((_, i) => `        <span data-slide="${i}"${i === 0 ? ' data-on="true"' : ''}></span>`).join('\n')}
      </div>
    </div>
  </section>`;

if (!h.includes(oldBlock)) { console.log('блок не найден'); process.exit(1); }
h = h.split(oldBlock).join(newBlock);
log.push('блок перестроен: ' + slides.length + ' слоя');

const oldCss = `/* --- Гарантии --------------------------------------------------------------- */
.guaranty { position: relative; min-height: calc(963 * var(--k)); display: grid; place-items: center; overflow: hidden; }
.guaranty__bg { position: absolute; inset: 0; }
.guaranty__bg img { width: 100%; height: 100%; object-fit: cover; }
.guaranty__bg::after { content: ""; position: absolute; inset: 0; background: var(--c-scrim); }
.guaranty .h2--big { position: relative; z-index: 2; color: var(--c-white); }`;

const newCss = `/* --- Гарантии: закреплённая сцена ------------------------------------------
   Каркас высотой в три экрана, сцена внутри липкая. У CSS-липкости нет
   собственного диапазона: она держит блок ровно пока каркас в кадре,
   и отпускает сама — досчитывать конец не нужно. */
.guaranty { position: relative; height: 300svh; }
.guaranty__stage { position: sticky; inset-block-start: 0; height: 100svh; overflow: hidden; display: grid; place-items: center; }
.guaranty__bg { position: absolute; inset: 0; }
.guaranty__bg img {
  position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover;
  opacity: 0; transition: opacity .9s var(--ease), transform 1.6s var(--ease);
  transform: scale(1.06);
}
.guaranty__bg img[data-on="true"] { opacity: 1; transform: scale(1); }
/* Затемнение одно на все слои, иначе на перекрёстном переходе оно удваивается */
.guaranty__bg::after { content: ""; position: absolute; inset: 0; background: var(--c-scrim); }

.guaranty__text { position: relative; z-index: 2; width: 100%; display: grid; place-items: center; }
.guaranty__title {
  grid-area: 1 / 1; margin: 0; color: var(--c-white); text-align: center;
  opacity: 0; transform: translateY(26px);
  transition: opacity .7s var(--ease), transform .7s var(--ease);
}
.guaranty__title[data-on="true"] { opacity: 1; transform: none; }

.guaranty__steps {
  position: absolute; z-index: 2; inset-block-end: calc(48 * var(--k));
  display: flex; gap: 10px;
}
.guaranty__steps span {
  width: 34px; height: 3px; border-radius: 2px;
  background: rgba(255, 255, 255, .35);
  transition: background-color .5s var(--ease);
}
.guaranty__steps span[data-on="true"] { background: var(--c-white); }

@media (max-width: 860px) {
  .guaranty__steps { inset-block-end: 28px; }
  .guaranty__steps span { width: 26px; }
}`;

if (!s.includes(oldCss)) { console.log('стили блока не найдены'); process.exit(1); }
s = s.split(oldCss).join(newCss);
log.push('стили заменены');

fs.writeFileSync(htm, h, 'utf8');
fs.writeFileSync(css, s, 'utf8');
console.log(log.join('\n'));
