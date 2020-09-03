<?php

namespace Frame\Core;
use tgmpa;

class Tgm {
	
	public function __construct() {
		
		$this->init();
	}
	
	public function init() {
		
		add_action( 'tgmpa_register', [ $this, 'register' ] );
	}
	
	public function register() {
		
		$plugins = apply_filters( 'frame/core/required_plugin_list', 
			
			[	
				[
					'name'     => 'PhotoPress',
					'slug'     => 'photopress',
					'required' => true,
				]
			]
		);
		
		$config = array(
			'id'           => 'frame',                    // Unique ID for hashing notices for multiple instances of TGMPA.
			'menu'         => 'tgmpa-install-plugins', // Menu slug.
			'has_notices'  => true,                    // Show admin notices or not.
			'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
			'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
			'is_automatic' => false,                   // Automatically activate plugins after installation or not.
			'message'      => '',                      // Message to output right before the plugins table.
		);

		tgmpa( $plugins, $config );
	}
}	
	
	
?>