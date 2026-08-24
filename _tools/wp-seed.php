<?php
/**
 * Первичное наполнение: объекты и отзывы.
 *
 * Данные демонстрационные — заменить настоящими. Помечаем каждую запись метой,
 * чтобы повторный запуск не наплодил копий.
 * Картинки заводим в медиатеку: поле «Изображение» хранит id вложения,
 * именем файла из папки темы его не заполнить.
 */
$_SERVER['HTTP_HOST'] = '127.0.0.1:8080';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['SERVER_NAME'] = '127.0.0.1';
$_SERVER['SERVER_PORT'] = '8080';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SCRIPT_NAME'] = '/index.php';
require __DIR__ . '/../wp/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$dry = in_array( '--dry', $argv, true );

/** Кладёт файл темы в медиатеку один раз. Проверка режима — ДО любого действия. */
function artdom_seed_image( $filename, $title, $dry ) {
	$existing = get_posts( array(
		'post_type'   => 'attachment',
		'meta_key'    => '_artdom_seed_file',
		'meta_value'  => $filename,
		'numberposts' => 1,
		'fields'      => 'ids',
	) );
	if ( $existing ) { return (int) $existing[0]; }
	if ( $dry ) { echo "  [проба] загрузил бы $filename\n"; return 0; }

	$path = get_template_directory() . '/img/' . $filename;
	if ( ! file_exists( $path ) ) { return 0; }
	$up = wp_upload_bits( $filename, null, file_get_contents( $path ) );
	if ( ! empty( $up['error'] ) ) { return 0; }
	$id = wp_insert_attachment( array(
		'post_mime_type' => 'image/webp',
		'post_title'     => $title,
		'post_status'    => 'inherit',
	), $up['file'] );
	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $up['file'] ) );
	update_post_meta( $id, '_artdom_seed_file', $filename );
	return (int) $id;
}

$objects = array(
	array( 'Резиденция на Ленинском', 'Новостройка', '2026', '89 млн ₽', array( '180 м²', '4 спальни', '12 этаж' ) ),
	array( 'Апартаменты «Панорама»',  'Офис',        '2026', '30 млн ₽', array( '320 м²', '1 этаж' ) ),
	array( 'Клубный дом «Северный»',  'Новостройка', '2026', '89 млн ₽', array( '180 м²', '4 спальни', '12 этаж' ) ),
	array( 'Пример объекта №4',       'Новостройка', '2027', '45 млн ₽', array( '96 м²', '2 спальни', '7 этаж' ) ),
	array( 'Пример объекта №5',       'Резиденция',  '2025', '120 млн ₽', array( '240 м²', '5 спален', '3 этаж' ) ),
	array( 'Пример объекта №6',       'Апартаменты', '2026', '72 млн ₽', array( '140 м²', '3 спальни', '18 этаж' ) ),
);
$text = 'Москва, Ленинский проспект. Текст-заглушка, который используют в типографике и дизайне. Он имитирует будущий текст, показывая, как будут выглядеть текстовые блоки, их объём, шрифт и расположение.';

$thumb = artdom_seed_image( 'object.webp', 'Фотография объекта', $dry );
$made = 0;
foreach ( $objects as $i => $o ) {
	list( $title, $cat, $year, $price, $facts ) = $o;
	$found = get_posts( array( 'post_type' => 'artdom_object', 'title' => $title, 'numberposts' => 1, 'fields' => 'ids', 'post_status' => 'any' ) );
	if ( $found ) { continue; }
	if ( $dry ) { echo "  [проба] создал бы объект «$title»\n"; continue; }

	$id = wp_insert_post( array( 'post_type' => 'artdom_object', 'post_title' => $title, 'post_status' => 'publish', 'menu_order' => $i ) );
	wp_set_object_terms( $id, $cat, 'artdom_object_type' );
	update_field( 'field_artdom_obj_year', $year, $id );
	update_field( 'field_artdom_obj_price', $price, $id );
	update_field( 'field_artdom_obj_text', $text, $id );
	update_field( 'field_artdom_obj_facts', array_map( function ( $v ) { return array( 'field_artdom_of_value' => $v ); }, $facts ), $id );
	if ( $thumb ) { set_post_thumbnail( $id, $thumb ); }
	++$made;
}

$reviews = array(
	array( 'Анна С.',   'Отзыв с Яндекс', 'Артдом нашли объект, о котором мы даже не думали — и он оказался именно тем домом, который мы искали три года.' ),
	array( 'Сергей П.', 'Отзыв на сайте', 'Полное сопровождение сделки заняло три недели вместо обещанных банком двух месяцев — юристы Артдом отработали безупречно.' ),
	array( 'Ольга У.',  'Отзыв с Яндекс', 'Оценили, что нам не показывали лишнего — всего три просмотра, и в каждом было понятно, зачем именно этот объект.' ),
	array( 'Дмитрий К.','Отзыв на сайте', 'Демонстрационный отзыв для проверки листания ленты. Заменить на настоящий.' ),
	array( 'Елена М.',  'Отзыв с Яндекс', 'Демонстрационный отзыв для проверки листания ленты. Заменить на настоящий.' ),
	array( 'Игорь В.',  'Отзыв на сайте', 'Демонстрационный отзыв для проверки листания ленты. Заменить на настоящий.' ),
);
$madeR = 0;
foreach ( $reviews as $i => $r ) {
	list( $name, $source, $body ) = $r;
	$found = get_posts( array( 'post_type' => 'artdom_review', 'title' => $name, 'numberposts' => 1, 'fields' => 'ids', 'post_status' => 'any' ) );
	if ( $found ) { continue; }
	if ( $dry ) { echo "  [проба] создал бы отзыв «$name»\n"; continue; }
	$id = wp_insert_post( array( 'post_type' => 'artdom_review', 'post_title' => $name, 'post_status' => 'publish', 'menu_order' => $i ) );
	update_field( 'field_artdom_rev_source', $source, $id );
	update_field( 'field_artdom_rev_rating', 5, $id );
	update_field( 'field_artdom_rev_text', $body, $id );
	++$madeR;
}

echo "объектов создано: $made, отзывов: $madeR" . ( $dry ? ' (пробный прогон)' : '' ) . "\n";
echo 'всего в базе: объектов ' . wp_count_posts( 'artdom_object' )->publish . ', отзывов ' . wp_count_posts( 'artdom_review' )->publish . "\n";
