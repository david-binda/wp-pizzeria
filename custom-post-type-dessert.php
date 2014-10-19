<?php
/*
* Register custom taxonomy wp_pizzeria_pizza for pizzas
*/
add_action('init', 'wp_pizzeria_dessert_post_type');

function wp_pizzeria_dessert_post_type() {
	//setup labels
	$labels = array(
	    'name' => __('Desserts', 'wp_pizzeria'),
	    'singular_name' => __('Dessert', 'wp_pizzeria'),
	    'add_new' => __('Add Dessert', 'wp_pizzeria'),
	    'add_new_item' => __('Add Dessert', 'wp_pizzeria'),
	    'edit_item' => __('Edit Dessert', 'wp_pizzeria'),
	    'new_item' => __('New Dessert', 'wp_pizzeria'),
	    'all_items' => __('Desserts', 'wp_pizzeria'),
	    'view_item' => __('View Desserts', 'wp_pizzeria'),
	    'search_items' => __('Search Desserts', 'wp_pizzeria'),
	    'not_found' =>  __('No desserts found', 'wp_pizzeria'),
	    'not_found_in_trash' => __('No desserts in the trash', 'wp_pizzeria'), 
	    'parent_item_colon' => '',
	    'menu_name' => 'Desserts'
	  );
	//register custom post type using before declared labels
	register_post_type('wp_pizzeria_dessert', array(
		'labels' => $labels,
		'public' => true,
		'show_in_nav_menus' => true,
		'show_ui' => true,
		'capability_type' => 'post',
		'has_archive' => true,	
		'rewrite' => array('slug' => 'dessert','with_front'=>true),
		'query_var' => false,
		'supports' => array('title','editor','thumbnail', 'page-attributes'),
		'taxonomies' => array('wp_pizzeria_dessert_category'),
		'register_meta_box_cb' => 'wp_pizzeria_add_custom_box_to_dessert'
	));
}

/* Custom Meta boxes */

function wp_pizzeria_add_custom_box_to_dessert() {
	$post_type = 'wp_pizzeria_dessert';
	if (isset($_GET['post'])){
		$post_id = $_GET['post'];	
	}elseif(isset($_POST['post_ID'])){
		$post_id = $_POST['post_ID'];	
	}
    if ( 
    		( isset($post_id) && get_post_type($post_id) == $post_type ) or 
    		( isset($_GET['post_type']) && $_GET['post_type'] == $post_type ) 
    	){  	
    	remove_meta_box( 'pageparentdiv', 'wp_pizzeria_dessert', 'side' );
		add_meta_box(
			'wp_pizzeria_dessert_price_custom_box',
			__('Dessert price', 'wp_pizzeria'),
			'wp_pizzeria_dessert_price_inner_custom_box',
			$post_type,
			'side',
			'core'		
		);
		add_meta_box(
			'wp_pizzeria_number_custom_box',
			__('Dessert menu number', 'wp_pizzeria'),
			'wp_pizzeria_number_inner_custom_box',
			$post_type,
			'side',
			'core'		
		);		 	
	}
}


function wp_pizzeria_dessert_price_inner_custom_box($post) { 
	$price = get_post_meta( $post->ID, '_wp_pizzeria_price', true );
	if ( $price === false )
		$price = '';	
	$pizzeria_settings = maybe_unserialize( get_option('wp_pizzeria_settings') );
?>
		<p>
			<label for="dessert_price"><?php _e('Price', 'wp_pizzeria'); ?></label>
		<?php if( array_key_exists( 'currency', $pizzeria_settings ) && array_key_exists( 'currency_pos', $pizzeria_settings ) && $pizzeria_settings['currency_pos'] == 'before' )					
				echo $pizzeria_settings['currency']; ?> 
			<input type="text" id="dessert_price" name="dessert_price" value="<?php echo $price; ?>"/>
		<?php
			if( array_key_exists( 'currency', $pizzeria_settings ) && (!array_key_exists( 'currency_pos', $pizzeria_settings ) || $pizzeria_settings['currency_pos'] == 'after' ) )
				echo $pizzeria_settings['currency'];
		?>
		</p>
<?php }

/* Save custom meta boxes content */

add_action( 'save_post', 'wp_pizzeria_dessert_save_postdata' );

function wp_pizzeria_dessert_save_postdata( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
		return;
	if ( get_post_type($post_id) != 'wp_pizzeria_dessert' )
		return;   
	if ( !current_user_can( 'edit_post', $post_id ) )
		return;

	if ( isset( $_POST['dessert_price'] ) ){
		update_post_meta( $post_id, '_wp_pizzeria_price', $_POST['dessert_price'] ); 	
	}
}

