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
$form  = get_query_var( 'artdom_cta_form' );

/* Запасные значения берутся из полей «Надписи разделов» → «Полосы призыва»,
   а не из литералов: раньше заказчик видел на трёх страницах три разных
   призыва и не мог тронуть ни один. Страницы объекта и услуги передают сюда
   свои наборы через set_query_var — те тоже читаются из полей. */
$title = $title ? $title : artdom_field( 'cta_title', true );
$text  = $text ? $text : artdom_field( 'cta_text', true );
$btn   = $btn ? $btn : artdom_field( 'cta_btn', true );
/* Какую форму открывает кнопка. На странице отзывов — форму отзыва, иначе
   призыв «расскажите, как всё прошло» вёл бы к подбору объектов. */
$form  = $form ? $form : 'lead';
?>
  <section class="sec sec--surface ctaband">
    <div class="wrap ctaband__in" data-rise>
      <div>
        <h2 class="h2"><?php echo artdom_lines( $title ); ?></h2>
        <p class="body"><?php echo artdom_lines( $text ); ?></p>
      </div>
      <?php artdom_btn( $btn, '#', 'btn btn--wide', array( 'data-form-open' => $form ) ); ?>
    </div>
  </section>
