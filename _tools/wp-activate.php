<?php
/**
 * Включает тему, заводит главную страницу на нашем шаблоне и делает её домашней.
 */
$_SERVER['HTTP_HOST'] = '127.0.0.1:8080';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['SERVER_NAME'] = '127.0.0.1';
$_SERVER['SERVER_PORT'] = '8080';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SCRIPT_NAME'] = '/index.php';

require __DIR__ . '/../wp/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/plugin.php';

switch_theme( 'artdom' );
echo 'тема: ' . get_option( 'stylesheet' ) . "\n";

$page = get_page_by_path( 'glavnaya' );
if ( ! $page ) {
	$id = wp_insert_post( array(
		'post_title'  => 'Главная',
		'post_name'   => 'glavnaya',
		'post_status' => 'publish',
		'post_type'   => 'page',
	) );
	echo "страница создана, id $id\n";
} else {
	$id = $page->ID;
	echo "страница уже есть, id $id\n";
}

update_post_meta( $id, '_wp_page_template', 'templates/template-mainpage.php' );
update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $id );

echo 'шаблон: ' . get_post_meta( $id, '_wp_page_template', true ) . "\n";
echo 'домашняя: ' . ( (int) get_option( 'page_on_front' ) === $id ? 'да' : 'НЕТ' ) . "\n";

// меню в шапке и подвале
$items = array( 'О компании' => '#about', 'Объекты' => '#objects', 'Услуги' => '#services', 'Контакты' => '#contacts' );
foreach ( array( 'menu_main' => 'Главное', 'menu_footer' => 'В подвале' ) as $loc => $title ) {
	$menu = wp_get_nav_menu_object( $title );
	if ( ! $menu ) {
		$menu_id = wp_create_nav_menu( $title );
		foreach ( $items as $label => $url ) {
			wp_update_nav_menu_item( $menu_id, 0, array(
				'menu-item-title'  => $label,
				'menu-item-url'    => home_url( '/' ) . $url,
				'menu-item-status' => 'publish',
			) );
		}
	} else {
		$menu_id = $menu->term_id;
	}
	$locations = get_theme_mod( 'nav_menu_locations', array() );
	$locations[ $loc ] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );
	echo "меню $loc: id $menu_id\n";
}
