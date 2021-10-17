<?php

/**
 * theme's main functions and globally usable variables, contants etc
 * added: v1.0 
 * textdomain: exhibz, class: Exhibz, var: $exhibz_, constants: EXHIBZ_, function: exhibz_
 */

// shorthand contants
// ------------------------------------------------------------------------
define('EXHIBZ_THEME', 'Exhibz Event WordPress Theme');
define('EXHIBZ_VERSION', '2.3.3');
define('EXHIBZ_MINWP_VERSION', '5.3');


// shorthand contants for theme assets url
// ------------------------------------------------------------------------
define('EXHIBZ_THEME_URI', get_template_directory_uri());
define('EXHIBZ_IMG', EXHIBZ_THEME_URI . '/assets/images');
define('EXHIBZ_CSS', EXHIBZ_THEME_URI . '/assets/css');
define('EXHIBZ_JS', EXHIBZ_THEME_URI . '/assets/js');



// shorthand contants for theme assets directory path
// ----------------------------------------------------------------------------------------
define('EXHIBZ_THEME_DIR', get_template_directory());
define('EXHIBZ_IMG_DIR', EXHIBZ_THEME_DIR . '/assets/images');
define('EXHIBZ_CSS_DIR', EXHIBZ_THEME_DIR . '/assets/css');
define('EXHIBZ_JS_DIR', EXHIBZ_THEME_DIR . '/assets/js');

define('EXHIBZ_CORE', EXHIBZ_THEME_DIR . '/core');
define('EXHIBZ_COMPONENTS', EXHIBZ_THEME_DIR . '/components');
define('EXHIBZ_EDITOR', EXHIBZ_COMPONENTS . '/editor');
define('EXHIBZ_EDITOR_ELEMENTOR', EXHIBZ_EDITOR . '/elementor');
define('EXHIBZ_INSTALLATION', EXHIBZ_CORE . '/installation-fragments');
define('EXHIBZ_REMOTE_CONTENT', esc_url('http://demo.themewinter.com/demo-content/exhibz'));


// set up the content width value based on the theme's design
// ----------------------------------------------------------------------------------------
if (!isset($content_width)) {
    $content_width = 800;
}

// set up theme default and register various supported features.
// ----------------------------------------------------------------------------------------

function exhibz_setup() {

    // make the theme available for translation
    $lang_dir = EXHIBZ_THEME_DIR . '/languages';
    load_theme_textdomain('exhibz', $lang_dir);

    // add support for post formats
    add_theme_support('post-formats', [
        'standard', 'gallery', 'video', 'audio'
    ]);

    // add support for automatic feed links
    add_theme_support('automatic-feed-links');

    // let WordPress manage the document title
    add_theme_support('title-tag');

    // add support for post thumbnails
    add_theme_support('post-thumbnails');

    // hard crop center center
    set_post_thumbnail_size(750, 465, ['center', 'center']);

    // woocommerce support
    
     add_theme_support( 'woocommerce', array(
        'thumbnail_image_width' => 600,
        'gallery_thumbnail_image_width' => 300,
        'single_image_width' => 600,
    ) );

    add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'wc-product-gallery-slider' );

 
    // register navigation menus
    register_nav_menus(
        [
            'primary' => esc_html__('Primary Menu', 'exhibz'),
            'footermenu' => esc_html__('Footer Menu', 'exhibz'),
        ]
    );

  
    // HTML5 markup support for search form, comment form, and comments
    add_theme_support('html5', array(
        'search-form', 'comment-form', 'comment-list', 'gallery', 'caption'
    ));

    /*
	 * Enable support for wide alignment class for Gutenberg blocks.
	 */
	add_theme_support( 'align-wide' );

}
add_action('after_setup_theme', 'exhibz_setup');



// hooks for unyson framework
// ----------------------------------------------------------------------------------------
function exhibz_framework_customizations_path($rel_path) {
    return '/components';
}
add_filter('fw_framework_customizations_dir_rel_path', 'exhibz_framework_customizations_path');


function exhibz_remove_fw_settings() {
    remove_submenu_page( 'themes.php', 'fw-settings' );
}
add_action( 'admin_menu', 'exhibz_remove_fw_settings', 999 );


//Gutenberg optimization enqueue files
add_action('enqueue_block_editor_assets', 'exhibz_action_enqueue_block_editor_assets' );
function exhibz_action_enqueue_block_editor_assets() {
    wp_enqueue_style( 'exhibz-fonts', exhibz_google_fonts_url(['Raleway:400,500,600,700,800,900', 'Roboto:400,700']), null, EXHIBZ_VERSION );
    wp_enqueue_style( 'exhibz-gutenberg-editor-font-awesome-styles', EXHIBZ_CSS . '/font-awesome.css', null, EXHIBZ_VERSION );
    wp_enqueue_style( 'exhibz-gutenberg-editor-customizer-styles', EXHIBZ_CSS . '/gutenberg-editor-custom.css', null, EXHIBZ_VERSION );
    wp_enqueue_style( 'exhibz-gutenberg-editor-styles', EXHIBZ_CSS . '/gutenberg-custom.css', null, EXHIBZ_VERSION );
    wp_enqueue_style( 'exhibz-gutenberg-blog-styles', EXHIBZ_CSS . '/blog.css', null, EXHIBZ_VERSION );
}

