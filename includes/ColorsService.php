<?php

namespace BlockEditorColors;


class ColorsService {

	private $default_colors_service = null;
	private $custom_colors_service = null;
	private $options_service = null;
	private static $_instance = null;

	public static function getInstance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {

		$this->default_colors_service = DefaultColorsService::getInstance();
		$this->custom_colors_service  = CustomColorsService::getInstance();
		$this->options_service        = OptionsService::getInstance();

		add_action( 'wp_head', array( $this, 'print_head_styles' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'add_editor_styles' ) );

		// since WP 5.8.0 'block_editor_settings' filter is deprecated
		if ( function_exists( 'get_block_editor_settings' ) ) {
			add_filter( 'block_editor_settings_all', array( $this, 'filter_block_editor_settings' ) );
		} else {
			add_filter( 'block_editor_settings', array( $this, 'filter_block_editor_settings' ) );
		}
	}

	public function generate_colors_css( $is_editor = false ) {

		$custom_colors         = $this->custom_colors_service->get_colors();
		$edited_initial_colors = $this->default_colors_service->get_edited_colors();

		$colors = array_merge( $edited_initial_colors, $custom_colors );
		$prefix = $is_editor ? 'body .editor-styles-wrapper' : $this->options_service->get_style_classes_prefix();

		$variables = '';
		$css = '';
		foreach ( $colors as $color ) {
			$css .= PHP_EOL;
			$css .= <<<CSS
{$prefix} .has-{$color['slug']}-color {
	color: var(--bec-color-{$color['slug']}, {$color['color']}) !important;
}
{$prefix} .has-{$color['slug']}-background-color {
	background-color: var(--bec-color-{$color['slug']}, {$color['color']}) !important;
}
CSS;
			$css .= PHP_EOL;

			$variables .= '--bec-color-' . $color['slug'] . ': ' . $color['color'] . ';' . PHP_EOL;
		}

		if ( $css ) {
			$css = $prefix . ' {' . PHP_EOL . $variables . '}' . PHP_EOL . $css;
		}

		return $css;
	}

	public function print_head_styles() {

		$style = $this->generate_colors_css();
		if ( $style == '' ) {
			return;
		}

		?><style id="bec-color-style" type="text/css">
/* Block Editor Colors generated css */
<?php echo esc_html($style); ?>
        </style><?php

	}

	public function add_editor_styles() {
		$style = $this->generate_colors_css( true );
		if ( $style == '' ) {
			return;
		}

		wp_register_style( 'bec-editor-styles', false, array(), BEC_PLUGIN_VERSION, true );
		wp_add_inline_style( 'bec-editor-styles', $style );
		wp_enqueue_style( 'bec-editor-styles' );
	}

	public function filter_block_editor_settings( $settings ) {

		$initial_colors = $this->default_colors_service->get_colors();
		$new_colors     = $this->custom_colors_service->get_colors();

		$initial_colors     = array_values( $initial_colors );
		$new_colors         = array_values( $new_colors );
		$settings['colors'] = array_merge( $initial_colors, $new_colors );

		if ( function_exists( 'get_block_editor_settings' ) ) {
			$settings['__experimentalFeatures']['color']['palette']['user']
				= $settings['__experimentalFeatures']['color']['palette']['theme']
				= array_merge($initial_colors, $new_colors);
		}

		return $settings;

	}

}

ColorsService::getInstance();
