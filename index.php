<?php
    declare(strict_types=1);

    // header("Access-Control-Allow-Origin: *");
    // header('Access-Control-Allow-Credentials: true');
    // header('Access-Control-Max-Age: 86400');
    // header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    // header('X-Frame-Options', 'ALLOW FROM *');


    require_once("Required.php");
    require_once("vendor/autoload.php");

    #region Library instance declaration & initialization
        $logger = new Logger(ROOT_DIRECTORY);
        $crypto = new Cryptographer(SECRET_KEY);
        $clock = new Clock();
        $db = new ExPDO(DB_SERVER, DB_NAME, DB_USER, DB_PASSWORD);
        $validable = new DataValidator();
        use Google\Cloud\Translate\V2\TranslateClient;
        use Orhanerday\OpenAi\OpenAi;
        $apiKey= GEMINI_API_KEY; //Generate API Key at Google AI studio and use it here.
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
            } catch (\SessionException $th) {
                HttpHeader::redirect(BASE_URL . "/sorry.php?msg=Invalid session.");
            } catch (ValidationException $exp) {
                HttpHeader::redirect(BASE_URL . "/sorry.php?msg=Invalid request.");
            }
        }
    #endregion

    if(isset($_GET["word"]) && !empty($_GET["word"])){
        $wordDetails = Words::findGerman(trim($_GET["word"]), $db);
    }
    else{
        $wordDetails = Words::selectRandomly($db);
    }
    
    if($isLoggedin){
        if($wordDetails){
            Histories::set($userId, $wordDetails->id, $db);
        }
    }

    if($wordDetails){
        //
        $examples = Examples::get(trim($wordDetails->german), $db);
    }
    else {
        $examples = Examples::get(trim($_GET["word"]), $db);
    }

    $parsedown = new Parsedown();
    $prompt = "";
?>

