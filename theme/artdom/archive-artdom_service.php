<?php
/**
 * Список услуг.
 *
 * Не сетка одинаковых карточек, а редакционный список: крупный номер, крупное
 * название, подводка и стрелка. Три равные карточки — самый узнаваемый приём
 * шаблонных сайтов; полосой во всю ширину список читается как оглавление
 * журнала и держит тот же язык, что аккордеон на главной.
 *
 * @package artdom
 */

get_header();

set_query_var( 'artdom_head_title', 'Услуги' );
set_query_var( 'artdom_head_lead', artdom_field( 'services_lead' ) );
/* Поле фотографии живёт на главной; в архиве оно пустое, а строку с именем
   файла шапка принять не может — ей нужен массив. Собираем его сами. */
$artdom_photo = artdom_field( 'services_photo' );
set_query_var(
	'artdom_head_figure',
	is_array( $artdom_photo ) && ! empty( $artdom_photo['url'] )
		? $artdom_photo
		: array( 'url' => get_template_directory_uri() . '/img/uslugi.webp', 'alt' => '' )
);
?>

<main id="main">
  <?php get_template_part( 'template-parts/page-head' ); ?>

  <section class="sec sec--white">
    <div class="wrap">
      <?php if ( have_posts() ) : ?>
      <ol class="svclist">
        <?php $artdom_n = 0; ?>
        <?php while ( have_posts() ) : the_post(); ++$artdom_n; ?>
        <li class="svclist__item" data-rise style="--i: <?php echo (int) $artdom_n; ?>">
          <a class="svclist__link" href="<?php the_permalink(); ?>" draggable="false">
            <span class="svclist__n" aria-hidden="true"><?php echo esc_html( str_pad( $artdom_n, 2, '0', STR_PAD_LEFT ) ); ?></span>
            <span class="svclist__body">
              <span class="svclist__title roll"><span class="roll__a"><?php the_title(); ?></span><span class="roll__b" aria-hidden="true"><?php the_title(); ?></span></span>
              <span class="body svclist__text"><?php echo esc_html( wp_trim_words( (string) get_field( 'svc_lead' ), 22 ) ); ?></span>
            </span>
            <span class="svclist__go" aria-hidden="true"><svg viewBox="0 0 23 6"><use href="#i-arrow"></use></svg><svg viewBox="0 0 23 6"><use href="#i-arrow"></use></svg></span>
          </a>
        </li>
        <?php endwhile; ?>
      </ol>
      <?php else : ?>
      <p class="body">Раздел наполняется.</p>
      <?php endif; ?>
    </div>
  </section>

  <?php get_template_part( 'template-parts/cta-band' ); ?>
</main>

<?php
get_footer();
