<?php
    declare(strict_types=1);
    require_once("../../../Required.php");

    #region Variable declaration & initialization
        $logger = new Logger(ROOT_DIRECTORY);  //This must be in first position.
        $crypto = new Cryptographer(SECRET_KEY);
        $db = new ExPDO(DB_SERVER, DB_NAME, DB_USER, DB_PASSWORD);
        $clock = new Clock();
        $validable = new DataValidator();
        $pageTitle = "Add New Example";
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


    $articles = $db->fetchObjects("SELECT id, `name` FROM articles");
    $genders = $db->fetchObjects("SELECT id, `name` FROM genders");
    $numbers = $db->fetchObjects("SELECT id, `name` FROM numbers");
    $partsOfSpeech = $db->fetchObjects("SELECT id, `name` FROM parts_of_speech");
    
?>

<!DOCTYPE html>
<html>
    <head>
        <title><?= $pageTitle ?> - <?= ORGANIZATION_SHORT_NAME ?></title>
        <?php
            Required::gtag()->metaTags()->favicon()->omnicss()->griddle()->sweetModalCSS();
        ?>
        <link rel="stylesheet" href="new-example.css?v=<?= time() ?>">
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
                        <form id="add-new-word" class="form" action="new-example-process.php?session=<?= $encSessionId ?>" method="post" enctype="multipart/form-data">
                            <div class="grid fr1">
                                <!-- german  --> 
                                <div class="field">  
                                    <label class="required">german</label>
                                    <input name="german" id="german" title="" class="validate" data-title="german" data-datatype="string" data-required="required" data-maxlen="255" type="text" >
                                </div>
                                <!-- english  --> 
                                <div class="field">  
                                    <label class="required">english</label>
                                    <input name="english" id="english" title="" class="validate" data-title="english" data-datatype="string" data-required="required" data-maxlen="255" type="text" >
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
                    
                </div>
            </div><!-- .container// -->
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

        <script src="new-example.js?v=<?= time() ?>"></script>
    </body>
</html>