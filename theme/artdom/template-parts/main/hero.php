<?php
/**
 * Секция: Hero
 *
 * @package artdom
 */

$u = get_template_directory_uri();
?>
  <!-- ============ Hero ============ -->
  <section class="hero">
    <div class="hero__bg">
      <!-- Постер вырезан из этого же ролика, поэтому подмены кадра не видно.
           На телефоне ни один source не подходит по media, качать нечего — остаётся постер.
           display:none видео не спасает: preload тянет файл независимо от отрисовки. -->
      <video class="hero__video" autoplay muted loop playsinline
             poster="img/hero-poster.webp" preload="metadata"
             width="1920" height="1080" aria-hidden="true" tabindex="-1">
        <source src="<?php echo esc_url( $u ); ?>/video/hero.webm" type="video/webm" media="(min-width: 768px)">
        <source src="<?php echo esc_url( $u ); ?>/video/hero.mp4"  type="video/mp4"  media="(min-width: 768px)">
      </video>
    </div>
    <div class="wrap hero__in">
      <h1 class="h1 hero__title">Дом&nbsp;— это<br>произведение<br>искусства, а&nbsp;не<br>квадратные метры.</h1>
      <div class="hero__aside">
        <p class="lead">Подбираем и сопровождаем сделки с премиальной недвижимостью: клубные дома, резиденции и апартаменты в лучших локациях города.</p>
        <a class="btn btn--wide" href="#objects" draggable="false">
          <span class="roll"><span class="roll__a">Смотреть<br>готовые объекты</span><span class="roll__b" aria-hidden="true">Смотреть<br>готовые объекты</span></span>
          <span class="btn__arrow" aria-hidden="true"><svg viewBox="0 0 23 6"><use href="#i-arrow"></use></svg><svg viewBox="0 0 23 6"><use href="#i-arrow"></use></svg></span>
        </a>
      </div>
    </div>
  </section>
