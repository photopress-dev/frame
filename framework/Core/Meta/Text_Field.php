<?php

namespace Frame\Core\Meta;

class Text_Field extends Field {
	
	public function __construct( $properties ) {
		
		$defaults = [
			
			'size'	=> '20'
			
		];
		
		$properties = wp_parse_args( $properties, $defaults );
		
		parent::__construct( $properties );
	}
	
	public function render() {
		
		$needles = \Frame\Core\Util::rekey_as_needles( $this->properties );
		
		// populate template for label
		$form_field = sprintf('<label for="%s_field">%s</label>', $field['id'], _e( $field['description'] ) );
		
		
		$form_field .= sprintf( strtr( '<input type="{type}" id="{dom_id}" name="%s" value="{value}" size="{size}" />', $needles ), $this->properties['parent_id'] .'['. $this->properties['id'] . ']');
		return $form_field;

	}
	
	public function sanitize( $value ) {
		
		return $value;
	}
}	
	
?>