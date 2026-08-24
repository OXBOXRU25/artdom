<?php
/**
 * Секция: Избранные объекты
 *
 * @package artdom
 */

$u = get_template_directory_uri();
?>
  <!-- ============ Избранные объекты ============ -->
  <section class="sec sec--white objects" id="objects">
    <div class="wrap">
      <div class="sechead" data-rise>
        <div class="sechead__text">
          <h2 class="h2">Избранные объекты</h2>
          <p class="body">Ведём клиента от подбора объекта до регистрации права&nbsp;— сами или через проверенных партнёров.</p>
        </div>
        <a class="btn btn--wide" href="#" draggable="false">
          <span class="roll"><span class="roll__a">Смотреть<br>готовые объекты</span><span class="roll__b" aria-hidden="true">Смотреть<br>готовые объекты</span></span>
          <span class="btn__arrow" aria-hidden="true"><svg viewBox="0 0 23 6"><use href="#i-arrow"></use></svg><svg viewBox="0 0 23 6"><use href="#i-arrow"></use></svg></span>
        </a>
      </div>

      <div class="rule"></div>

      <div class="slider" data-slider data-rise>
        <div class="slider__track" tabindex="0" role="group" aria-label="Избранные объекты, лента">

          <article class="card">
            <a class="card__media" data-rise="shutter" href="#" draggable="false" data-cursor tabindex="-1" aria-hidden="true"><img draggable="false" src="<?php echo esc_url( $u ); ?>/img/object.webp" alt="Резиденция на Ленинском" width="580" height="395" loading="lazy" decoding="async"></a>
            <div class="card__meta">
              <span class="chip">Новостройка</span>
              <span class="chip">2026</span>
            </div>
            <h3 class="card__title"><a class="roll" href="#" draggable="false"><span class="roll__a">Резиденция на Ленинском</span><span class="roll__b" aria-hidden="true">Резиденция на Ленинском</span></a></h3>
            <ul class="card__facts selectable"><li>180 м²</li><li>4 спальни</li><li>12 этаж</li></ul>
            <p class="body card__text">Москва, Ленинский проспект. Текст-заглушка, который используют в типографике и дизайне. Он имитирует будущий текст, показывая, как будут выглядеть текстовые блоки, их объём, шрифт и расположение.</p>
            <p class="card__price selectable">89 млн <sup>₽</sup></p>
          </article>

          <article class="card">
            <a class="card__media" data-rise="shutter" href="#" draggable="false" data-cursor tabindex="-1" aria-hidden="true"><img draggable="false" src="<?php echo esc_url( $u ); ?>/img/object.webp" alt="Апартаменты «Панорама»" width="580" height="395" loading="lazy" decoding="async"></a>
            <div class="card__meta">
              <span class="chip">Офис</span>
              <span class="chip">2026</span>
            </div>
            <h3 class="card__title"><a class="roll" href="#" draggable="false"><span class="roll__a">Апартаменты «Панорама»</span><span class="roll__b" aria-hidden="true">Апартаменты «Панорама»</span></a></h3>
            <ul class="card__facts selectable"><li>320 м²</li><li>1 этаж</li></ul>
            <p class="body card__text">Москва, Ленинский проспект. Текст-заглушка, который используют в типографике и дизайне. Он имитирует будущий текст, показывая, как будут выглядеть текстовые блоки, их объём, шрифт и расположение.</p>
            <p class="card__price selectable">30 млн <sup>₽</sup></p>
          </article>

          <article class="card">
            <a class="card__media" data-rise="shutter" href="#" draggable="false" data-cursor tabindex="-1" aria-hidden="true"><img draggable="false" src="<?php echo esc_url( $u ); ?>/img/object.webp" alt="Клубный дом «Северный»" width="580" height="395" loading="lazy" decoding="async"></a>
            <div class="card__meta">
              <span class="chip">Новостройка</span>
              <span class="chip">2026</span>
            </div>
            <h3 class="card__title"><a class="roll" href="#" draggable="false"><span class="roll__a">Клубный дом «Северный»</span><span class="roll__b" aria-hidden="true">Клубный дом «Северный»</span></a></h3>
            <ul class="card__facts selectable"><li>180 м²</li><li>4 спальни</li><li>12 этаж</li></ul>
            <p class="body card__text">Москва, Ленинский проспект. Текст-заглушка, который используют в типографике и дизайне. Он имитирует будущий текст, показывая, как будут выглядеть текстовые блоки, их объём, шрифт и расположение.</p>
            <p class="card__price selectable">89 млн <sup>₽</sup></p>
          </article>

          <!-- ВНИМАНИЕ: карточки 4–6 добавил я, в макете их нет.
               Нужны, чтобы лента реально листалась (в макете бегунок занимает 53% трека,
               то есть объектов там около шести). Данные условные — заменить на настоящие. -->
          <article class="card">
            <a class="card__media" data-rise="shutter" href="#" draggable="false" data-cursor tabindex="-1" aria-hidden="true"><img draggable="false" src="<?php echo esc_url( $u ); ?>/img/object.webp" alt="Пример объекта" width="580" height="395" loading="lazy" decoding="async"></a>
            <div class="card__meta">
              <span class="chip">Новостройка</span>
              <span class="chip">2027</span>
            </div>
            <h3 class="card__title"><a class="roll" href="#" draggable="false"><span class="roll__a">Пример объекта №4</span><span class="roll__b" aria-hidden="true">Пример объекта №4</span></a></h3>
            <ul class="card__facts selectable"><li>96 м²</li><li>2 спальни</li><li>7 этаж</li></ul>
            <p class="body card__text">Демонстрационная карточка для проверки листания. Настоящие данные подставим из админки после посадки на WordPress.</p>
            <p class="card__price selectable">45 млн <sup>₽</sup></p>
          </article>

          <article class="card">
            <a class="card__media" data-rise="shutter" href="#" draggable="false" data-cursor tabindex="-1" aria-hidden="true"><img draggable="false" src="<?php echo esc_url( $u ); ?>/img/object.webp" alt="Пример объекта" width="580" height="395" loading="lazy" decoding="async"></a>
            <div class="card__meta">
              <span class="chip">Резиденция</span>
              <span class="chip">2025</span>
            </div>
            <h3 class="card__title"><a class="roll" href="#" draggable="false"><span class="roll__a">Пример объекта №5</span><span class="roll__b" aria-hidden="true">Пример объекта №5</span></a></h3>
            <ul class="card__facts selectable"><li>240 м²</li><li>5 спален</li><li>3 этаж</li></ul>
            <p class="body card__text">Демонстрационная карточка для проверки листания. Настоящие данные подставим из админки после посадки на WordPress.</p>
            <p class="card__price selectable">120 млн <sup>₽</sup></p>
          </article>

          <article class="card">
            <a class="card__media" data-rise="shutter" href="#" draggable="false" data-cursor tabindex="-1" aria-hidden="true"><img draggable="false" src="<?php echo esc_url( $u ); ?>/img/object.webp" alt="Пример объекта" width="580" height="395" loading="lazy" decoding="async"></a>
            <div class="card__meta">
              <span class="chip">Апартаменты</span>
              <span class="chip">2026</span>
            </div>
            <h3 class="card__title"><a class="roll" href="#" draggable="false"><span class="roll__a">Пример объекта №6</span><span class="roll__b" aria-hidden="true">Пример объекта №6</span></a></h3>
            <ul class="card__facts selectable"><li>140 м²</li><li>3 спальни</li><li>18 этаж</li></ul>
            <p class="body card__text">Демонстрационная карточка для проверки листания. Настоящие данные подставим из админки после посадки на WordPress.</p>
            <p class="card__price selectable">72 млн <sup>₽</sup></p>
          </article>

        </div>
        <div class="slider__bar" aria-hidden="true"><div class="slider__thumb" data-thumb></div></div>
      </div>
    </div>
  </section>
