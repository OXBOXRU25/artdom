<?php
/**
 * Список услуг.
 *
 * @package artdom
 */

get_header();

set_query_var( 'artdom_head_title', 'Услуги' );
set_query_var( 'artdom_head_lead', artdom_field( 'services_lead' ) );
?>

<main>
  <?php get_template_part( 'template-parts/page-head' ); ?>

  <section class="sec sec--white">
    <div class="wrap">
      <div class="rule"></div>
      <?php if ( have_posts() ) : ?>
      <div class="grid-cards">
        <?php while ( have_posts() ) : the_post(); ?>
        <article class="svc" data-rise>
          <h2 class="svc__title"><a class="roll" href="<?php the_permalink(); ?>"><span class="roll__a"><?php the_title(); ?></span><span class="roll__b" aria-hidden="true"><?php the_title(); ?></span></a></h2>
          <p class="body svc__text"><?php echo esc_html( wp_trim_words( (string) get_field( 'svc_lead' ), 26 ) ); ?></p>
          <?php artdom_btn( 'Узнать больше', get_permalink(), 'btn btn--sm' ); ?>
        </article>
        <?php endwhile; ?>
      </div>
      <?php else : ?>
      <p class="body">Раздел наполняется.</p>
      <?php endif; ?>
    </div>
  </section>

  <?php get_template_part( 'template-parts/cta-band' ); ?>
</main>

<?php
get_footer();
