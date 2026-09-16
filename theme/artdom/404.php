<?php
/**
 * Страница не найдена.
 *
 * @package artdom
 */

get_header();

set_query_var( 'artdom_head_title', artdom_field( 'err404_title', true ) );
set_query_var( 'artdom_head_lead', artdom_field( 'err404_text', true ) );
?>

<main id="main" class="sheet">
  <?php get_template_part( 'template-parts/page-head' ); ?>

  <section class="sec sec--white">
    <div class="wrap notfound" data-rise>
      <?php artdom_btn( artdom_field( 'err404_btn', true ), get_post_type_archive_link( 'artdom_object' ), 'btn btn--wide' ); ?>
      <?php get_search_form(); ?>
    </div>
  </section>

</main>

<?php
get_footer();
