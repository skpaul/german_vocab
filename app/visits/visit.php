<?php
    declare(strict_types=1);
    require_once("../../Required.php");

    #region Variable declaration & initialization
        $logger = new Logger(ROOT_DIRECTORY);  //This must be in first position.
        $crypto = new Cryptographer(SECRET_KEY);
        $db = new ExPDO(DB_SERVER, DB_NAME, DB_USER, DB_PASSWORD);
        $clock = new Clock();
        $validable = new DataValidator();
        $pageTitle = "Institute Details";
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

    $eiin = null;
    $visits = [];
    if(isset($_POST["submitted"])){
        try {
            $eiin = $validable->label("EIIN")->post("eiin")->required()->asInteger(false)->exactLen(6)->validate();
        } catch (ValidationException $exp) {
            die($exp->getMessage());
        }
    }
    else{
        if(isset($_GET["eiin"]) && !empty($_GET["eiin"])){
            $eiin = trim($_GET["eiin"]);
            // $eiin = $crypto->decrypt($eiin);
            // if(!$eiin){
            //     die("Invalid request.");
            // }
        }
    }
    if(isset($eiin)){
        $searchedForEiin = true;
        $institute = Institute::find((int)$eiin, $db);
        if($institute){
            $visits = Visit::search(["eiin"=>$eiin], $db);
        }
    }
    else{
        $searchedForEiin = false;
    }

    $months = $db->fetchObjects("SELECT monthId, monthName FROM months");
    $visitors = $db->fetchObjects("SELECT visitorId, visitorName FROM `visitors` WHERE isActive = 1 ORDER BY visitorName");
?>

