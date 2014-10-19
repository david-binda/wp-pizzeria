<?php
function wp_pizzeria_pasta_display_func($atts) {
	extract(shortcode_atts(array(
		'cat' => 'wp_pizzeria_nocat',
	), $atts));
   $output = '';  
	
	/* Save original post*/	
	global $post;
	$tmp_post = $post;

	/* Loop all pizzas */
	$pizzeria_settings = maybe_unserialize( get_option('wp_pizzeria_settings') );
	if ( !array( $pizzeria_settings ) )
		$pizzeria_settings = array();
	$args = array( 'post_type' => 'wp_pizzeria_pasta', 'posts_per_page' => -1, 'orderby' => 'menu_order', 'order' => 'ASC' );
	if ( $cat != 'wp_pizzeria_nocat' )
		$args['wp_pizzeria_pasta_category'] = $cat;
	$pizzas = new WP_Query( $args );
	$output .= '<table class="wp-pizzeria pasta">'."\n\t<thead>";
	$table_footer_header = "\n\t\t<tr>";
	$table_footer_header .= "\n\t\t\t" . '<th class="col1 menu-number">'.__('#', 'wp_pizzeria').'</th>';
	$table_footer_header .= "\n\t\t\t" . '<th class="col2 title">'.__('Title', 'wp_pizzeria').'</th>';
	$table_footer_header .= "\n\t\t\t" . '<th class="col3 description">'.__('Description', 'wp_pizzeria').'</th>';
	//$table_footer_header .= "\n\t\t\t" . '<th class="col4 thumb">'.__('Image', 'wp_pizzeria').'</th>';
	$table_footer_header .= "\n\t\t\t" . '<th class="col5 price">'.__('Price', 'wp_pizzeria').'</th>';
	$table_footer_header .= "\n\t\t</tr>";
	$output .= $table_footer_header;
	$output .= "\n\t</thead>\n\t<tfoot>";
	$output .= $table_footer_header;	
	$output .= "\n\t</tfoot>\n\t<tbody>";
	unset($table_footer_header);
	$even = true;
	while ( $pizzas->have_posts() ) : 
		$pizzas->the_post();
		$categories = wp_get_post_terms( get_the_ID(), 'wp_pizzeria_beverage_category' );
		if ($even == true){
			$class = 'pizza even';	
		}else{
			$class = '$pizza odd';	
			$even = true;
		}
		if ( !empty( $categories ) )
		foreach ( $categories as $category ){
			$class .= ' ' . $category->slug;	
		}
		$output .= "\n\t\t" . '<tr class="'.$class.'">';
		global $post;
		$output .= "\n\t\t\t" . '<td class="col1 menu-number">' . $post->menu_order . '</td>';
		$output .= "\n\t\t\t" . '<td class="col2 title">';
			$output .= '<a href="#" class="pizza-title">' . get_the_title() . '</a>';
			$output .= get_the_post_thumbnail( get_the_ID(), 'wp_pizzeria_thumbnail' );
			$output .= '</td>';
		$output .= "\n\t\t\t" . '<td class="col3 description"><div class="content">'.get_the_content().'</div></td>';
		if( get_post_meta( $post->ID, '_wp_pizzeria_price', true ) !== false ){
			$output .= "\n\t\t\t" . '<td class="col5 price">';
			if( array_key_exists( 'currency', $pizzeria_settings ) && array_key_exists( 'currency_pos', $pizzeria_settings ) && $pizzeria_settings['currency_pos'] == 'before' )					
				$output .= $pizzeria_settings['currency']; 
			$output .= get_post_meta( $post->ID, '_wp_pizzeria_price', true );
			if( array_key_exists( 'currency', $pizzeria_settings ) && (!array_key_exists( 'currency_pos', $pizzeria_settings ) || $pizzeria_settings['currency_pos'] == 'after' ) )
				$output .= $pizzeria_settings['currency'];
			$output .= '</td>';	
		}else{
			$output .= "\n\t\t\t" . '<td class="col5 price"></td>';
		}
		$output .= "\n\t\t" . '</tr>'; 						
	endwhile; 
	$output .= "\n\t</tbody>\n";
	$output .= '</table>';
	
	/* Restore original post */
	$post = $tmp_post;
	
	return $output;
}
add_shortcode('pasta', 'wp_pizzeria_pasta_display_func');

function wp_pizzeria_pasta_display_scripts_method() {
	wp_enqueue_script( 'filter-pizzas', plugins_url('/js/filter-pizzas.js', __FILE__), array('jquery') );
}    
 
add_action('wp_enqueue_scripts', 'wp_pizzeria_pasta_display_scripts_method');

function pasta_loop(){
	global $wp_query;
	$args = array_merge( $wp_query->query_vars, array( 'orderby' => 'menu_order', 'order' => 'ASC' ) );
	query_posts( $args );	
	if ( have_posts() ) :
	$pizzeria_settings = maybe_unserialize( get_option('wp_pizzeria_settings') );
	if ( !array( $pizzeria_settings ) )
		$pizzeria_settings = array();	
	 ?>
	<table class="wp-pizzeria pasta">
		<thead>
			<tr>
				<th class="col1 menu-number">#</th>
				<th class="col2 title"><?php _e('Title', 'wp_pizzeria'); ?></th>
				<th class="col3 description hidden"><?php _e('Description', 'wp_pizzeria'); ?></th>
				<th class="col5 price"><?php _e('Price', 'wp_pizzeria'); ?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th class="col1 menu-number">#</th>
				<th class="col2 title"><?php _e('Title', 'wp_pizzeria'); ?></th>
				<th class="col3 description hidden"><?php _e('Description', 'wp_pizzeria'); ?></th>
				<th class="col5 price"><?php _e('Price', 'wp_pizzeria'); ?></th>
			</tr>
		</tfoot>
		<tbody>
<?php $odd = true; while ( have_posts() ) : the_post(); 
			$categories = wp_get_post_terms( get_the_ID(), 'wp_pizzeria_ingredient' ); ?>
			<tr class="pizza<?php if( $odd ) { echo ' odd '; $odd = false; }else{ echo ' even'; $odd = true; }  foreach ( (array)$categories as $category ) echo ' ' . $category->slug; ?>">
				<td class="col1 menu-number"><?php global $post; echo $post->menu_order; ?></td>
				<td class="col2 title"><a class="pizza-title" href="#"><?php the_title(); ?></a></td>
				<td class="col3 description hidden"><div class="content"><?php the_content(); ?></div></td>
				<td class="col5 price">
				<?php
					if( get_post_meta( get_the_ID(), '_wp_pizzeria_price', true ) !== false ){
						if( array_key_exists( 'currency', $pizzeria_settings ) && array_key_exists( 'currency_pos', $pizzeria_settings ) && $pizzeria_settings['currency_pos'] == 'before' ) 
							echo $pizzeria_settings['currency'];		
						echo get_post_meta( get_the_ID(), '_wp_pizzeria_price', true );
						if( array_key_exists( 'currency', $pizzeria_settings ) && (!array_key_exists( 'currency_pos', $pizzeria_settings ) || $pizzeria_settings['currency_pos'] == 'after' ) ) 
							echo $pizzeria_settings['currency'];
					}					
				?>
				</td>
			</tr>
<?php endwhile; ?>
		</tbody>
	</table>
<?php
	endif; 
}
?>