<?php
/**
 * Тексты макета в одном месте.
 *
 * Зачем так: пустое поле в админке не должно обнулять страницу — заказчик,
 * стерев текст, увидит прежний, а не дыру. Но тогда одни и те же строки
 * оказываются в двух местах и незаметно расходятся: после заполнения полей
 * запасной вариант уже не показывается и о нём забывают.
 *
 * Поэтому запас живёт ровно здесь, а читают отсюда и шаблоны (через
 * artdom_field), и скрипт первичного заполнения полей.
 *
 * @package artdom
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function artdom_defaults() {
	static $d = null;
	if ( null !== $d ) {
		return $d;
	}

	$d = array(

		/* ---------- Первый экран ---------- */
		'hero_title'      => "Дом&nbsp;— это\nпроизведение\nискусства, а&nbsp;не\nквадратные метры.",
		'hero_lead'       => 'Подбираем и сопровождаем сделки с премиальной недвижимостью: клубные дома, резиденции и апартаменты в лучших локациях города.',
		'hero_btn_text'   => "Смотреть\nготовые объекты",
		'hero_btn_link'   => '#objects',

		/* ---------- Услуги ---------- */
		'services_title'  => 'Услуги',
		'services_lead'   => 'Ведём клиента от подбора объекта до регистрации права&nbsp;— сами или через проверенных партнёров.',
		'services_items'  => array(
			array(
				'title'    => 'Покупка и покупка',
				'text'     => 'Подбор и сопровождение сделок с премиальной недвижимостью&nbsp;— от показа объекта до подписания и регистрации.',
				'btn_text' => 'Узнать больше',
				'btn_link' => '#',
			),
			array(
				'title'    => 'Новостройки',
				'text'     => 'Прямые договоры с застройщиками, брони на старте продаж и проверка проектной документации до внесения средств.',
				'btn_text' => 'Узнать больше',
				'btn_link' => '#',
			),
			array(
				'title'    => 'Ипотека и кредиты',
				'text'     => 'Подбор программы и банка, предварительное одобрение и сопровождение до выдачи средств.',
				'btn_text' => 'Узнать больше',
				'btn_link' => '#',
			),
			array(
				'title'    => 'Юридическое сопровождение',
				'text'     => 'Проверка истории объекта и продавца, подготовка договора, безопасные расчёты и регистрация права.',
				'btn_text' => 'Узнать больше',
				'btn_link' => '#',
			),
			array(
				'title'    => 'Оценка недвижимости',
				'text'     => 'Отчёт об оценке для банка и суда, а также экспресс-расчёт справедливой цены перед выходом на рынок.',
				'btn_text' => 'Узнать больше',
				'btn_link' => '#',
			),
		),

		/* ---------- Избранные объекты ---------- */
		'objects_title'   => 'Избранные объекты',
		/* Здесь стоял лид секции «Услуги» слово в слово — скопирован при
		   заведении запасных значений. Две разные секции обещали одно и то же,
		   и на телефоне, где они идут подряд, это читалось как сбой вёрстки. */
		'objects_lead'    => 'Квартиры, резиденции и загородные дома в продаже. Каждый объект проверен юридически до первого показа.',
		'objects_btn_text'=> "Смотреть\nготовые объекты",
		'objects_btn_link'=> '#',

		/* ---------- О компании ---------- */
		'about_title'     => "Экспертные решения\nв сфере недвижимости",
		'about_quote'     => '«Хороший брокер не показывает десять квартир подряд&nbsp;— он приходит с двумя, и одна из них уже именно та».',
		'about_name'      => 'Мария Артемьева',
		'about_role'      => 'Основатель',
		'about_text'      => "Компания «АРТДОМ» родилась путем объединения усилий ведущих экспертов и лиц руководящего состава крупных московских Агентств. За годы совместной работы сложилась Команда, получившая огромное количество отзывов от благодарных клиентов и, у которой, сложилось собственное понимание целей.\n\nТри кита, на которых строится наша работа&nbsp;— это ВЫГОДА, БЕЗОПАСНОСТЬ и ИНТЕРЕСЫ наших клиентов. Наша миссия&nbsp;— предоставить клиентам надежные и качественные услуги в сфере недвижимости, вдохновляя их на осуществление мечты о доме, придавая им уверенность в будущем. Создавая для клиентов безопасную и комфортную среду, мы обеспечиваем профессиональный подход, прозрачность сделок и высокий уровень сервиса.\n\nМы привыкли, что нам доверяют, рекомендуют друзьям и родственникам. Топовые места в рейтинге Российской гильдии риэлторов означают, что мы на правильном пути!\n\nОкеан недвижимости для нас&nbsp;— родная стихия, мы умеем находить в нем нужные пути, открываем перед клиентами поистине безграничные возможности и помогаем клиентам выбрать идеальное решение для их нужд и желаний.\n\nСвяжитесь с нами сегодня, и откройте для себя новый мир возможностей!",
		'about_btn_text'  => 'Узнать больше',
		'about_btn_link'  => '#contacts',

		/* ---------- Надёжность: закреплённая сцена ----------
		   Второй и третий кадр временные: в макете их нет. */
		'guaranty_slides' => array(
			/* Кадры сцены раскрываются на весь экран и в переходе доезжают до
			   увеличения 1.4, поэтому исходник обязан быть широким. Прежние
			   uslugi.webp (710x722) и object.webp (580x395) сюда попали из
			   других секций и растягивались втрое — отсюда и мыло. */
			/* Все три заголовка в две строки нарочно: панели сменяются шторкой,
			   тексты стоят на одном месте экрана, и разное число строк читалось
			   бы на переходе как перепутанные фразы. */
			array( 'title' => "Надежность\nи гарантии",                 'image' => 'garanty.webp' ),
			array( 'title' => "Проверяем объект\nдо внесения задатка",  'image' => 'garanty-2.webp' ),
			array( 'title' => "Сопровождаем\nдо регистрации права",     'image' => 'garanty-3.webp' ),
		),

		/* ---------- Цифры ---------- */
		'stats_items'     => array(
			array( 'number' => '14+',  'label' => 'Лет на рынке премиальной недвижимости' ),
			array( 'number' => '120+', 'label' => 'Реализованных объектов в портфеле' ),
			array( 'number' => '38',   'label' => "Млн ₽\nСредний бюджет сделки" ),
			array( 'number' => '<1',   'label' => 'Среднее время ответа, меньше часа' ),
		),

		/* ---------- Отзывы ---------- */
		'reviews_title'   => 'Честная оценка',
		'reviews_rating'  => '4.9',
		'reviews_count'   => '86',
		'reviews_btn_text'=> "Добавить\nсвой отзыв",
		'reviews_btn_link'=> '#',

		/* ---------- Плашка про cookie ----------
		   Текст и набор кнопок зависят от того, вписан ли номер счётчика в
		   «Контакты и подвал». Пусто — сайт ничего не отслеживает, и плашка
		   говорит об этом честно, с одной кнопкой: отказываться не от чего.
		   Номер вписан — появляется вторая кнопка «Отклонить», и счётчик
		   грузится ТОЛЬКО после согласия. Оба текста ниже подставляются
		   запасными; заказчик может переписать любой своим. */
		/* 21.09.2026 обе строки укорочены на одну фразу. Причина замеренная:
		   у образца плашка 46 пикселей в высоту при тексте в 55 знаков, а
		   наши 118 рвались на две строки и давали 57. Убранное не потеряно —
		   «счётчиков нет» и «без согласия не запускается» теперь стоят в
		   раскрытом списке категорий, где их и ищут. */
		'cookie_text'     => 'Только технические cookie — без них сайт не работает.',
		'cookie_text_analytics' => 'Считаем посещаемость Яндекс.Метрикой — с вашего согласия.',
		'cookie_link'     => 'Подробнее',
		'cookie_btn'      => 'Понятно',
		'cookie_btn_yes'  => 'Принять',
		'cookie_btn_no'   => 'Отклонить',
		'cookie_reopen'   => 'Cookie',

		/* Номер счётчика Яндекс.Метрики. Только цифры. Пусто — счётчика нет
		   и плашка про аналитику не говорит. */
		'opt_metrika'     => '',

		/* ---------- Блог ---------- */
		'blog_title'      => 'Разбираем сделки',
		'blog_lead'       => 'То, что обычно остаётся за кадром сделки: документы, форматы домов, деньги и работа брокера.',
		'blog_btn_text'   => "Все статьи",

		/* ---------- Подвал: две карточки призыва ---------- */
		/* Контакты */
		'contacts_chip'  => 'Берём новые обращения',
		'contacts_title' => "Всё начинается
