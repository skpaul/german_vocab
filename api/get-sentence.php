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

    
    $contextId = $_GET["contextId"]; 
   
    $parameters = array();
    if($contextId){
        $lastId = $_GET["lastId"]; 
        $clause = " WHERE contexts.contextId = :contextId AND id>:lastId ORDER BY id LIMIT 1";
        $parameters["contextId"] = $contextId;
        $parameters["lastId"] = $lastId;
    }
    else{
        $clause = " ORDER BY RAND() LIMIT 1";
    }
    $sql = "SELECT
                id, english, german, contextName FROM examples INNER JOIN contexts ON examples.contextId = contexts.contextId $clause";

    $result = $db->fetchAssoc($sql, $parameters);
    exit($json->success(true)->data($result)->create());
    
?>
