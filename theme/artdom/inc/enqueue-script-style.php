<?php
/**
 * Подключение стилей и скриптов.
 *
 * Переписано с нуля. В базе oxboxwise здесь было три дефекта:
 *   1. подключался js/libs.min.js, которого в теме нет — 404 на каждой загрузке;
 *   2. jQuery тянулся с ajax.googleapis.com — лишнее рукопожатие и риск
 *      недоступности, при том что WordPress несёт jQuery локально;
 *   3. висели стили прошлого проекта (critical.css, libs.min.css, RTL).
 *
 * @package artdom
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'ARTDOM_VERSION' ) ) {
	define( 'ARTDOM_VERSION', '1.0.0' );
}

/**
 * Версия файла по времени изменения: браузер подхватывает правки сразу,
 * а на боевом кеш всё равно живёт до следующей правки.
 */
function artdom_asset_version( $relative_path ) {
	$file = get_template_directory() . $relative_path;
	return file_exists( $file ) ? (string) filemtime( $file ) : ARTDOM_VERSION;
}

function artdom_scripts() {
	// Заголовок темы (обязателен для WordPress), сами стили — отдельным файлом,
	// чтобы относительные пути к шрифтам и картинкам не зависели от его места.
	wp_enqueue_style( 'artdom-theme', get_stylesheet_uri(), array(), ARTDOM_VERSION );

	wp_enqueue_style(
		'artdom-main',
		get_template_directory_uri() . '/css/style.css',
		array( 'artdom-theme' ),
		artdom_asset_version( '/css/style.css' )
	);

	wp_enqueue_script(
		'artdom-main',
		get_template_directory_uri() . '/js/main.js',
		array(),
		artdom_asset_version( '/js/main.js' ),
		true
	);

	// Адрес обработчика и одноразовый ключ для форм
	wp_localize_script(
		'artdom-main',
		'ARTDOM',
		array(
			'ajax'  => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'artdom_form' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'artdom_scripts' );

/**
 * Убираем то, что странице не нужно: стили редактора блоков и глобальные
 * css-переменные темы. Экономит примерно 90 КБ на каждой загрузке.
 */
function artdom_dequeue_bloat() {
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
	wp_dequeue_style( 'global-styles' );
	wp_dequeue_style( 'classic-theme-styles' );
}
add_action( 'wp_enqueue_scripts', 'artdom_dequeue_bloat', 100 );

/**
 * Шрифты грузим заранее: они нужны для первого экрана, а браузер узнаёт о них
 * только дочитав CSS.
 */
function artdom_preload_fonts() {
	$fonts = array( 'inter18-400.woff2', 'inter18-600.woff2' );
	foreach ( $fonts as $font ) {
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( get_template_directory_uri() . '/fonts/' . $font )
		);
	}
	printf(
		'<link rel="icon" href="%s" type="image/svg+xml">' . "\n",
		esc_url( get_template_directory_uri() . '/favicon.svg' )
	);
}
add_action( 'wp_head', 'artdom_preload_fonts', 1 );
