<?php
/**
 * Секция: Надёжность — закреплённая сцена
 *
 * Каркас держит высоту по числу кадров, сцена внутри липкая и не двигается,
 * пока каркас проезжает мимо. Слои меняются по доле прокрутки внутри каркаса.
 * Кадры приходят повторителем из админки.
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
      <div class="guaranty__bg">
        <?php foreach ( $slides as $i => $s ) : ?>
        <?php
        $first = ( 0 === $i );
        $src   = is_array( $s['image'] ) && ! empty( $s['image']['url'] )
          ? $s['image']['url']
          : get_template_directory_uri() . '/img/' . ( is_string( $s['image'] ) ? $s['image'] : 'garanty.webp' );
        ?>
        <img draggable="false" src="<?php echo esc_url( $src ); ?>" alt="" width="1920" height="963"<?php echo $first ? '' : ' loading="lazy"'; ?> decoding="async" data-slide="<?php echo (int) $i; ?>"<?php echo $first ? ' data-on="true"' : ''; ?>>
        <?php endforeach; ?>
      </div>
      <div class="guaranty__text wrap">
        <?php foreach ( $slides as $i => $s ) : ?>
        <?php if ( 0 === $i ) : ?>
        <h2 class="h2--big guaranty__title" data-slide="0" data-on="true"><?php echo artdom_lines( $s['title'] ); ?></h2>
        <?php else : ?>
        <p class="h2--big guaranty__title" data-slide="<?php echo (int) $i; ?>"><?php echo artdom_lines( $s['title'] ); ?></p>
        <?php endif; ?>
        <?php endforeach; ?>
      </div>
      <div class="guaranty__steps" aria-hidden="true">
        <?php foreach ( $slides as $i => $s ) : ?>
        <span data-slide="<?php echo (int) $i; ?>"<?php echo 0 === $i ? ' data-on="true"' : ''; ?>></span>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
<?php endif; ?>
