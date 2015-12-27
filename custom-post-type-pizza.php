<?php
/*
* Register custom taxonomy wp_pizzeria_pizza for pizzas
*/
Class WP_Pizzeria_Pizza extends CPT_Factory {

	protected $post_type = 'wp_pizzeria_pizza';

	protected function __construct() {

		parent::construct( $this );

		add_action( 'wp_ajax_wp_pizzeria_add_ingredient', array( $this, 'add_ingredient_callback' ), 10, 0 );
		add_action( 'admin_head', array( $this, 'add_ingredient_javascript' ), 10, 0 );
	}

	public function register_post_type() {
		//setup labels
		$labels = array(
			'name'               => esc_html__( 'Pizzas', 'wp_pizzeria' ),
			'singular_name'      => esc_html__( 'Pizza', 'wp_pizzeria' ),
			'add_new'            => esc_html__( 'Add pizza', 'wp_pizzeria' ),
			'add_new_item'       => esc_html__( 'Add pizza', 'wp_pizzeria' ),
			'edit_item'          => esc_html__( 'Edit pizza', 'wp_pizzeria' ),
			'new_item'           => esc_html__( 'New pizza', 'wp_pizzeria' ),
			'all_items'          => esc_html__( 'Pizzas', 'wp_pizzeria' ),
			'view_item'          => esc_html__( 'View pizza', 'wp_pizzeria' ),
			'search_items'       => esc_html__( 'Search pizza', 'wp_pizzeria' ),
			'not_found'          => esc_html__( 'No pizzas found', 'wp_pizzeria' ),
			'not_found_in_trash' => esc_html__( 'No pizzas in the trash', 'wp_pizzeria' ),
			'parent_item_colon'  => '',
			'menu_name'          => esc_html( 'Pizzas', 'wp_pizzeria' )
		);
		//register custom post type using before declared labels
		register_post_type( 'wp_pizzeria_pizza', array(
			'labels'               => $labels,
			'public'               => true,
			'show_in_nav_menus'    => true,
			'show_ui'              => true,
			'capability_type'      => 'post',
			'has_archive'          => true,
			'rewrite'              => array( 'slug' => 'pizza', 'with_front' => true ),
			'query_var'            => false,
			'supports'             => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
			'taxonomies'           => array( 'wp_pizzeria_ingredient', 'wp_pizzeria_category' ),
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
			( isset( $post_id ) && $this->post_type === get_post_type( $post_id ) ) ||
			( true === isset( $_GET['post_type'] ) && $this->post_type === $_GET['post_type'] )
		) {
			remove_meta_box( 'tagsdiv-wp_pizzeria_ingredient', $this->post_type, 'side' );
			remove_meta_box( 'pageparentdiv', $this->post_type, 'side' );
			add_meta_box(
				'wp_pizzeria_tags_custom_box',
				esc_html__( 'Pizza ingredients', 'wp_pizzeria' ),
				array( $this, 'tags_inner_custom_box' ),
				$this->post_type,
				'side',
				'core'
			);
			add_meta_box(
				'wp_pizzeria_pizza_price_custom_box',
				esc_html__( 'Pizza price', 'wp_pizzeria' ),
				array( $this, 'price_inner_custom_box' ),
				$this->post_type,
				'side',
				'core'
			);
			add_meta_box(
				'wp_pizzeria_number_custom_box',
				esc_html__( 'Pizza menu number', 'wp_pizzeria' ),
				array( $this, 'number_inner_custom_box' ),
				$this->post_type,
				'side',
				'core'
			);
		}
	}

	public function tags_inner_custom_box( $post ) {
		?>
		<div id="wp_pizzeria_ingredient">
			<div class="ingredientdiv">
				<div class="tabs-panel">
					<ul id="wp_pizzeria_ingredientchecklist" class="form-no-clear">
						<?php
						$tags = get_terms( 'wp_pizzeria_ingredient', array( 'hide_empty' => 0 ) );
						foreach ( $tags as $key => $tag ) {
							$link = get_term_link( intval( $tag->term_id ), 'wp_pizzeria_ingredient' );
							if ( true === is_wp_error( $link ) ) {
								return false;
							}
							?>
							<li class="popular-category tag-ingredient">
								<label for="<?php echo $tag->name; ?>">
									<input type="checkbox" id="<?php echo esc_attr( $tag->name ); ?>" name="wp_pizzeria_ingredients[]" value="<?php echo esc_attr( $tag->term_id ); ?>"<?php echo checked( has_term( $tag->term_id, 'wp_pizzeria_ingredient' ), true ); ?>/>
									<?php echo $tag->name; ?>
								</label>
								<a class="edit-ingredient hide-if-js" href="./edit-tags.php?action=edit&taxonomy=wp_pizzeria_ingredient&tag_ID=<?php echo urlencode( $tag->term_id ); ?>&post_type=wp_pizzeria_pizza"><?php esc_html_e( 'Edit', 'wp_pizzeria' ); ?></a>
								<a class="add-ingredient-image hide-if-js" href="#"><?php esc_html_e( 'Add image', 'wp_pizzeria' ); ?></a>
							</li>
						<?php
						}
						?>
					</ul>
					<script type="text/javascript">
						jQuery(document).ready(function ($) {
							$('.tag-ingredient').live('mouseover mouseout', function () {
								$(this).children('a').toggle();
							});
						});
					</script>
				</div>
			</div>
			<div class="jaxtag">
				<div class="nojs-tags hide-if-js">
					<p>Add or remove tags</p>
					<textarea name="tax_input[wp_pizzeria_ingredient]" rows="3" cols="20" class="the-tags" id="tax-input-wp_pizzeria_ingredient"></textarea>
				</div>
				<div class="ajaxtag hide-if-no-js">
					<label class="screen-reader-text" for="new-tag-wp_pizzeria_ingredient"><?php esc_html_e( 'Pizza ingredients', 'wp_pizzeria' ); ?></label>

					<div class="taghint" style=""><?php esc_html_e( 'Add new pizza ingredient', 'wp_pizzeria' ); ?></div>
					<p>
						<input type="text" id="new-tag-wp_pizzeria_ingredient" name="newtag[wp_pizzeria_ingredient]" class="newtag form-input-tip" size="16" autocomplete="off" value="">
						<input type="button" class="button tagadd" value="<?php esc_attr_e( 'Add', 'wp_pizzeria' ); ?>" tabindex="3">
					</p>
				</div>
				<p class="howto"><?php esc_html_e( 'Separate ingredients with commas', 'wp_pizzeria' ); ?></p>
			</div>
			<div class="tagchecklist"></div>
		</div>
	<?php
	}

	public function price_inner_custom_box( $post ) {
		$prices = maybe_unserialize( get_post_meta( $post->ID, '_wp_pizzeria_prices', true ) );
		if ( false === is_array( $prices ) ) {
			$prices = array();
		}
		$pizzeria_settings = maybe_unserialize( get_option( 'wp_pizzeria_settings' ) );
		if ( false === is_array( $pizzeria_settings ) ) {
			$pizzeria_settings = array();
		}
		if ( is_array( $pizzeria_settings['sizes'] ) ) :
			foreach ( $pizzeria_settings['sizes'] as $key => $size ) :
				if ( 'primary' === $key ) {
					continue;
				}
				?>
				<p>
					<?php if ( $pizzeria_settings['sizes']['primary'] === $key ) {
						echo '<strong>';
					} ?>
					<label for="<?php echo esc_attr( $key ); ?>_price"><?php esc_html_e( 'Price for', 'wp_pizzeria' ); ?> <?php echo esc_html( $size ); ?>:</label>
					<?php if ( $pizzeria_settings['sizes']['primary'] === $key ) {
						echo '</strong>';
					} ?>
				</p>
				<p>
					<?php
					if ( true === array_key_exists( 'currency', $pizzeria_settings )
					     && true === array_key_exists( 'currency_pos', $pizzeria_settings )
					     && 'before' === $pizzeria_settings['currency_pos'] ) {
						echo esc_html( $pizzeria_settings['currency'] );
					}
					?>
					<input type="text" name="<?php echo esc_attr( $key ); ?>_price" value="<?php if ( true === array_key_exists( $key, $prices ) ) {
						echo esc_attr( $prices[ $key ] );
					} ?>" />
					<?php
					if ( true === array_key_exists( 'currency', $pizzeria_settings )
					     && ( false === array_key_exists( 'currency_pos', $pizzeria_settings )
					          || 'after' === $pizzeria_settings['currency_pos'] )
					) {
						echo esc_html( $pizzeria_settings['currency'] );
					}
					?>
				</p>
			<?php endforeach;
		endif; ?>
	<?php
	}

	function save_postdata( $post_id ) {

		if ( false === $this->can_save( $post_id ) ) {
			return false;
		}

		if ( true === defined( 'DOING_AJAX' ) && true === constant( 'DOING_AJAX' ) ) {
			return;
		}

		$pizzeria_settings = $this::get_pizzeria_settings();

		$prices = array();
		if ( true === array_key_exists( 'sizes', $pizzeria_settings ) ) {
			foreach ( $pizzeria_settings['sizes'] as $key => $size ):
				if ( $key == 'primary' ) {
					continue;
				}
				if ( isset( $_POST[ $key . '_price' ] ) ) {
					$prices[ $key ] = sanitize_text_field( $_POST[ $key . '_price' ] );
				}
			endforeach;
		}
		update_post_meta( $post_id, '_wp_pizzeria_prices', maybe_serialize( $prices ) );
		if ( true === array_key_exists( 'sizes', $pizzeria_settings )
		     && true === array_key_exists( 'primary', $pizzeria_settings['sizes'] )
		     && true === isset( $_POST[ $pizzeria_settings['sizes']['primary'] . '_price' ] )
		) {
			update_post_meta( $post_id, '_wp_pizzeria_price', sanitize_text_field( $_POST[ $pizzeria_settings['sizes']['primary'] . '_price' ] ) );
		}
		if ( true === isset( $_POST['wp_pizzeria_number'] ) ) {
			global $wpdb;
			$wpdb->update( $wpdb->posts, array( 'menu_order' => absint( $_POST['wp_pizzeria_number'] ) ), array( 'ID' => $post_id ), array( '%d' ), array( '%d' ) );
			//todo: invalidate cache
		}
		if ( true === isset( $_POST['wp_pizzeria_ingredients'] ) ) {
			$term_ids = array_map( 'intval', $_POST['wp_pizzeria_ingredients'] );
			$term_ids = array_unique( $term_ids );
			wp_set_object_terms( $post_id, $term_ids, 'wp_pizzeria_ingredient' );
		}
	}

	/*
	* Add ajax for ability to add new ingredients on edit/add pizza page
	*/

	function add_ingredient_javascript() {
		global $typenow, $pagenow;
		if ( true === is_admin()
		     && true === in_array( $pagenow, array( 'post-new.php', 'post.php' ), true )
		     && ( 'wp_pizzeria_pizza' === $typenow
		          || ( true === isset( $_GET['post_type'] ) && 'wp_pizzeria_pizza' === $_GET['post_type'] )
		     )
		) {
			global $post;
			wp_enqueue_script( 'wp_pizzeria_addIngredient', plugin_dir_url( __FILE__ ) . 'js/ajax-add-ingredient.js', array( 'jquery' ) );
			wp_localize_script( 'wp_pizzeria_addIngredient', 'wp_pizzeria_addIngredient', array(
					'addIngredientNonce' => wp_create_nonce( 'wp_pizzeria_add_ingredient-nonce' ),
					'postID'             => $post->ID
				)
			);
		}
	}

	function add_ingredient_callback() {
		if ( false === wp_verify_nonce( $_POST['addIngredientNonce'], 'wp_pizzeria_add_ingredient-nonce' ) ) {
			die ( 'Busted!' );
		}
		$tags = explode( ',', $_POST['tag'] );
		$tags = array_map( "trim", $tags );
		wp_set_object_terms( absint( $_POST['postID'] ), array_map( 'sanitize_text_field', $tags ), 'wp_pizzeria_ingredient', true );
		die();
	}

	function edit_columns( $columns ) {

		$columns           = array(
			'cb'          => '<input type="checkbox" />',
			'menu_number' => esc_html__( '#', 'wp_pizzeria' ),
			'title'       => esc_html__( 'Title' ),
			'category'    => esc_html__( 'Category', 'wp_pizzeria' ),
			'ingredients' => esc_html__( 'Ingredients', 'wp_pizzeria' ),
		);
		$pizzeria_settings = maybe_unserialize( get_option( 'wp_pizzeria_settings' ) );
		if ( false === is_array( $pizzeria_settings ) ) {
			$pizzeria_settings = array();
		}
		if ( true === is_array( $pizzeria_settings['sizes'] ) ) {
			foreach ( $pizzeria_settings['sizes'] as $key => $size ) {
				if ( $key == 'primary' ) {
					continue;
				}
				$columns[ $size ] = $size;
			}

		}
		$columns['date'] = esc_html__( 'Date', 'wp_pizzeria' );

		return $columns;
	}

	function manage_columns( $column, $post_id ) {
		global $post;
		switch ( $column ) {
			case 'menu_number' :
				global $wpdb;
				$menu_id = $wpdb->get_var( $wpdb->prepare( "SELECT menu_order FROM $wpdb->posts WHERE ID = %d ", intval( $post_id ) ) );
				//TODO: check return value
				echo intval( $menu_id );
				break;
			case 'category' :
				$terms = get_the_terms( $post_id, 'wp_pizzeria_category' );
				if ( false === empty( $terms ) ) {
					$out = array();
					foreach ( $terms as $term ) {
						$out[] = sprintf( '<a href="%s">%s</a>',
							esc_url( add_query_arg( array( 'post_type'            => rawurlencode( $post->post_type ),
							                               'wp_pizzeria_category' => rawurlencode( $term->slug )
									), 'edit.php' ) ),
							esc_html( sanitize_term_field( 'name', $term->name, $term->term_id, 'wp_pizzeria_category', 'display' ) )
						);
					}
					echo join( ', ', $out );
				} else {
					esc_html_e( 'No Categories', 'wp_pizzeria' );
				}
				break;

			case 'ingredients' :
				$terms = get_the_terms( $post_id, 'wp_pizzeria_ingredient' );
				if ( false === empty( $terms ) ) {
					$out = array();
					foreach ( $terms as $term ) {
						$out[] = sprintf( '<a href="%s">%s</a>',
							esc_url( add_query_arg( array( 'post_type'              => rawurlencode( $post->post_type ),
							                               'wp_pizzeria_ingredient' => rawurlencode( $term->slug )
									), 'edit.php' ) ),
							esc_html( sanitize_term_field( 'name', $term->name, $term->term_id, 'wp_pizzeria_ingredient', 'display' ) )
						);
					}
					echo join( ', ', $out );
				} else {
					esc_html_e( 'No Ingredients', 'wp_pizzeria' );
				}
				break;
			default :
				break;
		}
		$pizzeria_settings = maybe_unserialize( get_option( 'wp_pizzeria_settings' ) );
		if ( false === is_array( $pizzeria_settings ) ) {
			$pizzeria_settings = array();
		}
		if ( true === is_array( $pizzeria_settings['sizes'] )
		     && true === array_key_exists( $column, $pizzeria_settings['sizes'] )
		) {
			$prices = maybe_unserialize( get_post_meta( $post->ID, '_wp_pizzeria_prices', true ) );
			if ( false === is_array( $prices ) ) {
				$prices = array();
			}
			if ( true === array_key_exists( $column, $prices ) ) {
				if ( true === array_key_exists( 'currency', $pizzeria_settings )
				     && true === array_key_exists( 'currency_pos', $pizzeria_settings )
				     && 'before' === $pizzeria_settings['currency_pos']
				) {
					echo esc_html( $pizzeria_settings['currency'] );
				}
				echo $prices[ $column ];
				if ( true === array_key_exists( 'currency', $pizzeria_settings )
				     && ( false === array_key_exists( 'currency_pos', $pizzeria_settings )
				          || 'after' === $pizzeria_settings['currency_pos'] )
				) {
					echo esc_html( $pizzeria_settings['currency'] );
				}
			}
		}
	}
}