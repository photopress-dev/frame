<?php

namespace Frame\Customizer\Fonts;
	
class Google {
	
	/**
	 * Default Google font setting value
	 */
	public static $default_font_setting = [

	    'font' => 'Arial',
	    'regularweight' => 'regular',
	    'italicweight' => 'italic',
	    'boldweight' => '700',
	    'category' => 'sans-serif'
	];
	
	/**
	 * Return the list of Google Fonts from our json file. Unless otherwise specfied, list will be limited to 30 fonts.
	 * Google Fonts json generated from https://www.googleapis.com/webfonts/v1/webfonts?sort=popularity&key=YOUR-API-KEY
	 */
	public static function get_font_list( $count = 30, $order = 'all' ) {
		
		if ( false === get_transient( 'frame_font_google_fonts_list' ) ) {
			
			$fontFile = \Frame\Core\Theme::get_framework_uri( 'Customizer/Fonts/google-fonts-alphabetical.json' );
			
			if ( $order === 'popular' ) {
				
				$fontFile = \Frame\Core\Theme::get_framework_uri( 'Customizer/Fonts/google-fonts-popularity.json' );
			}
	
			$request = wp_remote_get( $fontFile );
			
			if( is_wp_error( $request ) ) {
				return "";
			}
	
			$body = wp_remote_retrieve_body( $request );
			// Set transient for google fonts
			set_transient( 'frame_font_google_fonts_list', $body, 2 * DAY_IN_SECONDS );
			
		} else {
			
			$body = get_transient( 'frame_font_google_fonts_list' );
		} 
			
		$content = json_decode( $body );

		if( $count == 'all' ) {
			
			return $content->items;
			
		} else {
			
			return array_slice( $content->items, 0, $count );
		}
	}
		
}	
	
?>