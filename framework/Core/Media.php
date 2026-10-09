<?php
	
namespace Frame\Core;

/**
 * Media functions.
 */
class Media {
	
	public static function display_attachment_image( $args = [] ) {
		
		$args = wp_parse_args( $args, [
			'size'   			=> '',
			'width'	 			=> '',
			'height'			=> '',
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
		
		// The image's shape: from the width and height given, or else from
		// its metadata. Kept apart from the arguments, which, given, set the
		// width it is shown at.
		$width  = $args['width'];
		$height = $args['height'];

		if ( ( empty( $width ) || empty( $height ) ) && is_array( $meta ) ) {
			$width  = isset( $meta['width'] ) ? $meta['width'] : null;
			$height = isset( $meta['height'] ) ? $meta['height'] : null;
		}

		$aspect_ratio = $height > 0 ? intval( $width ) / intval( $height ) : 1;

		if ( $height > 0 && is_single() ) {
			$attrs['class'] .= $aspect_ratio < 1 ? ' portrait-orientation' : ' landscape-orientation';
		}

		/*
		 * sizes: the width the image is shown at, which the browser picks its
		 * file from srcset by. As given (only the theme knows the width of its
		 * column); else the width given, or the width at the height given;
		 * else WordPress's own, the image's width or the window's if less,
		 * never less than it is shown at. vertical_offset no longer sets it:
		 * the image's width attribute, not its sizes, decides how large it is
		 * laid out, so a sizes worked out from the window's height gave
		 * files too small for an image wider than that.
		 */
		if ( $args['sizes'] ) {

			$attrs['sizes'] = $args['sizes'];

		} else if ( $args['width'] ) {

			$attrs['sizes'] = intval( $args['width'] ) . 'px';

		} else if ( $args['height'] ) {

			$attrs['sizes'] = round( intval( $args['height'] ) * $aspect_ratio ) . 'px';
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
		
		if ( is_array( $meta ) && array_key_exists( 'file', $meta ) && ! empty( $meta['file'] ) ) {
		
			$file = basename($meta['file']);
		
			return $file;
		}	
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