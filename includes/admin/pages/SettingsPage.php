<?php

namespace BlockEditorColors;

class SettingsPage {

	private static $menu_slug = 'editor-colors';
	private static $capability = 'manage_options';
	private $custom_colors_service = null;
	private $default_colors_service = null;
	private $options_service = null;

	public function __construct() {
		add_options_page(
			esc_html__( 'Editor Colors', 'block-editor-colors' ),
			esc_html__( 'Editor Colors', 'block-editor-colors' ),
			self::$capability,
			self::$menu_slug,
			array( $this, 'render_page' )
		);
		$this->custom_colors_service  = CustomColorsService::getInstance();
		$this->default_colors_service = DefaultColorsService::getInstance();
		$this->options_service        = OptionsService::getInstance();
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
        <div class="wrap bec-wrapper">
            <h2><?php esc_html_e( 'Block Editor Colors', 'block-editor-colors' ); ?></h2>
            <p><?php esc_html_e( 'Change block editor colors that are registered with a theme or create your own colors. They appear in the color palette of the block editor.', 'block-editor-colors' ); ?></p>

            <hr>

			<?php
			$this->render_custom_colors();
			?>

            <hr>

			<?php
			$this->render_initial_colors();
			?>

            <hr>

			<?php
			$this->render_general_settings();
			?>

            <hr>

			<?php
			$this->render_disabled_custom_colors();
			?>
        </div>
		<?php
	}

	public function render_initial_colors() {

		$initial_colors = $this->default_colors_service->get_colors();
		?>
        <h3><?php esc_html_e( 'Default Colors', 'block-editor-colors' ); ?></h3>
        <p><?php esc_html_e( 'The colors of the active theme. They change when you switch a theme.', 'block-editor-colors' ); ?></p>

		<?php
		if ( ! $initial_colors ) {
			?>
            <strong><?php esc_html_e( 'Looks like your theme does not have any registered colors', 'block-editor-colors' ); ?> </strong>
			<?php
			return;
		}

		?>
        <div class="bec-color-tiles" id="bec-default-colors">
			<?php
			foreach ( $initial_colors as $color ):
				?>
                <form class="bec-color-tile"
                      action="<?php echo esc_url( admin_url( 'admin-post.php' ) ) ?>" method="post" autocomplete="off">

					<?php wp_nonce_field( 'update_initial_color', 'update_initial_color_nonce' ); ?>

                    <input name="slug" type="hidden" value="<?php echo esc_attr( $color['slug'] ); ?>">
                    <input name="action" type="hidden" value="edit_initial_color">

                    <table class="bec-color-table">
                        <tr>
                            <td class="bec-color-cell">
                                <div class="default-color"
                                     style="height: 40px; border: 2px solid black; background: <?php echo esc_attr( $color['default-color'] ); ?>"></div>
                            </td>
                            <td class="bec-color-cell">
                                <div class="bec-color-preview"
                                     style="background: <?php echo esc_attr( $color['color'] ); ?>"></div>
                            </td>
                        </tr>
                        <tr>
                            <td>
								<?php esc_html_e( 'Color: ', 'block-editor-colors' ); ?>
                            </td>
                            <td>
                                <input type="text" name="color"
                                       class="bec-color-field"
                                       value="<?php echo esc_attr( $color['color'] ); ?>" required>
                            </td>
                        </tr>
                        <tr>
                            <td>
								<?php esc_html_e( 'Name: ', 'block-editor-colors' ); ?>
                            </td>
                            <td>
                                <input type="text" value="<?php echo esc_attr( $color['name'] ); ?>"
                                       placeholder="<?php esc_html_e( 'Color Name', 'block-editor-colors' ); ?>"
                                       required disabled>
                            </td>
                        </tr>
                        <tr>
                            <td>
								<?php esc_html_e( 'Slug: ', 'block-editor-colors' ); ?>
                            </td>
                            <td>
                                <input type="text"
                                       value="<?php echo esc_attr( $color['slug'] ); ?>"
                                       placeholder="<?php esc_html_e( 'color-name', 'block-editor-colors' ); ?>"
                                       required disabled>
                            </td>
                        </tr>
                        <tr>
                            <td class="bec-color-submit-cell" colspan="2">
                                <button type="submit" name="update"
                                        class="button button-primary"><?php esc_html_e( 'Update', 'block-editor-colors' ); ?></button>

                                <button type="submit" name="clear"
                                        class="button button-secondary"><?php esc_html_e( 'Reset', 'block-editor-colors' ); ?></button>
                            </td>
                        </tr>
                    </table>
                </form>
			<?php
			endforeach;
			?>
        </div>
		<?php
	}

