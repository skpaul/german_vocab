<?php
    declare(strict_types=1);
    require_once("../../Required.php");

    #region Variable declaration & initialization
        $logger = new Logger(ROOT_DIRECTORY);  //This must be in first position.
        $crypto = new Cryptographer(SECRET_KEY);
        $db = new ExPDO(DB_SERVER, DB_NAME, DB_USER, DB_PASSWORD);
        $clock = new Clock();
        $pageTitle = "Institute Search";
        $baseUrl = BASE_URL;
    #endregion

    try {
        $session = new Session($db, SESSION_TABLE);
        $session->validate("$baseUrl/sorry.php?msg=Invalid session", $crypto);
        $encSessionId = trim($_GET["session"]);
        $userId = $session->getData("userId");
    } catch (\SessionException $th) {
        HttpHeader::redirect("$baseUrl/sorry.php?msg=Invalid session.");
    } catch (\Exception $exp) {
        HttpHeader::redirect("$baseUrl/sorry.php?msg=Something is wrong. Please try again");
    }

    $sql = "SELECT count(*) as visitQuantity FROM `visits` GROUP BY eiin ORDER BY visitQuantity DESC LIMIT 1";
    $maxVisits = $db->fetchObject($sql);

    $months = $db->fetchObjects("SELECT monthId, monthName FROM months");
    $visitors = $db->fetchObjects("SELECT visitorId, visitorName FROM `visitors` ORDER BY visitorName");
    $distrits = $db->fetchObjects("SELECT DISTINCT i.district FROM institutes as i INNER JOIN user_district AS ud ON i.district = ud.district WHERE ud.userId = $userId ORDER BY i.district ASC")
?>

