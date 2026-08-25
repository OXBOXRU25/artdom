<?php
/**
 * Template Name: Контакты
 * Template Post Type: page
 *
 * Композиция снята с symbolstudio.pl/en/contact: серый фон, белый лист с
 * отступом от краёв, по центру плашка с точкой, огромный заголовок капсом
 * и подводка; ниже две колонки, разделённые волосяной линией — слева живой
 * человек и контакты, справа форма.
 *
 * Взято оттуда именно построение, а не оформление: гарнитура, палитра,
 * скругления и кнопка остаются нашими.
 *
 * @package artdom
 */

/* Серый фон и прозрачная шапка включаются классом на <body>: страница
   единственная в своём роде, заводить ради неё отдельный шаблон шапки
   было бы дороже. */
add_filter(
	'body_class',
	static function ( $classes ) {
		$classes[] = 'is-sheet';
		return $classes;
	}
);

get_header();

$phone   = artdom_field( 'opt_phone', true );
$email   = artdom_field( 'opt_email', true );
$address = artdom_field( 'opt_address', true );
$socials = artdom_field( 'opt_socials', true );

while ( have_posts() ) :
	the_post();
?>

<main id="main" class="sheet">

  <section class="chero">
    <div class="wrap chero__in">
      <p class="chip chip--dot"><span class="chip__dot" aria-hidden="true"></span><?php echo esc_html( artdom_field( 'contacts_chip' ) ); ?></p>
      <h1 class="chero__title"><?php echo artdom_lines( artdom_field( 'contacts_title' ) ); ?></h1>
      <?php foreach ( preg_split( '/\R{2,}/u', trim( (string) artdom_field( 'contacts_lead' ) ) ) as $artdom_p ) : ?>
      <p class="chero__lead"><?php echo artdom_lines( $artdom_p ); ?></p>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="ccols">
    <div class="wrap ccols__in">

      <div class="ccols__left" data-rise>
        <div class="broker">
          <div class="broker__portrait">
            <?php
            artdom_img(
              artdom_field( 'about_portrait' ),
              'founder.webp',
              artdom_field( 'about_name' ) . ', ' . artdom_field( 'about_role' ),
              array( 160, 160 ),
              false
            );
            ?>
          </div>
          <div class="broker__who">
            <p class="broker__name"><?php echo esc_html( artdom_field( 'about_name' ) ); ?></p>
            <p class="broker__role"><?php echo esc_html( artdom_field( 'contacts_broker_note' ) ); ?></p>
          </div>
        </div>

        <dl class="crows selectable">
          <?php if ( $phone ) : ?>
          <div class="crows__row">
            <dt>Телефон</dt>
            <dd><a href="tel:<?php echo esc_attr( artdom_tel( $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a></dd>
          </div>
          <?php endif; ?>
          <?php if ( $email ) : ?>
          <div class="crows__row">
            <dt>Почта</dt>
            <dd><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></dd>
          </div>
          <?php endif; ?>
          <?php if ( $address ) : ?>
          <div class="crows__row">
            <dt>Адрес</dt>
            <dd><?php echo esc_html( $address ); ?></dd>
          </div>
          <?php endif; ?>
          <?php if ( is_array( $socials ) && $socials ) : ?>
          <div class="crows__row">
            <dt>Мессенджеры</dt>
            <dd class="crows__soc">
              <?php foreach ( $socials as $artdom_s ) : ?>
              <a href="<?php echo esc_url( $artdom_s['url'] ); ?>" rel="noopener"><?php echo esc_html( $artdom_s['label'] ); ?></a>
              <?php endforeach; ?>
            </dd>
          </div>
          <?php endif; ?>
        </dl>

        <?php if ( trim( wp_strip_all_tags( get_the_content() ) ) ) : ?>
        <div class="prose ccols__text"><?php the_content(); ?></div>
        <?php endif; ?>
      </div>

      <div class="ccols__right" data-rise>
        <h2 class="ccols__title"><?php echo esc_html( artdom_field( 'contacts_form_title' ) ); ?></h2>
        <?php
        set_query_var( 'artdom_form_kind', 'lead' );
        set_query_var( 'artdom_form_id', 'contacts-lead' );
        set_query_var( 'artdom_form_closer', false );
        set_query_var( 'artdom_form_heading', '' );
        get_template_part( 'template-parts/form' );
        ?>
      </div>

    </div>
  </section>


  <?php
  $artdom_map     = artdom_field( 'ct_map' );
  $artdom_map_url = artdom_field( 'ct_map_url' );
  /* Ссылка должна быть всегда: строка «Смотреть на Яндекс.Картах» — часть
     блока, а не украшение. Пока точный адрес точки не задан в админке,
     ведём на поиск по нашему же адресу — это работает и без ключа к API. */
  if ( ! $artdom_map_url ) {
    /* Экранирующих последовательностей здесь нет намеренно: обратный слеш
       не переживает передачу через оболочку и молча выпадает, превращая
       регулярку в бессмыслицу. chr(10) и chr(13) делают то же самое надёжно. */
    $artdom_addr_raw   = str_replace( chr( 13 ), '', (string) artdom_field( 'ct_address' ) );
    $artdom_addr_parts = array_filter( array_map( 'trim', explode( chr( 10 ), $artdom_addr_raw ) ) );
    $artdom_map_url    = 'https://yandex.ru/maps/?text=' . rawurlencode( implode( ', ', $artdom_addr_parts ) );
  }
  ?>

  <section class="caddr">
    <div class="wrap caddr__in">

      <figure class="caddr__photo" data-rise="shutter">
        <?php artdom_img( artdom_field( 'ct_photo' ), 'uslugi.webp', 'Офис АРТДОМ', array( 680, 358 ) ); ?>
      </figure>

      <div class="caddr__text selectable" data-rise>
        <p class="caddr__org"><?php echo esc_html( artdom_field( 'ct_org' ) ); ?></p>
        <p class="caddr__lines"><?php echo artdom_lines( artdom_field( 'ct_address' ) ); ?></p>
        <?php if ( $artdom_map_url ) : ?>
        <p class="caddr__link">Смотреть на <a href="<?php echo esc_url( $artdom_map_url ); ?>" target="_blank" rel="noopener">Яндекс.Картах</a></p>
        <?php endif; ?>
      </div>

      <div class="caddr__side" data-rise>
        <p class="caddr__clock">
          <span><?php echo esc_html( artdom_field( 'ct_tz' ) ); ?></span>
          <time data-clock aria-live="off">&mdash;&mdash;:&mdash;&mdash;</time>
        </p>
        <p class="caddr__claim"><?php echo artdom_lines( artdom_field( 'ct_claim' ) ); ?></p>
      </div>

    </div>
  </section>

  <?php if ( is_array( $artdom_map ) && ! empty( $artdom_map['url'] ) ) : ?>
  <section class="cmap">
    <div class="wrap cmap__in" data-rise>
      <img class="cmap__img" src="<?php echo esc_url( $artdom_map['url'] ); ?>" alt="<?php echo esc_attr( $artdom_map['alt'] ? $artdom_map['alt'] : 'Карта городов, где мы работаем' ); ?>" loading="lazy" decoding="async">
      <?php if ( artdom_field( 'ct_map_caption' ) ) : ?>
      <p class="cmap__caption"><?php echo esc_html( artdom_field( 'ct_map_caption' ) ); ?></p>
      <?php endif; ?>
    </div>
  </section>
  <?php endif; ?>

  <?php
  /* Переход в соседние разделы. У референса эта полоса чёрная, но у нас
     чёрный подвал стоит сразу под ней — два чёрных блока подряд сливаются
     в одно пятно. Поэтому те же огромные слова, но по белому. */
  $artdom_next = array(
    array( 'Объекты', get_post_type_archive_link( 'artdom_object' ) ),
    array( 'Услуги', get_post_type_archive_link( 'artdom_service' ) ),
  );
  ?>
  <section class="cnext">
    <div class="wrap">
      <p class="cnext__label"><?php echo esc_html( artdom_field( 'ct_next_title' ) ); ?></p>
      <div class="cnext__grid">
        <?php foreach ( $artdom_next as $artdom_n ) : ?>
        <?php if ( ! $artdom_n[1] ) { continue; } ?>
        <a class="cnext__item" href="<?php echo esc_url( $artdom_n[1] ); ?>">
          <span class="cnext__word"><?php echo esc_html( mb_strtoupper( $artdom_n[0] ) ); ?></span>
          <span class="cnext__go" aria-hidden="true"><svg viewBox="0 0 64 16"><use href="#i-arrow-xl"></use></svg><svg viewBox="0 0 64 16"><use href="#i-arrow-xl"></use></svg></span>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

</main>

<?php
endwhile;
get_footer();
