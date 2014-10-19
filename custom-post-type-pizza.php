<?php
/*
* Register custom taxonomy wp_pizzeria_pizza for pizzas
*/
add_action('init', 'wp_pizzeria_pizza_post_type');

function wp_pizzeria_pizza_post_type() {
	//setup labels
	$labels = array(
	    'name' => __('Pizzas', 'wp_pizzeria'),
	    'singular_name' => __('Pizza', 'wp_pizzeria'),
	    'add_new' => __('Add pizza', 'wp_pizzeria'),
	    'add_new_item' => __('Add pizza', 'wp_pizzeria'),
	    'edit_item' => __('Edit pizza', 'wp_pizzeria'),
	    'new_item' => __('New pizza', 'wp_pizzeria'),
	    'all_items' => __('Pizzas', 'wp_pizzeria'),
	    'view_item' => __('View pizza', 'wp_pizzeria'),
	    'search_items' => __('Search pizza', 'wp_pizzeria'),
	    'not_found' =>  __('No pizzas found', 'wp_pizzeria'),
	    'not_found_in_trash' => __('No pizzas in the trash', 'wp_pizzeria'), 
	    'parent_item_colon' => '',
	    'menu_name' => 'Pizzas'
	  );
	//register custom post type using before declared labels
	register_post_type('wp_pizzeria_pizza', array(
		'labels' => $labels,
		'public' => true,
		'show_in_nav_menus' => true,
		'show_ui' => true,
		'capability_type' => 'post',
		'has_archive' => true,	
		'rewrite' => array('slug' => 'pizza','with_front'=>true),
		'query_var' => false,
		'supports' => array('title','editor','thumbnail', 'page-attributes'),
		'taxonomies' => array('wp_pizzeria_ingredient', 'wp_pizzeria_category'),
		'register_meta_box_cb' => 'wp_pizzeria_add_custom_box_to_pizza'
	));
}

/* Custom Meta boxes */

function wp_pizzeria_add_custom_box_to_pizza() {
	$post_type = 'wp_pizzeria_pizza';
	if (isset($_GET['post'])){
		$post_id = $_GET['post'];	
	}elseif(isset($_POST['post_ID'])){
		$post_id = $_POST['post_ID'];	
	}
    if ( 
    		( isset($post_id) && get_post_type($post_id) == $post_type ) or 
    		( isset($_GET['post_type']) && $_GET['post_type'] == $post_type ) 
    	){
    	remove_meta_box( 'tagsdiv-wp_pizzeria_ingredient', 'wp_pizzeria_pizza', 'side' );
    	remove_meta_box( 'pageparentdiv', 'wp_pizzeria_pizza', 'side' );
    	add_meta_box(
			'wp_pizzeria_tags_custom_box',
			__('Pizza ingredients', 'wp_pizzeria'),
			'wp_pizzeria_tags_inner_custom_box',
			$post_type,
			'side',
			'core'
		);    	
		add_meta_box(
			'wp_pizzeria_pizza_price_custom_box',
			__('Pizza price', 'wp_pizzeria'),
			'wp_pizzeria_pizza_price_inner_custom_box',
			$post_type,
			'side',
			'core'		
		);
		add_meta_box(
			'wp_pizzeria_number_custom_box',
			__('Pizza menu number', 'wp_pizzeria'),
			'wp_pizzeria_number_inner_custom_box',
			$post_type,
			'side',
			'core'		
		);		 	
	}
}

