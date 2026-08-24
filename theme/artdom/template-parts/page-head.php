<?php
/**
 * Шапка внутренней страницы: крошки, заголовок, подводка.
 *
 * Значения передаются через set_query_var перед вызовом — так часть остаётся
 * обычным шаблоном и не требует глобальных переменных.
 *
 * @package artdom
 */

$head_title = get_query_var( 'artdom_head_title' );
$head_lead  = get_query_var( 'artdom_head_lead' );
$head_extra = get_query_var( 'artdom_head_extra' );
?>
  <section class="sec sec--white pagehead">
    <div class="wrap">
      <?php echo artdom_crumbs(); ?>
      <h1 class="h1 pagehead__title"><?php echo artdom_lines( $head_title ); ?></h1>
      <?php if ( $head_lead ) : ?>
      <p class="lead pagehead__lead"><?php echo artdom_lines( $head_lead ); ?></p>
      <?php endif; ?>
      <?php if ( $head_extra ) : ?>
      <div class="pagehead__extra"><?php echo wp_kses_post( $head_extra ); ?></div>
      <?php endif; ?>
    </div>
  </section>
