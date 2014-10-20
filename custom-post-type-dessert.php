<?php
/*
* Register custom taxonomy wp_pizzeria_pizza for pizzas
*/
Class WP_Pizzeria_Desert {

	protected $post_type = 'wp_pizzeria_desert';

	public static function getInstance() {
		static $instance = null;
		if ( null === $instance ) {
			$instance = new static();
		}

		return $instance;
	}

	protected function __construct() {
		add_action( 'init', array( $this, 'register_post_type' ), 10, 0 );

		add_action( 'save_post', array( $this, 'save_postdata' ), 10, 1 );
		add_action( 'right_now_content_table_end', array( $this, 'add_counts' ), 10, 0 );
		add_filter( "manage_edit-{$this->post_type}_columns", array( $this, 'edit_columns' ), 10, 1 );
		add_action( "manage_{$this->post_type}_posts_custom_column", array( $this, 'manage_columns' ), 10, 2 );
	}

	private function __clone() {
	}

	private function __wakeup() {
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

	/* Show desserts counts in dashboard overview widget */

	public function add_counts() {
		if ( false === post_type_exists( $this->post_type ) ) {
			return;
		}

		$num_posts = wp_count_posts( 'wp_pizzeria_dessert' );
		$num       = number_format_i18n( $num_posts->publish );
		$text      = _n( 'Dessert', 'Desserts', intval( $num_posts->publish ) );
		if ( current_user_can( 'edit_posts' ) ) {
			$num  = "<a href='edit.php?post_type=wp_pizzeria_dessert'>$num</a>";
			$text = "<a href='edit.php?post_type=wp_pizzeria_dessert'>$text</a>";
		}
		echo '<td class="first b b-wp_pizzeria_dessert">' . $num . '</td>';
		echo '<td class="t wp_pizzeria_dessert">' . $text . '</td>';

		echo '</tr>';

		if ( $num_posts->pending > 0 ) {
			$num  = number_format_i18n( $num_posts->pending );
			$text = _n( 'Dessert awaiting moderation', 'Desserts awaiting moderation', intval( $num_posts->pending ) );
			if ( current_user_can( 'edit_posts' ) ) {
				$num  = "<a href='edit.php?post_status=pending&post_type=wp_pizzeria_dessert'>$num</a>";
				$text = "<a href='edit.php?post_status=pending&post_type=wp_pizzeria_dessert'>$text</a>";
			}
			echo '<td class="first b b-wp_pizzeria_dessert">' . $num . '</td>';
			echo '<td class="t wp_pizzeria_dessert">' . $text . '</td>';

			echo '</tr>';
		}
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
		$post = get_post();
		switch ( $column ) {
			case 'menu_number' :
				global $wpdb;
				$menu_id = $wpdb->get_var( $wpdb->prepare( "SELECT menu_order FROM $wpdb->posts WHERE ID = %d ", $post_id ) );
				echo $menu_id;
				break;
			case 'category' :
				$terms = get_the_terms( $post_id, 'wp_pizzeria_dessert_category' );
				if ( ! empty( $terms ) ) {
					$out = array();
					foreach ( $terms as $term ) {
						$out[] = sprintf( '<a href="%s">%s</a>',
							esc_url( add_query_arg( array( 'post_type'                    => $post->post_type,
							                               'wp_pizzeria_dessert_category' => $term->slug
									), 'edit.php' ) ),
							esc_html( sanitize_term_field( 'name', $term->name, $term->term_id, 'wp_pizzeria_dessert_category', 'display' ) )
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