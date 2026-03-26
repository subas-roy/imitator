<?php
  declare(strict_types=1);
  require 'MySQLDump.php';
  include_once('../../../wp-load.php');
  include_once('../../../wp-config.php');

  if (isset($_POST['imitate'])) {

    class FlxZipArchive extends ZipArchive 
    {
     public function addDir($location, $name) 
     {
           $this->addEmptyDir($name);
           $this->addDirDo($location, $name);
     } 
     private function addDirDo($location, $name) 
     {
        $name .= '/';
        $location .= '/';
        $dir = opendir ($location);
        while ($file = readdir($dir))
        {
            if ($file == '.' || $file == '..') continue;
            $do = (filetype( $location . $file) == 'dir') ? 'addDir' : 'addFile';
            $this->$do($location . $file, $name . $file);
        }
     } 
    }
    ?>
    
    <?php
    $the_folder = '../../../wp-content';
    $rand = rand(100,999);
    $curDate = date("Ymd");
    $zip_file_name = '../'.$curDate.$rand.'archive.zip';
    $za = new FlxZipArchive;
    $res = $za->open($zip_file_name, ZipArchive::CREATE);

    if($res === TRUE) 
    {
        $za->addDir($the_folder, basename($the_folder));
        $za->close();

        // Database backup
        set_time_limit(0);
        ignore_user_abort(true);

        $host = DB_HOST;
        $user = DB_USER;
        $pass = DB_PASSWORD;
        $dbname = DB_NAME;
        $time = -microtime(true);
        $dump = new MySQLDump(new mysqli($host, $user, $pass, $dbname));
        $dump->save('../dump ' . $curDate.$rand . '.sql.gz');
        
        $time += microtime(true);
        echo "FINISHED (in $time s)";


        //Insert data
        global $wpdb;
        $name = substr($zip_file_name,3);
        $dbname = 'dump ' . $curDate.$rand . '.sql.gz';
        $date = date("Y-m-d H:i:s");

        $tablename = $wpdb->prefix . "imitator_packages";
        $sql = $wpdb->prepare("INSERT INTO $tablename (name, date, dbname) VALUES ('$name','$date','$dbname')");
      
        $wpdb->query($sql);

        // Back to home
        $site = site_url();
        header("location: $site/wp-admin/admin.php?page=imitator");

    }
    else{
    echo 'Could not create a zip archive';
    }

  }