<?php
/**
 * Template Name: Главная
 * Template Post Type: page
 *
 * @package artdom
 */

get_header();
?>

<main id="main">
	<?php get_template_part( 'template-parts/main/hero' ); ?>
	<?php get_template_part( 'template-parts/main/services' ); ?>
	<?php get_template_part( 'template-parts/main/objects' ); ?>
	<?php get_template_part( 'template-parts/main/about' ); ?>
	<?php get_template_part( 'template-parts/main/guaranty' ); ?>
	<?php get_template_part( 'template-parts/main/stats' ); ?>
	<?php get_template_part( 'template-parts/main/reviews' ); ?>
	<?php get_template_part( 'template-parts/main/blog' ); ?>
</main>

<?php
get_footer();