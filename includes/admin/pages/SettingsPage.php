<?php
/**
 * Created by PhpStorm.
 * User: endzevich
 * Date: 23.02.2020
 * Time: 11:35
 */

class SettingsPage
{
    private $page_title = 'Custom Colors';
    private $menu_title = 'Custom Colors';
    private $menu_slug = 'custom-colors';
    private $capability = 'manage_options';
    private $position = '';
    private $colors_service = null;

    public function __construct()
    {
        add_options_page(esc_html__($this->page_title, 'custom-editor-colors'), esc_html__($this->menu_title, 'custom-editor-colors'), $this->capability, $this->menu_slug, array($this, 'render_page'));
        $this->colors_service = ColorService::getInstance();
    }

    public function render_page()
    {
        ?>
        <div class="wrap">
            <h2>Custom Editor Colors</h2>

            <h3>Default Theme Colors</h3>
            <?php
            $this->render_initial_colors();
            ?>

            <h3>Custom Colors</h3>
        </div>
        <?php
    }

    public function render_initial_colors(){
        $initial_colors = $this->colors_service->get_initial_colors();
        if(!$initial_colors){
            ?>
            <strong><?php echo esc_html__('Looks like your theme do not register any colors. Colors which you can see in editor is hardcoded.', 'custom-editor-colors');?> </strong>
            <?php
            return;
        }

        ?>
        <div style="display: flex; flex-wrap: wrap;">
        <?php
        foreach ($initial_colors as $color):
        ?>
            <div style="margin: 0 0 20px; display: flex; flex: 1 0 25%">
                <div style="width: 40px; margin-right: 20px; border: 2px solid black; background: <?php echo $color['color']?>"></div>
                Name: <?php echo $color['name'];?><br/>
                Slug: <?php echo $color['slug'];?><br/>
                Colors: <?php echo $color['color'];?><br/>
            </div>
        <?php
        endforeach;
        ?>
        </div>
        <?php
    }
}