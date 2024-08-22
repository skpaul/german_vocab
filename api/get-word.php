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

    
    $languageColumn = $_GET["lang"]; //german/english
    $scope = $_GET["scope"]; //find/random
    $feature = $_GET["feature"]; //basic, maximum
    // $source = $_GET["source"]; //words, histories, bookmars
        
    if($feature == "basic"){
        $columns = "id, german, english";
        $from = "words";
    }

    if($feature == "maximum"){
        $columns = "id, english, german, ipa, phoneticSpelling, pronunciation, `definition`, genderName, numberName, partOfSpeechName, articleName, createdOn, updatedOn";
        $from = "words INNER JOIN genders ON words.genderId = genders.genderId INNER JOIN numbers ON words.numberId = numbers.numberId INNER JOIN parts_of_speech ON words.partOfSpeechId = parts_of_speech.partOfSpeechId
        INNER JOIN articles ON words.articleId = articles.articleId";
    }

    $parameters = array() ;
    if($scope == "find"){
        if(isset($_GET["id"]) && !empty($_GET["id"])){
            $id = $_GET["id"]; //wordId to search
            $parameters["id"] = $id;
            $sql = "SELECT $columns FROM $from WHERE id = :id";
        }
        else{
            $term = $_GET["term"]; //word to search
            $parameters["term"] = $term;
            $sql = "SELECT $columns FROM $from WHERE $languageColumn = :term ORDER BY RAND() LIMIT 1";
        }
    }

    if($scope == "random"){
        $sql = "SELECT $columns FROM $from ORDER BY RAND() LIMIT 1";
    }

    $result = $db->fetchAssoc($sql, $parameters);
    exit($json->success(true)->data($result)->create());
    
?>
