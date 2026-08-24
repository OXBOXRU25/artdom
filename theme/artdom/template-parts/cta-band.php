<?php
/**
 * Полоса призыва внизу внутренней страницы.
 *
 * Заголовок и текст можно подменить через set_query_var — на карточке объекта
 * призыв другой, чем в каталоге.
 *
 * @package artdom
 */

$title = get_query_var( 'artdom_cta_title' );
$text  = get_query_var( 'artdom_cta_text' );
$btn   = get_query_var( 'artdom_cta_btn' );

$title = $title ? $title : 'Не нашли подходящее?';
$text  = $text ? $text : 'Часть объектов не публикуется в открытом доступе. Расскажите, что ищете&nbsp;— брокер подберёт из закрытой базы.';
$btn   = $btn ? $btn : 'Оставить заявку';
?>
  <section class="sec sec--surface ctaband">
    <div class="wrap ctaband__in" data-rise>
      <div>
        <h2 class="h2"><?php echo artdom_lines( $title ); ?></h2>
        <p class="body"><?php echo artdom_lines( $text ); ?></p>
      </div>
      <?php artdom_btn( $btn, '#', 'btn btn--wide', array( 'data-form-open' => 'lead' ) ); ?>
    </div>
  </section>
