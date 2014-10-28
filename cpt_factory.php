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

	static private function throw_singleton_error() {
		return new WP_Error( 'direct_access_prohibit', __( 'This class is a singleton. Use ::getInstance instead of trying to create new object.', 'wp_pizzeria' ) );
	}

	private function __clone() {
		return self::throw_singleton_error();
	}

	private function __wakeup() {
		return self::throw_singleton_error();

	}

	abstract public function save_postdata( $post_id );

	protected static function construct( $obj ) {
		add_action( 'init', array( $obj, 'register_post_type' ), 10, 0 );

		add_action( 'save_post', array( $obj, 'save_postdata' ), 10, 1 );
		add_action( 'wp_insert_post_data', array( $obj, 'save_menu_number' ), 99, 2 );
		add_action( 'dashboard_glance_items', array( $obj, 'add_counts' ), 10, 0 );
		add_filter( "manage_edit-{$obj->post_type}_columns", array( $obj, 'edit_columns' ), 10, 1 );
		add_action( "manage_{$obj->post_type}_posts_custom_column", array( $obj, 'manage_columns' ), 10, 2 );
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
			$edit_url = add_query_arg( array( 'post_type' => $this->post_type ), admin_url( 'edit.php' ) );
			$text = sprintf( '<a href="%s">%d %s</a>', $edit_url, $num, $text );
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

	protected function can_save( $post_id ) {
		if ( true === defined( 'DOING_AUTOSAVE' ) && true ===  constant( 'DOING_AUTOSAVE' ) ) {
			return false;
		}
		if ( $this->post_type !== get_post_type( $post_id ) ) {
			return false;
		}
		if ( false === current_user_can( 'edit_post', $post_id ) ) {
			return false;
		}
		return true;
	}

	public static function get_pizzeria_settings() {
		return (array) maybe_unserialize( get_option( 'wp_pizzeria_settings' ) );
	}

	public function number_inner_custom_box( $post ) {
		$number = $post->menu_order;
		$post_type_obj = get_post_type_object( $this->post_type );
		$label = sprintf( __( '%s menu number' ), $post_type_obj->labels->singular_name );
		echo sprintf( '<label for="wp_pizzeria_number">%s</label><input type="text" name="wp_pizzeria_number" value="%d"/>', esc_html( $label ), intval( $number ) );
	}


	function save_menu_number( $data, $postarr ) {
		if ( false !== $this->can_save( $postarr['ID'] ) ) {
			if ( true === isset( $_POST['wp_pizzeria_number'] ) ) {
				$data['menu_order'] = intval( $_POST['wp_pizzeria_number'] );
			}
		}
		return $data;
	}
}