// include the init.php
// ----------------------------------------------------------------------------------------
require_once( EXHIBZ_CORE . '/init.php');
require_once( EXHIBZ_COMPONENTS . '/editor/elementor/elementor.php');

// preloader function
// ----------------------------------------------------------------------------------------
            

function exhibz_preloader_function(){
    $preloader_show = exhibz_option('preloader_show');
        if($preloader_show == 'yes'){
            $preloader_logo_url= esc_url(exhibz_src('preloader_logo'));
        ?>
        <div id="preloader">
            <?php if($preloader_logo_url !=''): ?>
            
            <div class="preloader-logo">
                <img  class="img-fluid" src="<?php echo esc_url($preloader_logo_url); ?>" alt="<?php echo get_bloginfo('name') ?>">
            </div>
            <?php else: ?>
            <div class="spinner">
                <div class="double-bounce1"></div>
                <div class="double-bounce2"></div>
            </div>
            <?php endif; ?>
            <div class="preloader-cancel-btn-wraper"> 
                <span class="btn btn-primary preloader-cancel-btn">
                  <?php echo esc_html__('Cancel Preloader', 'exhibz'); ?></span>
            </div>
        </div>
    <?php
    }
}
add_action('wp_head', 'exhibz_preloader_function');



function exhibz_advanced_search_filter(){

    $date_order = [
        'today'        => esc_html__( ' Today ', 'exhibz' ),
        'tomorrow'     => esc_html__( ' Tomorrow ', 'exhibz' ),
        'yesterday'    => esc_html__( ' Yesterday ', 'exhibz' ),
        'this-weekend' => esc_html__( ' This Weekend ', 'exhibz' ),
        'this-week'    => esc_html__( ' This Week ', 'exhibz' ),
        'this-month'   => esc_html__( ' This Month ', 'exhibz' ),
        'upcoming'     => esc_html__( ' Upcoming ', 'exhibz' ),
        'expired'      => esc_html__( ' Expired ', 'exhibz' ),
    ];
    $event_type_order = [
        'on'  => esc_html__( 'Online Event', 'exhibz' ),
        'no' => esc_html__( 'Offline Event', 'exhibz' ),
    ];
    ?>
    <div class="etn_event_inline_form_bottom" style="display: block;">
        <h3 class="etn_event_form_title"><?php echo esc_html__( "Advanced Search", 'exhibz' ); ?></h3>
        <div class="etn-row">
            <div class="etn-col-lg-4 etn-col-md-6">
                <p class="etn_event_inline_input_label"><?php echo esc_html__( "Sort by:", 'exhibz' )?></p>
                <select name="etn_event_date_range" class="etn_event_select2 etn_event_select">
                    <option value><?php echo esc_html__( "Event Date", 'exhibz' )?></option>
                    <?php
                    if (!empty($date_order) && is_array($date_order)) {
                        $select_date_value = '';
                        if (isset($_GET['etn_event_date_range'])) {
                            $select_date_value = $_GET['etn_event_date_range'];
                        }
                        foreach ($date_order as $key => $value) { ?>
                            <option <?php if (!empty($select_date_value) && $select_date_value == $key ) echo ' selected="selected"' ; ?> value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>
                        <?php }
                    }
                    ?>
                </select>
                <!-- // event date -->
            </div>
            <?php
            $settings = \Etn\Core\Settings\Settings::instance()->get_settings_option();
            if (isset($settings['etn_zoom_api']) && $settings['etn_zoom_api'] === "on") {
            ?>
            <div class="etn-col-lg-4 etn-col-md-6">
                <p class="etn_event_inline_input_label"><?php echo esc_html__( "Event Type:", 'exhibz' )?></p>
                <select name="etn_event_will_happen" class="etn_event_select2 etn_event_select">
                    <option value><?php echo esc_html__( "Event Type", 'exhibz' )?></option>
                    <?php
                    if (is_array($event_type_order) && !empty($event_type_order)) {
                        foreach ($event_type_order as $key => $value) {
                            $select_value = "";
                            if (isset($_GET['etn_event_will_happen'])) {
                                $select_value = $_GET['etn_event_will_happen'];
                            }
                            ?>
                        <option <?php if (!empty($select_value) && $select_value === $key ) echo ' selected="selected"' ; ?> value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>
                            <?php
                        }
                    }
                    ?>
                </select>
            </div>
            <?php } ?>
        </div>
    </div>
    <?php
 }
 if(!defined('ETN_PRO_FILES_LOADED')){ 
    add_action( 'etn_advanced_search', 'exhibz_advanced_search_filter' );
 }

 
// use for event location sub menu in admin area
if(is_admin()){
    $parent = 'etn-events-manager';
    $name   = 'event_location';
    $cpt    = 'etn';

    add_action( 'admin_menu', function () use ( $parent, $name, $cpt ) {
        add_submenu_page(
            $parent,
            esc_html__( 'Event Location', 'eventin' ),
            esc_html__( 'Event Location', 'eventin' ),
            'edit_posts',
            'edit-tags.php?taxonomy=' . $name . '&post_type=' . $cpt,
            false,
            4
        );
    } );
}