	public function render_custom_colors() {
		$colors = $this->custom_colors_service->get_colors();
		?>
        <h3><?php esc_html_e( 'Custom Colors', 'block-editor-colors' ); ?></h3>
        <p><?php esc_html_e( 'Create new colors using the form below to extend the color palette of the block editor. You will not lose these colors when you change a theme. These colors can be deactivated or transfered via XML.', 'block-editor-colors' ); ?></p>
		<p><?php esc_html_e( 'Name - will be displayed as the color name in the block editor.', 'block-editor-colors' ); ?><br/>
			<?php esc_html_e( 'Slug - will be used to generate CSS classes for a color.', 'block-editor-colors' ); ?>
			<strong><?php esc_html_e( 'Only Latin lowercase letters, numbers, hyphens and underscores are allowed. The slug must be unique.', 'block-editor-colors' ); ?></strong>
			<?php esc_html_e( '("name12" - bad slug use "name-12" instead)', 'block-editor-colors' ); ?>
        </p>
        <div class="bec-color-tiles" id="bec-custom-colors">
			<?php
			foreach ( $colors as $id => $color ):
				?>
                <form class="bec-color-tile"
                      action="<?php echo esc_url( admin_url( 'admin-post.php' ) ) ?>" method="post">

					<?php wp_nonce_field( 'update_custom_color', 'update_custom_color_nonce' ); ?>

                    <input name="color_id" type="hidden" value="<?php echo esc_attr( $id ); ?>">
                    <input name="action" type="hidden" value="edit_custom_color">

                    <table class="bec-color-table">
                        <tr>
                            <td colspan="2" class="bec-color-cell">
                                <div class="bec-color-preview"
                                     style="background: <?php echo esc_attr( $color['color'] ); ?>"></div>
                            </td>
                        </tr>
                        <tr>
                            <td>
								<?php esc_html_e( 'Color: ', 'block-editor-colors' ); ?>
                            </td>
                            <td>
                                <input type="text" name="color"
                                       class="bec-color-field"
                                       value="<?php echo esc_attr( $color['color'] ); ?>" required>
                            </td>
                        </tr>
                        <tr>
                            <td>
								<?php esc_html_e( 'Name: ', 'block-editor-colors' ); ?>
                            </td>
                            <td>
                                <input type="text" name="name" value="<?php echo esc_attr( $color['name'] ); ?>"
                                       placeholder="<?php esc_html_e( 'Color Name', 'block-editor-colors' ); ?>"
                                       required>
                            </td>
                        </tr>
                        <tr>
                            <td>
								<?php esc_html_e( 'Slug: ', 'block-editor-colors' ); ?>
                            </td>
                            <td>
                                <input type="text" name="slug"
                                       value="<?php echo esc_attr( $color['slug'] ); ?>"
                                       placeholder="<?php esc_html_e( 'color-name', 'block-editor-colors' ); ?>"
                                       disabled>
                            </td>
                        </tr>
                        <tr>
                            <td class="bec-color-submit" colspan="2">
                                <button type="submit" name="update"
                                        class="button button-primary"><?php esc_html_e( 'Update', 'block-editor-colors' ); ?></button>
                                <button type="submit" name="disable"
                                        class="button button-secondary"><?php esc_html_e( 'Disable', 'block-editor-colors' ); ?></button>
                            </td>
                        </tr>
                    </table>
	                <div class="status-icon move-icon" title="<?php esc_html_e('Drag the card to change the order of colors.'); ?>"><span class="dashicons dashicons-move"></span></div>
	                <div class="status-icon moving-icon" title="<?php esc_html_e('Updating.'); ?>"><span class="dashicons dashicons-update"></span></div>
	                <div class="status-icon update-error-icon" title="<?php esc_html_e('Something went wrong during the color update! Please try again.'); ?>"><span class="dashicons dashicons-warning"></span></div>
                </form>
			<?php
			endforeach;

			$this->render_custom_color_creator();

			?>
        </div>
		<?php
	}

	public function render_custom_color_creator() {
		?>
        <form class="bec-color-tile bec-color-creator" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ) ?>"
              method="post">

			<?php wp_nonce_field( 'create_custom_color', 'create_custom_color_nonce' ); ?>

            <input type="hidden" name="action" value="add_custom_color">

