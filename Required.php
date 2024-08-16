<?php
// namespace Nobanno;

$localHost =  "{$_SERVER['HTTP_HOST']}";//{$_SERVER['REQUEST_URI']}";

require_once("CONSTANTS.php");

spl_autoload_register(function ($class) {
    $modelPath = ROOT_DIRECTORY . '/classes/' . $class . '.php';
    $libPath = ROOT_DIRECTORY . '/vendor/nobanno/nobanno/src/' . $class . '.php';
    $incPath = ROOT_DIRECTORY . '/inc/' . $class . '.php';
    $sources = array($modelPath, $libPath, $incPath );

    foreach ($sources as $source) {
        if (file_exists($source)) {
            require_once $source;
        } 
    } 
});

class Required{

    #region Partial pages
        public static function gtag($version = null){
            //require_once(ROOT_DIRECTORY . '/inc/gtag.html');
            return new static;
        }
        public static function metaTags($version = null){
            require_once(ROOT_DIRECTORY . '/inc/meta-tags.html');
            return new static;
        }

        public static function favicon($version = null){
            require_once(ROOT_DIRECTORY . '/inc/favicon.php');
            return new static;
        }

        public static function headerBrand($version = null){
            require_once(ROOT_DIRECTORY .  '/inc/HeaderBrand.php');
            return new static;
        }

        public static function applicantHeaderNav($version = null){
            require_once(ROOT_DIRECTORY . '/inc/ApplicantHeaderNav.php');
            return new static;
        }

        public static function leftNav($version = null){
            require_once(ROOT_DIRECTORY . '/inc/LeftNav.php');
            return new static;
        }

        public static function Footer($version = null){
            require_once(ROOT_DIRECTORY . '/inc/Footer.php'); //used in applicants panel
            return new static;
               
        }

    
    #endregion

    #region JavaScript
        public static function jquery($version = null){
            echo '<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>';
            // echo '<script src="'.BASE_URL.'/assets/jquery/jquery-3.6.0.min.js"></script>';
            return new static;
        }

        public static function hamburgerMenu(){
            echo '<script src="https://cdn.jsdelivr.net/gh/skpaul/hamburger-menu@v1.0.0/hamburger-menu.js"></script>';
            
            return new static;
        }

        public static function html5shiv(){
            echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7.3/html5shiv.min.js" integrity="sha512-UDJtJXfzfsiPPgnI5S1000FPLBHMhvzAMX15I+qG2E2OAzC9P1JzUwJOfnypXiOH7MRPaqzhPbBGDNNj7zBfoA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>';
            return new static;
        }

        //moment is required for swift-submit.js
        public static function moment(){
            echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js" integrity="sha256-4iQZ6BVL4qNKlQ27TExEhBN1HFPvAvAMbFavKKosSWQ=" crossorigin="anonymous"></script>';
            return new static;
        }

        public static function html2pdf(){
            echo '<script src="'.BASE_URL.'/assets/plugins/html2pdf/html2pdf.bundle.min.js"></script>';
            return new static;
        }

        public static function leftNavJS(){
            echo '<script src="https://cdn.jsdelivr.net/gh/skpaul/left-nav@1.0.0/left-nav.js"></script>';            
            return new static;
        }

        /**
         * swiftSubmit() DEPRECATED.
         * 
         * Prerequisites - mobileNumberParser() and moment();
         */
        public static function swiftSubmit(){
            // echo '<script src="https://cdn.jsdelivr.net/gh/skpaul/swift-submit@3.0.0/swift-submit.min.js"></script>';
            echo '<script src="'.BASE_URL.'/assets/plugins/swift-submit/v3.0.0/swift-submit-new.js"></script>';
            return new static;
        }
        /**
         * swiftForm()
         * 
         * Prerequisites - sweetModal and moment();
         */
        public static function swiftForm(){
            // echo '<script src="https://cdn.jsdelivr.net/gh/skpaul/swift-form@4.0.0/swift-form.min.js"></script>';
            // echo '<script src="'.BASE_URL.'/assets/plugins/swift-form/v4.0.0/swift-form.min.js"></script>';
            echo '<script src="http://localhost/swift-form/swift-form.js"></script>';
            return new static;
        }

        /**
         * formstar()
         * 
         * Prerequisites - sweetModal and moment();
         */
        public static function formstar(){
            echo '<script src="https://cdn.jsdelivr.net/gh/skpaul/formstar@1.0.5/formstar.min.js"></script>';
            // echo '<script src="'.BASE_URL.'/assets/plugins/formstar/v1.0.0/formstar.min.js"></script>';
            // echo '<script src="http://localhost/formstar/formstar.js"></script>';
            return new static;
        }


