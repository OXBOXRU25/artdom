<?php
/**
 * Обычная страница: «О компании», правовые документы.
 *
 * Базовый шаблон темы oxboxwise был написан с короткими тегами <? — на боевом
 * PHP они не выполняются и страница отдаёт исходник. Переписан целиком.
 *
 * @package artdom
 */

get_header();

while ( have_posts() ) :
	the_post();
	set_query_var( 'artdom_head_title', get_the_title() );
	set_query_var( 'artdom_head_lead', has_excerpt() ? get_the_excerpt() : '' );
?>

<main>
  <?php get_template_part( 'template-parts/page-head' ); ?>

  <section class="sec sec--white">
    <div class="wrap prose" data-rise>
      <?php the_content(); ?>
    </div>
  </section>

  <?php get_template_part( 'template-parts/cta-band' ); ?>
</main>

<?php
endwhile;
get_footer();
