<?php
/**
 * Аналитика по согласию.
 *
 * Счётчик Яндекс.Метрики подключается ТОЛЬКО после того, как человек нажал
 * «Принять» в плашке про cookie. Пока согласия нет, ни одного запроса на
 * mc.yandex.ru не уходит и ни одной куки не ставится.
 *
 * Почему так, а не привычным куском в <head>: в обоих образцах, которые
 * показал заказчик, счётчик грузится сразу при открытии страницы, а плашка
 * появляется поверх уже собранных данных. Кнопка там ничего не решает, и
 * «согласие» получается задним числом.
 *
 * Заказчику для включения достаточно вписать номер счётчика в «Контакты и
 * подвал» — плашка сама перестроится на две кнопки и текст про аналитику.
 *
 * @package artdom
 */

/**
 * Номер счётчика. Только цифры: из поля может приехать что угодно, вплоть до
 * целого куска кода со страницы Метрики, а в адрес и в вызов уходит число.
 *
 * @return string Пустая строка, если счётчик не задан.
 */
function artdom_metrika_id() {
	$raw = (string) artdom_field( 'opt_metrika', true );
	$id  = preg_replace( '/\D+/', '', $raw );
	return $id ? $id : '';
}

/**
 * Значение по умолчанию в обход поля. Нужно там, где запасной текст
 * выбирается по состоянию сайта, а не просто подставляется.
 *
 * @param string $key Ключ.
 * @return mixed
 */
function artdom_default( $key ) {
	$d = artdom_defaults();
	return isset( $d[ $key ] ) ? $d[ $key ] : '';
}

/**
 * Загрузчик счётчика. Ждёт события artdom:cookie-ok — оно приходит в момент
 * нажатия «Принять» и сразу при загрузке, если человек согласился раньше.
 */
function artdom_print_analytics() {
	$id = artdom_metrika_id();
	if ( ! $id ) {
		return;
	}
	/* Админку не считаем: свои заходы в статистике только мешают. */
	if ( is_admin() || is_user_logged_in() ) {
		return;
	}
	?>
<script>
/* Счётчик подключается по согласию, а не при загрузке страницы. Флаг нужен
   потому, что событие может прийти дважды: один раз при восстановлении
   прежнего ответа, второй — если человек нажмёт кнопку в той же вкладке. */
(function () {
  var поднят = false;
  document.addEventListener("artdom:cookie-ok", function () {
    if (поднят) return;
    поднят = true;
    (function (m, e, t, r, i, k, a) {
      m[i] = m[i] || function () { (m[i].a = m[i].a || []).push(arguments); };
      m[i].l = 1 * new Date();
      k = e.createElement(t); a = e.getElementsByTagName(t)[0];
      k.async = 1; k.src = r; a.parentNode.insertBefore(k, a);
    })(window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");
    ym(<?php echo esc_js( $id ); ?>, "init", {
      clickmap: true, trackLinks: true, accurateTrackBounce: true, webvisor: false
    });
  });
})();
</script>
	<?php
}
add_action( 'wp_footer', 'artdom_print_analytics', 30 );
