<?php
    declare(strict_types=1);

    require_once("../Required.php");

    #region Library instance declaration & initialization
        $logger = new Logger(ROOT_DIRECTORY);
        $crypto = new Cryptographer(SECRET_KEY);
        $clock = new Clock();
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
            } catch (\SessionException $th) {
                die($json->fail()->message('Invalid session.')->create());
            } catch (ValidationException $exp) {
                die($json->fail()->message('Invalid request.')->create());
            }
        }
    #endregion


    if($isLoggedin){
        if(isset($_POST['wordId']) || !empty($_POST['wordId'])){
            $wordId = trim($_POST['wordId']);
            try {
                Histories::set($userId, (int)$wordId, $db);
                exit($json->success()->create());
            } catch (\Throwable $th) {
                //throw $th;
            }
        }
    }

?>
