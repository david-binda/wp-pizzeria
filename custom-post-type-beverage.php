<?php
/*
* Register custom taxonomy wp_pizzeria_pizza for pizzas
*/

Class WP_Pizzeria_Bevarage {

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
		add_filter( 'manage_edit-wp_pizzeria_beverage_columns', array( $this, 'edit_columns' ), 10, 1 );
		add_action( 'manage_wp_pizzeria_beverage_posts_custom_column', array( $this, 'columns' ), 10, 2 );
	}

	private function __clone() {
	}

	private function __wakeup() {
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
		register_post_type( 'wp_pizzeria_beverage', array(
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
		$post_type = 'wp_pizzeria_beverage';
		if ( true === isset( $_GET['post'] ) ) {
			$post_id = absint( $_GET['post'] );
		} elseif ( true === isset( $_POST['post_ID'] ) ) {
			$post_id = absint( $_POST['post_ID'] );
		}
		if (
			( true === isset( $post_id ) && $post_type === get_post_type( $post_id ) ) ||
			( true === isset( $_GET['post_type'] ) && $post_type === $_GET['post_type'] )
		) {
			remove_meta_box( 'pageparentdiv', 'wp_pizzeria_beverage', 'side' );
			add_meta_box(
				'wp_pizzeria_beverage_price_custom_box',
				esc_html__( 'Beverage price', 'wp_pizzeria' ),
				array( $this, 'price_inner_custom_box' ),
				$post_type,
				'side',
				'core'
			);
			add_meta_box(
				'wp_pizzeria_number_custom_box',
				esc_html__( 'Beverage menu number', 'wp_pizzeria' ),
				'wp_pizzeria_number_inner_custom_box',
				$post_type,
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
		$pizzeria_settings = (array) maybe_unserialize( get_option( 'wp_pizzeria_settings' ) );
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
		if ( true === defined( 'DOING_AUTOSAVE' ) && true === constant( 'DOING_AUTOSAVE' ) ) {
			return;
		}
		if ( 'wp_pizzeria_beverage' !== get_post_type( $post_id ) ) {
			return;
		}
		if ( false === current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( true === isset( $_POST['beverage_price'] ) ) {
			update_post_meta( $post_id, '_wp_pizzeria_price', $_POST['beverage_price'] );
		}
	}

	/* Show beverage counts in dashboard overview widget */

	public function add_counts() {
		if ( false === post_type_exists( 'wp_pizzeria_beverage' ) ) {
			return;
		}

		$num_posts = wp_count_posts( 'wp_pizzeria_beverage' );

		$num  = number_format_i18n( $num_posts->publish );
		$text = _n( 'Beverage', 'Beverages', intval( $num_posts->publish ) );
		if ( current_user_can( 'edit_posts' ) ) {
			$num  = sprintf( "<a href='edit.php?post_type=wp_pizzeria_beverage'>%d</a>", absint( $num ) );
			$text = sprintf( "<a href='edit.php?post_type=wp_pizzeria_beverage'>%s</a>", esc_html( $text ) );
		}
		echo sprintf( '<td class="first b b-wp_pizzeria_beverage">%s</td>', $num ); //num is already a properly escaped HTML
		echo sprintf( '<td class="t wp_pizzeria_beverage">%s</td>', $text ); //text is already a properly escaped HTML

		echo '</tr>';

		if ( $num_posts->pending > 0 ) {
			$num  = number_format_i18n( $num_posts->pending );
			$text = _n( 'Beverage awaiting moderation', 'Beverages awaiting moderation', absint( $num_posts->pending ) );
			if ( true === current_user_can( 'edit_posts' ) ) {
				$num  = sprintf( "<a href='edit.php?post_status=pending&post_type=wp_pizzeria_beverage'>%d</a>", absint( $num ) );
				$text = sprintf( "<a href='edit.php?post_status=pending&post_type=wp_pizzeria_beverage'>%s</a>", esc_html( $text ) );
			}
			echo sprintf( '<td class="first b b-wp_pizzeria_beverage">%s</td>', $num ); //num is already properly escaped HTML
			echo sprintf( '<td class="t wp_pizzeria_beverage">%s</td>', $text ); //text is already properly escaped HTML

			echo '</tr>';
		}
	}

	public function edit_columns( $columns ) {

		$columns = array(
			'cb'          => '<input type="checkbox" />',
			'menu_number' => esc_html__( '#', 'wp_pizzeria' ),
			'title'       => esc_html__( 'Title' ), //default WordPress Title translation
			'category'    => esc_html__( 'Category', 'wp_pizzeria' ),
			'price'       => esc_html__( 'Price', 'wp_pizzeria' ),
			'date'        => esc_html__( 'Date' ) //default WordPpress Date translation
		);

		return $columns;
	}

	public function columns( $column, $post_id ) {
		global $post;
		switch ( $column ) {
			case 'menu_number' :
				global $wpdb;
				$menu_id = $wpdb->get_var( $wpdb->prepare( "SELECT menu_order FROM $wpdb->posts WHERE ID = %d ", $post_id ) );
				echo absint( $menu_id );
				break;
			case 'category' :
				$terms = get_the_terms( $post_id, 'wp_pizzeria_beverage_category' );
				if ( false === empty( $terms ) ) {
					$out = array();
					foreach ( $terms as $term ) {
						$out[] = sprintf( '<a href="%s">%s</a>',
							esc_url( add_query_arg( array(
								'post_type'                     => $post->post_type,
								'wp_pizzeria_beverage_category' => $term->slug
							), 'edit.php' ) ),
							esc_html( sanitize_term_field( 'name', $term->name, $term->term_id, 'wp_pizzeria_beverage_category', 'display' ) )
						);
					}
					echo join( ', ', $out );
				} else {
					esc_html_e( 'No Categories', 'wp_pizzeria' );
				}
				break;

			case 'price' :
				$pizzeria_settings = maybe_unserialize( get_option( 'wp_pizzeria_settings' ) );
				if ( false !== get_post_meta( $post_id, '_wp_pizzeria_price', true ) ) {
					if ( true === array_key_exists( 'currency', $pizzeria_settings )
					     && true === array_key_exists( 'currency_pos', $pizzeria_settings ) && 'before' === $pizzeria_settings['currency_pos']
					) {
						echo esc_html( $pizzeria_settings['currency'] );
					}
					echo get_post_meta( $post_id, '_wp_pizzeria_price', true );
					if ( true === array_key_exists( 'currency', $pizzeria_settings )
					     && ( false === array_key_exists( 'currency_pos', $pizzeria_settings ) || 'after' === $pizzeria_settings['currency_pos'] )
					) {
						echo esc_html( $pizzeria_settings['currency'] );
					}
				}
				break;
			default :
				break;
		}
	}
}
