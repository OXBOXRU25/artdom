<?php
/**
 * Список услуг. Построение и размерный ряд сняты с symbolstudio.pl/en/services.
 *
 * ЗАМЕРЫ РЕФЕРЕНСА ПРИ 1440 (все числа проверены в браузере, не на глаз):
 *   раскладка   навигация l=48 w=195; вертикальный волосок x=215;
 *               контент l=239 w=1100; колонки 239 / 375(w414) / 845(w494)
 *   номер       12, вес 400, чёрный, l=239 w=15
 *   название    32/38.4, вес 400, UPPERCASE, чёрный, l=358
 *   подпись     11/16, uppercase, #747881, w=104
 *   список      14/21, вес 400, #1B1D22, маркер «–»
 *   вопрос      18/25.2, вес 400, #1B1D22, w=494
 *   штрих       37x1, #FE552E (у нас — наш синий), воздух 56 сверху и снизу
 *   текст       15/21, вес 400, чёрный
 *   ритм        название→строка 57; вопрос→штрих 56; штрих→текст 56;
 *               текст→снимки 112; снимки→следующее название 225
 *   снимки      три: слева 512x450, справа две 281x250, зазор 8, между 37
 *
 * Цены и «Подробнее» убраны по решению заказчика: у референса в блоке нет
 * ни того, ни другого, а название и так ведёт на страницу услуги.
 *
 * @package artdom
 */

get_header();

set_query_var( 'artdom_head_title', 'Услуги' );
set_query_var( 'artdom_head_lead', artdom_field( 'services_lead' ) );

$artdom_items = array();
if ( have_posts() ) {
	while ( have_posts() ) {
		the_post();
		$artdom_items[] = array(
			'id'    => 'svc-' . get_the_ID(),
			'title' => get_the_title(),
			'link'  => get_permalink(),
			'text'  => (string) get_field( 'svc_text' ),
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

      <nav class="svcnav" aria-label="Услуги на этой странице">
        <p class="svcnav__cap">Навигация</p>
        <ul class="svcnav__list" role="list">
          <?php foreach ( $artdom_items as $artdom_s ) : ?>
          <li><a href="#<?php echo esc_attr( $artdom_s['id'] ); ?>" data-svcnav="<?php echo esc_attr( $artdom_s['id'] ); ?>"><?php echo esc_html( $artdom_s['title'] ); ?></a></li>
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
          <p class="svcrow__cap">Что входит:</p>
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
            <p class="svcrow__text"><?php echo artdom_lines( $artdom_p ); ?></p>
            <?php endforeach; ?>
          </div>

          <?php /* Три заглушки под снимки, как у референса: одна крупная и
                   две поменьше. Пропорции заданы стилями (1.14 и 1.12 по
                   замеру), поэтому с появлением настоящих фотографий ряд не
                   поедет. */ ?>
          <div class="svcrow__media" aria-hidden="true">
            <div class="svcrow__shot svcrow__shot--big"><span class="svcrow__stub"><svg viewBox="0 0 38 40" fill="currentColor"><use href="#i-mark"></use></svg></span></div>
            <div class="svcrow__shot"><span class="svcrow__stub"><svg viewBox="0 0 38 40" fill="currentColor"><use href="#i-mark"></use></svg></span></div>
            <div class="svcrow__shot"><span class="svcrow__stub"><svg viewBox="0 0 38 40" fill="currentColor"><use href="#i-mark"></use></svg></span></div>
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
