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
				/* Полей «Оценка» и «На основе скольких отзывов» здесь больше
				   нет, и это не потеря. Оба числа сайт считает сам по
				   опубликованным отзывам, а поля стояли рядом и ничего не
				   меняли: заказчик мог вписать 4.9 и удивляться, почему на
				   сайте 4.7. Убрано вместе с «Кнопка: ссылка» — та кнопка
				   открывает форму отзыва, и ссылке некуда вести. */
				artdom_f( 'reviews_btn_text', 'Кнопка: текст', 'textarea', array( 'rows' => 2 ) + $multiline ),

				artdom_tab( 'Блог' ),
				artdom_f( 'blog_title', 'Заголовок' ),
				artdom_f( 'blog_lead', 'Подводка', 'textarea', array( 'rows' => 3 ) ),
				artdom_f( 'blog_btn_text', 'Кнопка: текст', 'textarea', array( 'rows' => 2 ) + $multiline ),

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
					'cookie_text',
					'Плашка про cookie: текст',
					'textarea',
					array(
						'rows'         => 2,
						'instructions' => 'Показывается один раз в углу и прячется после нажатия. Пусто — возьмём текст по умолчанию. Появится Яндекс.Метрика — текст надо переписать: сейчас в нём сказано, что счётчиков нет.',
					)
				),
				artdom_f( 'cookie_link', 'Плашка про cookie: надпись ссылки', 'text', array( 'wrapper' => array( 'width' => 60 ), 'instructions' => 'Ведёт на первый документ из списка правовых ниже.' ) ),
				artdom_f( 'cookie_btn', 'Плашка про cookie: кнопка', 'text', array( 'wrapper' => array( 'width' => 40 ), 'instructions' => 'Надпись на кнопке, пока счётчик не подключён.' ) ),
				/* Пока счётчик не вписан, на плашке одна кнопка; как только
				   вписан — их становится две, и надписи для них брались из
				   запасных текстов без единого поля в админке. Заказчик видел
				   на сайте «Принять» и «Отклонить» и не мог их изменить. */
				artdom_f( 'cookie_btn_yes', 'Плашка про cookie: кнопка согласия', 'text', array( 'wrapper' => array( 'width' => 50 ), 'instructions' => 'Появляется вместо кнопки выше, когда подключён счётчик посещаемости. Нажатие включает счётчик.' ) ),
				artdom_f( 'cookie_btn_no', 'Плашка про cookie: кнопка отказа', 'text', array( 'wrapper' => array( 'width' => 50 ), 'instructions' => 'Вторая кнопка. Нажатие закрывает плашку и счётчик не включает.' ) ),
				artdom_f( 'cookie_btn_manage', 'Плашка про cookie: кнопка «Настроить»', 'text', array( 'wrapper' => array( 'width' => 50 ), 'instructions' => 'Раскрывает список категорий прямо в плашке.' ) ),
				artdom_f( 'cookie_btn_save', 'Плашка про cookie: кнопка сохранения выбора', 'text', array( 'wrapper' => array( 'width' => 50 ), 'instructions' => 'Появляется в раскрытом списке категорий.' ) ),
				artdom_f( 'cookie_cat_need', 'Категория: необходимые', 'text', array( 'instructions' => 'Первая строка в раскрытом списке. Галочка стоит всегда и снять её нельзя — без этих cookie сайт не работает.' ) ),
				artdom_f( 'cookie_cat_stat', 'Категория: аналитика', 'text', array( 'instructions' => 'Вторая строка. Показывается, только когда вписан номер счётчика.' ) ),
				artdom_f( 'cookie_cat_none', 'Список категорий без счётчика', 'textarea', array( 'rows' => 2, 'instructions' => 'Что написано в раскрытом списке, пока счётчик не подключён и настраивать нечего.' ) ),
				artdom_f(
					'opt_metrika',
					'Яндекс.Метрика: номер счётчика',
					'text',
					array(
						'instructions' => 'Только цифры, например 12345678. Вписали — счётчик заработает, а плашка про cookie сама перестроится: текст про аналитику и две кнопки, «Принять» и «Отклонить». До нажатия «Принять» счётчик НЕ загружается и cookie не ставит. Пусто — счётчика нет.',
					)
				),
				artdom_f( 'opt_seo_desc', 'Описание сайта для поиска', 'textarea', array( 'rows' => 2, 'instructions' => 'Показывается на главной и там, где у страницы нет своего описания.' ) ),
				artdom_f( 'opt_seo_image', 'Картинка для ссылок', 'image', array( 'return_format' => 'url', 'instructions' => 'Подставляется, когда ссылку на сайт кидают в мессенджер. Годится 1200x630.' ) ),
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
					'opt_next',
					'Переход в разделы (в подвале)',
					'repeater',
					array(
						'layout'       => 'block',
						'max'          => 3,
						'button_label' => 'Добавить раздел',
						'instructions' => 'Крупные карточки над подвалом. Пусто — покажем «Объекты» и «Услуги».',
						'sub_fields'   => array(
							artdom_f( 'label', 'Название', 'text', array( 'key' => 'field_artdom_nx_label', 'wrapper' => array( 'width' => 30 ) ) ),
							artdom_f( 'url', 'Ссылка', 'text', array( 'key' => 'field_artdom_nx_url', 'wrapper' => array( 'width' => 70 ) ) ),
							artdom_f( 'text', 'Пояснение', 'textarea', array( 'key' => 'field_artdom_nx_text', 'rows' => 2 ) ),
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

/**
 * Надписи разделов — вторая коробка на той же странице настроек.
 *
 * 16.09.2026, после замечания заказчика, что многое нельзя поправить из
 * админки. Всё это раньше стояло литералами в шаблонах: заголовки разделов,
 * подписи характеристик объекта, полосы призыва, пустые состояния, текст
 * согласия под формой.
 *
 * Отдельной группой, а не довеском к «Контактам и подвалу»: там уже полтора
 * десятка полей, и ещё тридцать сверху превратили бы страницу в простыню.
 * Здесь же вкладки слева, по разделам сайта.
 *
 * Правило для всех полей ниже: пусто — берётся запасной текст из
 * artdom_defaults(), страница не пустеет. Об этом сказано в подсказках,
 * потому что заказчик ведёт сайт сам и проверить это ему негде.
 */
function artdom_register_labels_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$empty_hint = 'Оставьте пустым — на сайте останется надпись по умолчанию.';

	acf_add_local_field_group(
		array(
			'key'      => 'group_artdom_labels',
			'title'    => 'Надписи разделов',
			'fields'   => array(

				artdom_tab( 'Заголовки разделов' ),
				artdom_f( 'page_objects_title', 'Каталог объектов', 'text', array( 'instructions' => 'Заголовок над списком объектов. ' . $empty_hint ) ),
				artdom_f( 'page_services_title', 'Услуги', 'text', array( 'instructions' => $empty_hint ) ),
				artdom_f( 'page_reviews_title', 'Отзывы', 'text', array( 'instructions' => $empty_hint ) ),
				artdom_f( 'page_reviews_lead', 'Отзывы: текст под заголовком', 'textarea', array( 'rows' => 2, 'instructions' => $empty_hint ) ),
				artdom_f( 'page_blog_title', 'Блог', 'text', array( 'instructions' => $empty_hint ) ),
				artdom_f( 'page_search_title', 'Страница поиска', 'text', array( 'instructions' => $empty_hint ) ),
				artdom_f( 'reviews_add_btn', 'Отзывы: кнопка «Оставить отзыв»', 'text', array( 'wrapper' => array( 'width' => 50 ), 'instructions' => $empty_hint ) ),
				artdom_f( 'reviews_more_btn', 'Отзывы: кнопка «Показать ещё»', 'text', array( 'wrapper' => array( 'width' => 50 ), 'instructions' => $empty_hint ) ),

				artdom_tab( 'Карточка объекта' ),
				artdom_f( 'obj_about_title', 'Заголовок над описанием', 'text', array( 'wrapper' => array( 'width' => 50 ), 'instructions' => $empty_hint ) ),
				artdom_f( 'obj_specs_title', 'Заголовок над таблицей', 'text', array( 'wrapper' => array( 'width' => 50 ), 'instructions' => $empty_hint ) ),
				artdom_f( 'obj_lbl_area', 'Подпись: площадь', 'text', array( 'wrapper' => array( 'width' => 25 ) ) ),
				artdom_f( 'obj_lbl_rooms', 'Подпись: спальни', 'text', array( 'wrapper' => array( 'width' => 25 ) ) ),
				artdom_f( 'obj_lbl_floor', 'Подпись: этаж', 'text', array( 'wrapper' => array( 'width' => 25 ) ) ),
				artdom_f( 'obj_lbl_year', 'Подпись: год', 'text', array( 'wrapper' => array( 'width' => 25 ) ) ),
				artdom_f( 'obj_lbl_complex', 'Подпись: жилой комплекс', 'text', array( 'wrapper' => array( 'width' => 34 ) ) ),
				artdom_f( 'obj_lbl_district', 'Подпись: район', 'text', array( 'wrapper' => array( 'width' => 33 ) ) ),
				artdom_f( 'obj_lbl_metro', 'Подпись: метро', 'text', array( 'wrapper' => array( 'width' => 33 ) ) ),
				artdom_f( 'obj_btn', 'Кнопка в панели с ценой', 'text', array( 'wrapper' => array( 'width' => 50 ), 'instructions' => 'Открывает форму записи. ' . $empty_hint ) ),
				artdom_f( 'obj_similar_title', 'Заголовок ленты похожих объектов', 'text', array( 'wrapper' => array( 'width' => 50 ), 'instructions' => $empty_hint ) ),
				artdom_f( 'obj_btn_note', 'Строка под кнопкой', 'textarea', array( 'rows' => 2, 'instructions' => 'Мелкий текст под кнопкой записи. ' . $empty_hint ) ),

				artdom_tab( 'Страница услуги' ),
				artdom_f( 'svc_steps_title', 'Заголовок над этапами работы', 'text', array( 'instructions' => 'Показывается, только если у услуги заполнены этапы. ' . $empty_hint ) ),
				artdom_f( 'svc_faq_title', 'Заголовок над вопросами и ответами', 'text', array( 'instructions' => 'Показывается, только если у услуги заполнены вопросы. ' . $empty_hint ) ),
				artdom_f( 'obj_price_label', 'Подпись перед ценой', 'text', array( 'instructions' => 'Стоит перед стоимостью услуги, например «Стоимость». Саму цену пишут у каждой услуги отдельно. ' . $empty_hint ) ),

				artdom_tab( 'Полосы призыва' ),
				artdom_f( 'cta_title', 'Обычная: заголовок', 'text', array( 'instructions' => 'Тёмная полоса с кнопкой внизу внутренних страниц. ' . $empty_hint ) ),
				artdom_f( 'cta_text', 'Обычная: текст', 'textarea', array( 'rows' => 2 ) ),
				artdom_f( 'cta_btn', 'Обычная: кнопка', 'text' ),
				artdom_f( 'cta_obj_title', 'На странице объекта: заголовок', 'text' ),
				artdom_f( 'cta_obj_text', 'На странице объекта: текст', 'textarea', array( 'rows' => 2 ) ),
				artdom_f( 'cta_obj_btn', 'На странице объекта: кнопка', 'text' ),
				artdom_f( 'cta_svc_title', 'На странице услуги: заголовок', 'text' ),
				artdom_f( 'cta_svc_text', 'На странице услуги: текст', 'textarea', array( 'rows' => 2 ) ),
				artdom_f( 'cta_svc_btn', 'На странице услуги: кнопка', 'text' ),

				artdom_tab( 'Когда показывать нечего' ),
				artdom_f( 'empty_objects', 'В каталоге нет объектов', 'textarea', array( 'rows' => 2, 'instructions' => 'Увидит посетитель, если в категории пусто. ' . $empty_hint ) ),
				artdom_f( 'empty_reviews', 'Нет отзывов', 'text', array( 'instructions' => $empty_hint ) ),
				artdom_f( 'empty_section', 'Раздел пока пуст', 'text', array( 'instructions' => 'Общая надпись для блога и услуг. ' . $empty_hint ) ),
				artdom_f( 'empty_search', 'Поиск ничего не нашёл', 'textarea', array( 'rows' => 2, 'instructions' => $empty_hint ) ),
				artdom_f( 'empty_archive', 'Пустой список записей', 'text', array( 'instructions' => 'Общая надпись для рубрик и архивов по датам. ' . $empty_hint ) ),
				artdom_f( 'err404_title', 'Страница не найдена: заголовок', 'text', array( 'instructions' => 'Что увидит человек, открыв несуществующий адрес. ' . $empty_hint ) ),
				artdom_f( 'err404_text', 'Страница не найдена: текст', 'textarea', array( 'rows' => 2 ) ),
				artdom_f( 'err404_btn', 'Страница не найдена: кнопка', 'text' ),

				artdom_tab( 'Шапка и контакты' ),
				artdom_f( 'hdr_btn', 'Кнопка в шапке сайта', 'text', array( 'instructions' => 'Синяя кнопка справа вверху. Открывает форму заявки. ' . $empty_hint ) ),
				artdom_f( 'services_item_btn', 'Кнопка у услуги на главной', 'text', array( 'instructions' => 'Надпись под каждой услугой в списке на главной. ' . $empty_hint ) ),
				artdom_f( 'ct_lbl_phone', 'Подпись: телефон', 'text', array( 'wrapper' => array( 'width' => 25 ) ) ),
				artdom_f( 'ct_lbl_email', 'Подпись: почта', 'text', array( 'wrapper' => array( 'width' => 25 ) ) ),
				artdom_f( 'ct_lbl_address', 'Подпись: адрес', 'text', array( 'wrapper' => array( 'width' => 25 ) ) ),
				artdom_f( 'ct_lbl_socials', 'Подпись: мессенджеры', 'text', array( 'wrapper' => array( 'width' => 25 ) ) ),
				artdom_f( 'ct_map_link', 'Надпись ссылки на карты', 'text', array( 'instructions' => 'Строка под адресом. ' . $empty_hint ) ),
				artdom_f( 'search_placeholder', 'Поиск: подсказка в поле', 'text', array( 'instructions' => 'Серый текст внутри поля поиска, пока в него не начали печатать. ' . $empty_hint ) ),

				artdom_tab( 'Форма' ),
				artdom_f( 'form_consent', 'Согласие: текст', 'text', array( 'instructions' => 'Надпись рядом с галочкой под формой. Ссылка на политику подставляется следующим полем. ' . $empty_hint ) ),
				artdom_f( 'form_consent_link', 'Согласие: надпись ссылки', 'text', array( 'instructions' => 'Ведёт на первый документ из списка правовых во вкладке «Контакты и подвал». ' . $empty_hint ) ),
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
add_action( 'acf/init', 'artdom_register_labels_fields' );

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

/**
 * Поля для поиска и ссылок — на страницах, записях, объектах и услугах.
 *
 * Без них заказчик не мог задать ни описание в выдаче, ни картинку, которая
 * подставляется, когда ссылку кидают в мессенджер. Пусто — берём отрывок и
 * изображение записи, поэтому заполнять необязательно.
 */
function artdom_register_seo_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'         => 'group_artdom_seo',
			'title'       => 'Для поиска и ссылок',
			'description' => 'Как страница выглядит в поиске и когда ссылку отправляют в мессенджер. Пустые поля берутся из самой страницы.',
			'fields'      => array(
				artdom_f( 'seo_title', 'Заголовок в поиске', 'text', array( 'instructions' => 'Пусто — берётся название страницы.' ) ),
				artdom_f( 'seo_desc', 'Описание', 'textarea', array( 'rows' => 2, 'instructions' => 'Полторы строки, до 160 знаков: длиннее поиск обрезает.' ) ),
				artdom_f( 'seo_image', 'Картинка для ссылки', 'image', array( 'return_format' => 'url', 'instructions' => 'Пусто — берётся изображение записи, а если и его нет, общая из настроек сайта.' ) ),
			),
			'location'    => array(
				array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'page' ) ),
				array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'post' ) ),
				array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'artdom_object' ) ),
				array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'artdom_service' ) ),
			),
			'active'      => true,
		)
	);
}
add_action( 'acf/init', 'artdom_register_seo_fields' );
