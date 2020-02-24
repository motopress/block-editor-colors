<?php

class CustomEditorColors
{

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
        $this->load_includes();
    }

    public function load_includes()
    {
        include_once dirname(__FILE__) . '/ColorService.php';
        include_once dirname(__FILE__) . '/admin/AdminPages.php';
    }

}