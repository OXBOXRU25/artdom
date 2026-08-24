<?php
/**
 * Первый объект каталога — крупной полосой над сеткой.
 *
 * Растягивать карточку на две колонки внутри сетки нельзя: её кадр становится
 * вдвое выше соседних, ряд разъезжается и текст уходит под чужие карточки.
 * Отдельная полоса даёт ту же точку входа и ничего не ломает.
 *
 * @package artdom
 */

$u     = get_template_directory_uri();
$link  = get_field( 'obj_link' );
$link  = $link ? $link : get_permalink();
$price = get_field( 'obj_price' );
$year  = get_field( 'obj_year' );
$facts = get_field( 'obj_facts' );
$text  = get_field( 'obj_text' );
$terms = get_the_terms( get_the_ID(), 'artdom_object_type' );
$title = get_the_title();
$thumb = get_the_post_thumbnail_url( get_the_ID(), 'full' );
?>
<article class="leadcard" data-rise>
  <a class="leadcard__media" data-rise="shutter" href="<?php echo esc_url( $link ); ?>" draggable="false" data-cursor tabindex="-1" aria-hidden="true">
    <img draggable="false" src="<?php echo esc_url( $thumb ? $thumb : $u . '/img/object.webp' ); ?>" alt="<?php echo esc_attr( $title ); ?>" width="1160" height="760" decoding="async">
  </a>
  <div class="leadcard__body">
    <div class="card__meta">
      <?php if ( $terms && ! is_wp_error( $terms ) ) : ?><span class="chip"><?php echo esc_html( $terms[0]->name ); ?></span><?php endif; ?>
      <?php if ( $year ) : ?><span class="chip"><?php echo esc_html( $year ); ?></span><?php endif; ?>
    </div>
    <h2 class="leadcard__title"><a class="roll" href="<?php echo esc_url( $link ); ?>" draggable="false"><span class="roll__a"><?php echo esc_html( $title ); ?></span><span class="roll__b" aria-hidden="true"><?php echo esc_html( $title ); ?></span></a></h2>
    <?php if ( is_array( $facts ) && $facts ) : ?>
    <ul class="card__facts selectable"><?php foreach ( $facts as $f ) : ?><li><?php echo esc_html( $f['value'] ); ?></li><?php endforeach; ?></ul>
    <?php endif; ?>
    <?php if ( $text ) : ?>
    <p class="body leadcard__text"><?php echo esc_html( wp_trim_words( $text, 40 ) ); ?></p>
    <?php endif; ?>
    <?php if ( $price ) : ?>
    <p class="leadcard__price selectable"><?php echo esc_html( $price ); ?></p>
    <?php endif; ?>
    <?php artdom_btn( 'Смотреть объект', $link, 'btn btn--sm' ); ?>
  </div>
</article>
