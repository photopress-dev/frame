<?php
	
namespace Frame\Core;

/**
 * Media functions.
 */
class Media {
	
	public static function display_attachment_image( array $args = [] ) {
		
		$args = wp_parse_args( $args, [
			'size'   => null,
			'__'  => ''
		] );

		
		$post_id   = get_the_ID();
		
		echo wp_get_attachment_image( $post_id, $args['size'], '', array('class' => 'image-single') );

		//echo wp_get_attachment_image( $post_id, $args['size'], '', array('class' => '') );
	}
	
	public static function render_caption( $post_id = '') {
		
		return wp_get_attachment_caption( $post_id );
	}
	
	public static function display_caption( $post_id = '') {
		
		echo self::render_caption( $post_id );
	}
	
	public static function render_description( $post_id = '' ) {
		
		return get_the_content( $post_id );
	}
	
	public static function display_description( $post_id = '') {
		
		echo self::render_description( $post_id );
	}
	
	public static function check_orientation( $orientation = 'landscape' ) {
		
		$img = self::get_image_src();
	
		$width = $img[1];
		$height = $img[2];

		switch ( $orientation ) {
			
			case 'landscape':
				
				if ( $width > $height ) {
					return true;
				}
				
				break;
				
			case 'portrait':
			
				if ( $width < $height ) {
					return true;
				}
				
				break;
			
		}
	}
	
	public static function is_portrait_orientation() {
		
		return self::check_orientation('portrait');
	}
	
	public static function is_landscape_orientation() {
		
		return self::check_orientation('landscape');
	}

	public static function get_image_src() {
		
		return wp_get_attachment_image_src( get_the_ID(), 'fullsize' );
	}
	
	public static function get_all_image_sizes() {
		
		$default = get_intermediate_image_sizes();
		
		foreach ( $default as $size ) {
			
	        $sizes[ $size ][ 'width' ] = intval( get_option( "{$size}_size_w" ) );
	        $sizes[ $size ][ 'height' ] = intval( get_option( "{$size}_size_h" ) );
		}
		
		$sizes['fullsize'] = ['height' => '', 'width' => ''];

		$custom  = wp_get_additional_image_sizes();
		
		return array_merge( $sizes, $custom );
	}
}


?>