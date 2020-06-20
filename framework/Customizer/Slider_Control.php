<?php
	
namespace Frame\Customizer;
use WP_Customize_Control;

if ( class_exists( 'WP_Customize_Control' ) ) {

	class Slider_Control extends WP_Customize_Control {
		/**
		 * The type of control being rendered
		 */
		public $type = 'slider_control';
		/**
		 * Enqueue our scripts and styles
		 */
		public function enqueue() {
			
			wp_enqueue_script( 'frame-custom-controls-js', \Frame\Core\Theme::get_parent_theme_uri( 'frame/framework/assets/js/customizer-controls.js' ), array( 'jquery', 'jquery-ui-core' ), '1.0', true );
			wp_enqueue_style( 'frame-custom-controls-css', \Frame\Core\Theme::get_parent_theme_uri( 'frame/framework/assets/css/customizer.css' ), array(), FRAME_VERSION, 'all' );
		}
		
		/**
		 * Render the control in the customizer
		 */
		public function render_content() {
			
		?>
			<div class="slider-custom-control">
				
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
				<input type="number" id="<?php echo esc_attr( $this->id ); ?>" name="<?php echo esc_attr( $this->id ); ?>" value="<?php echo esc_attr( $this->value() ); ?>" class="customize-control-slider-value" <?php $this->link(); ?> />
				
				<div class="slider" slider-min-value="<?php echo esc_attr( $this->input_attrs['min'] ); ?>" slider-max-value="<?php echo esc_attr( $this->input_attrs['max'] ); ?>" slider-step-value="<?php echo esc_attr( $this->input_attrs['step'] ); ?>"></div>
				<span class="slider-reset dashicons dashicons-image-rotate" slider-reset-value="<?php echo esc_attr( $this->value() ); ?>"></span>
			
			</div>
		<?php
		}
	}
}
?>