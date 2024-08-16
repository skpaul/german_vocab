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

    $data = [];
    try {
        $eiin = $validable->label("EIIN")->post("eiin")->required()->asString(false)->validate();
        $dateOnSchedule = $validable->label("Visit date")->post("dateOnSchedule")->optional()->asDate()->default(null)->validate();
        $data["tentativeMonth"] = $validable->label("Month")->post("month")->required()->asInteger(false)->maxVal(12)->validate();
        $data["tentativeYear"] = $validable->label("Year")->post("year")->required()->asInteger(false)->maxLen(4)->validate();
        $visitors = $validable->label("Visitor")->post("visitor")->required()->asArray()->validate();
    } catch (ValidationException $exp) {
        exit($json->fail()->message($exp->getMessage())->create());
    }

    $eiin = $crypto->decrypt($eiin);
    if(!$eiin) exit($json->fail()->message('Invalid EIIN.')->create());
    $data["eiin"] = $eiin;

    if(isset($dateOnSchedule) && !empty($dateOnSchedule)){
        $dateOnSchedule = $clock->toString($dateOnSchedule, DatetimeFormat::MySqlDate());
        $data["actualVisitDate"] = $dateOnSchedule;
    }
    else{
        $data["actualVisitDate"] = null;
    }


    $institute = Institute::find((int)$eiin, $db,"nothiNumber");
    if(!isset($institute->nothiNumber) || empty($institute->nothiNumber)){
        exit($json->fail()->message('Nothi No. not found. Please update.')->create());
    }
    else{
        $data["nothiNumber"] = $institute->nothiNumber;
    }


    foreach ($visitors as $visitor) {
        $visitorId = $crypto->decrypt($visitor);
        if(!$visitorId) die($json->fail()->message('Invalid visitor.')->create());
        $visitorIds[] = (int)$visitorId;
    }
    
    $insertSql = $db->prepareInsertSql($data,"visits");
    $visitId = $db->insert($insertSql, $data);

    // $sql = "INSERT INTO visits(eiin, actualVisitDate, tentativeMonth, tentativeYear, nothiNumber) VALUES(:eiin, :tentativeMonth, :tentativeYear, '$institute->nothiNumber')";
    // $visitId = $db->insert($sql,["eiin"=>$eiin, "tentativeMonth"=>$month, "tentativeYear"=>$year]);
    
    if($visitId){
        foreach ($visitorIds as $visitorId) {
            $sql = "INSERT INTO visit_visitor(visitId, visitorId) VALUES($visitId, $visitorId)";
            $db->insert($sql);
        }
        $sql = "SELECT COUNT(visitId) AS qty FROM visits WHERE eiin = :eiin";
        $visitCounts = $db->fetchObject($sql, ["eiin"=>$eiin]);
        $ordinal = Ordinal::getOrdinal($visitCounts->qty);

        $visitId = $crypto->encrypt((string)$visitId);
        exit($json->success()->message("Saved successfully.")->ordinal($ordinal)->visitId($visitId)->create());
    }
    else
        exit($json->fail()->message("Failed to save. Try again.")->create());
?>
