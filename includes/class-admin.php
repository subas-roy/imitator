<?php

defined('ABSPATH') || exit;

class Imitator_Admin {

    /**
     * Register admin menu.
     *
     * @return void
     */
    public function register_menu() {
        add_menu_page(
            __('Imitator', 'imitator'),
            __('Imitator', 'imitator'),
            'manage_options',
            'imitator',
            array($this, 'render_admin_page'),
            'dashicons-database-export',
            26
        );
    }

    /**
     * Render admin page.
     *
     * @return void
     */
    public function render_admin_page() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'imitator_packages';
        $backups    = $wpdb->get_results("SELECT * FROM {$table_name} ORDER BY id DESC"); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

        include IMITATOR_PATH . 'templates/admin-page.php';
    }

    /**
     * Enqueue admin assets.
     *
     * @param string $hook Hook suffix.
     * @return void
     */
    public function enqueue_assets($hook) {
        if ('toplevel_page_imitator' !== $hook) {
            return;
        }

        wp_enqueue_style(
            'imitator-admin',
            IMITATOR_URL . 'assets/css/admin.css',
            array(),
            IMITATOR_VERSION
        );

        wp_enqueue_script(
            'imitator-admin',
            IMITATOR_URL . 'assets/js/admin.js',
            array(),
            IMITATOR_VERSION,
            true
        );
    }
}
