<?php
/**
 * Plugin Name: WP Pizzeria
 * Plugin URI: http://david.binda.cz
 * Description: Turns WordPress instalation into powerful pizzeria site backend with ability to add pizzas, pizza ingredients and custom categorization of pizzas. Allows pizza restaurant owner to take his business website on higher level and increase his revenue from online presentation.
 * Author: David Biňovec
 * Author URI: http://david.binda.cz 
 * Version: 1.1
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Licence: GPLv2 or later
 */

/*

2DO:
 - pizza ingredients taxonomy & pizza categories taxonomy
 	- show image on edit page, allow to remove image
 - custom quick edit menu & custom columns
 	- menu number (unique!)
 	- price (multiple sizes) 
 - other
 	- user manual
 - views
 	- enable to alter posts_per_page on archive
 	- alter WP_QUERY via hook
*/

/* Define plugin name */
if (!defined('WP_PIZZERIA_PLUGIN_NAME'))
    define('WP_PIZZERIA_PLUGIN_NAME', trim(dirname(plugin_basename(__FILE__)), '/'));
/* Define plugin directory */
if (!defined('WP_PIZZERIA_PLUGIN_DIR'))
    define('WP_PIZZERIA_PLUGIN_DIR', WP_PLUGIN_DIR . '/' .WP_PIZZERIA_PLUGIN_NAME);
/* Define plugin url */
if (!defined('WP_PIZZERIA_PLUGIN_URL'))
    define('WP_PIZZERIA_PLUGIN_URL', WP_PLUGIN_URL . '/' . WP_PIZZERIA_PLUGIN_NAME);

/* Internationalize this plugin */


function wp_pizzeria_init() { 
 load_plugin_textdomain('wp_pizzeria', false, basename( dirname( __FILE__ ) ) . '/languages' );
}
add_action('plugins_loaded', 'wp_pizzeria_init');

/* Load custom post types */
include 'custom-post-type-pizza.php';
include 'custom-post-type-beverage.php';
include 'custom-post-type-pasta.php';
include 'custom-post-type-dessert.php';

/* Load custom taxonomies */
include 'taxonomy-pizza-categories.php';
include 'taxonomy-pizza-ingredients.php';
include 'taxonomy-beverage-categories.php';
include 'taxonomy-pasta-categories.php';
include 'taxonomy-dessert-categories.php';

/* Load wp_pizzeria settings page */
include 'pizza-settings-page.php';

/* Load wp_pizzeria display functions */
include 'pizza-display.php';
include 'beverage-display.php';
include 'pasta-display.php';

/* Nav menu modifications */
include 'nav-menu-modifications.php';

/* Rename save button */

add_filter( 'gettext', 'wp_pizzeria_change_publish_button', 10, 2 );

function wp_pizzeria_change_publish_button( $translation, $text ) {
	//check if this is pizza add or edit page in administration	
	global $pagenow, $typenow;
	if ( is_admin() && ( $pagenow == 'post-new.php' or $pagenow == 'post.php' ) && ( $typenow == 'wp_pizzeria_pizza' or ( isset($_GET['post_type']) && $_GET['post_type'] == 'wp_pizzeria_pizza') ) ) {
		if ( $text == 'Publish' )
		    return 'Save Pizza';		
	}
	return $translation;
}

add_action( 'admin_print_footer_scripts', 'wp_pizzeria_rename_save_button' );

function wp_pizzeria_rename_save_button() {
	//check if this is pizza add or edit page in administration	
	global $pagenow, $typenow;
	if ( is_admin() && ( $pagenow == 'post-new.php' or $pagenow == 'post.php' ) && ( $typenow == 'wp_pizzeria_pizza' or ( isset($_GET['post_type']) && $_GET['post_type'] == 'wp_pizzeria_pizza') ) ) { ?>
<script>
jQuery(document).ready(function($){
	if ( $('#publish').val() == 'Update' ){
		$('#publish').val('Update pizza');
	}else{
		$('#publish').val('Bake a pizza');	
	}	
});
</script>
<?php }
}

/* Load plugin's stylesheet for administration */
add_action('admin_head', 'admin_register_head');

function admin_register_head() {
    wp_enqueue_style('wp-pizzeria-admin-style', plugins_url('/css/admin-style.css', __FILE__) );
    if (isset($_GET['taxonomy']) && ( $_GET['taxonomy'] == 'wp_pizzeria_category' || $_GET['taxonomy'] == 'wp_pizzeria_ingredient' ) ){
    	wp_enqueue_style('thickbox');
    	//wp_enqueue_script('thickbox');
    	wp_enqueue_script('wp_pizzeria_upload_image_admin_script', WP_PIZZERIA_PLUGIN_URL . '/js/upload-image.js', array('thickbox') );
    }	
}

add_action( 'wp_enqueue_scripts', 'wp_pizzeria_add_stylesheet' );

function wp_pizzeria_add_stylesheet() {
	wp_register_style( 'wp-pizzeria-style', plugins_url('/css/style.css', __FILE__) );
	wp_enqueue_style( 'wp-pizzeria-style' );
}

add_image_size( 'wp_pizzeria_thumbnail', 100, 100, false );

function wp_pizzeria_template_redirect() {
    if ( is_post_type_archive('wp_pizzeria_pizza') ) :
        include (WP_PIZZERIA_PLUGIN_DIR . '/templates/archive-wp_pizzeria_pizza.php');
        exit;
    endif;
}
//add_action( 'template_redirect', 'wp_pizzeria_template_redirect' );

?>