<?php
/**
 * Галерея с просмотром на весь экран.
 *
 * Одна часть на статьи и объекты: приём один, значит и код один — иначе
 * зум починят в одном месте и забудут во втором.
 *
 * Раскладка не ровной сеткой: первый снимок крупный во всю ширину, дальше
 * пары. Ряд одинаковых прямоугольников читается как таблица, а не как
 * репортаж, а у нас снимки — часть рассказа.
 *
 * Ожидает переменную запроса artdom_gallery — массив ACF-галереи.
 *
 * @package artdom
 */

$artdom_shots = get_query_var( 'artdom_gallery' );
if ( ! is_array( $artdom_shots ) || ! $artdom_shots ) {
	return;
}
?>
<div class="gal" data-gal>
  <?php foreach ( $artdom_shots as $artdom_k => $artdom_s ) : ?>
  <?php
  $artdom_full  = isset( $artdom_s['url'] ) ? $artdom_s['url'] : '';
  $artdom_thumb = isset( $artdom_s['sizes']['large'] ) ? $artdom_s['sizes']['large'] : $artdom_full;
  $artdom_alt   = isset( $artdom_s['alt'] ) ? $artdom_s['alt'] : '';
  $artdom_cap   = isset( $artdom_s['caption'] ) ? $artdom_s['caption'] : '';
  ?>
  <button class="gal__cell<?php echo 0 === $artdom_k ? ' gal__cell--wide' : ''; ?>" type="button"
          data-gal-open="<?php echo (int) $artdom_k; ?>"
          data-gal-src="<?php echo esc_url( $artdom_full ); ?>"
          data-gal-cap="<?php echo esc_attr( $artdom_cap ); ?>"
          aria-label="Открыть фотографию <?php echo (int) $artdom_k + 1; ?> из <?php echo count( $artdom_shots ); ?>">
    <img src="<?php echo esc_url( $artdom_thumb ); ?>" alt="<?php echo esc_attr( $artdom_alt ); ?>" loading="lazy" decoding="async" draggable="false">
    <span class="gal__zoom" aria-hidden="true"><svg viewBox="0 0 12 12"><use href="#i-plus"></use></svg></span>
  </button>
  <?php endforeach; ?>
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
    <img class="lb__img" data-lb-img alt="" draggable="false">
  </div>
  <p class="lb__bar">
    <span class="lb__count" data-lb-count></span>
    <span class="lb__cap" data-lb-cap></span>
    <span class="lb__hint">Колесо или двойное нажатие — увеличить</span>
  </p>
</dialog>
