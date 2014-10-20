<?php
/*
* Register custom taxonomy wp_pizzeria_pizza for pizzas
*/

Class WP_Pizzeria_Bevarage extends CPT_Factory {

	protected $post_type = 'wp_pizzeria_beverage';

	protected function __construct() {
		parent::construct( $this );
	}

	public function register_post_type() {
		//setup labels
		$labels = array(
			'name'               => esc_html__( 'Beverages', 'wp_pizzeria' ),
			'singular_name'      => esc_html__( 'Beverage', 'wp_pizzeria' ),
			'add_new'            => esc_html__( 'Add Beverage', 'wp_pizzeria' ),
			'add_new_item'       => esc_html__( 'Add Beverage', 'wp_pizzeria' ),
			'edit_item'          => esc_html__( 'Edit Beverage', 'wp_pizzeria' ),
			'new_item'           => esc_html__( 'New Beverage', 'wp_pizzeria' ),
			'all_items'          => esc_html__( 'Beverages', 'wp_pizzeria' ),
			'view_item'          => esc_html__( 'View Beverages', 'wp_pizzeria' ),
			'search_items'       => esc_html__( 'Search Beverages', 'wp_pizzeria' ),
			'not_found'          => esc_html__( 'No beverages found', 'wp_pizzeria' ),
			'not_found_in_trash' => esc_html__( 'No beverages in the trash', 'wp_pizzeria' ),
			'parent_item_colon'  => '',
			'menu_name'          => esc_html__( 'Beverages', 'wp_pizzeria' )
		);
		//register custom post type using before declared labels
		register_post_type( $this->post_type, array(
			'labels'               => $labels,
			'public'               => true,
			'show_in_nav_menus'    => true,
			'show_ui'              => true,
			'capability_type'      => 'post',
			'has_archive'          => true,
			'rewrite'              => array( 'slug' => 'beverage', 'with_front' => true ),
			'query_var'            => false,
			'supports'             => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
			'taxonomies'           => array( 'wp_pizzeria_beverage_category' ),
			'register_meta_box_cb' => array( $this, 'custom_box' )
		) );
	}

	/* Custom Meta boxes */

	public function custom_box() {
		if ( true === isset( $_GET['post'] ) ) {
			$post_id = absint( $_GET['post'] );
		} elseif ( true === isset( $_POST['post_ID'] ) ) {
			$post_id = absint( $_POST['post_ID'] );
		}
		if (
			( true === isset( $post_id ) && $this->post_type === get_post_type( $post_id ) ) ||
			( true === isset( $_GET['post_type'] ) && $this->post_type === $_GET['post_type'] )
		) {
			remove_meta_box( 'pageparentdiv', $this->post_type, 'side' );
			add_meta_box(
				'wp_pizzeria_beverage_price_custom_box',
				esc_html__( 'Beverage price', 'wp_pizzeria' ),
				array( $this, 'price_inner_custom_box' ),
				$this->post_type,
				'side',
				'core'
			);
			add_meta_box(
				'wp_pizzeria_number_custom_box',
				esc_html__( 'Beverage menu number', 'wp_pizzeria' ),
				array( $this, 'number_inner_custom_box' ),
				$this->post_type,
				'side',
				'core'
			);
		}
	}


	public function price_inner_custom_box( $post ) {
		$price = get_post_meta( $post->ID, '_wp_pizzeria_price', true );
		if ( false === $price ) {
			$price = '';
		}
		$pizzeria_settings = $this::get_pizzeria_settings();
		?>
		<p>
			<label for="beverage_price"><?php _e( 'Price', 'wp_pizzeria' ); ?></label>
			<?php if ( true === array_key_exists( 'currency', $pizzeria_settings )
			           && true === array_key_exists( 'currency_pos', $pizzeria_settings ) && 'before' === $pizzeria_settings['currency_pos']
			) {
				echo esc_html( $pizzeria_settings['currency'] );
			} ?>
			<input type="text" id="beverage_price" name="beverage_price" value="<?php echo esc_attr( $price ); ?>" />
			<?php
			if ( true === array_key_exists( 'currency', $pizzeria_settings )
			     && ( false === array_key_exists( 'currency_pos', $pizzeria_settings ) || 'after' === $pizzeria_settings['currency_pos'] )
			) {
				echo esc_html( $pizzeria_settings['currency'] );
			}
			?>
		</p>
	<?php
	}

	/* Save custom meta boxes content */

	public function save_postdata( $post_id ) {
		if ( false === $this->can_save( $post_id ) ) {
			return false;
		}

		if ( true === isset( $_POST['beverage_price'] ) ) {
			update_post_meta( $post_id, '_wp_pizzeria_price', intval( $_POST['beverage_price'] ) );
		}
	}

}
