<?php
/**
 * Denant functions and definitions
 *
 * Set up the theme and provides some helper functions, which are used in the
 * theme as custom template tags. Others are attached to action and filter
 * hooks in WordPress to change core functionality.
 *
 * When using a child theme you can override certain functions (those wrapped
 * in a function_exists() call) by defining them first in your child theme's
 * functions.php file. The child theme's functions.php file is included before
 * the parent theme's file, so the child theme functions would be used.
 *
 * @link https://codex.wordpress.org/Theme_Development
 * @link https://codex.wordpress.org/Child_Themes
 *
 * Functions that are not pluggable (not wrapped in function_exists()) are
 * instead attached to a filter or action hook.
 *
 * For more information on hooks, actions, and filters,
 * {@link https://codex.wordpress.org/Plugin_API}
 *
 * @package WordPress
 * @subpackage Denant
 * @since Denant 1.0
 */

/**
 * Set the content width based on the theme's design and stylesheet.
 *
 * @since Denant
 */

if ( ! function_exists( 'denant_setup' ) ) :
    /**
     * Sets up theme defaults and registers support for various WordPress features.
     *
     * Note that this function is hooked into the after_setup_theme hook, which
     * runs before the init hook. The init hook is too late for some features, such
     * as indicating support for post thumbnails.
     *
     * @since Denant 1.0
     */
    function denant_setup() {

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
         * See: https://codex.wordpress.org/Function_Reference/add_theme_support#Post_Thumbnails
         */
        add_theme_support( 'post-thumbnails' );
        set_post_thumbnail_size( 825, 510, true );

        /*
         * Switch default core markup for search form, comment form, and comments
         * to output valid HTML5.
         */
        add_theme_support( 'html5', array(
            'search-form', 'comment-form', 'comment-list', 'gallery', 'caption'
        ) );

        /*
         * Enable support for Post Formats.
         *
         * See: https://codex.wordpress.org/Post_Formats
         */
        add_theme_support( 'post-formats', array(
            'aside', 'image', 'video', 'quote', 'link', 'gallery', 'status', 'audio', 'chat'
        ) );
    }
endif; // denant_setup
add_action( 'after_setup_theme', 'denant_setup' );

/**
 * Enqueue scripts and styles.
 *
 * @since Denant 1.0
 */
