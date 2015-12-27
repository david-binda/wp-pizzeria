<?php
/*
* Register custom taxonomy wp_pizzeria_ingredient for ability to add ingredients
* Ingredients are using WordPress tagging system
*/

class WP_Pizzeria_Pizza_Ingredients extends Tax_Factory {

	protected $taxonomy = 'wp_pizzeria_ingredient';
	protected $cpt = array( 'wp_pizzeria_pizza' );
	protected $rewrite = array( 'slug' => 'ingredient' );
	protected $category_images = 'wp_pizzeria_ingredient_images';

	protected function __construct() {

		parent::construct( $this );

		add_action( 'edit_term', array( $this, 'image_save' ), 10, 1 );
		add_action( 'create_term', array( $this, 'image_save' ), 10, 1 );
	}

	protected function get_labels() {
		//setup labels
		return array(
			'name'              => esc_html_x( 'Pizza ingredients', 'taxonomy general name' ),
			'singular_name'     => esc_html_x( 'Pizza ingredient', 'taxonomy singular name' ),
			'search_items'      => esc_html__( 'Search pizza ingredients' ),
			'all_items'         => esc_html__( 'All pizza ingredients' ),
			'parent_item'       => esc_html__( 'Parent pizza ingredient' ),
			'parent_item_colon' => esc_html__( 'Parent pizza ingredient:' ),
			'edit_item'         => esc_html__( 'Edit pizza ingredient' ),
			'update_item'       => esc_html__( 'Update pizza ingredient' ),
			'add_new_item'      => esc_html__( 'Add new pizza ingredient' ),
			'new_item_name'     => esc_html__( 'New pizza ingredient name' ),
			'menu_name'         => esc_html__( 'Pizza ingredients' ),
		);
	}

	//register custom post type using before declared labels
	public function register_taxonomy() {
		register_taxonomy(
			$this->get_taxonomy(),
			$this->get_cpt(),
			array(
				'hierarchical'          => false,
				'labels'                => $this->get_labels(),
				'show_ui'               => true,
				'update_count_callback' => '_update_post_term_count',
				'query_var'             => true,
				'rewrite'               => $this->get_rewrite()
			)
		);
	}

	public function image_add() {
		?>
		<div class="form-field">
		<label for="ingredient-image"><?php esc_html_e( 'Image', 'wp_pizzeria' ); ?></label>
		<input type="text" class="tag-image" name="ingredient-image" id="ingredient-image" value="" />

		<p><?php esc_html_e( 'The image is not prominent by default; however themes modified for use with WP Pizzeria plugin will use it.', 'wp_pizzeria' ); ?></p>
		</div><?php
	}

	public function image_edit( $taxonomy ) {
		?>
		<tr class="form-field">
		<th scope="row" valign="top">
			<label for="ingredient-image">Image</label>
		</th>
		<td>
			<?php
			$ingredient_images = maybe_unserialize( get_option( 'wp_pizzeria_ingredient_images' ) );
			$value             = '';
			if ( true === array_key_exists( $taxonomy->term_id, $ingredient_images ) ) {
				$value = $ingredient_images[ $taxonomy->term_id ];
			} ?>
			<input type="text" class="tag-image" name="ingredient-image" id="ingredient-image" value="<?php echo esc_attr( $value ) ?>" />
			<br />
			<span class="description"><?php esc_html_e( 'The image is not prominent by default; however themes modified for use with WP Pizzeria plugin will use it.', 'wp_pizzeria' ); ?></span>
		</td>
		</tr><?php
	}

	function image_save( $term_id ) {
		if ( isset( $_POST['ingredient-image'] ) ) {
			$this->get_category_images();
			$ingredient_images[ $term_id ] = absint( $_POST['ingredient-image'] );
			$this->set_category_images( $ingredient_images );
		}
	}

	/* WP_QUERY alter for filtering */
	public function filter_by_ingredient( $query ) {
		if ( ( true === is_post_type_archive( 'wp_pizzeria_pizza' ) || true === is_tax( 'wp_pizzeria_ingredient' ) ) && false === is_admin() ) {
			$query->query_vars['posts_per_page'] = - 1;
			if ( true === is_tax( 'wp_pizzeria_ingredient' ) ) {
				unset( $query->query_vars['wp_pizzeria_ingredient'] );
			}
			$query->set( 'tax_query', array(
				array(
					'taxonomy' => 'wp_pizzeria_ingredient',
					'field'    => 'slug',
					'terms'    => array( 'cheese', 'rajcata' ),
					'operator' => 'IN'
				)
			) );

			return;
		}
	}

	/* views */
	public function wp_pizzeria_ingredients_checkbox() {
		$ingredient_images = $this->get_category_images();
		$ingredients       = get_terms( 'wp_pizzeria_ingredient', array( 'hide_empty' => 0 ) );
		if ( false === empty( $ingredients ) ) {
			?>
			<div id="ingredient-selectbox">
			<?php
			foreach ( $ingredients as $ingredient ) {
				$link = get_term_link( intval( $ingredient->term_id ), 'wp_pizzeria_ingredient' );
				if ( true === is_wp_error( $link ) ) {
					return false;
				}
				?>
				<label for="<?php echo esc_attr( $ingredient->name ); ?>">
					<input type="checkbox" id="<?php echo esc_attr( $ingredient->name ); ?>" name="wp_pizzeria_ingredients[]" value="<?php echo esc_attr( $ingredient->term_id ); ?>"/>
					<?php if ( true === array_key_exists( $ingredient->term_id, $ingredient_images ) ) : ?>
						<img src="<?php echo esc_url( $ingredient_images[ $ingredient->term_id ] ); ?>" alt="<?php echo esc_attr( $ingredient->name ); ?>" />
					<?php endif;
					echo esc_html( $ingredient->name ); ?>
				</label>
			<?php } ?>
			</div><?php
		}
	}

}