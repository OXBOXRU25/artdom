<?php
/**
 * Template Name: Контакты
 * Template Post Type: page
 *
 * Форма стоит прямо на странице, а не за кнопкой: человек, дошедший до
 * контактов, уже решил написать — лишний клик тут только мешает.
 *
 * @package artdom
 */

get_header();

$phone   = artdom_field( 'opt_phone', true );
$email   = artdom_field( 'opt_email', true );
$address = artdom_field( 'opt_address', true );
$socials = artdom_field( 'opt_socials', true );

while ( have_posts() ) :
	the_post();
	set_query_var( 'artdom_head_title', get_the_title() );
	set_query_var( 'artdom_head_lead', has_excerpt() ? get_the_excerpt() : 'Ответим в течение часа в рабочее время. Показы проводим и в выходные.' );
?>

<main>
  <?php get_template_part( 'template-parts/page-head' ); ?>

  <section class="sec sec--white contacts">
    <div class="wrap contacts__in">

      <div class="contacts__info" data-rise>
        <?php if ( $phone ) : ?>
        <p class="contacts__row">
          <span class="contacts__label muted">Телефон</span>
          <a class="contacts__big selectable" href="tel:<?php echo esc_attr( artdom_tel( $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a>
        </p>
        <?php endif; ?>
        <?php if ( $email ) : ?>
        <p class="contacts__row">
          <span class="contacts__label muted">Почта</span>
          <a class="contacts__big selectable" href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
        </p>
        <?php endif; ?>
        <?php if ( $address ) : ?>
        <p class="contacts__row">
          <span class="contacts__label muted">Адрес</span>
          <span class="contacts__addr selectable"><?php echo esc_html( $address ); ?></span>
        </p>
        <?php endif; ?>
        <?php if ( is_array( $socials ) && $socials ) : ?>
        <p class="contacts__row">
          <span class="contacts__label muted">Мессенджеры</span>
          <span class="contacts__soc">
            <?php foreach ( $socials as $s ) : ?>
            <a href="<?php echo esc_url( $s['url'] ); ?>" rel="noopener"><?php echo esc_html( $s['label'] ); ?></a>
            <?php endforeach; ?>
          </span>
        </p>
        <?php endif; ?>

        <?php if ( get_the_content() ) : ?>
        <div class="prose contacts__text"><?php the_content(); ?></div>
        <?php endif; ?>
      </div>

      <div class="contacts__form" data-rise>
        <h2 class="h2">Написать нам</h2>
        <?php
        set_query_var( 'artdom_form_kind', 'lead' );
        set_query_var( 'artdom_form_id', 'contacts-lead' );
        set_query_var( 'artdom_form_closer', false );
        set_query_var( 'artdom_form_heading', '' );
        get_template_part( 'template-parts/form' );
        ?>
      </div>

    </div>
  </section>
</main>

<?php
endwhile;
get_footer();
