<?php
    declare(strict_types=1);
    require_once("../../Required.php");

    #region Variable declaration & initialization
        $logger = new Logger(ROOT_DIRECTORY);  //This must be in first position.
        $crypto = new Cryptographer(SECRET_KEY);
        $db = new ExPDO(DB_SERVER, DB_NAME, DB_USER, DB_PASSWORD);
        $clock = new Clock();
        $pageTitle = "Logout";
    #endregion
    // header("Cache-Control", "no-cache, no-store, must-revalidate");
    header("Cache-Control: no-cache, must-revalidate");
?>

<!DOCTYPE html>
<html>
    <head>
        <title><?= $pageTitle ?> - <?= ORGANIZATION_SHORT_NAME ?></title>
        <script>
                history.pushState(null, null, document.URL);
                window.addEventListener('popstate', function() {
                    history.pushState(null, null, document.URL);
                });
        </script>
        <?php
            Required::gtag()->metaTags()->favicon()->omnicss()->griddle();
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
                <div class="ba bc-gray-7">
                    <div class="container-900 mv-1.5">
                        <div class="fs-120% fw-800"><?=GENERIC_APPLICATION_NAME?></div>
                        <h2 class="mt-1.0 mb-1.5 fw-700 c-gray-2"><?=$pageTitle?></h2>
                        <div class="round bg-light pa-1.5 ba bc-gray-7">
                            <div>
                                Logout successfully.
                            </div>
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
            var baseUrl = '<?php echo BASE_URL; ?>';
            history.pushState(null, document.title, location.href);
            window.addEventListener('popstate', function(event) {
                history.pushState(null, document.title, location.href);
            });
        </script>
        <?php
            Required::jquery()->hamburgerMenu();
        ?>
    </body>

</html>