<?php
/**
 * Артдом — подключение частей темы.
 *
 * Собрано на базе oxboxwise. Убрано то, что тянулось от прошлого проекта:
 * WooCommerce, Jetpack, кастомайзер (его скрипт был удалён вместе с кожей),
 * блоки Гутенберга, пагинация и обрезка текста — на лендинге они не нужны.
 *
 * @package artdom
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$artdom_parts = array(
	'/inc/theme-settings.php',        // поддержка возможностей темы и меню
	'/inc/artdom-defaults.php',       // тексты макета и artdom_field()
	'/inc/artdom-fields.php',         // группы полей ACF и страница настроек
	'/inc/artdom-post-types.php',     // объекты и отзывы
	'/inc/artdom-setup.php',          // первичная настройка при включении темы
	'/inc/artdom-demo.php',           // кнопка «Заполнить примерами»
	'/inc/artdom-forms.php',          // формы: обработчик и журнал заявок
	'/inc/enqueue-script-style.php',  // стили и скрипты
	'/inc/ajax-request.php',          // обработчик форм
	'/inc/template-tags.php',
	'/inc/template-functions.php',
);

foreach ( $artdom_parts as $artdom_part ) {
	$artdom_file = get_template_directory() . $artdom_part;
	if ( file_exists( $artdom_file ) ) {
		require_once $artdom_file;
	}
}
unset( $artdom_parts, $artdom_part, $artdom_file );

/**
 * Лендинг не ведёт блог: убираем из админки то, чем не пользуются,
 * чтобы заказчик не искал нужное среди лишнего.
 */
function artdom_tidy_admin_menu() {
	remove_menu_page( 'edit.php' );                   // записи
	remove_menu_page( 'edit-comments.php' );          // комментарии
}
add_action( 'admin_menu', 'artdom_tidy_admin_menu', 999 );

/** Подпись студии в подвале админки. */
function artdom_admin_footer() {
	echo 'Разработано в <a href="https://oxbox.ru" target="_blank" rel="noopener">OXBOX</a>';
}
add_filter( 'admin_footer_text', 'artdom_admin_footer' );

/** Эмодзи-скрипты странице не нужны. */
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );

/** На странице входа не подсказываем, что именно введено неверно. */
add_filter(
	'login_errors',
	function () {
		return 'Ошибка: неверный логин или пароль.';
	}
);
