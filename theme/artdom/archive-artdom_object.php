<?php
/**
 * Каталог объектов и страницы категорий.
 *
 * Один шаблон на оба случая: у категории меняется только заголовок и активный
 * фильтр, разметка та же. Отдельный taxonomy-*.php был бы копией с двумя
 * отличиями, которые разъедутся при первой правке.
 *
 * @package artdom
 */

get_header();

$terms   = get_terms( array( 'taxonomy' => 'artdom_object_type', 'hide_empty' => true ) );
$current = is_tax( 'artdom_object_type' ) ? get_queried_object_id() : 0;
$all     = get_post_type_archive_link( 'artdom_object' );
$total   = (int) $GLOBALS['wp_query']->found_posts;

set_query_var( 'artdom_head_title', is_tax( 'artdom_object_type' ) ? single_term_title( '', false ) : 'Объекты' );
set_query_var(
	'artdom_head_lead',
	is_tax( 'artdom_object_type' )
		? term_description()
		: 'Квартиры, резиденции и апартаменты в проверенных домах. Часть предложений не публикуется в открытом доступе — о них расскажет брокер.'
);
?>

<main>
  <?php get_template_part( 'template-parts/page-head' ); ?>

  <section class="sec sec--white catalog">
    <div class="wrap">

      <?php if ( ! is_wp_error( $terms ) && $terms ) : ?>
      <div class="filters" role="group" aria-label="Фильтр по типу объекта">
        <a class="filters__chip" href="<?php echo esc_url( $all ); ?>"<?php echo $current ? '' : ' aria-current="true"'; ?>>Все</a>
        <?php foreach ( $terms as $t ) : ?>
        <a class="filters__chip" href="<?php echo esc_url( get_term_link( $t ) ); ?>"<?php echo $current === $t->term_id ? ' aria-current="true"' : ''; ?>><?php echo esc_html( $t->name ); ?><span class="filters__n"><?php echo (int) $t->count; ?></span></a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <p class="catalog__count muted"><?php echo esc_html( $total ); ?> <?php echo esc_html( artdom_plural( $total, array( 'объект', 'объекта', 'объектов' ) ) ); ?></p>

      <div class="rule"></div>

      <?php if ( have_posts() ) : ?>
      <div class="grid-cards">
        <?php
        while ( have_posts() ) :
          the_post();
          get_template_part( 'template-parts/object-card' );
        endwhile;
        ?>
      </div>

      <?php
      the_posts_pagination(
        array(
          'mid_size'           => 2,
          'prev_text'          => 'Назад',
          'next_text'          => 'Дальше',
          'screen_reader_text' => 'Страницы каталога',
          'class'              => 'pager',
        )
      );
      ?>
      <?php else : ?>
      <p class="body">В этой категории пока нет объектов. Напишите нам&nbsp;— расскажем о том, что не публикуется в открытом доступе.</p>
      <?php endif; ?>

    </div>
  </section>

  <?php get_template_part( 'template-parts/cta-band' ); ?>
</main>

<?php
get_footer();
