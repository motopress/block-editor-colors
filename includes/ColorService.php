<?php

namespace BlockEditorColors;


class ColorService {

	private $color_cpt_slug = 'custom_editor_color';

	private $initial_colors = [];
	private $edited_initial_colors = [];
	private $custom_colors = [];
	private $disabled_custom_colors = [];
	private $theme_mod_prefix = 'cec_';
	private $option_class_prefix = 'cec_css_prefix';
	private static $_instance = null;

	public static function getInstance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	protected function __construct() {
		$this->boot_general_options();
		$this->set_initial_colors();
		$this->set_custom_color_cpt();
		$this->set_custom_colors();

		add_action( 'admin_post_add_custom_color', array( $this, 'add_custom_color' ) );
		add_action( 'admin_post_edit_custom_color', array( $this, 'edit_custom_color' ) );
		add_action( 'admin_post_edit_initial_color', array( $this, 'edit_initial_color' ) );
		add_action( 'admin_post_edit_inactive_color', array( $this, 'edit_inactive_color' ) );
		add_action( 'admin_post_update_general_options', array( $this, 'update_general_options' ) );

		add_action( 'wp_head', array( $this, 'print_head_styles' ) );

		add_filter( 'block_editor_settings', array( $this, 'filter_block_editor_settings' ) );
	}

	public function boot_general_options() {
		add_option( $this->get_class_prefix_option_name(), ':root' );
	}

	public function get_initial_colors() {
		return $this->initial_colors;
	}

	public function get_edited_initial_colors() {
		return $this->edited_initial_colors;
	}

	public function set_initial_colors() {
		$theme_colors        = current( (array) get_theme_support( 'editor-color-palette' ) );
		$edited_theme_colors = [];

		if ( $theme_colors ) {
			foreach ( $theme_colors as $index => $color ) {
				$theme_colors[ $index ]['default-color'] = $color['color'];
				$edited_color                            = get_theme_mod( $this->theme_mod_prefix . $color['slug'], false );
				if ( $edited_color ) {
					$theme_colors[ $index ]['color'] = $edited_color;
					$edited_theme_colors[]           = $theme_colors[ $index ];
				}

			}
		}

		$this->initial_colors        = $theme_colors ? $theme_colors : [];
		$this->edited_initial_colors = $edited_theme_colors;
	}

	public function set_custom_color_cpt() {
		register_post_type( $this->color_cpt_slug, array(
			'label'  => esc_html__( 'Custom Color', 'block-editor-colors' ),
			'labels' => array(
				'name'          => esc_html__( 'Custom Colors', 'block-editor-colors' ),
				'singular_name' => esc_html__( 'Custom Color', 'block-editor-colors' ),
				'add_new'       => esc_html__( 'Add Custom Color', 'block-editor-colors' ),
				'add_new_item'  => esc_html__( 'Add Custom Color', 'block-editor-colors' ),
				'edit_item'     => esc_html__( 'Edit Custom Color', 'block-editor-colors' ),
				'new_item'      => esc_html__( 'New Custom Color', 'block-editor-colors' ),
				'view_item'     => esc_html__( 'View Custom Color', 'block-editor-colors' ),
				'search_items'  => esc_html__( 'Find Custom Color', 'block-editor-colors' ),
				'not_found'     => esc_html__( 'Custom Color Not Found', 'block-editor-colors' ),
				'menu_name'     => esc_html__( 'Custom Colors', 'block-editor-colors' ),
			),
			'public' => false,
		) );
	}

