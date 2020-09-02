<?php
	
namespace Frame\Core;

/**
 * Media functions.
 */
class Media {
	
	private static function calc_aspect_ratio( $width, $height) {
		
		return isset( $width ) ? round( intval( $width ) / intval( $height ), 2 ) : '';
	}
	
	public static function display_attachment_image( $args = [] ) {
		
		$args = wp_parse_args( $args, [
			'size'   			=> '',
			'width'	 			=> '',
			'sizes'	 			=> '',
			'vertical_offset' 	=> apply_filters('frame/media/vertical_offset', 0),
			'horizontal_offset' => apply_filters('frame/media/horizontal_offset', 0),
			'class'				=> 'image-single',
			'link'				=> true,
			'__'  				=> ''
		] );
		
		$post_id = get_the_ID();
		
		$meta = wp_get_attachment_metadata( $post_id );
		
		$attrs = [];
		
		$attrs['class'] = 'wp-image ' . $args['class'];
		
		$aspect_ratio = self::calc_aspect_ratio( $meta['width'], $meta['height'] );
		
		if ( is_single() ) {
			if ( $aspect_ratio < 1 ) {
				
				$attrs['class'] = $attrs['class'] . ' portrait-orientation';
							
			} else {
				
				$attrs['class'] = $attrs['class'] . ' landscape-orientation';
				
			}
		}
		
		$vertical_offset = $args['vertical_offset'];
		
		// calc size based on keeping image above the fold.
		if ( $args['width'] ) {
			
			$attrs['sizes'] = $args['width'] .'px';
			
		} else if  ( $args['height'] ) {
			
			$attrs['sizes'] = round( intval( $args['height'] ) * $aspect_ratio) .'px';
			
		} else if ( ! $args['width'] || ! $args['height'] ) {
			
			$attrs['sizes'] = "calc( (100vh - $vertical_offset ) * $aspect_ratio )";	
		} 
		
		$img = wp_get_attachment_image( $post_id, $args['size'], '', $attrs );
		
		
		$markup = '';
		if ( $args['link'] ) {
			
			$url = get_attachment_link( $post_id );
			
			$markup = sprintf('<a href="%s">%s</a>', esc_url($url), $img ) ;
			
		} else {
			
			$markup = $img;
		}
		
		echo apply_filters( 'frame/attachment/image_markup', $markup, $post_id );
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
	
	public static function get_file_name( $id = '' ) {
		
		if ( ! $id ) {
			
			$id = get_the_ID();
		}
		
		$meta = wp_get_attachment_metadata( $id );
		
		$file = basename($meta['file']);
		
		return $file;
		
	}
	
	public static function display_file_name( $id = '') {
		
		echo self::get_file_name( $id );
	}
	
	static function render_title( array $args = [] ) {

		$post_id   = get_the_ID();

		$args = wp_parse_args( $args, [
			'text'   => '%s',
			'tag'    => 'div',
			'link'   => true,
			'class'  => 'attachment-title',
			'before' => '',
			'after'  => ''
		] );
	
		$text = sprintf( $args['text'], the_title( '', '', false ) );
	
		if ( $args['link'] ) {
			$text = self::render_permalink( [ 'text' => $text ] );
		
		}
	
		$html = sprintf(
			'<%1$s class="%2$s">%3$s</%1$s>',
			tag_escape( $args['tag'] ),
			esc_attr( $args['class'] ),
			$text
		);
	
		return apply_filters( 'frame/media/title', $args['before'] . $html . $args['after'] );
	}
	
	static function display_title( $args = []) {
		
		echo self::render_title( $args );
	}
	
	static function render_permalink( array $args = [] ) {

		$args = wp_parse_args( $args, [
			'text'   => '%s',
			'class'  => '',
			'before' => '',
			'after'  => ''
		] );
	
		$url = get_permalink();
	
		$html = sprintf(
			'<a class="%s" href="%s">%s</a>',
			esc_attr( $args['class'] ),
			esc_url( $url ),
			sprintf( $args['text'], esc_url( $url ) )
		);
	
		return apply_filters( 'frame/media/permalink', $args['before'] . $html . $args['after'] );
	}
}


?>