<?php

class ColorService
{

    private $initial_colors = [];
    protected static $_instance = null;

    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    protected function __construct()
    {
        $this->set_initial_colors();
    }

    public function get_initial_colors()
    {
        return $this->initial_colors;
    }

    public function set_initial_colors()
    {
        $theme_colors = current((array)get_theme_support( 'editor-color-palette' ));
        $this->initial_colors = $theme_colors ? $theme_colors : [];
    }

}

ColorService::getInstance();