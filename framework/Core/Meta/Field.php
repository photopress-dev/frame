<?php

namespace Frame\Core\Meta;

class Field {
	
	public $properties;
	
	public function __construct( $properties ) {
		
		$defaults = [
			
			'id'		=> '',
			'parent_id' => '',
			'value'		=> ''
			
		];
		
		$this->properties = wp_parse_args( $properties, $defaults );
	}
	
	public function render() {
		
	}
	
	public function sanitize( $value ) {
		
		return $value;
	}
	
	public function set_value( $value ) {
		
		$this->properties['value'] = $this->sanitize( $value );
	}
	
	public function get_value() {
		
		return $this->properties['value'];
	}
}	
	
?>