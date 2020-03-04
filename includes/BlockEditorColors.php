<?php

namespace BlockEditorColors;

class BlockEditorColors {

	private static $_instance = null;

	public static function getInstance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {
		add_action( 'init', array( $this, 'setup_color_service' ) );
		add_action( 'plugin_action_links_' . plugin_basename( BEC_PLUGIN_FILE ), array( $this, 'action_links' ) );
	}

	public function setup_color_service() {
		include_once dirname( __FILE__ ) . '/OptionsService.php';
		include_once dirname( __FILE__ ) . '/DefaultColorsService.php';
		include_once dirname( __FILE__ ) . '/CustomColorsService.php';
		include_once dirname( __FILE__ ) . '/ColorsService.php';
		include_once dirname( __FILE__ ) . '/admin/AdminPages.php';
	}

	public function action_links( $links ) {

		$settings_page_url = SettingsPage::getAdminUrl();

		$plugin_links = array(
			'<a href=' . esc_url( $settings_page_url ) . '>' . esc_html__( 'Settings', 'block-editor-colors' ) . '</a>'
		);

		return array_merge( $links, $plugin_links );
	}

}