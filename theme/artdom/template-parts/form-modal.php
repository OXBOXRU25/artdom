<?php
/**
 * Модальные окна форм.
 *
 * Нативный <dialog>: он сам ловит Esc, держит фокус внутри и рисует подложку.
 * Самодельная модалка всё это воспроизводит примерно на сотне строк скрипта
 * и обычно с ошибками в обходе табом.
 *
 * @package artdom
 */

$legal   = artdom_field( 'opt_legal', true );
$privacy = ( is_array( $legal ) && ! empty( $legal[0]['url'] ) ) ? $legal[0]['url'] : '#';
$started = time();

$forms = array(
	'lead'      => array(
		'title'  => 'Оставить заявку',
		'lead'   => 'Персональный брокер свяжется с вами и подберёт объекты под ваш запрос.',
		'submit' => 'Отправить',
		'fields' => array(
			array( 'name' => 'name',    'label' => 'Как к вам обращаться',   'type' => 'text',     'required' => true,  'autocomplete' => 'name' ),
			array( 'name' => 'phone',   'label' => 'Телефон',                'type' => 'tel',      'required' => true,  'autocomplete' => 'tel' ),
			array( 'name' => 'message', 'label' => 'Что ищете',              'type' => 'textarea', 'required' => false, 'autocomplete' => 'off' ),
		),
	),
	'subscribe' => array(
		'title'  => 'Подборка объектов на почту',
		'lead'   => 'Раз в неделю — новые лоты и закрытые предложения. Без спама, отписаться можно в любой момент.',
		'submit' => 'Подписаться',
		'fields' => array(
			array( 'name' => 'email', 'label' => 'Ваша почта', 'type' => 'email', 'required' => true, 'autocomplete' => 'email' ),
		),
	),
);
?>

<?php foreach ( $forms as $kind => $f ) : $id = 'form-' . $kind; ?>
<dialog class="modal" id="<?php echo esc_attr( $id ); ?>" aria-labelledby="<?php echo esc_attr( $id ); ?>-title">
  <form class="modal__box" method="dialog" data-form="<?php echo esc_attr( $kind ); ?>" novalidate>

    <button class="modal__close" type="button" data-form-close aria-label="Закрыть">
      <svg viewBox="0 0 22 22" aria-hidden="true"><use href="#i-close"></use></svg>
    </button>

    <h2 class="modal__title h2" id="<?php echo esc_attr( $id ); ?>-title"><?php echo esc_html( $f['title'] ); ?></h2>
    <p class="body modal__lead"><?php echo esc_html( $f['lead'] ); ?></p>

    <div class="modal__fields">
      <?php foreach ( $f['fields'] as $field ) : $fid = $id . '-' . $field['name']; ?>
      <p class="field">
        <label class="field__label" for="<?php echo esc_attr( $fid ); ?>">
          <?php echo esc_html( $field['label'] ); ?><?php echo $field['required'] ? '<span aria-hidden="true"> *</span>' : ''; ?>
        </label>
        <?php if ( 'textarea' === $field['type'] ) : ?>
        <textarea class="field__input" id="<?php echo esc_attr( $fid ); ?>" name="<?php echo esc_attr( $field['name'] ); ?>" rows="3" autocomplete="off"></textarea>
        <?php else : ?>
        <input class="field__input" id="<?php echo esc_attr( $fid ); ?>" name="<?php echo esc_attr( $field['name'] ); ?>"
               type="<?php echo esc_attr( $field['type'] ); ?>"
               autocomplete="<?php echo esc_attr( $field['autocomplete'] ); ?>"
               <?php echo $field['required'] ? 'required' : ''; ?>>
        <?php endif; ?>
        <span class="field__error" aria-live="polite"></span>
      </p>
      <?php endforeach; ?>

      <p class="check">
        <input class="check__box" type="checkbox" id="<?php echo esc_attr( $id ); ?>-consent" name="consent" required>
        <label class="check__label" for="<?php echo esc_attr( $id ); ?>-consent">
          Согласен на обработку персональных данных и принимаю
          <a class="ox-selectable" href="<?php echo esc_url( $privacy ); ?>" target="_blank" rel="noopener">политику конфиденциальности</a>
        </label>
        <span class="field__error" aria-live="polite"></span>
      </p>
    </div>

    <?php /* Ловушка для ботов: человек этого поля не видит и не заполняет. */ ?>
    <p class="modal__trap" aria-hidden="true">
      <label for="<?php echo esc_attr( $id ); ?>-website">Не заполняйте это поле</label>
      <input id="<?php echo esc_attr( $id ); ?>-website" type="text" name="website" tabindex="-1" autocomplete="off">
    </p>

    <input type="hidden" name="kind" value="<?php echo esc_attr( $kind ); ?>">
    <input type="hidden" name="started" value="<?php echo esc_attr( $started ); ?>">

    <button class="btn btn--wide modal__submit" type="submit">
      <span class="roll"><span class="roll__a"><?php echo esc_html( $f['submit'] ); ?></span><span class="roll__b" aria-hidden="true"><?php echo esc_html( $f['submit'] ); ?></span></span>
      <span class="btn__arrow" aria-hidden="true"><svg viewBox="0 0 23 6"><use href="#i-arrow"></use></svg><svg viewBox="0 0 23 6"><use href="#i-arrow"></use></svg></span>
    </button>

    <p class="modal__note" role="status" aria-live="polite"></p>
  </form>
</dialog>
<?php endforeach; ?>
