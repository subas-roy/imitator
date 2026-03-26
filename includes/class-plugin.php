<?php

defined('ABSPATH') || exit;

require_once IMITATOR_PATH . 'includes/class-admin.php';
require_once IMITATOR_PATH . 'includes/class-database.php';
require_once IMITATOR_PATH . 'includes/class-backup-manager.php';
require_once IMITATOR_PATH . 'includes/class-database-backup.php';

class Imitator_Plugin {

    /**
     * Run plugin hooks.
     *
     * @return void
     */
    public function run() {
        $admin          = new Imitator_Admin();
        $backup_manager = new Imitator_Backup_Manager();

        add_action('admin_menu', array($admin, 'register_menu'));
        add_action('admin_enqueue_scripts', array($admin, 'enqueue_assets'));

        add_action('admin_post_imitator_create_backup', array($backup_manager, 'handle_create_backup'));
    }
}
