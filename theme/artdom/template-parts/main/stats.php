<?php
/**
 * Секция: Цифры
 *
 * @package artdom
 */

$items = artdom_field( 'stats_items' );
$items = is_array( $items ) ? $items : array();

if ( $items ) :
?>
  <!-- ============ Цифры ============ -->
  <section class="sec--white stats-sec">
    <div class="wrap">
      <ul class="stats">
        <?php foreach ( $items as $item ) : ?>
        <li class="stats__item" data-rise>
          <p class="stats__num"><?php echo esc_html( $item['number'] ); ?></p>
          <p class="stats__label"><?php echo artdom_lines( $item['label'] ); ?></p>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </section>
<?php endif; ?>
