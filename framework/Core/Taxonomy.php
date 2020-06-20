<?php
	
namespace Frame\Core;

class Taxonomy {
	
	public static function get_slug() {
		
		return get_query_var( 'taxonomy' );
	}
	
	/**
	 * Displays the label for a specific Taxonomy
	 *
	 * @param	$taxonmy_name	string	the name of the taxonomy
	 */
	public static function get_label($taxonomy_name = '') {
		
		if ( ! $taxonomy_name ) {
			
			$taxonomy_name = self::get_slug();
		}
		
		$t = get_taxonomy( $taxonomy_name );

		return $t->label;
	}
	
	public static function display_label( $taxonomy_name = '' ) {
		
		echo self::get_label( $taxonomy_name );
	}
	
	/**
	 * Returns the object for a specific Taxonomy
	 *
	 * @param	$taxonmy_name	string	the name of the taxonomy
	 */
	public static function get_object( $taxonomy_name = '' ) {
		
		if ( ! $taxonomy_name ) {
			
			$taxonomy_name = self::get_slug();
		}
		
		$t = get_taxonomy($taxonomy_name);
		
		return $t->object_type;
	}
	
	public static function is_image_taxonomy() {
		
		if ( in_array( 'attachment', self::get_object() ) ) {
			
			return true;
		}
	}
		
	
	
}

	
?>