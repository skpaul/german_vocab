<?php

declare(strict_types=1);
class ApplicantHeaderNav
{
    /**
     * prepare()
     * 
     * This method has dynamic argument(s).
     * 
     * Arguments- 1) str Base URL
     */
    public static function prepare(array $params): string
    {
        // $numberOfArguments = func_num_args();
        // $arguments = func_get_args();
        $baseUrl =  $params["baseUrl"];
        $loginHtml = "";
        $session = "";
        if (isset($_GET["session"]) && !empty($_GET["session"])) {
            $session = "?session=" . trim($_GET["session"]);
            $loginHtml = <<<HTML
                <a href="$baseUrl/app/login/logout-processor.php{$session}" style="color:#daaa3f;" ><span class="m-icons mr-025">logout</span>Log out</a>
            HTML;
        } else {
            $session = "";
            //show login if session not found
            $loginHtml = <<<HTML
                <a class="c-red-4" href="$baseUrl/app/login/login.php"><span class="m-icons mr-0.2 c-teal-2">login</span><span class="c-teal-2">Login</span></a>
            HTML;
        }

        $html = <<<HTML
                <style>
                    /* Too many menu items. Therefore, override default font size here to accomodate the nav bar. */
                    .main-top-nav>a{
                        /* font-size:0.66rem !important; */
                        color: #E0E8F2;
                    }
                </style>
                <div class="main-top-nav-container">
                    <!-- <div class="container"> -->
                        <nav id="mainTopNav" class="main-top-nav">
                            <a href="$baseUrl/index-api-version.php{$session}"><span class="m-icons mr-0.2">home</span><span>Home</span></a>                           
                            <a href="$baseUrl/app/learn/sentence.php{$session}"><span class="m-icons mr-0.2">home</span><span>Learn Sentence</span></a>                           
                            <a href="$baseUrl/app/add/word/new-word.php{$session}"><span class="m-icons mr-0.2">home</span><span>Add Word</span></a>                           
                            <a href="$baseUrl/app/add/example/new-example.php{$session}"><span class="m-icons mr-0.2">home</span><span>Add Example</span></a>                           
                                                    
                            {$loginHtml}
                        </nav> 
                    <!-- </div> -->
                </div>
        HTML;

        return $html;
    }
}
?>


<!-- <div class="brand-container">
    <div class="container-fluid ">
        <div class="brand">
            <img class="logo" src="http://demo.bar.teletalk.com.bd/bar/lower-court/enrolment/assets/images/bar-logo.png" alt="Bangladesh Govt. Logo">
            <div style="flex:1; margin-left: 0.4rem;">
                <div class="govt-name" >&nbsp;Government of the People's Republic of Bangladesh</div>
                <div class="org">Bangladesh Judicial Service Commissin</div>
            </div>
            <div class="ham-menu-container">
                <div  class="hamb" id="hambItem" style="display: block;">☰</div>
                <div class="hamb" id="hambClose" style="display: none;">✕</div>
            </div>
        </div>
    </div>
</div>
           
<div class="main-top-nav-container">
    <div class="container-fluid">
        <nav id="mainTopNav" class="main-top-nav">
            <a href="http://demo.bar.teletalk.com.bd/bar/lower-court/enrolment/index.php"><span class="m-icons">home</span>Home</a>    
            <a href="http://demo.bar.teletalk.com.bd/bar/lower-court/enrolment/applicant-copy/applicant-copy.php"><span class="m-icons">article</span>Applicant Copy</a>
            <a href="http://demo.bar.teletalk.com.bd/bar/lower-court/enrolment/court-higher/written/admit-card/hc-written-admit-card.php"><span class="m-icons">account_box</span>Admit Card</a>
            <a href="http://demo.bar.teletalk.com.bd/bar/lower-court/enrolment/payment-status/payment-status.php"><span class="m-icons">done_outline</span>Payment Status</a>
            <a href="http://demo.bar.teletalk.com.bd/bar/lower-court/enrolment/recover-userid/recover-userid.php"><span class="m-icons">perm_identity</span>Recover User ID</a>
            <a href="https://www.photobip.com" target="_blank"><span class="m-icons">crop</span>Photo Resizer</a>
            <a href="http://demo.bar.teletalk.com.bd/bar/lower-court/enrolment/help/customer-care.php"><span class="m-icons">phone_in_talk</span>Help</a>
        </nav> 
    </div>
</div> -->