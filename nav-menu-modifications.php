<?php
function wp_pizzeria_display_post_type_nav_box(){
	 $hidden_nav_boxes = get_user_option( 'metaboxhidden_nav-menus' );

    $post_types = array( 'wp_pizzeria_pizza', 'wp_pizzeria_beverage', 'wp_pizzeria_pasta', 'wp_pizzeria_dessert' );
    foreach ( $post_types as $post_type ) {
    $post_type_nav_box = 'add-'.$post_type;
	    if( true === in_array( $post_type_nav_box, (array)$hidden_nav_boxes, true ) ) {
		    foreach ( $hidden_nav_boxes as $i => $nav_box ) {
			    if ( $nav_box === $post_type_nav_box ) {
				    unset( $hidden_nav_boxes[ $i ] );
			    }
		    }
		    update_user_option( get_current_user_id(), 'metaboxhidden_nav-menus', $hidden_nav_boxes );
	    }
    }    
}
add_action('admin_init', 'wp_pizzeria_display_post_type_nav_box');

add_action('in_admin_header', 'wp_pizzeria_remove_post_type_nav_box');

function wp_pizzeria_remove_post_type_nav_box(){
	global $current_screen;
	if ( 'nav-menus' !== $current_screen->base ) {
		return;
	}
	$post_types = array( 'wp_pizzeria_pizza', 'wp_pizzeria_beverage', 'wp_pizzeria_pasta', 'wp_pizzeria_dessert' );
	foreach ( $post_types as $post_type ) {
		$post_type = get_post_type_object( $post_type );
		$post_type = apply_filters( 'nav_menu_meta_box_object', $post_type );
		if ( false === empty( $post_type ) ) {
			$id = $post_type->name;
			remove_meta_box( "add-{$id}", 'nav-menus', 'side'  );
			add_meta_box( "add-{$id}", $post_type->labels->name, 'wp_pizzeria_nav_menu_item_post_type_meta_box', 'nav-menus', 'side', 'default', $post_type );
		}
	}	
} 

