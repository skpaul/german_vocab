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
            input#german{
                background-color: transparent;
                border-color: transparent;
                font-size: 3rem;
                height: unset !important;
                line-height: 1;
                padding: 5px 10px;
                border-radius: 9px;
            }
            button#search, button#next-word, a#go-to-edit{
                border: 1px solid #7b8083;
                background-color: #1c2128;
                color: #bdd0e5;
                font-size: 14px;
                padding: 2px;
                border-radius: 5px;
                cursor: pointer;
            }

            ul#examples li{
                line-height: 1.7;
            }
            ul#examples .de{
                /* font-size: 14px; */
            }
            ul#examples a{
                border-bottom: 1px dashed #C2D0E5;
            }
            ul#examples .en{
                font-size: 90%;
            }

            #gemini-content{
                font-size: 0.8rem;
            }
            #gemini-content h2{
                display: none;
            }

            #gemini-content>p{
                margin-top: 0.5rem;
            }
            #gemini-content>p>strong{
                font-weight:500;
            }
            #gemini-content>ul{
                list-style: disc;
                margin-left: 14px;
                font-size: 0.7rem;
            }
            #gemini-content ul>li{
                margin-top: 3px;
            }
            #gemini-content ul>li>strong{
                font-weight:500;
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
                                        <div class="mb-1.2">
                                            <button type="button" id="search">Search</button>
                                            <button type="button" id="next-word">Next</button>
                                            <a id="go-to-edit" href="">Edit</a>
                                        </div>
                                </div>
                                <div>
                                   <span id="ipa" title="International Phonetic Alphabet"></span>
                                   <span id="phoneticSpelling" title="Phonetic Spelling"></span>
                                </div>

                                <div><input type="text" name="english" id="english"></div>
                                <div id="definition" title="Definition">Definition</div>
                                <div>
                                    <span id="partOfSpeech" title="Part of Speech"></span>
                                    <span id="number" title="Number"></span>
                                    <span id="gender" title="Gender"></span>
                                    <span id="article" title="Article"></span>
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
                    <div id="derivatives">
                        derivatives
                    </div>
                    <div id="ai-content">
                        AI Generated Content (not reviewed)-
                        <div id="gemini-content">
                            gemini-content
                        </div>
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
        <script src="https://cdn.jsdelivr.net/npm/showdown@2.1.0/dist/showdown.min.js"></script>

        <script>
            var encSessionId = '<?=$encSessionId?>';
            var baseUrl = '<?=BASE_URL?>';

            var converter = new showdown.Converter();
           

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

                $('button#search').click(function(e){
                    germanWord = txtGerman.val();
                    searchWord(germanWord);
                });

                $('#next-word').click(function(e){
                    e.preventDefault();
                    getRandomWord();
                });

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
                        getDerivatives(data.id);
                        getGeminiContent(data.german);
                        // getVoice(data.german);
                    });
                }

                function searchWord(germanWord) {
                    $.get(baseUrl + '/api/get-word.php?session=' + encSessionId, {lang:"german", scope:"find", feature:"basic", term:germanWord}, function(response, textStatus, jqXHR) {
                        let data = response.data;
                        if(data === false){
                            txtEnglish.val("");
                            $("#ipa").text("");
                            $("#phoneticSpelling").text("");
                            $("#definition").text("");
                            $("#pronunciation").text("");
                            $("#number").text("");
                            $("#gender").text("");
                            $("#partOfSpeech").text("");
                            $("#articleName").text("");
                            $("#go-to-edit").hide().attr('href', "");
                            $("ul#examples").empty();
                            $("div#other-meanings").empty();
                            $("div#derivatives").empty();
                            alert("Not found."); 
                            return;
                        }
                        txtEnglish.val(data.english);
                        getWordDetails(data.id);
                        getExamples(germanWord);
                        if(encSessionId.length > 0){
                            setHistory(data.id);
                        }

                        getOtherMeanings(data.german, data.id);
                        getDerivatives(data.id);
                        getGeminiContent(germanWord);
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
                        $("#number").text(data.numberName);
                        $("#gender").text(data.genderName);
                        $("#partOfSpeech").text(data.partOfSpeechName);
                        $("#article").text(data.articleName);
                        $editUrl = baseUrl + '/app/edit/word/edit-word.php?session=' + encSessionId + '&id=' + id;
                        $("#go-to-edit").show().attr('href', $editUrl);

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
                                strGermanWords += ' <a href="'+ url + germanWord + '">'+ germanWord +'</a>';
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

                function getDerivatives(id) {
                    $.get(baseUrl + '/api/get-derivatives.php?session=' + encSessionId, {id:id}, function(response, textStatus, jqXHR) {
                        let content = "";  
                        let url = baseUrl + '/index-api-version.php?session=' + encSessionId + '&word=';
                        $.each(response.data, function(index, derivative){
                            content  += ' <a href="'+ url + derivative.german + '">'+ derivative.german +'</a>';
                        });
                        $("div#derivatives").empty().append(content);
                    });
                }

                function getGeminiContent(germanWord) {
                    $.get(baseUrl + '/api/get-gemini-content.php?session=' + encSessionId, {term:germanWord}, function(response, textStatus, jqXHR) {
                        let content =  converter.makeHtml(response.data);  //response.data ; //
                        $("div#gemini-content").empty().append(content);
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