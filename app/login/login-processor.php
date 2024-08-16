<?php 
    declare(strict_types=1);
    require_once("../../Required.php");

    #region Variable declaration & initialization
        $logger = new Logger(ROOT_DIRECTORY);
        $crypto = new Cryptographer(SECRET_KEY);
        $db = new ExPDO(DB_SERVER, DB_NAME, DB_USER, DB_PASSWORD);
        $validator = new DataValidator();
        $clock = new Clock();
        $json = new JSON();
    #endregion

    #region Validate form 
        try {
            $loginName = $validator->label("Login Name")->post("loginName")->required()->asString(false)->validate();
            $loginPassword = $validator->label("Login Password")->post("loginPassword")->required()->asString(false)->validate();
        } catch (\ValidationException $ve) {
            die($json->fail()->message($ve->getMessage())->create());
        }
        catch (\Exception $exp) {
            $logger->createLog($exp->getMessage());
            die($json->fail()->message($exp->getMessage())->create());
        }
    #endregion

    $sql = "SELECT userId FROM users WHERE loginName = :loginName AND loginPassword=:loginPassword";
    $user = $db->fetchObject($sql, array("loginName"=> $loginName, "loginPassword"=>$loginPassword));

    if(!$user){
        unset($db);
        die($json->fail()->message("Invalid login and/or password. Try again.")->create());
    }

    //initiate session
    $session = new Session($db,SESSION_TABLE); 
    $session->startNew();
    $sessionId = $session->getSessionId();
    $encSessionId = $crypto->encrypt((string) $sessionId);

    $session->setData("userId", $user->userId);

    $redirectUrl = BASE_URL . "/app/visits/visit.php?session=" . $encSessionId;
    exit($json->success()->redirecturl($redirectUrl)->create());

?>