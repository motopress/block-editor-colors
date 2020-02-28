<?php

namespace BlockEditorColors;

class AdminPages
{

    public function __construct()
    {
        $this->load_pages();
        add_action('admin_menu', array($this, 'create_pages'));
    }

    private function load_pages()
    {
        include_once dirname(__FILE__) . '/pages/SettingsPage.php';
    }

    public function create_pages()
    {
        new SettingsPage();
    }

}

new AdminPages();