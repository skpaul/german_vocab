<?php

    class Words {
        public int $id;
        public ?string $english;
        public ?string $german;
        public ?string $banglaPro;
        public ?string $createdOn;
        public ?string $updatedOn;
       
        protected static $table = "words";
       
        public static function selectRandomly(ExPDO $db, string $columns = "*"){
            $table = self::$table;
            $sql = "SELECT $columns FROM {$table} ORDER BY RAND() LIMIT 1";
            $parameters = array();
            return $db->fetchClass($sql, self::class, $parameters);
        }

        public static function find(int $visitId, ExPDO $db, string $columns = "*"){
            $table = self::$table;
            $sql = "SELECT $columns FROM {$table} WHERE visitId=:visitId";
            $parameters = array("visitId"=>$visitId);
            return $db->fetchClass($sql, self::class, $parameters);
        }

        public static function search(array $conditions, ExPDO $db, string $columns = "*"){
            $whereClause = "";
            if($conditions){
                foreach($conditions as $column => $value) $whereClause .= "$column=:$column AND ";
                $whereClause = "WHERE " . rtrim($whereClause, " AND ");
            }
            $sql =  "SELECT v.visitId, v.eiin, v.tentativeMonth, v.tentativeYear, v.actualVisitDate, v.reportingDate, m.monthName, GROUP_CONCAT(visitors.visitorName SEPARATOR ', ') AS visitorName FROM visits AS v INNER JOIN months AS m ON v.tentativeMonth = m.monthId INNER JOIN visit_visitor ON v.visitId = visit_visitor.visitId INNER JOIN visitors ON visit_visitor.visitorId = visitors.visitorId " . $whereClause . " 	GROUP BY v.visitId ORDER BY visitId DESC";

            return $db->fetchClasses($sql, self::class, $conditions);
        }
    }

?>