<?php
/**
 * Первичная настройка при включении темы.
 *
 * Чтобы установка на боевом была в три клика, а не в двадцать: тема сама
 * заводит главную страницу на своём шаблоне, назначает её домашней и создаёт
 * два меню. Всё идемпотентно — повторное включение ничего не дублирует.
 *
 * Демо-контент (объекты и отзывы) здесь НЕ создаётся: на боевом он не нужен,
 * а нужен — есть отдельная кнопка на странице настроек.
 *
 * @package artdom
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function artdom_after_switch_theme() {
	$done = array();

	/* 1. Главная страница на нашем шаблоне */
	$page = get_page_by_path( 'glavnaya' );
	if ( ! $page ) {
		$page_id = wp_insert_post(
			array(
				'post_title'  => 'Главная',
				'post_name'   => 'glavnaya',
				'post_status' => 'publish',
				'post_type'   => 'page',
			)
		);
		$done[] = 'создана страница «Главная»';
	} else {
		$page_id = $page->ID;
	}

	if ( $page_id && ! is_wp_error( $page_id ) ) {
		update_post_meta( $page_id, '_wp_page_template', 'templates/template-mainpage.php' );
		if ( 'page' !== get_option( 'show_on_front' ) || (int) get_option( 'page_on_front' ) !== (int) $page_id ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $page_id );
			$done[] = 'главная назначена домашней';
		}
	}

	/* 2. Меню в шапке и подвале */
	$items     = array(
		'О компании' => '#about',
		'Объекты'    => '#objects',
		'Услуги'     => '#services',
		'Контакты'   => '#contacts',
	);
	$locations = get_theme_mod( 'nav_menu_locations', array() );

	foreach ( array( 'menu_main' => 'Главное', 'menu_footer' => 'В подвале' ) as $loc => $title ) {
		$menu = wp_get_nav_menu_object( $title );
		if ( ! $menu ) {
			$menu_id = wp_create_nav_menu( $title );
			if ( ! is_wp_error( $menu_id ) ) {
				foreach ( $items as $label => $url ) {
					wp_update_nav_menu_item(
						$menu_id,
						0,
						array(
							'menu-item-title'  => $label,
							'menu-item-url'    => home_url( '/' ) . $url,
							'menu-item-status' => 'publish',
						)
					);
				}
				$done[] = 'создано меню «' . $title . '»';
			}
		} else {
			$menu_id = $menu->term_id;
		}
		if ( ! is_wp_error( $menu_id ) ) {
			$locations[ $loc ] = $menu_id;
		}
	}
	set_theme_mod( 'nav_menu_locations', $locations );

	/* 3. Человеческие ссылки — иначе объекты откроются только по ?p= */
	if ( '' === get_option( 'permalink_structure' ) ) {
		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '/%postname%/' );
		$wp_rewrite->flush_rules( true );
		$done[] = 'включены человеческие ссылки';
	}

	set_transient( 'artdom_setup_done', $done, 60 );
}
add_action( 'after_switch_theme', 'artdom_after_switch_theme' );

/** Показываем администратору, что тема сделала при включении. */
function artdom_setup_notice() {
	$done = get_transient( 'artdom_setup_done' );
	if ( ! $done ) {
		return;
	}
	delete_transient( 'artdom_setup_done' );

	echo '<div class="notice notice-success"><p><strong>Тема «Артдом» настроена.</strong> ';
	echo $done ? esc_html( implode( ', ', $done ) ) . '.' : 'Всё уже было на месте.';
	echo ' Тексты и картинки — в разделе «Страницы → Главная», контакты — в «Настройки сайта».</p></div>';
}
add_action( 'admin_notices', 'artdom_setup_notice' );

/**
 * Предупреждаем, если ACF нет: без него страница покажет тексты из макета,
 * но редактировать их будет негде.
 */
function artdom_acf_notice() {
	if ( function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}
	echo '<div class="notice notice-warning"><p><strong>Артдом:</strong> не найден Advanced Custom Fields PRO. ';
	echo 'Сайт работает и показывает тексты из макета, но поля в админке не появятся. Установите плагин.</p></div>';
}
add_action( 'admin_notices', 'artdom_acf_notice' );

/**
 * Перечитать правила адресов, когда состав разделов изменился.
 *
 * Новый тип записи без этого отдаёт 404: WordPress держит правила в базе и
 * сам их не обновляет. На боевом это выглядит как «страницы нет», хотя шаблон
 * на месте. Сравниваем подпись состава разделов и перечитываем только когда
 * она изменилась — flush на каждый запрос стоит дорого.
 */
function artdom_maybe_flush_rules() {
	$types = get_post_types( array( 'has_archive' => true ), 'names' );
	sort( $types );
	$signature = md5( implode( ',', $types ) . '|' . get_option( 'permalink_structure' ) );

	if ( get_option( 'artdom_routes' ) !== $signature ) {
		flush_rewrite_rules( false );
		update_option( 'artdom_routes', $signature, false );
	}
}
add_action( 'init', 'artdom_maybe_flush_rules', 99 );

/**
 * Один раз наполнить пустой сайт примерами.
 *
 * Демонстрационный сайт без содержимого показывает пустые разделы и выглядит
 * сломанным. Кнопка в админке для этого есть, но требовать нажатия ради того,
 * чтобы сайт вообще что-то показал, — плохой обмен. Поэтому наполняем сами,
 * строго один раз: флаг ставится до заполнения, так что удалённое вручную
 * содержимое обратно не вернётся.
 */
function artdom_maybe_seed() {
	if ( get_option( 'artdom_seeded' ) ) {
		return;
	}
	if ( ! function_exists( 'update_field' ) || ! function_exists( 'artdom_fill_demo' ) ) {
		return;   // ACF ещё не поднялся — попробуем на следующем запросе
	}

	$has = 0;
	foreach ( array( 'artdom_object', 'artdom_review', 'artdom_service' ) as $type ) {
		$has += (int) wp_count_posts( $type )->publish;
	}
	if ( $has > 0 ) {
		update_option( 'artdom_seeded', 'было своё', false );
		return;
	}

	update_option( 'artdom_seeded', gmdate( 'c' ), false );
	artdom_fill_demo();
	flush_rewrite_rules( false );
}
add_action( 'init', 'artdom_maybe_seed', 100 );
