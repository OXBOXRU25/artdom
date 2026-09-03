<?php
/**
 * Форма поиска.
 *
 * Своя, потому что стандартная у WordPress приходит с <input type="submit">
 * — валидатор просит здесь <button>, и оформить кнопку-инпут нашими стилями
 * всё равно нельзя: внутрь него не положишь ни стрелку, ни перекат текста.
 *
 * Поле и кнопка стоят в одной строке и живут по нашим же токенам: поле —
 * .field__input, кнопка — тот же примитив, что и везде на сайте.
 *
 * @package artdom
 */

$artdom_sid = 'srch-' . wp_unique_id();
?>
<form class="srch" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
  <label class="vh" for="<?php echo esc_attr( $artdom_sid ); ?>">Поиск по сайту</label>
  <input class="field__input srch__in" type="search" id="<?php echo esc_attr( $artdom_sid ); ?>"
         name="s" value="<?php echo esc_attr( get_search_query() ); ?>"
         placeholder="Например: пентхаус или ипотека">
  <button class="srch__go" type="submit">
    <span class="vh">Искать</span>
    <svg viewBox="0 0 24 16" aria-hidden="true"><use href="#i-arrow-xl"></use></svg>
  </button>
</form>
