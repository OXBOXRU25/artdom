<?php
/**
 * Типы записей: объекты и отзывы, с полями к ним.
 *
 * @package artdom
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function artdom_register_post_types() {

	register_post_type(
		'artdom_object',
		array(
			'labels'        => array(
				'name'               => 'Объекты',
				'singular_name'      => 'Объект',
				'add_new'            => 'Добавить объект',
				'add_new_item'       => 'Новый объект',
				'edit_item'          => 'Редактировать объект',
				'all_items'          => 'Все объекты',
				'search_items'       => 'Искать объекты',
				'not_found'          => 'Объектов пока нет',
				'featured_image'     => 'Фотография объекта',
				'set_featured_image' => 'Выбрать фотографию',
			),
			'public'        => true,
			'has_archive'   => true,
			'rewrite'       => array( 'slug' => 'objects', 'with_front' => false ),
			'menu_icon'     => 'dashicons-building',
			'menu_position' => 20,
			'supports'      => array( 'title', 'thumbnail', 'page-attributes' ),
			'show_in_rest'  => true,
		)
	);

	register_taxonomy(
		'artdom_object_type',
		'artdom_object',
		array(
			'labels'            => array(
				'name'          => 'Категории объектов',
				'singular_name' => 'Категория',
				'add_new_item'  => 'Добавить категорию',
				'all_items'     => 'Все категории',
			),
			'hierarchical'      => true,
			'public'            => true,
			'show_admin_column' => true,
			'rewrite'           => array( 'slug' => 'object-type', 'with_front' => false ),
			'show_in_rest'      => true,
		)
	);

	register_post_type(
		'artdom_review',
		array(
			'labels'        => array(
				'name'          => 'Отзывы',
				'singular_name' => 'Отзыв',
				'add_new'       => 'Добавить отзыв',
				'add_new_item'  => 'Новый отзыв',
				'edit_item'     => 'Редактировать отзыв',
				'all_items'     => 'Все отзывы',
				'not_found'     => 'Отзывов пока нет',
			),
			'public'        => true,
			'has_archive'   => true,
			'rewrite'       => array( 'slug' => 'reviews', 'with_front' => false ),
			'show_ui'       => true,
			'menu_icon'     => 'dashicons-format-quote',
			'menu_position' => 21,
			'supports'      => array( 'title', 'page-attributes' ),
			'exclude_from_search' => true,
		)
	);

	register_post_type(
		'artdom_service',
		array(
			'labels'        => array(
				'name'               => 'Услуги',
				'singular_name'      => 'Услуга',
				'add_new'            => 'Добавить услугу',
				'add_new_item'       => 'Новая услуга',
				'edit_item'          => 'Редактировать услугу',
				'all_items'          => 'Все услуги',
				'not_found'          => 'Услуг пока нет',
				'featured_image'     => 'Фотография услуги',
				'set_featured_image' => 'Выбрать фотографию',
			),
			'public'        => true,
			'has_archive'   => true,
			'rewrite'       => array( 'slug' => 'services', 'with_front' => false ),
			'menu_icon'     => 'dashicons-portfolio',
			'menu_position' => 19,
			'supports'      => array( 'title', 'thumbnail', 'page-attributes', 'excerpt' ),
			'show_in_rest'  => true,
		)
	);
}
add_action( 'init', 'artdom_register_post_types' );

/** Поля объекта и отзыва. */
function artdom_register_cpt_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'            => 'group_artdom_object',
			'title'          => 'Данные объекта',
			'fields'         => array(
				artdom_f( 'obj_year', 'Год', 'text', array( 'wrapper' => array( 'width' => 25 ), 'placeholder' => '2026' ) ),
				artdom_f( 'obj_price', 'Цена', 'text', array( 'wrapper' => array( 'width' => 75 ), 'placeholder' => '89 млн ₽', 'instructions' => 'Пишите как показывать: «89 млн ₽».' ) ),
				artdom_f(
					'obj_facts',
					'Характеристики',
					'repeater',
					array(
						'layout'       => 'table',
						'max'          => 4,
						'button_label' => 'Добавить',
						'instructions' => 'Выводятся в строку через разделитель: 180 м² | 4 спальни | 12 этаж.',
						'sub_fields'   => array(
							artdom_f( 'value', 'Значение', 'text', array( 'key' => 'field_artdom_of_value' ) ),
						),
					)
				),
				artdom_f( 'obj_text', 'Описание', 'textarea', array( 'rows' => 5 ) ),
				artdom_f( 'obj_link', 'Ссылка', 'text', array( 'instructions' => 'Куда ведут заголовок и фотография. Пусто — ведут на саму запись.' ) ),
			),
			'location'       => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'artdom_object' ) ) ),
			'hide_on_screen' => array( 'the_content', 'excerpt', 'discussion', 'comments', 'custom_fields' ),
			'active'         => true,
		)
	);

	acf_add_local_field_group(
		array(
			'key'            => 'group_artdom_review',
			'title'          => 'Данные отзыва',
			'fields'         => array(
				artdom_f( 'rev_source', 'Источник', 'text', array( 'wrapper' => array( 'width' => 50 ), 'placeholder' => 'Отзыв с Яндекс' ) ),
				artdom_f(
					'rev_rating',
					'Оценка',
					'number',
					array( 'wrapper' => array( 'width' => 25 ), 'default_value' => 5, 'min' => 1, 'max' => 5 )
				),
				artdom_f( 'rev_initials', 'Инициалы', 'text', array( 'wrapper' => array( 'width' => 25 ), 'maxlength' => 2, 'instructions' => 'Пусто — соберём из имени.' ) ),
				artdom_f( 'rev_text', 'Текст отзыва', 'textarea', array( 'rows' => 5 ) ),
			),
			'location'       => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'artdom_review' ) ) ),
			'hide_on_screen' => array( 'the_content', 'excerpt', 'discussion', 'comments', 'custom_fields' ),
			'active'         => true,
		)
	);
}
add_action( 'acf/init', 'artdom_register_cpt_fields' );

