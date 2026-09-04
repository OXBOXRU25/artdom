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
  <button class="lb__close" type="button" data-lb-close aria-label="Закрыть">
    <svg viewBox="0 0 22 22" aria-hidden="true"><use href="#i-close"></use></svg>
  </button>
  <button class="lb__nav lb__nav--prev" type="button" data-lb-prev aria-label="Предыдущая">
    <svg viewBox="0 0 24 16" aria-hidden="true"><use href="#i-arrow-xl"></use></svg>
  </button>
  <button class="lb__nav lb__nav--next" type="button" data-lb-next aria-label="Следующая">
    <svg viewBox="0 0 24 16" aria-hidden="true"><use href="#i-arrow-xl"></use></svg>
  </button>
  <div class="lb__stage" data-lb-stage>
    <?php /* Пустой src недопустим по стандарту, а картинку подставляет скрипт.
             Кладём прозрачную точку: место занято, лишнего запроса нет. */ ?>
    <img class="lb__img" data-lb-img alt="" draggable="false"
         src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==">
  </div>
  <p class="lb__bar">
    <span class="lb__count" data-lb-count></span>
    <span class="lb__cap" data-lb-cap></span>
    <span class="lb__hint">Колесо или двойное нажатие — увеличить</span>
  </p>
</dialog>
