<?php
    declare(strict_types=1);
    require_once("../../Required.php");
 
    #region Variable declaration & initialization
        $logger = new Logger(ROOT_DIRECTORY);  //This must be in first position.
        $crypto = new Cryptographer(SECRET_KEY);
        $db = new ExPDO(DB_SERVER, DB_NAME, DB_USER, DB_PASSWORD);
        $clock = new Clock();
        $adminBaseUrl = BASE_URL . "/app/admins";
    #endregion

    try {
        $session = new Session($db, SESSION_TABLE);
        $session->validate("$adminBaseUrl/sorry.php?msg=Invalid session", $crypto);
        $encSessionId = trim($_GET["session"]);
        $userId = $session->getData("userId");
    } catch (\SessionException $th) {
        HttpHeader::redirect("$adminBaseUrl/sorry.php?msg=Invalid session.");
    } catch (\Exception $exp) {
        HttpHeader::redirect("$adminBaseUrl/sorry.php?msg=Something is wrong. Please try again");
    }


    $queryString = $_SERVER['QUERY_STRING'];

    $term = $_GET["term"]; //comes from autocomplete plugin
    $searchParameters = ["term"=>$term];
    $columnName = $_GET["column"];
    $columnName = $crypto->decrypt($columnName);
    if(!$columnName) die("Invalid column");

    if($columnName == "thanaName"){
        $district = $_GET["district"];
        if(isset($district) && !empty($district)){
            $sql = "SELECT DISTINCT  `$columnName` FROM institutes WHERE `$columnName` LIKE CONCAT('%', :term, '%') AND district=:district ORDER BY `$columnName`";
            $searchParameters["district"] = $district;
        }
        else{
            $sql = "SELECT DISTINCT `$columnName` FROM institutes as i INNER JOIN user_district AS ud ON i.district = ud.district WHERE ud.userId = $userId AND `$columnName` LIKE CONCAT('%', :term, '%') ORDER BY `$columnName` ASC LIMIT 10";
        }
    }
    else{
        $sql = "SELECT DISTINCT `$columnName` FROM institutes as i INNER JOIN user_district AS ud ON i.district = ud.district WHERE ud.userId = $userId AND `$columnName` LIKE CONCAT('%', :term, '%') ORDER BY `$columnName` ASC LIMIT 10";
    }

    

    try {
        // $lists = $db->fetchAssocs($sql, array("term"=>$term));
        $lists = $db->fetchAssocs($sql, $searchParameters);
    } catch (\PDOException $exp) {
        $logger->createLog($exp->getMessage());
        die("Invalid column");
    }

    $data = [];
    foreach ($lists as $item) 
    $data[] = $item[$columnName];

    // $json = json_encode($data);
    // exit($json);

    exit(json_encode($data));

?>



