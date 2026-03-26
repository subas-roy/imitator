<?php

defined('ABSPATH') || exit;

class Imitator_Activator {

    /**
     * Run plugin activation tasks.
     *
     * @return void
     */
    public static function activate() {
        require_once IMITATOR_PATH . 'includes/class-database.php';
        Imitator_Database::create_tables();
    }
}
