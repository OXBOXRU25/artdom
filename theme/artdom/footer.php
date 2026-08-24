<?php
/**
 * Подвал сайта и закрытие документа.
 *
 * Контакты, соцсети и правовые ссылки — из «Настройки сайта». Меню в подвале
 * своё, чтобы его можно было отличать от главного.
 *
 * @package artdom
 */

$phone   = artdom_field( 'opt_phone', true );
$email   = artdom_field( 'opt_email', true );
$address = artdom_field( 'opt_address', true );
$socials = artdom_field( 'opt_socials', true );
$legal   = artdom_field( 'opt_legal', true );
?>

<!-- ============ Футер ============ -->
<footer class="ftr" id="contacts">
  <div class="wrap">

    <div class="ftr__cta">
      <div data-rise>
        <h2 class="h2"><?php echo esc_html( artdom_field( 'cta1_title' ) ); ?></h2>
        <p class="body"><?php echo artdom_lines( artdom_field( 'cta1_text' ) ); ?></p>
        <?php artdom_btn( artdom_field( 'cta1_btn_text' ), '#', 'btn btn--wide', array( 'data-form-open' => 'lead' ) ); ?>
      </div>
      <div data-rise>
        <h2 class="h2"><?php echo esc_html( artdom_field( 'cta2_title' ) ); ?></h2>
        <p class="body"><?php echo artdom_lines( artdom_field( 'cta2_text' ) ); ?></p>
        <?php artdom_btn( artdom_field( 'cta2_btn_text' ), '#', 'btn btn--wide', array( 'data-form-open' => 'subscribe' ) ); ?>
      </div>
    </div>

    <div class="ftr__rule"></div>

    <div class="ftr__mid">
      <div>
        <?php if ( $phone ) : ?>
        <a class="ftr__tel selectable" href="tel:<?php echo esc_attr( artdom_tel( $phone ) ); ?>"><?php echo esc_html( str_replace( ' ', "\u{00a0}", $phone ) ); ?></a>
        <?php endif; ?>
        <?php if ( $email ) : ?>
        <a class="ftr__mail selectable" href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
        <?php endif; ?>
        <?php if ( $address ) : ?>
        <p class="ftr__addr selectable"><?php echo esc_html( $address ); ?></p>
        <?php endif; ?>
        <?php if ( is_array( $socials ) && $socials ) : ?>
        <nav class="ftr__soc" aria-label="Мессенджеры и соцсети">
          <?php foreach ( $socials as $s ) : ?>
          <a href="<?php echo esc_url( $s['url'] ); ?>" rel="noopener"><?php echo esc_html( $s['label'] ); ?></a>
          <?php endforeach; ?>
        </nav>
        <?php endif; ?>
      </div>
      <div>
        <?php
        wp_nav_menu(
          array(
            'theme_location'       => 'menu_footer',
            'container'            => 'nav',
            'container_class'      => 'ftr__nav',
            'container_aria_label' => 'Навигация в подвале',
            'menu_class'           => '',
            'items_wrap'           => '%3$s',
            'depth'                => 1,
            'fallback_cb'          => false,
          )
        );
        ?>
        <?php if ( is_array( $legal ) && $legal ) : ?>
        <nav class="ftr__legal" aria-label="Правовые документы">
          <?php foreach ( $legal as $l ) : ?>
          <a href="<?php echo esc_url( $l['url'] ); ?>"><?php echo esc_html( $l['label'] ); ?></a>
          <?php endforeach; ?>
        </nav>
        <?php endif; ?>
      </div>
    </div>

    <div class="ftr__rule"></div>

    <div class="ftr__bottom">
      <p><?php echo esc_html( artdom_field( 'opt_copyright', true ) ); ?></p>
      <p>Разработано в <a href="https://oxbox.ru" rel="noopener">OXBOX</a></p>
    </div>

  </div>
</footer>

<div class="cursor" data-cursor-el data-on="false" aria-hidden="true"><svg viewBox="0 0 30 10"><use href="#i-arrow-lg"></use></svg></div>

<?php get_template_part( 'template-parts/form-modal' ); ?>

<?php wp_footer(); ?>
</body>
</html>
