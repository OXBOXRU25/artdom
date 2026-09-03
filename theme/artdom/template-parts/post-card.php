<?php
/**
 * Карточка записи блога.
 *
 * Одна разметка на два места: подборка на главной и лента в разделе «Блог».
 * Вызывается внутри цикла, данные берёт у текущей записи.
 *
 * Обложка берётся из миниатюры записи. Пока её нет — на её месте стоит
 * заглушка со знаком: так карточка держит свой размер, ряд не разъезжается,
 * а заказчик видит, куда встанет снимок. Подставлять сюда фотографии из
 * других секций нельзя — одна картинка на все карточки читается не как
 * оформление, а как недоделка.
 *
 * @package artdom
 */

$artdom_title = get_the_title();
$artdom_thumb = get_the_post_thumbnail_url( get_the_ID(), 'large' );
?>
<article class="pcard" data-rise>
  <a class="pcard__media" href="<?php the_permalink(); ?>" draggable="false" data-cursor tabindex="-1" aria-hidden="true">
    <?php if ( $artdom_thumb ) : ?>
    <img draggable="false" src="<?php echo esc_url( $artdom_thumb ); ?>" alt="" width="580" height="387" loading="lazy" decoding="async">
    <?php else : ?>
    <span class="pcard__stub"><svg viewBox="0 0 38 40" fill="currentColor"><use href="#i-mark"></use></svg></span>
    <?php endif; ?>
  </a>
  <p class="pcard__date"><time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'j F Y' ) ); ?></time></p>
  <h3 class="pcard__title">
    <a class="roll" href="<?php the_permalink(); ?>" draggable="false"><span class="roll__a"><?php echo esc_html( $artdom_title ); ?></span><span class="roll__b" aria-hidden="true"><?php echo esc_html( $artdom_title ); ?></span></a>
  </h3>
  <p class="body pcard__text"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 26 ) ); ?></p>
</article>