с разговора",
		'contacts_lead'  => "Расскажите, что ищете, — и мы поймём, есть ли у нас такой объект сегодня или его стоит подождать.

Ответим в течение часа в рабочее время.",
		'contacts_form_title' => 'Заполните форму',
		'contacts_broker_note' => 'Ваш персональный брокер',
		'ct_org'     => 'АРТДОМ, агентство недвижимости',
		'ct_address' => "Пресненская набережная, 8с1
Башня «Город столиц», 14 этаж
Москва, 123112",
		'ct_claim'   => "Офис в Москве.
Сделки — по всей России.",
		'ct_tz'      => 'Сейчас в Москве',
		'ct_map_caption' => 'Города, где мы закрывали сделки за последние три года',
		'ct_next_title'  => 'Дальше',

		'cta1_title'      => 'Готовы найти свой дом?',
		'cta1_text'       => 'Оставьте контакт&nbsp;— персональный брокер свяжется с вами и подберёт объекты под ваш запрос.',
		'cta1_btn_text'   => 'Оставить заявку',
		'cta2_title'      => 'Подборка объектов на почту',
		'cta2_text'       => 'Раз в неделю&nbsp;— новые лоты, изменения цен и закрытые предложения, которые не публикуются в открытом каталоге. Без спама, отписаться можно в любой момент.',
		'cta2_btn_text'   => "Подписаться\nна рассылку",

		/* ---------- Надписи разделов ----------
		   16.09.2026. Всё, что ниже, раньше стояло литералами прямо в
		   шаблонах: заказчик видел текст на сайте и не мог его тронуть.
		   Ключи собраны здесь по тому же правилу, что и остальные, — запас
		   живёт в одном месте, а поля в админке читают отсюда. */

		/* Заголовки разделов */
		'page_objects_title'  => 'Объекты',
		'page_services_title' => 'Услуги',
		'page_reviews_title'  => 'Отзывы',
		'page_reviews_lead'   => 'Отзывы приходят с Яндекс.Карт, из Авито и напрямую от клиентов. Публикуем как есть.',
		'page_blog_title'     => 'Блог',
		'page_search_title'   => 'Поиск',

		/* Подписи характеристик в карточке объекта */
		'obj_lbl_area'     => 'Площадь',
		'obj_lbl_rooms'    => 'Спален',
		'obj_lbl_floor'    => 'Этаж',
		'obj_lbl_complex'  => 'Жилой комплекс',
		'obj_lbl_district' => 'Район',
		'obj_lbl_metro'    => 'Метро',
		'obj_lbl_year'     => 'Год',
		'obj_about_title'  => 'Об объекте',
		'obj_specs_title'  => 'Характеристики',
		'obj_btn'          => 'Записаться на просмотр',
		'obj_btn_note'     => 'Ответим в течение часа. Показ&nbsp;— в удобное вам время, включая выходные.',
		'obj_similar_title' => 'Похожие объекты',
		'obj_price_label'  => 'Стоимость',

		/* Заголовки блоков на странице услуги */
		'svc_steps_title' => 'Как проходит работа',
		'svc_faq_title'   => 'Вопросы и ответы',

		/* Кнопки на странице отзывов */
		'reviews_add_btn'  => 'Оставить отзыв',
		'reviews_more_btn' => 'Показать ещё',

		/* Полоса призыва внизу внутренних страниц. Три набора: общий,
		   на карточке объекта и на странице услуги — призыв там разный. */
		'cta_title'     => 'Не нашли подходящее?',
		'cta_text'      => 'Часть объектов не публикуется в открытом доступе. Расскажите, что ищете&nbsp;— брокер подберёт из закрытой базы.',
		'cta_btn'       => 'Оставить заявку',
		'cta_obj_title' => 'Хотите посмотреть вживую?',
		'cta_obj_text'  => 'Организуем показ в удобное время и подготовим документы по объекту заранее.',
		'cta_obj_btn'   => 'Записаться на просмотр',
		'cta_svc_title' => 'Обсудим вашу задачу?',
		'cta_svc_text'  => 'Расскажите, что нужно&nbsp;— ответим в течение часа и предложим порядок действий.',
		'cta_svc_btn'   => 'Оставить заявку',

		/* Что показать, когда показывать нечего */
		'empty_objects' => 'В этой категории пока нет объектов. Напишите нам&nbsp;— расскажем о том, что не публикуется в открытом доступе.',
		'empty_reviews' => 'Отзывов пока нет.',
		'empty_section' => 'Раздел наполняется.',
		'empty_search'  => 'Ничего не нашлось. Попробуйте другое слово или посмотрите каталог целиком.',
		'err404_title'  => 'Такой страницы нет',
		'err404_text'   => 'Возможно, объект уже продан или адрес набран с опечаткой. Посмотрите каталог&nbsp;— там всё, что сейчас в работе.',
		'err404_btn'    => 'Смотреть объекты',

		/* Подписи строк на странице контактов и кнопка в шапке сайта */
		'ct_lbl_phone'   => 'Телефон',
		'ct_lbl_email'   => 'Почта',
		'ct_lbl_address' => 'Адрес',
		'ct_lbl_socials' => 'Мессенджеры',
		'ct_map_link'    => 'Смотреть на Яндекс.Картах',
		'hdr_btn'        => 'Оставить заявку',
		'services_item_btn' => 'Узнать больше',
		'search_placeholder' => 'Например: пентхаус или ипотека',
		'empty_archive'  => 'Здесь пока пусто.',

		/* Согласие под формой. Текст правовой, поэтому его правят чаще
		   прочего — держать его в коде нельзя. */
		'form_consent'      => 'Согласен на обработку персональных данных и принимаю',
		'form_consent_link' => 'политику конфиденциальности',

		/* ---------- Общие настройки сайта ---------- */
		'opt_phone'       => '+7 495 120 48 30',
		'opt_email'       => 'info@artdom.ru',
		'opt_address'     => 'Москва, Пресненская наб., 8',
		'opt_copyright'   => 'АРТДОМ © 2026',
		'opt_socials'     => array(
			array( 'label' => 'Telegram', 'url' => '#' ),
			array( 'label' => 'WhatsApp', 'url' => '#' ),
			array( 'label' => 'VK',       'url' => '#' ),
		),
		'opt_legal'       => array(
			array( 'label' => 'Политика конфиденциальности',    'url' => '#' ),
			array( 'label' => 'Политика использования Cookie',  'url' => '#' ),
			array( 'label' => 'Реквизиты',                      'url' => '#' ),
		),
	);

	return $d;
}