/**
 * Инициалы из имени, если их не заполнили: «Анна С.» -> «АС».
 */
function artdom_initials( $name, $manual = '' ) {
	$manual = trim( (string) $manual );
	if ( '' !== $manual ) {
		return mb_strtoupper( mb_substr( $manual, 0, 2 ) );
	}
	$parts = preg_split( '/\s+/u', trim( (string) $name ) );
	$out   = '';
	foreach ( array_slice( $parts, 0, 2 ) as $p ) {
		$out .= mb_substr( $p, 0, 1 );
	}
	return mb_strtoupper( $out );
}

/**
 * Поля услуги и дополнительные поля объекта.
 *
 * Объекту нужны разобранные по смыслу поля — площадь, комнаты, район, — иначе
 * каталог нечем фильтровать и сортировать: свободный повторитель для этого
 * не годится, там значения лежат строками без имени.
 */
function artdom_register_inner_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'            => 'group_artdom_service',
			'title'          => 'Содержание услуги',
			'fields'         => array(
				artdom_f( 'svc_lead', 'Короткое описание', 'textarea', array( 'rows' => 3, 'instructions' => 'Показывается в аккордеоне на главной и в списке услуг.' ) ),
				artdom_f( 'svc_text', 'Основной текст', 'textarea', array( 'rows' => 10, 'instructions' => 'Пустая строка между абзацами разделит их на сайте.' ) ),
				artdom_f(
					'svc_steps',
					'Как проходит работа',
					'repeater',
					array(
						'layout'       => 'block',
						'button_label' => 'Добавить шаг',
						'sub_fields'   => array(
							artdom_f( 'title', 'Шаг', 'text', array( 'key' => 'field_artdom_ss_title' ) ),
							artdom_f( 'text', 'Пояснение', 'textarea', array( 'key' => 'field_artdom_ss_text', 'rows' => 2 ) ),
						),
					)
				),
				artdom_f(
					'svc_faq',
					'Вопросы и ответы',
					'repeater',
					array(
						'layout'       => 'block',
						'button_label' => 'Добавить вопрос',
						'sub_fields'   => array(
							artdom_f( 'q', 'Вопрос', 'text', array( 'key' => 'field_artdom_faq_q' ) ),
							artdom_f( 'a', 'Ответ', 'textarea', array( 'key' => 'field_artdom_faq_a', 'rows' => 3 ) ),
						),
					)
				),
			),
			'location'       => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'artdom_service' ) ) ),
			'hide_on_screen' => array( 'the_content', 'discussion', 'comments', 'custom_fields' ),
			'active'         => true,
		)
	);

	acf_add_local_field_group(
		array(
			'key'      => 'group_artdom_object_more',
			'title'    => 'Параметры объекта',
			'fields'   => array(
				artdom_f( 'obj_area', 'Площадь, м²', 'number', array( 'wrapper' => array( 'width' => 25 ) ) ),
				artdom_f( 'obj_rooms', 'Спален', 'number', array( 'wrapper' => array( 'width' => 25 ) ) ),
				artdom_f( 'obj_floor', 'Этаж', 'text', array( 'wrapper' => array( 'width' => 25 ), 'placeholder' => '12 из 24' ) ),
				artdom_f( 'obj_price_num', 'Цена, млн ₽', 'number', array( 'wrapper' => array( 'width' => 25 ), 'instructions' => 'Число для сортировки. Показывается поле «Цена».' ) ),
				artdom_f( 'obj_district', 'Район', 'text', array( 'wrapper' => array( 'width' => 34 ), 'placeholder' => 'Хамовники' ) ),
				artdom_f( 'obj_metro', 'Метро', 'text', array( 'wrapper' => array( 'width' => 33 ), 'placeholder' => 'Парк культуры' ) ),
				artdom_f( 'obj_complex', 'Жилой комплекс', 'text', array( 'wrapper' => array( 'width' => 33 ) ) ),
				artdom_f( 'obj_address', 'Адрес', 'text' ),
				artdom_f(
					'obj_gallery',
					'Галерея',
					'gallery',
					array( 'return_format' => 'array', 'instructions' => 'Первая фотография — обложка, если не задано изображение записи.' )
				),
			),
			'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'artdom_object' ) ) ),
			'active'   => true,
		)
	);
}
add_action( 'acf/init', 'artdom_register_inner_fields' );