/* Show desserts counts in dashboard overview widget */

add_action('right_now_content_table_end', 'wp_pizzeria_dessert_add_counts');

function wp_pizzeria_dessert_add_counts() {
        if (!post_type_exists('wp_pizzeria_dessert')) {
             return;
        }

        $num_posts = wp_count_posts( 'wp_pizzeria_dessert' );
        $num = number_format_i18n( $num_posts->publish );
        $text = _n( 'Dessert', 'Desserts', intval($num_posts->publish) );
        if ( current_user_can( 'edit_posts' ) ) {
            $num = "<a href='edit.php?post_type=wp_pizzeria_dessert'>$num</a>";
            $text = "<a href='edit.php?post_type=wp_pizzeria_dessert'>$text</a>";
        }
        echo '<td class="first b b-wp_pizzeria_dessert">' . $num . '</td>';
        echo '<td class="t wp_pizzeria_dessert">' . $text . '</td>';

        echo '</tr>';

        if ($num_posts->pending > 0) {
            $num = number_format_i18n( $num_posts->pending );
            $text = _n( 'Dessert awaiting moderation', 'Desserts awaiting moderation', intval($num_posts->pending) );
            if ( current_user_can( 'edit_posts' ) ) {
                $num = "<a href='edit.php?post_status=pending&post_type=wp_pizzeria_dessert'>$num</a>";
                $text = "<a href='edit.php?post_status=pending&post_type=wp_pizzeria_dessert'>$text</a>";
            }
            echo '<td class="first b b-wp_pizzeria_dessert">' . $num . '</td>';
            echo '<td class="t wp_pizzeria_dessert">' . $text . '</td>';

            echo '</tr>';
        }
}

add_filter( 'manage_edit-wp_pizzeria_dessert_columns', 'wp_pizzeria_dessert_edit_columns' ) ;

function wp_pizzeria_dessert_edit_columns( $columns ) {

	$columns = array(
		'cb' => '<input type="checkbox" />',
		'menu_number' => __( '#', 'wp_pizzeria' ),
		'title' => __( 'Title' ),
		'category' => __( 'Category', 'wp_pizzeria' ),
		'price' => __('Price', 'wp_pizzeria'),
		'date' => __('Date')
	);

	return $columns;
}

add_action( 'manage_wp_pizzeria_dessert_posts_custom_column', 'manage_wp_pizzeria_dessert_columns', 10, 2 );

function manage_wp_pizzeria_dessert_columns( $column, $post_id ) {
	global $post;
	switch( $column ) {
		case 'menu_number' :
			global $wpdb;
			$menu_id = $wpdb->get_var( $wpdb->prepare( "SELECT menu_order FROM $wpdb->posts WHERE ID = %d ", $post_id ) );
			echo $menu_id;
			break;
		case 'category' :
			$terms = get_the_terms( $post_id, 'wp_pizzeria_dessert_category' );
			if ( !empty( $terms ) ) {
				$out = array();
				foreach ( $terms as $term ) {
					$out[] = sprintf( '<a href="%s">%s</a>',
						esc_url( add_query_arg( array( 'post_type' => $post->post_type, 'wp_pizzeria_dessert_category' => $term->slug ), 'edit.php' ) ),
						esc_html( sanitize_term_field( 'name', $term->name, $term->term_id, 'wp_pizzeria_dessert_category', 'display' ) )
					);
				}
				echo join( ', ', $out );
			}
			else {
				_e( 'No Categories', 'wp_pizzeria' );
			}
			break;

		case 'price' :
			$pizzeria_settings = maybe_unserialize( get_option('wp_pizzeria_settings') );
			if( get_post_meta( $post_id, '_wp_pizzeria_price', true ) !== false ){
				if( array_key_exists( 'currency', $pizzeria_settings ) && array_key_exists( 'currency_pos', $pizzeria_settings ) && $pizzeria_settings['currency_pos'] == 'before' )					
					echo $pizzeria_settings['currency'];
				echo get_post_meta( $post_id, '_wp_pizzeria_price', true );
				if( array_key_exists( 'currency', $pizzeria_settings ) && (!array_key_exists( 'currency_pos', $pizzeria_settings ) || $pizzeria_settings['currency_pos'] == 'after' ) )
					echo $pizzeria_settings['currency']; 
			}
			else {
				'';
			}
			break;
		default :
			break;
	}
}
?>