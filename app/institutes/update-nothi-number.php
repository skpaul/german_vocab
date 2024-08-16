<?php
    declare(strict_types=1);
    require_once("../../Required.php");

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
        $nothiNo = $validable->label("Visit Date")->post("nothiNo")->required()->asInteger(false)->maxLen(8)->validate();
        $eiin = $validable->label("EIIN")->post("nothiEiin")->required()->asString(false)->validate();
    } catch (ValidationException $exp) {
        exit($json->fail()->message($exp->getMessage())->create());
    }

    $eiin = $crypto->decrypt($eiin);
    if(!$eiin) die($json->fail()->message("Invalid request.")->create());

    $institute = Institute::find((int)$eiin, $db,"type");
    switch (trim($institute->type)){
        case 'SCHOOL':
            $nothiPrefix = "S";
            break;
        case 'MADRASHA':
            $nothiPrefix = "M";
            break;
        case 'SCHOOL & COLLEGE':
            $nothiPrefix = "C";
            break;
        case 'TECHNICAL INSTITUTION':
            $nothiPrefix = "T";
            break;
        default:
            exit($json->fail()->message("Type not defined.")->create());
            break;
    }

    $sql = "UPDATE institutes SET nothiNumber = :nothiNo WHERE  eiin=:eiin";
    $result = $db->update($sql,["nothiNo"=>$nothiPrefix."-".$nothiNo, "eiin"=>$eiin]);

    if($result){
        $url = BASE_URL . "/app/visits/visit.php?session=$encSessionId&eiin=$eiin";
        exit($json->success()->redirecturl($url)->create());
    }
    else
        exit($json->fail()->message("No record updated. Have you already updated it?")->create());
?>
