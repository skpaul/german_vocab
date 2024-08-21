<?php
    declare(strict_types=1);
    require_once("../../../Required.php");

    #region Variable declaration & initialization
        $logger = new Logger(ROOT_DIRECTORY);  //This must be in first position.
        $crypto = new Cryptographer(SECRET_KEY);
        $db = new ExPDO(DB_SERVER, DB_NAME, DB_USER, DB_PASSWORD);
        $clock = new Clock();
        $validable = new DataValidator();
        $json = new JSON();
    #endregion

    #region Session
        
        try {
            //'session' parameter must be present in query string-
            $encSessionId = $validable->title("Session parameter")->get("session")->required()->asString(false)->validate();
            $sessionId = $crypto->decrypt($encSessionId);
            if (!$sessionId) exit($json->fail()->message("Invalid session. Please login again.")->create());
            $session = new Session($db, SESSION_TABLE);
            $session->continue((int)$sessionId);
            $userId = $session->getData("userId");
        } catch (\SessionException $th) {
            die($json->fail()->redirecturl(BASE_URL . "/sorry.php?msg=Invalid session.")->create());
        } catch (ValidationException $exp) {
           die($json->fail()->redirecturl(BASE_URL . "/sorry.php?msg=Invalid session request.")->create());
        }
    #endregion

    try {
        $data["english"] = $validable->label("English")->post("english")->required()->asString(true)->maxLen(255)->validate();
        $data["german"] = $validable->label("German")->post("german")->required()->asString(true)->maxLen(255)->validate();
    } catch (ValidationException $exp) {
        exit($json->fail()->message($exp->getMessage())->create());
    }

    $data["isPublished"] = 0;
    if($userId == 1){
        $data["isPublished"] = 1;
    }

    $sql = $db->prepareInsertSql($data, "examples");
    $result = $db->insert($sql, $data);
    if($result)
        exit($json->success()->message("Saved successfully.")->create());
    else
        exit($json->fail()->message("No record updated. Try again.")->create());
?>
