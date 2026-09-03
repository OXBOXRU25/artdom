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
        <?php
        /* Ссылка — вся карточка, а не кнопка внутри неё. Пять одинаковых
           синих кнопок подряд были самым громким пятном на экране, и глаз
           считал их вместо названий услуг. Заголовок остаётся заголовком,
           а <a> оборачивает карточку целиком: одна цель нажатия во всю
           площадь вместо мелкой кнопки. */
        $artdom_price = (string) get_field( 'svc_price' );
        ?>
        <article class="svc" data-rise>
          <a class="svc__link" href="<?php the_permalink(); ?>">
            <h2 class="svc__title"><?php the_title(); ?></h2>
            <p class="body svc__text" data-clip="3"><?php echo esc_html( (string) get_field( 'svc_lead' ) ); ?></p>
            <span class="svc__foot">
              <span class="svc__price"><?php echo $artdom_price ? esc_html( $artdom_price ) : 'Подробнее'; ?></span>
              <span class="svc__arrow" aria-hidden="true"><svg viewBox="0 0 24 16"><use href="#i-arrow-xl"></use></svg></span>
            </span>
          </a>
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