<!DOCTYPE html>
<html>

    <head>
        <title>Home || <?= GENERIC_APPLICATION_NAME ?> - <?= ORGANIZATION_FULL_NAME ?></title>
        <?php
            Required::gtag()->html5shiv()->metaTags()->favicon()->sweetModalCSS()->omnicss();
        ?>

    </head>

    <body>
        <header class="header">
            <div class="container">
                <!-- <div class="content"> -->
                <?php
                echo HeaderBrand::prepare(array("baseUrl" => BASE_URL, "hambMenu" => true));
                echo ApplicantHeaderNav::prepare(array("baseUrl" => BASE_URL));
                ?>
                <!-- </div> -->
            </div>
        </header>
        <main class="main">
            <div class="container mv-3.0">
                    <div class="ba bc bg-1">
                        <div class="container-700 mv-1.5">
                            <div class="round bg-2 ba bc pv-2.0 ph-1.5">
                                <div class="fs-150%"><?=$wordDetails->german?></div>
                                <div>
                                    <?php
                                        if(!isset($wordDetails->ipa) || empty($wordDetails->ipa)){
                                            $prompt = "IPA";
                                        }
                                        else{
                                    ?>
                                        <?=$wordDetails->ipa?>
                                    <?php
                                        }
                                    ?>
                                </div>
                                <div>
                                    <?php
                                        if(!isset($wordDetails->phoneticSpelling) || empty($wordDetails->phoneticSpelling)){
                                            if(empty($prompt)){
                                                $prompt = "phonetic spelling";
                                            }
                                            else{
                                                $prompt = "$prompt AND phonetic spelling";
                                            }
                                        }
                                        else{
                                    ?>
                                        <?=$wordDetails->phoneticSpelling?>
                                        <a href="https://www.howtopronounce.com/german/<?=$wordDetails->german?>" target="_blank">Listen</a>
                                    <?php
                                        }
                                    ?>
                                </div>
                                <div><?=$wordDetails->english?></div>
                                <div>Definition <?=$wordDetails->definition?></div>
                                <div>
                                    <span title="Parts of Speech"><?=$wordDetails->partsOfSpeech?></span>,
                                    <span title="Number"><?=$wordDetails->number?></span>,
                                    <span title="Gender"><?=$wordDetails->gender?></span>
                                </div>
                                <div>Pronunciation <?=$wordDetails->pronunciation?></div>
                            </div>

                            <a href="<?=BASE_URL?>/?session=<?=$encSessionId?>">Next</a>
                        </div>
                    </div>
                    <ul style="list-style: disc; margin-left: 19px;">
                        <?php
                            foreach ($examples as $example) {
                                $arrWords = explode(" ", trim($example->german));
                               
                        ?>
                            <li>
                                <?php
                                    foreach ($arrWords as $word) {
                                        if($isLoggedin) $queryString = "session=" . $encSessionId . "&word=" . $word;
                                        else $queryString = "word=" . $word;
                                        
                                ?>
                                    <a href="<?=BASE_URL?>/index.php?<?=$queryString?>"><?=$word?></a> 
                                <?php
                                    }
                                ?>
                                 - 
                                <?=$example->english?>
                            </li>
                        <?php
                            }
                        ?>
                    </ul>
                    <div>
                        <?php
                           

                            

                            if(!empty($prompt)){
                                $prompt .= "  of the word '". $wordDetails->german ."' in German";

                                //Phonetic spelling of the german word 'oder'
                                // $prompt="pronouciate ". $wordDetails->german ." in german";
                                $prompt="IPA and phonetic spelling of the word '". $wordDetails->german ."' in German";
                                
                                $json_data = '{
                                
                                    "contents": [{
                                
                                        "parts":[{
                                
                                        "text":"'.$prompt.'"}]}]}';
                                
                                $url="https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key=$apiKey";
                                
                                $response=run_curl($url,$json_data);
                                $response = json_decode($response, false);
                                // return $this->candidates[0]->content->parts;
                                $content = $response->candidates[0]->content->parts[0]->text;
                                // var_dump($content);
                                $content1 =  str_replace("\n", "<br>", $content);
                                // $content1 =  str_replace("* ", "-", $content1);
                            
                                echo $parsedown->text($content1); 
                            }                        
                        ?>

                        <?php
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
                            
                            $voicePatch = "$voiceDir/".strtolower($wordDetails->german) . ".mp3";  //physical path (i.e. D:/Xampp/....)
                            if (!file_exists($voicePatch)){
                                createVoice($wordDetails->german, $voicePatch);
                            }
                            $audioUrl = BASE_URL . "/voices/" . strtolower($wordDetails->german) . ".mp3";  //relative path (http://localhost/site-name/...)
                        ?>
                        <button type="button" class="play-button">Play
                            <img src="pronounce.png" alt="" srcset="">
                            <img src="play-animation.gif" alt="" srcset="">

                        </button>
                        <audio controls id="myAudio" style="display: none;">
                            <source src="<?=$audioUrl?>" type="audio/mpeg">
                        </audio>
                        
                    </div>
            </div><!-- .container -->
        </main>
        <footer>
            <div class="container">
                <div class="divider-h bg-gray-8"></div>
                <?php
                echo Footer::prepare(array());
                ?>
            </div>
        </footer>

        <?php
        Required::jquery()->hamburgerMenu();
        ?>
        <script>
            function aud_play_pause(elementId) {
                var myAudio = document.getElementById(elementId);
                if (myAudio.paused) {
                    myAudio.play();
                } else {
                    myAudio.pause();
                }
            }

            var base_url = '<?php echo BASE_URL; ?>';
            $(function() {
                var playing = false;
                $('.play-button').click(function(){
                    aud_play_pause("myAudio");
                });

                function fades($div, cb) {
                    $div.fadeIn(300, function() {
                        myTimeout = setTimeout(function() {
                            $div.fadeOut(400, function() {
                                var $next = $div.next();
                                if ($next.length > 0) {
                                    fades($next, cb);
                                } else {
                                    // The last element has faded away, call the callback
                                    cb();
                                }
                            }); //fadeout ends


                        }, 2000); //setTimfeout ends
                    });
                }





                function startFading($firstDiv) {
                    fades($firstDiv, function() {
                        startFading($firstDiv);
                    });
                }

                startFading($(".a:first-child"));

            }) //document.ready ends.
        </script>

    </body>

</html>