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

    $word = "";
    if(isset($_GET["word"]) && !empty($_GET["word"])){
        $word = trim($_GET["word"]);
    }

?>

<!DOCTYPE html>
<html>

    <head>
        <title>Home || <?= GENERIC_APPLICATION_NAME ?> - <?= ORGANIZATION_FULL_NAME ?></title>
        <?php
            Required::gtag()->html5shiv()->metaTags()->favicon()->sweetModalCSS()->omnicss();
        ?>

        <style>
            ul#examples .de{
                font-size: 12px;
            }
            ul#examples a{
                border-bottom: 1px dashed red;
            }
            ul#examples .en{
                font-size: 9px;
            }
        </style>
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
                                   
                                        <input type="text" name="german" id="german" value="<?=$word?>">
                                        <button class="btn" type="button" id="search">Search</button>
                                   
                                    <button class="button" type="button" id="next-word">Next</button>
                                </div>
                                <div>
                                   <span id="ipa"></span>
                                   <span id="phoneticSpelling"></span>
                                </div>

                                <div><input type="text" name="english" id="english"></div>
                                <div id="definition">Definition</div>
                                <div>
                                    <span id="partOfSpeech" title="Part of Speech"></span>,
                                    <span id="number" title="Number"></span>,
                                    <span id="gender" title="Gender"></span>
                                    <span id="article" title="Gender"></span>
                                </div>
                                <div id="pronunciation">Pronunciation</div>
                            </div>
                        </div>
                    </div>
                    <ul id="examples" style="list-style: disc; margin-left: 19px;">
                       examples
                    </ul>
                    <div id="other-meanings">
                       other meanings
                    </div>
                    <div>
                        <button type="button" class="play-button">Play
                            <img src="pronounce.png" alt="" srcset="">
                            <img src="play-animation.gif" alt="" srcset="">
                        </button>
                        <audio controls id="myAudio" style="display: none;" autoplay="">
                            <source id="voice-source" src="" type="audio/mpeg">
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
                var txtGerman = $('#german');
                var txtEnglish = $('#english');

                var germanWord = txtGerman.val();
                if(germanWord.length == 0){
                   getRandomWord();
                }
                else{
                    searchWord(germanWord);
                }

                $('#next-word').click(function(e){
                    e.preventDefault();
                    getRandomWord();
                });
                
                // FormStar --->
                $('form#frm-word').formstar();

                function getRandomWord() {
                    $.get(baseUrl + '/api/get-word.php?session=' + encSessionId, {lang:"german", scope:"random", feature:"basic"}, function(response, textStatus, jqXHR) {
                        let data = response.data;
                        txtGerman.val(data.german);
                        txtEnglish.val(data.english);
                        getWordDetails(data.id);
                        getExamples(data.german);
                        getOtherMeanings(data.german, data.id);
                        if(encSessionId.length > 0){
                            setHistory(data.id);
                        }
                        // getVoice(data.german);
                    });
                }

                $('button#search').click(function(e){
                    germanWord = txtGerman.val();
                    searchWord(germanWord);
                });

                function searchWord(germanWord) {
                    $.get(baseUrl + '/api/get-word.php?session=' + encSessionId, {lang:"german", scope:"find", feature:"basic", term:germanWord}, function(response, textStatus, jqXHR) {
                        let data = response.data;
                        txtEnglish.val(data.english);
                        getWordDetails(data.id);
                        getExamples(germanWord);
                        if(encSessionId.length > 0){
                            setHistory(data.id);
                        }

                        getOtherMeanings(data.german, data.id);
                        // getVoice(data.german);
                    });
                }

                function getWordDetails(id) {
                    $.get(baseUrl + '/api/get-word.php?session=' + encSessionId, {lang:"german", scope:"find", feature:"maximum", id:id}, function(response, textStatus, jqXHR) {
                        let data = response.data;
                        $("#ipa").text(data.ipa);
                        $("#phoneticSpelling").text(data.phoneticSpelling);
                        $("#definition").text(data.definition);
                        $("#pronunciation").text(data.pronunciation);
                        $("#numberName").text(data.numberName);
                        $("#genderName").text(data.genderName);
                        $("#partOfSpeechName").text(data.partOfSpeechName);
                        $("#articleName").text(data.articleName);
                    });
                }

                function setHistory(wordId) {
                    $.post(baseUrl + '/api/set-history.php?session=' + encSessionId, {wordId:wordId});
                }

                function getExamples(german) {
                    $.get(baseUrl + '/api/get-examples.php?session=' + encSessionId, {"german":german}, function(response, textStatus, jqXHR) {
                        let examples = response.data;
                        let ul = $("ul#examples");
                        ul.empty();
                        let url = baseUrl + '/index-api-version.php?session=' + encSessionId + '&word=';
                        $.each(examples, function(index, example){
                            let li = "<li>";
                            let arrGermanWords = example.german.split(" ");
                            let strGermanWords = "";
                            arrGermanWords.forEach(function(germanWord){
                                let a = ' <a href="'+ url + germanWord + '">'+ germanWord +'</a>';
                                strGermanWords += a;
                            });
                           
                            let fullSentence = '<span class="de">' + strGermanWords + '</span> - <span class="en"> '+ example.english +' </span>';  
                          
                            $('<li />', {html: fullSentence}).appendTo(ul);
                        });
                    });
                }

                function getOtherMeanings(baseWord, id) {
                    $.get(baseUrl + '/api/get-other-meanings.php?session=' + encSessionId, {term:baseWord, id:id, lang:"german"}, function(response, textStatus, jqXHR) {
                        let div = $("div#other-meanings");
                        div.empty();
                        div.append(response.data.otherMeanings);
                    });
                }

                function getVoice(word) {
                    $.get(baseUrl + '/api/get-voice.php?session=' + encSessionId, {word:word}, function(response, textStatus, jqXHR) {
                        //myAudio
                        var audio = $("#myAudio");
                        let source = $("#voice-source");
                        source.attr("src", response.data);

                        audio[0].pause();
                        audio[0].load();//suspends and restores all audio element

                        //audio[0].play(); changed based on Sprachprofi's comment below
                        audio[0].oncanplaythrough = audio[0].play();
                        // aud_play_pause("myAudio");
                    });
                }

                var playing = false;
                $('.play-button').click(function(){
                    getVoice(txtGerman.val());
                    // aud_play_pause("myAudio");
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