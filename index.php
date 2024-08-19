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
        Histories::set($userId, $wordDetails->id, $db);
    }
    $examples = Examples::get(trim($wordDetails->german), $db);

    $parsedown = new Parsedown();
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
                            <div><?=$wordDetails->german?></div>
                            <div><?=$wordDetails->english?></div>
                            <div>Definition <?=$wordDetails->definition?></div>
                            <div>Gender- <?=$wordDetails->gender?></div>
                            <div>Number- <?=$wordDetails->number?></div>
                            <div>Parts of Speech <?=$wordDetails->partsOfSpeech?></div>
                            <div>Pronunciation <?=$wordDetails->pronunciation?></div>
                            
                        </div>

                        <a href="<?=BASE_URL?>/?session=<?=$encSessionId?>">Next</a>
                    </div>
                </div>

                <?php
                    foreach ($examples as $example) {
                        echo $example->german . ' - ' . $example->english;
                    }
                ?>
                
                <div>
                    <?php
                        /*
                            $translate = new TranslateClient(['key' => $apiKey ]);
                            // Translate text from english to german.
                            $result = $translate->translate('An_English_Word', [ 'target' => 'gr' ]);
                            echo $result['text'] . "\n";
                        */

                        
                        $prompt = "";
                        if(!isset($wordDetails->ipa) || empty($wordDetails->ipa)){
                            $prompt = "IPA";
                        }
                        else{
                            echo 'IPA : ' .  $wordDetails->ipa; 
                        }

                        if(!isset($wordDetails->phoneticSpelling) || empty($wordDetails->phoneticSpelling)){
                            if(empty($prompt)){
                                $prompt = "phonetic spelling";
                            }
                            else{
                                $prompt = "$prompt AND phonetic spelling";
                            }
                        }
                        else{
                            echo '<br>phonetic Spelling : ' .  $wordDetails->phoneticSpelling; 
                            echo '<a href="https://www.howtopronounce.com/german/' .$wordDetails->german . '" target="_blank">Listen</a>';
                        }

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
        var base_url = '<?php echo BASE_URL; ?>';
        $(function() {
            // $.get('https://www.howtopronounce.com/german/ich', function(html) {
            //     let kk = $(html).find("#pronouncedContents").html();
            //     alert(kk);
            // });


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