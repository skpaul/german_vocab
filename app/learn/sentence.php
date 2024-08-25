<?php
    declare(strict_types=1);
    require_once("../../Required.php");

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
    $contexts = $db->fetchObjects("SELECT * FROM contexts WHERE contextId>-1");
    $lastSession = $db->fetchObject("SELECT id, exampleId, contextId FROM sentence_practice_session WHERE userId = $userId");
?>

<!DOCTYPE html>
<html>

    <head>
        <title>Home || <?= GENERIC_APPLICATION_NAME ?> - <?= ORGANIZATION_FULL_NAME ?></title>
        <?php
            Required::gtag()->html5shiv()->metaTags()->favicon()->sweetModalCSS()->omnicss();
        ?>

        <style>
            #settings{
                display: flex;
                justify-content: space-between;
            }

            #settings select{
                height: unset !important;
                margin: unset !important;
                background-color: #1c2128;
                width: auto;
            }

            #german{
                background-color: transparent;
                border-color: transparent;
                font-size: 2rem;
                height: unset !important;
                line-height: 1;
                padding: 5px 0;
                border-radius: 9px;
                margin-top: 31px;
            }

            button#speak, button#search, button#next, a#go-to-edit{
                border: 1px solid #7b8083;
                background-color: #1c2128;
                color: #bdd0e5;
                font-size: 14px;
                padding: 2px;
                border-radius: 5px;
                cursor: pointer;
                width: 26px;
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
                                        <div id="settings">
                                            <select id="context">
                                                <?php
                                                    foreach ($contexts as $context){
                                                        $isSelected = "";
                                                        if($isLoggedin && $lastSession){
                                                            if($lastSession->contextId == $context->contextId){
                                                                $isSelected = "selected";
                                                            }
                                                        }
                                                ?>
                                                    <option value="<?=$context->contextId?>" <?=$isSelected?>><?=$context->contextName?></option>
                                                <?php
                                                    }
                                                ?>
                                            </select>
                                            <?php
                                                $lastId = "";
                                                if($isLoggedin && $lastSession){
                                                    $lastId = $lastSession->exampleId;
                                            ?>
                                                <select id="resume">
                                                    <option value="yes" selected>Continue prev session</option>
                                                    <option value="no">start from beginning</option>
                                                </select>
                                            <?php
                                                }
                                            ?>

                                            <select id="mode">
                                                <optgroup>Learn</optgroup>
                                                <option value="learn">Learn</option>
                                                <optgroup>Practice</optgroup>
                                                <option value="en-to-de">EN to DE</option>
                                                <option value="de-to-en">DE to EN</option>
                                                <option value="listen">Listen & write</option>
                                            </select>

                                        </div>
                                        <div>
                                        Ää,  Öö, Üü, ß
                                        </div>
                                        <input type="hidden" id="lastId" value="<?=$lastId?>">
                                        <div id="german"></div>
                                        <div class="mb-1.2">
                                            <button type="button" id="speak"><span class="m-icons">volume_up</span></button>
                                            <button type="button" id="search"><span class="m-icons">search</span></button>
                                            <button type="button" id="next"><span class="m-icons">arrow_forward</span></button>
                                            <a id="go-to-edit" href=""><span class="m-icons">edit</span></a>
                                        </div>
                                </div>
                                <div id="english"></div>

                                <div id="answer-container" class="hidden">
                                    <input type="text" id="answer">
                                    <button type="button" id="check">Check</button>
                                </div>
                            </div>
                        </div>
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
        
        <script src="https://code.responsivevoice.org/responsivevoice.js?key=cnXr472y"></script>

        <script>
            var encSessionId = '<?=$encSessionId?>';
            var baseUrl = '<?=BASE_URL?>';
            var prevValue = '<?=$lastSession->exampleId ?? ''?>';
          
            $(function(){      
                
                $('#mode').change(function(){
                    let selectedValue = $(this).val();
                    if(selectedValue == "learn"){
                        $('#answer-container').hide();
                        $('#german').css("visibility", "visible")
                            $('#english').css("visibility", "visible")
                    }
                    else{
                        $('#answer-container').show();
                        if(selectedValue == "en-to-de"){
                            $('#german').css("visibility", "hidden")
                            $('#english').css("visibility", "visible")
                        }
                        if(selectedValue == "de-to-en"){
                            $('#german').css("visibility", "visible")
                            $('#english').css("visibility", "hidden")
                        }
                        if(selectedValue == "listen"){
                            $('#german').css("visibility", "hidden")
                            $('#english').css("visibility", "hidden")
                        }
                    }
                });

                $('#check').click(function(){
                    let selectedValue = $('#mode').val();
                    let answer = $('#answer').val();
                    if(selectedValue == "en-to-de"){
                        if(answer == $.trim($('#german').text())){
                            alert("Fine");
                        }
                        else{
                            alert("Wrong");
                        }
                    }
                    if(selectedValue == "de-to-en"){
                        $('#german').css("visibility", "visible")
                        $('#english').css("visibility", "hidden")
                    }
                    if(selectedValue == "listen"){
                        $('#german').css("visibility", "hidden")
                        $('#english').css("visibility", "hidden")
                    }
                });

                $("select#resume").change(function(){
                    let selectedValue = $(this).val();
                    if(selectedValue == 'yes'){
                        $('input#lastId').val(prevValue);    
                    }
                    else{
                        prevValue = $('input#lastId').val();
                        $('input#lastId').val('');
                    }
                });
                $(document).on("click", "#speak", function(){
                    let text = $("#german").text();
                    responsiveVoice.speak(text, "Deutsch Female", {pitch: 1, rate: 0.9, volume: 3});
                });

                getRandomSentence();

                $('#next').click(function(e){
                    e.preventDefault();
                    $(this).attr('disabled', 'disabled').find('span').html('autorenew').addClass('spinner');
                    getRandomSentence();
                });

                function getRandomSentence() {
                    let contextId = $("#context").val();
                    let parameters = {};
                    parameters.contextId = contextId;
                    parameters.lastId = $('#lastId').val();

                    $.get(baseUrl + '/api/get-sentence.php?session=' + encSessionId, parameters, function(response, textStatus, jqXHR) {
                        let example = response.data;
                        let german = example.german;
                        let english = example.english;
                        let arrGermanWords = example.german.split(" ");
                            let strGermanWords = "";
                            let url = baseUrl + '/index-api-version.php?session=' + encSessionId + '&word=';
                            arrGermanWords.forEach(function(germanWord){
                                strGermanWords += ' <a href="'+ url + germanWord + '">'+ germanWord +'</a>';
                            });
                            $('div#german').html(strGermanWords);
                            $('div#english').html(english);
                            $('#lastId').val(example.id);
                        $('button#next').removeAttr('disabled').find('span').html('arrow_forward').removeClass('spinner');
                    });
                }
            }) //document.ready ends.
        </script>
    </body>
</html>