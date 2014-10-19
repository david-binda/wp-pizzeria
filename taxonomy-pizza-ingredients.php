<?php
/*
* Register custom taxonomy wp_pizzeria_ingredient for ability to add ingredients
* Ingredients are using WordPress tagging system
*/
add_action('init', 'wp_pizzeria_ingredients_custom_taxonomy');

function wp_pizzeria_ingredients_custom_taxonomy() {
	//setup labels
	$labels_taxonomy = array(
		'name' => _x( 'Pizza ingredients', 'taxonomy general name' ),
		'singular_name' => _x( 'Pizza ingredient', 'taxonomy singular name' ),
		'search_items' =>  __( 'Search pizza ingredients' ),
		'all_items' => __( 'All pizza ingredients' ),
		'parent_item' => __( 'Parent pizza ingredient' ),
		'parent_item_colon' => __( 'Parent pizza ingredient:' ),
		'edit_item' => __( 'Edit pizza ingredient' ), 
		'update_item' => __( 'Update pizza ingredient' ),
		'add_new_item' => __( 'Add new pizza ingredient' ),
		'new_item_name' => __( 'New pizza ingredient name' ),
		'menu_name' => __( 'Pizza ingredients' ),
	); 	
	//register custom post type using before declared labels
	register_taxonomy(
		'wp_pizzeria_ingredient',
		array('wp_pizzeria_pizza'), 
		array(
			'hierarchical' => false,
			'labels' => $labels_taxonomy,
			'show_ui' => true,
			'update_count_callback' => '_update_post_term_count',
			'query_var' => true,
			'rewrite' => array( 'slug' => 'ingredient' )
		)
	);
}

add_action('wp_pizzeria_ingredient_add_form_fields', 'wp_pizzeria_ingredient_image_add');

function wp_pizzeria_ingredient_image_add($taxonomy) { ?>
<div class="form-field">
	<label for="ingredient-image"><?php _e('Image', 'wp_pizzeria'); ?></label>
	<input type="text" class="tag-image" name="ingredient-image" id="ingredient-image" value="" />	
	<p><?php _e('The image is not prominent by default; however themes modified for use with WP Pizzeria plugin will use it.', 'wp_pizzeria'); ?></p>
</div><?php  
}

add_action('wp_pizzeria_ingredient_edit_form_fields','wp_pizzeria_ingredient_image_edit');

function wp_pizzeria_ingredient_image_edit($taxonomy) { ?>
<tr class="form-field">
	<th scope="row" valign="top">
		<label for="ingredient-image">Image</label>
	</th>
	<td>
		<?php 
			$ingredient_images = maybe_unserialize( get_option( 'wp_pizzeria_ingredient_images' ) );
			$value = '';
			if ( array_key_exists($taxonomy->term_id, $ingredient_images) ) 
				$value = $ingredient_images[$taxonomy->term_id]; ?> 
		<input type="text" class="tag-image" name="ingredient-image" id="ingredient-image" value="<?php echo $value ?>" />
		<br />
		<span class="description"><?php _e('The image is not prominent by default; however themes modified for use with WP Pizzeria plugin will use it.', 'wp_pizzeria'); ?></span>
	</td>
</tr><?php
}

add_action('edit_term','wp_pizzeria_ingredient_image_save');
add_action('create_term','wp_pizzeria_ingredient_image_save');
function wp_pizzeria_ingredient_image_save($term_id){
    if(isset($_POST['ingredient-image'])){
    	$ingredient_images = maybe_unserialize( get_option( 'wp_pizzeria_ingredient_images' ) );
    	if ( !is_array( $ingredient_images ) || !$ingredient_images )
    		$ingredient_images = array();
    	$ingredient_images[$term_id] = $_POST['ingredient-image'];
		update_option( 'wp_pizzeria_ingredient_images', maybe_serialize( $ingredient_images ) );
    }
}

/* WP_QUERY alter for filtering */

function wp_pizzeria_filter_by_ingredient( $query ) {
	if ( ( is_post_type_archive( 'wp_pizzeria_pizza' ) || is_tax( 'wp_pizzeria_ingredient' ) ) && !is_admin() ){
		$query->query_vars['posts_per_page'] = -1;
		if ( is_tax( 'wp_pizzeria_ingredient' ) )
			unset($query->query_vars['wp_pizzeria_ingredient']);
		$query->set('tax_query' , array(
			array(
				'taxonomy' => 'wp_pizzeria_ingredient',
				'field' => 'slug',
				'terms' => array('cheese', 'rajcata'),
				'operator' => 'IN'
			)
		));
		return;
    }
}
//add_action('pre_get_posts', 'wp_pizzeria_filter_by_ingredient', 1);

/* views */
function wp_pizzeria_ingredients_checkbox(){
	$ingredient_images = maybe_unserialize( get_option( 'wp_pizzeria_ingredient_images' ) );
	$ingredients = get_terms( 'wp_pizzeria_ingredient', array( 'hide_empty' =>0 ) );
	if ( !empty($ingredients) ) { ?>
	<div id="ingredient-selectbox">
	<?php
		foreach ( $ingredients as $ingredient ) {
			$link = get_term_link( intval($ingredient->term_id), 'wp_pizzeria_ingredient' );
			if ( is_wp_error( $link ) )
				return false;
			$checked = "";
			/*if ( has_term( $tag->term_id, 'wp_pizzeria_ingredient' )	)
				$checked = ' checked="checked"';*/
			?>
			<label for="<?php echo $ingredient->name; ?>">
				<input type="checkbox" id="<?php echo $ingredient->name; ?>" name="wp_pizzeria_ingredients[]" value="<?php echo $ingredient->term_id; ?>"<?php echo $checked; ?>/>
				<?php if ( array_key_exists( $ingredient->term_id, $ingredient_images ) ) : ?> 
					<img src="<?php echo $ingredient_images[$ingredient->term_id]; ?>" alt="<?php echo $ingredient->name; ?>" />		
				<?php endif;
						echo $ingredient->name; ?>
			</label>	
<?php } ?>
	</div><?php
	}
}


?>