function wp_pizzeria_tags_inner_custom_box($post){ ?>
<div id="wp_pizzeria_ingredient">
	<div class="ingredientdiv">
		<div class="tabs-panel">
		<ul id="wp_pizzeria_ingredientchecklist" class="form-no-clear">
		<?php 
			$tags = get_terms( 'wp_pizzeria_ingredient', array( 'hide_empty' =>0 ) );
			foreach ( $tags as $key => $tag ) {
				/*if ( 'edit' == 'view' )
					$link = get_edit_tag_link( $tag->term_id, 'wp_pizzeria_ingredient' );
				else*/
				$link = get_term_link( intval($tag->term_id), 'wp_pizzeria_ingredient' );
				if ( is_wp_error( $link ) )
					return false;
				$checked = "";
				if ( has_term( $tag->term_id, 'wp_pizzeria_ingredient' )	)
					$checked = ' checked="checked"';
				?>				
				<li class="popular-category tag-ingredient">
					<label for="<?php echo $tag->name; ?>">
						<input type="checkbox" id="<?php echo $tag->name; ?>" name="wp_pizzeria_ingredients[]" value="<?php echo $tag->term_id; ?>"<?php echo $checked; ?>/>
						<?php echo $tag->name; ?>
					</label>
					<a class="edit-ingredient hide-if-js" href="./edit-tags.php?action=edit&taxonomy=wp_pizzeria_ingredient&tag_ID=<?php echo $tag->term_id; ?>&post_type=wp_pizzeria_pizza"><?php _e('Edit', 'wp_pizzeria'); ?></a>
<?php /* if (!pizza_ingredient_has_picture($tag->term_id)) : */ ?>					
					<a class="add-ingredient-image hide-if-js" href="#"><?php _e('Add image', 'wp_pizzeria'); ?></a>
<?php /* endif; */ ?>	
				</li>				
				<?php
	      }
	  	?>		
		</ul>
		<script type="text/javascript" >
			jQuery(document).ready(function($){
				$('.tag-ingredient').live('mouseover mouseout', function(){
					$(this).children('a').toggle();
				});
				/*				
				$('.add-ingredient-image').live('click', function(){
					//add input for image upload with ajax
				});
				*/
			});
		</script>
		</div>
	</div>
	<div class="jaxtag">
	<div class="nojs-tags hide-if-js">
	<p>Add or remove tags</p>
	<textarea name="tax_input[wp_pizzeria_ingredient]" rows="3" cols="20" class="the-tags" id="tax-input-wp_pizzeria_ingredient"></textarea></div>
 		<div class="ajaxtag hide-if-no-js">
		<label class="screen-reader-text" for="new-tag-wp_pizzeria_ingredient"><?php _e('Pizza ingredients', 'wp_pizzeria'); ?></label>
		<div class="taghint" style=""><?php _e('Add new pizza ingredient', 'wp_pizzeria'); ?></div>
		<p><input type="text" id="new-tag-wp_pizzeria_ingredient" name="newtag[wp_pizzeria_ingredient]" class="newtag form-input-tip" size="16" autocomplete="off" value="">
		<input type="button" class="button tagadd" value="<?php _e('Add', 'wp_pizzeria'); ?>" tabindex="3"></p>
	</div>
	<p class="howto"><?php _e('Separate ingredients with commas', 'wp_pizzeria'); ?></p>
		</div>
	<div class="tagchecklist"></div>
</div>		
<?php 
} 

function wp_pizzeria_pizza_price_inner_custom_box($post) { 
	$prices = maybe_unserialize( get_post_meta( $post->ID, '_wp_pizzeria_prices', true ) );
	if (!is_array($prices)){
		$prices = array();	
	}
	$pizzeria_settings = maybe_unserialize( get_option('wp_pizzeria_settings') );
	if ( !is_array($pizzeria_settings) )
			$pizzeria_settings = array();	
	if (is_array($pizzeria_settings['sizes'])) :
		foreach ($pizzeria_settings['sizes'] as $key => $size) :
			if ( $key == 'primary' ) { continue; }
	?>
		<p>
		<?php if ($pizzeria_settings['sizes']['primary'] == $key) { echo '<strong>'; } ?>
		<label for="<?php echo $key; ?>_price"><?php _e('Price for', 'wp_pizzeria'); ?> <?php echo $size; ?>:</label>
		<?php if ($pizzeria_settings['sizes']['primary'] == $key) { echo '</strong>'; } ?>
		</p>
		<p>
		<?php 
			if( array_key_exists( 'currency', $pizzeria_settings ) && array_key_exists( 'currency_pos', $pizzeria_settings ) && $pizzeria_settings['currency_pos'] == 'before' )					
				echo $pizzeria_settings['currency'];		
		?>		
		<input type="text" name="<?php echo $key; ?>_price" value="<?php if ( array_key_exists( $key, $prices ) ) { echo $prices[$key]; } ?>"/>
		<?php
			if( array_key_exists( 'currency', $pizzeria_settings ) && (!array_key_exists( 'currency_pos', $pizzeria_settings ) || $pizzeria_settings['currency_pos'] == 'after' ) )
				echo $pizzeria_settings['currency'];
		?>
		</p>
	<?php endforeach; 
	endif; ?>
<?php }

function wp_pizzeria_number_inner_custom_box($post) {
	$number = $post->menu_order;
	if( isset( $_GET['post_type'] ) ){
		$post_type = $_GET['post_type'];	
	}else{
		$post_type = $post->post_type;	
	}
	if( $post_type == 'wp_pizzeria_pizza' ){
		$label = __('Pizza menu number', 'wp_pizzeria');
	}if( $post_type == 'wp_pizzeria_dessert' ){
		$label = __('Dessert menu number', 'wp_pizzeria');
	}else{
		$label = __('Beverage menu number', 'wp_pizzeria');
	}
?>
	<label for="wp_pizzeria_number"><?php echo $label; ?>:</label>
	<input type="text" name="wp_pizzeria_number" value="<?php echo $number; ?>" />
<?php }

