<?php
    declare(strict_types=1);
    require_once("Required.php");

    #region Library instance declaration & initialization
        $logger = new Logger(ROOT_DIRECTORY);
        $clock = new Clock();
    #endregion

    $msg = "That is all we know at this moment. If you're middle of something, sorry for the inconvenience.";

    if(isset($_GET["msg"]) || !empty($_GET["msg"]))
        $msg = $_GET["msg"];

        $pageTitle = "Sorry"
?>

<!DOCTYPE html>
<html>
    <head>
        <title><?=$pageTitle?> - <?= ORGANIZATION_FULL_NAME ?></title>
        <?php
            Required::gtag()->html5shiv()->metaTags()->favicon()->omnicss();
        ?>
    </head>

    <body>
        <header class="header">
            <div class="container">
                <?php
                    echo HeaderBrand::prepare(array("baseUrl"=>BASE_URL, "hambMenu"=>true));
                    echo ApplicantHeaderNav::prepare(array("baseUrl"=>BASE_URL));
                ?>
            </div>
        </header>
        <main class="main">
            <div class="container mv-3.0">
                <div class="ba bc-gray-7">
                    <div class="container-900 mv-1.5">
                        <div class="round bg-light pa-1.5 mb-2.0 ba bc-gray-8">
                            <div class="fs-200%" >Couldn't Continue</div>
                            <div class="mv-2.0"><?=$msg?></div>
                            <a href="<?php echo BASE_URL; ?>/" class="ba br-3 pa-0.4 bg-dark c-blue-3 outline fs-80%">Go to Home</a>
                        </div>
                        
                    </div>
                </div>
            </div><!-- .container -->
        </main>
        <footer>
            <div class="container">
                <div class="divider-h bg-gray-6 mb-025"></div>
                <?php
                    echo Footer::prepare(array());
                ?>
            </div>
        </footer>
        <?php
            Required::jquery()->hamburgerMenu();
        ?>
        <script>
            var base_url = '<?php echo BASE_URL; ?>';
            $(function() {


            })
        </script>
    </body>
</html>