<?php
/*
* Register custom taxonomy wp_pizzeria_category for pizzas categorization
*/

class WP_Pizzeria_Beverage_Categories extends Tax_Factory {

	protected $taxonomy = 'wp_pizzeria_beverage_category';
	protected $cpt = array( 'wp_pizzeria_beverage' );
	protected $rewrite = array( 'slug' => 'beverage-category' );
	protected $category_images = 'wp_pizzeria_beverage_category_images';

	protected function __construct() {

		parent::construct( $this );

		add_action( 'edit_term', array( $this, 'image_save' ), 10, 1 );
		add_action( 'create_term', array( $this, 'image_save' ), 10, 1 );
	}

	protected function get_labels() {
		return array(
			'name'              => _x( 'Beverage categories', 'taxonomy general name', 'wp_pizzeria' ),
			'singular_name'     => _x( 'Beverage categories', 'taxonomy singular name', 'wp_pizzeria' ),
			'search_items'      => __( 'Search Beverage categories', 'wp_pizzeria' ),
			'all_items'         => __( 'All Beverage categories', 'wp_pizzeria' ),
			'parent_item'       => __( 'Parent Beverage category', 'wp_pizzeria' ),
			'parent_item_colon' => __( 'Parent Beverage category:', 'wp_pizzeria' ),
			'edit_item'         => __( 'Edit Beverage category', 'wp_pizzeria' ),
			'update_item'       => __( 'Update Beverage category', 'wp_pizzeria' ),
			'add_new_item'      => __( 'Add New Beverage category', 'wp_pizzeria' ),
			'new_item_name'     => __( 'New Beverage category name', 'wp_pizzeria' ),
			'menu_name'         => __( 'Beverage categories', 'wp_pizzeria' ),
		);
	}

	public function image_add() {
		?>
		<div class="form-field">
		<label for="beverage_category-image"><?php esc_html_e( 'Image', 'wp_pizzeria' ); ?></label>
		<input type="text" class="tag-image" name="beverage_category-image" id="beverage_category-image" value="" />

		<p><?php esc_html_e( 'The image is not prominent by default; however themes modified for use with WP Pizzeria plugin will use it.', 'wp_pizzeria' ); ?></p>
		</div><?php
	}

	public function image_edit( $taxonomy ) {
		?>
		<tr class="form-field">
		<th scope="row" valign="top">
			<label for="beverage_category-image">Image</label>
		</th>
		<td>
			<?php $category_images = $this->get_category_images(); ?>
			<input type="text" class="tag-image" name="beverage_category-image" id="beverage_category-image" value="<?php echo esc_attr( $category_images[ $taxonomy->term_id ] ); ?>" />
			<br />
			<span class="description"><?php esc_html_e( 'The image is not prominent by default; however themes modified for use with WP Pizzeria plugin will use it.', 'wp_pizzeria' ); ?></span>
		</td>
		</tr><?php
	}

	function image_save( $term_id ) {
		if ( isset( $_POST['beverage_category-image'] ) ) {
			$category_images = $category_images = $this->get_category_images();
			$category_images[ $term_id ] = $_POST['beverage_category-image'];
			$this->set_category_images( $category_images );
		}
	}

}