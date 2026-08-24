<?php
/**
 * Формы: заявка и подписка.
 *
 * Написано вместо sendform() из базы oxboxwise. Отличия по существу:
 *   - проверяется nonce, иначе заявки может слать любой сторонний сайт;
 *   - всё уходит в письмо экранированным;
 *   - заявка ПЕРЕД отправкой письма сохраняется записью в админке —
 *     почта на хостингах отваливается молча, и без этого заявка теряется
 *     навсегда, а клиент об этом даже не узнает;
 *   - вместо капчи ловушка для ботов и проверка времени заполнения:
 *     человек не отправляет форму за полторы секунды.
 *
 * @package artdom
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Куда слать письма. */
function artdom_form_recipient() {
	$to = artdom_field( 'opt_email_send', true );
	if ( ! $to || ! is_email( $to ) ) {
		$to = artdom_field( 'opt_email', true );
	}
	return is_email( $to ) ? $to : get_option( 'admin_email' );
}

/* ---------------------------------------------------------------------------
 * Тип записи «Заявки» — журнал на случай, если письмо не дойдёт
 * ------------------------------------------------------------------------- */

function artdom_register_leads() {
	register_post_type(
		'artdom_lead',
		array(
			'labels'          => array(
				'name'          => 'Заявки',
				'singular_name' => 'Заявка',
				'all_items'     => 'Все заявки',
				'edit_item'     => 'Заявка',
				'not_found'     => 'Заявок пока нет',
			),
			'public'          => false,
			'show_ui'         => true,
			'menu_icon'       => 'dashicons-email-alt',
			'menu_position'   => 22,
			'supports'        => array( 'title' ),
			'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
			'map_meta_cap'    => true,
		)
	);
}
add_action( 'init', 'artdom_register_leads' );

/** Колонки в списке заявок: чтобы видеть суть, не открывая каждую. */
function artdom_lead_columns( $cols ) {
	return array(
		'cb'             => $cols['cb'],
		'title'          => 'Кто',
		'artdom_kind'    => 'Форма',
		'artdom_contact' => 'Контакт',
		'artdom_msg'     => 'Сообщение',
		'date'           => 'Когда',
	);
}
add_filter( 'manage_artdom_lead_posts_columns', 'artdom_lead_columns' );

function artdom_lead_column( $col, $post_id ) {
	$map = array(
		'artdom_kind'    => '_artdom_kind_label',
		'artdom_contact' => '_artdom_contact',
		'artdom_msg'     => '_artdom_message',
	);
	if ( isset( $map[ $col ] ) ) {
		echo esc_html( mb_strimwidth( (string) get_post_meta( $post_id, $map[ $col ], true ), 0, 90, '…' ) );
	}
}
add_action( 'manage_artdom_lead_posts_custom_column', 'artdom_lead_column', 10, 2 );