/**
 * Значение поля с откатом на макет.
 *
 * @param string $key    Ключ поля.
 * @param bool   $option Брать из общих настроек, а не со страницы.
 */
function artdom_field( $key, $option = false ) {
	$value = null;

	if ( function_exists( 'get_field' ) ) {
		$value = $option ? get_field( $key, 'option' ) : get_field( $key );
	}

	if ( null === $value || '' === $value || array() === $value ) {
		$d = artdom_defaults();
		return isset( $d[ $key ] ) ? $d[ $key ] : '';
	}

	return $value;
}

/**
 * Многострочный текст в разметку: переводы строк становятся <br>.
 * Неразрывные пробелы из макета проходят как есть, поэтому не esc_html,
 * а wp_kses с коротким белым списком.
 */
function artdom_lines( $text ) {
	$allowed = array( 'br' => array(), 'strong' => array(), 'em' => array(), 'sup' => array() );
	return nl2br( wp_kses( $text, $allowed ), false );
}

/**
 * Телефон в вид для href="tel:".
 */
function artdom_tel( $phone ) {
	return preg_replace( '/[^0-9+]/', '', $phone );
}

/**
 * Телефон для показа: пробелы неразрывные, чтобы номер не рвался на две
 * строки. Раньше замена стояла копией в шапке, меню и подвале, а на странице
 * контактов её забыли — и там номер переносился. Одна функция рядом с
 * artdom_tel(): парой они не расходятся.
 */
