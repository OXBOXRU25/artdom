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

$artdom_stats  = artdom_reviews_stats();
$artdom_rating = $artdom_stats['avg'];
$artdom_count  = $artdom_stats['count'];
?>

<main id="main" class="sheet">
  <section class="sec sec--white revpage">
    <div class="wrap revpage__in">

      <?php /* Имя обязательно: с появлением плашки про cookie на странице
               стало два ориентира complementary, и безымянный скринридер
               объявляет просто «дополнительно». */ ?>
      <aside class="revpage__side" aria-label="Оценка и общий рейтинг">
        <h1 class="h1 revpage__title vh"><?php echo esc_html( artdom_field( "page_reviews_title", true ) ); ?></h1>
        <p class="body revpage__lead"><?php echo artdom_lines( artdom_field( "page_reviews_lead", true ) ); ?></p>

        <?php if ( $artdom_rating ) : ?>
        <p class="rating"><span><?php echo esc_html( number_format( (float) $artdom_rating, 1, ',', '' ) ); ?></span><?php echo artdom_stars( $artdom_rating ); ?></p>
        <?php endif; ?>
        <?php if ( $artdom_count ) : ?>
        <p class="body revpage__count">на основе <strong><?php echo (int) $artdom_count; ?></strong> <?php echo esc_html( artdom_plural( (int) $artdom_count, array( 'отзыва', 'отзывов', 'отзывов' ) ) ); ?></p>
        <?php endif; ?>

        <?php artdom_btn( artdom_field( "reviews_add_btn", true ), '#', 'btn btn--wide revpage__btn', array( 'data-form-open' => 'review' ) ); ?>
      </aside>

      <div class="revpage__list" data-revlist>
        <?php if ( have_posts() ) : ?>
        <?php
        while ( have_posts() ) :
          the_post();
          get_template_part( 'template-parts/review-row' );
        endwhile;
        ?>
        <?php
        /* Подгрузка по нажатию, а не постраничная навигация: отзывы читают
           подряд, и уводить человека на вторую страницу значит терять его.
           Без скрипта это обычная ссылка на следующую страницу — работает
           и так. */
        $artdom_more = get_next_posts_link( artdom_field( "reviews_more_btn", true ) );
        ?>
        <?php if ( $artdom_more ) : ?>
        <p class="revpage__more" data-revmore><?php echo wp_kses_post( $artdom_more ); ?></p>
        <?php endif; ?>
        <?php else : ?>
        <p class="body"><?php echo esc_html( artdom_field( "empty_reviews", true ) ); ?></p>
        <?php endif; ?>
      </div>

    </div>
  </section>

</main>

<?php
get_footer();
