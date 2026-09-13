<?php
/**
 * Functions which enhance the theme by hooking into WordPress
 *
 * @package artdom
 */

/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function artdom_body_classes( $classes ) {
	// Adds a class of hfeed to non-singular pages.
	if ( ! is_singular() ) {
		$classes[] = 'hfeed';
	}

	// Adds a class of no-sidebar when there is no sidebar present.
	if ( ! is_active_sidebar( 'sidebar-1' ) ) {
		$classes[] = 'no-sidebar';
	}

	return $classes;
}
add_filter( 'body_class', 'artdom_body_classes' );

/**
 * Add a pingback url auto-discovery header for single posts, pages, or attachments.
 */
function artdom_pingback_header() {
	if ( is_singular() && pings_open() ) {
		printf( '<link rel="pingback" href="%s">', esc_url( get_bloginfo( 'pingback_url' ) ) );
	}
}
add_action( 'wp_head', 'artdom_pingback_header' );

/**
 * Адрес раздела «Объекты».
 *
 * Заведён 13.09.2026: кнопки «Смотреть готовые объекты» на первом экране и в
 * секции «Избранные объекты» вели одна якорем на секцию той же страницы
 * (#objects), вторая — на «#», то есть никуда. Обе должны открывать раздел.
 *
 * Берём архив типа записей, а не пишем «/objects/» руками: слаг задан при
 * регистрации типа, и при его смене хардкод разъехался бы молча. Запасной
 * вариант нужен на случай, когда функция зовётся до регистрации типа —
 * тогда архива ещё нет и вернулся бы false.
 *
 * @return string
 */
function artdom_objects_url() {
	$url = get_post_type_archive_link( 'artdom_object' );
	return $url ? $url : home_url( '/objects/' );
}

/**
 * Адрес страницы по её слагу.
 *
 * Нужен там, где кнопка на главной ведёт на внутреннюю страницу: «Узнать
 * больше» в блоке «О компании» вела на якорь #contacts — то есть к форме на
 * той же странице, а не на страницу о компании.
 *
 * Ищем страницу, а не пишем «/about/» руками: слаг заказчик может поменять
 * в админке, и хардкод разъехался бы молча.
 *
 * @param string $slug Слаг страницы.
 * @return string Пустая строка, если такой страницы нет.
 */
function artdom_page_url( $slug ) {
	$page = get_page_by_path( $slug );
	return $page ? get_permalink( $page ) : '';
}

/**
 * Ссылка кнопки, ведущей на страницу «О компании».
 *
 * Заглушку «#contacts» из посева разрешаем в адрес страницы; свою ссылку
 * заказчика не трогаем.
 *
 * @param string $raw Значение поля.
 * @return string
 */
function artdom_about_link( $raw ) {
	$raw = trim( (string) $raw );
	if ( '' === $raw || '#' === $raw || '#contacts' === $raw || '#about' === $raw ) {
		$url = artdom_page_url( 'about' );
		if ( $url ) {
			return $url;
		}
	}
	return $raw;
}

/**
 * Ссылка кнопки, ведущей в раздел объектов.
 *
 * Поле в админке могло остаться с посева со значением «#» или «#objects» —
 * обе заглушки разрешаем в настоящий адрес. Свою ссылку заказчика не трогаем:
 * если он вписал что-то осмысленное, оно и уедет в разметку.
 *
 * @param string $raw Значение поля.
 * @return string
 */
function artdom_objects_link( $raw ) {
	$raw = trim( (string) $raw );
	if ( '' === $raw || '#' === $raw || '#objects' === $raw ) {
		return artdom_objects_url();
	}
	return $raw;
}
