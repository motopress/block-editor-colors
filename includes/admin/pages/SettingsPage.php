<?php

namespace BlockEditorColors;

class SettingsPage {
	private static $page_title = 'Editor Colors';
	private static $menu_title = 'Editor Colors';
	private static $menu_slug = 'editor-colors';
	private static $capability = 'manage_options';
	private $colors_service = null;

	public function __construct() {
		add_options_page(
			esc_html__( self::$page_title, 'block-editor-colors' ),
			esc_html__( self::$menu_title, 'block-editor-colors' ),
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
        <div class="wrap bec-wrapper">
            <h2><?php esc_html_e( 'Block Editor Colors', 'block-editor-colors' ); ?></h2>
            <p><?php esc_html_e( 'This plugin allows you to change colors for a block editor that are registered with a 
            theme, or create your own colors, which then appear in the block editor in the color selector.', 'block-editor-colors' ); ?></p>

            <hr>

			<?php
			$this->render_initial_colors();
			?>

            <hr>

			<?php
			$this->render_custom_colors();
			?>

            <hr>

            <h3>General Settings</h3>
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

		$initial_colors = $this->colors_service->get_initial_colors();
		?>
        <h3><?php esc_html_e( 'Default Theme Colors', 'block-editor-colors' ); ?></h3>
        <p><?php esc_html_e( 'We do not recommend changing the colors that are set by the theme, as they are tied to a specific theme.
         When changing the theme there will be new colors.', 'block-editor-colors' ); ?></p>

		<?php
		if ( ! $initial_colors ) {
			?>
            <strong><?php echo esc_html__( 'Looks like your theme do not register any colors. Colors which you can see in editor is hardcoded.', 'block-editor-colors' ); ?> </strong>
			<?php
			return;
		}

		?>
        <div class="bec-color-tiles">
			<?php
			foreach ( $initial_colors as $color ):
				?>
                <form class="bec-color-tile"
                      action="<?php echo esc_url( admin_url( 'admin-post.php' ) ) ?>" method="post">

					<?php wp_nonce_field( 'update_initial_color', 'update_initial_color_nonce' ); ?>

                    <input name="slug" type="hidden" value="<?php echo $color['slug']; ?>">
                    <input name="action" type="hidden" value="edit_initial_color">

                    <table class="bec-color-table">
                        <tr>
                            <td class="bec-color-cell">
                                <div class="default-color"
                                     style="height: 40px; border: 2px solid black; background: <?php echo $color['default-color']; ?>"></div>
                            </td>
                            <td class="bec-color-cell">
                                <div class="bec-color-preview"
                                     style="background: <?php echo $color['color']; ?>"></div>
                            </td>
                        </tr>
                        <tr>
                            <td>
								<?php esc_html_e( 'Color: ', 'block-editor-colors' ); ?>
                            </td>
                            <td>
                                <input type="text" name="color"
                                       class="bec-color-field"
                                       value="<?php echo $color['color']; ?>" required>
                            </td>
                        </tr>
                        <tr>
                            <td>
								<?php esc_html_e( 'Name: ', 'block-editor-colors' ); ?>
                            </td>
                            <td>
                                <input type="text" value="<?php echo $color['name']; ?>"
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
                                       value="<?php echo $color['slug']; ?>"
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
		$colors = $this->colors_service->get_custom_colors();
		?>
        <h3><?php esc_html_e( 'Custom Colors', 'block-editor-colors' ); ?></h3>
        <p><?php esc_html_e( 'The colors you created when changing the theme will not be lost. The created color 
        can be deactivated, then it will not be displayed in the block editor color palette.', 'block-editor-colors' ); ?></p>

        <p><strong><?php esc_html_e( 'Name - ', 'block-editor-colors' ); ?></strong>
			<?php esc_html_e( 'will be displayed as the color name in the block editor.', 'block-editor-colors' ); ?>
        </p>
        <p><strong><?php esc_html_e( 'Slug - ', 'block-editor-colors' ); ?></strong>
			<?php esc_html_e( 'will be used to generate CSS classes for color.', 'block-editor-colors' ); ?>
            <strong><?php esc_html_e( 'Only Latin lowercase letters, numbers, hyphens and underscores are allowed. 
            The slug must be unique.', 'block-editor-colors' ); ?></strong>
			<?php esc_html_e( '("name12" - bad slug use "name-12" instead)', 'block-editor-colors' ); ?></p>

        <div class="bec-color-tiles">
			<?php
			foreach ( $colors as $id => $color ):
				?>
                <form class="bec-color-tile"
                      action="<?php echo esc_url( admin_url( 'admin-post.php' ) ) ?>" method="post">

					<?php wp_nonce_field( 'update_custom_color', 'update_custom_color_nonce' ); ?>

                    <input name="color_id" type="hidden" value="<?php echo $id; ?>">
                    <input name="action" type="hidden" value="edit_custom_color">

                    <table class="bec-color-table">
                        <tr>
                            <td colspan="2" class="bec-color-cell">
                                <div class="bec-color-preview"
                                     style="background: <?php echo $color['color']; ?>"></div>
                            </td>
                        </tr>
                        <tr>
                            <td>
								<?php esc_html_e( 'Color: ', 'block-editor-colors' ); ?>
                            </td>
                            <td>
                                <input type="text" name="color"
                                       class="bec-color-field"
                                       value="<?php echo $color['color']; ?>" required>
                            </td>
                        </tr>
                        <tr>
                            <td>
								<?php esc_html_e( 'Name: ', 'block-editor-colors' ); ?>
                            </td>
                            <td>
                                <input type="text" name="name" value="<?php echo $color['name']; ?>"
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
                                       value="<?php echo $color['slug']; ?>"
                                       placeholder="<?php esc_html_e( 'color-name', 'block-editor-colors' ); ?>"
                                       required disabled>
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
        <form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ) ?>" method="post">

			<?php wp_nonce_field( 'update_general_options', 'update_general_options_nonce' ); ?>
            <input name="action" type="hidden" value="update_general_options">

            <table style="max-width: 900px">
                <tr>
                    <td style="width: 100px">
						<?php esc_html_e( 'CSS class prefix:' ); ?>
                    </td>
                    <td>
                        <input type="text" value="<?php echo $this->colors_service->get_style_classes_prefix(); ?>"
                               name="<?php echo $this->colors_service->get_class_prefix_option_name(); ?>">
                    </td>
                    <td>
                        <?php esc_html_e('These CSS classes/selectors will be used in style generation, 
                        and will be added before the color classes.' , 'block-editor-colors');?>
                        <br/>
                        <i><?php esc_html_e('For example: .entry-content .has-my-color-background-color', 'block-editor-colors');?></i>
                    </td>
                </tr>
            </table>
            <button type="submit" name="save"
                    class="button button-primary"><?php esc_html_e( 'Update', 'block-editor-colors' ); ?></button>
        </form>
		<?php
	}

	public function render_disabled_custom_colors() {
		$colors = $this->colors_service->get_custom_colors( true );
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

                    <input name="color_id" type="hidden" value="<?php echo $id;?>">
                    <input name="action" type="hidden" value="edit_inactive_color">

                    <table>
                        <tr>
                            <td rowspan="5" style="width: 40px; background: <?php echo $color['color']; ?>">
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
	                            <?php echo $color['name']; ?>
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>
								<?php esc_html_e( 'Slug: ', 'block-editor-colors' ); ?>
                            </td>
                            <td>
	                            <?php echo $color['slug']; ?>
                            </td>
                        </tr>

                        <tr>
                            <td></td>
                            <td>
			                    <?php esc_html_e( 'Color: ', 'block-editor-colors' ); ?>
                            </td>
                            <td>
	                            <?php echo $color['color']; ?>
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
			'wp-color-picker'
		) );
		wp_enqueue_style( 'cec-admin-style', plugins_url( '/assets/style.css', BEC_PLUGIN_FILE ) );
	}
}