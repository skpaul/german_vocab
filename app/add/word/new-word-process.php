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
        $data["english"] = $validable->label("English")->post("english")->required()->asString(true)->maxLen(30)->validate();
        $data["german"] = $validable->label("German")->post("german")->required()->asString(true)->maxLen(50)->validate();
        $data["ipa"] = $validable->label("Ipa")->post("ipa")->asString(true)->maxLen(50)->default(NULL)->validate();
        $data["phoneticSpelling"] = $validable->label("PhoneticSpelling")->post("phoneticSpelling")->asString(true)->maxLen(100)->validate();
        $data["pronunciation"] = $validable->label("Pronunciation")->post("pronunciation")->asString(true)->maxLen(100)->default(NULL)->validate();
        $data["definition"] = $validable->label("Definition")->post("definition")->asString(true)->maxLen(255)->default(NULL)->validate();
        $data["number"] = $validable->label("Number")->post("number")->asInteger(true)->maxLen(11)->default(0)->validate();
        $data["gender"] = $validable->label("Gender")->post("gender")->asInteger(true)->maxLen(11)->default(0)->validate();
        $data["partsOfSpeech"] = $validable->label("PartsOfSpeech")->post("partsOfSpeech")->asInteger(true)->maxLen(11)->default(0)->validate();
        $data["article"] = $validable->label("Article")->post("article")->asString(true)->maxLen(5)->default(NULL)->validate();
        $data["derivativeOf"] = $validable->label("DerivativeOf")->post("derivativeOf")->asString(true)->maxLen(50)->default(NULL)->validate();
    } catch (ValidationException $exp) {
        exit($json->fail()->message($exp->getMessage())->create());
    }

    $sql = "SELECT id FROM words WHERE english=:english AND german=:german";
    $isExist = $db->fetchAssoc($sql, ["english"=>$data["english"], "german"=>$data["german"] ]);
    
    if($isExist)
        die($json->fail()->message("This word already exists.")->create());

    $sql = $db->prepareInsertSql($data, "words");
    $result = $db->insert($sql, $data);
    if($result)
        exit($json->success()->message("Saved successfully.")->create());
    else
        exit($json->fail()->message("No record updated. Try again.")->create());
?>
