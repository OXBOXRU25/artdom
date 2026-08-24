<?php
/**
 * Секция: О компании
 *
 * @package artdom
 */

$paragraphs = preg_split( '/\R{2,}/u', trim( (string) artdom_field( 'about_text' ) ) );
?>
  <!-- ============ О компании ============ -->
  <section class="sec sec--surface about" id="about">
    <div class="wrap">
      <h2 class="h2--big" data-rise><?php echo artdom_lines( artdom_field( 'about_title' ) ); ?></h2>

      <div class="about__in">
        <div class="about__side" data-rise>
          <div class="about__portrait">
            <?php
            artdom_img(
              artdom_field( 'about_portrait' ),
              'founder.webp',
              artdom_field( 'about_name' ) . ', ' . artdom_field( 'about_role' ) . ' компании АРТДОМ',
              array( 181, 181 )
            );
            ?>
          </div>
          <blockquote class="about__quote"><?php echo artdom_lines( artdom_field( 'about_quote' ) ); ?></blockquote>
          <p class="about__who"><?php echo esc_html( artdom_field( 'about_name' ) ); ?><span class="about__role"><?php echo esc_html( artdom_field( 'about_role' ) ); ?></span></p>
        </div>

        <div class="about__main" data-rise>
          <?php foreach ( $paragraphs as $p ) : ?>
          <p class="body"><?php echo artdom_lines( $p ); ?></p>
          <?php endforeach; ?>
          <?php artdom_btn( artdom_field( 'about_btn_text' ), artdom_field( 'about_btn_link' ), 'btn btn--ghost' ); ?>
        </div>
      </div>
    </div>
  </section>
