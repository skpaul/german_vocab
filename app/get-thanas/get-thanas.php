<?php
    declare(strict_types=1);

    #region Import libraries
        require_once("../../Required.php");
    #endregion

 #region Variable declaration & initialization
     $logger = new Logger(ROOT_DIRECTORY);  //This must be in first position.
     $crypto = new Cryptographer(SECRET_KEY);
     $db = new Database(DB_SERVER, DB_NAME, DB_USER, DB_PASSWORD);
     $validable = new DataValidator();
     $json = new JSON();

     $db->connect();
 #endregion

 #region Session check and validation
    try {
        //'session' parameter must be present in query string-
        $encSessionId = $validable->title("Session parameter")->get("session")->required()->asString(false)->validate();
        $sessionId = $crypto->decrypt($encSessionId);
        if (!$sessionId) {
            die($json->fail()->message("Invalid session. Please login again.")->create());
        }
        //Session() constructor requires a connected Database instance.
        $session = new Session($db->getPDO(), SESSION_TABLE);
        $session->continue((int)$sessionId);
    } catch (\SessionException $th) {
        die($json->fail()->message("Invalid session. Please login again.")->create());
    } catch(ValidationException $exp){
        die($json->fail()->message("Invalid request. Please try again.")->create());
    }
 #endregion

 try {
    $distCode = $validable->title("District Name")->post("distCode")->required()->asString(false)->validate();
    $distCode = $crypto->decrypt($distCode);
    $thanas = Thana::list((int)$distCode, $db);
    $encList = [];
    foreach ($thanas as $thana) {
        // $thana->code = $crypto->encrypt( (string)$thana->code);
        $encList[] = array("code"=>$crypto->encrypt( (string)$thana->code), "name"=>$thana->name);
    }
    $db->close(); unset($db);
    exit($json->success()->data($encList)->create()); //No need to use "$.parseJSON(response)" in JQuery. Response is, by default, a JavaScript object.

} catch(ValidationException $exp){
    die($json->fail()->message("Invalid request. Please try again.")->create());
}
  
?>