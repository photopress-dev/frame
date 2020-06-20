<?php

namespace Frame\Core;

/**
 * Unified interface for working with the theme's options.
 * Options are stored as defaults in code and in WP's options table.
 */
class Option {
	
	/* container for singleton
	 *
	 */
	private static $instance;
	
	/* name of the key for serialized settings in WP's options table
	 *
	 */
	private $db_key;
	
	/* container for option values
	 *
	 */
	private $options;
	
	
	public function __construct( $args ) {
		
		$this->db_key = "photopress_theme_{$args['theme_name']}";
		
		$this->options = array_merge( $this->get_defaults(), $args );
		$this->init();
	}
	
	public static function singleton( $args = []) {
		
		if ( ! self::$instance ) {
			
			self::$instance = new self( $args );
		}
		
		return self::$instance;
	} 
	
	private function init() {
		
		$this->load();
	}
	
	private function get_defaults() {
		
		return [
			
			'template_dir_path' => 'resources/templates',
			'theme_name'		=> '',
			'foo'				=> 'foo'
		];
	}
	
	public function get( $key ) {
		
		if (array_key_exists( $key, $this->options ) ) {
			
			return $this->options[ $key ];			
		}
	}
	
	public function set( $key, $value ) {
		
		$this->options[ $key ] = $value;
	}
	
	private function load() {
		
		//$this->options = $this->get_defaults();
		
		$options = get_option( $this->db_key );
		
		//echo 'options from DB: '. print_r( $options, true );
		if ( $options ) {
			
			$this->options = array_merge($this->options, $options);
		}

	}
	
	public static function get_option( $key ) {
		
		$options = self::singleton();
	
		if (array_key_exists( $key, $options->options ) ) {
			
			return $options->options[ $key ];			
		}
	}
}	

?>