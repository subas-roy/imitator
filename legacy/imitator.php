<?php
/* ======================================================
  Plugin Name: Imitator
  Plugin URI: https://github.com/subas-roy/imitator/
  Description: Backup a copy of your wp-content files and database.
  Version: 1.0.0
  Author: Subas Roy
  Author URI: https://github.com/subas-roy/
  Text Domain: imitator
  License: GPLv2 or later

  ==================================================== */
defined('ABSPATH') || exit;


// admin menu
function imitator_admin_menu() {
  add_menu_page( 'Imitator', 'Imitator', 'manage_options', 'imitator', 'imitator_admin_page', 'dashicons-media-archive' );
}
add_action('admin_menu', 'imitator_admin_menu');


// admin page
function imitator_admin_page() { ?>
  <div class="wrap">
    <h3>Imitator Settings</h3>

    <form action="<?php echo site_url();?>/wp-content/plugins/imitator/imitate.php" method="POST">

      <button class="button" type="submit" id="imitate" name="imitate" style="margin-bottom:5px;">Get backup</button>

    </form>
  
    <table class="widefat">
      
      <thead>
        <tr>
          <th>Created</th>
          <th>Size</th>
          <th>Name</th>
          <th>Package</th>
        </tr>
      </thead>
      <tbody>
        <?php
          //Insert data
          global $wpdb;
          $table_name = $wpdb->prefix . "imitator_packages";
          $results = $wpdb->get_results( "SELECT * FROM $table_name"); // Query to fetch data from database table and storing in $results
          if(!empty($results)) { 
            // print_r($results);
            foreach($results as $row) {
            ?>
            <tr>
              <td><?php echo $row->date; ?></td>
              <td>
                <?php
                  $surl = site_url();
                  $name = $row->name;
                  $url = $surl.'/wp-content/plugins/'.$name;

                  $ch = curl_init($url);

                  curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                  curl_setopt($ch, CURLOPT_HEADER, TRUE);
                  curl_setopt($ch, CURLOPT_NOBODY, TRUE);

                  $data = curl_exec($ch);
                  $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

                  curl_close($ch);
                  echo round($size / 1024 / 1024, 1) . 'MB';                 
                ?>
              </td>
              <td><?php echo $row->name; ?></td>
              <td><a href="<?php echo site_url();?>/wp-content/plugins/imitator/download.php?f=<?php echo $row->name; ?>"><button class="button"><i class="bi bi-file-earmark-zip"></i> Archive</button></a>
              <a href="<?php echo site_url();?>/wp-content/plugins/imitator/database-backup.php?d=<?php echo $row->dbname; ?>"><button class="button"><i class="bi bi-hdd-stack"></i> SQL</button></a>
              <a href="<?php echo site_url();?>/wp-content/plugins/imitator/delete.php?del=<?php echo $row->id; ?>"><button class="button">Delete</button></a>
              </td>
            </tr>
            <?php
            } 
          } ?>
      </tbody>
    </table>

  </div>

<?php };


// Datbase table creation start ========================================
// Act on plugin activation
register_activation_hook( __FILE__, "activate_myplugin" );

// Act on plugin de-activation
register_deactivation_hook( __FILE__, "deactivate_myplugin" );

// Activate Plugin
function activate_myplugin() {

	// Execute tasks on Plugin activation

	// Insert DB Tables
	init_db_myplugin();
}

// De-activate Plugin
function deactivate_myplugin() {

	// Execute tasks on Plugin de-activation
}

// Initialize DB Tables
function init_db_myplugin() {

    // WP Globals
    global $table_prefix, $wpdb;

    // Customer Table
    $imitatorTable = $table_prefix . 'imitator_packages';

    // Create Customer Table if not exist
    if( $wpdb->get_var( "show tables like '$imitatorTable'" ) != $imitatorTable ) {

        // Query - Create Table
        $sql = "CREATE TABLE `$imitatorTable` (";
        $sql .= " `id` int(11) NOT NULL auto_increment, ";
        $sql .= " `name` varchar(500) NOT NULL, ";
        $sql .= " `dbname` varchar(255) NOT NULL, ";
        $sql .= " `date` varchar(500) NOT NULL, ";
        $sql .= " PRIMARY KEY `customer_id` (`id`) ";
        $sql .= ") AUTO_INCREMENT=1;";
        // Include Upgrade Script
        require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
    
        // Create Table
        dbDelta( $sql );
    }

}
// Table creation end ========================================================


add_action( 'admin_enqueue_scripts', 'imitator_script' );
function imitator_script() {
  wp_enqueue_style( 'imitator-bootstrap-icons', 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.3/font/bootstrap-icons.css');
}

?>