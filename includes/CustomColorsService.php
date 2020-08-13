<?php

namespace BlockEditorColors;


class CustomColorsService {

	private $color_cpt_slug = 'block_editor_color';
	private $custom_colors = [];
	private $disabled_custom_colors = [];
	private static $_instance = null;

	public function __construct() {
		$this->set_color_cpt();
		$this->set_colors();

		add_action( 'admin_post_add_custom_color', array( $this, 'add_color' ) );
		add_action( 'admin_post_edit_custom_color', array( $this, 'edit_color' ) );
		add_action( 'admin_post_edit_inactive_color', array( $this, 'edit_inactive_color' ) );
		if ( wp_doing_ajax() ) {
			add_action( 'wp_ajax_bec_update_color_order', array( $this, 'update_color_order' ) );
		}
	}

	public static function getInstance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function set_color_cpt() {
		register_post_type( $this->color_cpt_slug, array(
			'label'  => esc_html__( 'Editor Color', 'block-editor-colors' ),
			'labels' => array(
				'name'          => esc_html__( 'Editor Colors', 'block-editor-colors' ),
				'singular_name' => esc_html__( 'Editor Color', 'block-editor-colors' ),
				'add_new'       => esc_html__( 'Add Editor Color', 'block-editor-colors' ),
				'add_new_item'  => esc_html__( 'Add Editor Color', 'block-editor-colors' ),
				'edit_item'     => esc_html__( 'Edit Editor Color', 'block-editor-colors' ),
				'new_item'      => esc_html__( 'New Editor Color', 'block-editor-colors' ),
				'view_item'     => esc_html__( 'View Editor Color', 'block-editor-colors' ),
				'search_items'  => esc_html__( 'Find Editor Color', 'block-editor-colors' ),
				'not_found'     => esc_html__( 'Editor Color Not Found', 'block-editor-colors' ),
				'menu_name'     => esc_html__( 'Editor Colors', 'block-editor-colors' ),
			),
			'public' => false,
		) );
	}

	private function set_colors() {

		$args = array(
			'post_type' => $this->color_cpt_slug,
			'orderby'   => 'menu_order',
			'order'     => 'ASC',
			'nopaging'  => true
		);

		$query           = new \WP_Query( $args );
		$colors          = [];
		$disabled_colors = [];

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();

				if ( get_post_status() === 'publish' ) {
					$colors[ get_the_ID() ] = [
						'name'  => get_the_title(),
						'slug'  => get_post_meta( get_the_ID(), 'slug', true ),
						'color' => get_post_meta( get_the_ID(), 'color', true ),
					];
				} else {
					$disabled_colors[ get_the_ID() ] = [
						'name'  => get_the_title(),
						'slug'  => get_post_meta( get_the_ID(), 'slug', true ),
						'color' => get_post_meta( get_the_ID(), 'color', true ),
					];
				}

			}
		}

		wp_reset_postdata();

		$this->custom_colors          = $colors;
		$this->disabled_custom_colors = $disabled_colors;
	}

	public function get_colors( $disabled = false ) {
		if ( $disabled ) {
			return $this->disabled_custom_colors;
		}

		return $this->custom_colors;
	}

	public function add_color() {

		if ( empty( $_POST ) || ! wp_verify_nonce( $_POST['create_custom_color_nonce'], 'create_custom_color' ) ) {
			wp_die( esc_html__( 'Denied', 'block-editor-colors' ) );
		}

		if ( ! isset( $_POST['new_name'] ) || ! isset( $_POST['new_slug'] ) || ! isset( $_POST['new_color'] ) ) {
			wp_die( esc_html__( 'Empty fields', 'block-editor-colors' ) );
		}

		$name  = sanitize_text_field( $_POST['new_name'] );
		$slug  = sanitize_title( $_POST['new_slug'] );
		$color = sanitize_hex_color( $_POST['new_color'] );

		$this->update_color( $name, $color, $slug );

		wp_redirect( SettingsPage::getAdminUrl() );
		exit;
	}

	public function edit_color() {

		if ( empty( $_POST ) || ! wp_verify_nonce( $_POST['update_custom_color_nonce'], 'update_custom_color' ) ) {
			wp_die( esc_html__( 'Denied', 'block-editor-colors' ) );
		}

		if ( ! isset( $_POST['color_id'] ) ) {
			wp_die( esc_html__( 'You must specify Color ID', 'block-editor-colors' ) );
		}

		$id = absint( $_POST['color_id'] );

		if ( isset( $_POST['disable'] ) ) {
			$this->disable_color( $id );
			wp_redirect( SettingsPage::getAdminUrl() );
			exit;
		}

		if ( ! isset( $_POST['name'] ) || ! isset( $_POST['color'] ) || ! isset( $_POST['update'] ) ) {
			wp_die( esc_html__( 'Empty fields', 'block-editor-colors' ) );
		}

		$name  = sanitize_text_field( $_POST['name'] );
		$color = sanitize_hex_color( $_POST['color'] );

		$this->update_color( $name, $color, false, $id );

		wp_redirect( SettingsPage::getAdminUrl() );
		exit;

	}

	private function disable_color( $id ) {
		wp_update_post( array(
			'ID'          => $id,
			'post_type'   => $this->color_cpt_slug,
			'post_status' => 'draft',
		) );
	}

	private function enable_color( $id ) {
		wp_update_post( array(
			'ID'          => $id,
			'post_type'   => $this->color_cpt_slug,
			'post_status' => 'publish',
		) );
	}

	private function delete_color( $id ) {
		wp_delete_post( $id );
	}

	private function update_color( $name, $color, $slug = false, $id = false ) {

		$post_data = array(
			'post_type'   => $this->color_cpt_slug,
			'post_title'  => $name,
			'meta_input'  => array(
				'color' => $color,
			),
			'post_status' => 'publish',
		);

		if ( $slug ) {
			$post_data['meta_input']['slug'] = $slug;
		}

		if ( $id ) {
			$post_data['ID'] = $id;
		}

		wp_insert_post( $post_data );
	}

	public function edit_inactive_color() {
		if ( empty( $_POST ) || ! wp_verify_nonce( $_POST['update_inactive_color_nonce'], 'update_inactive_color' ) ) {
			wp_die( esc_html__( 'Denied', 'block-editor-colors' ) );
		}

		if ( ! isset( $_POST['color_id'] ) ) {
			wp_die( esc_html__( 'You must specify Color ID', 'block-editor-colors' ) );
		}

		$id = absint( $_POST['color_id'] );

		if ( isset( $_POST['delete'] ) ) {
			$this->delete_color( $id );
		}

		if ( isset( $_POST['restore'] ) ) {
			$this->enable_color( $id );
		}

		wp_redirect( SettingsPage::getAdminUrl() );
		exit;
	}

	public function update_color_order() {
		check_ajax_referer( 'block_editor_colors_nonce', 'nonce' );

		$colors = $_POST['colors'];

		foreach ( $colors as $order => $color_id ) {
			$updated = wp_update_post( [
				'ID'         => $color_id,
				'menu_order' => $order
			] );

			if ( ! $updated ) {
				wp_send_json_error();
			}
		}

		wp_send_json_success();
		wp_die();
	}

}

CustomColorsService::getInstance();