<?php
/**
 * Модальные окна форм.
 *
 * Нативный <dialog>: он сам ловит Esc, держит фокус внутри и рисует подложку.
 * Самодельная модалка всё это воспроизводит примерно на сотне строк скрипта
 * и обычно с ошибками в обходе табом.
 *
 * Разметка самой формы общая — template-parts/form.php.
 *
 * @package artdom
 */

foreach ( array_keys( artdom_forms_config() ) as $artdom_kind ) :
	$artdom_id = 'form-' . $artdom_kind;
	?>
<dialog class="modal" id="<?php echo esc_attr( $artdom_id ); ?>" aria-labelledby="<?php echo esc_attr( $artdom_id ); ?>-title">
	<?php
	set_query_var( 'artdom_form_kind', $artdom_kind );
	set_query_var( 'artdom_form_id', $artdom_id );
	set_query_var( 'artdom_form_closer', true );
	get_template_part( 'template-parts/form' );
	?>
</dialog>
	<?php
endforeach;
