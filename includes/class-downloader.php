<?php

defined('ABSPATH') || exit;

class Imitator_Downloader {

    /**
     * Handle backup download request.
     *
     * @return void
     */
    public function handle_download() {
        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('You are not allowed to download backups.', 'imitator'));
        }

        check_admin_referer('imitator_download_backup_action', 'imitator_nonce');

        $backup_id = isset($_GET['backup_id']) ? absint($_GET['backup_id']) : 0;
        $type      = isset($_GET['type']) ? sanitize_key($_GET['type']) : '';

        if (! $backup_id || ! in_array($type, array('archive', 'database'), true)) {
            wp_die(esc_html__('Invalid backup request.', 'imitator'));
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'imitator_packages';

        $backup = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE id = %d",
                $backup_id
            )
        );

        if (! $backup) {
            wp_die(esc_html__('Backup record not found.', 'imitator'));
        }

        $file_name = ('archive' === $type) ? $backup->archive_name : $backup->database_name;

        if (empty($file_name)) {
            wp_die(esc_html__('Requested backup file is not available.', 'imitator'));
        }

        $backup_dir = $this->get_backup_directory();
        $file_path  = trailingslashit($backup_dir) . $file_name;

        if (! file_exists($file_path) || ! is_readable($file_path)) {
            wp_die(esc_html__('Backup file does not exist.', 'imitator'));
        }

        $this->stream_file($file_path, $file_name);
        exit;
    }

    /**
     * Get backup storage directory.
     *
     * @return string
     */
    private function get_backup_directory() {
        $upload_dir = wp_upload_dir();
        return trailingslashit($upload_dir['basedir']) . 'imitator-backups';
    }

    /**
     * Stream file to browser.
     *
     * @param string $file_path Full file path.
     * @param string $file_name Download filename.
     * @return void
     */
    private function stream_file($file_path, $file_name) {
        if (ob_get_length()) {
            ob_end_clean();
        }

        $mime_type = wp_check_filetype($file_name);
        $type      = ! empty($mime_type['type']) ? $mime_type['type'] : 'application/octet-stream';

        header('Content-Description: File Transfer');
        header('Content-Type: ' . $type);
        header('Content-Disposition: attachment; filename="' . basename($file_name) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file_path));

        readfile($file_path);
    }
}
