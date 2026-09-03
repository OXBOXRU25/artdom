<?php
/**
 * Секция: Блог
 *
 * Три свежие записи под отзывами. Записей нет — секции нет вовсе: пустая
 * подборка читается как поломка, а не как пустота, ровно как у объектов.
 *
 * @package artdom
 */

$artdom_posts = new WP_Query(
	array(
		'post_type'           => 'post',
		'posts_per_page'      => 3,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	)
);

if ( $artdom_posts->have_posts() ) :
	$artdom_blog_page = (int) get_option( 'page_for_posts' );
	$artdom_blog_link = $artdom_blog_page ? get_permalink( $artdom_blog_page ) : home_url( '/' );
?>
  <!-- ============ Блог ============ -->
  <section class="sec sec--white blog" id="blog">
    <div class="wrap">
      <div class="sechead" data-rise>
        <div class="sechead__text">
          <h2 class="h2"><?php echo esc_html( artdom_field( 'blog_title' ) ); ?></h2>
          <p class="body"><?php echo artdom_lines( artdom_field( 'blog_lead' ) ); ?></p>
        </div>
        <?php artdom_btn( artdom_field( 'blog_btn_text' ), $artdom_blog_link, 'btn btn--wide' ); ?>
      </div>

      <div class="rule"></div>

      <div class="blog__grid">
        <?php
        while ( $artdom_posts->have_posts() ) :
          $artdom_posts->the_post();
          get_template_part( 'template-parts/post-card' );
        endwhile;
        wp_reset_postdata();
        ?>
      </div>
    </div>
  </section>
<?php endif; ?>
