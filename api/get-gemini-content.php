<?php
    declare(strict_types=1);
    require_once("../Required.php");
    require_once("../vendor/autoload.php");

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

    function run_curl($url, $json_data) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    $word = $_GET["term"];

    $prompt="Meaning, definition, article, number, gender,  part of speech, IPA, phonetic spelling, german spelling and usage examples of the german word $word";
    
    $json_data = '{
                    "contents": [{
                                    "parts":[{
    
                                                "text":"'.$prompt.'"
                                            }]
                                }]
                }';
    
    $url="https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key=" . GEMINI_API_KEY;
    
    $response=run_curl($url,$json_data);
    $response = json_decode($response, false);
    // return $this->candidates[0]->content->parts;
    $content = $response->candidates[0]->content->parts[0]->text;
    // $parsedown = new Parsedown();
    // $content =  $parsedown->text($content); 
    exit($json->success(true)->data($content)->create());
    
?>
