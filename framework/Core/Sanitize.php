<?php

namespace Frame\Core;

class Sanitize {
	
	public static function to_string( $input ) {
		
		$input .= '';
		$input = trim($input);
		
		return $input;
	}
	
	public static function to_bool( $input ) {
		
		if ( true === $input ) {
	    	return 1;
		} else {
	    	return 0;
		}
	}
	
	public static function to_int( $input ) {
		
		return absint( $input );
	}
	
	
}	
	
?>