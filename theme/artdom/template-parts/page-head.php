<?php
/**
 * Шапка внутренней страницы: крошки, заголовок, подводка.
 *
 * Значения передаются через set_query_var перед вызовом — так часть остаётся
 * обычным шаблоном и не требует глобальных переменных.
 *
 * Крошки стоят по центру, под меню: заказчик просил один приём на всех
 * внутренних страницах.
 *
 * Заголовок можно спрятать (artdom_head_hide) — но ТОЛЬКО с экрана, из
 * разметки он не исчезает. У страницы обязан быть ровно один <h1>: по нему
 * строится оглавление для скринридера и по нему же страницу читают поисковые
 * системы. Убрать его совсем — значит сделать страницу безымянной.
 *
 * @package artdom
 */

$head_title = get_query_var( 'artdom_head_title' );
$head_lead  = get_query_var( 'artdom_head_lead' );
$head_extra = get_query_var( 'artdom_head_extra' );
$head_hide  = (bool) get_query_var( 'artdom_head_hide' );
?>
  <section class="sec sec--white pagehead<?php echo $head_hide ? ' pagehead--bare' : ''; ?>">
    <div class="wrap">
      <h1 class="h1 pagehead__title<?php echo $head_hide ? ' vh' : ''; ?>"><?php echo artdom_lines( $head_title ); ?></h1>
      <?php if ( $head_lead ) : ?>
      <p class="lead pagehead__lead"><?php echo artdom_lines( $head_lead ); ?></p>
      <?php endif; ?>
      <?php if ( $head_extra ) : ?>
      <div class="pagehead__extra"><?php echo wp_kses_post( $head_extra ); ?></div>
      <?php endif; ?>
    </div>
  </section>
