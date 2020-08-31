<?php

namespace Frame\Core;

class Search {
	
	public static function display_pagination( $wp_query = '' ) {
		
		global $wp_query;
		
		$pages = $wp_query->max_num_pages ?: 1;
	
		$paged = $wp_query->query['paged'] > 0 ? $wp_query->query['paged'] : 1;
		
		$range = 7;
		
		$showitems = ( $range * 2 ) + 1;
		
		if ( $pages > 1 ) {
			
			echo "<div class=\"numeric-pagination\">\n";
			
			
			if ( $paged > 2 && $paged > $range + 1 && $showitems < $pages) {
			 
			 echo "<a class=\"item\" href='".get_pagenum_link(1)."'>&laquo; First</a>";
			}
			
			if ( $paged > 1 && $showitems < $pages ) {
			
			  echo "<a class=\"item\" href='".get_pagenum_link( $paged - 1)."'>&lsaquo; Previous</a>";
			}
			
			for ( $i = 1; $i <= $pages; $i++ ) {
			 
			 if (1 != $pages && ( ! ( $i >= $paged + $range + 1 || $i <= $paged - $range - 1) || $pages <= $showitems )) {
			     
			     echo ($paged == $i)? "<span class=\"item current\">".$i."</span>":"<a href='".get_pagenum_link($i)."' class=\"item inactive\">".$i."</a>";
			 }
			}
			
			if ( $paged < $pages && $showitems < $pages) {
			 echo "<a class=\"item\" href=\"".get_pagenum_link($paged + 1)."\">Next &rsaquo;</a>";  
			}
			
			if ( $paged < $pages-1 &&  $paged+$range-1 < $pages && $showitems < $pages) {
			  
			  echo "<a class=\"item\" href='".get_pagenum_link($pages)."'>Last &raquo;</a>";
			}
	     
			echo "</div>\n";
		}	


	}
	
}

?>