<?php 
    require_once("../../../Required.php");
       
    #region Variable declaration & initialization
        $logger = new Logger(ROOT_DIRECTORY);
        $crypto = new Cryptographer(SECRET_KEY);
        $db = new ExPDO(DB_SERVER, DB_NAME, DB_USER, DB_PASSWORD);
        $validator = new DataValidator();
        $clock = new Clock();
        $json = new JSON();
    #endregion

    #region Validate form 
        try {
            $eiin = $validator->label("EIIN")->post("eiin")->required()->asInteger(false)->maxLen(6)->validate();
        } catch (\ValidationException $ve) {
            die($json->fail()->message($ve->getMessage())->create());
        }
        catch (\Exception $exp) {
            $logger->createLog($exp->getMessage());
            die($json->fail()->message($exp->getMessage())->create());
        }
    #endregion

    #region Data
        try {
            $sql = "SELECT i.instituteNameBn
                    FROM approval_authorities AS a INNER JOIN institutes AS i ON a.instituteId = i.instituteId
                    WHERE a.distCode = :distCode ORDER BY i.instituteNameBn";

            $institutes = $db->fetchAssocs($sql, array("distCode"=>$distCode));

            $institutes = (object)$institutes;
            $data = json_encode($institutes);
            exit( $json->success()->data($institutes)->create());

        } catch (\Exception $exp) {
            $logger->createLog($exp->getMessage());
        }
    #endregion

?>