<?php
    declare(strict_types=1);
    require_once("../Required.php");

    #region Library instance declaration & initialization
        $logger = new Logger(ROOT_DIRECTORY);
        $crypto = new Cryptographer(SECRET_KEY);
        $db = new ExPDO(DB_SERVER, DB_NAME, DB_USER, DB_PASSWORD);
        $validable = new DataValidator();
        $json = new JSON();
    #endregion

    #region Session
        $isLoggedin = false;
        if(isset($_GET["session"]) && !empty($_GET["session"])){
            try {
                //'session' parameter must be present in query string-
                $encSessionId = $validable->title("Session parameter")->get("session")->required()->asString(false)->validate();
                $sessionId = $crypto->decrypt($encSessionId);
                // $sessionId = $encSessionId; //TODO: For temporary use. Must use encrypted id.
                if (!$sessionId) {
                    unset($db);
                    HttpHeader::redirect(BASE_URL . "/sorry.php?msg=Session parameter is invalid.");
                }

                //Session() constructor requires a connected Database instance.
                $session = new Session($db, SESSION_TABLE);
                $session->continue((int)$sessionId);
                $userId = $session->getData("userId");
                $isLoggedin = true;
                $json = new JSON();
            } catch (\SessionException $th) {
                HttpHeader::redirect(BASE_URL . "/sorry.php?msg=Invalid session.");
            } catch (ValidationException $exp) {
                HttpHeader::redirect(BASE_URL . "/sorry.php?msg=Invalid request.");
            }
        }
    #endregion

    $parameters = array();
    if(isset($_GET["contextId"]) && !empty($_GET["contextId"])){
        $contextId = $_GET["contextId"];
        $parameters["contextId"] = $contextId;
        $totalRows = $db->fetchAssoc("SELECT COUNT(id) AS quantity FROM examples WHERE contextId=:contextId", $parameters);
        if(isset($_GET["lastId"]) && !empty($_GET["lastId"])){
            $lastId = $_GET["lastId"];
            $parameters["lastId"] = $lastId;
            
            $sql = "SELECT a.id, a.german, a.english, b.nthPosition FROM examples AS a INNER JOIN (SELECT ROW_NUMBER() OVER (ORDER BY id) AS nthPosition, id FROM examples WHERE contextId = :contextId) AS b ON a.id = b.id WHERE a.id > :lastId  ORDER BY id LIMIT 1";

            $result = $db->fetchAssoc($sql, $parameters);
            //if there are no more examples, start from the beginning using the same context-
            if(!$result){
                $sql = "SELECT a.id, a.german, a.english, b.nthPosition FROM examples AS a INNER JOIN (SELECT ROW_NUMBER() OVER (ORDER BY id) AS nthPosition, id FROM examples WHERE contextId = :contextId) AS b ON a.id = b.id ORDER BY id LIMIT 1";
                $result = $db->fetchAssoc($sql,["contextId"=>$contextId]);
            }
        }
        else{
            $sql = "SELECT a.id, a.german, a.english, b.nthPosition FROM examples AS a INNER JOIN (SELECT ROW_NUMBER() OVER (ORDER BY id) AS nthPosition, id FROM examples WHERE contextId = :contextId) AS b ON a.id = b.id ORDER BY id LIMIT 1";
            $result = $db->fetchAssoc($sql,["contextId"=>$contextId]);
        }
    }
    else{
        // $sql = "SELECT id, english, german FROM examples ORDER BY RAND() LIMIT 1";
        $sql = "SELECT a.id, a.german, a.english, b.nthPosition FROM examples AS a INNER JOIN (SELECT ROW_NUMBER() OVER (ORDER BY id) AS nthPosition, id FROM examples) AS b ON a.id = b.id ORDER BY RAND() LIMIT 1";
        $result = $db->fetchAssoc($sql);
        $totalRows = $db->fetchAssoc("SELECT COUNT(id) AS quantity FROM examples");
    }

    $result["totalRows"] = $totalRows["quantity"];
    
    $currentId = $result["id"];
    if($isLoggedin && isset($contextId) && $contextId>0){
        $lastSession = $db->fetchObject("SELECT id FROM sentence_practice_session WHERE userId = $userId");
        if($lastSession){
            $db->update("UPDATE sentence_practice_session SET exampleId=$currentId, contextId=$contextId WHERE id=$lastSession->id");
        }
        else{
            $db->insert("INSERT INTO sentence_practice_session(userId, exampleId, contextId) VALUES($userId, $currentId, $contextId)");
        }
    }
    
    exit($json->success(true)->data($result)->create());
    
?>