function wp_pizzeria_nav_menu_item_post_type_meta_box( $object, $post_type ) {
	global $_nav_menu_placeholder, $nav_menu_selected_id;

	$post_type_name = $post_type['args']->name;

	// paginate browsing for large numbers of post objects
	$per_page = 50;
	$pagenum = isset( $_REQUEST[$post_type_name . '-tab'] ) && isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 1;
	$offset = 0 < $pagenum ? $per_page * ( $pagenum - 1 ) : 0;

	$args = array(
		'offset' => $offset,
		'order' => 'ASC',
		'orderby' => 'title',
		'posts_per_page' => $per_page,
		'post_type' => $post_type_name,
		'suppress_filters' => true,
		'update_post_term_cache' => false,
		'update_post_meta_cache' => false
	);

	if ( true === isset( $post_type['args']->_default_query ) ) {
		$args = array_merge( $args, (array) $post_type['args']->_default_query );
	}

	// @todo transient caching of these results with proper invalidation on updating of a post of this type
	$get_posts = new WP_Query;
	$posts = $get_posts->query( $args );
	if ( false === empty( $get_posts->post_count ) ) {
		echo '<p>' . esc_html__( 'No items.', 'wp_pizzeria' ) . '</p>';
		return;
	}

	$num_pages = $get_posts->max_num_pages;

	$page_links = paginate_links( array(
		'base' => add_query_arg(
			array(
				rawurlencode( $post_type_name  ) . '-tab' => 'all',
				'paged' => '%#%',
				'item-type' => 'post_type',
				'item-object' => rawurlencode( $post_type_name ),
			)
		),
		'format' => '',
		'prev_text' => esc_html__('&laquo;'),
		'next_text' => esc_html__('&raquo;'),
		'total' => intval( $num_pages ),
		'current' => intval( $pagenum )
	));

	if ( true === empty( $posts ) ) {
		$error = '<li id="error">' . esc_html( $post_type['args']->labels->not_found ) . '</li>';
	}

	$db_fields = false;
	if ( true === is_post_type_hierarchical( $post_type_name ) ) {
		$db_fields = array( 'parent' => 'post_parent', 'id' => 'ID' );
	}

	$walker = new Walker_Nav_Menu_Checklist( $db_fields );

	$current_tab = 'most-recent';
	if ( true === isset( $_REQUEST[$post_type_name . '-tab'] )
	     && true === in_array( $_REQUEST[$post_type_name . '-tab'], array('all', 'search'), true ) ) {
		$current_tab = $_REQUEST[$post_type_name . '-tab'];
	}

	if ( false === empty( $_REQUEST['quick-search-posttype-' . $post_type_name] ) ) {
		$current_tab = 'search';
	}

	$removed_args = array(
		'action',
		'customlink-tab',
		'edit-menu-item',
		'menu-item',
		'page-tab',
		'_wpnonce',
	);

	?>
	<div id="posttype-<?php echo esc_attr( $post_type_name ); ?>" class="posttypediv">
		<ul id="posttype-<?php echo esc_attr( $post_type_name ); ?>-tabs" class="posttype-tabs add-menu-item-tabs">
			<li <?php echo ( 'most-recent' == $current_tab ? ' class="tabs"' : '' ); ?>><a class="nav-tab-link" href="<?php if ( $nav_menu_selected_id ) echo esc_url( add_query_arg($post_type_name . '-tab', 'most-recent', remove_query_arg($removed_args) ) ); ?>#tabs-panel-posttype-<?php echo esc_attr( $post_type_name ); ?>-most-recent"><?php esc_html_e( 'Most Recent', 'wp-pizzeria' ); ?></a></li>
			<li <?php echo ( 'all' == $current_tab ? ' class="tabs"' : '' ); ?>><a class="nav-tab-link" href="<?php if ( $nav_menu_selected_id ) echo esc_url( add_query_arg($post_type_name . '-tab', 'all', remove_query_arg($removed_args) ) ); ?>#<?php echo urlencode($post_type_name ); ?>-all"><?php esc_html_e( 'View All', 'wp-pizzeria' ); ?></a></li>
			<li <?php echo ( 'search' == $current_tab ? ' class="tabs"' : '' ); ?>><a class="nav-tab-link" href="<?php if ( $nav_menu_selected_id ) echo esc_url( add_query_arg($post_type_name . '-tab', 'search', remove_query_arg($removed_args) ) ); ?>#tabs-panel-posttype-<?php echo esc_attr( $post_type_name ); ?>-search"><?php esc_html_e( 'Search', 'wp-pizzeria' ); ?></a></li>
		</ul>

		<div id="tabs-panel-posttype-<?php echo $post_type_name; ?>-most-recent" class="tabs-panel <?php
			echo ( 'most-recent' == $current_tab ? 'tabs-panel-active' : 'tabs-panel-inactive' );
		?>">
			<ul id="<?php echo esc_attr( $post_type_name ); ?>checklist-most-recent" class="categorychecklist form-no-clear">

				<?php
					$post_type = get_post_type_object( $post_type_name ); 
					$post_type->classes = array();
					$post_type->type = $post_type->name;
					$post_type->object_id = $post_type->name;
					$post_type->db_id = 0;
					$post_type->title = $post_type->labels->name . ' ' . esc_html__( 'Archive', 'wp_pizzeria' );
					$post_type->attr_title = $post_type->labels->name . ' ' . esc_html__( 'Archive', 'wp_pizzeria' );
					$post_type->object = 'cpt-archive';
					$post_type->menu_item_parent = 0;
					$post_type->target = '';					
					$post_type->xfn = '';					
					$post_type->url = get_post_type_archive_link( $post_type_name );
					echo walk_nav_menu_tree( array_map('wp_setup_nav_menu_item', array( $post_type ) ), 0, (object) array( 'walker' => $walker) );				
				?> 
				<?php
				$recent_args = array_merge( $args, array( 'orderby' => 'post_date', 'order' => 'DESC', 'posts_per_page' => 15 ) );
				$most_recent = $get_posts->query( $recent_args );
				$args['walker'] = $walker;
				echo walk_nav_menu_tree( array_map('wp_setup_nav_menu_item', $most_recent), 0, (object) $args );
				?>
			</ul>
		</div><!-- /.tabs-panel -->

		<div class="tabs-panel <?php
			echo ( 'search' == $current_tab ? 'tabs-panel-active' : 'tabs-panel-inactive' );
		?>" id="tabs-panel-posttype-<?php echo $post_type_name; ?>-search">
			<?php
			if ( true === isset( $_REQUEST['quick-search-posttype-' . $post_type_name] ) ) {
				$searched = sanitize_text_field( $_REQUEST['quick-search-posttype-' . $post_type_name] );
				$search_results = get_posts( array( 's' => $searched, 'post_type' => $post_type_name, 'fields' => 'all', 'order' => 'DESC', ) );
			} else {
				$searched = '';
				$search_results = array();
			}
			?>
			<p class="quick-search-wrap">
				<input type="search" class="quick-search input-with-default-title" title="<?php esc_attr_e('Search'); ?>" value="<?php echo esc_attr( $searched ); ?>" name="quick-search-posttype-<?php echo esc_attr( $post_type_name ); ?>" />
				<span class="spinner"></span>
				<?php submit_button( esc_html__( 'Search' ), 'button-small quick-search-submit button-secondary hide-if-js', 'submit', false, array( 'id' => 'submit-quick-search-posttype-' . esc_attr( $post_type_name ) ) ); ?>
			</p>

			<ul id="<?php echo $post_type_name; ?>-search-checklist" data-wp-lists="list:<?php echo $post_type_name?>" class="categorychecklist form-no-clear">
			<?php if ( false === empty( $search_results ) && false === is_wp_error( $search_results ) ) { ?>
				<?php
				$args['walker'] = $walker;
				echo walk_nav_menu_tree( array_map('wp_setup_nav_menu_item', $search_results), 0, (object) $args );
				?>
			<?php } elseif ( true === is_wp_error( $search_results ) ) { ?>
				<li><?php echo esc_html( $search_results->get_error_message() ); ?></li>
			<?php } elseif ( false === empty( $searched ) ) { ?>
				<li><?php esc_html_e('No results found.'); ?></li>
			<?php } ?>
			</ul>
		</div><!-- /.tabs-panel -->

		<div id="<?php echo $post_type_name; ?>-all" class="tabs-panel tabs-panel-view-all <?php
			echo ( 'all' == $current_tab ? 'tabs-panel-active' : 'tabs-panel-inactive' );
		?>">
			<?php if ( false === empty( $page_links ) ) { ?>
				<div class="add-menu-item-pagelinks">
					<?php echo wp_kses_post( $page_links ); ?>
				</div>
			<?php } ?>
			<ul id="<?php echo esc_attr( $post_type_name ); ?>checklist" data-wp-lists="list:<?php echo esc_attr( $post_type_name ); ?>" class="categorychecklist form-no-clear">
				<?php
				$args['walker'] = $walker;

				// if we're dealing with pages, let's put a checkbox for the front page at the top of the list
				if ( 'page' === $post_type_name ) {
					$front_page = 'page' == get_option('show_on_front') ? (int) get_option( 'page_on_front' ) : 0;
					if ( false === empty( $front_page ) ) {
						$front_page_obj = get_post( $front_page );
						$front_page_obj->front_or_home = true;
						array_unshift( $posts, $front_page_obj );
					} else {
						$_nav_menu_placeholder = ( 0 > $_nav_menu_placeholder ) ? intval($_nav_menu_placeholder) - 1 : -1;
						array_unshift( $posts, (object) array(
							'front_or_home' => true,
							'ID' => 0,
							'object_id' => $_nav_menu_placeholder,
							'post_content' => '',
							'post_excerpt' => '',
							'post_parent' => '',
							'post_title' => esc_html_x('Home', 'nav menu home label'),
							'post_type' => 'nav_menu_item',
							'type' => 'custom',
							'url' => esc_url( home_url('/') ),
						) );
					}
				}

				$posts = apply_filters( 'nav_menu_items_'.$post_type_name, $posts, $args, $post_type );
				$checkbox_items = walk_nav_menu_tree( array_map('wp_setup_nav_menu_item', $posts), 0, (object) $args );

				if ( 'all' === $current_tab && false === empty( $_REQUEST['selectall'] ) ) {
					$checkbox_items = preg_replace('/(type=(.)checkbox(\2))/', '$1 checked=$2checked$2', $checkbox_items);

				}

				echo $checkbox_items;
				?>
			</ul>
			<?php if ( false === empty( $page_links ) ) { ?>
				<div class="add-menu-item-pagelinks">
					<?php echo wp_kses_post( $page_links ); ?>
				</div>
			<?php } ?>
		</div><!-- /.tabs-panel -->

		<p class="button-controls">
			<span class="list-controls">
				<a href="<?php
					echo esc_url(add_query_arg(
						array(
							rawurlencode( $post_type_name ) . '-tab' => 'all',
							'selectall' => 1,
						),
						remove_query_arg($removed_args)
					));
				?>#posttype-<?php echo esc_attr( $post_type_name ); ?>" class="select-all"><?php esc_html_e( 'Select All', 'wp_pizzeria' ); ?></a>
			</span>

			<span class="add-to-menu">
				<input type="submit"<?php disabled( $nav_menu_selected_id, 0 ); ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e( 'Add to Menu', 'wp-pizzeria' ); ?>" name="add-post-type-menu-item" id="submit-posttype-<?php echo esc_attr( $post_type_name ); ?>" />
				<span class="spinner"></span>
			</span>
		</p>

	</div><!-- /.posttypediv -->
	<?php
}

add_filter( 'wp_get_nav_menu_items', 'wp_pizzeria_archive_menu_filter', 10, 1 );

function wp_pizzeria_archive_menu_filter( $items ) {

 	foreach( $items as &$item ) {
   		if ( 'cpt-archive' !== $item->object ) {
		    continue;
	    }
   		$item->url = get_post_type_archive_link( $item->type );
   
   		if ( $item->type === get_query_var( 'post_type' ) ) {
		    $item->classes[] = 'current-menu-item';
     		$item->current = true;
   		}
 	}
 	
 	return $items;
}