        public static function multiStepForm(){
            echo '<script src="https://cdn.jsdelivr.net/gh/skpaul/multi-step-form@1.0.1/multi-step-form.min.js"></script>';
            return new static;
        }

    #endregion

    #region CSS
        public static function omnicss(){
            //Documentation: https://skpaul.github.io/omnicss/
            echo '<link href="https://cdn.jsdelivr.net/gh/skpaul/omnicss@13.0.0/omnicss.min.css" rel="stylesheet">';
            // echo ' <link href="'.BASE_URL.'/assets/css/omnicss/v12.0.1/omnicss.min.css"  rel="stylesheet">';
            echo '<link href="'. BASE_URL .'/assets/css/theme.css" rel="stylesheet">';
            echo '<link href="'. BASE_URL .'/assets/css/new-color-scheme.css" rel="stylesheet">';
            return new static;
        }
        public static function griddle(){
            echo ' <link href="https://cdn.jsdelivr.net/gh/skpaul/griddle@2.0.1/griddle.min.css"  rel="stylesheet">';
            // echo ' <link href="'.BASE_URL.'/assets/css/griddle/v1.0.1/griddle.min.css"  rel="stylesheet">';
            return new static;
        }
    #endregion

    #region OverlayScrollbar Plugin
        public static function overlayScrollbarCSS($version = null){
            echo '<link rel="stylesheet" href="'.BASE_URL.'/assets/plugins/OverlayScrollbars/css/OverlayScrollbars.min.css">';
            echo '<link rel="stylesheet" href="'.BASE_URL.'/assets/plugins/OverlayScrollbars/css/os-theme-round-light.css">';

            return new static;
        }

        public static function overlayScrollbarJS($version = null){
            echo '<script src="'.BASE_URL.'/assets/plugins/OverlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>';
            /*
                //How to use----------------------
                $(document).ready(function(){   
                    $('.overlayScroll').overlayScrollbars({
                        className: 'os-theme-round-light',
                        scrollbars : {
                            visibility: "auto", 
                            autoHide: 'leave',
                            autoHideDelay: 100
                        }                    
                    });
                });
            */

            return new static;
        }

    #endregion

    public static function sweetModalJS(){
        echo '<script src="'.BASE_URL.'/assets/plugins/jquery.sweet-modal-1.3.3/jquery.sweet-modal.min.js"></script>';
        return new static;
    }

    public static function sweetModalCSS(){
        echo '<link rel="stylesheet" href="'.BASE_URL.'/assets/plugins/jquery.sweet-modal-1.3.3/jquery.sweet-modal.min.css">';
        return new static;
    }
   
    //requried for swiftSubmit and swiftChanger
    public static function mobileValidator(){
        echo '<script src="'.BASE_URL.'/assets/js/plugins/mobile-number-validator/mobile-number-validator.js"></script>';
        return new static;
    }

    public static function swiftNumeric(){
        echo '<script src="https://cdn.jsdelivr.net/gh/skpaul/swift-numeric@1.0.0/swift-numeric.js"></script>';
        return new static;
    }

    public static function swiftNumericInput(){
        echo '<script src="'.BASE_URL.'/assets/plugins/swift-numeric-input.js"></script>';
        return new static;
    }

    /**
     * airDatePicker()
     * 
     * Includes necessary css and js.
     */
    public static function airDatePicker(){
        echo '<link href="'.BASE_URL.'/assets/plugins/air-datepicker/css/datepicker.min.css" rel="stylesheet">';

        echo '<script src="'.BASE_URL.'/assets/plugins/air-datepicker/js/datepicker.min.js"></script>';
        // <!-- Include English language -->
        echo '<script src="'.BASE_URL.'/assets/plugins/air-datepicker/js/i18n/datepicker.en.js"></script>';
        return new static;
    }


    public static function airDatePickerJS(){
        echo '<script src="'.BASE_URL.'/assets/plugins/air-datepicker/js/datepicker.min.js"></script>';
        // <!-- Include English language -->
        echo '<script src="'.BASE_URL.'/assets/plugins/air-datepicker/js/i18n/datepicker.en.js"></script>';
        return new static;
    }

    public static function airDatePickerCSS(){
        echo '<link href="'.BASE_URL.'/assets/plugins/air-datepicker/css/datepicker.min.css" rel="stylesheet">';
        return new static;
    }
}


?>