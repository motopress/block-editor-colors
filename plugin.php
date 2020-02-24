<?php
/**
 * Plugin Name: Custom Editor Colors
 * Description: Change your editor colors.
 * Version: 0.0.1
 * Author: MotoPress
 * Author URI: https://motopress.com
 * Text Domain: custom-editor-colors
 * Domain Path: /languages/
 */

if (!defined('CEC_PLUGIN_FILE')) {
    define('CEC_PLUGIN_FILE', __FILE__);
}

if (!class_exists('CustomEditorColors')) {
    require_once dirname(__FILE__) . '/includes/CustomEditorColors.php';
}

function CustomEditorColors()
{
    return CustomEditorColors::getInstance();
}

add_action('init', 'CustomEditorColors');