/* Save custom meta boxes content */

add_action( 'save_post', 'wp_pizzeria_pizza_save_postdata' );

function wp_pizzeria_pizza_save_postdata( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
		return;   
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
		return;
	if ( get_post_type( $post_id ) != 'wp_pizzeria_pizza' )
		return;
	if ( !current_user_can( 'edit_post', $post_id ) )
			return;
	$pizzeria_settings = maybe_unserialize( get_option('wp_pizzeria_settings') );
	if ( !is_array($pizzeria_settings) )
			$pizzeria_settings = array();	
	$prices = array();
	if( array_key_exists( 'sizes', $pizzeria_settings ) ) {
		foreach ( $pizzeria_settings['sizes'] as $key => $size ):
			if ( $key == 'primary' ) 
				continue;
			if ( isset( $_POST[$key.'_price'] ) )
				$prices[$key] = $_POST[$key.'_price']; 	
		endforeach;
	}	
		update_post_meta( $post_id, '_wp_pizzeria_prices', maybe_serialize( $prices ) );
	if ( array_key_exists( 'sizes', $pizzeria_settings ) && array_key_exists( 'primary', $pizzeria_settings['sizes'] ) && isset( $_POST[$pizzeria_settings['sizes']['primary'].'_price'] ) ) 
		update_post_meta( $post_id, '_wp_pizzeria_price', $_POST[$pizzeria_settings['sizes']['primary'].'_price'] );
	if( isset($_POST['wp_pizzeria_number']) ){
		global $wpdb;
		$wpdb->update( $wpdb->posts, array( 'menu_order' => intval( $_POST['wp_pizzeria_number'] ) ), array( 'ID' => $post_id ), array( '%d' ),  array( '%d' ) );
	}		
	if ( isset( $_POST['wp_pizzeria_ingredients'] ) ){
		$term_ids = array_map('intval', $_POST['wp_pizzeria_ingredients']);
    	$term_ids = array_unique( $term_ids );
		wp_set_object_terms( $post_id, $term_ids, 'wp_pizzeria_ingredient' ); 	
	}
}

/* Show pizza counts in dashboard overview widget */

add_action('right_now_content_table_end', 'wp_pizzeria_pizza_add_counts');

function wp_pizzeria_pizza_add_counts() {
        if (!post_type_exists('wp_pizzeria_pizza')) {
             return;
        }

        $num_posts = wp_count_posts( 'wp_pizzeria_pizza' );
        $num = number_format_i18n( $num_posts->publish );
        $text = _n( 'Pizza', 'Pizzas', intval($num_posts->publish) );
        if ( current_user_can( 'edit_posts' ) ) {
            $num = "<a href='edit.php?post_type=wp_pizzeria_pizza'>$num</a>";
            $text = "<a href='edit.php?post_type=wp_pizzeria_pizza'>$text</a>";
        }
        echo '<td class="first b b-wp_pizzeria_pizza">' . $num . '</td>';
        echo '<td class="t wp_pizzeria_pizza">' . $text . '</td>';

        echo '</tr>';

        if ($num_posts->pending > 0) {
            $num = number_format_i18n( $num_posts->pending );
            $text = _n( 'Pizza awaiting moderation', 'Pizza awaiting moderation', intval($num_posts->pending) );
            if ( current_user_can( 'edit_posts' ) ) {
                $num = "<a href='edit.php?post_status=pending&post_type=wp_pizzeria_pizza'>$num</a>";
                $text = "<a href='edit.php?post_status=pending&post_type=wp_pizzeria_pizza'>$text</a>";
            }
            echo '<td class="first b b-wp_pizzeria_pizza">' . $num . '</td>';
            echo '<td class="t wp_pizzeria_pizza">' . $text . '</td>';

            echo '</tr>';
        }
}

/*
* Add ajax for ability to add new ingredients on edit/add pizza page
*/

add_action('admin_head', 'wp_pizzeria_add_ingredient_javascript');

function wp_pizzeria_add_ingredient_javascript() {
	global $typenow, $pagenow; 
	if ( is_admin() && ( $pagenow == 'post-new.php' or $pagenow == 'post.php' ) && ( $typenow == 'wp_pizzeria_pizza' or ( isset($_GET['post_type']) && $_GET['post_type'] == 'wp_pizzeria_pizza') ) ) {
		global $post;
		wp_enqueue_script( 'wp_pizzeria_addIngredient', plugin_dir_url( __FILE__ ) . 'js/ajax-add-ingredient.js', array( 'jquery' ) );
		wp_localize_script( 'wp_pizzeria_addIngredient', 'wp_pizzeria_addIngredient', array(
	   	'addIngredientNonce' => wp_create_nonce( 'wp_pizzeria_add_ingredient-nonce' ),
	   	'postID' => $post->ID
	   	)
		);
	}
}

