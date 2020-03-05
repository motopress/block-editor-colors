<?php

namespace BlockEditorColors;


class OptionsService {

	private $option_class_prefix = 'bec_css_prefix';
	private static $_instance = null;

	public static function getInstance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {
		$this->boot_general_options();

		add_action( 'admin_post_update_general_options', array( $this, 'update_general_options' ) );
	}

	public function boot_general_options() {
		add_option( $this->get_class_prefix_option_name(), ':root' );
	}

	public function get_style_classes_prefix() {

		return get_option( $this->get_class_prefix_option_name() );

	}

	public function get_class_prefix_option_name() {
		return $this->option_class_prefix;
	}

	public function update_general_options() {

		if ( empty( $_POST ) || ! wp_verify_nonce( $_POST['update_general_options_nonce'], 'update_general_options' ) ) {
			wp_die( esc_html__( 'Denied', 'block-editor-colors' ) );
		}

		$prefix_option_name = $this->get_class_prefix_option_name();
		if ( isset( $_POST[ $prefix_option_name ] ) ) {
			$option_value = sanitize_text_field( $_POST[ $prefix_option_name ] );
			update_option( $prefix_option_name, $option_value );
		}

		wp_redirect( SettingsPage::getAdminUrl() );
		exit;
	}

}

OptionsService::getInstance();