<?php
/**
 * Строка отзыва на странице «Отзывы».
 *
 * Отличается от карточки в ленте тем, что текст показывается целиком: сюда
 * приходят читать, а не пробегать глазами. Поэтому же здесь есть дата —
 * по ней судят, свежие отзывы или собраны три года назад.
 *
 * @package artdom
 */

$artdom_name   = get_the_title();
$artdom_rating = (int) get_field( 'rev_rating' );
$artdom_rating = $artdom_rating > 0 ? $artdom_rating : 5;
?>
<article class="revrow" data-rise>
  <div class="revrow__head">
    <div class="review__ava" aria-hidden="true"><?php echo esc_html( artdom_initials( $artdom_name, get_field( 'rev_initials' ) ) ); ?></div>
    <div class="revrow__who">
      <h2 class="review__name"><?php echo esc_html( $artdom_name ); ?></h2>
      <p class="review__src"><?php echo esc_html( get_field( 'rev_source' ) ); ?></p>
    </div>
    <p class="revrow__date"><time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'j F Y' ) ); ?></time></p>
  </div>
  <?php echo artdom_stars( $artdom_rating, sprintf( '%d из 5', $artdom_rating ), 'p' ); ?>
  <p class="body revrow__text selectable"><?php echo artdom_lines( get_field( 'rev_text' ) ); ?></p>
</article>
