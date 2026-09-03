<?php
/**
 * Лента записей блога.
 *
 * WordPress берёт этот шаблон для страницы, назначенной лентой записей. Она
 * назначается при посеве, см. artdom_fill_demo(). Базовый index.php от темы
 * oxboxwise сюда не годится: он рисует записи вообще без нашего оформления.
 *
 * @package artdom
 */

get_header();

$artdom_blog_page = (int) get_option( 'page_for_posts' );

set_query_var( 'artdom_head_title', $artdom_blog_page ? get_the_title( $artdom_blog_page ) : 'Блог' );
/* Подводки нет: заголовок «Блог» и лента под ним объясняют раздел сами.
   Описание страницы остаётся в админке — вернуть его сюда одна строка. */
set_query_var( 'artdom_head_lead', '' );
?>

<main id="main">
  <?php get_template_part( 'template-parts/page-head' ); ?>

  <section class="sec sec--white">
    <div class="wrap">
      <div class="rule"></div>

      <?php if ( have_posts() ) : ?>
      <div class="blog__grid">
        <?php
        while ( have_posts() ) :
          the_post();
          get_template_part( 'template-parts/post-card' );
        endwhile;
        ?>
      </div>

      <?php
      /* Постраничная навигация нужна с самого начала: без неё после десятой
         записи остальные становятся недостижимы, и заметит это не заказчик,
         а поисковик. */
      the_posts_pagination(
        array(
          'mid_size'           => 1,
          'prev_text'          => 'Назад',
          'next_text'          => 'Дальше',
          'screen_reader_text' => 'Страницы ленты',
        )
      );
      ?>
      <?php else : ?>
      <p class="body">Раздел наполняется.</p>
      <?php endif; ?>
    </div>
  </section>

  <?php get_template_part( 'template-parts/cta-band' ); ?>
</main>

<?php
get_footer();
