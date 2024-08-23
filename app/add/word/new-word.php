<?php
    declare(strict_types=1);
    require_once("../../../Required.php");

    #region Variable declaration & initialization
        $logger = new Logger(ROOT_DIRECTORY);  //This must be in first position.
        $crypto = new Cryptographer(SECRET_KEY);
        $db = new ExPDO(DB_SERVER, DB_NAME, DB_USER, DB_PASSWORD);
        $clock = new Clock();
        $validable = new DataValidator();
        $pageTitle = "Add New Word";
    #endregion

    #region Session
        try {
            //'session' parameter must be present in query string-
            $encSessionId = $validable->title("Session parameter")->get("session")->required()->asString(false)->validate();
            $sessionId = $crypto->decrypt($encSessionId);
            if (!$sessionId) {
                unset($db);
                HttpHeader::redirect(BASE_URL . "/sorry.php?msg=Session parameter is invalid.");
            }

            //Session() constructor requires a connected Database instance.
            $session = new Session($db, SESSION_TABLE);
            $session->continue((int)$sessionId);
            $userId = $session->getData("userId");
        } catch (\SessionException $th) {
            HttpHeader::redirect(BASE_URL . "/sorry.php?msg=Invalid session.");
        } catch (ValidationException $exp) {
            HttpHeader::redirect(BASE_URL . "/sorry.php?msg=Invalid request.");
        }
    #endregion


    $articles = $db->fetchObjects("SELECT articleId AS id, articleName AS `name` FROM articles");
    $genders = $db->fetchObjects("SELECT genderId AS id, genderName AS `name` FROM genders");
    $numbers = $db->fetchObjects("SELECT numberId AS id, numberName AS `name` FROM numbers");
    $partsOfSpeech = $db->fetchObjects("SELECT partOfSpeechId AS id, partOfSpeechName AS `name` FROM parts_of_speech");
    
?>

