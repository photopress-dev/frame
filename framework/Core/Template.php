<?php
	
namespace Frame\Core;

class Template {
	
	static function display( $slug, $post_type = '', $params = [] ) {
		
		$post_type = $post_type ?: get_post_type();
		
		$standard_components = ['header', 'footer', 'sidebar'];
		
		
		
		if ( in_array($slug, $standard_components ) ) {
			do_action( "get_{$slug}", $slug );
			
		} else {
			do_action( "get_template_part_{$slug}", $slug, $post_type );
			//get_template_part( \Frame\Core\Theme::get_option('template_dir_path') . "/partials/$slug", $post_type );			
		}
		
		self::get_standard_component($slug, $post_type, $params);
	}
	
	static function get_standard_component($type, $name, $params = [] ) {
				

		$path = \Frame\Core\Theme::get_option('template_dir_path') ;
		
		// add directory 
		if ( ! strpos( $type, '/') ) {
			
			$path .= '/'.$type; 
		}
	
	    $templates = array();
	    $name      = (string) $name;
	    if ( '' !== $name ) {
	        $templates[] = "$path/$type-{$name}.php";
	    }
	 
	    $templates[] = "$path/$type.php";
		
		if ($params) {
			
			$params = ['params' => (object) $params];
			
			extract( $params );
		}
		
	
		
		$located = '';
		$load = true;
	    foreach ( (array) $templates as $template_name ) {
	        if ( ! $template_name ) {
	            continue;
	        }
	        if ( file_exists( STYLESHEETPATH . '/' . $template_name ) ) {
	            $located = STYLESHEETPATH . '/' . $template_name;
	            break;
	        } elseif ( file_exists( TEMPLATEPATH . '/' . $template_name ) ) {
	            $located = TEMPLATEPATH . '/' . $template_name;
	            break;
	        } elseif ( file_exists( ABSPATH . WPINC . '/theme-compat/' . $template_name ) ) {
	            $located = ABSPATH . WPINC . '/theme-compat/' . $template_name;
	            break;
	        }
	    }
	
	    if ( $load && '' != $located ) {
	        
	        $_template_file = $located;
	        
	        global $posts, $post, $wp_did_header, $wp_query, $wp_rewrite, $wpdb, $wp_version, $wp, $id, $comment, $user_ID;
 
		    if ( is_array( $wp_query->query_vars ) ) {
		        /*
		         * This use of extract() cannot be removed. There are many possible ways that
		         * templates could depend on variables that it creates existing, and no way to
		         * detect and deprecate it.
		         *
		         * Passing the EXTR_SKIP flag is the safest option, ensuring globals and
		         * function variables cannot be overwritten.
		         */
		        // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		        extract( $wp_query->query_vars, EXTR_SKIP );
		    }
		 
		    if ( isset( $s ) ) {
		        $s = esc_attr( $s );
		    }
		 
		    
		    require $_template_file;
		    
	    }
	 
	    return $located;
		
	    //locate_template( $templates, true );
	}
}

?>