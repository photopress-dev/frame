<?php

namespace Frame\Customizer\Fonts;
use WP_Customize_Control;

if ( class_exists( 'WP_Customize_Control' ) ) {
	
	/**
	 * Font Select Control
	 *
	 * Based on control by Anthony Hortin
	 * @link https://github.com/maddisondesigns
	 */
	class Font_Control extends WP_Customize_Control {
		/**
		 * The type of control being rendered
		 */
		public $type = 'fonts';
		/**
		 * The list of Google Fonts
		 */
		private $fontList = false;
		/**
		 * The saved font values decoded from json
		 */
		private $fontValues = [];
		/**
		 * The index of the saved font within the list of Google fonts
		 */
		private $fontListIndex = 0;
		/**
		 * The number of fonts to display from the json file. Either positive integer or 'all'. Default = 'all'
		 */
		private $fontCount = 'all';
		/**
		 * The font list sort order. Either 'alpha' or 'popular'. Default = 'alpha'
		 */
		private $fontOrderBy = 'alpha';
		
		/**
		 * Get our list of fonts from the json file
		 */
		public function __construct( $manager, $id, $args = array(), $options = array() ) {
			
			parent::__construct( $manager, $id, $args );
			
			// Get the font sort order
			if ( isset( $this->input_attrs['orderby'] ) && strtolower( $this->input_attrs['orderby'] ) === 'popular' ) {
				
				$this->fontOrderBy = 'popular';
			}
			
			// Get the list of Google fonts
			if ( isset( $this->input_attrs['font_count'] ) ) {
				
				if ( 'all' != strtolower( $this->input_attrs['font_count'] ) ) {
					
					$this->fontCount = ( abs( (int) $this->input_attrs['font_count'] ) > 0 ? abs( (int) $this->input_attrs['font_count'] ) : 'all' );
				}
			}
			
			$this->fontList = Google::get_font_list('all');
			
			// Decode the default json font value
			if ( $this->value() ) {
				
				$this->fontValues = json_decode( $this->value() );
			}

			if ( $this->fontValues ) {
				// Find the index of our default font within our list of Google fonts
				$this->fontListIndex = $this->get_font_index( $this->fontList, $this->fontValues->font );
			}
		}
		
		/**
		 * Enqueue our scripts and styles
		 */
		public function enqueue() {
			
			wp_enqueue_script( 'frame-select2-js', \Frame\Core\Theme::get_parent_theme_uri( 'frame/framework/assets/js/select2.full.min.js' ), array( 'jquery' ), '4.0.13', true );
			wp_enqueue_script( 'frame-custom-controls-js', \Frame\Core\Theme::get_parent_theme_uri( 'frame/framework/assets/js/customizer-controls.js' ), array( 'frame-select2-js' ), FRAME_VERSION, true );
			wp_enqueue_style( 'frame-custom-controls-css', \Frame\Core\Theme::get_parent_theme_uri( 'frame/framework/assets/css/customizer.css' ), array(), FRAME_VERSION, 'all' );
			wp_enqueue_style( 'frame-select2-css', \Frame\Core\Theme::get_parent_theme_uri( 'frame/framework/assets/css/select2.min.css' ), array(), '4.0.13', 'all' );
		}
		/**
		 * Export our List of Google Fonts to JavaScript
		 */
		public function to_json() {
			parent::to_json();
			$this->json['skyrocketfontslist'] = array_merge( \Frame\Core\Customizer::get_default_fonts(), $this->fontList);
		}
		/**
		 * Render the control in the customizer
		 */
		public function render_content() {
			
			$fontCounter = 0;
			$isFontInList = false;
			$fontListStr = '';
			$default_fonts = \Frame\Core\Customizer::get_default_fonts();
			
			if( !empty($this->fontList) ) {
				?>
				<div class="google_fonts_select_control">
					<?php if( !empty( $this->label ) ) { ?>
						<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
					<?php } ?>
					<?php if( !empty( $this->description ) ) { ?>
						<span class="customize-control-description"><?php echo esc_html( $this->description ); ?></span>
					<?php } ?>
					<input type="hidden" id="<?php echo esc_attr( $this->id ); ?>" name="<?php echo esc_attr( $this->id ); ?>" value="<?php echo esc_attr( $this->value() ); ?>" class="customize-control-google-font-selection" <?php $this->link(); ?> />
					<div class="google-fonts">
						<select data-placeholder="Select an option..." class="google-fonts-list" control-name="<?php echo esc_attr( $this->id ); ?>">
							<option value=""></option>
							<?php
								$fontListStr .= '<option value="" disabled>--- Default Fonts ---</option>';
								foreach( $default_fonts as $dkey => $dvalue ) {
									
									$fontCounter++;
									
									$fontListStr .= '<option value="' . $dvalue->family . '" ' . selected( $this->fontValues->font, $dvalue->family, false ) . '>' .  $dvalue->family . '</option>';
									
									if ( $this->fontValues->font === $dvalue->family ) {
										
										$isFontInList = true;
									}
									
									if ( is_int( $this->fontCount ) && $fontCounter === $this->fontCount ) {
										
										break;
									}
								}
							?>
							
							<?php
								$fontListStr .= '<option value="" disabled>--- Google Fonts ---</option>';
								foreach( $this->fontList as $key => $value ) {
									
									$fontCounter++;
									
									$fontListStr .= '<option value="' . $value->family . '" ' . selected( $this->fontValues->font, $value->family, false ) . '>' .  $value->family . '</option>';
									
									if ( $this->fontValues->font === $value->family ) {
										$isFontInList = true;
									}
									
									if ( is_int( $this->fontCount ) && $fontCounter === $this->fontCount ) {
										
										break;
									}
								}
								
								
								if ( !$isFontInList && $this->fontListIndex ) {
									// If the default or saved font value isn't in the list of displayed fonts, add it to the top of the list as the default font
									$fontListStr = '<option value="' . $this->fontList[$this->fontListIndex]->family . '" ' . selected( $this->fontValues->font, $this->fontList[$this->fontListIndex]->family, false ) . '>' . $this->fontList[$this->fontListIndex]->family . ' (default)</option>' . $fontListStr;
								}
								// Display our list of font options
								echo $fontListStr;
							?>
						</select>
					</div>
					<div class="customize-control-description"><?php esc_html_e( 'Select weight & style for regular text', \Frame\Core\Option::get_option('theme_name') ) ?></div>
					<div class="weight-style">
						<select class="google-fonts-regularweight-style">
							<?php
								foreach( $this->fontList[$this->fontListIndex]->variants as $key => $value ) {
									echo '<option value="' . $value . '" ' . selected( $this->fontValues->regularweight, $value, false ) . '>' . $value . '</option>';
								}
							?>
						</select>
					</div>
					<div class="customize-control-description"><?php esc_html_e( 'Select weight for', \Frame\Core\Option::get_option('theme_name') ) ?> <italic><?php esc_html_e( 'italic text', 'skyrocket' ) ?></italic></div>
					<div class="weight-style">
						<select class="google-fonts-italicweight-style" <?php disabled( in_array( 'italic', $this->fontList[$this->fontListIndex]->variants ), false ); ?>>
							<?php
								$optionCount = 0;
								foreach( $this->fontList[$this->fontListIndex]->variants as $key => $value ) {
									// Only add options that are italic
									if( strpos( $value, 'italic' ) !== false ) {
										echo '<option value="' . $value . '" ' . selected( $this->fontValues->italicweight, $value, false ) . '>' . $value . '</option>';
										$optionCount++;
									}
								}
								if( $optionCount == 0 ) {
									echo '<option value="">Not Available for this font</option>';
								}
							?>
						</select>
					</div>
					<div class="customize-control-description"><?php esc_html_e( 'Select weight for', \Frame\Core\Option::get_option('theme_name') ) ?> <strong><?php esc_html_e( 'bold text', 'skyrocket' ) ?></strong></div>
					<div class="weight-style">
						<select class="google-fonts-boldweight-style">
							<?php
								$optionCount = 0;
								foreach( $this->fontList[$this->fontListIndex]->variants as $key => $value ) {
									// Only add options that aren't italic
									if( strpos( $value, 'italic' ) === false ) {
										echo '<option value="' . $value . '" ' . selected( $this->fontValues->boldweight, $value, false ) . '>' . $value . '</option>';
										$optionCount++;
									}
								}
								// This should never evaluate as there'll always be at least a 'regular' weight
								if( $optionCount == 0 ) {
									echo '<option value="">Not Available for this font</option>';
								}
							?>
						</select>
					</div>
					<input type="hidden" class="google-fonts-category" value="<?php echo $this->fontValues->category; ?>">
				</div>
				<?php
			}
		}
	
		/**
		 * Find the index of the saved font in our multidimensional array of Google Fonts
		 */
		public function get_font_index( $haystack, $needle ) {
			
			foreach( $haystack as $key => $value ) {
				
				if( $value->family == $needle ) {
					
					return $key;
				}
			}
			
			return false;
		}
	}	
}	

?>