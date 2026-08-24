<?php
/**
 * Шапка внутренней страницы: крошки, заголовок, подводка, при желании фото.
 *
 * Раскладка асимметричная — текст слева, изображение справа и ниже базовой
 * линии заголовка. Симметричный столбик по центру читается как страница
 * документации, а не как раздел сайта, который что-то продаёт.
 *
 * Значения передаются через set_query_var перед вызовом.
 *
 * @package artdom
 */

$head_title  = get_query_var( 'artdom_head_title' );
$head_lead   = get_query_var( 'artdom_head_lead' );
$head_extra  = get_query_var( 'artdom_head_extra' );
$head_figure = get_query_var( 'artdom_head_figure' );
$has_figure  = is_array( $head_figure ) && ! empty( $head_figure['url'] );
?>
  <section class="sec sec--white pagehead<?php echo $has_figure ? ' pagehead--media' : ''; ?>">
    <div class="wrap pagehead__in">
      <div class="pagehead__text">
        <?php echo artdom_crumbs(); ?>
        <h1 class="h1 pagehead__title"><?php echo artdom_lines( $head_title ); ?></h1>
        <?php if ( $head_lead ) : ?>
        <p class="lead pagehead__lead"><?php echo artdom_lines( $head_lead ); ?></p>
        <?php endif; ?>
        <?php if ( $head_extra ) : ?>
        <div class="pagehead__extra"><?php echo wp_kses_post( $head_extra ); ?></div>
        <?php endif; ?>
      </div>
      <?php if ( $has_figure ) : ?>
      <figure class="pagehead__figure" data-rise="shutter">
        <?php artdom_img( $head_figure, 'uslugi.webp', '', array( 710, 500 ), false ); ?>
      </figure>
      <?php endif; ?>
    </div>
  </section>
