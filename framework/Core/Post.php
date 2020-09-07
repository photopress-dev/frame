<?php

namespace Frame\Core;	

class Post {
	
	static function display_terms( $args = [] ) {
		
		echo self::render_terms( $args );
	}
	
	/**
	 * Returns the post taxonomy terms

	 * @param  array  $args
	 * @return string
	 */
	static function render_terms( array $args = [] ) {
	
		$html = '';
	
		$args = wp_parse_args( $args, [
			
			'taxonomy' => 'category',
			'text'     => '%s',
			'class'    => '',
			'seperator'      => _x( ', ', 'taxonomy terms separator', 'photopress-frame' ),
			'before'   => '',
			'after'    => ''
		] );
	
		// Append taxonomy to class name.
		if ( ! $args['class'] ) {
			
			$args['class'] = "entry__terms entry__terms--{$args['taxonomy']}";
		}
	
		$terms = get_the_term_list( get_the_ID(), $args['taxonomy'], '', $args['seperator'], '' );
	
		if ( $terms ) {
	
			$html = sprintf(
				
				'<span class="%s">%s</span>',
				esc_attr( $args['class'] ),
				sprintf( $args['text'], $terms )
			);
	
			$html = $args['before'] . $html . $args['after'];
		}
	
		return apply_filters( 'frame/post/terms', $html );
	}

	/**
	 * Displays a Post's Date.
	 *
	 * 
	 */
	static function display_date() {
		
			$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
			if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
				$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
			}
	
			$time_string = sprintf(
				$time_string,
				esc_attr( get_the_date( DATE_W3C ) ),
				esc_html( get_the_date() ),
				esc_attr( get_the_modified_date( DATE_W3C ) ),
				esc_html( get_the_modified_date() )
			);
	
			$posted_on = sprintf(
				/* translators: %s: post date. */
				esc_html_x( 'Posted on %s', 'post date', 'photopress-frame' ),
				'<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">' . $time_string . '</a>'
			);
	
			echo '<span class="posted-on">' . $posted_on . '</span>'; 
	}
	
	/**
	 * Displays a Post's Author.
	 *
	 * 
	 */
	static function display_author() {
		
		$byline = sprintf(
			/* translators: %s: post author. */
			esc_html_x( 'by %s', 'post author', 'photoress-frame' ),
			'<span class="author vcard"><a class="url fn n" href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . esc_html( get_the_author() ) . '</a></span>'
		);

		echo '<span class="byline"> ' . $byline . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	}
	
	/**
	 * Displays an optional post thumbnail.
	 *
	 * Wraps the post thumbnail in an anchor element on index views, or a div
	 * element when on single views.
	 */
	static function display_thumbnail( $size = 'medium') {
		
		if ( post_password_required() || is_attachment() || ! has_post_thumbnail() ) {
			return;
		}

		if ( is_singular() ) {
			?>

			<div class="post-thumbnail">
				<?php the_post_thumbnail( $size ); ?>
			</div><!-- .post-thumbnail -->

		<?php } else { ?>
			
			<a class="post-thumbnail" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
				<?php
					the_post_thumbnail(
						$size,
						array(
							'alt' => the_title_attribute(
								array(
									'echo' => false,
								)
							),
						)
					);
				?>
			</a>

			<?php
		}
	}
	
	/**
	 * Prints HTML with meta information for the categories, tags and comments.
	 */
	static function display_entry_footer() {
		
		// Hide category and tag text for pages.
		if ( 'post' === get_post_type() ) {
			/* translators: used between list items, there is a space after the comma */
			$categories_list = get_the_category_list( esc_html__( ', ', 'photopress-frame' ) );
			if ( $categories_list ) {
				/* translators: 1: list of categories. */
				printf( '<span class="cat-links">' . esc_html__( 'Posted in %1$s', 'photopress-frame' ) . '</span>', $categories_list ); 			}

			/* translators: used between list items, there is a space after the comma */
			$tags_list = get_the_tag_list( '', esc_html_x( ', ', 'list item separator', 'photopress-frame' ) );
			if ( $tags_list ) {
				/* translators: 1: list of tags. */
				printf( '<span class="tags-links">' . esc_html__( 'Tagged %1$s', 'photopress-frame' ) . '</span>', $tags_list ); 
			}
		}

		if ( ! is_single() && ! post_password_required() && ( comments_open() || get_comments_number() ) ) {
			echo '<span class="comments-link">';
			comments_popup_link(
				sprintf(
					wp_kses(
						/* translators: %s: post title */
						__( 'Leave a Comment<span class="screen-reader-text"> on %s</span>', 'photopress-frame' ),
						array(
							'span' => array(
								'class' => array(),
							),
						)
					),
					wp_kses_post( get_the_title() )
				)
			);
			echo '</span>';
		}
	}
	
	static function render_title( array $args = [] ) {

		$post_id   = get_the_ID();

		$is_single = is_single( $post_id ) || is_page( $post_id ) || is_attachment( $post_id );
	
		$args = wp_parse_args( $args, [
			'text'   => '%s',
			'tag'    => $is_single ? 'h1' : 'h2',
			'link'   => ! $is_single,
			'class'  => 'entry-title',
			'before' => '',
			'after'  => ''
		] );
	
		$text = sprintf( $args['text'], $is_single ? single_post_title( '', false ) : the_title( '', '', false ) );
	
		if ( $args['link'] ) {
			$text = self::render_permalink( [ 'text' => $text ] );
		
		}
	
		$html = sprintf(
			'<%1$s class="%2$s">%3$s</%1$s>',
			tag_escape( $args['tag'] ),
			esc_attr( $args['class'] ),
			$text
		);
	
		return apply_filters( 'frame/post/title', $args['before'] . $html . $args['after'] );
	}
	
	static function display_title( $args = []) {
		
		echo self::render_title( $args );
	}
	
	static function render_permalink( array $args = [] ) {

		$args = wp_parse_args( $args, [
			'text'   => '%s',
			'class'  => 'entry__permalink',
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
	
		return apply_filters( 'frame/post/permalink', $args['before'] . $html . $args['after'] );
	}
	
	static function get_meta( $key, $subkey = '') {
		
		$meta = get_post_meta( get_the_ID(), $key, true );
		
		if ( ! $subkey ) {
			
			return $meta;
			
		} else {
		
			if ( is_array( $meta ) && array_key_exists( $subkey, $meta ) ) {
			
				return $meta[ $subkey ];
			}
		}

	}

}	
?>