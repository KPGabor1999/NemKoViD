<?php

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

if(count($_POST) > 0){
    if(verify_post("fullname", "TAJ_number", "home_address", "email_address", "password", "confirm_password")){
        //Egyes inputok validálása

        //Teljes név:
        if(trim($_POST["fullname"]) !== ''){
            $inputs["fullname"] = $_POST["fullname"];
            //echo "Teljes név: " . $inputs["fullname"] . "<br>";
        } else {
            $errors["missing_fullname"] = "Hiba: Nincs megadva teljes név.";
        }

        //TAJ-szám:
        if(preg_match("/[0-9]{9}/i", $_POST["TAJ_number"])){
            $inputs["TAJ_number"] = $_POST["TAJ_number"];
            //echo "TAJ-szám: " . $inputs["TAJ_number"] . "<br>";
        } else {
            $errors["invalid_TAJ_number"] = "Hiba: Érvénytelen TAJ-szám.";
        }

        //Értesítési cím:
        if(trim($_POST["home_address"]) !== ''){
            $inputs["home_address"] = $_POST["home_address"];
            //echo "Értesítési cím: " . $inputs["home_address"] . "<br>";
        } else {
            $errors["missing_home_address"] = "Hiba: Nincs megadva értesítési cím.";
        }

        //E-mail cím:
        if(filter_var($_POST["email_address"], FILTER_VALIDATE_EMAIL)){
            if($user_storage->findOne(["email_address" => $_POST["email_address"]]) === NULL){      //Ezt még külön teszteld, ha már fel van töltve az adatbázis.
                $inputs["email_address"] = $_POST["email_address"];
                //echo "E-mail cím: " . $inputs["email_address"] . "<br>";
            } else {
                $errors["email_address_already_in_use"] = "Hiba: Ez az e-mail cím már foglalt.";
            }
        } else {
            $errors["invalid_email_address_format"] = "Hiba: A megadott e-mail cím formailag helytelen.";
        }

        //Jelszó és megerősítése:
        if(trim($_POST["password"]) !== "" && $_POST["confirm_password"] === $_POST["password"]){
            $inputs["password"] = $_POST["password"];
            //echo "Jelszó: " . $_POST["password"] . "<br>";
        } else if(trim($_POST["password"]) === ""){
            $errors["missing_password"] = "Hiba: Nem adtál meg jelszót.";
        } else if($_POST["confirm_password"] !== $_POST["password"]){
            $errors["passwords_dont_match"] = "Hiba: A két jelszó nem egyezik.";
        }

        //Új felhasználó felvétele az adatbázisba:
        if($errors === []){
            $new_user = [
                "fullname" => $inputs["fullname"],
                "TAJ_number" => $inputs["TAJ_number"],
                "home_address" => $inputs["home_address"],
                "email_address" => $inputs["email_address"],
                "password" => $inputs["password"],
            ];
            var_dump($new_user);
            if($new_user["email_address"] === "admin@nemkovid.hu"){
                $user_storage_handler->register_admin($new_user);
                header("Location: login.php");
                //echo "Sikeres admin regisztráció!<br>";
            } else {
                $user_storage_handler->register_user($new_user);
                header("Location: login.php");
                //echo "Sikeres user regisztráció!<br>";
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
    <h1>Regisztráció</h1>

    <form method="post" action="" novalidate>
        Teljes név:<input type="text" name="fullname" value="<?= $inputs["fullname"] ?? "" ?>">
        <?php if(isset($errors["missing_fullname"])): ?>
            <small><?=$errors["missing_fullname"]?></small>
        <?php endif ?><br>
        TAJ szám:<input type="text" name="TAJ_number" value=<?= $inputs["TAJ_number"] ?? "" ?>>
        <?php if(isset($errors["invalid_TAJ_number"])): ?>
            <small><?=$errors["invalid_TAJ_number"]?></small>
        <?php endif ?><br>
        Értesítési cím:<input type="text" name="home_address" value="<?= $inputs["home_address"] ?? "" ?>">
        <?php if(isset($errors["missing_home_address"])): ?>
            <small><?=$errors["missing_home_address"]?></small>
        <?php endif ?><br>
        E-mail:<input type="text" name="email_address" value="<?= $inputs["email_address"] ?? "" ?>">
        <?php if(isset($errors["invalid_email_address_format"])): ?>
            <small><?=$errors["invalid_email_address_format"]?></small>
        <?php endif ?>
        <?php if(isset($errors["email_address_already_in_use"])): ?>
            <small><?=$errors["email_address_already_in_use"]?></small>
        <?php endif ?><br>
        Jelszó:<input type="password" name="password">
        <?php if(isset($errors["missing_password"])): ?>
            <small><?=$errors["missing_password"]?></small>
        <?php endif ?><br>
        Jelszó megerősítése: <input type="password" name="confirm_password">
        <?php if(isset($errors["passwords_dont_match"])): ?>
            <small><?=$errors["passwords_dont_match"]?></small>
        <?php endif ?><br>
        <button type="submit">Regisztrál</button>
        <?php if(isset($errors["missing_inputs"])): ?>
            <small><?=$errors["missing_inputs"]?></small>
        <?php endif ?>
    </form>
    
</body>
</html>