function artdom_phone_text( $phone ) {
	return str_replace( ' ', "\u{00a0}", $phone );
}

/**
 * Кнопка с перекатом текста и улетающей стрелкой.
 *
 * Разметка у неё непростая — две копии текста под перекат и две стрелки под
 * улёт, — и повторялась она в шаблонах восемь раз. Одна функция вместо восьми
 * копий: поправить поведение теперь можно в одном месте.
 *
 * @param string $text  Текст. Перенос строки станет <br>.
 * @param string $link  Адрес.
 * @param string $class Классы: btn btn--wide | btn btn--ghost | btn btn--sm.
 */
function artdom_btn( $text, $link = '#', $class = 'btn btn--wide', $attrs = array() ) {
	$html = artdom_lines( $text );
	/* Стрелка та же, что в кружках подвала, и того же размера: на сайте один
	   рисунок стрелки, а не тонкий волосок в кнопках и плотный в кружках. */
	$arrow = '<span class="btn__arrow" aria-hidden="true">'
		. '<svg viewBox="0 0 24 16"><use href="#i-arrow-xl"></use></svg>'
		. '<svg viewBox="0 0 24 16"><use href="#i-arrow-xl"></use></svg></span>';

	$extra = '';
	foreach ( $attrs as $k => $v ) {
		$extra .= ' ' . esc_attr( $k ) . '="' . esc_attr( $v ) . '"';
	}

	printf(
		'<a class="%1$s" href="%2$s" draggable="false"%5$s><span class="roll">'
			. '<span class="roll__a">%3$s</span>'
			. '<span class="roll__b" aria-hidden="true">%3$s</span></span>%4$s</a>',
		esc_attr( $class ),
		esc_url( $link ),
		$html,
		$arrow,
		$extra
	);
}

