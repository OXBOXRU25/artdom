<?php
/**
 * Досоздать и обновить служебные страницы на боевом.
 *
 * Пришёл на смену ensure-cookie-page.php: страниц, которые тема знает, а
 * сайт — ещё нет, стало больше одной, и второй такой скрипт означал бы два
 * места с одной задачей.
 *
 * Тексты берутся из artdom_demo_pages() в самой теме — единственного места,
 * где они записаны. Здесь только перенос их в базу.
 *
 * ПРАВКИ ЗАКАЗЧИКА НЕ ТРОГАЕМ. Существующая страница переписывается только
 * если её текст до сих пор содержит пометку «Демонстрационный текст» —
 * значит руками её не правили. Нет пометки — страница остаётся как есть, и
 * скрипт говорит об этом вслух, а не молчит.
 *
 * Запуск:
 *   scp ensure-pages.php root@<адрес>:/var/www/artdom/wp-pages.php
 *   ssh root@<адрес> "php /var/www/artdom/wp-pages.php; rm -f /var/www/artdom/wp-pages.php"
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

/* Какие страницы ведёт этот скрипт. Остальные создаёт обычный посев. */
$ведём = array( 'cookie', 'privacy' );

$все    = artdom_demo_pages();
$итог   = array();
$ошибки = 0;

foreach ( $ведём as $слаг ) {
	$данные = null;
	foreach ( $все as $p ) {
		if ( $слаг === $p['slug'] ) {
			$данные = $p;
			break;
		}
	}
	if ( ! $данные ) {
		fwrite( STDERR, "в теме нет страницы со slug «$слаг»\n" );
		++$ошибки;
		continue;
	}

	$есть = get_page_by_path( $слаг, OBJECT, 'page' );

	if ( ! $есть ) {
		$id = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_title'   => $данные['title'],
				'post_name'    => $данные['slug'],
				'post_status'  => 'publish',
				'post_content' => $данные['content'],
				'post_excerpt' => $данные['excerpt'],
			)
		);
		if ( is_wp_error( $id ) || ! $id ) {
			fwrite( STDERR, "$слаг: создать не удалось\n" );
			++$ошибки;
			continue;
		}
		$итог[] = "$слаг: создана, #$id";
		continue;
	}

	$id       = (int) $есть->ID;
	$нетронут = ( false !== mb_strpos( (string) $есть->post_content, 'Демонстрационный текст' ) );

	if ( ! $нетронут ) {
		$итог[] = "$слаг: #$id оставлена как есть — текст уже правили руками";
		continue;
	}

	wp_update_post(
		array(
			'ID'           => $id,
			'post_title'   => $данные['title'],
			'post_content' => $данные['content'],
			'post_excerpt' => $данные['excerpt'],
		)
	);
	$итог[] = "$слаг: #$id обновлена (была демонстрационная)";
}

/* ---------- список правовых документов ---------- */
if ( function_exists( 'get_field' ) ) {
	$cookie = get_page_by_path( 'cookie', OBJECT, 'page' );
	if ( $cookie ) {
		$адрес  = get_permalink( $cookie->ID );
		$список = get_field( 'opt_legal', 'option' );
		$список = is_array( $список ) ? $список : array();
		$нашлось = false;
		foreach ( $список as $строка ) {
			if ( ! empty( $строка['url'] ) && untrailingslashit( $строка['url'] ) === untrailingslashit( $адрес ) ) {
				$нашлось = true;
			}
		}
		if ( ! $нашлось ) {
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
			$итог[] = 'список документов: дописана строка про cookie';
		}
	}
}

foreach ( $итог as $строка ) {
	echo $строка . "\n";
}

/* Вердикт, а не перечисление шагов: иначе успех неотличим от провала. */
$плохо = 0;
foreach ( $ведём as $слаг ) {
	$стр = get_page_by_path( $слаг, OBJECT, 'page' );
	if ( ! $стр || 'publish' !== $стр->post_status ) {
		fwrite( STDERR, "ПРОВЕРКА: страницы «$слаг» нет или она не опубликована\n" );
		++$плохо;
	}
}
if ( $плохо || $ошибки ) {
	fwrite( STDERR, "ЗАВЕРШЕНО С ОШИБКАМИ\n" );
	exit( 1 );
}
echo "ГОТОВО: все страницы на месте и опубликованы\n";
