
<?php

defined('ABSPATH') || exit;

class Imitator_Database {

    /**
     * Create plugin database tables.
     *
     * @return void
     */
    public static function create_tables() {
        global $wpdb;

        $table_name      = $wpdb->prefix . 'imitator_packages';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			backup_name VARCHAR(255) NOT NULL,
			archive_name VARCHAR(255) NOT NULL,
			database_name VARCHAR(255) NOT NULL,
			backup_type VARCHAR(50) DEFAULT 'manual',
			created_at DATETIME NOT NULL,
			PRIMARY KEY (id)
		) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
}
