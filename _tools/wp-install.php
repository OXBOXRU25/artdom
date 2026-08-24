<?php
/**
 * Установка локального WordPress без прохода по формам.
 * Запускать из CLI: php _tools/wp-install.php
 */

// Ядро при загрузке смотрит в $_SERVER, а в CLI их нет — подставляем.
$_SERVER['HTTP_HOST']      = '127.0.0.1:8080';
$_SERVER['REQUEST_URI']    = '/';
$_SERVER['SERVER_NAME']    = '127.0.0.1';
$_SERVER['SERVER_PORT']    = '8080';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SCRIPT_NAME']    = '/index.php';

define( 'WP_INSTALLING', true );
require __DIR__ . '/../wp/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/upgrade.php';
require_once ABSPATH . 'wp-admin/includes/plugin.php';

if ( is_blog_installed() ) {
	echo "WordPress уже установлен\n";
} else {
	$result = wp_install(
		'АРТДОМ',            // название сайта
		'admin',             // логин администратора
		'admin@artdom.local',
		true,                // видим для поисковиков (локально неважно)
		'',
		'artdom-local'       // пароль, только для этой машины
	);
	if ( is_wp_error( $result ) ) {
		echo 'ОШИБКА: ' . $result->get_error_message() . "\n";
		exit( 1 );
	}
	echo "установлен, id администратора: " . $result['user_id'] . "\n";
}

// Человеческие ссылки
global $wp_rewrite;
$wp_rewrite->set_permalink_structure( '/%postname%/' );
$wp_rewrite->flush_rules( true );
update_option( 'permalink_structure', '/%postname%/' );

// Русский интерфейс и часовой пояс
update_option( 'WPLANG', 'ru_RU' );
update_option( 'timezone_string', 'Europe/Moscow' );
update_option( 'blogdescription', 'Премиальная недвижимость в Москве' );

echo "ссылки: " . get_option( 'permalink_structure' ) . "\n";
echo "адрес: " . home_url() . "\n";
echo "тем в наличии: " . implode( ', ', array_keys( wp_get_themes() ) ) . "\n";
