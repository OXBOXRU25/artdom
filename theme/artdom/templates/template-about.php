<?php
/**
 * Template Name: О компании
 * Template Post Type: page
 *
 * Построение и размерный ряд сняты с symbolstudio.pl/en/about.
 *
 * ЗАМЕРЫ РЕФЕРЕНСА ПРИ 1440 (проверены в браузере):
 *   поле слева   48, контент до 1377
 *   утверждение  H1 40/44, вес 400, UPPERCASE, чёрный, ширина 1329
 *   подзаголовок H3 28/36.4, обычный регистр, во второй колонке (l=275)
 *   крупная фраза H1 64/70.4, UPPERCASE, белым поверх коллажа
 *   серое утверждение H2 32/38.4, UPPERCASE, #747881, ширина 665
 *   раздел        H2 32/38.4, UPPERCASE, чёрный
 *   «почему мы»   колонки: номер 14/21 полупрозрачный серый, заголовок 22/30.8
 *   команда       имя H3 28/36.4, роль 14/21 серая справа, шаг 116
 *   шаги          номер 64/83.2 акцентом, текст 15/21 во второй колонке (l=745)
 *
 * Блок с видео у референса не берём — заказчик отказался. Вместо снимков
 * заглушки: пропорции заданы стилями, чтобы с появлением настоящих
 * фотографий страница не поехала.
 *
 * Пустой блок не рисуется вовсе: недозаполненная страница должна выглядеть
 * короче, а не дырявой.
 *
 * @package artdom
 */
get_header();

while ( have_posts() ) :
	the_post();

	$intro_title = artdom_field( 'ab_intro_title' );
	$intro_text  = artdom_field( 'ab_intro_text' );
	$principles  = artdom_field( 'ab_principles' );
	$path        = artdom_field( 'ab_path' );
	$team        = artdom_field( 'ab_team' );

	$quote = artdom_field( 'about_quote' );
	$paras = $intro_text ? preg_split( '/\R{2,}/u', trim( (string) $intro_text ) ) : array();

	set_query_var( 'artdom_head_title', get_the_title() );
	set_query_var( 'artdom_head_lead', '' );
?>
<main id="main" class="sheet">

  <?php /* Первый экран построен как на контактах: серая полоса под шапкой,
           затем белый лист, и всё содержимое по центру. Заказчик просил один
           приём на обеих страницах, а не два разных. */ ?>
  <section class="chero chero--about">
    <div class="wrap chero__in">
      <h1 class="chero__title"><?php echo artdom_lines( $intro_title ? $intro_title : get_the_title() ); ?></h1>
      <?php foreach ( array_slice( $paras, 0, 1 ) as $p ) : ?>
      <p class="chero__lead"><?php echo artdom_lines( $p ); ?></p>
      <?php endforeach; ?>
    </div>
  </section>

  <?php if ( count( $paras ) > 1 ) : ?>
  <section class="sec sec--white abtext">
    <div class="wrap abtext__in">
      <div class="abtext__col" data-rise>
        <?php foreach ( array_slice( $paras, 1 ) as $p ) : ?>
        <p class="abtext__p"><?php echo artdom_lines( $p ); ?></p>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <?php if ( $quote ) : ?>
  <?php /* Коллаж заглушек, поверх — фраза белым капсом. У референса слова
           разнесены по разным точкам поверх картинок; мы держим ту же идею,
           но без разброса по словам: по-русски он читался бы как ошибка
           вёрстки, а не как приём. */ ?>
  <section class="abstage" data-rise>
    <blockquote class="abstage__q"><?php echo artdom_lines( $quote ); ?></blockquote>
    <p class="abstage__who">
      <?php echo esc_html( artdom_field( 'about_name' ) ); ?><span class="abstage__role"><?php echo esc_html( artdom_field( 'about_role' ) ); ?></span>
    </p>
  </section>
  <?php endif; ?>
  <?php if ( is_array( $principles ) && $principles ) : ?>

  <?php
  /* Сетка плиток по референсу: заголовок занимает первую ячейку, подводка —
     вторую, дальше шесть плиток. В плитке номер, название и кружок с плюсом
     внизу; описание выезжает при наведении.

     tabindex у плитки не для красоты: описание показывается по наведению, а
     с клавиатуры навести нельзя — фокус открывает то же самое. */
  ?>
  <section class="sec sec--white abwhy">
    <div class="wrap abwhy__in">
      <h2 class="abhead abwhy__head" data-rise><?php echo esc_html( artdom_field( 'ab_principles_title' ) ); ?></h2>
      <?php foreach ( $principles as $artdom_i => $pr ) : ?>
      <article class="abtile" data-rise tabindex="0">
        <p class="abtile__n" aria-hidden="true"><?php echo esc_html( str_pad( $artdom_i + 1, 2, '0', STR_PAD_LEFT ) ); ?></p>
        <h3 class="abtile__t"><?php echo esc_html( $pr['title'] ); ?></h3>
        <p class="abtile__x"><?php echo artdom_lines( $pr['text'] ); ?></p>
        <span class="abtile__plus" aria-hidden="true"><svg viewBox="0 0 12 12"><use href="#i-plus"></use></svg></span>
      </article>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <?php if ( is_array( $team ) && $team ) : ?>
  <section class="sec sec--white abteam">
    <div class="wrap abteam__in">
      <h2 class="abhead abteam__head" data-rise><?php echo esc_html( artdom_field( "ab_team_title" ) ); ?></h2>
      <ul class="abteam__list" role="list">
        <?php foreach ( $team as $person ) : ?>
        <li class="abteam__row" data-rise>
          <span class="abteam__shot" aria-hidden="true"></span>
          <h3 class="abteam__name"><?php echo esc_html( $person['name'] ); ?></h3>
          <p class="abteam__role"><?php echo esc_html( $person['role'] ); ?></p>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </section>
  <?php endif; ?>

  <?php if ( is_array( $path ) && $path ) : ?>
  <?php /* «Как мы к этому пришли» — крупные номера акцентом слева, текст
           второй колонкой. У референса номера 64 и оранжевые; у нас тот же
           размер и наш синий. */ ?>
  <section class="sec sec--white abpath">
    <div class="wrap">
      <h2 class="abhead" data-rise><?php echo esc_html( artdom_field( 'ab_path_title' ) ); ?></h2>
      <ol class="abpath__list">
        <?php foreach ( $path as $i => $step ) : ?>
        <li class="abpath__row" data-rise>
          <p class="abpath__n" aria-hidden="true"><?php echo esc_html( str_pad( $i + 1, 2, '0', STR_PAD_LEFT ) ); ?></p>
          <div class="abpath__body">
            <h3 class="abpath__t"><?php echo esc_html( $step['year'] ); ?> — <?php echo esc_html( $step['title'] ); ?></h3>
            <?php if ( ! empty( $step['text'] ) ) : ?>
            <p class="abpath__x"><?php echo artdom_lines( $step['text'] ); ?></p>
            <?php endif; ?>
          </div>
        </li>
        <?php endforeach; ?>
      </ol>
    </div>
  </section>
  <?php endif; ?>

  <?php if ( trim( wp_strip_all_tags( get_the_content() ) ) ) : ?>
  <section class="sec sec--white">
    <div class="wrap prose" data-rise><?php the_content(); ?></div>
  </section>
  <?php endif; ?>
</main>

<?php
endwhile;
get_footer();
