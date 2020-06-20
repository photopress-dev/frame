<?php
	
namespace Frame\Core;

class Util {
	
	static public function rekey_as_needles( $args ) {
		
		$needles = [];
		
		array_walk( $args, function( &$v, &$k ) use (&$needles) {
			
			$needles[ "{". $k . "}" ] = $v;
			
		});
		
		return $needles;
	}
}

?>