/**
 * Картинка из поля ACF, а если поле пусто — файл из папки темы.
 *
 * @param array|false $field Значение поля типа «Изображение» (формат «массив»).
 * @param string      $fallback Имя файла в /img/.
 * @param string      $alt   Запасное описание.
 * @param array       $size  Ширина и высота для верстки.
 * @param bool        $lazy  Ленивая загрузка.
 */
function artdom_img( $field, $fallback, $alt = '', $size = array( 580, 395 ), $lazy = true ) {
	$src = is_array( $field ) && ! empty( $field['url'] )
		? $field['url']
		: get_template_directory_uri() . '/img/' . $fallback;

	if ( is_array( $field ) && ! empty( $field['alt'] ) ) {
		$alt = $field['alt'];
	}

	printf(
		'<img draggable="false" src="%s" alt="%s" width="%d" height="%d"%s decoding="async">',
		esc_url( $src ),
		esc_attr( $alt ),
		(int) $size[0],
		(int) $size[1],
		$lazy ? ' loading="lazy"' : ''
	);
}

/**
 * Пять звёзд одной строкой.
 *
 * @param int    $n     Сколько закрашено, 1..5.
 * @param string $label Подпись для скринридера.
 */
function artdom_stars( $n = 5, $label = '', $tag = 'span' ) {
	/* Оценка приходит дробной: 4.9 — это четыре полных звезды и девять
	   десятых пятой. Прежде число приводилось к целому, и 4.9 рисовалось
	   пятью полными — картинка спорила с подписью рядом. */
	$n     = max( 0, min( 5, (float) $n ) );
	$full  = (int) floor( $n );
	$part  = $n - $full;
	/* Меньше пяти процентов не рисуем: такая доля неотличима от пустой
	   звезды и читается грязью. */
	if ( $part < 0.05 ) {
		$part = 0;
	}

	$label = $label ? $label : sprintf( 'Оценка %s из 5', rtrim( rtrim( number_format( $n, 1, ',', '' ), '0' ), ',' ) );
	$tag   = in_array( $tag, array( 'span', 'p' ), true ) ? $tag : 'span';
	$star  = '<svg viewBox="0 0 20 19" aria-hidden="true"><use href="#i-star"></use></svg>';

	$out = '<' . $tag . ' class="stars" role="img" aria-label="' . esc_attr( $label ) . '">';
	for ( $i = 1; $i <= 5; $i++ ) {
		if ( $i <= $full ) {
			$out .= $star;
		} elseif ( $i === $full + 1 && $part > 0 ) {
			/* Две звезды одна над другой: снизу пустая, сверху залитая и
			   обрезанная по доле. Доля идёт свойством, а обрезку делает CSS. */
			$out .= '<span class="stars__part" style="--part:' . round( $part * 100 ) . '%">'
				. '<svg viewBox="0 0 20 19" aria-hidden="true" data-off="true"><use href="#i-star"></use></svg>'
				. '<svg class="stars__fill" viewBox="0 0 20 19" aria-hidden="true"><use href="#i-star"></use></svg>'
				. '</span>';
		} else {
			$out .= '<svg viewBox="0 0 20 19" aria-hidden="true" data-off="true"><use href="#i-star"></use></svg>';
		}
	}
	return $out . '</' . $tag . '>';
}

