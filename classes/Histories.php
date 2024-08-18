<?php

    class Histories {
        public int $id;
        public ?int $userId;
        public ?int $wordId;
        public ?int $frequency;

      
        protected static $table = "histories";
       
        public static function set(int $userId, int $wordId, ExPDO $db, string $columns = "*"){
            $table = self::$table;
            $sql = "SELECT $columns FROM {$table} WHERE userId=$userId AND wordId=$wordId";
            $parameters = array();
            $history =  $db->fetchClass($sql, self::class, $parameters);
            if($history){
                $sql = "UPDATE $table SET frequency = frequency + 1 WHERE userId=$userId AND wordId=$wordId";
                $db->update($sql);
            }
            else{
                $sql = "INSERT INTO $table(userId, wordId, frequency) VALUES($userId,$wordId,1)";
                $db->insert($sql);
            }
        }
    }

?>