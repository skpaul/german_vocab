<?php
    require_once("../Required.php");
    $db = new Database(DB_SERVER, DB_NAME, DB_USER, DB_PASSWORD);  $db->connect();
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Dyna Form</title>
        <style>
            .grid{
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 10px;
            }
            .field{
                display: flex;
                flex-direction: column;
            }

            table{
                border-collapse: collapse;
            }
            thead{
                background-color: lightblue;
            }
            th, td{
                border:1px solid gray;
                padding: 5px;
            }
        </style>
    </head>
    <body>
        <?php
            if(isset($_POST["showData"])){
                $table = $_GET["t"];
                if($table == "1") $table = "applicants";
                if($table == "2") $table = "choices";
                if($table == "3") $table = "ntrca_national_merit_data";

                $where = "";
                $values = [];
                foreach($_POST as $key => $value) {
                    if(isset($value) && !empty($value)){
                        if($key == "queryType") continue;
                        $pos = strpos($key, "Operator");
                        if ($pos === false) { //This is an input
                            if(!empty($_POST["Operator{$key}"])){
                                $operator = $_POST["Operator{$key}"];
                                if($operator == "like"){
                                    $where .=  "$key $operator '%$key%' AND ";
                                }
                                else{
                                    $where .=  "$key $operator :$key AND ";
                                    $values[$key] = $value;
                                }
                            }
                        }
                    }
                }

                $where = rtrim($where, " AND ");
                if(!empty($where)) $where = " WHERE $where";
                $pdo = $db->getPDO();
                
                if($_POST["queryType"]==1) $sql = "SELECT * FROM {$table} $where";
                if($_POST["queryType"]==2) $sql = "SELECT count(*) AS Qty FROM {$table} $where";
               
                $statement =  $pdo->prepare($sql) ;
                $statement->setFetchMode(PDO::FETCH_ASSOC); 
                $statement->execute($values);
                $rows =  $statement->fetchAll();

                if(count($rows)>0){
                    $header = $rows[0];
                    echo '<table><thead><tr><th>SL.</th>';
                    foreach ($header as $key => $value) echo "<th>$key</th>";
                    echo '</tr></thead><tbody>';
        
                    $sl = 1;
                    foreach ($rows as $row) {
                        echo "<tr><td>$sl</td>";
                        foreach ($row as $key => $value) echo "<td>$value</td>";
                        echo '</tr>';
                        $sl++;
                    }
                    echo '</tbody></table>';
                }
                else{
                    echo "Nothing found.";
                }
            }
        ?>
    </body>
</html>
