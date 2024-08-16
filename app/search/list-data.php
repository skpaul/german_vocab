<?php

    declare(strict_types=1);
    require_once("../../Required.php");

    $logger = new Logger(ROOT_DIRECTORY);  //This must be in first position.
    $crypto = new Cryptographer(SECRET_KEY);
    $db = new ExPDO(DB_SERVER, DB_NAME, DB_USER, DB_PASSWORD);
    $clock = new Clock();
    $baseUrl = BASE_URL;
    try {
        $session = new Session($db, SESSION_TABLE);
        $session->validate("$baseUrl/sorry.php?msg=Invalid session", $crypto);
        $encSessionId = trim($_GET["session"]);
        $userId = $session->getData("userId");
    } catch (\SessionException $th) {
        HttpHeader::redirect("$baseUrl/sorry.php?msg=Invalid session.");
    } catch (\Exception $exp) {
        HttpHeader::redirect("$baseUrl/sorry.php?msg=Something is wrong. Please try again");
    }

    $whereClause = " i.eiin > 0 AND ud.userId = $userId";
    $havingClause = "";
    $searchType = trim($_GET["searchType"]);

    $params = [];
    if($searchType == "instituteSearch"){
        if(isset($_GET["visitCount"]) && !empty($_GET["visitCount"])){
            $visitCount = trim($_GET["visitCount"]);
            $havingClause = " HAVING visitCount = :visitCount";
            $params["visitCount"] = $visitCount;
        }
    }
    if($searchType == "inspectionSearch"){
        if(isset($_GET["reportStatus"]) && !empty($_GET["reportStatus"])){
            $value = trim($_GET["reportStatus"]);
            if($value == "pending"){
                $whereClause .= " AND v.actualVisitDate IS NULL";
            }
            if($value == "not-pending"){
                $whereClause .= " AND v.actualVisitDate IS NOT NULL";
            }
        }

        if(isset($_GET["visitorId"]) && !empty($_GET["visitorId"])){
            $value = trim($_GET["visitorId"]);
            $whereClause .= " AND vv.visitorId = :visitorId";
            $params["visitorId"] = $value;
        }

        if(isset($_GET["tentativeMonth"]) && !empty($_GET["tentativeMonth"])){
            $value = trim($_GET["tentativeMonth"]);
            $whereClause .= " AND v.tentativeMonth = :tentativeMonth";
            $params["tentativeMonth"] = $value;
        }

        if(isset($_GET["tentativeYear"]) && !empty($_GET["tentativeYear"])){
            $value = trim($_GET["tentativeYear"]);
            $whereClause .= " AND v.tentativeYear = :tentativeYear";
            $params["tentativeYear"] = $value;
        }

        if(isset($_GET["actualVisitDateFrom"]) && !empty($_GET["actualVisitDateFrom"])){
            $value = trim($_GET["actualVisitDateFrom"]);
            $value = $clock->toString($value, DatetimeFormat::MySqlDate());
            $whereClause .= " AND v.actualVisitDate >= :actualVisitDateFrom";
            $params["actualVisitDateFrom"] = $value;
        }

        if(isset($_GET["actualVisitDateTo"]) && !empty($_GET["actualVisitDateTo"])){
            $value = trim($_GET["actualVisitDateTo"]);
            $value = $clock->toString($value, DatetimeFormat::MySqlDate());
            $whereClause .= " AND v.actualVisitDate <= :actualVisitDateTo";
            $params["actualVisitDateTo"] = $value;
        }
    }

    $commonColumns = "i.instName, i.instNameBangla, i.thanaName, i.district, i.`level`, i.type, i.post, i.mobile, i.management, i.mpo, i.forWhom, i.area, i.geography, i.nothiNumber";
    $arrCommonColumns = explode(",", $commonColumns);
    foreach ($arrCommonColumns as $column) {
        $column = trim($column);
        $column = str_replace("i.","",$column);
        if(isset($_GET[$column]) && !empty($_GET[$column])){
            $value = $_GET[$column]; //data has blank spaces. therefore, do not use "trim()"
            $whereClause .= " AND i.$column = '$value'";
        }
    }
    
    $limitClause = "";
    if(isset($_GET["page"]) && !empty($_GET["page"]) && isset($_GET["size"]) && !empty($_GET["size"])){
        $page = $_GET["page"];
        $size = $_GET["size"];
        $offset = ($page-1) * $size;
        $limitClause = "LIMIT $size OFFSET $offset";
    }

    if($searchType == "instituteSearch"){
        $sql = "SELECT i.eiin, i.instName, i.instNameBangla, i.thanaName, i.district, i.`level`, i.type, i.post, i.mobile, i.management, 
                        i.mpo, i.forWhom, i.area, i.geography, i.nothiNumber,	count(v.eiin) as visitCount 
                        FROM institutes AS i LEFT JOIN visits AS v ON i.eiin = v.eiin INNER JOIN user_district AS ud ON i.district = ud.district 
                WHERE $whereClause
                GROUP BY i.eiin, i.instName, i.instNameBangla, i.thanaName, i.district, i.`level`, i.type, i.post, i.mobile, i.management, i.mpo, i.forWhom, i.area, i.geography, i.nothiNumber
                $havingClause ORDER BY i.instName $limitClause";
        $data = $db->fetchAssocs($sql, $params);
    }

    if($searchType == "inspectionSearch"){
        $sql = "SELECT i.eiin, i.instName, i.instNameBangla, i.thanaName, i.district, i.`level`, i.type, i.post, i.mobile, i.management, i.mpo, i.forWhom, i.area,  i.geography, i.nothiNumber, v.tentativeMonth, v.tentativeYear, v.actualVisitDate, GROUP_CONCAT(visitors.visitorName SEPARATOR ', ') AS visitorName
                FROM user_district AS ud INNER JOIN	institutes AS i	ON ud.district = i.district	INNER JOIN visits AS v ON i.eiin = v.eiin INNER JOIN visit_visitor AS vv ON v.visitId = vv.visitId INNER JOIN visitors ON vv.visitorId = visitors.visitorId 
                WHERE $whereClause
                GROUP BY v.visitId
                ORDER BY instName $limitClause";
        $data = $db->fetchAssocs($sql, $params);
    }

   
    // echo(json_encode(["last_page"=>$page + 1, "data"=>$data]));
    echo(json_encode($data));
?>