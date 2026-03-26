<?php

defined('ABSPATH') || exit;

class Imitator_Database_Backup {

    /**
     * Export WordPress database into a SQL file.
     *
     * @param string $file_path Full path of output SQL file.
     * @return bool|\WP_Error
     */
    public function export($file_path) {
        global $wpdb;

        $tables = $wpdb->get_col('SHOW TABLES');

        if (empty($tables)) {
            return new WP_Error('no_tables', __('No database tables found.', 'imitator'));
        }

        $handle = fopen($file_path, 'w');

        if (! $handle) {
            return new WP_Error('file_open_failed', __('Could not create SQL backup file.', 'imitator'));
        }

        fwrite($handle, "-- Imitator Database Backup\n");
        fwrite($handle, '-- Generated: ' . current_time('mysql') . "\n");
        fwrite($handle, '-- Site: ' . home_url() . "\n\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n\n");

        foreach ($tables as $table) {
            $create_table = $wpdb->get_row("SHOW CREATE TABLE `{$table}`", ARRAY_N);

            if (isset($create_table[1])) {
                fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");
                fwrite($handle, $create_table[1] . ";\n\n");
            }

            $rows = $wpdb->get_results("SELECT * FROM `{$table}`", ARRAY_A);

            if (! empty($rows)) {
                foreach ($rows as $row) {
                    $values = array();

                    foreach ($row as $value) {
                        if (is_null($value)) {
                            $values[] = 'NULL';
                        } else {
                            $values[] = "'" . esc_sql($value) . "'";
                        }
                    }

                    $columns = '`' . implode('`, `', array_keys($row)) . '`';
                    $values  = implode(', ', $values);

                    fwrite($handle, "INSERT INTO `{$table}` ({$columns}) VALUES ({$values});\n");
                }

                fwrite($handle, "\n");
            }
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($handle);

        return true;
    }
}
