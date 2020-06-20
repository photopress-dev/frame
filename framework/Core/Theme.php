<?php

namespace Frame\Core;

define ('FRAME_VERSION', '1.0.0');

// main theme class


class Theme {
	
	public $name;
	
	private $hierarchy;
	
	private $options;
	
	private $customizer;
	
	public function __construct( $args = [] ) {
		
		$this->name = $args['name'] ?: 'untitled';
		$this->version = $args['version'] ?: '1.0.0'; 
	}
	
	public function load() {
		
		$this->setup_hierarchies();
		$this->load_options();
		$this->setup_customizer();
		$this->setup_tgm();
	}
	
	public function setup_hierarchies() {
		
		$this->hierarchy = new \Frame\Core\Hierarchy();
		
	}
	
	public function setup_customizer() {
		
		$this->customizer = new \Frame\Core\Customizer();
	}
	
	public function setup_tgm() {
		
		$tgm = new \Frame\Core\Tgm;
	}
	
	public function load_options() {
		
		$this->options = \Frame\Core\Option::singleton( ['theme_name' => $this->name] );
	}
	
	public static function get_option( $key ) {
		
		$options = \Frame\Core\Option::singleton();
		
		return $options->get( $key );
	}
	
	public static function set_option( $key, $value ) {
		
		$options = \Frame\Core\Option::singleton();
		return $options->set( $key, $value );
	}
	
	// gets the directory of Frame
	public static function get_framework_dir() {
		
		return trailingslashit( get_template_directory() ) . 'frame/framework/';
	}
	
	public static function get_framework_uri( $path ) {
		
		return self::get_parent_theme_uri( 'frame/framework/' . $path );
	}
	
	public static function get_theme_uri( $path = '') {
		
		return get_theme_file_uri( $path );
	}
	
	public static function get_parent_theme_uri( $path = '' ) {
		
		$uri = get_template_directory_uri();
		
		if ( $path ) {
			
			$uri = trailingslashit( $uri ) . $path;
		}
		
		return $uri;
	}
	
}	
	
?>