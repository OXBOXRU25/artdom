<?php
/**
 * Поля админки — кодом, а не кликами.
 *
 * Так группы лежат в git, едут вместе с темой и поднимаются на боевом сами.
 * Иначе полсотни кликов повторяются на каждом сайте, и первое же расхождение
 * между локальной копией и боевым находится через месяц.
 *
 * @package artdom
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Короткие сборщики полей, чтобы не тонуть в повторе. */
function artdom_f( $name, $label, $type = 'text', $extra = array() ) {
	return array_merge(
		array(
			'key'   => 'field_artdom_' . $name,
			'name'  => $name,
			'label' => $label,
			'type'  => $type,
		),
		$extra
	);
}

function artdom_tab( $label ) {
	static $n = 0;
	++$n;
	return array(
		'key'       => 'field_artdom_tab_' . $n,
		'label'     => $label,
		'type'      => 'tab',
		'placement' => 'left',
	);
}

function artdom_register_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$multiline = array( 'instructions' => 'Перенос строки в поле станет переносом на сайте.' );
	$link      = array( 'instructions' => 'Можно якорь вида #objects или полный адрес.' );

	acf_add_local_field_group(
		array(
			'key'    => 'group_artdom_main',
			'title'  => 'Главная страница',
			'fields' => array(

				artdom_tab( 'Первый экран' ),
				artdom_f( 'hero_title', 'Заголовок', 'textarea', array( 'rows' => 4 ) + $multiline ),
				artdom_f( 'hero_lead', 'Подводка', 'textarea', array( 'rows' => 3 ) ),
				artdom_f( 'hero_btn_text', 'Кнопка: текст', 'textarea', array( 'rows' => 2 ) + $multiline ),
				artdom_f( 'hero_btn_link', 'Кнопка: ссылка', 'text', $link ),
				artdom_f( 'hero_video_webm', 'Видео WebM', 'file', array( 'return_format' => 'url', 'mime_types' => 'webm' ) ),
				artdom_f( 'hero_video_mp4', 'Видео MP4', 'file', array( 'return_format' => 'url', 'mime_types' => 'mp4' ) ),
				artdom_f( 'hero_poster', 'Постер видео', 'image', array( 'return_format' => 'array', 'preview_size' => 'medium' ) ),

				artdom_tab( 'Услуги' ),
				artdom_f( 'services_title', 'Заголовок' ),
				artdom_f( 'services_lead', 'Подводка', 'textarea', array( 'rows' => 3 ) ),
				artdom_f( 'services_photo', 'Фотография', 'image', array( 'return_format' => 'array', 'preview_size' => 'medium' ) ),
				artdom_f(
					'services_items',
					'Пункты',
					'repeater',
					array(
						'layout'       => 'block',
						'button_label' => 'Добавить услугу',
						'sub_fields'   => array(
							artdom_f( 'title', 'Название', 'text', array( 'key' => 'field_artdom_si_title', 'wrapper' => array( 'width' => 100 ) ) ),
							artdom_f( 'text', 'Описание', 'textarea', array( 'key' => 'field_artdom_si_text', 'rows' => 3 ) ),
							artdom_f( 'btn_text', 'Кнопка: текст', 'text', array( 'key' => 'field_artdom_si_btn', 'wrapper' => array( 'width' => 50 ) ) ),
							artdom_f( 'btn_link', 'Кнопка: ссылка', 'text', array( 'key' => 'field_artdom_si_link', 'wrapper' => array( 'width' => 50 ) ) ),
						),
					)
				),

				artdom_tab( 'Избранные объекты' ),
				artdom_f( 'objects_title', 'Заголовок' ),
				artdom_f( 'objects_lead', 'Подводка', 'textarea', array( 'rows' => 3 ) ),
				artdom_f( 'objects_btn_text', 'Кнопка: текст', 'textarea', array( 'rows' => 2 ) + $multiline ),
				artdom_f( 'objects_btn_link', 'Кнопка: ссылка', 'text', $link ),
				artdom_f(
					'objects_count',
					'Сколько показывать',
					'number',
					array( 'default_value' => 6, 'min' => 3, 'max' => 12, 'instructions' => 'Объекты берутся из раздела «Объекты», свежие сверху.' )
				),

				artdom_tab( 'О компании' ),
				artdom_f( 'about_title', 'Заголовок', 'textarea', array( 'rows' => 2 ) + $multiline ),
				artdom_f( 'about_portrait', 'Портрет', 'image', array( 'return_format' => 'array', 'preview_size' => 'thumbnail' ) ),
				artdom_f( 'about_quote', 'Цитата', 'textarea', array( 'rows' => 4 ) ),
				artdom_f( 'about_name', 'Имя', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
				artdom_f( 'about_role', 'Должность', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
				artdom_f( 'about_text', 'Текст', 'textarea', array( 'rows' => 12, 'instructions' => 'Пустая строка между абзацами разделит их на сайте.' ) ),
				artdom_f( 'about_btn_text', 'Кнопка: текст', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
				artdom_f( 'about_btn_link', 'Кнопка: ссылка', 'text', array( 'wrapper' => array( 'width' => 50 ) ) + $link ),

				artdom_tab( 'Надёжность' ),
				artdom_f(
					'guaranty_slides',
					'Кадры',
					'repeater',
					array(
						'layout'       => 'block',
						'min'          => 1,
						'button_label' => 'Добавить кадр',
						'instructions' => 'Блок закрепляется, и кадры сменяются по мере прокрутки. Фотографии лучше тёмные: заголовок на них белый.',
						'sub_fields'   => array(
							artdom_f( 'image', 'Фотография', 'image', array( 'key' => 'field_artdom_gs_img', 'return_format' => 'array', 'preview_size' => 'medium', 'wrapper' => array( 'width' => 40 ) ) ),
							artdom_f( 'title', 'Заголовок', 'textarea', array( 'key' => 'field_artdom_gs_title', 'rows' => 3, 'wrapper' => array( 'width' => 60 ) ) ),
						),
					)
				),

				artdom_tab( 'Цифры' ),
				artdom_f(
					'stats_items',
					'Показатели',
					'repeater',
					array(
						'layout'       => 'table',
						'button_label' => 'Добавить показатель',
						'instructions' => 'Число досчитывается от нуля при появлении блока. Приставки и хвосты вроде «+» и «&lt;» сохраняются.',
						'sub_fields'   => array(
							artdom_f( 'number', 'Число', 'text', array( 'key' => 'field_artdom_st_num', 'wrapper' => array( 'width' => 25 ) ) ),
							artdom_f( 'label', 'Подпись', 'textarea', array( 'key' => 'field_artdom_st_label', 'rows' => 2, 'wrapper' => array( 'width' => 75 ) ) ),
						),
					)
				),

				artdom_tab( 'Отзывы' ),
				artdom_f( 'reviews_title', 'Заголовок' ),
				artdom_f( 'reviews_rating', 'Оценка', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
				artdom_f( 'reviews_count', 'На основе скольких отзывов', 'text', array( 'wrapper' => array( 'width' => 50 ) ) ),
				artdom_f( 'reviews_btn_text', 'Кнопка: текст', 'textarea', array( 'rows' => 2 ) + $multiline ),
				artdom_f( 'reviews_btn_link', 'Кнопка: ссылка', 'text', $link ),

				artdom_tab( 'Подвал' ),
				artdom_f( 'cta1_title', 'Левый блок: заголовок' ),
				artdom_f( 'cta1_text', 'Левый блок: текст', 'textarea', array( 'rows' => 3 ) ),
				artdom_f( 'cta1_btn_text', 'Левый блок: кнопка' ),
				artdom_f( 'cta2_title', 'Правый блок: заголовок' ),
				artdom_f( 'cta2_text', 'Правый блок: текст', 'textarea', array( 'rows' => 3 ) ),
				artdom_f( 'cta2_btn_text', 'Правый блок: кнопка', 'textarea', array( 'rows' => 2 ) + $multiline ),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'templates/template-mainpage.php',
					),
				),
			),
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'hide_on_screen'        => array( 'the_content', 'excerpt', 'discussion', 'comments', 'author', 'format', 'featured_image', 'tags', 'send-trackbacks' ),
			'active'                => true,
			'description'           => 'Тексты и картинки главной. Пустое поле показывает текст из макета, страница не ломается.',
		)
	);

	/* ---------- Общие настройки сайта ---------- */
	acf_add_local_field_group(
		array(
			'key'      => 'group_artdom_options',
			'title'    => 'Контакты и подвал',
			'fields'   => array(
				artdom_f( 'opt_phone', 'Телефон', 'text', array( 'wrapper' => array( 'width' => 34 ) ) ),
				artdom_f( 'opt_email', 'Почта', 'text', array( 'wrapper' => array( 'width' => 33 ) ) ),
				artdom_f( 'opt_address', 'Адрес', 'text', array( 'wrapper' => array( 'width' => 33 ) ) ),
				artdom_f( 'opt_copyright', 'Строка копирайта' ),
				artdom_f(
					'opt_email_send',
					'Куда слать заявки',
					'email',
					array( 'instructions' => 'Пусто — письма пойдут на адрес из поля «Почта», а если и он пуст — на почту администратора. Заявки в любом случае сохраняются в разделе «Заявки», даже если письмо не ушло.' )
				),
				artdom_f(
					'opt_socials',
					'Мессенджеры и соцсети',
					'repeater',
					array(
						'layout'       => 'table',
						'button_label' => 'Добавить',
						'sub_fields'   => array(
							artdom_f( 'label', 'Название', 'text', array( 'key' => 'field_artdom_soc_label', 'wrapper' => array( 'width' => 35 ) ) ),
							artdom_f( 'url', 'Ссылка', 'text', array( 'key' => 'field_artdom_soc_url', 'wrapper' => array( 'width' => 65 ) ) ),
						),
					)
				),
				artdom_f(
					'opt_legal',
					'Правовые документы',
					'repeater',
					array(
						'layout'       => 'table',
						'button_label' => 'Добавить',
						'sub_fields'   => array(
							artdom_f( 'label', 'Название', 'text', array( 'key' => 'field_artdom_leg_label', 'wrapper' => array( 'width' => 55 ) ) ),
							artdom_f( 'url', 'Ссылка', 'text', array( 'key' => 'field_artdom_leg_url', 'wrapper' => array( 'width' => 45 ) ) ),
						),
					)
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'artdom-settings',
					),
				),
			),
			'active'   => true,
		)
	);
}
add_action( 'acf/init', 'artdom_register_fields' );

/** Страница общих настроек. */
function artdom_register_options_page() {
	if ( ! function_exists( 'acf_add_options_page' ) ) {
		return;
	}
	acf_add_options_page(
		array(
			'page_title'      => 'Настройки сайта',
			'menu_title'      => 'Настройки сайта',
			'menu_slug'       => 'artdom-settings',
			'position'        => 16,
			'icon_url'        => 'dashicons-admin-settings',
			'redirect'        => false,
			'update_button'   => 'Сохранить',
			'updated_message' => 'Сохранено',
		)
	);
}
add_action( 'acf/init', 'artdom_register_options_page' );
