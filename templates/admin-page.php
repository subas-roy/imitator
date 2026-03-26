<?php
defined('ABSPATH') || exit;
?>

<div class="wrap">
    <h1><?php esc_html_e('Imitator Backup Manager', 'imitator'); ?></h1>

    <?php if (isset($_GET['imitator_status'], $_GET['imitator_message'])) : ?>
        <?php
        $status  = sanitize_text_field(wp_unslash($_GET['imitator_status']));
        $message = sanitize_text_field(wp_unslash($_GET['imitator_message']));
        $class   = 'success' === $status ? 'notice notice-success' : 'notice notice-error';
        ?>
        <div class="<?php echo esc_attr($class); ?> is-dismissible">
            <p><?php echo esc_html(rawurldecode($message)); ?></p>
        </div>
    <?php endif; ?>

    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin: 20px 0;">
        <input type="hidden" name="action" value="imitator_create_backup">
        <?php wp_nonce_field('imitator_create_backup_action', 'imitator_nonce'); ?>

        <button class="button button-primary" type="submit">
            <?php esc_html_e('Create Full Backup', 'imitator'); ?>
        </button>
    </form>

    <h2><?php esc_html_e('Backup History', 'imitator'); ?></h2>

    <table class="widefat striped">
        <thead>
            <tr>
                <th><?php esc_html_e('ID', 'imitator'); ?></th>
                <th><?php esc_html_e('Backup Name', 'imitator'); ?></th>
                <th><?php esc_html_e('Archive', 'imitator'); ?></th>
                <th><?php esc_html_e('Database', 'imitator'); ?></th>
                <th><?php esc_html_e('Type', 'imitator'); ?></th>
                <th><?php esc_html_e('Created At', 'imitator'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (! empty($backups)) : ?>
                <?php foreach ($backups as $backup) : ?>
                    <tr>
                        <td><?php echo esc_html($backup->id); ?></td>
                        <td><?php echo esc_html($backup->backup_name); ?></td>

                        <td>
                            <?php if (! empty($backup->archive_name)) : ?>
                                <a class="button button-secondary" href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=imitator_download_backup&backup_id=' . $backup->id . '&type=archive'), 'imitator_download_backup_action', 'imitator_nonce')); ?>">
                                    <?php esc_html_e('Download ZIP', 'imitator'); ?>
                                </a>
                            <?php else : ?>
                                <span style="color:#777;"><?php esc_html_e('Not available', 'imitator'); ?></span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <?php if (! empty($backup->database_name)) : ?>
                                <a class="button button-secondary" href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=imitator_download_backup&backup_id=' . $backup->id . '&type=database'), 'imitator_download_backup_action', 'imitator_nonce')); ?>">
                                    <?php esc_html_e('Download SQL', 'imitator'); ?>
                                </a>
                            <?php endif; ?>
                        </td>

                        <td><?php echo esc_html($backup->backup_type); ?></td>
                        <td><?php echo esc_html($backup->created_at); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="6"><?php esc_html_e('No backups found yet.', 'imitator'); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>