<!DOCTYPE html>
<html>
    <head>
        <title><?= $pageTitle ?> - <?= ORGANIZATION_SHORT_NAME ?></title>
        <?php Required::gtag()->metaTags()->favicon()->omnicss()->griddle()->sweetModalCSS()->airDatePickerCSS(); ?>

        <link href="https://unpkg.com/tabulator-tables@5.5.2/dist/css/tabulator.min.css" rel="stylesheet">
        <link href="https://code.jquery.com/ui/1.13.1/themes/smoothness/jquery-ui.css" rel="stylesheet">
        
        <!-- //Override tabulator default css -->
        <style>
            .tabulator{
                border: 1px solid #304A6F; /*gray-7*/
            }
            .tabulator-tableholder{
                background-color: rgba(27,42,62,1) !important;
            }
            .tabulator-table{
                background-color: rgba(27,42,62,1) !important;
                color:#a3b9d8 !important;
            }

            .tabulator-header, .tabulator-col{
                background-color: #162233 !important;
                color:#a3b9d8 !important;
            }

            .tabulator-col-title{
                font-size: 12px;
                font-weight: 600;
            }

            .tabulator-row{
                background-color: rgba(27,42,62,1) !important;
            }
            .tabulator-row-even{
                background-color: #21324b !important;
            }

            .tabulator-row .tabulator-cell {
                border-right: 1px solid #304A6F; /*gray-7*/
            }

            .tabulator-cell {
                font-size: 13px;
                text-transform: capitalize;
            }

            .tabulator-menu-item{
                background-color: #162233;
                color:#03A9F4;
                padding:8px 10px !important;
            }

            .tabulator-menu-item:not(:last-child){
                border-bottom: 1px solid #aaa;
            }

            .tabulator-menu .tabulator-menu-item:not(.tabulator-menu-item-disabled):hover {
                cursor: pointer;
                background: #21324b;
            }
        </style> <!-- Override tabulator default css// -->

        <!-- Override auto-complete widget -->
        <style>
            .ui-widget-content {
                border: 1px solid #aaaaaa;
                background: #1b2a3e;
                color: #9fb9d8;
            }

            .ui-widget-content:hover {
                /* background-color: red !important; */
            }
            .ui-menu-item{
                font-size: 13px;
            }
        </style>
        <!-- Override auto-complete widget -->

        <!-- Override OmniCSS -->
        <style>
            input[type=text], input[type=password], select, .textbox, textarea, .radio-group label, .checkbox-group label {
                font-size: 0.7rem;
                letter-spacing: normal;
            }
            input[type=text], input[type=password], select, .textbox {
                height: 29px !important;
            }
        </style>

        <style>
            .special-grid .field{
                margin-bottom: unset;
            }
            .auto-complete-loader{
                position: absolute;
                right: 5px;
                top: 9px;
            }

            input[type='radio'] {
                height: 20px;
                width: 20px;
                vertical-align: middle;
            }
        </style>
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
                <div class="ba bc bg-1">
                    <div class="container-1100 mv-1.5">
                        <h2 class="mt-1.0 mb-1.5 fw-700 c-gray-2"><?=$pageTitle?></h2>

                        <div class="ba br-7 bc-light-3 bg-3 flex ph-0.9 pv-0.5 w-max">
                            <label class="pointer"><input type="radio" name="searchType" value="instituteSearch" checked="checked"><span class="ml-0.3">Institute Search</span></label>
                            <label class="pointer ml-1.0"><input type="radio" name="searchType" value="inspectionSearch"><span class="ml-0.3">Inspection Search</span></label>
                        </div>

                        <div class="ba bc-light-3 pa-0.5 mt-1.0 bg-3">
                            <div class="grid fr4 visitCount">
                                <div class="field fr1">
                                    <select name="visitCount" id="visitCount" title="visit count" placeholder="Visit count">
                                        <option value="">Visit Count</option>
                                        <?php
                                            for ($i=0; $i <= $maxVisits->visitQuantity ; $i++) { 
                                        ?>
                                            <option value="<?=$i?>"><?=$i?></option>
                                        <?php
                                            }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="grid fr6 visitDetails hidden">
                                <div class="field fr1">
                                    <select name="reportStatus" id="reportStatus" title="Report Status" placeholder="Report Status">
                                        <option value="">Report Status</option>
                                        <option value="pending">Pending</option>
                                        <option value="not-pending">Not Pending</option>
                                    </select>
                                </div>

                                <div class="field fr1">
                                    <select name="visitorId" id="visitorId" title="Visitor" placeholder="Visitor">
                                        <option value="">Visitor</option>
                                        <?php
                                            foreach ($visitors as $visitor) {
                                        ?>
                                        <option value="<?=$visitor->visitorId?>"><?=$visitor->visitorName?></option>
                                        <?php
                                            }
                                        ?>
                                    </select>
                                </div>
                                <div class="field fr1">
                                    <select name="tentativeMonth" id="tentativeMonth" title="Schedule Month" placeholder="Schedule Month">
                                        <option value="">Schedule Month</option>
                                        <?php
                                            foreach ($months as $month) {
                                        ?>
                                        <option value="<?=$month->monthId?>"><?=$month->monthName?></option>
                                        <?php
                                            }
                                        ?>
                                    </select>
                                </div>

                                <div class="field fr1">
                                    <input type="text" id="tentativeYear" class="swiftYear" placeholder="Schedule Year" title="Schedule Year">
                                </div>
                                <div class="field fr1">
                                    <input type="text" id="actualVisitDateFrom" class="swiftDate" placeholder="Visit Date From (dd-mm-yyyy)" title="Visit Date From">
                                </div>
                                <div class="field fr1">
                                    <input type="text" id="actualVisitDateTo" class="swiftDate" placeholder="Visit Date To (dd-mm-yyyy)" title="Visit Date From">
                                </div>
                            </div>
                            <div class="line bg-light-3 mb-0.4"></div>

                            <div class="grid fr6 special-grid rowgap-0.1">
                                <div class="field fr1 relative">
                                    <span class="auto-complete-loader hidden m-icons">autorenew</span>
                                    <input type="text" id="district" data-column="<?=$crypto->encrypt("district")?>" value="" placeholder="District" title="District">
                                </div>
                                <div class="field fr1 relative">
                                    <span class="auto-complete-loader hidden m-icons">autorenew</span>
                                    <input type="text" id="thanaName" data-column="<?=$crypto->encrypt("thanaName")?>" placeholder="Thana" title="Thana">
                                </div>
                                <div class="field fr4 relative">
                                    <span class="auto-complete-loader hidden m-icons">autorenew</span>
                                    <input type="text" id="instName" class="autoComplete" data-column="<?=$crypto->encrypt("instName")?>" placeholder="Inst Name" title="Inst Name (english)">
                                </div>
                                <div class="field fr4 relative">
                                    <span class="auto-complete-loader hidden m-icons">autorenew</span>
                                    <input type="text" id="instNameBangla" class="autoComplete" data-column="<?=$crypto->encrypt("instNameBangla")?>" placeholder="Inst Name (bangla)" title="Inst Name (bangla)">
                                </div>

                                <div class="field fr1 relative">
                                    <span class="auto-complete-loader hidden m-icons">autorenew</span>
                                    <input type="text" id="level" class="autoComplete" data-column="<?=$crypto->encrypt("level")?>" placeholder="Level" title="Level">
                                </div>
                                <div class="field fr1 relative">
                                    <span class="auto-complete-loader hidden m-icons">autorenew</span>
                                    <input type="text" id="type" class="autoComplete" data-column="<?=$crypto->encrypt("type")?>" placeholder="Type" title="Type">
                                </div>
                                <div class="field fr1 relative">
                                    <span class="auto-complete-loader hidden m-icons">autorenew</span>
                                    <input type="text" id="management" class="autoComplete" data-column="<?=$crypto->encrypt("management")?>" placeholder="Management" title="Management">
                                </div>
                                <div class="field fr1 relative">
                                    <span class="auto-complete-loader hidden m-icons">autorenew</span>
                                    <input type="text" id="mpo" class="autoComplete" data-column="<?=$crypto->encrypt("mpo")?>" placeholder="MPO" title="MPO">
                                </div>
                                <div class="field fr1 relative">
                                    <span class="auto-complete-loader hidden m-icons">autorenew</span>
                                    <input type="text" id="forWhom" class="autoComplete" data-column="<?=$crypto->encrypt("forWhom")?>" placeholder="For whom" title="For whom">
                                </div>
                                <div class="field fr1 relative">
                                    <span class="auto-complete-loader hidden m-icons">autorenew</span>
                                    <input type="text" id="area" class="autoComplete" data-column="<?=$crypto->encrypt("area")?>" placeholder="Area" title="Area">
                                </div>
                                <div class="field fr1 relative">
                                    <span class="auto-complete-loader hidden m-icons">autorenew</span>
                                    <input type="text" id="geography" class="autoComplete" data-column="<?=$crypto->encrypt("geography")?>" placeholder="Geography" title="Geography">
                                </div>
                                <div class="field fr1 relative">
                                    <span class="auto-complete-loader hidden m-icons">autorenew</span>
                                    <input type="text" id="nothiNumber" class="autoComplete" data-column="<?=$crypto->encrypt("nothiNumber")?>" placeholder="Nothi No." title="Nothi No.">
                                </div>
                            </div>

                            <div class="line bg-light-3 mt-0.5"></div>

                            <div class="mt-0.8">
                                <button id="search" class="button green-rest bc-0 pv-0.4 ph-0.5 flex form-submit-button" style="color:white;" type="submit">Search <span class="m-icons login ml-0.7">search</span></button>
                            </div>
                        </div>
                       
                        <div class="round bg-light ba bc-gray-7 mt-2.0">
                            <div id="dataTable"></div>
                        </div>
                        <div class="mt-0.5">
                            <button id="download" class="button green-rest bc-0 pa-0.5 flex form-submit-button" style="color:white;" type="submit">Download <span class="m-icons btnIcon ml-0.7">download</span></button>
                        </div>
                    </div>
                </div>
            </div><!-- .container -->
        </main>
        <footer class="footer mt-3.0">
            <div class="container">
                <div class="line bg-gray-8"></div>
                <?= Footer::prepare() ?>
            </div>
        </footer>
        <script>
            var baseUrl = '<?= BASE_URL ?>';
            var encSessionId = '<?=$encSessionId ?>';
            var districts = [];
        </script>
        <?php
            $arrDist = [];
            foreach ($distrits as $district) {
        ?>
            <script >
            districts.push('<?=$district->district?>');
            </script>
        <?php
            }
        ?>
        <?php
            Required::jquery()->hamburgerMenu()->sweetModalJS()->moment()->swiftSubmit()->swiftNumericInput()->airDatePickerJS();
        ?>
        <script type="text/javascript" src="https://unpkg.com/tabulator-tables@5.5.2/dist/js/tabulator.min.js"></script>

        <script src="https://code.jquery.com/ui/1.13.1/jquery-ui.min.js"></script>

        <!-- Site Script  -->

        <script>
            var rowContextMenuItems = [];
            //Add common left menu items for all users - 
            let commonMenu = {
                                label:"Details",
                                action:function(e, row){
                                    let eiin = row.getData().eiin;
                                    window.open(baseUrl +  "/app/visits/visit.php?session=" + encSessionId + "&eiin=" + eiin, '_blank');
                                }
                             };
            rowContextMenuItems.push(commonMenu);  //<---- common menus for all users
        </script>
        <script src="list.js"></script>
    </body>
</html>