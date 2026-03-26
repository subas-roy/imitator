<?php

defined('ABSPATH') || exit;

class Imitator_File_Backup {

    /**
     * Create ZIP backup of wp-content.
     *
     * @param string $zip_path Full path to output zip file.
     * @return bool|\WP_Error
     */
    public function create($zip_path) {
        if (! class_exists('ZipArchive')) {
            return new WP_Error('zip_missing', __('ZipArchive is not available on this server.', 'imitator'));
        }

        $wp_content_dir = WP_CONTENT_DIR;
        $zip            = new ZipArchive();

        if (true !== $zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
            return new WP_Error('zip_create_failed', __('Could not create ZIP archive.', 'imitator'));
        }

        $this->add_folder_to_zip($wp_content_dir, $zip, basename($wp_content_dir));

        $zip->close();

        return true;
    }

    /**
     * Recursively add folder contents into ZIP.
     *
     * @param string     $folder_path Folder absolute path.
     * @param ZipArchive $zip         Zip object.
     * @param string     $zip_root    Root folder name inside zip.
     * @return void
     */
    private function add_folder_to_zip($folder_path, $zip, $zip_root) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($folder_path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        $upload_dir = wp_upload_dir();
        $exclude    = trailingslashit($upload_dir['basedir']) . 'imitator-backups';

        foreach ($iterator as $item) {
            $file_path = $item->getRealPath();

            // Exclude backup folder itself.
            if (strpos($file_path, $exclude) === 0) {
                continue;
            }

            $relative_path = $zip_root . '/' . ltrim(str_replace($folder_path, '', $file_path), '/\\');

            if ($item->isDir()) {
                $zip->addEmptyDir($relative_path);
            } else {
                $zip->addFile($file_path, $relative_path);
            }
        }
    }
}
