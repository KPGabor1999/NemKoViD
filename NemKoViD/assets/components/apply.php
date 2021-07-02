<?php
session_start();
require_once("../utils/Storage.php");
require_once("../utils/Auth.php");

$user_storage = new Storage(new JsonIO("../json_databases/users.json"));
$user_storage_handler = new Auth($user_storage);
$appointment_storage = new Storage(new JsonIO("../json_databases/appointments.json"));
$current_appointment = $appointment_storage->findOne(["id" => $_GET["current_appointment"]]);

$inputs = [];
$errors = [];

if(count($_POST) > 0){
    if(isset($_POST["form_sent"])){
        if(isset($_POST["agreed"])){
            $current_appointment["applicants_count"]++;
            ($current_appointment["applicants"])[] = ($_SESSION["user"])["id"];
            $appointment_storage->update($current_appointment["id"], $current_appointment);
            ($_SESSION["user"])["appointment"] = $current_appointment["id"];
            $user_storage->update(($_SESSION["user"])["id"], $_SESSION["user"]);

            header("Location: application_successful.php");
        } else {
            $errors["not_agreed"] = "Hiba: Nem fogadtad el a jelentkezési feltételeket.";
        }
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
    <span>Dátum: <?=$current_appointment["date"]?></span></br>
    <span>Időpont: <?=$current_appointment["time"]?></span></br>
    <span>Név: <?=($_SESSION["user"])["fullname"]?></span></br>
    <span>Lakcím: <?=($_SESSION["user"])["home_address"]?></span></br>
    <span>TAJ szám: <?=($_SESSION["user"])["TAJ_number"]?></span></br>
    <br>

    <form method="post" action="" novalidate>
        <span>Tudomásul veszem, hogy a fent említett időpontban kötelező megjelennem, továbbá az oltásnak különféle mellékhatásai lehetnek.</span><br>
        <input type="checkbox" name="agreed" value="true">Megértettem<br>
        <input type="hidden" name="form_sent">
        <br>
        <button type="submit">Jelentkezés megerősítése</button>
        <?php if(isset($errors["not_agreed"])): ?>
            <small><?=$errors["not_agreed"]?></small>
        <?php endif ?>
    </form>

    <?php if($user_storage_handler->authorize(["admin"])): ?>
        <h2>Az időpontra eddig jelentkezett felhasználók:</h2>
        <?php foreach($current_appointment["applicants"] as $applicant_id): ?>
            <span><?=($user_storage->findOne(["id" => $applicant_id]))["fullname"]?>, <?=($user_storage->findOne(["id" => $applicant_id]))["TAJ_number"]?>, <?=($user_storage->findOne(["id" => $applicant_id]))["email_address"]?></span><br>
        <?php endforeach ?>    
    <?php endif ?>
</body>
</html>