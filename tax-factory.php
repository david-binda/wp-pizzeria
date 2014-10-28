<?php

abstract class Tax_Factory {
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

	protected static function construct( $obj ) {
		add_action('init', array( $obj, 'register_taxonomy' ), 10, 0 );
	}

	protected function get_taxonomy() {
		return $this->taxonomy;
	}

	protected function get_cpt() {
		return (array) $this->cpt;
	}

	protected function get_rewrite() {
		return $this->rewrite;
	}

	protected function get_category_images() {
		$category_images = maybe_unserialize( get_option( 'wp_pizzeria_pasta_category_images' ) );
		if ( false === is_array( $category_images ) || true === empty( $category_images ) ) {
			$category_images = array();
		}
		return $category_images;
	}

	protected function set_category_images( $category_images ) {
		update_option( 'wp_pizzeria_pasta_category_images', maybe_serialize( $category_images ) );
	}

	public function register_taxonomy() {

		//register custom post type using before declared labels
		register_taxonomy(
			$this->get_taxonomy(),
			$this->get_cpt(),
			array(
				'hierarchical' => true,
				'labels' => $this->get_labels(),
				'show_ui' => true,
				'query_var' => true,
				'rewrite' => $this->get_rewrite()
			)
		);
	}

	abstract protected function get_labels();

}