/**
 * Оценка и число отзывов — по факту из базы, а не из полей в админке.
 *
 * Поля стояли заполненными вручную: 4.9 и 86, а на странице показывалось
 * шесть отзывов. Две цифры в пятнадцати сантиметрах друг от друга
 * противоречили одна другой, и заметно это было сразу.
 *
 * Считаем с запасом в час: отзывы меняются редко, а перебирать их на каждой
 * загрузке страницы незачем.
 */
function artdom_reviews_stats() {
	$stats = get_transient( 'artdom_rev_stats' );
	if ( is_array( $stats ) ) {
		return $stats;
	}

	$ids = get_posts(
		array(
			'post_type'   => 'artdom_review',
			'post_status' => 'publish',
			'numberposts' => -1,
			'fields'      => 'ids',
		)
	);

	$sum = 0;
	$n   = 0;
	foreach ( $ids as $id ) {
		$r = function_exists( 'get_field' ) ? (int) get_field( 'rev_rating', $id ) : 0;
		if ( $r >= 1 && $r <= 5 ) {
			$sum += $r;
			++$n;
		}
	}

	$stats = array(
		'count' => count( $ids ),
		'avg'   => $n ? round( $sum / $n, 1 ) : 0,
	);
	set_transient( 'artdom_rev_stats', $stats, HOUR_IN_SECONDS );
	return $stats;
}

/** Отзыв опубликовали или сняли — счёт пересчитаем заново. */
function artdom_drop_reviews_stats() {
	delete_transient( 'artdom_rev_stats' );
}
add_action( 'save_post_artdom_review', 'artdom_drop_reviews_stats' );
add_action( 'deleted_post', 'artdom_drop_reviews_stats' );

/**
 * Хлебные крошки.
 *
 * Собираются из того, где мы находимся, а не из плагина: на сайте четыре типа
 * страниц, и правил для них меньше, чем настроек у любого готового решения.
 *
 * @param array $extra Дополнительные звенья вида array( 'Название' => 'адрес' ).
 */
function artdom_crumbs( $extra = array() ) {
	$items = array( 'Главная' => home_url( '/' ) );

	if ( is_post_type_archive( 'artdom_object' ) || is_tax( 'artdom_object_type' ) ) {
		$items['Объекты'] = get_post_type_archive_link( 'artdom_object' );
		if ( is_tax( 'artdom_object_type' ) ) {
			$items[ single_term_title( '', false ) ] = '';
		}
	} elseif ( is_singular( 'artdom_object' ) ) {
		$items['Объекты'] = get_post_type_archive_link( 'artdom_object' );
		$items[ get_the_title() ] = '';
	} elseif ( is_post_type_archive( 'artdom_service' ) ) {
		$items['Услуги'] = '';
	} elseif ( is_singular( 'artdom_service' ) ) {
		$items['Услуги'] = get_post_type_archive_link( 'artdom_service' );
		$items[ get_the_title() ] = '';
	} elseif ( is_post_type_archive( 'artdom_review' ) ) {
		$items['Отзывы'] = '';
	} elseif ( is_home() ) {
		/* Лента записей: WordPress отдаёт её через is_home(), а не is_page(),
		   даже когда она назначена обычной страницей. */
		$artdom_blog = (int) get_option( 'page_for_posts' );
		$items[ $artdom_blog ? get_the_title( $artdom_blog ) : 'Блог' ] = '';
	} elseif ( is_singular( 'post' ) ) {
		$artdom_blog = (int) get_option( 'page_for_posts' );
		if ( $artdom_blog ) {
			$items[ get_the_title( $artdom_blog ) ] = get_permalink( $artdom_blog );
		}
		$items[ get_the_title() ] = '';
	} elseif ( is_search() ) {
		$items['Поиск'] = '';
	} elseif ( is_404() ) {
		$items['Страница не найдена'] = '';
	} elseif ( is_page() ) {
		$parent = wp_get_post_parent_id( get_the_ID() );
		if ( $parent ) {
			$items[ get_the_title( $parent ) ] = get_permalink( $parent );
		}
		$items[ get_the_title() ] = '';
	}

	foreach ( $extra as $label => $url ) {
		$items[ $label ] = $url;
	}

	$out   = array();
	$last  = array_key_last( $items );
	foreach ( $items as $label => $url ) {
		if ( $url && $label !== $last ) {
			$out[] = '<a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a>';
		} else {
			$out[] = '<span aria-current="page">' . esc_html( $label ) . '</span>';
		}
	}

	return '<nav class="crumbs" aria-label="Вы здесь">' . implode( '', $out ) . '</nav>';
}

