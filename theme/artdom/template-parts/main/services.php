<?php
/**
 * Секция: Услуги
 *
 * Пункты аккордеона приходят повторителем из админки. Первый открыт,
 * остальные закрыты — это состояние из макета.
 *
 * @package artdom
 */

$items = artdom_field( 'services_items' );
$items = is_array( $items ) ? $items : array();
$photo = artdom_field( 'services_photo' );
?>
  <!-- ============ Услуги ============ -->
  <section class="sec sec--surface services" id="services">
    <div class="wrap services__in">
      <div class="services__col">
        <div class="services__head" data-rise>
          <h2 class="h2"><?php echo esc_html( artdom_field( 'services_title' ) ); ?></h2>
          <p class="body"><?php echo artdom_lines( artdom_field( 'services_lead' ) ); ?></p>
        </div>

        <div class="acc" data-acc data-rise>
          <?php foreach ( $items as $i => $item ) : ?>
            <?php
            $n    = $i + 1;
            $open = ( 0 === $i );
            $link = ! empty( $item['btn_link'] ) ? $item['btn_link'] : '#';
            $text = ! empty( $item['btn_text'] ) ? $item['btn_text'] : 'Узнать больше';
            ?>
          <div class="acc__item" data-open="<?php echo $open ? 'true' : 'false'; ?>">
            <h3>
              <button class="acc__btn" type="button" aria-expanded="<?php echo $open ? 'true' : 'false'; ?>" aria-controls="acc-<?php echo (int) $n; ?>">
                <span><?php echo esc_html( $item['title'] ); ?></span>
                <span class="acc__icon" aria-hidden="true"><svg viewBox="0 0 12 12"><use href="#i-plus"></use></svg></span>
              </button>
            </h3>
            <div class="acc__panel" id="acc-<?php echo (int) $n; ?>">
              <div class="acc__panelIn">
                <div class="acc__body">
                  <p class="body"><?php echo artdom_lines( $item['text'] ); ?></p>
                  <?php artdom_btn( $text, $link, 'btn btn--sm' ); ?>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <figure class="services__media" data-rise="shutter">
        <?php artdom_img( $photo, 'uslugi.webp', 'Фасад современного жилого комплекса', array( 710, 722 ) ); ?>
      </figure>
    </div>
  </section>
