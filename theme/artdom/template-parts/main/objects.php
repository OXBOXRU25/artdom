<?php
/**
 * Секция: Избранные объекты
 *
 * Объекты берутся из раздела «Объекты», свежие сверху. Сколько показывать —
 * поле в админке. Если объектов нет вовсе, секция не рисуется: пустая лента
 * с бегунком выглядит поломкой, а не пустотой.
 *
 * @package artdom
 */

$count = (int) artdom_field( 'objects_count' );
$count = $count > 0 ? $count : 6;

$objects = new WP_Query(
	array(
		'post_type'           => 'artdom_object',
		'posts_per_page'      => $count,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	)
);

if ( $objects->have_posts() ) :
?>
  <!-- ============ Избранные объекты ============ -->
  <section class="sec sec--white objects" id="objects">
    <div class="wrap">
      <div class="sechead" data-rise>
        <div class="sechead__text">
          <h2 class="h2"><?php echo esc_html( artdom_field( 'objects_title' ) ); ?></h2>
          <p class="body"><?php echo artdom_lines( artdom_field( 'objects_lead' ) ); ?></p>
        </div>
        <?php artdom_btn( artdom_field( 'objects_btn_text' ), artdom_field( 'objects_btn_link' ), 'btn btn--wide' ); ?>
      </div>

      <div class="rule"></div>

      <div class="slider" data-slider data-rise>
        <div class="slider__track" tabindex="0" role="group" aria-label="Избранные объекты, лента">
          <?php
          while ( $objects->have_posts() ) :
            $objects->the_post();
            get_template_part( 'template-parts/object-card' );
          endwhile;
          wp_reset_postdata();
          ?>
        </div>
        <div class="slider__bar" aria-hidden="true"><div class="slider__thumb" data-thumb></div></div>
      </div>
    </div>
  </section>
<?php endif; ?>