function load_scripts() {
    // Load our main stylesheet.
    wp_enqueue_style('font-style', get_stylesheet_uri() );
//    wp_enqueue_style('font-google1', 'http://fonts.googleapis.com/css?family=Inconsolata:400,400italic,600,700');
//    wp_enqueue_style('font-google2', 'http://fonts.googleapis.com/css?family=Raleway:300,400,400italic,600,700');

    wp_enqueue_style('font-google3', 'https://fonts.googleapis.com/css?family=Lora');


    wp_enqueue_style( 'reset', get_template_directory_uri() . '/css/reset.css', array(), '1.0.0', 'all' );
    wp_enqueue_style( 'bootstrap', get_template_directory_uri() . '/css/bootstrap.min.css', array(), '1.0.0', 'all' );
    wp_enqueue_style( 'font-awesome', get_template_directory_uri() . '/css/font-awesome.min.css', array(), '1.0.0', 'all' );
    wp_enqueue_style( 'contact', get_template_directory_uri() . '/css/contact.css', array(), '1.0.0', 'all' );
    wp_enqueue_style( 'flexslider', get_template_directory_uri() . '/css/flexslider.css', array(), '1.0.0', 'all' );
    wp_enqueue_style( 'jquery.fancybox', get_template_directory_uri() . '/css/jquery.fancybox.css', array(), '1.0.0', 'all' );
    wp_enqueue_style( 'animate', get_template_directory_uri() . '/css/animate.css', array(), '1.0.0', 'all' );
    wp_enqueue_style( 'owl.carousel', get_template_directory_uri() . '/css/owl.carousel.css', array(), '1.0.0', 'all' );
    wp_enqueue_style( 'owl.theme.default', get_template_directory_uri() . '/css/owl.theme.default.css', array(), '1.0.0', 'all' );
    wp_enqueue_style( 'owl.theme.default', get_template_directory_uri() . '/css/owl.theme.default.css', array(), '1.0.0', 'all' );

    wp_enqueue_script('jquery');
    wp_enqueue_script( 'modernizr.custom', get_template_directory_uri() . '/js/modernizr.custom.js', array(), '1.0.0');
    wp_enqueue_script( 'menu-custom', get_template_directory_uri() . '/js/menu-custom.js', array(), '1.0.0');
//    wp_enqueue_script( 'retina.min', get_template_directory_uri() . '/js/retina.min.js', array(), '1.0.0', true );
//    wp_enqueue_script( 'jquery.nav', get_template_directory_uri() . '/js/jquery.nav.js', array(), '1.0.0', true );
    wp_enqueue_script( 'input.fields', get_template_directory_uri() . '/js/input.fields.js', array(), '1.0.0', true );
    wp_enqueue_script( 'preloader', get_template_directory_uri() . '/js/preloader.js', array(), '1.0.0', true );
//    wp_enqueue_script( 'responsive-nav', get_template_directory_uri() . '/js/responsive-nav.js', array(), '1.0.0', true );
    wp_enqueue_script( 'flexslider-min', get_template_directory_uri() . '/js/jquery.flexslider-min.js', array(), '1.0.0', true );
    wp_enqueue_script( 'jquery.sticky', get_template_directory_uri() . '/js/jquery.sticky.js', array(), '1.0.0', true );
    wp_enqueue_script( 'jquery.lettering', get_template_directory_uri() . '/js/jquery.lettering.js', array(), '1.0.0', true );
    wp_enqueue_script( 'jquery.textillate', get_template_directory_uri() . '/js/jquery.textillate.js', array(), '1.0.0', true );
    wp_enqueue_script( 'general-lettering', get_template_directory_uri() . '/js/general-lettering.js', array(), '1.0.0', true );
    wp_enqueue_script( 'bootstrap.min-js', get_template_directory_uri() . '/js/bootstrap.min.js', array(), '1.0.0', true );
    wp_enqueue_script( 'owl.carousel.min-js', get_template_directory_uri() . '/js/owl.carousel.min.js', array(), '1.0.0', true );
    wp_enqueue_script( 'waypoints.min-js', get_template_directory_uri() . '/js/waypoints.min.js', array(), '1.0.0', true );
    wp_enqueue_script( 'custom-scripts', get_template_directory_uri() . '/js/scripts.js', array(), '1.0.0', true );
    wp_enqueue_script( 'ie-js-html', get_template_directory_uri() . 'http://html5shim.googlecode.com/svn/trunk/html5.js', array(), '1.0.0', true );
    wp_script_add_data('ie-js-html', 'conditional', 'lt IE 9');
}
add_action( 'wp_enqueue_scripts', 'load_scripts' );

add_action('customize_register', function($customizer){
    $customizer->add_section(
        'settings_denant',
        array(
            'title' => 'Настройки Денант',
            'description' => 'Телефон и лого',
            'priority' => 35,
        )
    );
    $customizer->add_setting(
        'item_phone',
        array('default' => '+375(152)60-50-66')
    );

    $customizer->add_control(
        'item_phone',
        array(
            'label' => 'Телефон',
            'section' => 'settings_denant',
            'type' => 'text',
        )
    );

    $customizer->add_setting(
        'item_logo'
    );

    $customizer->add_control(
        'item_logo',
        array(
            'label' => 'Логотип',
            'section' => 'settings_denant',
            'type' => 'file',
        )
    );
});

/**
 * Metaboxes additions.
 */
require get_template_directory().'/inc/custom-metaboxes.php';