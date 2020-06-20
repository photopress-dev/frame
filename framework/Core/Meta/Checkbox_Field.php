<?php

namespace Frame\Core\Meta;

class Checkbox_Field extends Field {
	
	public function __construct( $properties ) {
		
		$defaults = [
			
			'size'	=> '20'
			
		];
		
		$properties = wp_parse_args( $properties, $defaults );
		
		parent::__construct( $properties );
	}
	
	public function render() {
		
		$needles = \Frame\Core\Util::rekey_as_needles( $this->properties );
		
		$checked = checked( $this->get_value(), 1, false );
			
		return sprintf( 
					strtr( 
						'<input type="{type}" id="{dom_id}" name="%s" value="1" %s /> {description}', $needles 
					), 
					$this->properties['parent_id'] .'['. $this->properties['id'] . ']', $checked
				);
		
	}
	
	public function sanitize( $value ) {
		
		return $value;
	}
}	
	
?>