<?php
/**
 * Досоздать страницу «Как и зачем АРТДОМ использует файлы cookie» на боевом.
 *
 * Зачем отдельный скрипт, а не кнопка «Заполнить примерами»: та создаёт ещё
 * и демонстрационные объекты, отзывы и услуги. Здесь нужна одна страница и
 * одна строка в списке документов — остальное трогать незачем.
 *
 * Текст страницы НЕ дублируется: скрипт берёт его из artdom_demo_pages() в
 * самой теме, то есть из единственного места, где он записан.
 *
 * Запуск с машины:
 *   ssh -i <ключ> root@<адрес> "php /var/www/artdom/wp-cookie-page.php"
 * — файл кладётся туда же, где лежит wp-load.php, и удаляется после.
 *
 * Скрипт идемпотентен: второй запуск ничего не меняет и честно говорит об
 * этом, а не делает вид, что поработал.
 *
 * @package artdom
 */

$корень = __DIR__;
if ( ! file_exists( $корень . '/wp-load.php' ) ) {
	fwrite( STDERR, "НЕ НАЙДЕН wp-load.php рядом со скриптом: $корень\n" );
	exit( 2 );
}
require $корень . '/wp-load.php';

if ( ! function_exists( 'artdom_demo_pages' ) ) {
	fwrite( STDERR, "Тема АРТДОМ не активна: функции artdom_demo_pages() нет.\n" );
	exit( 2 );
}

/* ---------- 1. страница ---------- */
$нужная = null;
foreach ( artdom_demo_pages() as $p ) {
	if ( 'cookie' === $p['slug'] ) {
		$нужная = $p;
		break;
	}
}
if ( ! $нужная ) {
	fwrite( STDERR, "В artdom_demo_pages() нет страницы со slug «cookie» — нечего создавать.\n" );
	exit( 2 );
}

$существует = get_page_by_path( 'cookie', OBJECT, 'page' );
if ( $существует ) {
	$id = (int) $существует->ID;
	echo "страница уже есть: #$id " . get_permalink( $id ) . "\n";
} else {
	$id = wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_title'   => $нужная['title'],
			'post_name'    => $нужная['slug'],
			'post_status'  => 'publish',
			'post_content' => $нужная['content'],
			'post_excerpt' => $нужная['excerpt'],
		)
	);
	if ( is_wp_error( $id ) || ! $id ) {
		fwrite( STDERR, "СОЗДАТЬ НЕ УДАЛОСЬ: " . ( is_wp_error( $id ) ? $id->get_error_message() : 'пустой ответ' ) . "\n" );
		exit( 1 );
	}
	echo "страница создана: #$id " . get_permalink( $id ) . "\n";
}

/* ---------- 2. строка в списке правовых документов ---------- */
if ( ! function_exists( 'get_field' ) ) {
	fwrite( STDERR, "ACF не активен — список документов не тронут.\n" );
	exit( 1 );
}

$адрес  = get_permalink( $id );
$список = get_field( 'opt_legal', 'option' );
if ( ! is_array( $список ) ) {
	$список = array();
}

$есть = false;
foreach ( $список as $строка ) {
	if ( ! empty( $строка['url'] ) && untrailingslashit( $строка['url'] ) === untrailingslashit( $адрес ) ) {
		$есть = true;
		break;
	}
}

if ( $есть ) {
	echo "в списке документов уже есть — ничего не меняю\n";
} else {
	$новый = array();
	foreach ( $список as $строка ) {
		$новый[] = array(
			'field_artdom_leg_label' => isset( $строка['label'] ) ? $строка['label'] : '',
			'field_artdom_leg_url'   => isset( $строка['url'] ) ? $строка['url'] : '#',
		);
	}
	$новый[] = array(
		'field_artdom_leg_label' => 'Использование файлов cookie',
		'field_artdom_leg_url'   => $адрес,
	);
	update_field( 'field_artdom_opt_legal', $новый, 'option' );
	echo "дописано в список документов, строк стало: " . count( $новый ) . "\n";
}

/* ---------- 3. вердикт ----------
   Лог обязан говорить вердикт, а не печатать промежуточные значения:
   иначе успех неотличим от провала. */
$проверка = get_field( 'opt_legal', 'option' );
$нашлось  = false;
if ( is_array( $проверка ) ) {
	foreach ( $проверка as $строка ) {
		if ( ! empty( $строка['url'] ) && untrailingslashit( $строка['url'] ) === untrailingslashit( $адрес ) ) {
			$нашлось = true;
		}
	}
}
if ( ! $нашлось || 'publish' !== get_post_status( $id ) ) {
	fwrite( STDERR, "ПРОВЕРКА НЕ ПРОШЛА: страница опубликована — " . get_post_status( $id ) . ", в списке — " . ( $нашлось ? 'да' : 'нет' ) . "\n" );
	exit( 1 );
}
echo "ГОТОВО: страница опубликована и стоит в списке документов\n";
