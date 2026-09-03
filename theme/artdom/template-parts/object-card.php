<?php
/**
 * Карточка объекта.
 *
 * Одна разметка на два места: лента на главной и сетка каталога. Вызывается
 * внутри цикла, данные берёт у текущей записи.
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
$thumb = get_the_post_thumbnail_url( get_the_ID(), 'large' );
?>
<article class="card">
  <a class="card__media" data-rise="shutter" href="<?php echo esc_url( $link ); ?>" draggable="false" data-cursor tabindex="-1" aria-hidden="true"><img draggable="false" src="<?php echo esc_url( $thumb ? $thumb : $u . '/img/object.webp' ); ?>" alt="<?php echo esc_attr( $title ); ?>" width="580" height="395" loading="lazy" decoding="async"></a>
  <div class="card__meta">
    <?php if ( $terms && ! is_wp_error( $terms ) ) : ?>
      <span class="chip"><?php echo esc_html( $terms[0]->name ); ?></span>
    <?php endif; ?>
    <?php if ( $year ) : ?>
      <span class="chip"><?php echo esc_html( $year ); ?></span>
    <?php endif; ?>
  </div>
  <h3 class="card__title"><a class="roll" href="<?php echo esc_url( $link ); ?>" draggable="false"><span class="roll__a"><?php echo esc_html( $title ); ?></span><span class="roll__b" aria-hidden="true"><?php echo esc_html( $title ); ?></span></a></h3>
  <?php if ( is_array( $facts ) && $facts ) : ?>
  <ul class="card__facts selectable"><?php foreach ( $facts as $f ) : ?><li><?php echo esc_html( $f['value'] ); ?></li><?php endforeach; ?></ul>
  <?php endif; ?>
  <?php if ( $text ) : ?>
  <?php /* Обрез по СТРОКАМ, а не по числу слов: wp_trim_words не знает ширину
           карточки и обрывал текст на середине мысли — например на одиноком
           «В», хотя в строку влезало ещё три слова. Скрипт режет по последнему
           слову, которое поместилось. */ ?>
  <p class="body card__text" data-clip="4" data-clip-href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $text ); ?></p>
  <?php endif; ?>
  <?php if ( $price ) : ?>
  <p class="card__price selectable"><?php echo wp_kses( str_replace( '₽', '<sup>₽</sup>', esc_html( $price ) ), array( 'sup' => array() ) ); ?></p>
  <?php endif; ?>
</article>
