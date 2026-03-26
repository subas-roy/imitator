<?php

defined('ABSPATH') || exit;

class Imitator_Backup_Manager {

    /**
     * Handle manual backup request.
     *
     * @return void
     */
    public function handle_create_backup() {
        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('You are not allowed to perform this action.', 'imitator'));
        }

        check_admin_referer('imitator_create_backup_action', 'imitator_nonce');

        $backup_dir = $this->get_backup_directory();

        if (is_wp_error($backup_dir)) {
            $this->redirect_with_notice('error', $backup_dir->get_error_message());
        }

        $timestamp = current_time('Y-m-d-H-i-s');
        $base_name = 'imitator-backup-' . $timestamp;

        $sql_file_name = $base_name . '.sql';
        $sql_file_path = trailingslashit($backup_dir) . $sql_file_name;

        $zip_file_name = $base_name . '.zip';
        $zip_file_path = trailingslashit($backup_dir) . $zip_file_name;

        // 1. Database backup.
        $database_backup = new Imitator_Database_Backup();
        $db_result       = $database_backup->export($sql_file_path);

        if (is_wp_error($db_result)) {
            $this->redirect_with_notice('error', $db_result->get_error_message());
        }

        // 2. wp-content ZIP backup.
        $file_backup = new Imitator_File_Backup();
        $zip_result  = $file_backup->create($zip_file_path);

        if (is_wp_error($zip_result)) {
            $this->redirect_with_notice('error', $zip_result->get_error_message());
        }

        // 3. Save DB record.
        $this->insert_backup_record(
            array(
                'backup_name'   => $base_name,
                'archive_name'  => $zip_file_name,
                'database_name' => $sql_file_name,
                'backup_type'   => 'manual',
            )
        );

        $this->redirect_with_notice('success', __('Full backup created successfully.', 'imitator'));
    }

    /**
     * Get backup storage directory.
     *
     * @return string|\WP_Error
     */
    public function get_backup_directory() {
        $upload_dir = wp_upload_dir();

        if (! empty($upload_dir['error'])) {
            return new WP_Error('upload_dir_error', $upload_dir['error']);
        }

        $backup_dir = trailingslashit($upload_dir['basedir']) . 'imitator-backups';

        if (! file_exists($backup_dir)) {
            wp_mkdir_p($backup_dir);
        }

        if (! file_exists($backup_dir . '/index.php')) {
            file_put_contents($backup_dir . '/index.php', "<?php\n// Silence is golden.\n");
        }

        return $backup_dir;
    }

    /**
     * Insert backup record into DB.
     *
     * @param array $data Backup metadata.
     * @return void
     */
    private function insert_backup_record($data) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'imitator_packages';

        $wpdb->insert(
            $table_name,
            array(
                'backup_name'   => $data['backup_name'],
                'archive_name'  => $data['archive_name'],
                'database_name' => $data['database_name'],
                'backup_type'   => $data['backup_type'],
                'created_at'    => current_time('mysql'),
            ),
            array('%s', '%s', '%s', '%s', '%s')
        );
    }

    /**
     * Redirect admin with notice.
     *
     * @param string $status  success|error
     * @param string $message Notice message.
     * @return void
     */
    private function redirect_with_notice($status, $message) {
        $url = add_query_arg(
            array(
                'page'             => 'imitator',
                'imitator_status'  => $status,
                'imitator_message' => rawurlencode($message),
            ),
            admin_url('admin.php')
        );

        wp_safe_redirect($url);
        exit;
    }
}