add_action('wp_ajax_wp_pizzeria_add_ingredient', 'wp_pizzeria_add_ingredient_callback');

function wp_pizzeria_add_ingredient_callback() {
	if ( ! wp_verify_nonce( $_POST['addIngredientNonce'], 'wp_pizzeria_add_ingredient-nonce' ) )
		die ( 'Busted!');
	$tags = explode(',', $_POST['tag']);
	$tags = array_map("trim", $tags);
   wp_set_object_terms( $_POST['postID'], $tags, 'wp_pizzeria_ingredient', true );
	die();
}

add_filter( 'manage_edit-wp_pizzeria_pizza_columns', 'wp_pizzeria_pizza_edit_columns' ) ;

function wp_pizzeria_pizza_edit_columns( $columns ) {

	$columns = array(
		'cb' => '<input type="checkbox" />',
		'menu_number' => __( '#', 'wp_pizzeria' ),
		'title' => __( 'Title' ),
		'category' => __( 'Category', 'wp_pizzeria' ),
		'ingredients' => __( 'Ingredients', 'wp_pizzeria' ),
	);
	$pizzeria_settings = maybe_unserialize( get_option('wp_pizzeria_settings') );
	if ( !is_array($pizzeria_settings) )
			$pizzeria_settings = array();	
	if ( is_array( $pizzeria_settings['sizes'] ) ){
		foreach ($pizzeria_settings['sizes'] as $key => $size) {
			if ( $key == 'primary' ) 
				continue;
			$columns[$size] = $size;
		}
			
	}
	$columns['date'] = __('Date');

	return $columns;
}

add_action( 'manage_wp_pizzeria_pizza_posts_custom_column', 'manage_wp_pizzeria_pizza_columns', 10, 2 );

function manage_wp_pizzeria_pizza_columns( $column, $post_id ) {
	global $post;
	switch( $column ) {
		case 'menu_number' :
			global $wpdb;
			$menu_id = $wpdb->get_var( $wpdb->prepare( "SELECT menu_order FROM $wpdb->posts WHERE ID = %d ", $post_id ) );
			echo $menu_id;
			break;
		case 'category' :
			$terms = get_the_terms( $post_id, 'wp_pizzeria_category' );
			if ( !empty( $terms ) ) {
				$out = array();
				foreach ( $terms as $term ) {
					$out[] = sprintf( '<a href="%s">%s</a>',
						esc_url( add_query_arg( array( 'post_type' => $post->post_type, 'wp_pizzeria_category' => $term->slug ), 'edit.php' ) ),
						esc_html( sanitize_term_field( 'name', $term->name, $term->term_id, 'wp_pizzeria_category', 'display' ) )
					);
				}
				echo join( ', ', $out );
			}
			else {
				_e( 'No Categories', 'wp_pizzeria' );
			}
			break;

		case 'ingredients' :
			$terms = get_the_terms( $post_id, 'wp_pizzeria_ingredient' );
			if ( !empty( $terms ) ) {
				$out = array();
				foreach ( $terms as $term ) {
					$out[] = sprintf( '<a href="%s">%s</a>',
						esc_url( add_query_arg( array( 'post_type' => $post->post_type, 'wp_pizzeria_ingredient' => $term->slug ), 'edit.php' ) ),
						esc_html( sanitize_term_field( 'name', $term->name, $term->term_id, 'wp_pizzeria_ingredient', 'display' ) )
					);
				}
				echo join( ', ', $out );
			}
			else {
				_e( 'No Ingredients', 'wp_pizzeria' );
			}
			break;
		default :
			break;
	}
	$pizzeria_settings = maybe_unserialize( get_option('wp_pizzeria_settings') );
	if ( !is_array($pizzeria_settings) )
			$pizzeria_settings = array();	
	if ( is_array( $pizzeria_settings['sizes'] ) && array_key_exists( $column, $pizzeria_settings['sizes'] ) ){					
		$prices = maybe_unserialize( get_post_meta( $post->ID, '_wp_pizzeria_prices', true ) );
		if (!is_array($prices)){
			$prices = array();	
		}		
		if ( array_key_exists( $column, $prices ) ) {
			if( array_key_exists( 'currency', $pizzeria_settings ) && array_key_exists( 'currency_pos', $pizzeria_settings ) && $pizzeria_settings['currency_pos'] == 'before' )					
				echo $pizzeria_settings['currency'];
			echo $prices[$column];			
			if( array_key_exists( 'currency', $pizzeria_settings ) && (!array_key_exists( 'currency_pos', $pizzeria_settings ) || $pizzeria_settings['currency_pos'] == 'after' ) )
				echo $pizzeria_settings['currency']; 
		}		 
	}
}
?>