/**
 * Склонение слова после числа: 1 объект, 2 объекта, 5 объектов.
 *
 * @param int   $n     Число.
 * @param array $forms Три формы: один, два, пять.
 */
function artdom_plural( $n, $forms ) {
	$n = abs( (int) $n );
	$i = ( $n % 100 > 4 && $n % 100 < 20 ) ? 2 : array( 2, 0, 1, 1, 1, 2 )[ min( $n % 10, 5 ) ];
	return $forms[ $i ];
}

/**
 * Описание форм: одно на модалку и на страницу контактов.
 *
 * Пока описание жило внутри шаблона модалки, вставить ту же форму в страницу
 * было нечем — пришлось бы копировать, а копии расходятся.
 */
function artdom_forms_config() {
	return array(
		'lead'      => array(
			'title'  => 'Оставить заявку',
			'lead'   => 'Персональный брокер свяжется с вами и подберёт объекты под ваш запрос.',
			'submit' => 'Отправить',
			'fields' => array(
				array( 'name' => 'name',    'label' => 'Как к вам обращаться', 'type' => 'text',     'required' => true,  'autocomplete' => 'name',  'placeholder' => 'Иван Петров' ),
				array( 'name' => 'phone',   'label' => 'Телефон',              'type' => 'tel',      'required' => true,  'autocomplete' => 'tel',   'placeholder' => '+7 999 123 45 67' ),
				array( 'name' => 'message', 'label' => 'Что ищете',            'type' => 'textarea', 'required' => false, 'autocomplete' => 'off',   'placeholder' => 'Трёхкомнатная в Хамовниках, бюджет до 200 млн, окна во двор' ),
			),
		),
		'review'    => array(
			'title'  => 'Оставить отзыв',
			'lead'   => 'Расскажите, как прошла работа. Отзыв появится на сайте после проверки.',
			'submit' => 'Отправить отзыв',
			'fields' => array(
				array( 'name' => 'name',    'label' => 'Как вас представить',  'type' => 'text',     'required' => true,  'autocomplete' => 'name',  'placeholder' => 'Анна С.' ),
				array( 'name' => 'rating',  'label' => 'Оценка',               'type' => 'rating',   'required' => true,  'autocomplete' => 'off' ),
				array( 'name' => 'message', 'label' => 'Отзыв',                'type' => 'textarea', 'required' => true,  'autocomplete' => 'off',   'placeholder' => 'Что понравилось, а что стоит улучшить' ),
				/* Почта не обязательна: отзыв можно оставить и не оставляя контакта,
				   но если он есть — будет куда ответить. */
				array( 'name' => 'email',   'label' => 'Почта для ответа',     'type' => 'email',    'required' => false, 'autocomplete' => 'email', 'placeholder' => 'name@domain.ru' ),
			),
		),
		'subscribe' => array(
			'title'  => 'Подборка объектов на почту',
			'lead'   => 'Раз в неделю — новые лоты и закрытые предложения. Без спама, отписаться можно в любой момент.',
			'submit' => 'Подписаться',
			'fields' => array(
				array( 'name' => 'email', 'label' => 'Ваша почта', 'type' => 'email', 'required' => true, 'autocomplete' => 'email', 'placeholder' => 'name@domain.ru' ),
			),
		),
	);
}

