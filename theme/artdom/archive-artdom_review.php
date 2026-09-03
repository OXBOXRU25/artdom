<?php
/**
 * Все отзывы.
 *
 * Построение снято с communityclinic.ru/reviews: слева липкий столбец с
 * заголовком, оценкой и призывом, справа лента отзывов во всю высоту.
 * Так шапка не съедает первый экран целиком — отзывы видно сразу, а
 * призыв остаётся на глазах, пока их листают.
 *
 * У референса карточки лежат тенями на сером; у нас теней нет нигде, и
 * ставить их ради одной страницы — значит завести второй язык. Отзывы
 * разделены волосяными линиями, как всё остальное на внутренних страницах.
 *
 * @package artdom
 */

get_header();

$artdom_rating = artdom_field( 'reviews_rating' );
$artdom_count  = artdom_field( 'reviews_count' );
?>

<main id="main">
  <section class="sec sec--white revpage">
    <div class="wrap revpage__in">

      <aside class="revpage__side">
        <?php echo artdom_crumbs(); ?>
        <h1 class="h1 revpage__title">Отзывы</h1>
        <p class="body revpage__lead">Отзывы приходят с Яндекс.Карт, из Авито и напрямую от клиентов. Публикуем как есть.</p>

        <?php if ( $artdom_rating ) : ?>
        <p class="rating"><span><?php echo esc_html( $artdom_rating ); ?></span><?php echo artdom_stars( 5, 'Оценка 5 из 5' ); ?></p>
        <?php endif; ?>
        <?php if ( $artdom_count ) : ?>
        <p class="body revpage__count">на основе <strong><?php echo esc_html( $artdom_count ); ?></strong> отзывов</p>
        <?php endif; ?>

        <?php artdom_btn( 'Оставить отзыв', '#', 'btn btn--wide revpage__btn', array( 'data-form-open' => 'review' ) ); ?>
      </aside>

      <div class="revpage__list">
        <?php if ( have_posts() ) : ?>
        <?php
        while ( have_posts() ) :
          the_post();
          get_template_part( 'template-parts/review-row' );
        endwhile;
        ?>
        <?php
        the_posts_pagination(
          array(
            'mid_size'           => 1,
            'prev_text'          => 'Назад',
            'next_text'          => 'Дальше',
            'screen_reader_text' => 'Страницы отзывов',
          )
        );
        ?>
        <?php else : ?>
        <p class="body">Отзывов пока нет.</p>
        <?php endif; ?>
      </div>

    </div>
  </section>

  <?php
  set_query_var( 'artdom_cta_title', 'Работали с нами?' );
  set_query_var( 'artdom_cta_text', 'Расскажите, как всё прошло&nbsp;— это помогает нам и тем, кто выбирает брокера.' );
  set_query_var( 'artdom_cta_btn', 'Оставить отзыв' );
  set_query_var( 'artdom_cta_form', 'review' );
  get_template_part( 'template-parts/cta-band' );
  ?>
</main>

<?php
get_footer();
