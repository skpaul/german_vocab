<?php

    declare(strict_types=1);
    require_once("Required.php");

    #region Library instance declaration & initialization
        $logger = new Logger(ROOT_DIRECTORY);
        $crypto = new Cryptographer(SECRET_KEY);
        $clock = new Clock();
        $db = new ExPDO(DB_SERVER, DB_NAME, DB_USER, DB_PASSWORD);
    #endregion

?>

<!DOCTYPE html>
<html>

    <head>
        <title>Home || <?= GENERIC_APPLICATION_NAME ?> - <?= ORGANIZATION_FULL_NAME ?></title>
        <?php
            Required::gtag()->html5shiv()->metaTags()->favicon()->sweetModalCSS()->omnicss();
        ?>

        <style>
            marquee {
                border-radius: 20px;
                border: 1px solid transparent;
            }

            marquee:hover {
                /* background-color: #2d333b; */
                /* border: 1px solid #bfbfbf; */
            }

            .marquee-items {
                display: flex;
                flex-direction: row;
                list-style-position: inside;
                padding: 10px;
            }

            .marquee-items li {
                margin-right: 20px;
                color: var(--scroll-color);
            }

            .marquee-items li>a {

                /* color:#FFF; */
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
            <!-- <marquee class="mt150" id="marquee" behavior="" direction="left" scrollamount="5">
                    <ul class="marquee-items">
                        <li><a href="$fileUrl" target="_blank">Here is the notice</a></li>
                    </ul>
                </marquee> -->
                <div class="ba bc bg-1">
                    <div class="container-700 mv-1.5">
                        <div class="round bg-2 ba bc pv-2.0 ph-1.5">
                            <div class="fs-140% fw-500 bn mb-2.5 lh-1.3">Inspection & Audit Management System</div>
                            <a class="green-rest bc-0 br-5 ph-0.7 pv-0.4 button" href="<?= BASE_URL ?>/app/login/login.php">Login</a>
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
    Required::jquery()->hamburgerMenu();
    ?>
    <script>
        var base_url = '<?php echo BASE_URL; ?>';
        $(function() {
            $('#marquee').mouseover(function() {
                this.setAttribute('scrollamount', 0, 0);
                $(this).trigger('stop');
            }).mouseout(function() {
                this.setAttribute('scrollamount', 5, 0);
                $(this).trigger('start');
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