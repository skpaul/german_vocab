<?php

    class Words {
        public int $id;
        public ?string $english;
        public ?string $german;
        public ?string $ipa;
        public ?string $phoneticSpelling;
        public ?string $pronunciation;
        public ?string $definition;
        public ?string $number;
        public ?string $gender;
        public ?string $partsOfSpeech;
        public ?string $createdOn;
        public ?string $updatedOn;
      
        protected static $table = "words";
       
        public static function selectRandomly(ExPDO $db, string $columns = "*"){
            $sql = "SELECT
                        words.id, 
                        words.english, 
                        words.german, 
                        words.ipa, 
                        words.phoneticSpelling, 
                        words.pronunciation, 
                        words.definition, 
                        words.createdOn, 
                        words.updatedOn, 
                        words.rowVersion, 
                        genders.`name` AS gender, 
                        numbers.`name` AS `number`, 
                        parts_of_speech.`name` AS partsOfSpeech
                    FROM
                        words
                        INNER JOIN
                        genders
                        ON 
                            words.gender = genders.id
                        INNER JOIN
                        numbers
                        ON 
                            words.number = numbers.id
                        INNER JOIN
                        parts_of_speech
                        ON 
                            words.partsOfSpeech = parts_of_speech.id
                        ORDER BY RAND() LIMIT 1";
                        
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