<?php

namespace Frame\Core;

class Site {
	
	public static function render_name( $args = [] ) {
		
		$defaults = [
			'tag'		=> is_front_page() ? 'h1' : 'div',
			'class'		=> 'site-description',
			'link'		=> esc_url( home_url( '/' ) )
		];
		
		$args = wp_parse_args( $args, $defaults );
			
		$args = \Frame\Core\Util::rekey_as_needles( $args );
		
		$name = get_bloginfo( 'name','display' );
		
		return strtr('<{tag} class="{class}"><a href="{link}" rel="home">'. $name .'</a></{tag}>',  $args);
	}
	
	public static function display_name( $args = [] ) {
		
		echo self::render_name( $args );
	}
	
	public static function render_description( $args = [] ) {
		
		$args = wp_parse_args( $args, [
			'tag'   => 'div',
			'class' => 'site-description',
			] );

	
		$d = get_bloginfo( 'description', 'display' );

		if ( $d ) { 
			
			$args = \Frame\Core\Util::rekey_as_needles( $args );
			return strtr('<{tag} class="{class}">'. $d . '</{tag}>', $args);
		}

	}
	
	public static function display_description( $args = [] ) {
		
		echo self::render_description( $args );
	}
	
}

?>