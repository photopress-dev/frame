<?php
	
namespace Frame\Customizer;
use WP_Customize_Control;

if ( class_exists( 'WP_Customize_Control' ) ) {

	/**
	 * Image Checkbox Control
	 *
	 * based on the work of Anthony Hortin <http://maddisondesigns.com>
	 */
	 class Image_Checkbox_Control extends WP_Customize_Control {
		 
		/**
		 * The type of control being rendered
		 */
		public $type = 'image_checkbox';
		
		/**
		 * Enqueue our scripts and styles
		 */
		public function enqueue() {
			
			wp_enqueue_style( 'frame-custom-controls-css', \Frame\Core\Theme::get_parent_theme_uri( 'frame/framework/assets/css/customizer.css' ), array(), '1.0', 'all' );
		}
		/**
		 * Render the control in the customizer
		 */
		public function render_content() {
		?>
			<div class="image_checkbox_control">
				<?php if( !empty( $this->label ) ) { ?>
					<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
				<?php } ?>
				<?php if( !empty( $this->description ) ) { ?>
					<span class="customize-control-description"><?php echo esc_html( $this->description ); ?></span>
				<?php } ?>
				<?php	$chkboxValues = explode( ',', esc_attr( $this->value() ) ); ?>
				<input type="hidden" id="<?php echo esc_attr( $this->id ); ?>" name="<?php echo esc_attr( $this->id ); ?>" value="<?php echo esc_attr( $this->value() ); ?>" class="customize-control-multi-image-checkbox" <?php $this->link(); ?> />
				<?php foreach ( $this->choices as $key => $value ) { ?>
					<label class="checkbox-label">
						<input type="checkbox" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $key ); ?>" <?php checked( in_array( esc_attr( $key ), $chkboxValues ), 1 ); ?> class="multi-image-checkbox"/>
						<img src="<?php echo esc_attr( $value['image'] ); ?>" alt="<?php echo esc_attr( $value['name'] ); ?>" title="<?php echo esc_attr( $value['name'] ); ?>" />
					</label>
				<?php	} ?>
			</div>
		<?php
		}
	}	
}
?>