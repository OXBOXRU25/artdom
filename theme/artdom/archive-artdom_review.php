<?php
/**
 * Все отзывы.
 *
 * @package artdom
 */

get_header();

$rating = artdom_field( 'reviews_rating' );
$count  = artdom_field( 'reviews_count' );

set_query_var( 'artdom_head_title', 'Отзывы' );
set_query_var( 'artdom_head_lead', 'Отзывы приходят с Яндекс.Карт, из Авито и напрямую от клиентов. Публикуем как есть.' );
set_query_var(
	'artdom_head_extra',
	$rating
		? '<p class="rating"><span>' . esc_html( $rating ) . '</span>' . artdom_stars( 5, 'Оценка 5 из 5' ) . '</p>'
			. ( $count ? '<p class="body">на основе <strong>' . esc_html( $count ) . '</strong> отзывов</p>' : '' )
		: ''
);
?>

<main id="main">
  <?php get_template_part( 'template-parts/page-head' ); ?>

  <section class="sec sec--surface">
    <div class="wrap">
      <div class="rule"></div>
      <?php if ( have_posts() ) : ?>
      <div class="grid-cards">
        <?php
        while ( have_posts() ) :
          the_post();
          get_template_part( 'template-parts/review-card' );
        endwhile;
        ?>
      </div>
      <?php
      the_posts_pagination(
        array( 'mid_size' => 2, 'prev_text' => 'Назад', 'next_text' => 'Дальше', 'screen_reader_text' => 'Страницы отзывов', 'class' => 'pager' )
      );
      ?>
      <?php else : ?>
      <p class="body">Отзывов пока нет.</p>
      <?php endif; ?>
    </div>
  </section>

  <?php
  set_query_var( 'artdom_cta_title', 'Работали с нами?' );
  set_query_var( 'artdom_cta_text', 'Расскажите, как всё прошло&nbsp;— это помогает нам и тем, кто выбирает брокера.' );
  set_query_var( 'artdom_cta_btn', 'Оставить отзыв' );
  get_template_part( 'template-parts/cta-band' );
  ?>
</main>

<?php
get_footer();
