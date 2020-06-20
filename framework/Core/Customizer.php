<?php

namespace Frame\Core;
use WP_Customize_Manager;

class Customizer {
	
		
	public static $panels;
	public static $sections;
	public static $settings;
	public static $controls;
	public static $partials;
	public static $control_implementations;

	// names of settings that contain custom font controls.
	public static $font_settings;
	
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



	public function __construct() {
		
		self::$control_implementations = apply_filters( 'frame/customizer/control_implementations', [
			
			// custom controls 
			
			'font'		 			=> [
				'class'					=> '\Frame\Customizer\Fonts\Font_Control',
			],
			'select'				=> [
				'class'					=> '\Frame\Customizer\Select_Control',
				'sanitize_callback'		=> '\Frame\Core\Sanitize::to_string'
			],
			'slider'				=> [
				'class'					=> '\Frame\Customizer\Slider_Control',
				'sanitize_callback'		=> '\Frame\Core\Sanitize::to_int'
			],
			'image_checkbox'		=> [
				'class'					=> '\Frame\Customizer\Image_Checkbox_Control',
				'sanitize_callback'		=> '\Frame\Core\Sanitize::to_string'
			],
			'image_radio_button'	=> [
				'class'					=> '\Frame\Customizer\Image_Radio_Button_Control',
				'sanitize_callback'		=> '\Frame\Core\Sanitize::to_string'
			],
			'toggle'				=> [
				'class'					=> '\Frame\Customizer\Toggle_Control',
				'sanitize_callback'		=> '\Frame\Core\Sanitize::to_bool'
			],
			
			// core controls
			
			'checkbox'				=>[
				'sanitize_callback'		=> '\Frame\Core\Sanitize::to_bool'
			],
			'text'					=>[
				'sanitize_callback'		=> '\Frame\Core\Sanitize::to_string'
			]
		]);
			
		$this->processConfig();
		
		$this->init();
	}
	
	public function init() {
		
		// register customizer panels
		add_action( 'customize_register', [ $this, 'register' ] );
		// Output custom CSS to live site
		add_action( 'wp_head' , [ $this , 'header_output' ] );
		
		add_action( 'customize_preview_init',  [ $this , 'live_preview' ] );
		
		add_action( 'enqueue_block_editor_assets', [ $this, 'block_styles' ], 100 );
 
	}
	
	private function processConfig() {
		
		// Get and filter the default sections array. 
		// Themes will filter this to add their own sections.
		$config = apply_filters( 'frame/customizer/config', $this->get_default_config() );
		
		foreach ( $config as $panel_key => $panel ) {
		
			self::$panels[$panel_key] = $panel;
			
			foreach ( $panel['sections'] as $section_key => $section ) {
				
				$section['panel'] = $panel_key;
				self::$sections[$section_key] = $section;
				
				foreach ($section['settings'] as $setting_key => $setting ) {
				
					self::$settings[ $setting_key ] =  $this->apply_setting_defaults( $setting );
					
					$setting['control']['section'] = $section_key;
					
					self::$controls[ $setting_key ] = $setting['control'];
					
					if ( array_key_exists( 'partial', $setting ) ) {
						self::$partials[ $setting_key ] =  $setting['partial'];
					}
					
					if ( isset($setting['control']['type']) && $setting['control']['type'] === 'font') {
						
						self::$font_settings[] = $setting_key;
					}
				}
			}
		}
	}
	
	// register our customizer controls, sections ,etc.
	public function register( $wp_customize ) {
		
		$components = [
		
			'panels' 	=> 'add_panel', 
			'sections' 	=> 'add_section', 
			'settings' 	=> 'add_setting', 
			'controls' 	=> 'add_control', 
			'partials' 	=> 'add_partial'
		];
		
		foreach ( $components as $class_prop => $method ) {
			
			if ( ! empty( self::${$class_prop} ) ) {
				
				foreach ( self::${$class_prop} as $id => $value ) {
					
					$this->$method( $wp_customize, $id, $value );
				}
			}
		}
	}
	
	private function get_core_panels() {
		
		$core_panels = [
			
			'title_tagline',
			'colors',
			'header_image',
			'background_image',
			'nav_menus',
			'widgets',
			'static_front_page',
			'custom_css'
		];
	}
	
	private function add_panel(  WP_Customize_Manager $wp_customize, $id, $args) {
		
		$defaults = [
	       'title' 				=> __( '' ),
	       'description' 		=> esc_html__( '' ),  
	       'priority' 			=> 160, 
	       'capability' 		=> 'edit_theme_options', 
	       'theme_supports' 	=> '', 
	       'active_callback' 	=> '',
	       'sections'			=> []
	   ];
	   
	   $args = wp_parse_args( $args, $defaults );
	   $wp_customize->add_panel( $id, $args );
	   
	}
	
