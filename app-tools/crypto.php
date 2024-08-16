<?php
    require('../Required.php');

    // Required::Cryptographer();
    $crypto = new Cryptographer(SECRET_KEY);

    if(isset($_POST["valueToEncrypt"])){
        echo $crypto->encrypt($_POST["valueToEncrypt"]);
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>123</title>
</head>
<body>
    <form action="<?=$_SERVER['PHP_SELF']?>" method="post" enctype="multipart/form-data">
        <input type="text" name="valueToEncrypt" placeholder="enter text/number to encrypt">
        <input type="submit" value="Encrypt" name="encrypt">
    </form>
</body>
</html>