            <table class="bec-color-table">
                <tr>
                    <td colspan="2" class="bec-color-cell">
                        <div class="bec-color-preview"
                             style="background: #ffff"></div>
                    </td>
                </tr>
                <tr>
                    <td>
						<?php esc_html_e( 'Color: ', 'block-editor-colors' ); ?>
                    </td>
                    <td>
                        <input type="text" name="new_color" class="bec-color-field" value="#ffffff"
                               required>
                    </td>
                </tr>
                <tr>
                    <td>
						<?php esc_html_e( 'Name: ', 'block-editor-colors' ); ?>
                    </td>
                    <td>
                        <input type="text" name="new_name" value=""
                               placeholder="<?php esc_html_e( 'Color Name', 'block-editor-colors' ); ?>" required>
                    </td>
                </tr>
                <tr>
                    <td>
						<?php esc_html_e( 'Slug: ', 'block-editor-colors' ); ?>
                    </td>
                    <td>
                        <input type="text" name="new_slug" value=""
								title="<?php esc_html_e( 'Only Latin lowercase letters, numbers, hyphens and underscores are allowed. The slug must be unique.', 'block-editor-colors' ); ?>"
                               placeholder="<?php esc_html_e( 'color-name', 'block-editor-colors' ); ?>" required>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="custom-color-submit">
                        <button type="submit" name="submit" id="submit" class="button button-primary">
							<?php esc_html_e( 'Add Color', 'block-editor-colors' ); ?>
                        </button>
                    </td>
                </tr>
            </table>
        </form>
		<?php
	}

	public function render_general_settings() {
		?>
        <h3><?php esc_html_e( 'General Settings', 'block-editor-colors' ); ?></h3>
        <form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ) ?>" method="post">

			<?php wp_nonce_field( 'update_general_options', 'update_general_options_nonce' ); ?>
            <input name="action" type="hidden" value="update_general_options">

            <table style="max-width: 900px">
                <tr>
                    <td style="width: 100px">
						<?php esc_html_e( 'CSS class prefix:', 'block-editor-colors' ); ?>
                    </td>
                    <td>
                        <input type="text"
                               value="<?php echo esc_attr( $this->options_service->get_style_classes_prefix() ); ?>"
                               name="<?php echo esc_attr( $this->options_service->get_class_prefix_option_name() ); ?>">
                    </td>
                    <td>
						<?php esc_html_e( 'This prefix will be used in style generation, and will be added before the color classes.', 'block-editor-colors' ); ?>
                        <br/>
                        <i><?php esc_html_e( 'For example: .entry-content', 'block-editor-colors' ); ?></i>
                    </td>
                </tr>
            </table>
            <p>
                <button type="submit" name="save"
                        class="button button-primary"><?php esc_html_e( 'Update', 'block-editor-colors' ); ?></button>
            </p>
        </form>
		<?php
	}

	public function render_disabled_custom_colors() {
		$colors = $this->custom_colors_service->get_colors( true );
		if ( ! $colors ) {
			return;
		}
		?>
        <h4><?php esc_html_e( 'Inactive Custom Colors', 'block-editor-colors' ); ?></h4>
        <div class="bec-inactive-colors">
			<?php
			foreach ( $colors as $id => $color ):
				?>
                <form class="bec-inactive-color"
                      action="<?php echo esc_url( admin_url( 'admin-post.php' ) ) ?>" method="post">

					<?php wp_nonce_field( 'update_inactive_color', 'update_inactive_color_nonce' ); ?>

                    <input name="color_id" type="hidden" value="<?php echo esc_attr( $id ); ?>">
                    <input name="action" type="hidden" value="edit_inactive_color">

                    <table>
                        <tr>
                            <td rowspan="5" style="width: 40px; background: <?php echo esc_attr( $color['color'] ); ?>">
                            </td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>
								<?php esc_html_e( 'Name: ', 'block-editor-colors' ); ?>
                            </td>
                            <td>
								<?php echo esc_html( $color['name'] ); ?>
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>
								<?php esc_html_e( 'Slug: ', 'block-editor-colors' ); ?>
                            </td>
                            <td>
								<?php echo esc_html( $color['slug'] ); ?>
                            </td>
                        </tr>

                        <tr>
                            <td></td>
                            <td>
								<?php esc_html_e( 'Color: ', 'block-editor-colors' ); ?>
                            </td>
                            <td>
								<?php echo esc_html( $color['color'] ); ?>
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td class="bec-color-submit-cell" colspan="2">
                                <button type="submit" name="restore"
                                        class="bec-link-button"><?php esc_html_e( 'Restore', 'block-editor-colors' ); ?></button>
                                |
                                <button type="submit" name="delete"
                                        class="bec-link-button red"><?php esc_html_e( 'Delete', 'block-editor-colors' ); ?></button>
                            </td>
                        </tr>
                    </table>
                </form>
			<?php
			endforeach;
			?>
        </div>
		<?php
	}

	public function enqueue_page_scripts( $hook ) {
		if ( 'settings_page_' . self::getSettingPageSlug() !== $hook ) {
			return;
		}
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'cec-admin-js', plugins_url( '/assets/admin.js', BEC_PLUGIN_FILE ), array(
			'jquery',
			'wp-color-picker',
			'jquery-ui-sortable'
		), BEC_PLUGIN_VERSION );

		wp_localize_script( 'cec-admin-js', 'BlockEditorColors',
			array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('block_editor_colors_nonce')
			)
		);

		wp_enqueue_style( 'cec-admin-style', plugins_url( '/assets/style.css', BEC_PLUGIN_FILE ), array(), BEC_PLUGIN_VERSION );
	}
}