<?php
/*
* Register custom taxonomy wp_pizzeria_pizza for pizzas
*/
Class WP_Pizzeria_Desert extends CPT_Factory {

	protected $post_type = 'wp_pizzeria_desert';

	protected function __construct() {
		parent::construct( $this );
	}

	public function register_post_type() {
		//setup labels
		$labels = array(
			'name'               => esc_html__( 'Desserts', 'wp_pizzeria' ),
			'singular_name'      => esc_html__( 'Dessert', 'wp_pizzeria' ),
			'add_new'            => esc_html__( 'Add Dessert', 'wp_pizzeria' ),
			'add_new_item'       => esc_html__( 'Add Dessert', 'wp_pizzeria' ),
			'edit_item'          => esc_html__( 'Edit Dessert', 'wp_pizzeria' ),
			'new_item'           => esc_html__( 'New Dessert', 'wp_pizzeria' ),
			'all_items'          => esc_html__( 'Desserts', 'wp_pizzeria' ),
			'view_item'          => esc_html__( 'View Desserts', 'wp_pizzeria' ),
			'search_items'       => esc_html__( 'Search Desserts', 'wp_pizzeria' ),
			'not_found'          => esc_html__( 'No desserts found', 'wp_pizzeria' ),
			'not_found_in_trash' => esc_html__( 'No desserts in the trash', 'wp_pizzeria' ),
			'parent_item_colon'  => '',
			'menu_name'          => esc_html__( 'Desserts', 'wp_pizzeria' )
		);
		//register custom post type using before declared labels
		register_post_type( $this->post_type, array(
			'labels'               => $labels,
			'public'               => true,
			'show_in_nav_menus'    => true,
			'show_ui'              => true,
			'capability_type'      => 'post',
			'has_archive'          => true,
			'rewrite'              => array( 'slug' => 'dessert', 'with_front' => true ),
			'query_var'            => false,
			'supports'             => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
			'taxonomies'           => array( 'wp_pizzeria_dessert_category' ),
			'register_meta_box_cb' => array( $this, 'custom_box' )
		) );
	}

	/* Custom Meta boxes */

	public function custom_box() {
		if ( isset( $_GET['post'] ) ) {
			$post_id = $_GET['post'];
		} elseif ( isset( $_POST['post_ID'] ) ) {
			$post_id = $_POST['post_ID'];
		}
		if (
			( true === isset( $post_id ) && $this->post_type === get_post_type( $post_id ) ) ||
			( true === isset( $_GET['post_type'] ) && $this->post_type === $_GET['post_type'] )
		) {
			remove_meta_box( 'pageparentdiv', 'wp_pizzeria_dessert', 'side' );
			add_meta_box(
				'wp_pizzeria_dessert_price_custom_box',
				__( 'Dessert price', 'wp_pizzeria' ),
				array( $this, 'inner_custom_box' ),
				$this->post_type,
				'side',
				'core'
			);
			add_meta_box(
				'wp_pizzeria_number_custom_box',
				__( 'Dessert menu number', 'wp_pizzeria' ),
				'wp_pizzeria_number_inner_custom_box',
				$this->post_type,
				'side',
				'core'
			);
		}
	}


	public function inner_custom_box( $post ) {
		$price = get_post_meta( $post->ID, '_wp_pizzeria_price', true );
		if ( $price === false ) {
			$price = '';
		}
		$pizzeria_settings = maybe_unserialize( get_option( 'wp_pizzeria_settings' ) );
		?>
		<p>
			<label for="dessert_price"><?php _e( 'Price', 'wp_pizzeria' ); ?></label>
			<?php if ( array_key_exists( 'currency', $pizzeria_settings ) && array_key_exists( 'currency_pos', $pizzeria_settings ) && $pizzeria_settings['currency_pos'] == 'before' ) {
				echo $pizzeria_settings['currency'];
			} ?>
			<input type="text" id="dessert_price" name="dessert_price" value="<?php echo $price; ?>" />
			<?php
			if ( array_key_exists( 'currency', $pizzeria_settings ) && ( ! array_key_exists( 'currency_pos', $pizzeria_settings ) || $pizzeria_settings['currency_pos'] == 'after' ) ) {
				echo $pizzeria_settings['currency'];
			}
			?>
		</p>
	<?php
	}

	/* Save custom meta boxes content */


	public function save_postdata( $post_id ) {
		if ( true === defined( 'DOING_AUTOSAVE' ) && true ===  constant( 'DOING_AUTOSAVE' ) ) {
			return;
		}
		if ( $this->post_type !== get_post_type( $post_id ) ) {
			return;
		}
		if ( false === current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( true === isset( $_POST['dessert_price'] ) ) {
			update_post_meta( $post_id, '_wp_pizzeria_price', $_POST['dessert_price'] );
		}
	}
}