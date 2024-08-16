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
        $visitDate = $validable->label("Visit date")->post("visitDate")->required()->asDate()->validate();
        $reportingDate = $validable->label("Report date")->post("reportingDate")->required()->asDate()->validate();
        $visitId = $validable->label("Visit Id")->post("visitId")->required()->asString(false)->validate();
    } catch (ValidationException $exp) {
        exit($json->fail()->message($exp->getMessage())->create());
    }

    $visitId = $crypto->decrypt($visitId);
    if(!$visitId) die($json->fail()->message("Invalid request.")->create());

    $sql = "SELECT actualVisitDate FROM visits WHERE visitId=:visitId";
    $currectStatus = $db->fetchAssoc($sql, ["visitId"=>$visitId]);
    if(isset($currectStatus['actualVisitDate']))
        die($json->fail()->message("Visit date already set. Please refresh this page to see update.")->create());

    $sql = "UPDATE visits SET actualVisitDate = :actualVisitDate, reportingDate=:reportingDate WHERE  visitId=:visitId";
    $actualVisitDate = $clock->toString($visitDate, DatetimeFormat::MySqlDate());
    $reportingDate = $clock->toString($reportingDate, DatetimeFormat::MySqlDate());
    $result = $db->update($sql,["actualVisitDate"=>$actualVisitDate, "reportingDate"=>$reportingDate, "visitId"=>$visitId]);
    if($result)
        exit($json->success()->message("Saved successfully.")->create());
    else
        exit($json->fail()->message("No record updated. Try again.")->create());
?>
