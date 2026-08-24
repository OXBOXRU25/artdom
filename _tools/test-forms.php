<?php
/**
 * Прогон формы во всех состояниях: настоящими POST-запросами к admin-ajax,
 * а не вызовом обработчика напрямую — иначе не проверятся ни nonce, ни маршрут.
 */
$_SERVER['HTTP_HOST'] = '127.0.0.1:8080';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['SERVER_NAME'] = '127.0.0.1';
$_SERVER['SERVER_PORT'] = '8080';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SCRIPT_NAME'] = '/index.php';
require __DIR__ . '/../wp/wp-load.php';

$url   = admin_url( 'admin-ajax.php' );
$nonce = wp_create_nonce( 'artdom_form' );
delete_transient( 'artdom_rate_' . md5( '127.0.0.1' ) );
delete_transient( 'artdom_rate_' . md5( '::1' ) );
$before = (int) wp_count_posts( 'artdom_lead' )->publish;

function post( $url, $fields ) {
	$ch = curl_init( $url );
	curl_setopt_array( $ch, array(
		CURLOPT_POST           => true,
		CURLOPT_POSTFIELDS     => http_build_query( $fields ),
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT        => 25,
	) );
	$body = curl_exec( $ch );
	$err  = curl_error( $ch );
	curl_close( $ch );
	if ( $err ) { return array( 'ошибка сети', $err ); }
	$j = json_decode( $body, true );
	if ( null === $j ) { return array( 'не JSON', mb_substr( $body, 0, 120 ) ); }
	return array( $j['success'] ? 'успех' : 'отказ', isset( $j['data']['message'] ) ? $j['data']['message'] : '' );
}

$base = array( 'action' => 'artdom_form', 'nonce' => $nonce, 'started' => time() - 20, 'page' => 'http://127.0.0.1:8080/' );

$cases = array(
	'заявка как надо' => $base + array( 'kind' => 'lead', 'name' => 'Тестов Тест', 'phone' => '+7 999 123 45 67', 'message' => 'Ищу трёшку', 'consent' => '1' ),
	'без согласия'    => $base + array( 'kind' => 'lead', 'name' => 'Тестов Тест', 'phone' => '+7 999 123 45 67' ),
	'короткий телефон'=> $base + array( 'kind' => 'lead', 'name' => 'Тестов Тест', 'phone' => '123', 'consent' => '1' ),
	'пустое имя'      => $base + array( 'kind' => 'lead', 'name' => '', 'phone' => '+7 999 123 45 67', 'consent' => '1' ),
	'подписка'        => $base + array( 'kind' => 'subscribe', 'email' => 'test@artdom.ru', 'consent' => '1' ),
	'кривая почта'    => $base + array( 'kind' => 'subscribe', 'email' => 'not-an-email', 'consent' => '1' ),
	'ловушка бота'    => $base + array( 'kind' => 'lead', 'name' => 'Бот', 'phone' => '+7 999 123 45 67', 'consent' => '1', 'website' => 'http://spam.ru' ),
	'слишком быстро'  => array( 'action' => 'artdom_form', 'nonce' => $nonce, 'started' => time() + 600, 'kind' => 'lead', 'name' => 'Тестов Тест', 'phone' => '+7 999 123 45 67', 'consent' => '1' ),
	'страница протухла' => array( 'action' => 'artdom_form', 'nonce' => $nonce, 'started' => time() - 200000, 'kind' => 'lead', 'name' => 'Тестов Тест', 'phone' => '+7 999 123 45 67', 'consent' => '1' ),
	'подделанный nonce' => array( 'action' => 'artdom_form', 'nonce' => 'deadbeef', 'started' => time() - 20, 'kind' => 'lead', 'name' => 'Тестов Тест', 'phone' => '+7 999 123 45 67', 'consent' => '1' ),
);

$expect = array(
	'заявка как надо'   => 'успех',
	'без согласия'      => 'отказ',
	'короткий телефон'  => 'отказ',
	'пустое имя'        => 'отказ',
	'подписка'          => 'успех',
	'кривая почта'      => 'отказ',
	'ловушка бота'      => 'успех',   // боту отвечаем «спасибо», но заявку не пишем
	'слишком быстро'    => 'отказ',
	'страница протухла'  => 'отказ',
	'подделанный nonce' => 'отказ',
);

$ok = 0;
foreach ( $cases as $name => $fields ) {
	list( $result, $msg ) = post( $url, $fields );
	$pass = ( $result === $expect[ $name ] );
	$ok += $pass ? 1 : 0;
	printf( "  %-19s %-6s %s  %s\n", $name, $result, $pass ? 'ok' : 'НЕ ТО (ждали ' . $expect[ $name ] . ')', mb_substr( $msg, 0, 52 ) );
}

wp_cache_flush();
$after = (int) wp_count_posts( 'artdom_lead' )->publish;
echo "\n  пройдено: $ok из " . count( $cases ) . "\n";
echo '  заявок в журнале было ' . $before . ', стало ' . $after . " (ждали +2: заявка и подписка, бот писаться не должен)\n";

$last = get_posts( array( 'post_type' => 'artdom_lead', 'numberposts' => 3, 'post_status' => 'publish' ) );
foreach ( $last as $l ) {
	echo '    ' . $l->post_title . '  |  ' . get_post_meta( $l->ID, '_artdom_contact', true )
		. '  |  письмо: ' . get_post_meta( $l->ID, '_artdom_mail_status', true ) . "\n";
}
