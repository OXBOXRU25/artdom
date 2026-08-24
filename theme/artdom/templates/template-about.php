<?php
/**
 * Template Name: О компании
 * Template Post Type: page
 *
 * Страница доверия, а не текстовая справка: человек приходит сюда решить,
 * можно ли доверить этим людям сделку на сотни миллионов. Поэтому блоки идут
 * в порядке возрастания доказательности — сперва кто мы, потом цифры, потом
 * принципы, потом путь и живые люди.
 *
 * Пустой блок не рисуется вовсе: недозаполненная страница должна выглядеть
 * короче, а не дырявой.
 *
 * @package artdom
 */

get_header();

while ( have_posts() ) :
	the_post();

	$intro_title = artdom_field( 'ab_intro_title' );
	$intro_text  = artdom_field( 'ab_intro_text' );
	$photo       = artdom_field( 'ab_photo' );
	$principles  = artdom_field( 'ab_principles' );
	$path        = artdom_field( 'ab_path' );
	$team        = artdom_field( 'ab_team' );

	set_query_var( 'artdom_head_title', get_the_title() );
	set_query_var( 'artdom_head_lead', has_excerpt() ? get_the_excerpt() : '' );
?>

<main>
  <?php get_template_part( 'template-parts/page-head' ); ?>

  <?php if ( $intro_text ) : ?>
  <section class="sec sec--white abintro">
    <div class="wrap abintro__in">
      <div class="abintro__text" data-rise>
        <?php if ( $intro_title ) : ?>
        <h2 class="h2"><?php echo artdom_lines( $intro_title ); ?></h2>
        <?php endif; ?>
        <?php foreach ( preg_split( '/\R{2,}/u', trim( (string) $intro_text ) ) as $p ) : ?>
        <p class="body"><?php echo artdom_lines( $p ); ?></p>
        <?php endforeach; ?>
      </div>
      <figure class="abintro__media" data-rise="shutter">
        <?php artdom_img( $photo, 'uslugi.webp', 'Офис компании АРТДОМ', array( 710, 722 ) ); ?>
      </figure>
    </div>
  </section>
  <?php endif; ?>

  <?php get_template_part( 'template-parts/main/stats' ); ?>

  <?php if ( is_array( $principles ) && $principles ) : ?>
  <section class="sec sec--surface">
    <div class="wrap">
      <h2 class="h2" data-rise><?php echo esc_html( artdom_field( 'ab_principles_title' ) ); ?></h2>
      <div class="rule"></div>
      <ol class="steps steps--three">
        <?php foreach ( $principles as $i => $pr ) : ?>
        <li class="steps__item" data-rise>
          <p class="steps__n" aria-hidden="true"><?php echo esc_html( str_pad( $i + 1, 2, '0', STR_PAD_LEFT ) ); ?></p>
          <h3 class="steps__title"><?php echo esc_html( $pr['title'] ); ?></h3>
          <p class="body"><?php echo artdom_lines( $pr['text'] ); ?></p>
        </li>
        <?php endforeach; ?>
      </ol>
    </div>
  </section>
  <?php endif; ?>

  <?php /* Основатель: портрет и прямая речь. Те же поля, что на главной. */ ?>
  <section class="sec sec--white founder">
    <div class="wrap founder__in" data-rise>
      <div class="founder__portrait">
        <?php
        artdom_img(
          artdom_field( 'about_portrait' ),
          'founder.webp',
          artdom_field( 'about_name' ) . ', ' . artdom_field( 'about_role' ),
          array( 181, 181 )
        );
        ?>
      </div>
      <blockquote class="founder__quote"><?php echo artdom_lines( artdom_field( 'about_quote' ) ); ?></blockquote>
      <p class="founder__who"><?php echo esc_html( artdom_field( 'about_name' ) ); ?><span class="about__role"><?php echo esc_html( artdom_field( 'about_role' ) ); ?></span></p>
    </div>
  </section>

  <?php if ( is_array( $path ) && $path ) : ?>
  <section class="sec sec--surface">
    <div class="wrap">
      <h2 class="h2" data-rise><?php echo esc_html( artdom_field( 'ab_path_title' ) ); ?></h2>
      <ol class="path">
        <?php foreach ( $path as $step ) : ?>
        <li class="path__item" data-rise>
          <p class="path__year"><?php echo esc_html( $step['year'] ); ?></p>
          <div class="path__body">
            <h3 class="path__title"><?php echo esc_html( $step['title'] ); ?></h3>
            <?php if ( ! empty( $step['text'] ) ) : ?>
            <p class="body"><?php echo artdom_lines( $step['text'] ); ?></p>
            <?php endif; ?>
          </div>
        </li>
        <?php endforeach; ?>
      </ol>
    </div>
  </section>
  <?php endif; ?>

  <?php if ( is_array( $team ) && $team ) : ?>
  <section class="sec sec--white">
    <div class="wrap">
      <div class="sechead" data-rise>
        <div class="sechead__text">
          <h2 class="h2"><?php echo esc_html( artdom_field( 'ab_team_title' ) ); ?></h2>
          <p class="body"><?php echo artdom_lines( artdom_field( 'ab_team_lead' ) ); ?></p>
        </div>
      </div>
      <div class="rule"></div>
      <ul class="team">
        <?php foreach ( $team as $person ) : ?>
        <li class="team__item" data-rise>
          <span class="team__ava" aria-hidden="true"><?php echo esc_html( artdom_initials( $person['name'] ) ); ?></span>
          <h3 class="team__name"><?php echo esc_html( $person['name'] ); ?></h3>
          <p class="team__role muted"><?php echo esc_html( $person['role'] ); ?></p>
          <?php if ( ! empty( $person['note'] ) ) : ?>
          <p class="body team__note"><?php echo esc_html( $person['note'] ); ?></p>
          <?php endif; ?>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </section>
  <?php endif; ?>

  <?php if ( trim( wp_strip_all_tags( get_the_content() ) ) ) : ?>
  <section class="sec sec--white">
    <div class="wrap prose" data-rise><?php the_content(); ?></div>
  </section>
  <?php endif; ?>

  <?php
  set_query_var( 'artdom_cta_title', 'Познакомимся?' );
  set_query_var( 'artdom_cta_text', 'Расскажите о задаче — подберём брокера, который занимается именно вашим сегментом.' );
  set_query_var( 'artdom_cta_btn', 'Оставить заявку' );
  get_template_part( 'template-parts/cta-band' );
  ?>
</main>

<?php
endwhile;
get_footer();
