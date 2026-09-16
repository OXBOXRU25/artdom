<?php
/**
 * Страница услуги: текст, порядок работы, вопросы и ответы.
 *
 * @package artdom
 */

get_header();

while ( have_posts() ) :
	the_post();

	$lead  = get_field( 'svc_lead' );
	$text  = get_field( 'svc_text' );
	$steps = get_field( 'svc_steps' );
	$faq   = get_field( 'svc_faq' );

	set_query_var( 'artdom_head_title', get_the_title() );
	set_query_var( 'artdom_head_lead', $lead );

	/* Стоимость. Поле было заведено с подсказкой «Пусто — строка не
	   покажется», но строки в шаблоне не было вовсе: заказчик мог вписать
	   цену и не увидеть её нигде. Выводим в шапку страницы, под подводкой —
	   там же, где у объекта стоит цена. Пусто — строки нет, как и обещано. */
	$price = get_field( 'svc_price' );
	if ( $price ) {
		set_query_var(
			'artdom_head_extra',
			'<p class="pagehead__price"><span class="muted">' . esc_html( artdom_field( 'obj_price_label', true ) ) . '</span> ' . esc_html( $price ) . '</p>'
		);
	}
?>

<main id="main" class="sheet">
  <?php get_template_part( 'template-parts/page-head' ); ?>

  <?php if ( $text ) : ?>
  <section class="sec sec--white">
    <div class="wrap prose" data-rise>
      <?php foreach ( preg_split( '/\R{2,}/u', trim( $text ) ) as $p ) : ?>
      <p class="body"><?php echo artdom_lines( $p ); ?></p>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <?php if ( is_array( $steps ) && $steps ) : ?>
  <section class="sec sec--surface">
    <div class="wrap">
      <h2 class="h2" data-rise><?php echo esc_html( artdom_field( "svc_steps_title", true ) ); ?></h2>
      <div class="rule"></div>
      <ol class="steps">
        <?php foreach ( $steps as $i => $s ) : ?>
        <li class="steps__item" data-rise>
          <p class="steps__n" aria-hidden="true"><?php echo esc_html( str_pad( $i + 1, 2, '0', STR_PAD_LEFT ) ); ?></p>
          <h3 class="steps__title"><?php echo esc_html( $s['title'] ); ?></h3>
          <p class="body"><?php echo artdom_lines( $s['text'] ); ?></p>
        </li>
        <?php endforeach; ?>
      </ol>
    </div>
  </section>
  <?php endif; ?>

  <?php if ( is_array( $faq ) && $faq ) : ?>
  <section class="sec sec--white">
    <div class="wrap faq">
      <h2 class="h2" data-rise><?php echo esc_html( artdom_field( "svc_faq_title", true ) ); ?></h2>
      <div class="acc" data-acc data-rise>
        <?php foreach ( $faq as $i => $q ) : ?>
        <div class="acc__item" data-open="<?php echo 0 === $i ? 'true' : 'false'; ?>">
          <h3>
            <button class="acc__btn" type="button" aria-expanded="<?php echo 0 === $i ? 'true' : 'false'; ?>" aria-controls="faq-<?php echo (int) $i; ?>">
              <span><?php echo esc_html( $q['q'] ); ?></span>
              <span class="acc__icon" aria-hidden="true"><svg viewBox="0 0 12 12"><use href="#i-plus"></use></svg></span>
            </button>
          </h3>
          <div class="acc__panel" id="faq-<?php echo (int) $i; ?>">
            <div class="acc__panelIn">
              <div class="acc__body">
                <p class="body"><?php echo artdom_lines( $q['a'] ); ?></p>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <?php
  set_query_var( 'artdom_cta_title', artdom_field( 'cta_svc_title', true ) );
  set_query_var( 'artdom_cta_text', artdom_field( 'cta_svc_text', true ) );
  set_query_var( 'artdom_cta_btn', artdom_field( 'cta_svc_btn', true ) );
  ?>
</main>

<?php
endwhile;
get_footer();