	private function add_section( WP_Customize_Manager $wp_customize, $id, $args ) {
		
		$defaults = [
			
			'title'					=> '',
			'priority'				=> 160,
			'panel'					=> '',
			'description'			=> '',
			'active_callback'		=> null,
			'theme_supports'		=> '',
			'description_hidden'	=> false
		];
		
		$args = wp_parse_args( $args, $defaults );
	   
		$wp_customize->add_section( $id, $args );
		
	}
	
	private function apply_setting_defaults( $args ) {
		
		$defaults = [
			
			'default'				=> '',	
			'label'					=> '',
			'type'					=> 'theme_mod',
			'capability'			=> 'edit_theme_options',
			'transport'				=> 'refresh',
			'theme_supports'		=> '',
			'validate_callback'		=> '',
			'sanitize_callback'		=> [],
			'sanitize_js_callback'	=> '',
			'dirty'					=> false,
			'control'				=> [],
			'partial'				=> []
		];
		
		$args = wp_parse_args( $args, $defaults );
		
		if ( empty ( $args['sanitize_callback'] ) ) {
			
			$args['sanitize_callback'] = $this->lookup_sanitize_callback( $args['type'] );
		}
		
		// check to see ifthere is a style property on this setting. If so, apply those defaults too.
		if ( isset( $args['style'] ) ) {
			
			$style_defaults = [
				
				'selector'			=> '',
				'editor_selector'	=> '',
				'css_property'		=> '',
				'css_suffix'		=> ''
			];
			
			$args['style'] = wp_parse_args( $args['style'], $style_defaults );
		
			// determin the suffix to add to the css value.
			if ( ! $args['style']['css_suffix'] ) {
				
				$args['style']['css_suffix'] = self::lookup_css_value_suffix( $args['style']['css_property'] );
			} 
		}
		
		return $args;
	}
	
	private function add_setting( WP_Customize_Manager $wp_customize, $id, $args ) {
	
		$wp_customize->add_setting( $id, $args );
	}
	
	private function add_control( WP_Customize_Manager $wp_customize, $id, $args ) {
		
		$controls = self::$control_implementations;
		
		$defaults = [
			
			'label'				=> '',
			'description'		=> '',
			'section'			=> '',
			'priority'			=> 10,
			'type'				=> 'text',
			'capability'		=> 'edit_theme_options',
			'input_attrs'		=> []
		];	
		
		$args = wp_parse_args( $args, $defaults );
		
		if ( ! array_key_exists( $args['type'], $controls ) ||  array_key_exists( $args['type'], $controls ) && ! isset( $controls[ $args['type'] ][ 'class' ] ) ) {

			$wp_customize->add_control( $id, $args );
				
		} else {
			
			if ( array_key_exists( $args['type'], $controls ) && isset( $controls[ $args['type'] ]['class'] ) ) {
				
				$class = $controls[ $args['type'] ]['class']; 
				$wp_customize->add_control( new $class( $wp_customize, $id, $args ) ) ;				
			} 
		}
	}
	
	private function lookup_sanitize_callback( $type ) {
		
	    $controls = self::$control_implementations;
	    
	    if ( array_key_exists( $type, $controls ) && 
	    	isset ( $controls[ $type ]['sanitization_callback'] ) ) {
		    
		    return $controls[ $type ]['sanitization_callback']; 
	    }	    
    }

	
	private function add_partial( WP_Customize_Manager $wp_customize, $id, $args ) {
		
		$defaults = [
					
			'selector'				=> '',
			'container_inclusive'	=> false,
			'capability'			=> '',
			'render_callback'		=> '',
			'fallback_refresh'		=> true
		];
		
		$args = wp_parse_args( $args, $defaults );
	   
		$wp_customize->selective_refresh->add_partial( $id, $args );
	}
	
