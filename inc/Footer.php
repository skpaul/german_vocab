<?php
declare(strict_types=1);
class Footer{
    /**
     * prepare()
     * 
     * This method has dynamic argument(s).
     * 
     * Arguments- 1) str Base URL 2) bool showHamburger
     */
    public static function prepare(array $params=array()):string
    {
        $orgName = ORGANIZATION_SHORT_NAME;
        $year = date("Y");
        $html = <<<HTML
            <div class="footer-container ">
                    <div class="footer-content-wrapper mt-0.5">
                        <div class="copyright">©{$orgName}, {$year}.</div>
                        <div class="powered-by">Powered By: Winbip Solutions</div>
                    </div>
            </div>
        HTML;

        return $html;
    }
}
?>




