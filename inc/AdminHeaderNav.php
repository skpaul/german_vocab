<?php
declare(strict_types=1);
class AdminHeaderNav{
    /**
     * prepare()
     * 
     * This method has dynamic argument(s).
     * 
     * Arguments- 1) str Base URL
     */
    public static function prepare(array $params):string
    {
        // $numberOfArguments = func_num_args();
        // $arguments = func_get_args();
        $baseUrl =  $params["baseUrl"]; 
        $role =  ""; 
        if(isset($params["role"]) || !empty($params["role"])){
            $role = $params["role"];
        }

        $regMenus = ""; //only for registration user.
        $dashboard = ""; //

        if (isset($_GET["session"]) && !empty($_GET["session"])) {
            $session = "?session=" . trim($_GET["session"]);

            $dashboard = <<<HTML
                <a href="$baseUrl/app/admins/admin-dashboard.php{$session}"><span class="m-icons mr-025">home</span>Dashboard</a>  
                <a href="$baseUrl/app/admins/attendance-sheet/preview.php{$session}"><span class="m-icons mr-025">article</span>Attendance Sheet</a>
                <a href="$baseUrl/app/admins/seat-card/seat-card.php{$session}" target="_blank"><span class="m-icons mr-025">article</span>Seat Card</a>
            HTML;
            $loginHtml = <<<HTML
                <a href="$baseUrl/app/admins/admin-login/admin-logout-processor.php{$session}"><span class="m-icons mr-025">article</span>Log out</a>
            HTML;
            if($role=="reg"){
                $regMenus = <<<HTML
                    <a href="$baseUrl/app/admins/update/registration-search.php{$session}"><span class="m-icons mr-025">article</span>Update</a>
                HTML;
            }
        } else {
            $session = "";
            //show login if session not found
            $loginHtml = <<<HTML
                <a class="c-red-4" href="$baseUrl/app/admins/admin-login/admin-login.php"><span class="m-icons mr-025">article</span>Login</a>
            HTML;
        }

        $html = <<<HTML
                <div class="main-top-nav-container">
                    <div class="container">
                        <nav id="mainTopNav" class="main-top-nav">
                            $dashboard
                            $regMenus
                            $loginHtml
                        </nav> 
                    </div>
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

