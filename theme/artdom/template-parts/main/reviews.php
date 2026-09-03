<?php
/**
 * Секция: Отзывы
 *
 * Отзывы берутся из раздела «Отзывы». Нет ни одного — секции нет вовсе:
 * лента с пустым бегунком читается как поломка.
 *
 * @package artdom
 */

$reviews = new WP_Query(
	array(
		'post_type'           => 'artdom_review',
		'posts_per_page'      => 8,
		'orderby'             => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	)
);

if ( $reviews->have_posts() ) :
	$rating = artdom_field( 'reviews_rating' );
	$count  = artdom_field( 'reviews_count' );
?>
  <!-- ============ Отзывы ============ -->
  <section class="sec sec--surface reviews" id="reviews">
    <div class="wrap">
      <div class="sechead" data-rise>
        <div class="sechead__text">
          <h2 class="h2"><?php echo esc_html( artdom_field( 'reviews_title' ) ); ?></h2>
          <?php if ( $rating ) : ?>
          <p class="rating">
            <span><?php echo esc_html( $rating ); ?></span>
            <?php echo artdom_stars( 5, 'Оценка 5 из 5' ); ?>
          </p>
          <?php endif; ?>
          <?php if ( $count ) : ?>
          <p class="body">на основе <strong><?php echo esc_html( $count ); ?></strong> отзывов</p>
          <?php endif; ?>
        </div>
      </div>

      <div class="rule"></div>

      <div class="slider" data-slider data-rise>
        <div class="slider__track" tabindex="0" role="group" aria-label="Отзывы, лента">
          <?php
          while ( $reviews->have_posts() ) :
            $reviews->the_post();
            get_template_part( 'template-parts/review-card' );
          endwhile;
          wp_reset_postdata();
          ?>
        </div>
        <div class="slider__bar" aria-hidden="true"><div class="slider__thumb" data-thumb></div></div>
      </div>

      <?php
      /* Кнопка стоит в разметке ПОСЛЕ ленты — так она и читается на телефоне:
         сперва отзывы, потом бегунок, потом призыв. На широком экране сетка
         поднимает её обратно в шапку секции, справа от заголовка. */
      ?>
      <div class="reviews__cta" data-rise>
        <?php artdom_btn( artdom_field( 'reviews_btn_text' ), '#', 'btn btn--wide', array( 'data-form-open' => 'review' ) ); ?>
      </div>
    </div>
  </section>
<?php endif; ?>
