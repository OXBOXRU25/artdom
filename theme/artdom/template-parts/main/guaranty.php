<?php
/**
 * Секция: Надёжность — закреплённая сцена
 *
 * Каркас держит высоту по числу кадров, сцена внутри липкая. Каждый кадр —
 * самостоятельная панель со своей фотографией и своим заголовком, обрезанная
 * по краям сцены. Панель приезжает снизу, а текст внутри неё смещается
 * навстречу ровно на её ход — поэтому текст стоит на экране неподвижно и
 * просто открывается снизу вверх вместе с фотографией.
 *
 * Построение снято с mimcocapital.com (замеры в STATUS.md).
 *
 * @package artdom
 */

$slides = artdom_field( 'guaranty_slides' );
$slides = is_array( $slides ) ? $slides : array();

if ( $slides ) :
?>
  <!-- ============ Надёжность ============ -->
  <section class="guaranty" data-guaranty aria-label="Надежность и гарантии" style="--slides: <?php echo (int) count( $slides ); ?>">
    <div class="guaranty__stage">

      <?php foreach ( $slides as $i => $s ) : ?>
      <?php
      $first = ( 0 === $i );
      $src   = is_array( $s['image'] ) && ! empty( $s['image']['url'] )
        ? $s['image']['url']
        : get_template_directory_uri() . '/img/' . ( is_string( $s['image'] ) ? $s['image'] : 'garanty.webp' );
      ?>
      <div class="guaranty__slide" data-slide="<?php echo (int) $i; ?>">
        <?php /* Увеличение и отставание живут на этой обёртке, а не на завесе:
                 иначе вместе с фотографией растягивалось бы и затемнение. */ ?>
        <div class="guaranty__media">
          <img draggable="false" src="<?php echo esc_url( $src ); ?>" alt=""<?php echo $first ? '' : ' loading="lazy"'; ?> decoding="async">
        </div>
        <div class="guaranty__veil" aria-hidden="true"></div>
        <div class="guaranty__content wrap">
          <?php if ( $first ) : ?>
          <h2 class="h2--big guaranty__title"><?php echo artdom_lines( $s['title'] ); ?></h2>
          <?php else : ?>
          <p class="h2--big guaranty__title"><?php echo artdom_lines( $s['title'] ); ?></p>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>

      <div class="guaranty__steps" aria-hidden="true">
        <?php foreach ( $slides as $i => $s ) : ?>
        <span></span>
        <?php endforeach; ?>
      </div>

    </div>
  </section>
<?php endif; ?>
