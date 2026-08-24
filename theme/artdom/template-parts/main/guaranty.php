<?php
/**
 * Секция: Надёжность: закреплённая сцена на три экрана
 *
 * @package artdom
 */

$u = get_template_directory_uri();
?>
  <!-- ============ Надёжность: закреплённая сцена на три экрана ============
       Каркас держит высоту (три экрана), сцена внутри липкая и не двигается,
       пока каркас проезжает мимо. Слои меняются по доле прокрутки внутри каркаса.
       ФОТО 2-3 И ЗАГОЛОВКИ 2-3 ВРЕМЕННЫЕ — в макете их нет, заменить. -->
  <section class="guaranty" data-guaranty aria-label="Надежность и гарантии">
    <div class="guaranty__stage">
      <div class="guaranty__bg">
        <img draggable="false" src="<?php echo esc_url( $u ); ?>/img/garanty.webp" alt="" width="1920" height="963" decoding="async" data-slide="0" data-on="true">
        <img draggable="false" src="<?php echo esc_url( $u ); ?>/img/uslugi.webp" alt="" width="1920" height="963" loading="lazy" decoding="async" data-slide="1">
        <img draggable="false" src="<?php echo esc_url( $u ); ?>/img/object.webp" alt="" width="1920" height="963" loading="lazy" decoding="async" data-slide="2">
      </div>
      <div class="guaranty__text wrap">
        <h2 class="h2--big guaranty__title" data-slide="0" data-on="true">Надежность и гарантии</h2>
        <p class="h2--big guaranty__title" data-slide="1">Проверяем объект<br>до внесения задатка</p>
        <p class="h2--big guaranty__title" data-slide="2">Сопровождаем<br>до регистрации права</p>
      </div>
      <div class="guaranty__steps" aria-hidden="true">
        <span data-slide="0" data-on="true"></span>
        <span data-slide="1"></span>
        <span data-slide="2"></span>
      </div>
    </div>
  </section>
