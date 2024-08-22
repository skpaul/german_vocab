<?php
    declare(strict_types=1);

    require_once("Required.php");
    require_once("vendor/autoload.php");

    #region Library instance declaration & initialization
        $logger = new Logger(ROOT_DIRECTORY);
        $crypto = new Cryptographer(SECRET_KEY);
        $clock = new Clock();
        $db = new ExPDO(DB_SERVER, DB_NAME, DB_USER, DB_PASSWORD);
        $validable = new DataValidator();
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
            } catch (\SessionException $th) {
                HttpHeader::redirect(BASE_URL . "/sorry.php?msg=Invalid session.");
            } catch (ValidationException $exp) {
                HttpHeader::redirect(BASE_URL . "/sorry.php?msg=Invalid request.");
            }
        }
    #endregion

    if(isset($_GET["word"]) && !empty($_GET["word"])){
        $wordDetails = Words::findGerman(trim($_GET["word"]), $db);
    }
    else{
        $wordDetails = Words::selectRandomly($db);
    }
    
    if($isLoggedin){
        if($wordDetails){
            Histories::set($userId, $wordDetails->id, $db);
        }
    }

    if($wordDetails){
        //
        $examples = Examples::get(trim($wordDetails->german), $db);
    }
    else {
        $examples = Examples::get(trim($_GET["word"]), $db);
    }


?>
