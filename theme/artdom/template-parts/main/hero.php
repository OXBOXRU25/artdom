<?php
/**
 * Секция: Hero
 *
 * Тексты, видео и постер берутся из админки; пустое поле показывает то,
 * что нарисовано в макете, а не дыру.
 *
 * @package artdom
 */

$u      = get_template_directory_uri();
$webm   = artdom_field( 'hero_video_webm' );
$mp4    = artdom_field( 'hero_video_mp4' );
$poster = artdom_field( 'hero_poster' );
$poster = is_array( $poster ) && ! empty( $poster['url'] ) ? $poster['url'] : $u . '/img/hero-poster.webp';
?>
  <!-- ============ Hero ============ -->
  <section class="hero">
    <div class="hero__bg">
      <!-- Постер вырезан из этого же ролика, поэтому подмены кадра не видно.
           На телефоне ни один source не подходит по media, качать нечего — остаётся постер.
           display:none видео не спасает: preload тянет файл независимо от отрисовки. -->
      <video class="hero__video" autoplay muted loop playsinline
             poster="<?php echo esc_url( $poster ); ?>" preload="metadata"
             width="1920" height="1080" aria-hidden="true" tabindex="-1">
        <source src="<?php echo esc_url( $webm ? $webm : $u . '/video/hero.webm' ); ?>" type="video/webm" media="(min-width: 768px)">
        <source src="<?php echo esc_url( $mp4 ? $mp4 : $u . '/video/hero.mp4' ); ?>"  type="video/mp4"  media="(min-width: 768px)">
      </video>
    </div>
    <div class="wrap hero__in">
      <h1 class="h1 hero__title"><?php echo artdom_lines( artdom_field( 'hero_title' ) ); ?></h1>
      <div class="hero__aside">
        <p class="lead"><?php echo artdom_lines( artdom_field( 'hero_lead' ) ); ?></p>
        <?php artdom_btn( artdom_field( 'hero_btn_text' ), artdom_field( 'hero_btn_link' ), 'btn btn--wide' ); ?>
      </div>
    </div>
  </section>
