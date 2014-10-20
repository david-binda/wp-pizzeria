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

abstract class CPT_Factory {
	private static $_instances = array();
	public static function getInstance() {
		$class = get_called_class();
		if (!isset(self::$_instances[$class])) {
			self::$_instances[$class] = new $class();
		}
		return self::$_instances[$class];
	}

	protected static function construct( $obj ) {
		add_action( 'init', array( $obj, 'register_post_type' ), 10, 0 );

		add_action( 'save_post', array( $obj, 'save_postdata' ), 10, 1 );
		add_action( 'dashboard_glance_items', array( $obj, 'add_counts' ), 10, 0 );
		add_filter( "manage_edit-{$obj->post_type}_columns", array( $obj, 'edit_columns' ), 10, 1 );
		add_action( "manage_{$obj->post_type}_posts_custom_column", array( $obj, 'manage_columns' ), 10, 2 );
}
	private function __clone() {
	}

	private function __wakeup() {
	}

	public function add_counts() {
		if ( false === post_type_exists( $this->post_type ) ) {
			return;
		}

		$post_type_obj = get_post_type_object( $this->post_type );

		$num_posts = wp_count_posts( $this->post_type );
		$num       = number_format_i18n( $num_posts->publish );
		$text      = _n( $post_type_obj->labels->singular_name, $post_type_obj->labels->name, intval( $num_posts->publish ) );
		if ( current_user_can( 'edit_posts' ) ) {
			$text = sprintf( '<a href="edit.php?post_type=%s">%d %s</a>', $this->post_type, $num, $text );
		}
		echo sprintf( '<li class="t %s">%s</li>', $this->post_type, $text );

	}

	public function edit_columns( $columns ) {

		$columns = array(
			'cb'          => '<input type="checkbox" />',
			'menu_number' => strip_tags( __( '#', 'wp_pizzeria' ) ),
			'title'       => strip_tags( __( 'Title' ) ),
			'category'    => strip_tags( __( 'Category', 'wp_pizzeria' ) ),
			'price'       => strip_tags( __( 'Price', 'wp_pizzeria' ) ),
			'date'        => strip_tags( __( 'Date' ) )
		);

		return $columns;
	}

	public function manage_columns( $column, $post_id ) {
		global $post;
		switch ( $column ) {
			case 'menu_number' :
				global $wpdb;
				$menu_id = $wpdb->get_var( $wpdb->prepare( "SELECT menu_order FROM $wpdb->posts WHERE ID = %d ", $post_id ) );
				echo $menu_id;
				break;
			case 'category' :
				$terms = get_the_terms( $post_id, $this->post_type . '_category' );
				if ( ! empty( $terms ) ) {
					$out = array();
					foreach ( $terms as $term ) {
						$out[] = sprintf( '<a href="%s">%s</a>',
							esc_url( add_query_arg( array( 'post_type'                  => $post->post_type,
							                               $this->post_type . '_category' => $term->slug
							), 'edit.php' ) ),
							esc_html( sanitize_term_field( 'name', $term->name, $term->term_id, $this->post_type . '_category', 'display' ) )
						);
					}
					echo join( ', ', $out );
				} else {
					_e( 'No Categories', 'wp_pizzeria' );
				}
				break;

			case 'price' :
				$pizzeria_settings = maybe_unserialize( get_option( 'wp_pizzeria_settings' ) );
				if ( false !== get_post_meta( $post_id, '_wp_pizzeria_price', true ) ) {
					if ( array_key_exists( 'currency', $pizzeria_settings ) && array_key_exists( 'currency_pos', $pizzeria_settings ) && $pizzeria_settings['currency_pos'] == 'before' ) {
						echo $pizzeria_settings['currency'];
					}
					echo get_post_meta( $post_id, '_wp_pizzeria_price', true );
					if ( array_key_exists( 'currency', $pizzeria_settings ) && ( ! array_key_exists( 'currency_pos', $pizzeria_settings ) || $pizzeria_settings['currency_pos'] == 'after' ) ) {
						echo $pizzeria_settings['currency'];
					}
				} else {
					echo '';
				}
				break;
			default :
				break;
		}
	}
}

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

		//add_action( 'template_redirect', array( $this, 'template_redirect' ) );

		$this->load_cpts();
	}

	private function __clone() {
	}

	private function __wakeup() {
	}

	public function init() {
		load_plugin_textdomain( 'wp_pizzeria', false, basename( dirname( __FILE__ ) ) . '/languages' );
	}

	private function load_cpts() {

		include 'custom-post-type-beverage.php';
		$bevarage = WP_Pizzeria_Bevarage::getInstance();
		include 'beverage-display.php';
		$bevarage_display = WP_Pizzeria_Bevarage_Display::getInstance();

		/* Load custom post types */
		include 'custom-post-type-pizza.php';
		$pizza = WP_Pizzeria_Pizza::getInstance();

		include 'custom-post-type-pasta.php';
		$pasta = WP_Pizzeria_Pasta::getInstance();

		include 'custom-post-type-dessert.php';
		$desert = WP_Pizzeria_Desert::getInstance();
	}

	/* Rename save button */
	public function change_publish_button( $translation, $text ) {
		//check if this is pizza add or edit page in administration
		global $pagenow, $typenow;
		if ( true === is_admin() &&
		     ( 'post-new.php' === $pagenow || 'post.php' === $pagenow )
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
		     && ( 'post-new.php' === $pagenow || 'post.php' === $pagenow )
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
include 'pasta-display.php';

/* Nav menu modifications */
include 'nav-menu-modifications.php';

$wp_pizzeria = WP_Pizzeria::getInstance();