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
	/* Заголовок встаёт в ту же колонку, что и текст под ним. Без этого флага
	   шапка занимала всю ширину листа (заголовок от 33 пикселя), а текст шёл
	   колонкой по центру (от 307) — разрыв в 274 пикселя, который заказчик
	   увидел с первого взгляда на странице про cookie.
	   Флаг только здесь, на обычных страницах: у каталогов и услуг под
	   шапкой идут сетки во всю ширину, и сужать её там нельзя. */
	set_query_var( 'artdom_head_narrow', true );
?>

<main id="main" class="sheet">
  <?php get_template_part( 'template-parts/page-head' ); ?>

  <section class="sec sec--white">
    <div class="wrap prose" data-rise>
      <?php the_content(); ?>
    </div>
  </section>

</main>

<?php
endwhile;
get_footer();
