<?php
/**
 * Страница статьи блога.
 *
 * Базовый шаблон темы oxboxwise рисовал запись без нашего оформления и с
 * боковой колонкой, которой на сайте нет. Переписан на тот же каркас, что у
 * прочих внутренних страниц: шапка с крошками, текст в .prose, призыв внизу.
 *
 * @package artdom
 */

get_header();

while ( have_posts() ) :
	the_post();

	set_query_var( 'artdom_head_title', get_the_title() );
	set_query_var( 'artdom_head_lead', has_excerpt() ? get_the_excerpt() : '' );
	/* Дата идёт отдельной строкой под подводкой: у статьи она часть смысла,
	   а не служебная пометка — по ней судят, не устарела ли. */
	set_query_var(
		'artdom_head_extra',
		'<p class="pagehead__date"><time datetime="' . esc_attr( get_the_date( 'c' ) ) . '">'
			. esc_html( get_the_date( 'j F Y' ) ) . '</time></p>'
	);
?>

<main id="main">
  <?php get_template_part( 'template-parts/page-head' ); ?>

  <section class="sec sec--white">
    <div class="wrap prose" data-rise>
      <?php the_content(); ?>
    </div>
  </section>

  <?php
  /* Соседние записи: со статьи должен быть выход не только в подвал. */
  $artdom_prev = get_previous_post();
  $artdom_next = get_next_post();
  if ( $artdom_prev || $artdom_next ) :
  ?>
  <section class="sec sec--white postnav">
    <div class="wrap">
      <div class="rule"></div>
      <nav class="postnav__in" aria-label="Другие статьи">
        <?php if ( $artdom_prev ) : ?>
        <a class="postnav__item" href="<?php echo esc_url( get_permalink( $artdom_prev ) ); ?>">
          <span class="postnav__label">Предыдущая</span>
          <span class="postnav__title"><?php echo esc_html( get_the_title( $artdom_prev ) ); ?></span>
        </a>
        <?php endif; ?>
        <?php if ( $artdom_next ) : ?>
        <a class="postnav__item postnav__item--next" href="<?php echo esc_url( get_permalink( $artdom_next ) ); ?>">
          <span class="postnav__label">Следующая</span>
          <span class="postnav__title"><?php echo esc_html( get_the_title( $artdom_next ) ); ?></span>
        </a>
        <?php endif; ?>
      </nav>
    </div>
  </section>
  <?php endif; ?>

  <?php get_template_part( 'template-parts/cta-band' ); ?>
</main>

<?php
endwhile;
get_footer();
