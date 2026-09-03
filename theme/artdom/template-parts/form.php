<?php
/**
 * Сама форма, без обёртки.
 *
 * В модалке её оборачивает <dialog>, на странице контактов она стоит прямо
 * в вёрстке. Разметка одна: и обработчик, и проверки, и ловушка для ботов
 * живут в одном месте.
 *
 * @package artdom
 */

$kind = get_query_var( 'artdom_form_kind' );
$kind = $kind ? $kind : 'lead';

$forms = artdom_forms_config();
if ( ! isset( $forms[ $kind ] ) ) {
	return;
}

$f       = $forms[ $kind ];
$id      = get_query_var( 'artdom_form_id' );
$id      = $id ? $id : 'form-' . $kind;
$closer  = (bool) get_query_var( 'artdom_form_closer' );
/* Запасное значение возвращается только если переменную не задавали. Пустая
   строка — это осознанное «без заголовка», её ставит страница контактов, где
   заголовок формы уже есть в вёрстке. */
$heading = get_query_var( 'artdom_form_heading', '__нет__' );
$heading = '__нет__' === $heading ? $f['title'] : $heading;

$legal   = artdom_field( 'opt_legal', true );
$privacy = ( is_array( $legal ) && ! empty( $legal[0]['url'] ) ) ? $legal[0]['url'] : '#';
$started = time();
?>
<form class="modal__box" method="dialog" data-form="<?php echo esc_attr( $kind ); ?>" novalidate>

  <?php if ( $closer ) : ?>
  <button class="modal__close" type="button" data-form-close aria-label="Закрыть">
    <svg viewBox="0 0 22 22" aria-hidden="true"><use href="#i-close"></use></svg>
  </button>
  <?php endif; ?>

  <?php if ( $heading ) : ?>
  <h2 class="modal__title h2" id="<?php echo esc_attr( $id ); ?>-title"><?php echo esc_html( $heading ); ?></h2>
  <p class="body modal__lead"><?php echo esc_html( $f['lead'] ); ?></p>
  <?php endif; ?>

  <div class="modal__fields">
    <?php foreach ( $f['fields'] as $field ) : $fid = $id . '-' . $field['name']; ?>
    <p class="field<?php echo 'rating' === $field['type'] ? ' field--rating' : ''; ?>">
      <label class="field__label" for="<?php echo esc_attr( $fid ); ?>">
        <?php echo esc_html( $field['label'] ); ?><?php echo $field['required'] ? '<span aria-hidden="true"> *</span>' : ''; ?>
      </label>
      <?php
      /* Пример заполнения серым: он показывает не только формат, но и то,
         какие подробности нам полезны. Пустое поле человек заполняет как
         придётся, а с примером — по образцу. */
      $ph = isset( $field['placeholder'] ) ? $field['placeholder'] : '';
      ?>
      <?php if ( 'rating' === $field['type'] ) : ?>
      <?php
      /* Звёзды идут в разметке от пяти к одной, а показываются в обратном
         порядке (row-reverse). Так соседний селектор ~ закрашивает выбранную
         звезду и все левее неё — без единой строчки скрипта. */
      ?>
      <span class="rate">
        <?php for ( $artdom_r = 5; $artdom_r >= 1; $artdom_r-- ) : ?>
        <input class="rate__in vh" type="radio" id="<?php echo esc_attr( $fid . '-' . $artdom_r ); ?>" name="<?php echo esc_attr( $field['name'] ); ?>" value="<?php echo (int) $artdom_r; ?>">
        <label class="rate__star" for="<?php echo esc_attr( $fid . '-' . $artdom_r ); ?>">
          <svg viewBox="0 0 20 19" aria-hidden="true"><use href="#i-star"></use></svg>
          <span class="vh"><?php echo (int) $artdom_r; ?> из 5</span>
        </label>
        <?php endfor; ?>
      </span>
      <?php elseif ( 'textarea' === $field['type'] ) : ?>
      <textarea class="field__input" id="<?php echo esc_attr( $fid ); ?>" name="<?php echo esc_attr( $field['name'] ); ?>" rows="3" autocomplete="off" placeholder="<?php echo esc_attr( $ph ); ?>"></textarea>
      <?php else : ?>
      <input class="field__input" id="<?php echo esc_attr( $fid ); ?>" name="<?php echo esc_attr( $field['name'] ); ?>"
             type="<?php echo esc_attr( $field['type'] ); ?>"
             autocomplete="<?php echo esc_attr( $field['autocomplete'] ); ?>"
             placeholder="<?php echo esc_attr( $ph ); ?>"
             <?php echo $field['required'] ? 'required' : ''; ?>>
      <?php endif; ?>
      <span class="field__error" aria-live="polite"></span>
    </p>
    <?php endforeach; ?>

    <p class="check">
      <input class="check__box" type="checkbox" id="<?php echo esc_attr( $id ); ?>-consent" name="consent" required>
      <label class="check__label" for="<?php echo esc_attr( $id ); ?>-consent">
        Согласен на обработку персональных данных и принимаю
        <a class="selectable" href="<?php echo esc_url( $privacy ); ?>" target="_blank" rel="noopener">политику конфиденциальности</a>
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
    <span class="btn__arrow" aria-hidden="true"><svg viewBox="0 0 24 16"><use href="#i-arrow-xl"></use></svg><svg viewBox="0 0 24 16"><use href="#i-arrow-xl"></use></svg></span>
  </button>

  <p class="modal__note" role="status" aria-live="polite"></p>
</form>
