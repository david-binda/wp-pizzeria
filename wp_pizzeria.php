<?php
/**
 * Plugin Name: WP Pizzeria
 * Plugin URI: https://github.com/david-binda/wp-pizzeria
 * Description: Turns WordPress instalation into powerful pizzeria site backend with ability to add pizzas, pizza ingredients and custom categorization of pizzas. Allows pizza restaurant owner to take his business website on higher level and increase his revenue from online presentation.
 * Author: David Biňovec
 * Author URI: http://david.binda.cz
 * Version: 1.2
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
if ( false === defined( 'WP_PIZZERIA_PLUGIN_NAME' ) ) {
	define( 'WP_PIZZERIA_PLUGIN_NAME', trim( dirname( plugin_basename( __FILE__ ) ), '/' ) );
}
/* Define plugin directory */
if ( false === defined( 'WP_PIZZERIA_PLUGIN_DIR' ) ) {
	define( 'WP_PIZZERIA_PLUGIN_DIR', constant( 'WP_PLUGIN_DIR' ) . '/' . constant( 'WP_PIZZERIA_PLUGIN_NAME' ) );
}
/* Define plugin url */
if ( false === defined( 'WP_PIZZERIA_PLUGIN_URL' ) ) {
	define( 'WP_PIZZERIA_PLUGIN_URL', constant( 'WP_PLUGIN_URL' ) . '/' . constant( 'WP_PIZZERIA_PLUGIN_NAME' ) );
}

/* Internationalize this plugin */

class WP_Pizzeria {

	public static function getInstance() {
		static $instance = null;
		if ( null === $instance ) {
			$instance = new static();
		}

		return $instance;
	}

	protected function __construct() {
		add_action( 'plugins_loaded', array( $this, 'init' ) );
		add_filter( 'gettext', array( $this, 'change_publish_button' ), 10, 2 );
		add_action( 'admin_print_footer_scripts', array( $this, 'rename_save_button' ) );
		add_action( 'admin_head', array( $this, 'admin_register_head' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'add_stylesheet' ) );

		add_image_size( 'wp_pizzeria_thumbnail', 100, 100, false );

		$this->load_cpts();
		$this->load_displays();
		$this->load_taxonomies();

		/* Load wp_pizzeria settings page */
		include 'pizza-settings-page.php';

		/* Nav menu modifications */
		include 'nav-menu-modifications.php';
	}

	private function __clone() {
	}

	private function __wakeup() {
	}

	public function init() {
		load_plugin_textdomain( 'wp_pizzeria', false, basename( dirname( __FILE__ ) ) . '/languages' );
	}

	private function load_cpts() {

		require_once( 'cpt_factory.php' );

		foreach ( glob( WP_PIZZERIA_PLUGIN_DIR . '/custom\-post\-type\-*.php' ) as $filename ) {
			$post_type_name = ucfirst( str_replace( 'custom-post-type-', '', basename( $filename ) ) );
			$post_type_name = str_replace( '.php', '', $post_type_name );
			require_once( $filename );
			$class_name = "WP_Pizzeria_{$post_type_name}";
			$class_name::getInstance();
		}
	}

	private function load_displays() {
		foreach ( glob( dirname( __FILE__ ) . '/*\-display.php' ) as $filename ) {
			$post_type_name = ucfirst( str_replace( '-display', '', basename( $filename ) ) );
			$post_type_name = str_replace( '.php', '', $post_type_name );
			require_once( $filename );
			$class_name = "WP_Pizzeria_{$post_type_name}_Display";
			$class_name::getInstance();
		}
	}

	private function load_taxonomies() {
		/* Load custom taxonomies */
		require_once( WP_PIZZERIA_PLUGIN_DIR . '/tax-factory.php' );

		require_once( WP_PIZZERIA_PLUGIN_DIR . '/taxonomy-pizza-categories.php' );
		WP_Pizzeria_Pizza_Categories::getInstance();

		require_once( WP_PIZZERIA_PLUGIN_DIR . '/taxonomy-pizza-ingredients.php' );
		WP_Pizzeria_Pizza_Ingredients::getInstance();

		require_once( WP_PIZZERIA_PLUGIN_DIR . '/taxonomy-beverage-categories.php' );
		WP_Pizzeria_Beverage_Categories::getInstance();

		require_once( WP_PIZZERIA_PLUGIN_DIR . '/taxonomy-pasta-categories.php' );
		WP_Pizzeria_Pasta_Categories::getInstance();

		require_once( WP_PIZZERIA_PLUGIN_DIR . '/taxonomy-dessert-categories.php' );
		WP_Pizzeria_Dessert_Categories::getInstance();
	}

	/* Rename save button */
	public function change_publish_button( $translation, $text ) {
		//check if this is pizza add or edit page in administration
		global $pagenow, $typenow;
		if ( true === is_admin()
		     && true === in_array( $pagenow, array( 'post-new.php', 'post.php' ), true )
		     && ( 'wp_pizzeria_pizza' === $typenow || ( true === isset( $_GET['post_type'] ) && 'wp_pizzeria_pizza' === $_GET['post_type'] ) )
		) {
			if ( 'Publish' === $text ) {
				$translation = esc_attr__( 'Save Pizza', 'wp_pizzeria' );
			}
		}

		return $translation;
	}

	public function rename_save_button() {
		//check if this is pizza add or edit page in administration
		global $pagenow, $typenow;
		if ( true === is_admin()
		     && true === in_array( $pagenow, array( 'post-new.php', 'post.php' ), true )
		     && ( 'wp_pizzeria_pizza' === $typenow || ( true === isset( $_GET['post_type'] ) && 'wp_pizzeria_pizza' === $_GET['post_type'] ) )
		) {
			$update_val  = esc_attr__( 'Update pizza', 'wp_pizzeria' );
			$publish_val = esc_attr__( 'Bake a Pizza', 'wp_pizzeria' );
			//todo: figure out translated update button
			$script = <<<EOT
			<script>
				jQuery(document).ready(function($){
					if ( $('#publish').val() == 'Update' ){
						$('#publish').val( {$update_val} );
					} else {
						$('#publish').val( {$publish_val} );
					}
				});
			</script>
EOT;
			echo $script;
		}
	}

	/* Load plugin's stylesheet for administration */
	public function admin_register_head() {
		wp_enqueue_style( 'wp-pizzeria-admin-style', plugins_url( '/css/admin-style.css', __FILE__ ) );
		if ( true === isset( $_GET['taxonomy'] )
		     && ( 'wp_pizzeria_category' === $_GET['taxonomy'] || 'wp_pizzeria_ingredient' === $_GET['taxonomy'] )
		) {
			wp_enqueue_style( 'thickbox' );
			wp_enqueue_script( 'wp_pizzeria_upload_image_admin_script', constant( 'WP_PIZZERIA_PLUGIN_URL' ) . '/js/upload-image.js', array( 'thickbox' ) );
		}
	}

	public function add_stylesheet() {
		wp_register_style( 'wp-pizzeria-style', plugins_url( '/css/style.css', __FILE__ ) );
		wp_enqueue_style( 'wp-pizzeria-style' );
	}

	public function template_redirect() {
		if ( true === is_post_type_archive( 'wp_pizzeria_pizza' ) ) {
			include( WP_PIZZERIA_PLUGIN_DIR . '/templates/archive-wp_pizzeria_pizza.php' );
			exit;
		}
	}
}

$wp_pizzeria = WP_Pizzeria::getInstance();