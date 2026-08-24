<?php
/**
 * Кнопка «Заполнить примерами» на странице настроек.
 *
 * Сами данные и создание записей живут в inc/artdom-content.php — их много,
 * и правят их чаще, чем кнопку.
 *
 * @package artdom
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
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
add_action( 'admin_post_artdom_demo', 'artdom_handle_demo' );
add_action( 'admin_notices', 'artdom_demo_button' );
