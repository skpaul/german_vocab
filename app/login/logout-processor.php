<?php
    require_once("../../Required.php");

    /*
        This page is used to close the session, to stop transferring query string parameter "session" and then redirect to "logout-cofirmed.php" page.
    */
    $logger = new Logger(ROOT_DIRECTORY);
    $endecryptor = new Cryptographer(SECRET_KEY);
    $db = new ExPDO(DB_SERVER, DB_NAME, DB_USER, DB_PASSWORD);

    #region check session
        if(!isset($_GET["session"]) || empty(trim($_GET["session"]))){
            HttpHeader::redirect("sorry.php?msg=Invalid session.");
        }

        $encSessionId = trim($_GET["session"]);

        try {
            $sessionId = $endecryptor->decrypt($encSessionId);
            $session = new Session($db, SESSION_TABLE);
            $session->continue($sessionId);
            $session->close();
            HttpHeader::redirect("logout-confirmed.php");

        } catch (\SessionException $th) {
            HttpHeader::redirect("sorry.php?msg=Invalid session.");
        } catch (\Exception $exp) {
            HttpHeader::redirect("sorry.php?msg=Unknown error occured. Error code- 197346.");
        }
    #endregion
?>

