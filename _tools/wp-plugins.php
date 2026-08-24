<?php
$_SERVER['HTTP_HOST'] = '127.0.0.1:8080';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['SERVER_NAME'] = '127.0.0.1';
$_SERVER['SERVER_PORT'] = '8080';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SCRIPT_NAME'] = '/index.php';
require __DIR__ . '/../wp/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/plugin.php';

foreach ( array( 'advanced-custom-fields-pro/acf.php' ) as $p ) {
	if ( is_plugin_active( $p ) ) { echo "уже включён: $p\n"; continue; }
	$r = activate_plugin( $p );
	echo is_wp_error( $r ) ? 'ОШИБКА: ' . $r->get_error_message() . "\n" : "включён: $p\n";
}
echo "\nвсе активные плагины:\n";
foreach ( (array) get_option( 'active_plugins', array() ) as $p ) { echo "  $p\n"; }

echo "\nдоступность функций PRO:\n";
foreach ( array( 'acf_add_local_field_group', 'acf_add_options_page', 'have_rows', 'get_field' ) as $f ) {
	echo '  ' . str_pad( $f, 28 ) . ( function_exists( $f ) ? 'есть' : 'НЕТ' ) . "\n";
}
if ( defined( 'ACF_VERSION' ) ) { echo "\nверсия ACF: " . ACF_VERSION . "\n"; }
