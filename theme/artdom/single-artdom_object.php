<?php
/**
 * Карточка объекта.
 *
 * Слева галерея и текст, справа липкая панель с ценой и кнопкой — это
 * привычная раскладка каталогов недвижимости, и ломать её незачем: человек
 * пришёл за ценой и за возможностью написать, оба должны быть видны всегда.
 *
 * @package artdom
 */

get_header();

while ( have_posts() ) :
	the_post();

	$u        = get_template_directory_uri();
	$price    = get_field( 'obj_price' );
	$area     = get_field( 'obj_area' );
	$rooms    = get_field( 'obj_rooms' );
	$floor    = get_field( 'obj_floor' );
	$district = get_field( 'obj_district' );
	$metro    = get_field( 'obj_metro' );
	$complex  = get_field( 'obj_complex' );
	$address  = get_field( 'obj_address' );
	$year     = get_field( 'obj_year' );
	$text     = get_field( 'obj_text' );
	$facts    = get_field( 'obj_facts' );
	$gallery  = get_field( 'obj_gallery' );
	$terms    = get_the_terms( get_the_ID(), 'artdom_object_type' );

	$cover = get_the_post_thumbnail_url( get_the_ID(), 'full' );
	if ( ! $cover && is_array( $gallery ) && ! empty( $gallery[0]['url'] ) ) {
		$cover = $gallery[0]['url'];
	}
	if ( ! $cover ) {
		$cover = $u . '/img/object.webp';
	}

	/* Строки таблицы: только то, что заполнено. Пустая ячейка в таблице
	   характеристик выглядит как недоделка, а не как отсутствие данных. */
	$rows = array();
	if ( $area )     { $rows['Площадь']        = $area . ' м²'; }
	if ( $rooms )    { $rows['Спален']         = $rooms; }
	if ( $floor )    { $rows['Этаж']           = $floor; }
	if ( $complex )  { $rows['Жилой комплекс'] = $complex; }
	if ( $district ) { $rows['Район']          = $district; }
	if ( $metro )    { $rows['Метро']          = $metro; }
	if ( $year )     { $rows['Год']            = $year; }
	if ( is_array( $facts ) ) {
		foreach ( $facts as $f ) {
			if ( ! empty( $f['name'] ) && ! empty( $f['value'] ) ) {
				$rows[ $f['name'] ] = $f['value'];
			}
		}
	}

	set_query_var( 'artdom_head_title', get_the_title() );
	set_query_var( 'artdom_head_lead', $address ? $address : trim( $district . ( $district && $metro ? ', метро ' : '' ) . $metro ) );
?>

<main>
  <?php get_template_part( 'template-parts/page-head' ); ?>

  <section class="sec sec--white object">
    <div class="wrap object__in">

      <div class="object__main">
        <figure class="object__cover" data-rise="shutter">
          <img draggable="false" src="<?php echo esc_url( $cover ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" width="1160" height="700" decoding="async">
        </figure>

        <?php if ( is_array( $gallery ) && count( $gallery ) > 1 ) : ?>
        <div class="object__gallery" role="group" aria-label="Фотографии объекта">
          <?php foreach ( array_slice( $gallery, 1 ) as $g ) : ?>
          <img draggable="false" src="<?php echo esc_url( $g['sizes']['large'] ); ?>" alt="<?php echo esc_attr( $g['alt'] ); ?>" width="580" height="395" loading="lazy" decoding="async">
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if ( $text ) : ?>
        <div class="object__text" data-rise>
          <h2 class="h2">Об объекте</h2>
          <?php foreach ( preg_split( '/\R{2,}/u', trim( $text ) ) as $p ) : ?>
          <p class="body"><?php echo artdom_lines( $p ); ?></p>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if ( $rows ) : ?>
        <div class="object__specs" data-rise>
          <h2 class="h2">Характеристики</h2>
          <dl class="specs selectable">
            <?php foreach ( $rows as $k => $v ) : ?>
            <div class="specs__row"><dt><?php echo esc_html( $k ); ?></dt><dd><?php echo esc_html( $v ); ?></dd></div>
            <?php endforeach; ?>
          </dl>
        </div>
        <?php endif; ?>
      </div>

      <aside class="object__side">
        <div class="pricebox">
          <?php if ( $terms && ! is_wp_error( $terms ) ) : ?>
          <div class="card__meta"><span class="chip"><?php echo esc_html( $terms[0]->name ); ?></span></div>
          <?php endif; ?>
          <?php if ( $price ) : ?>
          <p class="pricebox__price selectable"><?php echo esc_html( $price ); ?></p>
          <?php endif; ?>
          <?php if ( $area && $rooms ) : ?>
          <p class="pricebox__sub muted"><?php echo esc_html( $area ); ?> м² · <?php echo esc_html( $rooms ); ?> <?php echo esc_html( artdom_plural( $rooms, array( 'спальня', 'спальни', 'спален' ) ) ); ?></p>
          <?php endif; ?>
          <?php artdom_btn( 'Записаться на просмотр', '#', 'btn btn--wide', array( 'data-form-open' => 'lead' ) ); ?>
          <p class="pricebox__note muted">Ответим в течение часа. Показ&nbsp;— в удобное вам время, включая выходные.</p>
        </div>
      </aside>

    </div>
  </section>

  <?php
  /* Похожие: та же категория, свежие, кроме текущего. */
  $tax_ids = ( $terms && ! is_wp_error( $terms ) ) ? wp_list_pluck( $terms, 'term_id' ) : array();
  $similar = new WP_Query(
    array(
      'post_type'      => 'artdom_object',
      'posts_per_page' => 3,
      'post__not_in'   => array( get_the_ID() ),
      'no_found_rows'  => true,
      'tax_query'      => $tax_ids ? array( array( 'taxonomy' => 'artdom_object_type', 'field' => 'term_id', 'terms' => $tax_ids ) ) : array(),
    )
  );
  if ( $similar->have_posts() ) :
  ?>
  <section class="sec sec--white">
    <div class="wrap">
      <h2 class="h2" data-rise>Похожие объекты</h2>
      <div class="rule"></div>
      <div class="grid-cards">
        <?php
        while ( $similar->have_posts() ) :
          $similar->the_post();
          get_template_part( 'template-parts/object-card' );
        endwhile;
        wp_reset_postdata();
        ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <?php
  set_query_var( 'artdom_cta_title', 'Хотите посмотреть вживую?' );
  set_query_var( 'artdom_cta_text', 'Организуем показ в удобное время и подготовим документы по объекту заранее.' );
  set_query_var( 'artdom_cta_btn', 'Записаться на просмотр' );
  get_template_part( 'template-parts/cta-band' );
  ?>
</main>

<?php
endwhile;
get_footer();
