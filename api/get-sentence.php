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
        if(isset($_GET["lastId"]) && !empty($_GET["lastId"])){
            $lastId = $_GET["lastId"];
            $parameters["lastId"] = $lastId;
            
            $clause = " WHERE contexts.contextId = :contextId AND id>:lastId ORDER BY id LIMIT 1";
        }
        else{
            $clause = " WHERE contexts.contextId = :contextId ORDER BY id LIMIT 1";
        }
        $parameters["contextId"] = $contextId;
    }
    else{
        $clause = " ORDER BY RAND() LIMIT 1";
    }
    
    $sql = "SELECT id, english, german, contextName FROM examples INNER JOIN contexts ON examples.contextId = contexts.contextId $clause";
    $result = $db->fetchAssoc($sql, $parameters);
    //if there are no more questions, start from the beginning using the same context-
    if(!$result){
        $clause = " WHERE contexts.contextId = :contextId ORDER BY id LIMIT 1";
        $sql = "SELECT id, english, german, contextName FROM examples INNER JOIN contexts ON examples.contextId = contexts.contextId $clause";
        $result = $db->fetchAssoc($sql,["contextId"=>$contextId]);
    }
    
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
