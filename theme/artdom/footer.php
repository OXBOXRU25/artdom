<?php
/**
 * Подвал сайта и закрытие документа.
 *
 * @package artdom
 */

$u = get_template_directory_uri();
?>

<!-- ============ Футер ============ -->
<footer class="ftr" id="contacts">
  <div class="wrap">

    <div class="ftr__cta">
      <div data-rise>
        <h2 class="h2">Готовы найти свой дом?</h2>
        <p class="body">Оставьте контакт&nbsp;— персональный брокер свяжется с вами и подберёт объекты под ваш запрос.</p>
        <a class="btn btn--wide" href="#" draggable="false">
          <span class="roll"><span class="roll__a">Оставить заявку</span><span class="roll__b" aria-hidden="true">Оставить заявку</span></span>
          <span class="btn__arrow" aria-hidden="true"><svg viewBox="0 0 23 6"><use href="#i-arrow"></use></svg><svg viewBox="0 0 23 6"><use href="#i-arrow"></use></svg></span>
        </a>
      </div>
      <div data-rise>
        <h2 class="h2">Подборка объектов на почту</h2>
        <p class="body">Раз в неделю&nbsp;— новые лоты, изменения цен и закрытые предложения, которые не публикуются в открытом каталоге. Без спама, отписаться можно в любой момент.</p>
        <a class="btn btn--wide" href="#" draggable="false">
          <span class="roll"><span class="roll__a">Подписаться<br>на рассылку</span><span class="roll__b" aria-hidden="true">Подписаться<br>на рассылку</span></span>
          <span class="btn__arrow" aria-hidden="true"><svg viewBox="0 0 23 6"><use href="#i-arrow"></use></svg><svg viewBox="0 0 23 6"><use href="#i-arrow"></use></svg></span>
        </a>
      </div>
    </div>

    <div class="ftr__rule"></div>

    <div class="ftr__mid">
      <div>
        <a class="ftr__tel selectable" href="tel:+74951204830">+7&nbsp;495&nbsp;120&nbsp;48&nbsp;30</a>
        <a class="ftr__mail selectable" href="mailto:info@artdom.ru">info@artdom.ru</a>
        <p class="ftr__addr selectable">Москва, Пресненская наб., 8</p>
        <nav class="ftr__soc" aria-label="Мессенджеры и соцсети">
          <a href="#">Telegram</a>
          <a href="#">WhatsApp</a>
          <a href="#">VK</a>
        </nav>
      </div>
      <div>
        <nav class="ftr__nav" aria-label="Навигация в подвале">
          <a href="#about">О компании</a>
          <a href="#objects">Объекты</a>
          <a href="#services">Услуги</a>
          <a href="#contacts">Контакты</a>
        </nav>
        <nav class="ftr__legal" aria-label="Правовые документы">
          <a href="#">Политика конфиденциальности</a>
          <a href="#">Политика использования Cookie</a>
          <a href="#">Реквизиты</a>
        </nav>
      </div>
    </div>

    <div class="ftr__rule"></div>

    <div class="ftr__bottom">
      <p>АРТДОМ © 2026</p>
      <p>Разработано в <a href="https://oxbox.ru" rel="noopener">OXBOX</a></p>
    </div>

  </div>
</footer>

<div class="cursor" data-cursor-el data-on="false" aria-hidden="true"><svg viewBox="0 0 30 10"><use href="#i-arrow-lg"></use></svg></div>

<?php wp_footer(); ?>
</body>
</html>