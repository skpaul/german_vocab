<?php
    declare(strict_types=1);
    #region Import libraries
        require_once("../Required.php");
    #endregion

    
    #region Variable declaration & initialization
        $validator = new DataValidator();
        $logger = new Logger(ROOT_DIRECTORY);  //This must be in first position.
        $pageTitle = "General Instruction";
    #endregion

    #region Validate GET request
        try {
            $encConfigId = $validator->label("Config ID")->get("cid")->required()->asString(false)->validate();
        } catch (\ValidationException $ve) {
            die("Invalid request. Please try again.");
            // die($json->fail()->message($ve->getMessage())->create());
            // HttpHeader::redirect()
        }
    #endregion
?>

<!DOCTYPE html>
<html>
    <head>
        <title><?= $pageTitle ?> - <?= ORGANIZATION_SHORT_NAME ?></title>
        <?php
            Required::gtag()->metaTags()->favicon()->omnicss()->griddle();
        ?>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Anek+Bangla:wght@400;600;800&display=swap" rel="stylesheet">
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
            <div class="container">
                <div class="content mt-3.0 border bc-gray-7 bg-gray-8 pa-1.5">
                    <div class="fs-150% c-teal-2"><?= $pageTitle ?></div>
                    <div class="ucase fw-500 fs-70% c-gray-0">Read carefully before proceed</div>
                    <div class="border pa-1.5 bc-gray-6 bg-gray-9 mt-150">
                        <style>
                            .general-instruction{
                                /* border:1px solid var(--gray-7); */
                            }
                            .general-instruction>li{
                                color: var(--gray-1);
                                font-size: 0.89rem;
                                margin-bottom: 1rem;
                                font-family: 'Anek Bangla', sans-serif;
                                text-align: justify;
                            }
                        </style>
                        <div class="grid fr6-lg fr1-sm ">
                            <div class="fr4 fr1-sm">
                                <ul class="general-instruction">
                                    <li>
                                        একটি রোল, ব্যাচ এবং রেজিষ্ট্রেশন নং ব্যবহার করে কেবলমাত্র <span class="c-teal-3 fw-600">একটি আবেদন</span> করা যাবে।
                                    </li>
                                    <li>
                                        ফি জমাদানের পূর্ব পর্যন্ত আবেদনটি <span class="c-teal-3 fw-600">ড্রাফট হিসেবে</span> সংরক্ষিত থাকবে। 
                                    </li>
                                    <li>
                                        আবেদন প্রক্রিয়ার যে কোন পর্যায়ে আপনি <span class="fw-600">লগআউট</span> করতে পারেন। পুনরায় <span class="fw-600">লগইন</span> করে ড্রাফট আবেদন-এর অবশিষ্ট ধাপ সম্পন্ন করা যাবে। এই সময় ইতিমধ্যে পূরণকৃত যেকোন তথ্য পরিবর্তন, পরিমার্জন ও সংশোধন করতে পারেন।
                                    </li>
                                    <li>
                                        আবেদন দাখিলের পর প্রতিটি আবেদনের জন্য একটা স্বতন্ত্র ট্রাকিং নম্বর প্রদান করা হবে যেটা ব্যবহার করে ফি প্রদান করতে হবে।
                                    </li>
                                    <li>
                                        নির্দিষ্ট সময়ের মধ্যে ফি প্রদান করলেই কেবলমাত্র আবেদনটি চুড়ান্ত হিসেবে বিবেচিত হবে এবং পূর্ণাঙ্গ তথ্য সম্বলিত একটি অ্যাপ্লিক্যান্ট কপি পাবেন।
                                    </li>
                                    
                                    <li>
                                        আবেদন সংক্রান্ত কোন সাহায্যের জন্য কাস্টোমার কেয়ার-এ যোগাযোগ করুন। এছাড়াও, উপরের মেনু থেকে Help লিংক-এ ক্লিক করে আপনার প্রশ্ন সাবমিট করতে পারেন।
                                    </li>
                                </ul>
                            </div>
                            <style>
                            .red-grad{
                                background-image: linear-gradient(to right top, var(--red-7), var(--red-6), var(--red-5),var(--red-4));
                                border-color: var(--red-9);
                                border-top-width: 6px;
                                border-top-color: var(--red-6);
                                border-top-style: solid;
                                border-top-right-radius: 8px;
                                border-top-left-radius: 8px;
                            }
                        </style>   
                            <div class="fr2-lg fr1-sm" style="font-family: 'Anek Bangla', sans-serif;">
                                <div class="red-grad c-red-0 pa-0.5 fs-90% hover:dark">
                                    ফি প্রদানের পর কোন অবস্থাতেই আবেদনটির কোন তথ্য পরিবর্তন, পরিমার্জন ও সংশোধন করা যাবে না।
                                </div>
                            </div>
                        </div>

                        <div class="mt-150">
                            <a class="button ba pa-0.5 fw-400 inflex bc-teal-5 c-teal-5" href="verify-applicant/verify-applicant.php?cid=<?=$encConfigId?>">Proceed</a>

                            <button class="button fs-70% ph-100 br-6 bc-teal-6 c-teal-4 bg-0"  >Button</button>
                        </div>
                    </div><!-- card/ -->
                </div><!-- .content/ -->
            </div><!-- .container// -->
        </main>
        <footer class="footer mt-150 pt-100">
            <div class="container">
                <!-- <div class="content"> -->
                <div class="line bg-gray-8"></div>
                    <?= Footer::prepare() ?>
                <!-- </div> -->
            </div>
        </footer>
        <script>
            var baseUrl = '<?php echo BASE_URL; ?>';
        </script>
        <?php
            Required::jquery()->hamburgerMenu();
        ?>
        
    </body>
</html>