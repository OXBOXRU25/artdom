<?php
/**
 * Плашка про файлы cookie.
 *
 * Уведомление, а не диалог: страницу не затемняет и ничего не блокирует.
 * У образцов, с которых снято построение (poedimdostavka.ru и второй пример
 * заказчика), подложка закрывает весь экран — читать сайт нельзя, пока не
 * ткнёшь. Для информационного сообщения это перебор, приём не взят.
 *
 * ДВА СОСТОЯНИЯ, и выбирает их номер счётчика в настройках:
 *
 *   номера нет  — сайт не отслеживает ничего. Текст говорит это прямо,
 *                 кнопка одна: отказываться не от чего.
 *   номер есть  — текст про аналитику и ДВЕ кнопки. Счётчик подключается
 *                 только после «Принять» (см. inc/artdom-analytics.php);
 *                 отказ запоминается так же, как согласие, и второй раз
 *                 человека не спрашивают.
 *
 * Так сделано ради передачи сайта: заказчик вписывает номер в админке, и
 * согласие начинает работать само. Иначе он поставил бы Метрику в <head>,
 * как в обоих образцах, где счётчик отрабатывает ДО нажатия и кнопка ничего
 * не решает.
 *
 * @package artdom
 */

$legal   = artdom_field( 'opt_legal', true );
$privacy = ( is_array( $legal ) && ! empty( $legal[0]['url'] ) ) ? $legal[0]['url'] : '';

$counter = artdom_metrika_id();

/* Своё поле выигрывает всегда — заказчик мог переписать формулировку.
   Пусто — берём запасной текст под текущее состояние сайта. Читаем поле
   напрямую, а не через artdom_field(): та подставила бы запасной вариант
   раньше, чем мы решим, какой из двух нужен. */
$own  = function_exists( 'get_field' ) ? trim( (string) get_field( 'cookie_text', 'option' ) ) : '';
$text = '' !== $own ? $own : artdom_default( $counter ? 'cookie_text_analytics' : 'cookie_text' );
?>
<aside class="cookie" data-cookie hidden aria-label="Про файлы cookie">
  <p class="cookie__text">
    <?php echo esc_html( $text ); ?>
    <?php if ( $privacy ) : ?>
    <a class="selectable" href="<?php echo esc_url( $privacy ); ?>"><?php echo esc_html( artdom_field( 'cookie_link' ) ); ?></a>
    <?php endif; ?>
  </p>
  <div class="cookie__acts">
    <?php if ( $counter ) : ?>
    <button class="btn btn--sm cookie__ok" type="button" data-cookie-ok>
      <?php echo esc_html( artdom_field( 'cookie_btn_yes' ) ); ?>
    </button>
    <button class="cookie__no" type="button" data-cookie-no>
      <?php echo esc_html( artdom_field( 'cookie_btn_no' ) ); ?>
    </button>
    <?php else : ?>
    <button class="btn btn--sm cookie__ok" type="button" data-cookie-ok>
      <?php echo esc_html( artdom_field( 'cookie_btn' ) ); ?>
    </button>
    <?php endif; ?>
  </div>
</aside>
