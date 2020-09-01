<?php
	
namespace Frame\Core;

class Hierarchy {
	
	protected $types = [
	       'index',
	       'single',
	       'singular',
	       'page',
	       'attachment',
	       'taxonomy',
	       '404',
	       'archive',
	       'author',
	       'category',
	       'tag',
	       'date',
	       'embed',
	       'home',
	       'frontpage',
	       'paged',
	       'search',
	       'searchform'
	];
	
	public function __construct() {
		
		$this->init();
	}
	
	public function init() {
		
		// Filter the single, page, and attachment templates.
		add_filter( 'single_template_hierarchy',     [ $this, 'single' ], 5 );
	
		// System for capturing the template hierarchy.
		foreach ( $this->types as $type ) {
			
			add_filter( 'comments_template', [ $this, 'comments_template' ], 5 );
			add_filter( "{$type}_template_hierarchy", [ $this, 'set_template_location' ], PHP_INT_MAX );
			add_filter( 'single_template', function( $template ) {
			
				return $template;
			});
		}
	}
	
	
	public function single( $templates ) {
		
		$templates = [];

		// Get the queried post.
		$post = get_queried_object();

		// Decode the post name.
		$name = urldecode( $post->post_name );

		// Check for a custom post template.
		$custom = get_page_template_slug( $post->ID );

		if ( $custom ) {
			$templates[] = $custom;
		}

		// If viewing an attachment page, handle the files by mime type.
		if ( is_attachment() ) {

			// Split the mime type into two distinct parts.
			$type    = get_post_mime_type( $post );
			
			$subtype = '';

			if ( false !== strpos( $type, '/' ) ) {
				list( $type, $subtype ) = explode( '/', $type );
			}

			if ( $subtype ) {
				$templates[] = "attachment-{$type}-{$subtype}.php";
				$templates[] = "attachment-{$subtype}.php";
			}

			$templates[] = "attachment-{$type}.php";

		// If not viewing an attachment page.
		} else {

			// Add a post ID template.
			$templates[] = "single-{$post->post_type}-{$post->ID}.php";
			$templates[] = "{$post->post_type}-{$post->ID}.php";

			// Add a post name (slug) template.
			$templates[] = "single-{$post->post_type}-{$name}.php";
			$templates[] = "{$post->post_type}-{$name}.php";
		}

		// Add a template based off the post type name.
		$templates[] = "single-{$post->post_type}.php";
		$templates[] = "{$post->post_type}.php";

		// Allow for WP standard 'single' template.
		$templates[] = 'single.php';

		// Return the template hierarchy.
	
		return $templates;
	}
	
	/**
	 * Redirects the location of the stored templates to 'resources/templates/main'
	 */
	function set_template_location( $templates ) {
		
		$path = trailingslashit( \Frame\Core\Theme::get_option('template_dir_path') ) . 'main/';

		if ( $path ) {
			
			array_walk( $templates, function( &$template, $key ) use ( $path ) {
	
				$template = ltrim( str_replace( $path, '', $template ), '/' );
	
				$template = $path . $template;
			} );
		}
		
		return $templates;
	}
	
	/**
	 * Overrides the default comments template.  This filter allows for a
	 * `comments-{$post_type}.php` template based on the post type of the current
	 * single post view.  If this template is not found, it falls back to the
	 * default `comments.php` template.
	 *
	 * @param  string $template
	 * @return string
	 */
	public function comments_template( $template ) {
	
		$templates = [];
	
		// Allow for custom templates entered into comments_template( $file ).
		$template = str_replace( trailingslashit( get_stylesheet_directory() ), '', $template );
	
		if ( 'comments.php' !== $template ) {
			$templates[] = $template;
		}
	
		// Add a comments template based on the post type.
		$templates[] = sprintf( 'comments/%s.php', get_post_type() );
	
		// Add the default comments template.
		$templates[] = trailingslashit(\Frame\Core\Theme::get_option('template_dir_path'))  . 'comments/comments.php';
		$templates[] = trailingslashit(\Frame\Core\Theme::get_option('template_dir_path'))  . 'comments/default.php';
		$templates[] = 'comments.php';
	
		// Return the found template.
		return locate_template( $templates );
	}
}

?>