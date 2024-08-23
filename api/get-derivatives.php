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

    
    // $languageColumn = $_GET["lang"]; //german/english       
    // $term = $_GET["term"]; //word to search
    $id = $_GET["id"]; //word to search
    $parameters["id"] = $id;
    $sql = "SELECT derivativeOf FROM `words` WHERE id = :id";
    $baseWord = $db->fetchObject($sql, $parameters);
    if(isset($baseWord->derivativeOf) && !empty($baseWord->derivativeOf)){
        $sql = "SELECT german FROM `words` WHERE id <> :id AND derivativeOf = :derivativeOf";
        $parameters["derivativeOf"] = $baseWord->derivativeOf;
        $derivatives = $db->fetchObjects($sql, $parameters);
        exit($json->success(true)->data($derivatives)->create());
    }
    else{
        exit($json->success(true)->data(false)->create());
    }
    
    
?>
