<?php
/**
 * Страница не найдена.
 *
 * @package artdom
 */

get_header();

set_query_var( 'artdom_head_title', 'Такой страницы нет' );
set_query_var( 'artdom_head_lead', 'Возможно, объект уже продан или адрес набран с опечаткой. Посмотрите каталог&nbsp;— там всё, что сейчас в работе.' );
?>

<main>
  <?php get_template_part( 'template-parts/page-head' ); ?>

  <section class="sec sec--white">
    <div class="wrap notfound" data-rise>
      <?php artdom_btn( 'Смотреть объекты', get_post_type_archive_link( 'artdom_object' ), 'btn btn--wide' ); ?>
      <?php get_search_form(); ?>
    </div>
  </section>

  <?php get_template_part( 'template-parts/cta-band' ); ?>
</main>

<?php
get_footer();
