<?php
    include_once('../../../wp-load.php');
    $id = $_GET['del'];
    echo $id;
    // Select data
    global $wpdb;
    $table_name = $wpdb->prefix . "imitator_packages";
    $results = $wpdb->get_results("select * from $table_name where id='$id'");
    foreach ($results as $row) { 
        $name = $row->name; 
        $dbname = $row->dbname; 
    }
    // Delete file
    unlink("../$name");
    unlink("../$dbname");
    // Delete data
    $results = $wpdb->get_results("delete from $table_name where id='$id'");
    // back to menu
    $site = site_url();
    header("location: $site/wp-admin/admin.php?page=imitator");

?>