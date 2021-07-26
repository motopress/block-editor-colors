<?php
/**
 * Plugin Name: Block Editor Colors
 * Plugin URI: https://motopress.com/products/block-editor-colors/
 * Description: Change Gutenberg block editor colors or create new ones.
 * Version: 1.2.0
 * Author: MotoPress
 * Author URI: https://motopress.com
 * Text Domain: block-editor-colors
 * Domain Path: /languages/
 */

use BlockEditorColors\BlockEditorColors;

if ( ! defined( 'BEC_PLUGIN_FILE' ) ) {
	define( 'BEC_PLUGIN_FILE', __FILE__ );
	define( 'BEC_PLUGIN_VERSION', '1.2.0' );
}

if ( ! class_exists( 'BlockEditorColors' ) ) {
	require_once dirname( __FILE__ ) . '/includes/BlockEditorColors.php';
}

function BlockEditorColors() {
	return BlockEditorColors::getInstance();
}

BlockEditorColors();
