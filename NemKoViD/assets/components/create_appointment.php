<?php
require_once("../utils/verify_keys_exist.php");
require_once("../utils/Storage.php");

$appointment_storage = new Storage(new JsonIO("../json_databases/appointments.json"));

$inputs = [];
$errors = [];

if(count($_POST) > 0){
    if(verify_post("date", "time", "applicants_limit")){
        //Dátum:
        if(preg_match("/^20[0-9]{2}.(0[1-9]|1[0-2]).(0[1-9]|1[0-9]|2[0-9]|3[0-1]).$/i", $_POST["date"])){   //Bocs, szökévekre meg ilyenekre nem validál, csak az általános dátumformára.
            $inputs["date"] = $_POST["date"];
        } else {
            $errors["invalid_date_format"] = "Hiba: Érvénytelen dátumforma.";
        }

        //Időpont:
        if(preg_match("/^(0[0-9]|1[0-9]|2[0-3]):([0-5][0-9])$/i", $_POST["time"])){   //Bocs, szökévekre meg ilyenekre nem validál, csak az általános dátumformára.
            $inputs["time"] = $_POST["time"];
        } else {
            $errors["invalid_time_format"] = "Hiba: Érvénytelen időpontforma.";
        }

        //Férőhelyek száma:
        if($_POST["applicants_limit"] > 0){
            $inputs["applicants_limit"] = $_POST["applicants_limit"];
        } else {
            $errors["invalid_number"] = "Hiba: A megadott szám NULL, 0 vagy negatív.";
        }

        //Új időpont felvétele az adatbázisba:
        if($errors === []){
            $new_appointment = [
                "date" => $inputs["date"],
                "month" => (int)substr($inputs["date"], 6, 2),
                "time" => $inputs["time"],
                "applicants_count" => 0,
                "applicants" => [],
                "applicants_limit" => (int)$inputs["applicants_limit"]
            ];
            $appointment_storage->add($new_appointment);
            header("Location: ../../index.php");
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
    <h1>Új időpont meghirdetése</h1>

    <form method="post" action="" novalidate>
        Dátum (yyyy.mm.dd.):<input type="text" name="date" value="<?=$inputs["date"] ?? ""?>">
        <?php if(isset($errors["invalid_date_format"])): ?>
            <small><?=$errors["invalid_date_format"]?></small>
        <?php endif ?><br>
        Időpont (hh:mm):<input type="text" name="time" value="<?=$inputs["time"] ?? ""?>">
        <?php if(isset($errors["invalid_time_format"])): ?>
            <small><?=$errors["invalid_time_format"]?></small>
        <?php endif ?><br>
        Helyek száma:<input type="number" name="applicants_limit" value="<?=$inputs["applicants_limit"] ?? ""?>">
        <?php if(isset($errors["invalid_number"])): ?>
            <small><?=$errors["invalid_number"]?></small>
        <?php endif ?><br>
        <button type="submit">Meghirdet</button>
        <?php if(isset($errors["missing_inputs"])): ?>
            <small><?=$errors["missing_inputs"]?></small>
        <?php endif ?>
    </form>
</body>
</html>