	private function get_default_config() {
		
		return [
			
			'typography'	=> [
				
				'title'			=> 'Typography',
				'priority'		=>	'1',
				'capability'	=> 'edit_theme_options',
				'description'	=> 'Typography controls.',
				'sections'		=> [
					
					'headings'		=> [
						
						'title'			=> 'Headings',
						'description'	=> 'Customize the typography used your Site Headings (h1, h2, etc.)',
						'settings'		=> [
								
							'headings_font_family'	=> [
								
							    'control'			=> [
							    
							    	'type'				=> 'font',
							    	'label'				=> ' Headings Font & Weight'
							    ],
							    'style'				=>  [
									
									'selector'			=> 'h1, h2, h3, h4, h5, h6',
									'css_property'		=> '',
									'callback'			=> 'generate_font_css'
										
								]
							],
							
							'headings_text_transform'	=> [
								
								'control'		=> [
									
									'type' 			=> 'image_radio_button',
									'transport'		=> 'postMessage',
									'label' 		=> __( 'Text Transform' ),
									'description' 	=> esc_html__( 'Adjust the case of the text.' ),
									'choices' 		=> [	
					
								        'capitalize' 	=> [
								            'image' 		=>  \Frame\Core\Theme::get_framework_uri( 'assets/images/titlecase.png') ,
								            'name' 			=> __( 'Capitalize' )
								        ],
								        'uppercase' 	=> [
								            'image' 		=>  \Frame\Core\Theme::get_framework_uri( 'assets/images/uppercase.png') ,
								            'name' 			=> __( 'Uppercase' )
								        ],
								        'lowercase' 	=> [
								            'image' 		=>  \Frame\Core\Theme::get_framework_uri( 'assets/images/lowercase.png') ,
								            'name' 			=> __( 'Lowercase' )
								        ]
							        ]
							    ],
							    
							    'style'			=>  [
									
									'selector'			=> 'h1, h2, h3, h4, h5, h6',
									'editor_selector'	=> '.editor-styles-wrapper h1, .editor-styles-wrapper h2, .editor-styles-wrapper h3, .editor-styles-wrapper h4, .editor-styles-wrapper h5, .editor-styles-wrapper h6 ',
									'css_property'		=> 'text-transform'
										
								],
								
								'editor_style'	=> [
									
									'selector'			=> '.editor-styles-wrapper h1, .editor-styles-wrapper h2, .editor-styles-wrapper h3, .editor-styles-wrapper h4, .editor-styles-wrapper h5, .editor-styles-wrapper h6 ',
									'css_property'		=> 'text-transform'
									
								]
							]
						]
					],
					
					'body'			=> [
						
						'title'			=> 'Body Text',
						'description'	=> 'Customize the typography used in the body of your site.',
						'settings'		=> [
						
							'body_font_family'		=> [
								
								'control'			=> [
							    
							    	'type'				=> 'font',
							    	'label'				=> 'Body Text Font & Weight'
							    ],
							    'style'				=>  [
									
									'selector'			=> 'body',
									'editor_selector'	=> '.block-editor-block-list__layout',
									'css_property'		=> '',
									'callback'			=> 'generate_font_css'
										
								]
							],
							
							'body_font_size'		=> [
								
								'default'				=> 14,
								'transport' 			=> 'postMessage',
								'control'				=> [
									
									'type'					=> 'slider',
									'label'					=> 'Body Text Font Size',
									'input_attrs' 			=> [
								         'min' => 9, 	// Required. Minimum value for the slider
								         'max' => 100, 	// Required. Maximum value for the slider
								         'step' => 1, 	// Required. The size of each interval or step
								     ]
								],
								'style'					=> [
									
									'selector'				=> 'body',
									'css_property'			=> 'font-size',
									'css_suffix'			=> 'px'
								]
							],
							
							'body_line_height'		=> [
								
								'default'				=> 2,
								'transport' 			=> 'postMessage',
								'control'				=> [
									
									'type'					=> 'slider',
									'label'					=> 'Body Text Line height',
									'input_attrs' 			=> [
								         'min' => 1, // Required. Minimum value for the slider
								         'max' => 20, // Required. Maximum value for the slider
								         'step' => 0.1, // Required. The size of each interval or step the slider takes between the minimum and maximum values
								     ]
								],
								'style'					=> [
									
									'selector'				=> 'body',
									'css_property'			=> 'line-height'
								]
							]
						] 
					], // end body
					
					'captions' 		=> [
						
						'title'			=> 'Image Captions',
						'description'	=> 'Customize the typography used in image captions.',
						'settings'		=> [
						
							'caption_font_family'		=> [
								
								'control'			=> [
							    
							    	'type'				=> 'font',
							    	'label'				=> 'Body Text Font & Weight'
							    ],
							    'style'				=>  [
									
									'selector'			=> 'figcaption p',
									'css_property'		=> '',
									'callback'			=> 'generate_font_css'
										
								]
							]
						]
						
					] // end captions
				] // sections
			] // panels
		];
	}
	
