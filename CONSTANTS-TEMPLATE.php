<?php
    //Must add SLASH(/) after this constant i.e.  require_once(ROOT_DIRECTORY . '/db_connect.php');
    defined("ROOT_DIRECTORY")
    or define("ROOT_DIRECTORY", realpath(dirname(__FILE__)));
    //$example = ROOT_DIRECTORY . "/applicant_photo/" . "$gender" . "/" . $post_code . "/" . $userid. ".jpg";

    //Must add SLASH(/) after this constant i.e. require_once(LIBRARY_PATH .'/form.php');
    //defined("LIBRARY_PATH") or define("LIBRARY_PATH", realpath(dirname(__FILE__) . '/library'));

    //Application settings
    defined("BASE_URL") or define("BASE_URL", "http://localhost/german_vocs"); 
    defined("ORGANIZATION_SHORT_NAME") or define("ORGANIZATION_SHORT_NAME", "GermanVocs");
    defined("ORGANIZATION_FULL_NAME") or define("ORGANIZATION_FULL_NAME", "German Vocabulries");
    defined("GENERIC_APPLICATION_NAME") or define("GENERIC_APPLICATION_NAME", "GermanVocs");
    defined("ENVIRONMENT") or define("ENVIRONMENT", "DEVELOPMENT"); //DEVELOPMENT  //PRODUCTION

    //Database and tables
    defined("DB_SERVER") or define("DB_SERVER", "localhost");
    defined("DB_USER") or define("DB_USER", "root");
    defined("DB_PASSWORD") or define("DB_PASSWORD", "");
    defined("DB_NAME") or define("DB_NAME", "german_vocs");
    defined("SESSION_TABLE") or define("SESSION_TABLE", "sessions"); 
    
    //Application secrets
    defined("SECRET_KEY") or define("SECRET_KEY", "Kua2E9MQ9p"); //SECRET_KEY is required for Cryptographer.php TODO: Must change the secret key before live

    // cd .. then 
    // cd var/www/html/xdev/demo/bjsc-departmental
?>
