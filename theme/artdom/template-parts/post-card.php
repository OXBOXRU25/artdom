<?php
/**
 * Карточка записи блога.
 *
 * Одна разметка на два места: подборка на главной и лента в разделе «Блог».
 * Вызывается внутри цикла, данные берёт у текущей записи.
 *
 * Без обложки намеренно. Своих снимков под статьи у компании пока нет, а
 * подставлять сюда фотографии из других секций — ровно та ошибка, которую
 * заказчик уже заметил на объектах: одна картинка на двенадцать карточек
 * читается не как оформление, а как недоделка. Текстовая карточка с датой и
 * волосяной линией сверху — тот же язык, что на странице контактов.
 *
 * @package artdom
 */

$artdom_title = get_the_title();
?>
<article class="pcard" data-rise>
  <p class="pcard__date"><time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'j F Y' ) ); ?></time></p>
  <h3 class="pcard__title">
    <a class="roll" href="<?php the_permalink(); ?>" draggable="false"><span class="roll__a"><?php echo esc_html( $artdom_title ); ?></span><span class="roll__b" aria-hidden="true"><?php echo esc_html( $artdom_title ); ?></span></a>
  </h3>
  <p class="body pcard__text"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 26 ) ); ?></p>
</article>
