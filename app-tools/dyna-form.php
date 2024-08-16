<?php
    require_once("../Required.php");
    require_once("prevent_access_if_not_localhost.php");
    $queryString = $_SERVER['QUERY_STRING'];
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
                grid-template-columns: repeat(6, 1fr);
                /* gap: 10px; */
            }
            input{
                /* width: 100%; */
            }
            .field{
                display: flex;
                flex-direction: column;
                padding: 10px;
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
        
        <form method="post" action="<?=htmlspecialchars($_SERVER['PHP_SELF']);?>?<?=$queryString?>">
            <select name="dbTable">
                <option value="1">Applicant Table</option>
                <option value="2">Choices Table</option>
                <option value="3">Merit List Table</option>
            </select>
            <button name="showCols" type="submit">Submit</button>
        </form>

        <?php
            if(isset($_POST["showCols"])){
                $table = $_POST["dbTable"];
                if($table == "1") $table = "applicants";
                if($table == "2") $table = "choices";
                if($table == "3") $table = "ntrca_national_merit_data";
                $columns = $db->getFields($table);
        ?>
                <form method="post" target='formresponse' action="dyna-form-data.php?t=<?=$_POST["dbTable"]?>">
                    <div class="grid">
                        <?php
                            function createOperator($name){
                                $html = <<<HTML
                                    <select name="{$name}">
                                        <option value="">operator</option>
                                        <option value="=">=</option>
                                        <option value="!=">!=</option>
                                        <option value="<"><</option>
                                        <option value=">">></option>
                                        <option value="like">like</option>
                                    </select>
                                HTML;
                                return $html;
                            }
                            foreach ($columns as $column) {
                                $op = createOperator("Operator". $column["Field"]);
                                $html = <<<HTML
                                    <div class="field">
                                        <div>
                                            <label>{$column["Field"]}</label> 
                                        </div>
                                        <div>
                                        {$op} <input type="text" name="{$column['Field']}">
                                        </div>
                                    </div>
                                HTML;
                                echo $html;
                            }
                        ?>
                    </div>

                    <br>
                    <select name="queryType">
                        <option value="1">List</option>
                        <option value="2">Count</option>
                    </select>
                    <button name="showData" type="submit">Submit</button>
                </form>
        <?php
            }
        ?>

        <iframe name='formresponse' style="width: 100%; border: none; height: 349px; overflow: auto;"></iframe>

    </body>
</html>
