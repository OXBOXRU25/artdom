<?php
/**
 * Галерея статьи: лента с одним крупным кадром.
 *
 * Построение снято с profkamaz.ru (страница «Диалог с коллективом»): один
 * снимок во всю ширину, стрелки СНАРУЖИ кадра, тонкая полоса прогресса под
 * ним. Замеры при 1440: кадр 1092x515 (2.12), стрелки 40x40 по вертикали от
 * середины кадра, полоса 420x2 в 22 под кадром.
 *
 * Лента — тот же примитив .slider, что у объектов и отзывов: он уже умеет
 * прокрутку с доводкой, перетаскивание и полосу с бегунком. Заводить второй
 * слайдер ради одной страницы значит чинить потом оба.
 *
 * Клик по кадру открывает просмотрщик с увеличением — он ниже.
 *
 * Ожидает переменную запроса artdom_gallery — массив ACF-галереи.
 *
 * @package artdom
 */

$artdom_shots = get_query_var( 'artdom_gallery' );
if ( ! is_array( $artdom_shots ) || ! $artdom_shots ) {
	return;
}
$artdom_total = count( $artdom_shots );
?>
<div class="gal slider slider--solo" data-slider data-gal>
  <div class="gal__frame">
    <?php if ( $artdom_total > 1 ) : ?>
    <button class="gal__arrow gal__arrow--prev" type="button" data-slider-prev aria-label="Предыдущая фотография">
      <svg viewBox="0 0 24 16" aria-hidden="true"><use href="#i-arrow-xl"></use></svg>
    </button>
    <button class="gal__arrow gal__arrow--next" type="button" data-slider-next aria-label="Следующая фотография">
      <svg viewBox="0 0 24 16" aria-hidden="true"><use href="#i-arrow-xl"></use></svg>
    </button>
    <?php endif; ?>

    <div class="slider__track gal__track" tabindex="0" role="group" aria-label="Фотографии к статье">
      <?php foreach ( $artdom_shots as $artdom_k => $artdom_s ) : ?>
      <?php
      $artdom_full  = isset( $artdom_s['url'] ) ? $artdom_s['url'] : '';
      $artdom_thumb = isset( $artdom_s['sizes']['large'] ) ? $artdom_s['sizes']['large'] : $artdom_full;
      $artdom_alt   = isset( $artdom_s['alt'] ) ? $artdom_s['alt'] : '';
      $artdom_cap   = isset( $artdom_s['caption'] ) ? $artdom_s['caption'] : '';
      ?>
      <?php /* Подложка — та же фотография, растянутая по кадру и размытая: так
               вертикальный или мелкий снимок виден целиком, а поля по бокам не
               зияют пустотой. Адрес кладём переменной стиля — он у каждого
               снимка свой. */ ?>
      <button class="gal__cell" type="button"
              style="--shot: url('<?php echo esc_url( $artdom_thumb ); ?>')"
              data-gal-open="<?php echo (int) $artdom_k; ?>"
              data-gal-src="<?php echo esc_url( $artdom_full ); ?>"
              data-gal-cap="<?php echo esc_attr( $artdom_cap ); ?>"
              aria-label="Открыть фотографию <?php echo (int) $artdom_k + 1; ?> из <?php echo (int) $artdom_total; ?> во весь экран">
        <img src="<?php echo esc_url( $artdom_thumb ); ?>" alt="<?php echo esc_attr( $artdom_alt ); ?>" loading="lazy" decoding="async" draggable="false">
      </button>
      <?php endforeach; ?>
    </div>
  </div>

  <?php if ( $artdom_total > 1 ) : ?>
  <div class="slider__bar gal__bar" aria-hidden="true"><div class="slider__thumb" data-thumb></div></div>
  <?php endif; ?>
</div>

<?php /* Просмотрщик один на страницу, наполняется скриптом. Нативный dialog:
         сам ловит Esc, держит фокус внутри и рисует подложку. */ ?>
<dialog class="lb" data-lb aria-label="Просмотр фотографии">

  <div class="lb__bar lb__bar--top">
    <span class="lb__count" data-lb-count></span>
    <span class="lb__tools">
      <?php /* Набор кнопок повторяет образец: увеличить, слайдшоу, во весь
               экран, миниатюры, закрыть. Знаки нарисованы здесь, а не взяты
               из спрайта сайта: у спрайта своя стрелка с крупной головкой —
               фирменная, но в просмотрщике она читается тяжело. */ ?>
      <button class="lb__btn" type="button" data-lb-zoom aria-label="Увеличить">
        <svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
          <circle cx="10.5" cy="10.5" r="6.5"/><path d="M15.5 15.5 21 21"/><path d="M10.5 7.5v6M7.5 10.5h6"/>
        </svg>
      </button>
      <button class="lb__btn" type="button" data-lb-play aria-label="Слайдшоу" aria-pressed="false">
        <svg class="lb__i-play" viewBox="0 0 24 24" aria-hidden="true" fill="currentColor"><path d="M8 5.5v13l11-6.5z"/></svg>
        <svg class="lb__i-pause" viewBox="0 0 24 24" aria-hidden="true" fill="currentColor"><rect x="7" y="5" width="3.6" height="14" rx="1"/><rect x="13.4" y="5" width="3.6" height="14" rx="1"/></svg>
      </button>
      <button class="lb__btn" type="button" data-lb-full aria-label="Во весь экран">
        <svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
          <path d="M4 9V4h5M20 9V4h-5M4 15v5h5M20 15v5h-5"/>
        </svg>
      </button>
      <button class="lb__btn" type="button" data-lb-thumbs aria-label="Показать миниатюры" aria-pressed="true">
        <svg viewBox="0 0 24 24" aria-hidden="true" fill="currentColor"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
      </button>
      <button class="lb__btn" type="button" data-lb-close aria-label="Закрыть">
        <svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M5 5 19 19M19 5 5 19"/></svg>
      </button>
    </span>
  </div>

  <?php /* Стрелки — тонкие шевроны, как в образце: они не спорят с фотографией
           и не превращаются в кнопки поверх неё. */ ?>
  <button class="lb__nav lb__nav--prev" type="button" data-lb-prev aria-label="Предыдущая">
    <svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 4 7 12l8 8"/></svg>
  </button>
  <button class="lb__nav lb__nav--next" type="button" data-lb-next aria-label="Следующая">
    <svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 4l8 8-8 8"/></svg>
  </button>
  <div class="lb__stage" data-lb-stage>
    <?php /* Пустой src недопустим по стандарту, а картинку подставляет скрипт.
             Кладём прозрачную точку: место занято, лишнего запроса нет. */ ?>
    <img class="lb__img" data-lb-img alt="" draggable="false"
         src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==">
  </div>
  <p class="lb__cap" data-lb-cap></p>

  <?php /* Лента миниатюр внизу — как в просмотрщике, который выбрал заказчик.
           Наполняется скриптом из той же галереи. */ ?>
  <div class="lb__thumbs" data-lb-thumbs-strip></div>
</dialog>
