<?php

class WP_Pizzeria_Pizza_Categories extends Tax_Factory {

	protected $taxonomy = 'wp_pizzeria_category';
	protected $cpt = array( 'wp_pizzeria_pizza' );
	protected $rewrite = array( 'slug' => 'pizza-category' );
	protected $category_images = 'wp_pizzeria_pizza_category_images';

	protected function __construct() {

		parent::construct( $this );

		add_action('edit_term', array( $this, 'image_save' ), 10, 1 );
		add_action('create_term', array( $this, 'image_save' ), 10, 1 );
	}

	protected function get_labels() {
		//setup labels
		return array(
			'name' => _x( 'Pizza categories', 'taxonomy general name', 'wp_pizzeria' ),
			'singular_name' => _x( 'Pizza categories', 'taxonomy singular name', 'wp_pizzeria' ),
			'search_items' =>  __( 'Search pizza categories', 'wp_pizzeria' ),
			'all_items' => __( 'All pizza categories', 'wp_pizzeria' ),
			'parent_item' => __( 'Parent pizza category', 'wp_pizzeria' ),
			'parent_item_colon' => __( 'Parent pizza category:', 'wp_pizzeria' ),
			'edit_item' => __( 'Edit pizza category', 'wp_pizzeria' ),
			'update_item' => __( 'Update pizza category', 'wp_pizzeria' ),
			'add_new_item' => __( 'Add new pizza category', 'wp_pizzeria' ),
			'new_item_name' => __( 'New pizza category name', 'wp_pizzeria' ),
			'menu_name' => __( 'Pizza categories', 'wp_pizzeria' ),
		);
	}

	public function image_add() { ?>
		<div class="form-field">
		<label for="pizza_category-image"><?php esc_html_e('Image', 'wp_pizzeria'); ?></label>
		<input type="text" class="tag-image" name="pizza_category-image" id="pizza_category-image" value="" />
		<p><?php esc_html_e('The image is not prominent by default; however themes modified for use with WP Pizzeria plugin will use it.', 'wp_pizzeria'); ?></p>
		</div><?php
	}

	public function image_edit( $taxonomy ) { ?>
		<tr class="form-field">
		<th scope="row" valign="top">
			<label for="pizza_category-image">Image</label>
		</th>
		<td>
			<?php $category_images = $this->get_category_images(); ?>
			<input type="text" class="tag-image" name="pizza_category-image" id="pizza_category-image" value="<?php echo esc_attr( $category_images[$taxonomy->term_id] ); ?>" />
			<br />
			<span class="description"><?php esc_html_e('The image is not prominent by default; however themes modified for use with WP Pizzeria plugin will use it.', 'wp_pizzeria'); ?></span>
		</td>
		</tr><?php
	}

	function image_save($term_id){
		if( true === isset( $_POST['pizza_category-image'] ) ) {
			$category_images = $this->get_category_images();
			$category_images[$term_id] = $_POST['pizza_category-image'];
			$this->set_category_images( $category_images );
		}
	}
}