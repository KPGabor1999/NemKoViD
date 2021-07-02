<?php
session_start();

require_once("../utils/verify_keys_exist.php");
require_once("../utils/Storage.php");
require_once("../utils/Auth.php");

$user_storage = new Storage(new JsonIO("../json_databases/users.json"));
$user_storage_handler = new Auth($user_storage);

//Eddigi felhasználók:
//Korom Pál Gábor - korom.p.gabor@gmail.com - H6N8XS
//ADMIN - admin@nemkovid.hu - admin

$inputs = [];
$errors = [];

if(count($_GET) > 0){
    if(verify_get("email_address", "password")){

        //E-mail cím:
        if(!filter_var($_GET["email_address"], FILTER_VALIDATE_EMAIL)){
            $errors["invalid_email_address_format"] = "Hiba: A megadott e-mail cím formailag helytelen.";
        }

        //Jelszó:
        if(trim($_GET["password"]) === ""){
            $errors["missing_password"] = "Hiba: Nem adtál meg jelszót.";
        }

        if($errors === []){
            $potential_user = $user_storage->findOne(["email_address" => $_GET["email_address"]]);
            if($potential_user !== NULL && password_verify($_GET["password"], $potential_user["password"])){
                $user_storage_handler->login($potential_user);
                //echo "Sikeres bejelentkezés: " . $_SESSION["user_id"] . "<br>";
                header("Location: ../../index.php");
            } else {
                $errors["invalid_email_address_or_password"] = "Hiba: Érvénytelen e-mail cím vagy jelszó.";
            }
        }
    } else {
        $errors["missing_inputs"] = "Hiba: Egy vagy több adat nem lett elküldve.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
    small{
        color: red;
    }
    </style>
</head>
<body>
    <h1>Bejelentkezés</h1>

    <?php if(isset($errors["missing_inputs"])): ?>
            <small><?=$errors["missing_inputs"]?></small><br>
    <?php endif ?>
    <?php if(isset($errors["invalid_email_address_format"])): ?>
            <small><?=$errors["invalid_email_address_format"]?></small><br>
    <?php endif ?>
    <?php if(isset($errors["missing_password"])): ?>
            <small><?=$errors["missing_password"]?></small><br>
    <?php endif ?>
    <?php if(isset($errors["invalid_email_address_or_password"])): ?>
            <small><?=$errors["invalid_email_address_or_password"]?></small><br>
    <?php endif ?>
    <form method="get" action="" novalidate>
        E-mail cím: <input type="text" name="email_address"><br>
        Jelszó: <input type="password" name="password"><br>
        <button type="submit">Bejelentkezés</button>
    </form>
</body>
</html>