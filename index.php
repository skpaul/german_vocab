<?php

    declare(strict_types=1);
    require_once("Required.php");
    require_once("vendor/autoload.php");

    #region Library instance declaration & initialization
        $logger = new Logger(ROOT_DIRECTORY);
        $crypto = new Cryptographer(SECRET_KEY);
        $clock = new Clock();
        $db = new ExPDO(DB_SERVER, DB_NAME, DB_USER, DB_PASSWORD);
        use Google\Cloud\Translate\V2\TranslateClient;
    #endregion

    $randomWord = Words::selectRandomly($db);

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
                            <div><?=$randomWord->english?></div>
                            <div><?=$randomWord->german?></div>
                            <div><?=$randomWord->pronunciation?></div>
                        </div>
                    </div>
                </div>
                <div>
                    <?php

                        $apiKey= GEMINI_API_KEY; //Generate API Key at Google AI studio and use it here.
                        /*
                            $translate = new TranslateClient(['key' => $apiKey ]);
                            // Translate text from english to german.
                            $result = $translate->translate('An_English_Word', [ 'target' => 'gr' ]);
                            echo $result['text'] . "\n";
                        */

                        //Phonetic spelling of the german word 'oder'
                        // $prompt="pronouciate ". $randomWord->german ." in german";
                        $prompt="Phonetic spelling of ". $randomWord->german ." in German";
                        
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
                        $Parsedown = new Parsedown();

                        echo $Parsedown->text($content1); 
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
            $('#marquee').mouseover(function() {
                this.setAttribute('scrollamount', 0, 0);
                $(this).trigger('stop');
            }).mouseout(function() {
                this.setAttribute('scrollamount', 5, 0);
                $(this).trigger('start');
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