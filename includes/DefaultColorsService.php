<?php

namespace BlockEditorColors;


class DefaultColorsService {

	private $colors = [];
	private $edited_colors = [];
	private $theme_colors = false;
	private $theme_mod_name = 'bec_theme_colors';
	private static $_instance = null;

	public static function getInstance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {
		$this->theme_colors = current( (array) get_theme_support( 'editor-color-palette' ) );
		$this->set_colors();

		add_action( 'admin_post_edit_initial_color', array( $this, 'edit_color' ) );
	}

	public function get_colors() {
		return $this->colors;
	}

	public function get_edited_colors() {
		return $this->edited_colors;
	}

	private function set_colors() {
		$theme_colors        = $this->theme_colors ? $this->theme_colors : $this->get_initial_colors();
		$edited_theme_colors = [];
		$colors_theme_mod    = get_theme_mod( $this->theme_mod_name, array() );

		if ( $theme_colors ) {
			foreach ( $theme_colors as $index => $color ) {
				$theme_colors[ $index ]['default-color'] = $color['color'];
				$edited_color                            = isset( $colors_theme_mod[ $color['slug'] ] ) ? $colors_theme_mod[ $color['slug'] ] : false;
				if ( $edited_color ) {
					$theme_colors[ $index ]['color'] = $edited_color;
					$edited_theme_colors[]           = $theme_colors[ $index ];
				}
			}
		}

		$this->colors        = $theme_colors ? $theme_colors : array();
		$this->edited_colors = $edited_theme_colors;
	}

	public function edit_color() {

		if ( empty( $_POST ) || ! wp_verify_nonce( $_POST['update_initial_color_nonce'], 'update_initial_color' ) ) {
			wp_die( esc_html__( 'Denied', 'block-editor-colors' ) );
		}

		if ( ! isset( $_POST['slug'] ) ) {
			wp_die( esc_html__( 'You must specify Color Slug', 'block-editor-colors' ) );
		}

		$slug = sanitize_title( $_POST['slug'] );

		if ( isset( $_POST['clear'] ) ) {
			$this->reset_color( $slug );
			wp_redirect( SettingsPage::getAdminUrl() );
			exit;
		}

		if ( ! isset( $_POST['color'] ) || ! isset( $_POST['update'] ) ) {
			wp_die( esc_html__( 'Empty fields', 'block-editor-colors' ) );
		}

		$color = sanitize_hex_color( $_POST['color'] );

		$this->update_color( $slug, $color );

		wp_redirect( SettingsPage::getAdminUrl() );
		exit;

	}

	private function update_color( $slug, $color ) {

		$colors = $this->get_edited_colors();

		if ( $colors ) {
			$colors = array_column( $colors, 'color', 'slug' );
		}

		$colors[ $slug ] = $color;

		set_theme_mod( $this->theme_mod_name, $colors );

	}

	private function reset_color( $slug ) {

		$colors = $this->get_edited_colors();

		if ( $colors ) {
			$colors = array_column( $colors, 'color', 'slug' );
		} else {
			return;
		}

		if ( array_key_exists( $slug, $colors ) ) {
			unset( $colors[ $slug ] );
		}

		set_theme_mod( $this->theme_mod_name, $colors );

	}

	private function get_initial_colors() {
		return array(
			array(
				'name'  => esc_html__( 'Pale pink' ),
				'slug'  => 'pale-pink',
				'color' => '#f78da7',
			),
			array(
				'name'  => esc_html__( 'Vivid red' ),
				'slug'  => 'vivid-red',
				'color' => '#cf2e2e'
			),
			array(
				'name'  => esc_html__( 'Luminous vivid orange' ),
				'slug'  => 'luminous-vivid-orange',
				'color' => '#ff6900',
			),
			array(
				'name'  => esc_html__( 'Luminous vivid amber' ),
				'slug'  => 'luminous-vivid-amber',
				'color' => '#fcb900',
			),
			array(
				'name'  => esc_html__( 'Light green cyan' ),
				'slug'  => 'light-green-cyan',
				'color' => '#7bdcb5',
			),
			array(
				'name'  => esc_html__( 'Vivid green cyan' ),
				'slug'  => 'vivid-green-cyan',
				'color' => '#00d084',
			),
			array(
				'name'  => esc_html__( 'Pale cyan blue' ),
				'slug'  => 'pale-cyan-blue',
				'color' => '#8ed1fc',
			),
			array(
				'name'  => esc_html__( 'Vivid cyan blue' ),
				'slug'  => 'vivid-cyan-blue',
				'color' => '#0693e3',
			),
			array(
				'name'  => esc_html__( 'Vivid purple' ),
				'slug'  => 'vivid-purple',
				'color' => '#9b51e0',
			),
			array(
				'name'  => esc_html__( 'Very light gray' ),
				'slug'  => 'very-light-gray',
				'color' => '#eeeeee',
			),
			array(
				'name'  => esc_html__( 'Cyan bluish gray' ),
				'slug'  => 'cyan-bluish-gray',
				'color' => '#abb8c3',
			),
			array(
				'name'  => esc_html__( 'Very dark gray' ),
				'slug'  => 'very-dark-gray',
				'color' => '#313131',
			),
		);
	}
}

DefaultColorsService::getInstance();