	/**
	 * Gets a theme_mod setting from the DB
	 */	
	public static function get( $id, $default = '') {
		
		return get_theme_mod( $id, $default );
	}
	
	/**
    * This will output the customizer css changes to the theme's WP head.
    * 
    * Used by hook: 'wp_head'
    * 
    */
	public static function header_output( $selector_key = 'selector', $return = false ) {
		
		$selector_key = $selector_key ?: 'selector';
		$return = $return ?: false;
		
	    $output = "\n";
		
		// style tags are needed for customizer css in added to the <head> html of public site pages
		// but not when used in the block editor via wp_add_inline_style().
		if ( ! $return ) {
	    
		    $output .= "<!--Customizer CSS--> \n";
		    $output .= '<style type="text/css"> ' . "\n";
		} else {
			
			 $output .= '/* Customizer Editor CSS */ ' . "\n";
		}
		       	   
		foreach ( self::$settings as $setting_key => $setting ) {
		
			if ( array_key_exists( 'style', $setting ) ) {
			
				if ( ! array_key_exists( 'callback', $setting[ 'style' ] ) ) {
				
					$setting[ 'style' ]['callback'] = 'generate_css'; 
				}
				
				$callback = $setting[ 'style' ]['callback'];
				
				if ($selector_key === 'editor_selector') {
					
					if ( isset ($setting['style']['editor_selector']) && ! empty( $setting['style']['editor_selector'] ) ) {
						
						$setting['style']['selector'] = $setting['style']['editor_selector'];
						unset( $setting['style']['editor_selector'] );
	
					} else {
						
						continue;
					}
					
				}
				
				$css = self::{$callback}( $setting_key, $setting[ 'style' ] );
				
				if ($css) {
					
					$output .= $css;
				}
			}				   
		}
		
		if ( ! $return ) {
		
			$output.= "</style> \n"; 
			$output .= "<!--/Customizer CSS--> \n";
		}
		
		if ( $return ) {
			
			return $output;
			
		} else {
			
			echo $output;
		}
	}
	
	/**
     * This will generate a line of CSS for use in header output.
     */
    private static function generate_css( $setting_key, $args ) {
	      
	    $value = get_theme_mod( $setting_key );
	    
		$css = '';
		
		if ($value) {
			
			$css_suffix = $args['css_suffix'] ?: self::lookup_css_value_suffix( $args['css_property'] );
			
			$css  = sprintf( "%s { %s: %s; }", $args['selector'], $args['css_property'], $value . $css_suffix );
			$css .= " \n";
		}
		
		return $css;
    }
    
    public static function lookup_css_value_suffix( $property ) {
	    
	    $suffixes = [
		    
		  'font-size'			=> 'px',
		  'padding-top'			=> 'px',
		  'padding-right'		=> 'px',
		  'padding-bottom'		=> 'px',
		  'padding-left'		=> 'px',
		  'margin-top'			=> 'px',
		  'margin-right'		=> 'px',
		  'margin-bottom'		=> 'px',
		  'margin-left'			=> 'px'
	    ];
	    
	    if ( array_key_exists( $property, $suffixes ) ) {
		    
		    return $suffixes[ $property ];
	    }
    }
    
    private static function generate_font_css($setting_key, $args ) {
		
		$value = json_decode( get_theme_mod( $setting_key ) );
	
		$defaults = [
			
			'font'			=> '',
			'regularweight'	=> '',
			'category'		=> '',
			'italicweight'	=> '',
			'boldweight'	=> ''
		];
		
		$style_defaults = [
			
			'selector'		=> '',
			'css_property'	=> ''
		];
		
		$css = '';
		
		if ( ! is_array( $args['selector'] ) ) {
			
			$args['selector'] = [$args['selector']];
		}
		
		
		if ($value) {
			
			foreach ( $args['selector'] as $selector ) {
				
				$args = wp_parse_args( $args, $style_defaults );	
				$value = wp_parse_args( $value, $defaults );
				
				$value["regularweight"] == "regular" ? "normal" : $value["regularweight"];
				$css  = sprintf( '%s { font-family: %s, %s; font-weight: %s; } ', $selector, $value['font'], $value['category'], $value["regularweight"] );
				$css .=  "\n";
				if ( $selector === 'body' || $selector === '.block-editor-block-list__layout' ) {
					
					if ( ! empty( $value["italicweight"] ) ) {
					
						$css  .= sprintf( 'em { font-family: %s, %s; font-weight: %s; } ', $value['font'], $value['category'], $value["italicweight"] );
						$css .=  "\n";
					}
					
					if ( ! empty( $value["boldweight"] ) ) {
					
						$css  .= sprintf( 'b, strong { font-family: %s, %s; font-weight: %s; } ', $value['font'], $value['category'], $value["boldweight"] );
						$css .=  "\n";
					}
				}
			}
		}
		
		return $css;
	}
	
