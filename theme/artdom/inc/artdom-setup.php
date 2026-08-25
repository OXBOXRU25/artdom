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
 * Наполнить сайт примерами — один раз на каждую версию набора.
 *
 * Версия нужна вот зачем: первый заход поставил прежние шесть заглушек, флаг
 * «уже наполнено» встал, и переписанный набор на боевой уже не попал бы
 * никогда. Номер версии снимает это ограничение ровно один раз.
 *
 * Создание идемпотентно, поэтому повторный проход ничего не дублирует.
 */
function artdom_maybe_seed() {
	$version = 5;

	if ( (int) get_option( 'artdom_seed_version' ) >= $version ) {
		return;
	}
	if ( ! function_exists( 'update_field' ) || ! function_exists( 'artdom_fill_demo' ) ) {
		return;   // ACF ещё не поднялся — попробуем на следующем запросе
	}

	/* Флаг ставим ДО работы: если что-то упадёт на середине, второй заход не
	   начнёт всё заново и не наплодит половинок. */
	update_option( 'artdom_seed_version', $version, false );

	artdom_drop_old_demo();
	artdom_fill_demo();
	artdom_drop_duplicates();
	artdom_trim_contacts();
	flush_rewrite_rules( false );
}
add_action( 'init', 'artdom_maybe_seed', 100 );

/**
 * Убрать заглушки прежнего набора.
 *
 * Опознаём по тексту, который писался только в них: заказчик такого не
 * напишет, а чужие записи трогать нельзя.
 */
function artdom_drop_old_demo() {
	$marks = array(
		'artdom_object' => array( 'obj_text', array( 'Текст-заглушка', 'Демонстрационная карточка' ) ),
		'artdom_review' => array( 'rev_text', array( 'Демонстрационный отзыв' ) ),
	);

	foreach ( $marks as $type => $rule ) {
		list( $field, $needles ) = $rule;
		$posts = get_posts( array( 'post_type' => $type, 'numberposts' => -1, 'post_status' => 'any' ) );
		foreach ( $posts as $post ) {
			$text = (string) get_field( $field, $post->ID );
			foreach ( $needles as $needle ) {
				if ( false !== mb_strpos( $text, $needle ) ) {
					wp_delete_post( $post->ID, true );
					break;
				}
			}
		}
	}
}


/**
 * Снять дубли по заголовку.
 *
 * Появились от смены правил ярлыка: прежние записи имели кириллический
 * post_name, проверка «уже есть» искала латинский и не находила — заводилась
 * вторая копия того же отзыва. Оставляем самую свежую.
 */
function artdom_drop_duplicates() {
	foreach ( array( 'artdom_object', 'artdom_review', 'artdom_service' ) as $type ) {
		$seen  = array();
		$posts = get_posts(
			array(
				'post_type'   => $type,
				'numberposts' => -1,
				'post_status' => 'any',
				'orderby'     => 'ID',
				'order'       => 'DESC',
			)
		);
		foreach ( $posts as $post ) {
			if ( isset( $seen[ $post->post_title ] ) ) {
				wp_delete_post( $post->ID, true );
				continue;
			}
			$seen[ $post->post_title ] = true;
		}
	}
}

/**
 * Убрать с контактов раздел «Как добраться».
 *
 * Текст лежит в содержимом страницы, а оно уже сохранено в базе — правки в
 * исходных данных на существующий сайт не действуют. Отсюда отдельный проход.
 * Режем по заголовку: всё от него до следующего заголовка того же уровня.
 */
function artdom_trim_contacts() {
	$page = get_page_by_path( 'contacts' );
	if ( ! $page ) {
		return;
	}

	$content = (string) $page->post_content;
	if ( false === mb_strpos( $content, 'Как добраться' ) ) {
		return;
	}

	$parts = explode( '<h3>', $content );
	$kept  = array();
	foreach ( $parts as $part ) {
		if ( 0 === mb_strpos( $part, 'Как добраться' ) ) {
			continue;
		}
		$kept[] = $part;
	}

	$new = trim( implode( '<h3>', $kept ) );
	if ( $new !== $content ) {
		wp_update_post( array( 'ID' => $page->ID, 'post_content' => $new ) );
	}
}