<!DOCTYPE html>
<html>
    <head>
        <title><?= $pageTitle ?> - <?= ORGANIZATION_SHORT_NAME ?></title>
        <?php
        Required::gtag()->metaTags()->favicon()->omnicss()->griddle()->sweetModalCSS()->airDatePickerCSS();
        ?>

        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

        <!-- override select2 -->
            <style>
            .select2.select2-container{
                width:100% !important;
                padding-left:0;
                padding-right:0;
                color: black;
            }

            /* Dropdown */
            #select2-visitor-g1-results{
                color:black;
            }

            .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
                background-color: #5897fb;
                color: black;
            }
            .select2-results__option, .select2-results__option--selectable{
                color:black;
            }
        </style>


        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.15.2/css/selectize.default.min.css"
            integrity="sha512-pTaEn+6gF1IeWv3W1+7X7eM60TFu/agjgoHmYhAfLEU8Phuf6JKiiE8YmsNC0aCgQv4192s4Vai8YZ6VNM6vyQ=="
            crossorigin="anonymous"     referrerpolicy="no-referrer"         />


        <link rel="stylesheet" href="new-word.css?v=<?= time() ?>">
    </head>

    <body>
        <header class="header">
            <div class="container">
                <?php
                    echo HeaderBrand::prepare(array("baseUrl" => BASE_URL, "hambMenu" => true));
                    echo ApplicantHeaderNav::prepare(array("baseUrl" => BASE_URL));
                ?>
            </div>
        </header>

        <main class="main">
            <div class="container mv-3.0">

                <div class="ba bg-1 bc-gray-7 pa-2.0">
                    <div class="fs-130% c-gray-2 fw-700 mb-1.5"><?= $pageTitle ?></div>

                    <section class="mb-1.0">
                        <form id="add-new-word" class="form" action="new-word-process.php?session=<?= $encSessionId ?>" method="post" enctype="multipart/form-data">
                            <div class="grid">
                                <!-- german  --> 
                                <div class="field fr6-lg fr12-sm">  
                                    <label class="required">german</label>
                                    <input name="german" id="german" title="" class="validate" data-title="german" data-datatype="string" data-required="required" data-unicode="yes" data-minlen="" data-maxlen="50" type="text" >
                                </div>
                                <!-- english  --> 
                                <div class="field fr6-lg fr12-sm">  
                                    <label class="required">english</label>
                                    <input name="english" id="english" title="" class="validate" data-title="english" data-datatype="string" data-required="required" data-minlen="allow null" data-maxlen="30" type="text" >
                                </div>
                                <!-- ipa  --> 
                                <div class="field fr6-lg fr12-sm">  
                                    <label class="">ipa</label>
                                    <input name="ipa" id="ipa" title="" class="validate" data-title="ipa" data-datatype="string" data-required="optional" data-unicode="yes"  data-maxlen="50" type="text" >
                                </div>
                                <!-- phoneticSpelling  --> 
                                <div class="field fr6-lg fr12-sm">  
                                <label class="">phonetic Spelling</label>
                                <input name="phoneticSpelling" id="phoneticSpelling" title="" class="validate" data-title="phoneticSpelling" data-datatype="string" data-required="optional" data-maxlen="100" type="text" >
                                </div>
                                <!-- pronunciation  --> 
                                <div class="field fr6-lg fr12-sm">  
                                    <label class="">pronunciation</label>
                                    <input name="pronunciation" id="pronunciation" title="" class="validate" data-title="pronunciation" data-datatype="string" data-required="optional" data-maxlen="100" type="text" >
                                </div>
                                <!-- definition  --> 
                                <div class="field fr6-lg fr12-sm">  
                                    <label class="">definition</label>
                                    <input name="definition" id="definition" title="" class="validate" data-title="definition" data-datatype="string" data-required="optional" data-minlen="allow null" data-maxlen="255" type="text" >
                                </div>
                                <!-- number  --> 
                                <div class="field fr6-lg fr12-sm">  
                                    <label class="">number</label>
                                    <select name="number" id="number" class="validate" data-datatype="integer" data-required="optional">
                                        <option value=""></option>
                                        <?php
                                            foreach ($numbers as $ps) {
                                        ?>
                                            <option value="<?=$ps->id?>"><?=$ps->name?></option>
                                        <?php
                                            }
                                        ?>
                                    </select>
                                </div>
                                <!-- gender  --> 
                                <div class="field fr6-lg fr12-sm">  
                                    <label class="">gender</label>
                                    <select name="gender" id="gender" class="validate" data-datatype="integer" data-required="optional">
                                        <option value=""></option>
                                        <?php
                                            foreach ($genders as $ps) {
                                        ?>
                                            <option value="<?=$ps->id?>"><?=$ps->name?></option>
                                        <?php
                                            }
                                        ?>
                                    </select>
                                </div>
                                <!-- partsOfSpeech  --> 
                                <div class="field fr6-lg fr12-sm">  
                                    <label class="">Parts of Speech</label>
                                    <select name="partsOfSpeech" id="partsOfSpeech" class="validate" data-datatype="integer"  data-required="optional">
                                        <option value=""></option>
                                        <?php
                                            foreach ($partsOfSpeech as $ps) {
                                        ?>
                                            <option value="<?=$ps->id?>"><?=$ps->name?></option>
                                        <?php
                                            }
                                        ?>
                                    </select>
                                </div>
                                
                              
                                <!-- article  --> 
                                <div class="field fr6-lg fr12-sm">  
                                    <label class="">article</label>
                                    <select name="article" id="article" class="validate" data-datatype="integer"  data-required="optional">
                                        <option value=""></option>
                                        <?php
                                            foreach ($articles as $ps) {
                                        ?>
                                            <option value="<?=$ps->id?>"><?=$ps->name?></option>
                                        <?php
                                            }
                                        ?>
                                    </select>
                                </div>
                                
                                <!-- derivativeOf  --> 
                                <div class="field fr6-lg fr12-sm">  
                                    <label class="required">derivativeOf</label>
                                    <input name="derivativeOf" id="derivativeOf" title="" class="validate" data-title="derivativeOf" data-datatype="string" data-maxlen="50"  data-required="optional"  type="text">
                                </div>

                            </div>
                            <div class="flex ai-center">
                                <input type="hidden" name="submitted" value="1">   
                                <div class="relative">

                                <button type="submit" class="flex ai-center button bg-0 bc-0  c-primary-7" style="height: 38px; margin-top:6px; position:absolute; right:5px;">
                                    <span class="buttonText">Go</span>
                                    <span class="m-icons ml-0.2 buttonIcon">arrow_forward</span>
                                </button>
                               
                               
                                </div>
                            </div>
                        </form>
                    </section>


                    <section>

                        <style>
                            .buttonTd>span{
                                padding: 2px 7px;
                                border: 1px solid black;
                                border-radius: 4px;
                                background-color: #e1bb6a;
                                color: black;
                                font-size: 14px;
                                cursor: pointer;
                            }
                            .buttonTd>span:hover{
                                background-color: #f9b625;
                            }
                        </style>
                       
                        <style>
                            .add-schedule{
                                margin-top: 16px;
                                padding: 7px 10px;
                                border: 1px solid black;
                                border-radius: 4px;
                                background-color: #00bbff;
                                color: black;
                                font-size: 16px;
                                cursor: pointer;
                            }
                            .add-schedule:hover{
                                background-color: #51c7f1;
                            }
                        </style>
                       
                         
                    </section>
                </div>
            </div><!-- .container// -->


            <style>
                .modal{
                    margin: auto;
                    width: 300px;
                    height: 424px;
                    position: fixed;
                    top:0;
                    width: 100%;
                    height: 100%;
                    background-color: rgb(0 0 0 / 36%);
                    display: none;
                }
                .modal .box{
                    background-color: white;
                    position: relative;
                    margin: auto;
                    padding: 11px 17px;
                    width: 463px;
                    margin-top: 200px;
                    border-radius: 10px;
                }
                .modal-title{
                    color: #304767;
                    font-weight: 600;
                    border-bottom: 1px solid lightgray;
                    padding-bottom: 5px;
                }
                .modal-content{
                    min-height: 150px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }

                .modal .swiftDate:focus{
                    background-color: white;
                }

                .modal-buttons{
                    text-align: center;
                    padding-top: 13px;
                    border-top: 1px solid lightgray;
                }

                .cancelModal, .okModal{
                    cursor: pointer;
                    padding: 3px 10px 3px 10px;
                }
            </style>
            <div class="modal" id="addVisitDate">
                <div class="box">
                    <div class="modal-title">Title</div>
                    <div class="modal-content mt-1.5">
                        <form id="frmVisitDate" action="update-visit-date.php?session=<?=$encSessionId?>" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="visitId" value="">
                            <input type="hidden" name="trId" value="">
                            <div class="ta-center">
                                <input type="text" name="visitDate" class="swiftDate validate" title="Visit date" data-required="required" data-datatype="date" style="width: 277px; background-color:white; color:black;" placeholder="visit date (dd-mm-yyyy)">
                                <input type="text" name="reportingDate" class="swiftDate validate" data-title="Reporting date" data-required="required" data-datatype="date" style="width: 277px; background-color:white; color:black;" placeholder="reporting date (dd-mm-yyyy)">
                            </div>
                            <div class="modal-buttons mt-1.8">
                                <button type="button" class="cancelModal" >Cancel</button>
                                <button type="submit" class="okModal" id="submitVisitDate" >Ok</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal" id="addMonthYear">
                <div class="box">
                    <div class="modal-title mb-1.6">Add New Schedule</div>
                    <div class="modal-content">
                        <div>
                            <style>

                                select, select:focus{
                                    background-color: white;
                                    color:black;
                                }
                            </style>
                            <form id="create-schedule" action="create-schedule.php?session=<?=$encSessionId?>" method="post">
                               
                                <input type="text" class="swiftDate validate br-6 mb-0.6" data-datatype="date" name="dateOnSchedule" id="dateOnSchedule" placeholder="Visit date (optional), dd-mm-yyyy"  style="background-color: unset; color:black;">
                                
                                <select name="month" class="mb-0.4 br-6 validate" data-datatype="integer" data-required="required" data-title="Month">
                                    <option value="">Select month</option>
                                  
                                </select>

                                 <select name="year" class="mb-0.6 br-6 validate" data-datatype="integer" data-maxlen="4" data-required="required" data-title="Year" >
                                    <option value="">Select year</option>
                                
                                    <?php
                                        $currentYear = date('Y');
                                        for ($year=$currentYear + 1; $year > $currentYear-10; $year--) { 
                                    ?>
                                        <option value="<?=$year?>"><?=$year?></option>
                                    <?php
                                        }
                                    ?>
                                </select>
                                <style>
                                    .selectize-input, .selectize-input.focus {
                                        box-shadow: none;
                                        border: 1px solid;
                                        border-radius: 6px;
                                    }
                                </style>
                                <select name="visitor[]" multiple="multiple" class="validate" data-required="required" data-title="Visitor/s">
                                    <option value="">Select visitor</option>
                                   
                                </select>
                                <br><br>

                                <div class="modal-buttons">
                                    <button type="button" class="cancelModal">Cancel</button>
                                    <button class="okModal" type="submit" id="submitMonthYear">Ok</button>
                                </div>
                            </form>
                        </div>
                    </div> 
                   
                    
                </div>
            </div>

        </main>
        <footer class="footer">
            <div class="container">
                <div class="divider-h bg-gray-6 mb-1.0"></div>
                <?= Footer::prepare() ?>
            </div>
        </footer>


        <script>
            var baseUrl = '<?= BASE_URL; ?>';
            var encSessionId = '<?= $encSessionId ?>';
        </script>
        <?php
        Required::jquery()->hamburgerMenu()->sweetModalJS()->airDatePickerJS()->moment()->formstar()->swiftNumericInput();
        ?>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script
            src="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.15.2/js/selectize.min.js"
            integrity="sha512-IOebNkvA/HZjMM7MxL0NYeLYEalloZ8ckak+NDtOViP7oiYzG5vn6WVXyrJDiJPhl4yRdmNAG49iuLmhkUdVsQ=="
            crossorigin="anonymous"
            referrerpolicy="no-referrer"
            ></script>

        <script src="new-word.js?v=<?= time() ?>"></script>
    </body>
</html>