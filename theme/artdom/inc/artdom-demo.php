<?php
/**
 * Кнопка «Заполнить примерами» на странице настроек.
 *
 * Нужна для показа: без объектов и отзывов ленты пустые и сайт выглядит
 * недоделанным. На боевом эти записи заменяются настоящими, поэтому кнопка
 * помечена явно и не срабатывает сама.
 *
 * @package artdom
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Демонстрационные данные — здесь же, чтобы не искать по файлам. */
function artdom_demo_data() {
	$text = 'Москва, Ленинский проспект. Текст-заглушка, который используют в типографике и дизайне. Он имитирует будущий текст, показывая, как будут выглядеть текстовые блоки, их объём, шрифт и расположение.';

	return array(
		'objects' => array(
			array( 'Резиденция на Ленинском', 'Новостройка', '2026', '89 млн ₽',  array( '180 м²', '4 спальни', '12 этаж' ) ),
			array( 'Апартаменты «Панорама»',  'Офис',        '2026', '30 млн ₽',  array( '320 м²', '1 этаж' ) ),
			array( 'Клубный дом «Северный»',  'Новостройка', '2026', '89 млн ₽',  array( '180 м²', '4 спальни', '12 этаж' ) ),
			array( 'Пример объекта №4',       'Новостройка', '2027', '45 млн ₽',  array( '96 м²', '2 спальни', '7 этаж' ) ),
			array( 'Пример объекта №5',       'Резиденция',  '2025', '120 млн ₽', array( '240 м²', '5 спален', '3 этаж' ) ),
			array( 'Пример объекта №6',       'Апартаменты', '2026', '72 млн ₽',  array( '140 м²', '3 спальни', '18 этаж' ) ),
		),
		'text'    => $text,
		'reviews' => array(
			array( 'Анна С.',    'Отзыв с Яндекс', 'Артдом нашли объект, о котором мы даже не думали — и он оказался именно тем домом, который мы искали три года.' ),
			array( 'Сергей П.',  'Отзыв на сайте', 'Полное сопровождение сделки заняло три недели вместо обещанных банком двух месяцев — юристы Артдом отработали безупречно.' ),
			array( 'Ольга У.',   'Отзыв с Яндекс', 'Оценили, что нам не показывали лишнего — всего три просмотра, и в каждом было понятно, зачем именно этот объект.' ),
			array( 'Дмитрий К.', 'Отзыв на сайте', 'Демонстрационный отзыв. Заменить на настоящий.' ),
			array( 'Елена М.',   'Отзыв с Яндекс', 'Демонстрационный отзыв. Заменить на настоящий.' ),
			array( 'Игорь В.',   'Отзыв на сайте', 'Демонстрационный отзыв. Заменить на настоящий.' ),
		),
	);
}

/** Кладёт файл темы в медиатеку один раз. */
function artdom_demo_image( $filename ) {
	$found = get_posts(
		array(
			'post_type'   => 'attachment',
			'meta_key'    => '_artdom_demo_file',
			'meta_value'  => $filename,
			'numberposts' => 1,
			'fields'      => 'ids',
			'post_status' => 'inherit',
		)
	);
	if ( $found ) {
		return (int) $found[0];
	}

	$path = get_template_directory() . '/img/' . $filename;
	if ( ! file_exists( $path ) ) {
		return 0;
	}

	require_once ABSPATH . 'wp-admin/includes/image.php';
	$up = wp_upload_bits( $filename, null, file_get_contents( $path ) );
	if ( ! empty( $up['error'] ) ) {
		return 0;
	}

	$id = wp_insert_attachment(
		array(
			'post_mime_type' => 'image/webp',
			'post_title'     => 'Фотография объекта',
			'post_status'    => 'inherit',
		),
		$up['file']
	);
	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $up['file'] ) );
	update_post_meta( $id, '_artdom_demo_file', $filename );
	return (int) $id;
}

