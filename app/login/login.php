<?php
    declare(strict_types=1);
    require_once("../../Required.php");

    #region Variable declaration & initialization
        $logger = new Logger(ROOT_DIRECTORY);  //This must be in first position.
        $crypto = new Cryptographer(SECRET_KEY);
        $db = new ExPDO(DB_SERVER, DB_NAME, DB_USER, DB_PASSWORD);
        $clock = new Clock();
        $pageTitle = "Login";
    #endregion
?>

<!DOCTYPE html>
<html>
    <head>
        <title><?= $pageTitle ?> - <?= ORGANIZATION_SHORT_NAME ?></title>
        <?php
            Required::gtag()->metaTags()->favicon()->omnicss()->griddle()->sweetModalCSS()->airDatePickerCSS();
        ?>
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
                    <div class="container-900 mv-1.5">                        
                        <h2 class="mt-1.0 mb-1.5 fw-700 c-gray-2"><?=$pageTitle?></h2>
                        <form action="login-processor.php" method="post" enctype="multipart/form-data">
                            <div class="round bg-2 pa-1.5 ba bc-gray-7">
                                <div class="mt-1.0">
                                    <div class="grid fr2-lg fr1-sm">
                                        <div class="field">
                                            <label class="required">Login Name</label>
                                            <input type="text" value="" name="loginName" class="validate" maxlength="13" data-required="required" data-maxlen="13">
                                        </div>
                                        <div class="field">
                                            <label class="required">Login Password</label>
                                            <input type="password" value="" name="loginPassword" class="validate" maxlength="13" data-required="required" data-maxlen="13">
                                        </div>

                                        <div class="mt-0.5">
                                            <button class="button green-rest bc-0 pa-0.5 flex form-submit-button" style="color:white;" type="submit">Submit <span class="m-icons login ml-0.7">login</span></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
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
            var baseUrl = '<?php echo BASE_URL; ?>';
        </script>
        <?php
            Required::jquery()->hamburgerMenu()->sweetModalJS()->formstar();
        ?>
        <script src="login.js?v=<?= time() ?>"></script>
    </body>

</html>