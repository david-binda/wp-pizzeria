<?php
/*
*	WP Pizzeria plugin settings pag
*/

/* Add submenu */
add_action('admin_menu', 'wp_pizzeria_menu_item');

function wp_pizzeria_menu_item() {	    	     	   
	add_menu_page(__("WP Pizzeria Settings", 'wp_pizzeria'), __("WP Pizzeria", 'wp_pizzeria'), 'manage_options', 'wp_pizzeria_settings', 'wp_pizzeria_settings' );
}     
	
/* Settings page main function */
function wp_pizzeria_settings(){
	// Check for relevant permission
	if (!current_user_can('manage_options'))
			wp_die( __("You don't have permission to access this page.") );
	/* Save function */
	if ( isset( $_POST['wp_pizzeria_settings_submit'] ) ){
		$pizzeria_settings = maybe_unserialize( get_option('wp_pizzeria_settings') );
		if ( !is_array($pizzeria_settings) )
			$pizzeria_settings = array();	
		/*		
		if ( isset( $_POST['ingredient_page'] ) )
			$pizzeria_settings['ingredient_page'] = $_POST['ingredient_page'];
		*/
		// Update default price after changing default size
		if ( array_key_exists('primary',$pizzeria_settings['sizes'] ) ){
			$old_primary = $pizzeria_settings['sizes']['primary'];
		} else {
			$old_primary = '';
		}
		if (sanitize_title($_POST['primary']) != $old_primary){			
			$all_pizzas = get_posts( array('post_type' => 'wp_pizzeria_pizza','numberposts' => -1) );
			foreach ($all_pizzas as $post){
				unset($pizza_prices);
				setup_postdata($post);
				$pizza_prices = maybe_unserialize( get_post_meta($post->ID, 'wp_pizzeria_prices', true) );
				update_post_meta( $post->ID, 'wp_pizzeria_price', $pizza_prices[sanitize_title($_POST['primary'])] );	
			}
		}		
		// Save new sizes
		unset($pizzeria_settings['sizes']);
		if ( isset( $_POST['size'] ) ){
			$primary = $_POST['primary'];
			foreach ( $_POST['size'] as $size ){
				if ( $size <> '' ) {					
					$pizzeria_settings['sizes'][sanitize_title( $size )] = $size;
					if ( $size == $primary )
						$pizzeria_settings['sizes']['primary'] = sanitize_title( $size );				
				}	
			}	
		}
		// Save currency
		if ( isset( $_POST['wp_pizzeria_currency'] ) && trim($_POST['wp_pizzeria_currency']) != '' )
			$pizzeria_settings['currency'] = htmlspecialchars($_POST['wp_pizzeria_currency']);	
		if ( isset( $_POST['wp_pizzeria_currency_position'] ) && ( $_POST['wp_pizzeria_currency_position'] == 'after' || $_POST['wp_pizzeria_currency_position'] == 'before' ) )
			$pizzeria_settings['currency_pos'] = ($_POST['wp_pizzeria_currency_position']);	
		update_option( 'wp_pizzeria_settings', maybe_serialize( $pizzeria_settings ) ); 	 	
	}
	/* Settings page UI */
	?>	
	<div class="wrap">
		<div id="icon-options-general" class="icon32 icon32-posts-wp_pizzeria_pizza"><br/></div>
		<h2><?php _e('WP Pizzeria plugin settings', 'wp_pizzeria'); ?></h2>
		<br/><br/>
		<?php
			$pizzeria_settings = maybe_unserialize( get_option('wp_pizzeria_settings') );
			if ( !is_array( $pizzeria_settings ) )
				$pizzeria_settings = array();	
		?>
		<form action="<?php echo add_query_arg( array() ); ?>" method="post">
			<table class="form-table">
				<?php /*
				<tr>
					<th>
						<label for="ingredient_page"><?php _e('Pizzas by ingredient page', 'wp_pizzeria'); ?></label>
					</th>
					<td>
						<select name="ingredient_page">
						<?php 
							$all_pages_query = new WP_Query( array( 'post_type' => 'page', 'post_per_page' => -1)); 															
							while ( $all_pages_query->have_posts() ) : 
								$all_pages_query->the_post();
								$selected = ""; 
								if( isset($pizzeria_settings['ingredient_page']) && get_the_ID() == $pizzeria_settings['ingredient_page']){
									$selected = ' selected="selected"';
								}else{
									$selected='';
								} 
						?>
							<option value="<?php the_ID(); ?>"<?php echo $selected; ?>><?php the_title(); ?></option>
						<?php endwhile; wp_reset_postdata();  ?>
						</select>
					</td>
				</tr>
				*/ ?>
				<tr>
					<th><?php _e('Pizza sizes', 'wp_pizzeria'); ?></th>
					<td>
						<div id="sizes">
						<?php 
							if ( array_key_exists( 'sizes', $pizzeria_settings ) ) :							
								foreach ( $pizzeria_settings['sizes'] as $key => $size ) :
									if ( $key == 'primary' ) { continue; }								 
						?>
							<div class="size">
								<?php 
									$checked = '';
									if ( $pizzeria_settings['sizes']['primary'] == $key ) {
										$checked = ' checked="checked"';
									}
								?>
								<label for="<?php echo $size; ?>">
									<input type="radio" name="primary" value="<?php echo $size; ?>"<?php echo $checked; ?>/>
									<input type="text" name="size[]" id="<?php echo $size; ?>" value="<?php echo $size; ?>" />
								</label>
								<span><a href="#" title="<?php _e('Remove', 'wp_pizzeria'); ?>" class="remove"><?php _e('Remove', 'wp_pizzeria'); ?></a></span>
								</div>
						<?php endforeach;
								endif; ?>
						</div>
						<a href="#" title="<?php _e('Add pizza size'); ?>" id="add_pizza_size"><?php _e('Add pizza size'); ?></a>
						<p class="description">Check the radio button (<input type="radio" checked="checked"/>) beside pizza size to set this size as default</p>						
					</td>					
				</tr>
				<tr>
					<th><label for="wp_pizzeria_currency"><?php _e('Currency', 'wp_pizzeria'); ?></label></th>
					<td>
						<?php 
							if( !array_key_exists('currency', $pizzeria_settings) )
								$pizzeria_settings['currency'] = '';
						?>
						<input type="text" value="<?php echo $pizzeria_settings['currency']; ?>" name="wp_pizzeria_currency" id="wp_pizzeria_currency" />
						<p class="desctiption">Add your currency sign to display it next to your price. Include also spaces if necessary.</p>					
					</td>				
				</tr>
				<tr>
					<th><label for="wp_pizzeria_currency_position"><?php _e('Currency position', 'wp_pizzeria'); ?></label></th>
					<td>
						<?php 
							if( !array_key_exists('currency_pos', $pizzeria_settings) )
								$pizzeria_settings['currency_pos'] = 'after';
						?>
						<select name="wp_pizzeria_currency_position" id="wp_pizzeria_currency_position">
							<option value="before"<?php if ( $pizzeria_settings['currency_pos'] == 'before' ){ echo ' selected="selected"'; } ?>><?php _e('Before', 'wp_pizzeria'); ?></option>
							<option value="after"<?php if ( $pizzeria_settings['currency_pos'] == 'after' ){ echo ' selected="selected"'; } ?>><?php _e('After', 'wp_pizzeria'); ?></option>
						</select>
						<p class="desctiption">Select position of currency sign relative to price</p>					
					</td>				
				</tr>
			</table>
			<p class="submit">
				<input type="submit" value="<?php _e('Save'); ?>" name="wp_pizzeria_settings_submit" class="button-primary"/>
			</p>
		</form>
	</div>
	<script type="text/javascript" >
		jQuery(document).ready(function($){
			$('#add_pizza_size').click(function(e){
				e.preventDefault();
				$('#sizes').append('<div class="size"><input type="radio" value="" name="primary"/><input type="text" name="size[]" class="new_price" value="" />&nbsp; <span><a href="#" title="<?php _e('Remove', 'wp_pizzeria'); ?>" class="remove"><?php _e('Remove', 'wp_pizzeria'); ?></a></span></div>');
			})
			$('.new_price').live('change', function(){
				var value = $(this).val();
				$(this).siblings('[name="primary"]').val(value);
			});	
			$('.remove').live('click', function(e){
				e.preventDefault();
				$(this).parent().remove();	
			});
		});
	</script>
<?php	
}
?>