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

    function createVoice(string $germanWord, string $voicePatch){
        //de-DE_BirgitV3Voice,de-DE_DieterV3Voice,de-DE_ErikaV3Voice, en-US_MichaelV3Voice
        $ibmKey = IBM_API_KEY;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_URL,"https://api.us-east.text-to-speech.watson.cloud.ibm.com/instances/947980ec-a4fc-447b-a6cb-257d51b3c3ad/v1/synthesize?voice=de-DE_DieterV3Voice");
        curl_setopt($ch, CURLOPT_USERPWD, "apikey:$ibmKey");
        // curl_setopt($ch, CURLOPT_POSTFIELDS,'{"text":"'. $germanWord .'"}'); //This statement is also fine.
        curl_setopt($ch, CURLOPT_POSTFIELDS,'{"text":"<p><s><prosody rate=\'-50%\'>'. $germanWord .'</prosody></s></p>"}');

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLINFO_HEADER_OUT, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER,
            array(
                "Content-Type: application/json",
                "Accept: audio/mp3"
            )
        );
        $response = curl_exec($ch);
        curl_close($ch);

        $voiceDir = ROOT_DIRECTORY . "/voices";
        if (!file_exists($voiceDir)) mkdir($voiceDir, 0777, true);
       
        // $savefile = fopen("voices/". strtolower($germanWord). ".mp3", 'w');
        $savefile = fopen($voicePatch, 'w');
        fwrite($savefile, $response);
        fclose($savefile);
    }

    $voiceDir = ROOT_DIRECTORY . "/voices";
    if (!file_exists($voiceDir)) mkdir($voiceDir, 0777, true);
    
    $word = $_GET["word"]; //word to search

    $voicePatch = "$voiceDir/".strtolower($word) . ".mp3";  //physical path (i.e. D:/Xampp/....)
    if (!file_exists($voicePatch)){
        createVoice($word, $voicePatch);
    }
    $audioUrl = BASE_URL . "/voices/" . strtolower($word) . ".mp3";  //relative path (http://localhost/site-name/...)
    
    exit($json->success(true)->data($audioUrl)->create());
    
?>