<!DOCTYPE html>
<html>
    <head>
        <title><?= $pageTitle ?> - <?= ORGANIZATION_SHORT_NAME ?></title>
        <?php
        Required::gtag()->metaTags()->favicon()->omnicss()->griddle()->sweetModalCSS()->airDatePickerCSS();
        ?>
        <style>
            table.visits{
               
                border-collapse: collapse;
            }

            table.visits tr>td{
                padding: 10px 56px 10px 13px;
            }
            table.visits tr:nth-child(2n)>td{
                background-color: #1c2c43;
            }

        </style>

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


        <link rel="stylesheet" href="visit.css?v=<?= time() ?>">
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
                        <form id="eiinSearch" class="form" action="visit.php?session=<?= $encSessionId ?>" method="post" enctype="multipart/form-data">
                            <div class="flex ai-center">
                                <input type="hidden" name="submitted" value="1">   
                                <div class="relative">

                                <button type="submit" class="flex ai-center button bg-0 bc-0  c-primary-7" style="height: 38px; margin-top:6px; position:absolute; right:5px;">
                                    <!-- <span class="buttonText">Go</span> -->
                                    <span class="m-icons ml-0.2 buttonIcon">arrow_forward</span>
                                </button>
                                <input name="eiin" class="validate number" style="width:9rem;" data-title="EIIN" data-datatype="integer" data-required="required" data-maxlen="6" maxlength="6" type="text" value="<?=$eiin?>" placeholder="EIIN">
                               
                                </div>
                            </div>
                        </form>
                    </section>


                    <section>
                        <?php
                            if($searchedForEiin){
                                if($institute){
                        ?>
                                    <div class="grid fr4-lg fr1-sm mb-2.0">
                                        <div class="fr3">
                                            <div class="fw-600 mb-0.3 fs-150%-lg">
                                                <?=$institute->instName?>
                                            </div>
                                            <div class="bn">
                                                <?=$institute->instNameBangla?>
                                            </div>
                                            <div class="fs-80% mt-1.6">
                                                <?=$institute->address?>, <?=$institute->post?>, <?=$institute->thanaName?>, <?=$institute->district?>, <?=$institute->mobile?>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="ba br-6 bg-dark-3 bc-green-3 ph-2.5 pv-0.5 w-max center">
                                                <?php
                                                    if(isset($institute->nothiNumber) && !empty($institute->nothiNumber)){
                                                ?>
                                                    <span class="m-icons c-green-8" style="font-size: 56px; line-height: 0.8;">topic</span>
                                                    <div class="fw-800 fs-2.0"><?=$institute->nothiNumber?></div>
                                                 <?php
                                                    }
                                                 ?>
                                            </div>
                                        </div>
                                        
                                    </div>
                                     <?php
                                        switch (strtoupper(trim($institute->mpo))) {
                                            case 'YES':
                                                $mpo = "MPO";
                                                break;
                                            case 'NO':
                                                $mpo = "Non-MPO";
                                                break;
                                            default:
                                                $mpo = "N/A";
                                                break;
                                        }
                                    ?>

                                    <!-- other info -->
                                    <div>
                                            <span title="Level" class="bg-dark-1 ba bc-light-3 fs80% pv02 ph04 br3"><?= trim($institute->level)?></span> 
                                            <span title="Inst type" class="bg-dark-1 ba bc-light-3 fs80% pv02 ph04 br3 capitalize"><?= strtolower($institute->type)?></span>
                                            <span title="Inst type" class="bg-dark-1 ba bc-light-3 fs80% pv02 ph04 br3 capitalize"><?= strtolower($institute->management)?></span>
                                            <span title="MPO/Non-MPO" class="bg-dark-1 ba bc-light-3 fs80% pv02 ph04 br3 capitalize"><?=strtolower($mpo)?></span> 
                                            <span title="For whom" class="bg-dark-1 ba bc-light-3 fs80% pv02 ph04 br3 capitalize"><?= strtolower($institute->forWhom)?></span>
                                            <span title="Area" class="bg-dark-1 ba bc-light-3 fs80% pv02 ph04 br3 capitalize"><?= strtolower($institute->area)?></span>
                                            <span title="Geograpy" class="bg-dark-1 ba bc-light-3 fs80% pv02 ph04 br3 capitalize"><?=trim(strtolower($institute->geography))?></span>
                                    </div>
                                    <?php
                                        if(!isset($institute->nothiNumber) || empty($institute->nothiNumber)){
                                            switch (trim($institute->type)) {
                                                case 'SCHOOL':
                                                    $nothiPrefix = "S";
                                                    break;
                                                case 'MADRASHA':
                                                    $nothiPrefix = "M";
                                                    break;
                                                case 'SCHOOL & COLLEGE':
                                                    $nothiPrefix = "C";
                                                    break;
                                                case 'TECHNICAL INSTITUTION':
                                                    $nothiPrefix = "T";
                                                    break;
                                                default:
                                                    $nothiPrefix = "Undefined";
                                                    break;
                                            }
                                    ?>
                                        <style>
                                            .nothi-notice{
                                                border: 1px solid red;
                                                padding:20px;
                                            }
                                            .nothi-prefix{
                                                position: absolute;left: 10px;top: 12px;
                                            }
                                            .nothi-prefix::after{
                                                content: "-";
                                            }
                                            input[name="nothiNo"]{
                                                padding-left: 35px; width:auto; height: 32px !important;
                                            }

                                            button.update-nothi{
                                                margin-top: 4px;
                                                padding: 7px 10px;
                                                border: 1px solid black;
                                                border-radius: 4px;
                                                background-color: #00bbff;
                                                color: black;
                                                font-size: 16px;
                                                cursor: pointer;
                                            }
                                            button.update-nothi:hover{
                                                background-color: #51c7f1;
                                            }
                                        </style>
                                        <div class="nothi-notice bc-red-3 mb-2.0 mt-2.0">
                                            <form id="updateNothi" action="<?=BASE_URL?>/app/institutes/update-nothi-number.php?session=<?=$encSessionId?>" method="post">
                                            <div class="flex ai-center">
                                                    <span style="margin-right: 20px;">Please update Nothi No.</span>
                                                    <div class="relative mr-0.5">
                                                        <span class="nothi-prefix"><?=$nothiPrefix?></span>
                                                        <input type="text" name="nothiNo" placeholder="enter nothi no."> 
                                                        <input type="hidden" name="nothiEiin" value="<?=$crypto->encrypt((string)$eiin)?>"> 
                                                    </div>
                                                    <button type="submit" class="update-nothi">Update</button>
                                                </div>
                                            </form>
                                        </div>
                            <?php
                                }
                            ?>
                        <?php
                                }  //<---- if($institute)
                                else{
                                    echo "Institute not found.";
                                } 
                            }
                        ?>
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
                        <div id="tableContainer" class="mt-2.0">
                            <?php
                                if(isset($institute) && count($visits)>0){
                                    echo '<table class="visits ba bc-gray-8 fs-80%"><tbody>';
                                    $counter = count($visits);
                                    foreach($visits as $visit){
                                        echo '<tr id="'.$counter.'">';
                                        $visitId = $crypto->encrypt((string)$visit->visitId);
                                        $visitNo = Ordinal::getOrdinal($counter) . " Visit";

                                        if(isset($visit->actualVisitDate)){
                                            $visitSchedule = $clock->toString($visit->actualVisitDate, DatetimeFormat::BdDate());
                                            $button = "";
                                        }
                                        else{
                                                $visitSchedule = substr($visit->monthName, 0, 3)  . ", " . $visit->tentativeYear;
                                                $button = '<span class="addDate" data-visitId='. $visitId . '>Add Date</span>';
                                        }

                                        if(isset($visit->reportingDate))  $reportingDate = $clock->toString($visit->reportingDate, DatetimeFormat::BdDate());
                                        else $reportingDate = "";

                                        $counter--;
                            ?>
                                        <td class="visitNo"><?=$visitNo?></td><td><?=$visit->visitorName?></td><td class="visitSchedule"><?=$visitSchedule?></td><td class="reportingDate"><?=$reportingDate?></td><td class="buttonTd"><?=$button?></td>
                                    </tr>
                            <?php
                                    } //<-- foreach
                                    echo '</tbody></table>';
                                }
                            ?>
                        </div>
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
                        <?php
                            if(isset($institute) && $institute){
                        ?>
                            <button type="button" class="add-schedule">Add Schedule</button>
                        <?php
                         }
                        ?>
                         
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
                                <?php
                                    if(isset($institute)){
                                ?>
                                    <input type="hidden" name="eiin" value="<?=$crypto->encrypt((string)$eiin)?>">
                                <?php
                                    }
                                ?>
                                <input type="text" class="swiftDate validate br-6 mb-0.6" data-datatype="date" name="dateOnSchedule" id="dateOnSchedule" placeholder="Visit date (optional), dd-mm-yyyy"  style="background-color: unset; color:black;">
                                
                                <select name="month" class="mb-0.4 br-6 validate" data-datatype="integer" data-required="required" data-title="Month">
                                    <option value="">Select month</option>
                                    <?php
                                        foreach ($months as $month) {
                                    ?>
                                        <option value="<?=$month->monthId?>"><?=$month->monthName?></option>
                                    <?php
                                        }
                                    ?>
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
                                    <?php
                                        foreach ($visitors as $visitor) {
                                            $visitorId = $crypto->encrypt((string)$visitor->visitorId);
                                    ?>
                                                <option value="<?=$visitorId?>"><?=$visitor->visitorName?></option>
                                    <?php
                                        }
                                    ?>
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

        <script src="visit.js?v=<?= time() ?>"></script>
    </body>
</html>