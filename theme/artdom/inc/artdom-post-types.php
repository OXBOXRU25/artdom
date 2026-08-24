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
			'public'        => false,
			'show_ui'       => true,
			'menu_icon'     => 'dashicons-format-quote',
			'menu_position' => 21,
			'supports'      => array( 'title', 'page-attributes' ),
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
