<?php
if (! defined("ABSPATH") ){
    exit; //Exit if accessed directly
}
if ( ! function_exists( 'artdom_setup' ) ) :
    /**
     * Sets up theme defaults and registers support for various WordPress features.
     *
     * Note that this function is hooked into the after_setup_theme hook, which
     * runs before the init hook. The init hook is too late for some features, such
     * as indicating support for post thumbnails.
     */
    function artdom_setup() {
        /*
         * Make theme available for translation.
         * Translations can be filed in the /languages/ directory.
         * If you're building a theme based on artdom, use a find and replace
         * to change 'artdom' to the name of your theme in all the template files.
         */
        load_theme_textdomain( 'artdom', get_template_directory() . '/languages' );

        // Add default posts and comments RSS feed links to head.
        add_theme_support( 'automatic-feed-links' );

        /*
         * Let WordPress manage the document title.
         * By adding theme support, we declare that this theme does not use a
         * hard-coded <title> tag in the document head, and expect WordPress to
         * provide it for us.
         */
        add_theme_support( 'title-tag' );

        /*
         * Enable support for Post Thumbnails on posts and pages.
         *
         * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
         */
        add_theme_support( 'post-thumbnails' );

        // This theme uses wp_nav_menu() in one location.
        register_nav_menus(
            array(
                'menu_main' => esc_html__( 'Главное', 'artdom' ),
                'menu_footer' => esc_html__( 'В подвале', 'artdom' ),
            )
        );

        /*
         * Switch default core markup for search form, comment form, and comments
         * to output valid HTML5.
         */
        add_theme_support(
            'html5',
            array(
                'search-form',
                'comment-form',
                'comment-list',
                'gallery',
                'caption',
                'style',
                'script',
            )
        );

        // Set up the WordPress core custom background feature.
        add_theme_support(
            'custom-background',
            apply_filters(
                'artdom_custom_background_args',
                array(
                    'default-color' => 'ffffff',
                    'default-image' => '',
                )
            )
        );

        // Add theme support for selective refresh for widgets.
        add_theme_support( 'customize-selective-refresh-widgets' );

        /**
         * Add support for core custom logo.
         *
         * @link https://codex.wordpress.org/Theme_Logo
         */
        add_theme_support(
            'custom-logo',
            array(
                'height'      => 250,
                'width'       => 250,
                'flex-width'  => true,
                'flex-height' => true,
            )
        );
    }
endif;
add_action( 'after_setup_theme', 'artdom_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function artdom_content_width() {
    $GLOBALS['content_width'] = apply_filters( 'artdom_content_width', 640 );
}
add_action( 'after_setup_theme', 'artdom_content_width', 0 );


/**
 * Admin footer modification
 */
function remove_footer_admin ()
{
    echo '<span id="footer-thankyou">Developed by: <a href="mailto:oxbox@oxbox.ru">oxbox.ru </a></span>';
}
add_filter('admin_footer_text', 'remove_footer_admin');

/**
 *  Remove emoji Wordpress
 */
remove_action("wp_head", "print_emoji_detection_script", 7);
remove_action("admin_print_scripts", "print_emoji_detection_script");
remove_action("wp_print_styles", "print_emoji_styles");
remove_action("admin_print_styles", "print_emoji_styles");

/**
 *  Security  for WP from change files of users adminpanel
 */
## Полное Удаление версии WP
## Также нужно удалить файл readme.html в корне сайта
//remove_action('wp_head', 'wp_generator'); // из заголовка
//add_filter('the_generator', '__return_empty_string');
// Отключим вывод ошибок на странице авторизации
add_filter('login_errors', 'login_obscure_func');
function login_obscure_func(){
    return 'Ошибка: вы ввели неправильный логин или пароль.';
}
/**
 *  Удаляет "Рубрика: ", "Метка: " и т.д. из заголовка архива
 */
add_filter( 'get_the_archive_title', function( $title ){
    return preg_replace('~^[^:]+: ~', '', $title );
});


/**
 *  Custom thumbnail size
 */
//if ( function_exists( 'add_image_size' ) ) {
//    add_image_size( 'wise_img', 460, 320, array( 'center', 'center' ) );
//    add_image_size( 'wise_gal', 420, 260, array( 'center', 'center' ) );
//}
/**
 *  change post per page for CPT services
 */
function wisedev_number_displayed_posts($query) {
    if (is_admin() || !$query->is_main_query()) {
        return;
    }
    if (is_post_type_archive('vakances')) {
        $query->set('posts_per_page', 100);
        return;
    }
}
add_action('pre_get_posts', 'wisedev_number_displayed_posts', 1);

if( function_exists('bcn_display') ) {
    add_filter('bcn_breadcrumb_title', 'my_breadcrumb_title_swapper', 3, 10);
    function my_breadcrumb_title_swapper($title, $type, $id)
    {
        if (in_array('home', $type)) {
            $title = __('Главная');
        }
        return $title;
    }
}
