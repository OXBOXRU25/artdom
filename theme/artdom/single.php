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

?>

<main id="main" class="sheet">
  <?php /* Шапка статьи по центру, как на контактах и «О компании»: тот же
           приём на всех внутренних страницах. Дата под подводкой. */ ?>
  <section class="chero chero--post">
    <div class="wrap chero__in">
      <h1 class="chero__title"><?php the_title(); ?></h1>
      <?php if ( has_excerpt() ) : ?>
      <p class="chero__lead"><?php echo esc_html( get_the_excerpt() ); ?></p>
      <?php endif; ?>
      <p class="chero__date"><time datetime="<?php echo esc_attr( get_the_date( "c" ) ); ?>"><?php echo esc_html( get_the_date( "j F Y" ) ); ?></time></p>
    </div>
  </section>

  <?php
  /* Галерея идёт СРАЗУ под заголовком, а не под текстом: снимки здесь
     вводят в материал, а не иллюстрируют его конец. Часть общая с объектами:
     приём один, значит и код один — иначе увеличение починят в одном месте
     и забудут во втором. */
  $artdom_shots = get_field( 'post_gallery' );
  if ( is_array( $artdom_shots ) && $artdom_shots ) :
	  set_query_var( 'artdom_gallery', $artdom_shots );
  ?>
  <section class="sec sec--white postgal">
    <div class="wrap" data-rise>
      <?php get_template_part( 'template-parts/gallery' ); ?>
    </div>
  </section>
  <?php endif; ?>

  <section class="sec sec--white postbody">
    <div class="wrap postbody__in">
      <div class="prose" data-rise>
        <?php the_content(); ?>
      </div>
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

</main>

<?php
endwhile;
get_footer();
