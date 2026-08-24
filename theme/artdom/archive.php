<?php
/**
 * Запасной архив.
 *
 * У сайта нет блога, поэтому сюда попадают только неожиданные случаи — новый
 * тип записи без своего шаблона, дата, автор. Раньше здесь лежал шаблон
 * базовой темы с короткими тегами <?, и любая такая страница отдавала 500.
 *
 * @package artdom
 */

get_header();

set_query_var( 'artdom_head_title', get_the_archive_title() );
set_query_var( 'artdom_head_lead', get_the_archive_description() );
?>

<main>
  <?php get_template_part( 'template-parts/page-head' ); ?>

  <section class="sec sec--white">
    <div class="wrap">
      <div class="rule"></div>
      <?php if ( have_posts() ) : ?>
      <ul class="results">
        <?php while ( have_posts() ) : the_post(); ?>
        <li class="results__item">
          <h2 class="results__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
        </li>
        <?php endwhile; ?>
      </ul>
      <?php the_posts_pagination( array( 'mid_size' => 2, 'prev_text' => 'Назад', 'next_text' => 'Дальше', 'class' => 'pager' ) ); ?>
      <?php else : ?>
      <p class="body">Здесь пока пусто.</p>
      <?php endif; ?>
    </div>
  </section>
</main>

<?php
get_footer();