function artdom_fill_demo() {
	$data  = artdom_demo_data();
	$thumb = artdom_demo_image( 'object.webp' );
	$made  = array( 'objects' => 0, 'reviews' => 0 );

	foreach ( $data['objects'] as $i => $o ) {
		list( $title, $cat, $year, $price, $facts ) = $o;
		if ( get_page_by_title( $title, OBJECT, 'artdom_object' ) ) {
			continue;
		}
		$id = wp_insert_post(
			array(
				'post_type'   => 'artdom_object',
				'post_title'  => $title,
				'post_status' => 'publish',
				'menu_order'  => $i,
			)
		);
		if ( is_wp_error( $id ) ) {
			continue;
		}
		wp_set_object_terms( $id, $cat, 'artdom_object_type' );
		update_field( 'field_artdom_obj_year', $year, $id );
		update_field( 'field_artdom_obj_price', $price, $id );
		update_field( 'field_artdom_obj_text', $data['text'], $id );
		update_field(
			'field_artdom_obj_facts',
			array_map(
				static function ( $v ) {
					return array( 'field_artdom_of_value' => $v );
				},
				$facts
			),
			$id
		);
		if ( $thumb ) {
			set_post_thumbnail( $id, $thumb );
		}
		++$made['objects'];
	}

	foreach ( $data['reviews'] as $i => $r ) {
		list( $name, $source, $body ) = $r;
		if ( get_page_by_title( $name, OBJECT, 'artdom_review' ) ) {
			continue;
		}
		$id = wp_insert_post(
			array(
				'post_type'   => 'artdom_review',
				'post_title'  => $name,
				'post_status' => 'publish',
				'menu_order'  => $i,
			)
		);
		if ( is_wp_error( $id ) ) {
			continue;
		}
		update_field( 'field_artdom_rev_source', $source, $id );
		update_field( 'field_artdom_rev_rating', 5, $id );
		update_field( 'field_artdom_rev_text', $body, $id );
		++$made['reviews'];
	}

	return $made;
}

/** Обработчик нажатия. */
function artdom_handle_demo() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Недостаточно прав.' );
	}
	check_admin_referer( 'artdom_demo' );

	$made = artdom_fill_demo();
	set_transient( 'artdom_demo_result', $made, 60 );

	wp_safe_redirect( admin_url( 'admin.php?page=artdom-settings' ) );
	exit;
}
add_action( 'admin_post_artdom_demo', 'artdom_handle_demo' );

/** Сама кнопка — под полями на странице настроек. */
function artdom_demo_button() {
	$screen = get_current_screen();
	if ( ! $screen || false === strpos( $screen->id, 'artdom-settings' ) ) {
		return;
	}

	$objects = (int) wp_count_posts( 'artdom_object' )->publish;
	$reviews = (int) wp_count_posts( 'artdom_review' )->publish;
	$result  = get_transient( 'artdom_demo_result' );
	if ( $result ) {
		delete_transient( 'artdom_demo_result' );
		printf(
			'<div class="notice notice-success"><p>Создано: объектов %d, отзывов %d.</p></div>',
			(int) $result['objects'],
			(int) $result['reviews']
		);
	}

	echo '<div class="notice notice-info" style="padding:14px 16px">';
	echo '<p style="margin-top:0"><strong>Демонстрационное наполнение.</strong> ';
	printf( 'Сейчас в базе объектов: %d, отзывов: %d. ', $objects, $reviews );
	echo 'Кнопка добавит по шесть примеров, чтобы ленты не были пустыми. Повторное нажатие ничего не продублирует. На боевом сайте эти записи нужно заменить настоящими.</p>';
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	echo '<input type="hidden" name="action" value="artdom_demo">';
	wp_nonce_field( 'artdom_demo' );
	echo '<button type="submit" class="button">Заполнить примерами</button>';
	echo '</form></div>';
}
add_action( 'admin_notices', 'artdom_demo_button' );
