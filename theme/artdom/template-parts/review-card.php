<?php
/**
 * Карточка отзыва. Одна разметка на ленту главной и на страницу всех отзывов.
 *
 * @package artdom
 */

$name   = get_the_title();
$rating = (int) get_field( 'rev_rating' );
$rating = $rating > 0 ? $rating : 5;
?>
<article class="review">
  <div class="review__top">
    <div class="review__ava" aria-hidden="true"><?php echo esc_html( artdom_initials( $name, get_field( 'rev_initials' ) ) ); ?></div>
    <div>
      <h3 class="review__name"><?php echo esc_html( $name ); ?></h3>
      <p class="review__src"><?php echo esc_html( get_field( 'rev_source' ) ); ?></p>
    </div>
  </div>
  <?php echo artdom_stars( $rating, sprintf( '%d из 5', $rating ), 'p' ); ?>
  <?php
  /* Две строки — под ленту в три колонки. На телефоне карточка во всю ширину,
     и в две строки влезало 74-79 знаков при длине отзывов 110-684: человек
     видел десятую часть и должен был тыкать в многоточие.
     Четыре, а не шесть: в ленте все карточки равны самой высокой, и разброс
     строк превращается в дыру под коротким отзывом. При четырёх три отзыва из
     шести влезают целиком, у остальных видно суть, а провал — одна строка
     вместо трёх. */
  ?>
  <p class="body review__text" data-clip="2" data-clip-narrow="4"><?php echo artdom_lines( get_field( 'rev_text' ) ); ?></p>
</article>
