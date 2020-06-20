<?php 

namespace Frame\Core\Meta;

class Metabox {
	
	private $id;
	private $title;
	private $callback;
	private $screens;
	private $context;
	private $priority;
	private $meta_key;
	private $field_types;
	
	public function __construct( $args ) {
		
		$defaults = [
			
			'id' 			=> 'some_meta',
			'title'			=>  __( 'Some Meta Box Headline', 'textdomain' ),
			'screens'		=> [],
			'priority'		=> 'high',
			'callback_args'	=> null,
			'context'		=> 'side',
			'fields'		=> []
		];
		
		$args = wp_parse_args( $args, $defaults );
		
		$this->id = "photopress_{$args['id']}";
		$this->title = $args['title'];
		$this->callback = 'render';
		$this->context = $args['context'];
		$this->screens = $args['screens'];
		$this->priority = $args['priority'];
		$this->fields = $args['fields'];
		
		$this->field_types = [
		
			'text'		=>	'Text_Field',
			'checkbox'	=>	'Checkbox_Field'
		]; 
		
		$this->init();
	}
	
	public function init() {
		
		add_action( 'add_meta_boxes', array( $this, 'register' ), 10);
        add_action( 'save_post', array( $this, 'save' ) );
	}
	
	public function register( $post_type ) {
		
		add_meta_box( $this->id, $this->title, [$this, 'render'], $this->screens, $this->context, $this->priority ); 
	}
	
	public function render( $post ) {
		
		
		foreach ($this->fields as $k => $field) {
			
			$defaults = [
				
				'id'			=> $k,
				'parent_id'		=> $this->id,
				'type' 			=> 'text', 
				'description' 	=> 'Description goes here...'
			];
			
			$field = wp_parse_args( $field, $defaults );
			
			// Use get_post_meta to retrieve an existing value from the database.
			$value = '';
			$meta = get_post_meta( $post->ID, $this->id, true );
			if ($meta && is_array($meta) && array_key_exists($field['id'], $meta) ) {
				
				$value = $meta[ $field['id'] ];
			}
			$field['value'] = esc_attr( $value );
			
			// make the field id
			$field['dom_id'] = "{$this->id}_{$field['id']}_field";
			 
			$field_class = sprintf('\Frame\Core\Meta\%s', $this->field_types[ $field[ 'type' ] ] );
			
			$f = new $field_class( $field ) ;
			
			$form_field = $f->render();
			
			echo $form_field;
		
		}
		
		// Add an nonce field so we can check it when saving meta
        wp_nonce_field( $this->id, $this->id . '_nonce' );
		
	}
	
	private function make_form_field( $field ) {
		
		$defaults = [
			
			'size'	=> 25
		];
		
		$field = wp_parse_args( $field, $defaults );

		$needles = \Frame\Core\Util::rekey_as_needles( $field );
			
		return sprintf( strtr( '<input type="{type}" id="{dom_id}" name="%s" value="{value}" size="{size}" />', $needles ), $this->id.'['. $field['id'] . ']');
	}
	
	public function save( $post_id ) {
		
		$nonce_name = "{$this->id}_nonce";
		
        // Check if our nonce is set.
        if ( ! isset( $_POST[ $nonce_name ] ) ) {
            
            return $post_id;
        }
 
        $nonce = $_POST[ $nonce_name ];
 
        // Verify that the nonce is valid.
        if ( ! wp_verify_nonce( $nonce, $this->id ) ) {
            
            return $post_id;
        }
 
        /*
         * If this is an autosave, our form has not been submitted,
         * so we don't want to do anything.
         */
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            
            return $post_id;
        }
 
        // Check the user's permissions.
        if ( 'page' == $_POST['post_type'] ) {
	        
            if ( ! current_user_can( 'edit_page', $post_id ) ) {
	            
                return $post_id;
            }
            
        } else {
	        
            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return $post_id;
            }
        }
 
        /* OK, it's safe for us to save the data now. */
 
        // Sanitize the user input.
        $data = $_POST[ $this->id ] ;
        
        foreach ( $this->fields as $field) {
	        
	        if ( $field['type'] === 'text' ) {
		        
		        $data[ $field['id'] ] = sanitize_text_field( $data[ $field['id'] ] );
		    }
		        
        }
 
        // Update the meta field.
        update_post_meta( $post_id, $this->id, $data );
	}
	
}
	
?>