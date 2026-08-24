<?php
/**
 * Секция: Отзывы
 *
 * @package artdom
 */

$u = get_template_directory_uri();
?>
  <!-- ============ Отзывы ============ -->
  <section class="sec sec--surface reviews">
    <div class="wrap">
      <div class="sechead" data-rise>
        <div class="sechead__text">
          <h2 class="h2">Честная оценка</h2>
          <p class="rating">
            <span>4.9</span>
            <span class="stars" role="img" aria-label="Оценка 5 из 5">
              <svg viewBox="0 0 20 19" aria-hidden="true"><use href="#i-star"></use></svg><svg viewBox="0 0 20 19" aria-hidden="true"><use href="#i-star"></use></svg><svg viewBox="0 0 20 19" aria-hidden="true"><use href="#i-star"></use></svg><svg viewBox="0 0 20 19" aria-hidden="true"><use href="#i-star"></use></svg><svg viewBox="0 0 20 19" aria-hidden="true"><use href="#i-star"></use></svg>
            </span>
          </p>
          <p class="body">на основе <strong>86</strong> отзывов</p>
        </div>
        <a class="btn btn--wide" href="#" draggable="false">
          <span class="roll"><span class="roll__a">Добавить<br>свой отзыв</span><span class="roll__b" aria-hidden="true">Добавить<br>свой отзыв</span></span>
          <span class="btn__arrow" aria-hidden="true"><svg viewBox="0 0 23 6"><use href="#i-arrow"></use></svg><svg viewBox="0 0 23 6"><use href="#i-arrow"></use></svg></span>
        </a>
      </div>

      <div class="rule"></div>

      <div class="slider" data-slider data-rise>
        <div class="slider__track" tabindex="0" role="group" aria-label="Отзывы, лента">

          <article class="review">
            <div class="review__top">
              <div class="review__ava" aria-hidden="true">АС</div>
              <div>
                <h3 class="review__name">Анна С.</h3>
                <p class="review__src">Отзыв с Яндекс</p>
              </div>
            </div>
            <p class="stars" role="img" aria-label="5 из 5"><svg viewBox="0 0 20 19" aria-hidden="true"><use href="#i-star"></use></svg><svg viewBox="0 0 20 19" aria-hidden="true"><use href="#i-star"></use></svg><svg viewBox="0 0 20 19" aria-hidden="true"><use href="#i-star"></use></svg><svg viewBox="0 0 20 19" aria-hidden="true"><use href="#i-star"></use></svg><svg viewBox="0 0 20 19" aria-hidden="true"><use href="#i-star"></use></svg></p>
            <p class="body review__text">Артдом нашли объект, о котором мы даже не думали&nbsp;— и он оказался именно тем домом, который мы искали три года.</p>
          </article>

          <article class="review">
            <div class="review__top">
              <div class="review__ava" aria-hidden="true">СП</div>
              <div>
                <h3 class="review__name">Сергей П.</h3>
                <p class="review__src">Отзыв на сайте</p>
              </div>
            </div>
            <p class="stars" role="img" aria-label="5 из 5"><svg viewBox="0 0 20 19" aria-hidden="true"><use href="#i-star"></use></svg><svg viewBox="0 0 20 19" aria-hidden="true"><use href="#i-star"></use></svg><svg viewBox="0 0 20 19" aria-hidden="true"><use href="#i-star"></use></svg><svg viewBox="0 0 20 19" aria-hidden="true"><use href="#i-star"></use></svg><svg viewBox="0 0 20 19" aria-hidden="true"><use href="#i-star"></use></svg></p>
            <p class="body review__text">Полное сопровождение сделки заняло три недели вместо обещанных банком двух месяцев&nbsp;— юристы Артдом отработали безупречно.</p>
          </article>

          <article class="review">
            <div class="review__top">
              <div class="review__ava" aria-hidden="true">ОУ</div>
              <div>
                <h3 class="review__name">Ольга У.</h3>
                <p class="review__src">Отзыв с Яндекс</p>
              </div>
            </div>
            <p class="stars" role="img" aria-label="5 из 5"><svg viewBox="0 0 20 19" aria-hidden="true"><use href="#i-star"></use></svg><svg viewBox="0 0 20 19" aria-hidden="true"><use href="#i-star"></use></svg><svg viewBox="0 0 20 19" aria-hidden="true"><use href="#i-star"></use></svg><svg viewBox="0 0 20 19" aria-hidden="true"><use href="#i-star"></use></svg><svg viewBox="0 0 20 19" aria-hidden="true"><use href="#i-star"></use></svg></p>
            <p class="body review__text">Оценили, что нам не показывали лишнего&nbsp;— всего три просмотра, и в каждом было понятно, зачем именно этот объект.</p>
          </article>

          <!-- ВНИМАНИЕ: отзывы 4–6 добавил я, в макете их нет — нужны для проверки листания. -->
          <article class="review">
            <div class="review__top">
              <div class="review__ava" aria-hidden="true">ДК</div>
              <div>
                <h3 class="review__name">Дмитрий К.</h3>
                <p class="review__src">Отзыв на сайте</p>
              </div>
            </div>
            <p class="stars" role="img" aria-label="5 из 5"><svg viewBox="0 0 20 19" aria-hidden="true"><use href="#i-star"></use></svg><svg viewBox="0 0 20 19" aria-hidden="true"><use href="#i-star"></use></svg><svg viewBox="0 0 20 19" aria-hidden="true"><use href="#i-star"></use></svg><svg viewBox="0 0 20 19" aria-hidden="true"><use href="#i-star"></use></svg><svg viewBox="0 0 20 19" aria-hidden="true"><use href="#i-star"></use></svg></p>
            <p class="body review__text">Демонстрационный отзыв для проверки листания ленты. Заменить на настоящий после подключения админки.</p>
          </article>

          <article class="review">
            <div class="review__top">
              <div class="review__ava" aria-hidden="true">ЕМ</div>
              <div>
                <h3 class="review__name">Елена М.</h3>
                <p class="review__src">Отзыв с Яндекс</p>
              </div>
            </div>
            <p class="stars" role="img" aria-label="5 из 5"><svg viewBox="0 0 20 19" aria-hidden="true"><use href="#i-star"></use></svg><svg viewBox="0 0 20 19" aria-hidden="true"><use href="#i-star"></use></svg><svg viewBox="0 0 20 19" aria-hidden="true"><use href="#i-star"></use></svg><svg viewBox="0 0 20 19" aria-hidden="true"><use href="#i-star"></use></svg><svg viewBox="0 0 20 19" aria-hidden="true"><use href="#i-star"></use></svg></p>
            <p class="body review__text">Демонстрационный отзыв для проверки листания ленты. Заменить на настоящий после подключения админки.</p>
          </article>

          <article class="review">
            <div class="review__top">
              <div class="review__ava" aria-hidden="true">ИВ</div>
              <div>
                <h3 class="review__name">Игорь В.</h3>
                <p class="review__src">Отзыв на сайте</p>
              </div>
            </div>
            <p class="stars" role="img" aria-label="5 из 5"><svg viewBox="0 0 20 19" aria-hidden="true"><use href="#i-star"></use></svg><svg viewBox="0 0 20 19" aria-hidden="true"><use href="#i-star"></use></svg><svg viewBox="0 0 20 19" aria-hidden="true"><use href="#i-star"></use></svg><svg viewBox="0 0 20 19" aria-hidden="true"><use href="#i-star"></use></svg><svg viewBox="0 0 20 19" aria-hidden="true"><use href="#i-star"></use></svg></p>
            <p class="body review__text">Демонстрационный отзыв для проверки листания ленты. Заменить на настоящий после подключения админки.</p>
          </article>

        </div>
        <div class="slider__bar" aria-hidden="true"><div class="slider__thumb" data-thumb></div></div>
      </div>
    </div>
  </section>
