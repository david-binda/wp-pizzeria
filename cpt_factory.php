<?php

abstract class CPT_Factory {
	private static $_instances = array();
	public static function getInstance() {
		$class = get_called_class();
		if (!isset(self::$_instances[$class])) {
			self::$_instances[$class] = new $class();
		}
		return self::$_instances[$class];
	}

	protected static function construct( $obj ) {
		add_action( 'init', array( $obj, 'register_post_type' ), 10, 0 );

		add_action( 'save_post', array( $obj, 'save_postdata' ), 10, 1 );
		add_action( 'dashboard_glance_items', array( $obj, 'add_counts' ), 10, 0 );
		add_filter( "manage_edit-{$obj->post_type}_columns", array( $obj, 'edit_columns' ), 10, 1 );
		add_action( "manage_{$obj->post_type}_posts_custom_column", array( $obj, 'manage_columns' ), 10, 2 );
	}
	private function __clone() {
	}

	private function __wakeup() {
	}

	public function add_counts() {
		if ( false === post_type_exists( $this->post_type ) ) {
			return;
		}

		$post_type_obj = get_post_type_object( $this->post_type );

		$num_posts = wp_count_posts( $this->post_type );
		$num       = number_format_i18n( $num_posts->publish );
		$text      = _n( $post_type_obj->labels->singular_name, $post_type_obj->labels->name, intval( $num_posts->publish ) );
		if ( current_user_can( 'edit_posts' ) ) {
			$text = sprintf( '<a href="edit.php?post_type=%s">%d %s</a>', $this->post_type, $num, $text );
		}
		echo sprintf( '<li class="t %s">%s</li>', $this->post_type, $text );

	}

	public function edit_columns( $columns ) {

		$columns = array(
			'cb'          => '<input type="checkbox" />',
			'menu_number' => strip_tags( __( '#', 'wp_pizzeria' ) ),
			'title'       => strip_tags( __( 'Title' ) ),
			'category'    => strip_tags( __( 'Category', 'wp_pizzeria' ) ),
			'price'       => strip_tags( __( 'Price', 'wp_pizzeria' ) ),
			'date'        => strip_tags( __( 'Date' ) )
		);

		return $columns;
	}

	public function manage_columns( $column, $post_id ) {
		global $post;
		switch ( $column ) {
			case 'menu_number' :
				global $wpdb;
				$menu_id = $wpdb->get_var( $wpdb->prepare( "SELECT menu_order FROM $wpdb->posts WHERE ID = %d LIMIT 1", $post_id ) );
				echo $menu_id;
				break;
			case 'category' :
				$terms = get_the_terms( $post_id, $this->post_type . '_category' );
				if ( false === empty( $terms ) ) {
					$out = array();
					foreach ( $terms as $term ) {
						$out[] = sprintf( '<a href="%s">%s</a>',
							esc_url(
								add_query_arg( array(
									'post_type' => $post->post_type,
									$this->post_type . '_category' => $term->slug
								) , 'edit.php' )
							),
							esc_html( sanitize_term_field( 'name', $term->name, $term->term_id, $this->post_type . '_category', 'display' ) )
						);
					}
					echo join( ', ', $out );
				} else {
					_e( 'No Categories', 'wp_pizzeria' );
				}
				break;

			case 'price' :
				$pizzeria_settings = maybe_unserialize( get_option( 'wp_pizzeria_settings' ) );
				if ( false !== get_post_meta( $post_id, '_wp_pizzeria_price', true ) ) {
					if ( true === array_key_exists( 'currency', $pizzeria_settings )
					     && true === array_key_exists( 'currency_pos', $pizzeria_settings )
					     && 'before' === $pizzeria_settings['currency_pos'] )
					{
						echo $pizzeria_settings['currency'];
					}
					echo get_post_meta( $post_id, '_wp_pizzeria_price', true );

					if ( true === array_key_exists( 'currency', $pizzeria_settings )
					     && ( false === array_key_exists( 'currency_pos', $pizzeria_settings )
					          || 'after' === $pizzeria_settings['currency_pos'] )
					) {
						echo $pizzeria_settings['currency'];
					}
				} else {
					echo '';
				}
				break;
			default :
				break;
		}
	}
}