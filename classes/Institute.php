<?php

    class Institute {
        public int $eiin;
        public ?string $instName;
        public ?string $instNameBangla;
        public int $thanaId;
        public ?string $thanaName;
        public ?string $district;
        public ?string $level;
        public ?string $type;
        public ?string $address;
        public ?string $post;
        public ?string $mobile;
        public ?string $management;
        public ?string $mpo;
        public ?string $forWhom;
        public ?string $area;
        public ?string $geography;
        public ?string $nothiNumber;
        
        protected static $table = "institutes";
       
        public static function find(int $eiin, ExPDO $db, string $columns = "*"){
            $table = self::$table;
            $sql = "SELECT $columns FROM institutes AS i INNER JOIN user_district AS ud ON i.district = ud.district WHERE eiin=:eiin";
            $parameters = array("eiin"=>$eiin);
            return $db->fetchClass($sql, self::class, $parameters);
        }

        public static function search(mixed $conditions, ExPDO $db, string $columns = "*"){
            $whereClause = "";
            if($conditions){
                foreach($conditions as $column => $value) $whereClause .= "$column=:$column AND ";
                $whereClause = "WHERE " . rtrim($whereClause, " AND ");
            }
            $sql =  "SELECT $columns FROM institutes AS i INNER JOIN user_district AS ud ON i.district = ud.district " . $whereClause;
            return $db->fetchClasses($sql, self::class, $conditions);
        }
    }

?>



