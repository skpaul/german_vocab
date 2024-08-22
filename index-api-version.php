<?php
    declare(strict_types=1);
    require_once("Required.php");

    #region Library instance declaration & initialization
        $logger = new Logger(ROOT_DIRECTORY);
        $crypto = new Cryptographer(SECRET_KEY);
        $clock = new Clock();
        $db = new ExPDO(DB_SERVER, DB_NAME, DB_USER, DB_PASSWORD);
        $validable = new DataValidator();
        $isLoggedin = false;
        $encSessionId = "";
        $queryString = "";
    #endregion

    #region Session
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
                $queryString = "session=$encSessionId&";
            } catch (\SessionException $th) {
                HttpHeader::redirect(BASE_URL . "/sorry.php?msg=Invalid session.");
            } catch (ValidationException $exp) {
                HttpHeader::redirect(BASE_URL . "/sorry.php?msg=Invalid request.");
            }
        }
    #endregion

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
                                <div class="fs-150%">
                                    <form id="frm-word" action="<?=BASE_URL?>/api/get-rand-word.php?<?=$queryString?>" method="get">
                                        <input type="text" name="current-word" id="current-word">
                                        <button class="btn" type="submit">Search</button>
                                    </form>
                                    <button class="button" type="button" id="next-word">Next</button>
                                </div>
                                <div>
                                   IPA
                                </div>
                                <div>
                                    phonetic spelling
                                </div>
                                <div>English meaning</div>
                                <div>Definition</div>
                                <div>
                                    <span title="Parts of Speech"></span>,
                                    <span title="Number"></span>,
                                    <span title="Gender"></span>
                                </div>
                                <div>Pronunciation</div>
                            </div>

                            <a href="<?=BASE_URL?>/?session=<?=$encSessionId?>">Next</a>
                        </div>
                    </div>
                    <ul style="list-style: disc; margin-left: 19px;">
                       examples
                    </ul>
                    <div>
                                               
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
            Required::jquery()->hamburgerMenu()->moment()->sweetModalJS()->formstar();
        ?>
        <script>
            var encSessionId = '<?=$encSessionId?>';
            var baseUrl = '<?=BASE_URL?>';

            function aud_play_pause(elementId) {
                var myAudio = document.getElementById(elementId);
                if (myAudio.paused) {
                    myAudio.play();
                } else {
                    myAudio.pause();
                }
            }

           
            $(function() {
                
                var txtCurrentWord = $('#current-word');
                var currentWord = txtCurrentWord.val();
                if(currentWord.length == 0){
                   getRandomWord();
                }

                $('#next-word').click(function(e){
                    e.preventDefault();
                    getRandomWord();
                });

                
                // FormStar --->
                $('form#frm-word').formstar();

                function getRandomWord() {
                    $.get(baseUrl + '/api/get-random-word.php', {"payload":"basic"}, function(response, textStatus, jqXHR) {
                        let data = response.data;
                        txtCurrentWord.val(data.german);
                        setHistory(data.id, encSessionId);
                    });
                }

                function search(params) {
                    
                }

                function setHistory(wordId, sessionId) {
                    $.post(baseUrl + '/api/set-history.php?session=' + sessionId, {wordId:wordId}, function(response, textStatus, jqXHR) {
                        console.log(response);
                    });
                }

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