<?php

namespace CustomEditorColors;

class SettingsPage {
	private static $page_title = 'Custom Colors';
	private static $menu_title = 'Custom Colors';
	private static $menu_slug = 'custom-colors';
	private static $capability = 'manage_options';
	private $position = '';
	private $colors_service = null;

	public function __construct() {
		add_options_page(
			esc_html__( self::$page_title, 'custom-editor-colors' ),
			esc_html__( self::$menu_title, 'custom-editor-colors' ),
			self::$capability,
			self::$menu_slug,
			array( $this, 'render_page' )
		);
		$this->colors_service = ColorService::getInstance();
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_page_scripts' ) );
	}

	public static function getSettingPageSlug() {
		return self::$menu_slug;
	}

	public static function getAdminUrl() {
		return admin_url( '/options-general.php?page=' . self::getSettingPageSlug() );
	}

	public function render_page() {
		?>
        <div class="wrap cec-wrapper">
            <h2>Custom Editor Colors</h2>

            <h3>Default Theme Colors</h3>
			<?php
			$this->render_initial_colors();
			?>

            <h3>Custom Colors</h3>
			<?php
			$this->render_custom_colors();
			?>

            <h3>Add New Color</h3>
			<?php
			$this->render_custom_color_creator();
			?>

            <h3>General Settings</h3>
			<?php
			$this->render_general_settings();
			?>
        </div>
		<?php
	}

	public function render_initial_colors() {
		$initial_colors = $this->colors_service->get_initial_colors();
		if ( ! $initial_colors ) {
			?>
            <strong><?php echo esc_html__( 'Looks like your theme do not register any colors. Colors which you can see in editor is hardcoded.', 'custom-editor-colors' ); ?> </strong>
			<?php
			return;
		}

		?>
        <div style="display: flex; flex-wrap: wrap;">
			<?php
			foreach ( $initial_colors as $color ):
				?>
                <form style="margin: 0 20px 40px 0;"
                      action="<?php echo esc_url( admin_url( 'admin-post.php' ) ) ?>" method="post">

					<?php wp_nonce_field( 'update_initial_color', 'update_initial_color_nonce' ); ?>

                    <input name="slug" type="hidden" value="<?php echo $color['slug']; ?>">
                    <input name="action" type="hidden" value="edit_initial_color">

                    <table>
                        <tr>
                            <td>
                                <div class="default-color"
                                     style="height: 40px; border: 2px solid black; background: <?php echo $color['default-color']; ?>"></div>
                            </td>
                            <td>
                                <div class="color-preview"
                                     style="height: 40px; border: 2px solid black; background: <?php echo $color['color']; ?>"></div>
                            </td>
                        </tr>
                        <tr>
                            <td>
								<?php esc_html_e( 'Name: ', 'custom-editor-colors' ); ?>
                            </td>
                            <td>
                                <input type="text" value="<?php echo $color['name']; ?>"
                                       placeholder="<?php esc_html_e( 'Color Name', 'custom-editor-colors' ); ?>"
                                       required disabled>
                            </td>
                        </tr>
                        <tr>
                            <td>
								<?php esc_html_e( 'Slug: ', 'custom-editor-colors' ); ?>
                            </td>
                            <td>
                                <input type="text"
                                       value="<?php echo $color['slug']; ?>"
                                       placeholder="<?php esc_html_e( 'color-name', 'custom-editor-colors' ); ?>"
                                       required disabled>
                            </td>
                        </tr>
                        <tr>
                            <td>
								<?php esc_html_e( 'Color: ', 'custom-editor-colors' ); ?>
                            </td>
                            <td>
                                <input type="text" name="color"
                                       class="cec-color-field-edit"
                                       value="<?php echo $color['color']; ?>" required>
                            </td>
                        </tr>
                    </table>

                    <button type="submit" name="update"
                            class="button button-primary"><?php esc_html_e( 'Update', 'custom-editor-colors' ); ?></button>

                    <button type="submit" name="clear"
                            class="button button-secondary"><?php esc_html_e( 'Clear', 'custom-editor-colors' ); ?></button>

                </form>
			<?php
			endforeach;
			?>
        </div>
		<?php
	}