	/**
	 * Appends editor styles to <head> for the block editor edit interface.
	 */
    public function block_styles() {
	    
		$css = $this->header_output('editor_selector', true);
		
		wp_add_inline_style( \Frame\Core\Theme::get_option('theme_name') . '-editor-style', $css );
    }
    
    
    /**
     * This outputs the javascript needed to automate the live settings preview.
     * Only applies ot settings that have "transport" property set to "postMessage".
     */
    public function live_preview() {
	   
	   // enqueue Dummy preview js file.
	   wp_enqueue_script( 
		   
	   		'frame-postmessage', 
	   		\Frame\Core\Theme::get_parent_theme_uri( 'frame/framework/assets/js/customizer-preview.js' ), 
	   		[ 'jquery', 'customize-preview', 'wp-hooks' ], 
	   		'1.0', 
	   		true 
	   	);
	   
	   // Print JavaScript for the Preview pane
	   $calls = '';
	   foreach ( self::$settings as $id => $setting ) {
		      
		   if ( $setting['transport'] === 'postMessage' ) {
			   
			   $calls .= "
			   
			   /* Link Color */
			    wp.customize( '{$id}', function( value ) {
			        value.bind( function( to ) {
				        sel = '{$setting['style']['selector']}'; 
				        if ( sel.startsWith(':') ) {
					        
					        let root = document.documentElement;
					        
					        root.style.setProperty('{$setting['style']['css_property']}', to + '{$setting['style']['css_suffix']}');
					          
					    } else { 
				        
			            	$( '{$setting['style']['selector']}' ).css( '{$setting['style']['css_property']}', to + '{$setting['style']['css_suffix']}' );
			            }
			        } );
			    } );
			   
			   " . "\n";
			   
		   }
	   }
	   
	   	$outer = "
	   	
		   	( function( $ ) {
		
				    %s
				
				} )( jQuery );
				
	   	";
	   
	    wp_add_inline_script( 'frame-postmessage' , sprintf( $outer, $calls ), 'after' );
	  			
	}
   
   public function text_santize_callback( $input ) {
	   
	   return trim( $input );
   }
   
   public static function get_font_settings() {
	   
	   return self::$font_settings;
   }
   
   /**
	 * Generate the URL for the CSS API.
	 */
	public static function css_api() {
		
		$fonts_url = '';
		
		$subsets = 'latin';
	
		$font_settings = self::get_font_settings();
		
		$font_families = array();
		
		foreach ( $font_settings as $setting ) {
		
			$font = json_decode( get_theme_mod( $setting, '' ), true );
			
			$default_families = self::get_default_font_families();			
			
			if ( ! $font ) {
				
				$font = self::$default_font_setting;
			}
			
			if ( in_array( $font['font'], $default_families ) ) {
				
				continue;
			}

			
			$font_families[ ] = $font['font'] . ':' . $font['regularweight'] . ',' . $font['italicweight'] . ',' . $font['boldweight'];
		}
		
		if ( $font_families ) {
			
			// remove default fonts.
			
			$query_args = array(
				'family' => urlencode( implode( '|', $font_families ) ),
				'subset' => urlencode( $subsets ),
				'display' => urlencode( 'fallback' ),
			);
			
			$fonts_url = add_query_arg( $query_args, "https://fonts.googleapis.com/css" );
		}
		 		
		return esc_url_raw( $fonts_url );
	}
	
	public static function get_default_font_families() {
		
		return [	
			
			'Arial',
			'Arial Black',
			'Century Gothic',
			'Courier',
			'Courier New',
			'Georgia',
			'Impact',
			'Lucida Console',
			'Lucida Sans Unicode',
			'Palatino Linotype',
			'Tahoma',
			'Trebuchet MS',
			'Verdana'
		];
	}
	
	public static function get_default_fonts() {
			
		$families = self::get_default_font_families();
				
		$fonts = [];
			
		foreach ( $families as $family ) {
			
			$fonts[] = (object) ['kind' => 'default', 'subsets' => ['latin'],'family' => $family, 'category' => 'sans-serif', 'variants' => [ '400', '400italic', '700', '700italic'] ];
		}	
		
		return $fonts;					

	}
}	

?>