	private function set_custom_colors() {

		$args            = array(
			'post_type' => $this->color_cpt_slug,
			'orderby'   => 'date',
			'order'     => 'ASC'
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

	public function get_custom_colors( $disabled = false ) {
		if ( $disabled ) {
			return $this->disabled_custom_colors;
		}

		return $this->custom_colors;
	}

	public function add_custom_color() {

		if ( empty( $_POST ) || ! wp_verify_nonce( $_POST['create_custom_color_nonce'], 'create_custom_color' ) ) {
			wp_die( esc_html__( 'Denied', 'block-editor-colors' ) );
		}

		if ( ! isset( $_POST['new_name'] ) || ! isset( $_POST['new_slug'] ) || ! isset( $_POST['new_color'] ) ) {
			wp_die( esc_html__( 'Empty fields', 'block-editor-colors' ) );
		}

		$name  = $_POST['new_name'];
		$slug  = $_POST['new_slug'];
		$color = $_POST['new_color'];

		$this->update_custom_color( $name, $color, $slug );

		wp_redirect( SettingsPage::getAdminUrl() );
	}

	public function edit_custom_color() {

		if ( empty( $_POST ) || ! wp_verify_nonce( $_POST['update_custom_color_nonce'], 'update_custom_color' ) ) {
			wp_die( esc_html__( 'Denied', 'block-editor-colors' ) );
		}

		if ( ! isset( $_POST['color_id'] ) ) {
			wp_die( esc_html__( 'You should specify Color ID', 'block-editor-colors' ) );
		}

		$id = $_POST['color_id'];

		if ( isset( $_POST['disable'] ) ) {
			$this->disable_custom_color( $id );
			wp_redirect( SettingsPage::getAdminUrl() );

			return;
		}

		if ( ! isset( $_POST['name'] ) || ! isset( $_POST['color'] ) || ! isset( $_POST['update'] ) ) {
			wp_die( esc_html__( 'Empty fields', 'block-editor-colors' ) );
		}

		$name  = $_POST['name'];
		$color = $_POST['color'];

		$this->update_custom_color( $name, $color, false, $id );

		wp_redirect( SettingsPage::getAdminUrl() );

	}

	private function disable_custom_color( $id ) {
		wp_update_post( array(
			'ID'          => $id,
			'post_type'   => $this->color_cpt_slug,
			'post_status' => 'draft',
		) );
	}

	private function enable_custom_color( $id ) {
		wp_update_post( array(
			'ID'          => $id,
			'post_type'   => $this->color_cpt_slug,
			'post_status' => 'publish',
		) );
	}

	private function delete_custom_color( $id ) {
		wp_delete_post( $id );
	}

	private function update_custom_color( $name, $color, $slug = false, $id = false ) {

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

	public function edit_initial_color() {

		if ( empty( $_POST ) || ! wp_verify_nonce( $_POST['update_initial_color_nonce'], 'update_initial_color' ) ) {
			wp_die( esc_html__( 'Denied', 'block-editor-colors' ) );
		}

		if ( ! isset( $_POST['slug'] ) ) {
			wp_die( esc_html__( 'You should specify Color Slug', 'block-editor-colors' ) );
		}

		$slug = $_POST['slug'];

		if ( isset( $_POST['clear'] ) ) {
			$this->clear_initial_color( $slug );
			wp_redirect( SettingsPage::getAdminUrl() );

			return;
		}

		if ( ! isset( $_POST['color'] ) || ! isset( $_POST['update'] ) ) {
			wp_die( esc_html__( 'Empty fields', 'block-editor-colors' ) );
		}

		$color = $_POST['color'];

		$this->update_initial_color( $slug, $color );

		wp_redirect( SettingsPage::getAdminUrl() );

	}

	private function update_initial_color( $slug, $color ) {
		set_theme_mod( $this->theme_mod_prefix . $slug, $color );
	}

	private function clear_initial_color( $slug ) {
		remove_theme_mod( $this->theme_mod_prefix . $slug );
	}

	public function edit_inactive_color() {
		if ( empty( $_POST ) || ! wp_verify_nonce( $_POST['update_inactive_color_nonce'], 'update_inactive_color' ) ) {
			wp_die( esc_html__( 'Denied', 'block-editor-colors' ) );
		}

		if ( ! isset( $_POST['color_id'] ) ) {
			wp_die( esc_html__( 'You should specify Color ID', 'block-editor-colors' ) );
		}

		$id = $_POST['color_id'];

		if ( isset( $_POST['delete'] ) ) {
			$this->delete_custom_color( $id );
		}

		if ( isset( $_POST['restore'] ) ) {
			$this->enable_custom_color( $id );
		}

		wp_redirect( SettingsPage::getAdminUrl() );
	}

	public function get_style_classes_prefix() {

		return get_option( $this->get_class_prefix_option_name() );

	}

	public function generate_colors_css() {

		$custom_colors         = $this->get_custom_colors();
		$edited_initial_colors = $this->get_edited_initial_colors();

		$colors = array_merge( $edited_initial_colors, $custom_colors );
		$prefix = $this->get_style_classes_prefix();

		$css = '';
		foreach ( $colors as $color ) {
			$css .= PHP_EOL;
			$css .= <<<CSS
{$prefix} .has-{$color['slug']}-color{
	color: {$color['color']};
}
{$prefix} .has-{$color['slug']}-background-color{
	background-color: {$color['color']};
}
CSS;
			$css .= PHP_EOL;
		}

		return $css;
	}

	public function print_head_styles() {

		$style = $this->generate_colors_css();
		if ( $style == '' ) {
			return;
		}

		?>
        <style>
            <?php echo $style; ?>
        </style>
		<?php

	}

	public function filter_block_editor_settings( $settings ) {

		$initial_colors = $this->get_initial_colors();
		$new_colors     = $this->get_custom_colors();

		$initial_colors     = array_values( $initial_colors );
		$new_colors         = array_values( $new_colors );
		$settings['colors'] = array_merge( $initial_colors, $new_colors );

		return $settings;

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
			update_option( $prefix_option_name, $_POST[ $prefix_option_name ] );
		}

		wp_redirect( SettingsPage::getAdminUrl() );
	}
}

ColorService::getInstance();