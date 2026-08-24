<?php
/**
 * Результаты поиска.
 *
 * @package artdom
 */

get_header();

$found = (int) $GLOBALS['wp_query']->found_posts;

set_query_var( 'artdom_head_title', 'Поиск' );
set_query_var(
	'artdom_head_lead',
	sprintf( 'По запросу «%s» %s %d %s', get_search_query(), 1 === $found ? 'найден' : 'найдено', $found, artdom_plural( $found, array( 'результат', 'результата', 'результатов' ) ) )
);
?>

<main>
  <?php get_template_part( 'template-parts/page-head' ); ?>

  <section class="sec sec--white">
    <div class="wrap">
      <?php get_search_form(); ?>
      <div class="rule"></div>
      <?php if ( have_posts() ) : ?>
      <ul class="results">
        <?php while ( have_posts() ) : the_post(); ?>
        <li class="results__item">
          <h2 class="results__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
          <p class="body"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( get_the_excerpt() ), 28 ) ); ?></p>
        </li>
        <?php endwhile; ?>
      </ul>
      <?php the_posts_pagination( array( 'mid_size' => 2, 'prev_text' => 'Назад', 'next_text' => 'Дальше', 'class' => 'pager' ) ); ?>
      <?php else : ?>
      <p class="body">Ничего не нашлось. Попробуйте другое слово или посмотрите каталог целиком.</p>
      <?php endif; ?>
    </div>
  </section>
</main>

<?php
get_footer();