	public function render_custom_colors() {
		$colors = $this->colors_service->get_custom_colors();
		?>
        <div style="display: flex; flex-wrap: wrap;">
			<?php
			foreach ( $colors as $id => $color ):
				?>
                <form style="margin: 0 20px 40px 0;"
                      action="<?php echo esc_url( admin_url( 'admin-post.php' ) ) ?>" method="post">

					<?php wp_nonce_field( 'update_custom_color', 'update_custom_color_nonce' ); ?>

                    <input name="color_id" type="hidden" value="<?php echo $id; ?>">
                    <input name="action" type="hidden" value="edit_custom_color">

                    <table>
                        <tr>
                            <td colspan="2">
                                <div class="color-preview"
                                     style="height: 40px; border: 2px solid black; background: <?php echo $color['color']; ?>"></div>
                            </td>
                        </tr>
                        <tr>
                            <td>
								<?php esc_html_e( 'Name: ', 'custom-editor-colors' ); ?>
                            </td>
                            <td>
                                <input type="text" name="name" value="<?php echo $color['name']; ?>"
                                       placeholder="<?php esc_html_e( 'Color Name', 'custom-editor-colors' ); ?>"
                                       required>
                            </td>
                        </tr>
                        <tr>
                            <td>
								<?php esc_html_e( 'Slug: ', 'custom-editor-colors' ); ?>
                            </td>
                            <td>
                                <input type="text" name="slug"
                                       value="<?php echo $color['slug']; ?>"
                                       placeholder="<?php esc_html_e( 'color-name', 'custom-editor-colors' ); ?>"
                                       required>
                            </td>
                        </tr>
                        <tr>
                            <td>
								<?php esc_html_e( 'Color: ', 'custom-editor-colors' ); ?>
                            </td>
                            <td>
                                <input type="text" name="color"
                                       class="cec-color-field-edit"
                                       value="<?php echo $color['color']; ?>" required>
                            </td>
                        </tr>
                    </table>

                    <button type="submit" name="update"
                            class="button button-primary"><?php esc_html_e( 'Update', 'custom-editor-colors' ); ?></button>
                    <button type="submit" name="delete"
                            class="button button-secondary"><?php esc_html_e( 'Delete', 'custom-editor-colors' ); ?></button>

                </form>
			<?php
			endforeach;
			?>
        </div>
		<?php
	}

	public function render_custom_color_creator() {
		?>
        <form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ) ?>" method="post">

			<?php wp_nonce_field( 'create_custom_color', 'create_custom_color_nonce' ); ?>

            <input type="hidden" name="action" value="add_custom_color">

            <table>
                <tr>
                    <td>
						<?php esc_html_e( 'Name: ', 'custom-editor-colors' ); ?>
                    </td>
                    <td>
                        <input type="text" name="new_name" value=""
                               placeholder="<?php esc_html_e( 'Color Name', 'custom-editor-colors' ); ?>" required>
                    </td>
                </tr>
                <tr>
                    <td>
						<?php esc_html_e( 'Slug: ', 'custom-editor-colors' ); ?>
                    </td>
                    <td>
                        <input type="text" name="new_slug" value=""
                               placeholder="<?php esc_html_e( 'color-name', 'custom-editor-colors' ); ?>" required>
                    </td>
                </tr>
                <tr>
                    <td>
						<?php esc_html_e( 'Color: ', 'custom-editor-colors' ); ?>
                    </td>
                    <td>
                        <input type="text" name="new_color" class="cec-color-field-edit" value="#ffffff"
                               required>
                    </td>
                </tr>
            </table>

            <input type="submit" name="submit" id="submit" class="button button-primary"
                   value="<?php esc_html_e( 'Add Color', 'custom-editor-colors' ); ?>">

        </form>
		<?php
	}

	public function render_general_settings() {
		?>
        <form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ) ?>" method="post">

			<?php wp_nonce_field( 'update_general_options', 'update_general_options_nonce' ); ?>
            <input name="action" type="hidden" value="update_general_options">

            <table>
                <tr>
                    <td>
						<?php esc_html_e( 'CSS class prefix:' ); ?>
                    </td>
                    <td>
                        <input type="text" value="<?php echo $this->colors_service->get_style_classes_prefix(); ?>"
                               name="<?php echo $this->colors_service->get_class_prefix_option_name(); ?>">
                    </td>
                </tr>
            </table>
            <button type="submit" name="save"
                    class="button button-primary"><?php esc_html_e( 'Update', 'custom-editor-colors' ); ?></button>
        </form>
		<?php
	}

	public function enqueue_page_scripts( $hook ) {
		if ( 'settings_page_' . self::getSettingPageSlug() !== $hook ) {
			return;
		}
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'cec-admin-js', plugins_url( '/assets/admin.js', CEC_PLUGIN_FILE ), array(
			'jquery',
			'wp-color-picker'
		) );
		wp_enqueue_style( 'cec-admin-style', plugins_url( '/assets/style.css', CEC_PLUGIN_FILE ) );
	}
}