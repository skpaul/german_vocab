<?php

    class Examples {
        public int $id;
        public ?string $english;
        public ?string $german;
      
        protected static $table = "examples";
       
        public static function get(string $germanWord, ExPDO $db){
            $table = self::$table;
            $sql = "SELECT id, english, german FROM examples WHERE german REGEXP '\\\b$germanWord\\\b' AND isPublished = 1;";
            $parameters = array();
            return $db->fetchClasses($sql, self::class, $parameters);
        }
    }

?>