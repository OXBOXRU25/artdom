<?php
/**
 * Список услуг. Построение снято с symbolstudio.pl/en/services.
 *
 * Не карточки, а длинный разбор: слева липкий столбец с оглавлением, справа
 * блоки услуг во всю ширину. У референса блок устроен так: мелкий номер,
 * крупное название, ниже слева «Scope of work» списком, справа вопрос
 * подводкой и текст. Замеры при 1440: контейнер 1100, колонки 136 / 470 /
 * 494, название 32/38.4, номер 12, подпись 11, список 14, вопрос 18,
 * текст 15, оглавление слева 195 и sticky.
 *
 * Вопрос справа — не выдумка: берём первый из «Вопросов и ответов» самой
 * услуги. Так подводка всегда про эту услугу и не расходится с её страницей.
 *
 * @package artdom
 */

get_header();

set_query_var( 'artdom_head_title', 'Услуги' );
set_query_var( 'artdom_head_lead', artdom_field( 'services_lead' ) );

/* Собираем список заранее: оглавление слева должно знать все услуги ещё до
   того, как начнётся вывод блоков. */
$artdom_items = array();
if ( have_posts() ) {
	while ( have_posts() ) {
		the_post();
		$artdom_items[] = array(
			'id'    => 'svc-' . get_the_ID(),
			'title' => get_the_title(),
			'link'  => get_permalink(),
			'lead'  => (string) get_field( 'svc_lead' ),
			'text'  => (string) get_field( 'svc_text' ),
			'price' => (string) get_field( 'svc_price' ),
			'steps' => get_field( 'svc_steps' ),
			'faq'   => get_field( 'svc_faq' ),
		);
	}
	rewind_posts();
}
?>

<main id="main">
  <?php get_template_part( 'template-parts/page-head' ); ?>

  <?php if ( $artdom_items ) : ?>
  <section class="sec sec--white svcpage">
    <div class="wrap svcpage__in">

      <?php /* Оглавление. На узком экране прячется: там страница и так
               листается одним пальцем, а столбец съел бы половину ширины. */ ?>
      <nav class="svcnav" aria-label="Услуги на этой странице">
        <p class="svcnav__cap">Навигация</p>
        <ul class="svcnav__list" role="list">
          <?php foreach ( $artdom_items as $artdom_i => $artdom_s ) : ?>
          <li><a href="#<?php echo esc_attr( $artdom_s['id'] ); ?>"><span class="svcnav__n"><?php echo esc_html( str_pad( $artdom_i + 1, 2, '0', STR_PAD_LEFT ) ); ?></span><?php echo esc_html( $artdom_s['title'] ); ?></a></li>
          <?php endforeach; ?>
        </ul>
      </nav>

      <div class="svclist">
        <?php foreach ( $artdom_items as $artdom_i => $artdom_s ) : ?>
        <?php
        $artdom_q     = ( is_array( $artdom_s['faq'] ) && $artdom_s['faq'] ) ? $artdom_s['faq'][0]['q'] : '';
        $artdom_paras = preg_split( '/\R{2,}/u', trim( $artdom_s['text'] ) );
        ?>
        <article class="svcrow" id="<?php echo esc_attr( $artdom_s['id'] ); ?>" data-rise>

          <p class="svcrow__n" aria-hidden="true"><?php echo esc_html( str_pad( $artdom_i + 1, 2, '0', STR_PAD_LEFT ) ); ?></p>
          <h2 class="svcrow__title"><a href="<?php echo esc_url( $artdom_s['link'] ); ?>"><?php echo esc_html( $artdom_s['title'] ); ?></a></h2>

          <?php if ( is_array( $artdom_s['steps'] ) && $artdom_s['steps'] ) : ?>
          <p class="svcrow__cap">Что входит</p>
          <ul class="svcrow__scope" role="list">
            <?php foreach ( $artdom_s['steps'] as $artdom_st ) : ?>
            <li><?php echo esc_html( $artdom_st['title'] ); ?></li>
            <?php endforeach; ?>
          </ul>
          <?php endif; ?>

          <div class="svcrow__side">
            <?php if ( $artdom_q ) : ?>
            <p class="svcrow__q"><?php echo esc_html( $artdom_q ); ?></p>
            <span class="svcrow__tick" aria-hidden="true"></span>
            <?php endif; ?>
            <?php foreach ( array_slice( $artdom_paras, 0, 2 ) as $artdom_p ) : ?>
            <p class="body svcrow__text"><?php echo artdom_lines( $artdom_p ); ?></p>
            <?php endforeach; ?>
            <p class="svcrow__foot">
              <?php if ( $artdom_s['price'] ) : ?>
              <span class="svcrow__price"><?php echo esc_html( $artdom_s['price'] ); ?></span>
              <?php endif; ?>
              <a class="svcrow__more" href="<?php echo esc_url( $artdom_s['link'] ); ?>">Подробнее об услуге<span class="svcrow__arrow" aria-hidden="true"><svg viewBox="0 0 24 16"><use href="#i-arrow-xl"></use></svg></span></a>
            </p>
          </div>

          <?php /* Заглушка вместо снимка. Пропорция задана стилями, а не
                   файлом: с появлением настоящей фотографии блок не
                   подпрыгнет. */ ?>
          <div class="svcrow__media" aria-hidden="true">
            <span class="svcrow__stub"><svg viewBox="0 0 38 40" fill="currentColor"><use href="#i-mark"></use></svg></span>
          </div>

        </article>
        <?php endforeach; ?>
      </div>

    </div>
  </section>
  <?php else : ?>
  <section class="sec sec--white"><div class="wrap"><p class="body">Раздел наполняется.</p></div></section>
  <?php endif; ?>
</main>

<?php
get_footer();
