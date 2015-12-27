<?php

/*
* Register custom taxonomy wp_pizzeria_category for pizzas categorization
*/

class WP_Pizzeria_Dessert_Categories extends Tax_Factory {

	protected $taxonomy = 'wp_pizzeria_dessert_category';
	protected $cpt = array( 'wp_pizzeria_dessert' );
	protected $rewrite = array( 'slug' => 'dessert-category' );
	protected $category_images = 'wp_pizzeria_dessert_category_images';

	protected function __construct() {

		parent::construct( $this );

		add_action( 'edit_term', array( $this, 'image_save' ), 10, 1 );
		add_action( 'create_term', array( $this, 'image_save' ), 10, 1 );
	}

	protected function get_labels() {
		//setup labels
		return array(
			'name'              => esc_html_x( 'Dessert categories', 'taxonomy general name', 'wp_pizzeria' ),
			'singular_name'     => esc_html_x( 'Dessert categories', 'taxonomy singular name', 'wp_pizzeria' ),
			'search_items'      => esc_html__( 'Search Dessert categories', 'wp_pizzeria' ),
			'all_items'         => esc_html__( 'All Dessert categories', 'wp_pizzeria' ),
			'parent_item'       => esc_html__( 'Parent Dessert category', 'wp_pizzeria' ),
			'parent_item_colon' => esc_html__( 'Parent Dessert category:', 'wp_pizzeria' ),
			'edit_item'         => esc_html__( 'Edit Dessert category', 'wp_pizzeria' ),
			'update_item'       => esc_html__( 'Update Dessert category', 'wp_pizzeria' ),
			'add_new_item'      => esc_html__( 'Add New Dessert category', 'wp_pizzeria' ),
			'new_item_name'     => esc_html__( 'New Dessert category name', 'wp_pizzeria' ),
			'menu_name'         => esc_html__( 'Dessert categories', 'wp_pizzeria' ),
		);
	}


	public function image_add() {
		?>
		<div class="form-field">
		<label for="dessert_category-image"><?php esc_html_e( 'Image', 'wp_pizzeria' ); ?></label>
		<input type="text" class="tag-image" name="dessert_category-image" id="dessert_category-image" value="" />

		<p><?php esc_html_e( 'The image is not prominent by default; however themes modified for use with WP Pizzeria plugin will use it.', 'wp_pizzeria' ); ?></p>
		</div><?php
	}

	public function image_edit( $taxonomy ) {
		?>
		<tr class="form-field">
		<th scope="row" valign="top">
			<label for="dessert_category-image">Image</label>
		</th>
		<td>
			<?php $category_images = $this->get_category_images(); ?>
			<input type="text" class="tag-image" name="dessert_category-image" id="dessert_category-image" value="<?php echo esc_attr( $category_images[ $taxonomy->term_id ] ); ?>" />
			<br />
			<span class="description"><?php esc_html_e( 'The image is not prominent by default; however themes modified for use with WP Pizzeria plugin will use it.', 'wp_pizzeria' ); ?></span>
		</td>
		</tr><?php
	}

	public function image_save( $term_id ) {
		if ( true === isset( $_POST['dessert_category-image'] ) ) {
			$category_images = $this->get_category_images();
			$category_images[ $term_id ] = absint( $_POST['dessert_category-image'] );
			$this->set_category_images( $category_images );
		}
	}

}