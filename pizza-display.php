<?php
class WP_Pizzeria_Pizza_Display {

	public static function getInstance() {
		static $instance = null;
		if ( null === $instance ) {
			$instance = new static();
		}

		return $instance;
	}

	protected function __construct() {
		add_shortcode( 'pizzas', array( $this, 'display' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );
	}

	private function __clone() {
	}

	private function __wakeup() {
	}


	public function display( $atts ) {
		extract( shortcode_atts( array(
			'cat' => 'wp_pizzeria_nocat',
		), $atts ) );
		$output = '';

		//output ingredience filter
		$output .= '<div class="filter-ingrediences">';
		$ingrediences      = get_terms( 'wp_pizzeria_ingredient' );
		$ingredient_images = maybe_unserialize( get_option( 'wp_pizzeria_ingredient_images' ) );
		if ( ! empty( $ingrediences ) ) {
			$output .= '<ul class="wp-pizzeria-ingredienceFilter">';
			foreach ( $ingrediences as $ingredience ) {
				$output .= '<li>';
				if ( array_key_exists( $ingredience->term_id, (array) $ingredient_images ) ) {
					$output .= '<span class="pizza-image-wrapper"><img src="' . $ingredient_images[ $ingredience->term_id ] . '" alt="' . $ingredience->name . '"/></span>';
				}
				$output .= '<input type="checkbox" value="' . $ingredience->term_id . '" name="ingredienceFilter[' . $ingredience->slug . ']" id="ingredienceFilter[' . $ingredience->slug . ']" class="' . $ingredience->slug . '">';
				$output .= '<label for="ingredienceFilter[' . $ingredience->slug . ']">' . $ingredience->name . '</label>';
				$output .= '</li>';
			}
			$output .= '</ul>';
		}
		$output .= '</div>';

		/* Save original post*/
		global $post;
		$tmp_post = $post;

		/* Loop all pizzas */
		$pizzeria_settings = maybe_unserialize( get_option( 'wp_pizzeria_settings' ) );
		if ( ! array( $pizzeria_settings ) ) {
			$pizzeria_settings = array();
		}
		$args = array( 'post_type'      => 'wp_pizzeria_pizza',
		               'posts_per_page' => - 1,
		               'orderby'        => 'menu_order',
		               'order'          => 'ASC'
		);
		if ( $cat != 'wp_pizzeria_nocat' ) {
			$args['wp_pizzeria_category'] = $cat;
		}
		$pizzas = new WP_Query( $args );
		$output .= '<table class="wp-pizzeria pizzas">' . "\n\t<thead>";
		$table_footer_header = "\n\t\t<tr>";
		$table_footer_header .= "\n\t\t\t" . '<th class="col1 menu-number">' . __( '#', 'wp_pizzeria' ) . '</th>';
		$table_footer_header .= "\n\t\t\t" . '<th class="col2 title">' . __( 'Title', 'wp_pizzeria' ) . '</th>';
		$table_footer_header .= "\n\t\t\t" . '<th class="col3 description">' . __( 'Description', 'wp_pizzeria' ) . '</th>';
		//$table_footer_header .= "\n\t\t\t" . '<th class="col4 thumb">'.__('Image', 'wp_pizzeria').'</th>';
		$table_footer_header .= "\n\t\t\t" . '<th class="col5 ingrediences">' . __( 'Ingrediences', 'wp_pizzeria' ) . '</th>';
		if ( is_array( $pizzeria_settings['sizes'] ) ) {
			$i = 6;
			foreach ( $pizzeria_settings['sizes'] as $key => $size ) {
				if ( $key == 'primary' ) {
					continue;
				}
				$table_footer_header .= "\n\t\t\t" . '<th class="col' . $i . ' ' . $key . '">' . $size . '</th>';
				$i ++;
			}
		}
		//$table_footer_header .= "\n\t\t\t" . '<th class="col'.$i.' add-to-cart">'.__('Add to cart', 'wp_pizzeria').'</th>';
		$table_footer_header .= "\n\t\t</tr>";
		$output .= $table_footer_header;
		$output .= "\n\t</thead>\n\t<tfoot>";
		$output .= $table_footer_header;
		$output .= "\n\t</tfoot>\n\t<tbody>";
		unset( $table_footer_header );
		$even = true;
		while ( $pizzas->have_posts() ) {
			$pizzas->the_post();
			$ingrediences = wp_get_post_terms( get_the_ID(), 'wp_pizzeria_ingredient' );
			if ( $even == true ) {
				$class = 'pizza even';
			} else {
				$class = '$pizza odd';
				$even  = true;
			}
			if ( ! empty( $ingrediences ) ) {
				foreach ( $ingrediences as $ingredience ) {
					$class .= ' ' . $ingredience->slug;
				}
			}
			$output .= "\n\t\t" . '<tr class="' . $class . '">';
			global $post;
			extract( shortcode_atts( array(
				'cat' => 'wp_pizzeria_nocat',
			), $atts ) );
			$output .= "\n\t\t\t" . '<td class="col1 menu-number">' . $post->menu_order . '</td>';
			$output .= "\n\t\t\t" . '<td class="col2 title">';
			$output .= '<a href="#" class="pizza-title">' . get_the_title() . '</a>';
			$output .= get_the_post_thumbnail( get_the_ID(), 'wp_pizzeria_thumbnail' );
			$output .= '</td>';
			$output .= "\n\t\t\t" . '<td class="col3 description"><div class="content">' . apply_filters( 'the_content', get_the_content() ) . '</div></td>';
			//$output .= "\n\t\t\t" . '<td class="col4 thumb">' . get_the_post_thumbnail( get_the_ID(), 'wp_pizzeria_thumbnail' ) . '</td>';
			$output .= "\n\t\t\t" . '<td class="col5 ingrediences">';

			$ingrediences_array = array();
			foreach ( $ingrediences as $ingredience ) {
				$ingrediences_array[] = $ingredience->name;
			}
			$output .= implode( ', ', $ingrediences_array );
			$output .= "\n\t\t\t" . '</td>';
			/* manage prices */
			unset( $prices );
			$prices = maybe_unserialize( get_post_meta( get_the_ID(), '_wp_pizzeria_prices', true ) );
			if ( ! is_array( $prices ) ) {
				$prices = array();
			}
			if ( is_array( $pizzeria_settings['sizes'] ) ) {
				$i = 6;
				foreach ( $pizzeria_settings['sizes'] as $key => $size ) {
					if ( $key == 'primary' ) {
						continue;
					}
					$output .= "\n\t\t\t" . '<td class="col' . $i . ' price ' . $key . '">';
					if ( array_key_exists( $key, $prices ) ) {
						if ( array_key_exists( 'currency', $pizzeria_settings ) && array_key_exists( 'currency_pos', $pizzeria_settings ) && $pizzeria_settings['currency_pos'] == 'before' ) {
							$output .= $pizzeria_settings['currency'];
						}
						$output .= $prices[ $key ];
						if ( array_key_exists( 'currency', $pizzeria_settings ) && ( ! array_key_exists( 'currency_pos', $pizzeria_settings ) || $pizzeria_settings['currency_pos'] == 'after' ) ) {
							$output .= $pizzeria_settings['currency'];
						}
						$output .= '</td>';
					}
					$i ++;
				}
			}
			$output .= "\n\t\t" . '</tr>';
		}
		$output .= "\n\t</tbody>\n";
		$output .= '</table>';

		/* Restore original post */
		$post = $tmp_post;

		return $output;
	}

	public function scripts() {
		wp_enqueue_script( 'filter-pizzas', plugins_url( '/js/filter-pizzas.js', __FILE__ ), array( 'jquery' ) );
	}

	function loop() {
		global $wp_query;
		$args = array_merge( $wp_query->query_vars, array( 'orderby' => 'menu_order', 'order' => 'ASC' ) );
		query_posts( $args );
		if ( have_posts() ) {
			$pizzeria_settings = maybe_unserialize( get_option( 'wp_pizzeria_settings' ) );
			if ( ! array( $pizzeria_settings ) ) {
				$pizzeria_settings = array();
			}
			?>

			<div class="filter-ingrediences">
				<?php $ingrediences = get_terms( 'wp_pizzeria_ingredient' );
				$ingredient_images  = maybe_unserialize( get_option( 'wp_pizzeria_ingredient_images' ) );
				if ( ! empty( $ingrediences ) ) : ?>
					<ul class="wp-pizzeria-ingredienceFilter">
						<?php
						foreach ( $ingrediences as $ingredience ) : ?>
							<li>
								<?php
								if ( array_key_exists( $ingredience->term_id, (array) $ingredient_images ) ) : ?>
									<span class="pizza-image-wrapper"><img
											src="<?php echo $ingredient_images[ $ingredience->term_id ]; ?>"
											alt="<?php echo $ingredience->name; ?>"/></span>
								<?php endif; ?>
								<input type="checkbox" value="<?php echo $ingredience->term_id; ?>"
								       name="ingredienceFilter[<?php echo $ingredience->slug; ?>]"
								       id="ingredienceFilter[<?php echo $ingredience->slug; ?>]"
								       class="<?php echo $ingredience->slug; ?>">
								<label
									for="ingredienceFilter[<?php echo $ingredience->slug; ?>]"><?php echo $ingredience->name; ?></label>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
			<table class="wp-pizzeria pizzas">
				<thead>
				<tr>
					<th class="col1 menu-number">#</th>
					<th class="col2 title"><?php _e( 'Title', 'wp_pizzeria' ); ?></th>
					<th class="col3 description hidden"><?php _e( 'Description', 'wp_pizzeria' ); ?></th>
					<th class="col5 ingrediences"><?php _e( 'Ingrediences', 'wp_pizzeria' ); ?></th>
					<?php if ( is_array( $pizzeria_settings['sizes'] ) ) {
						$i = 6;
						foreach ( $pizzeria_settings['sizes'] as $key => $size ) {
							if ( $key == 'primary' ) {
								continue;
							} ?>
							<th class="col<?php echo $i; ?> <?php echo $key; ?>"><?php echo $size; ?></th>
							<?php
							$i ++;
						}
					} ?>
				</tr>
				</thead>
				<tfoot>
				<tr>
					<th class="col1 menu-number">#</th>
					<th class="col2 title"><?php _e( 'Title', 'wp_pizzeria' ); ?></th>
					<th class="col3 description hidden"><?php _e( 'Description', 'wp_pizzeria' ); ?></th>
					<th class="col5 ingrediences"><?php _e( 'Ingrediences', 'wp_pizzeria' ); ?></th>
					<?php if ( is_array( $pizzeria_settings['sizes'] ) ) {
						$i = 6;
						foreach ( $pizzeria_settings['sizes'] as $key => $size ) {
							if ( $key == 'primary' ) {
								continue;
							} ?>
							<th class="col<?php echo $i; ?> <?php echo $key; ?>"><?php echo $size; ?></th>
							<?php
							$i ++;
						}
					} ?>
				</tr>
				</tfoot>
				<tbody>
				<?php $odd = true;
				while ( have_posts() ) : the_post();
					$ingrediences = wp_get_post_terms( get_the_ID(), 'wp_pizzeria_ingredient' ); ?>
					<tr class="pizza<?php if ( $odd ) {
						echo ' odd ';
						$odd = false;
					} else {
						echo ' even';
						$odd = true;
					}
					foreach ( $ingrediences as $ingredience ) {
						echo ' ' . $ingredience->slug;
					} ?>">
						<td class="col1 menu-number"><?php global $post;
							echo $post->menu_order; ?></td>
						<td class="col2 title"><a class="pizza-title" href="#"><?php the_title(); ?></a></td>
						<td class="col3 description hidden">
							<div class="content"><?php the_content(); ?></div>
						</td>
						<td class="col5 ingrediences">
							<?php
							$ingrediences_array = array();
							foreach ( $ingrediences as $ingredience ) {
								$ingrediences_array[] = $ingredience->name;
							}
							echo implode( ', ', $ingrediences_array ); /* ?>
					<ul>
						<?php foreach ($ingrediences as $ingredience): ?>
						<li>
							<?php //<label for="ingrediences[<?php echo $ingredience->slug; ?>]"><input type="checkbox" id="ingrediences[<?php echo $ingredience->slug; ?>]" name="ingrediences[<?php echo $ingredience->slug; ?>]" value="4" class="wp-pizzeria-ingredience <?php echo $ingredience->slug; ?>"><?php echo $ingredience->name; ?></label> ?>
							<?php echo $ingredience->name; ?>
						</li>
						<?php endforeach; ?>
					</ul> */
							?>
						</td>
						<?php
						unset( $prices );
						$prices = maybe_unserialize( get_post_meta( get_the_ID(), '_wp_pizzeria_prices', true ) );
						if ( ! is_array( $prices ) ) {
							$prices = array();
						}
						if ( is_array( $pizzeria_settings['sizes'] ) ) {
							$i = 6;
							foreach ( $pizzeria_settings['sizes'] as $key => $size ) {
								if ( $key == 'primary' ) {
									continue;
								} ?>
								<td class="col<?php echo $i; ?> price <?php echo $key; ?>">
									<?php
									if ( array_key_exists( $key, $prices ) ) {
										if ( array_key_exists( 'currency', $pizzeria_settings ) && array_key_exists( 'currency_pos', $pizzeria_settings ) && $pizzeria_settings['currency_pos'] == 'before' ) {
											echo $pizzeria_settings['currency'];
										}
										echo $prices[ $key ];
										if ( array_key_exists( 'currency', $pizzeria_settings ) && ( ! array_key_exists( 'currency_pos', $pizzeria_settings ) || $pizzeria_settings['currency_pos'] == 'after' ) ) {
											echo $pizzeria_settings['currency'];
										}
									} ?>
								</td>
								<?php
								$i ++;
							}
						}
						?>
					</tr>
				<?php endwhile; ?>
				</tbody>
			</table>
			<?php
		}
	}
}

function pizza_loop() {
	WP_Pizzeria_Pizza_Display::getInstance()->loop();
}