/**
 * Поля страницы «О компании».
 *
 * Страница из одного текста для агентства премиального сегмента не годится:
 * она и есть главный аргумент доверия. Поэтому блоки разложены по смыслу —
 * вводная часть, цифры, принципы, путь, основатель, команда, — и каждый
 * редактируется отдельно.
 */
function artdom_register_about_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'            => 'group_artdom_about',
			'title'          => 'О компании',
			'fields'         => array(

				artdom_tab( 'Вводная часть' ),
				artdom_f( 'ab_intro_title', 'Заголовок', 'textarea', array( 'rows' => 2 ) ),
				artdom_f( 'ab_intro_text', 'Текст', 'textarea', array( 'rows' => 8, 'instructions' => 'Пустая строка между абзацами разделит их на сайте.' ) ),
				artdom_f( 'ab_photo', 'Фотография', 'image', array( 'return_format' => 'array', 'preview_size' => 'medium' ) ),

				artdom_tab( 'Принципы' ),
				artdom_f( 'ab_principles_title', 'Заголовок' ),
				artdom_f(
					'ab_principles',
					'Принципы',
					'repeater',
					array(
						'layout'       => 'block',
						'button_label' => 'Добавить принцип',
						'sub_fields'   => array(
							artdom_f( 'title', 'Название', 'text', array( 'key' => 'field_artdom_pr_title' ) ),
							artdom_f( 'text', 'Пояснение', 'textarea', array( 'key' => 'field_artdom_pr_text', 'rows' => 3 ) ),
						),
					)
				),

				artdom_tab( 'Путь компании' ),
				artdom_f( 'ab_path_title', 'Заголовок' ),
				artdom_f(
					'ab_path',
					'Вехи',
					'repeater',
					array(
						'layout'       => 'table',
						'button_label' => 'Добавить веху',
						'sub_fields'   => array(
							artdom_f( 'year', 'Год', 'text', array( 'key' => 'field_artdom_pt_year', 'wrapper' => array( 'width' => 15 ) ) ),
							artdom_f( 'title', 'Что произошло', 'text', array( 'key' => 'field_artdom_pt_title', 'wrapper' => array( 'width' => 35 ) ) ),
							artdom_f( 'text', 'Подробнее', 'textarea', array( 'key' => 'field_artdom_pt_text', 'rows' => 2, 'wrapper' => array( 'width' => 50 ) ) ),
						),
					)
				),

				artdom_tab( 'Команда' ),
				artdom_f( 'ab_team_title', 'Заголовок' ),
				artdom_f( 'ab_team_lead', 'Подводка', 'textarea', array( 'rows' => 3 ) ),
				artdom_f(
					'ab_team',
					'Сотрудники',
					'repeater',
					array(
						'layout'       => 'table',
						'button_label' => 'Добавить человека',
						'sub_fields'   => array(
							artdom_f( 'name', 'Имя', 'text', array( 'key' => 'field_artdom_tm_name', 'wrapper' => array( 'width' => 30 ) ) ),
							artdom_f( 'role', 'Должность', 'text', array( 'key' => 'field_artdom_tm_role', 'wrapper' => array( 'width' => 30 ) ) ),
							artdom_f( 'note', 'Коротко о человеке', 'text', array( 'key' => 'field_artdom_tm_note', 'wrapper' => array( 'width' => 40 ) ) ),
						),
					)
				),
			),
			'location'       => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'templates/template-about.php',
					),
				),
			),
			'hide_on_screen' => array( 'discussion', 'comments', 'custom_fields' ),
			'active'         => true,
			'description'    => 'Блоки страницы «О компании». Пустой блок просто не показывается.',
		)
	);
}
add_action( 'acf/init', 'artdom_register_about_fields' );

/**
 * Порядок в архивах — заданный руками, а не по дате.
 *
 * У услуг порядок содержательный: сперва основная, потом сопутствующие.
 * Архив по умолчанию сортирует по дате, и список выходил задом наперёд —
 * последняя созданная услуга оказывалась первой.
 *
 * @param WP_Query $query Основной запрос.
 */
function artdom_archive_order( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( $query->is_post_type_archive( 'artdom_service' ) || $query->is_post_type_archive( 'artdom_review' ) ) {
		$query->set( 'orderby', array( 'menu_order' => 'ASC', 'date' => 'ASC' ) );
	}

	if ( $query->is_post_type_archive( 'artdom_object' ) || $query->is_tax( 'artdom_object_type' ) ) {
		$query->set( 'orderby', array( 'menu_order' => 'ASC', 'date' => 'DESC' ) );
		$query->set( 'posts_per_page', 9 );
	}
}
add_action( 'pre_get_posts', 'artdom_archive_order' );