/** Данные заявки под редактором — только для чтения. */
function artdom_lead_metabox() {
	add_meta_box(
		'artdom_lead_data',
		'Данные заявки',
		function ( $post ) {
			$rows = array(
				'Форма'    => '_artdom_kind_label',
				'Имя'      => '_artdom_name',
				'Контакт'  => '_artdom_contact',
				'Сообщение'=> '_artdom_message',
				'Страница' => '_artdom_page',
				'Письмо'   => '_artdom_mail_status',
				'IP'       => '_artdom_ip',
			);
			echo '<table class="widefat striped"><tbody>';
			foreach ( $rows as $label => $key ) {
				$v = (string) get_post_meta( $post->ID, $key, true );
				if ( '' === $v ) {
					continue;
				}
				printf( '<tr><td style="width:140px"><strong>%s</strong></td><td>%s</td></tr>', esc_html( $label ), nl2br( esc_html( $v ) ) );
			}
			echo '</tbody></table>';
		},
		'artdom_lead',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'artdom_lead_metabox' );

/* ---------------------------------------------------------------------------
 * Обработчик отправки
 * ------------------------------------------------------------------------- */

function artdom_form_error( $message, $field = '' ) {
	wp_send_json_error( array( 'message' => $message, 'field' => $field ), 200 );
}

function artdom_handle_form() {

	/* 1. Nonce: без него заявки может слать любой сторонний сайт от имени посетителя */
	if ( ! check_ajax_referer( 'artdom_form', 'nonce', false ) ) {
		artdom_form_error( 'Страница устарела. Обновите её и попробуйте снова.' );
	}

	/* 2. Ловушка для ботов: поле спрятано от человека, бот его заполняет */
	if ( ! empty( $_POST['website'] ) ) {
		wp_send_json_success( array( 'message' => 'Спасибо, заявка отправлена.' ) );
	}

	/* 3. Время заполнения.
	      Порог 4 секунды: человеку нужно больше даже на одно поле с почтой,
	      а бот отправляет мгновенно. Два меня подвели — столько съедает сама
	      загрузка WordPress, пока запрос идёт по сети.
	      Отрицательная разница означает подделанное поле: время «отправки»
	      оказалось раньше времени отрисовки формы. */
	$started = isset( $_POST['started'] ) ? (int) $_POST['started'] : 0;
	if ( $started ) {
		$elapsed = time() - $started;
		if ( $elapsed < 4 ) {
			artdom_form_error( 'Слишком быстро. Проверьте поля и отправьте ещё раз.' );
		}
		if ( $elapsed > DAY_IN_SECONDS ) {
			artdom_form_error( 'Страница давно открыта. Обновите её и попробуйте снова.' );
		}
	}

	/* 4. Не больше пяти заявок с адреса в час */
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$key = 'artdom_rate_' . md5( $ip );
	$hits = (int) get_transient( $key );
	if ( $hits >= 5 ) {
		artdom_form_error( 'Слишком много отправок. Попробуйте через час или позвоните нам.' );
	}

	$kind    = isset( $_POST['kind'] ) ? sanitize_key( $_POST['kind'] ) : 'lead';
	$name    = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$phone   = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
	$email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';
	$page    = isset( $_POST['page'] ) ? esc_url_raw( wp_unslash( $_POST['page'] ) ) : '';
	$consent = ! empty( $_POST['consent'] );

	/* 5. Согласие на обработку персональных данных — требование 152-ФЗ */
	if ( ! $consent ) {
		artdom_form_error( 'Нужно согласие на обработку персональных данных.', 'consent' );
	}

	if ( 'subscribe' === $kind ) {
		if ( ! is_email( $email ) ) {
			artdom_form_error( 'Проверьте адрес почты.', 'email' );
		}
		$who     = $email;
		$contact = $email;
		$label   = 'Подписка на рассылку';
	} else {
		if ( mb_strlen( $name ) < 2 ) {
			artdom_form_error( 'Как к вам обращаться?', 'name' );
		}
		$digits = preg_replace( '/\D/', '', $phone );
		if ( mb_strlen( $digits ) < 10 ) {
			artdom_form_error( 'Проверьте номер телефона.', 'phone' );
		}
		$who     = $name;
		$contact = $phone . ( $email ? ', ' . $email : '' );
		$label   = 'Заявка на подбор';
	}

	/* 6. Сохраняем ДО отправки письма: почта на хостинге отваливается молча,
	      и без записи заявка пропала бы бесследно. */
	$lead_id = wp_insert_post(
		array(
			'post_type'   => 'artdom_lead',
			'post_title'  => $who . ' — ' . $label,
			'post_status' => 'publish',
		)
	);

	if ( ! is_wp_error( $lead_id ) ) {
		update_post_meta( $lead_id, '_artdom_kind_label', $label );
		update_post_meta( $lead_id, '_artdom_name', $name );
		update_post_meta( $lead_id, '_artdom_contact', $contact );
		update_post_meta( $lead_id, '_artdom_message', $message );
		update_post_meta( $lead_id, '_artdom_page', $page );
		update_post_meta( $lead_id, '_artdom_ip', $ip );
	}

	/* 7. Письмо. Всё, что пришло от посетителя, экранируем. */
	$rows = array(
		'Форма'     => $label,
		'Имя'       => $name,
		'Телефон'   => $phone,
		'E-mail'    => $email,
		'Сообщение' => $message,
		'Страница'  => $page,
	);

	$html  = '<table style="border-collapse:collapse;font:15px/1.5 Arial,sans-serif;color:#000003">';
	foreach ( $rows as $k => $v ) {
		if ( '' === trim( (string) $v ) ) {
			continue;
		}
		$html .= '<tr>'
			. '<td style="padding:8px 16px 8px 0;border-bottom:1px solid #d9dae0;vertical-align:top"><strong>' . esc_html( $k ) . '</strong></td>'
			. '<td style="padding:8px 0;border-bottom:1px solid #d9dae0">' . nl2br( esc_html( $v ) ) . '</td>'
			. '</tr>';
	}
	$html .= '</table>';

	$headers = array( 'Content-Type: text/html; charset=UTF-8' );
	if ( is_email( $email ) ) {
		$headers[] = 'Reply-To: ' . $email;
	}

	$sent = wp_mail(
		artdom_form_recipient(),
		$label . ' с сайта ' . wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
		$html,
		$headers
	);

	if ( ! is_wp_error( $lead_id ) ) {
		update_post_meta( $lead_id, '_artdom_mail_status', $sent ? 'отправлено' : 'НЕ отправлено (проверьте почту на хостинге)' );
	}

	set_transient( $key, $hits + 1, HOUR_IN_SECONDS );

	wp_send_json_success(
		array(
			'message' => 'subscribe' === $kind
				? 'Готово. Первая подборка придёт в ближайшую неделю.'
				: 'Спасибо! Персональный брокер свяжется с вами.',
		)
	);
}
add_action( 'wp_ajax_artdom_form', 'artdom_handle_form' );
add_action( 'wp_ajax_nopriv_artdom_form', 'artdom_handle_form' );

/** Счётчик новых заявок рядом с пунктом меню. */
function artdom_lead_bubble() {
	global $menu;
	$count = (int) wp_count_posts( 'artdom_lead' )->publish;
	if ( ! $count ) {
		return;
	}
	foreach ( $menu as $i => $item ) {
		if ( isset( $item[2] ) && 'edit.php?post_type=artdom_lead' === $item[2] ) {
			$menu[ $i ][0] .= ' <span class="update-plugins count-' . $count . '"><span class="plugin-count">' . $count . '</span></span>';
			break;
		}
	}
}
add_action( 'admin_menu', 'artdom_lead_bubble', 999 );
