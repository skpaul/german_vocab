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
       
        public static function selectRandomly(ExPDO $db){
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

        public static function findGerman(string $germanWord, ExPDO $db){
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
                        
                        WHERE words.german = :german
                        ORDER BY words.german LIMIT 1";
                        
            $parameters = array("german"=>$germanWord);
            return $db->fetchClass($sql, self::class, $parameters);
        }
    }
?>