/**
 * Кириллица в ярлыках адресов — латиницей.
 *
 * Без этого WordPress оставляет русские буквы, и ссылка при копировании
 * превращается в /objects/%d0%bf%d0%b5%d0%bd... — прочитать и прислать её
 * невозможно. Отдельный плагин ради двадцати строк ставить незачем.
 */
function artdom_translit( $text ) {
	$map = array(
		'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e',
		'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm',
		'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u',
		'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch',
		'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
	);
	$lower = function_exists( 'mb_strtolower' ) ? mb_strtolower( $text, 'UTF-8' ) : strtolower( $text );
	return strtr( $lower, $map );
}

/** Ярлык записи собираем уже из латиницы. */
function artdom_sanitize_title( $title, $raw_title = '', $context = 'save' ) {
	if ( 'save' !== $context ) {
		return $title;
	}
	$source = $raw_title ? $raw_title : $title;
	return sanitize_title_with_dashes( artdom_translit( $source ), '', 'save' );
}
add_filter( 'sanitize_title', 'artdom_sanitize_title', 9, 3 );

/**
 * Серый фон под шапкой и белый лист ниже — приём страницы контактов.
 *
 * Ставится СРАЗУ ВСЕМ внутренним страницам, а не вызывается из шаблонов.
 * Сначала это была функция, которую шаблон звал сам, — и её дважды забыли
 * позвать на новых страницах. Признак был известен заранее: правило
 * пишется в каждом шаблоне отдельно, значит следующий шаблон его не
 * получит. Условие одно — кроме главной, у неё своя первая сцена.
 */
function artdom_sheet_body_class( $classes ) {
	if ( ! is_front_page() ) {
		$classes[] = "is-sheet";
	}
	return $classes;
}
add_filter( "body_class", "artdom_sheet_body_class" );

/**
 * Описание страницы и карточка ссылки для мессенджеров.
 *
 * Раньше в <head> не было ни того, ни другого: в выдаче поисковик сочинял
 * описание сам, а ссылка в переписке разворачивалась без картинки. Значения
 * берутся по цепочке — своё поле страницы, потом её отрывок, потом общее из
 * настроек сайта; заполнять ничего не обязательно.
 */
function artdom_meta_tags() {
	$id    = is_singular() ? get_queried_object_id() : 0;
	$title = $id ? (string) get_field( 'seo_title', $id ) : '';
	$desc  = $id ? (string) get_field( 'seo_desc', $id ) : '';
	$image = $id ? (string) get_field( 'seo_image', $id ) : '';

	if ( '' === $title ) {
		$title = is_front_page() ? get_bloginfo( 'name' ) : wp_get_document_title();
	}

	if ( '' === $desc && $id ) {
		/* Отрывок годится в описание: он и пишется как краткий пересказ.
		   get_the_excerpt() сам соберёт его из текста, если поле пустое. */
		$desc = wp_strip_all_tags( get_the_excerpt( $id ) );
	}
	if ( '' === $desc ) {
		$desc = (string) artdom_field( 'opt_seo_desc', true );
	}
	$desc = trim( preg_replace( '/\s+/u', ' ', $desc ) );
	if ( mb_strlen( $desc ) > 200 ) {
		$desc = mb_substr( $desc, 0, 197 ) . '…';
	}

	if ( '' === $image && $id && has_post_thumbnail( $id ) ) {
		$image = (string) get_the_post_thumbnail_url( $id, 'large' );
	}
	if ( '' === $image ) {
		$image = (string) artdom_field( 'opt_seo_image', true );
	}

	if ( '' !== $desc ) {
		printf( '<meta name="description" content="%s">' . "\n", esc_attr( $desc ) );
		printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $desc ) );
	}
	printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( $title ) );
	printf( '<meta property="og:type" content="%s">' . "\n", is_singular( 'post' ) ? 'article' : 'website' );
	printf( '<meta property="og:url" content="%s">' . "\n", esc_url( home_url( add_query_arg( array() ) ) ) );
	printf( '<meta property="og:site_name" content="%s">' . "\n", esc_attr( get_bloginfo( 'name' ) ) );
	if ( '' !== $image ) {
		printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $image ) );
		echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
	}
}
add_action( 'wp_head', 'artdom_meta_tags', 2 );
