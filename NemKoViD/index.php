<?php
require_once("assets/utils/Storage.php");
require_once("assets/utils/Auth.php");

session_start();

$current_user = ($_SESSION["user"])["id"] ?? NULL;
$user_storage = new Storage(new JsonIO("assets/json_databases/users.json"));
$user_storage_handler = new Auth($user_storage);

if($current_user !== NULL){
    $user_storage_handler->login($_SESSION["user"]);
}

echo "Jelenlegi felhasználó: " . ($current_user !== NULL ? $current_user : "*nincs bejelentkezve felhasználó*") . "<br><br>";

$appointment_storage = new Storage(new JsonIO("assets/json_databases/appointments.json"));
$months = ["Január", "Február", "Március", "Április", "Május", "Június", "Július", "Augusztus", "Szeptember", "Október", "November", "December"];

if(!isset($_SESSION["current_month"])){
    $_SESSION["current_month"] = 1;
}

if(count($_GET) > 0){
    if(isset($_GET["next_month"])){
        $next_month = $_SESSION["current_month"] + (int)$_GET["next_month"];
        if(1 <= $next_month && $next_month <= 12){
            $_SESSION["current_month"] = $next_month;
        }
    }
}

if(count($_POST) > 0){
    if(isset($_POST["cancel_appointment"])){
        //var_dump(($_SESSION["user"])["appointment"]);
        $cancelled_appointment = $appointment_storage->findOne(["id" => ($_SESSION["user"])["appointment"]]);
        //var_dump($cancelled_appointment);
        $cancelled_appointment["applicants_count"]--;
        if (($index = array_search(($_SESSION["user"])["id"], $cancelled_appointment["applicants"])) !== false) {
            unset($cancelled_appointment["applicants"][$index]);
        }
        $appointment_storage->update($cancelled_appointment["id"], $cancelled_appointment);
        ($_SESSION["user"])["appointment"] = "none";
        $user_storage->update(($_SESSION["user"])["id"], $_SESSION["user"]);
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
    .vacant{
        background-color: green;
    }
    .full{
        background-color: red;
    }
    .apply{
        background-color: white;
    }
    </style>
</head>
<body>
    <a href="assets/components/register.php">Regisztráció</a><br>
    <a href="assets/components/login.php">Bejelentkezés</a><br>
    <h1>A Nemzeti Koronavírus Depó (NemKoViD) Hivatalos oldala</h1>
    <p>Üdvözlünk a főoldalon! Itt időpontot foglalhatsz magadnak COVID-19 elleni védőoltásra! A 2021-re meghirdetett időpontok listáját az alábbi táblázatban láthatod:</p>

    <!--<?php echo "Current month: " . $_SESSION["current_month"] . "<br>" ?>-->
    <!--<?php echo "Current appointment: " . ($_SESSION["user"])["appointment"] . "<br>" ?>-->

    <?php if(isset($_SESSION["user"]) && ($_SESSION["user"])["appointment"] !== "none"): ?>
        <h2>A te időpontod: <?=($appointment_storage->findOne(["id" => ($_SESSION["user"])["appointment"]]))["date"]?> <?=($appointment_storage->findOne(["id" => ($_SESSION["user"])["appointment"]]))["time"]?></h2>
        <!--<button type="button" onclick="cancel_appointment()">Visszavonás</button>-->
        <form method="post" action="" novalidate>
            <input type="submit" name="cancel_appointment" value="Időpont lemondása">
        </form>
    <?php endif ?>

    <h2><?=$months[$_SESSION["current_month"]-1]?>i időpontok:</h2>
    <table>
    <?php if($appointment_storage->findAll(["month" => $_SESSION["current_month"]]) === []): ?>
        <span>Erre a hónapra nem hirdettek időpontokat. :(</span>
    <?php endif ?>
    <?php foreach($appointment_storage->findAll(["month" => $_SESSION["current_month"]]) as $appointment): ?>
        <tr class="<?= ($appointment["applicants_count"] < $appointment["applicants_limit"]) ? "vacant" : "full" ?>">
            <td><?=$appointment["date"]?></td><td><?=$appointment["time"]?></td><td><?=($appointment['applicants_count'] . "/" . $appointment['applicants_limit'])?> szabad hely.</td>
            <?php if($appointment["applicants_count"] < $appointment["applicants_limit"] && (!isset($_SESSION["user"]) || isset($_SESSION["user"]) && ($_SESSION["user"])["appointment"] === "none")): ?>
                <td class="apply"><a href=<?= isset($_SESSION["user"]) ? ("assets/components/apply.php?current_appointment=" . $appointment['id']) : "assets/components/login.php" ?>>Jelentkezés</a></td>
            <?php endif ?>
        </tr>
    <?php endforeach ?>
    </table>

    <br>
    <a href="index.php?next_month=-1"><img width="24px" height="24px" src="assets/images/left_arrow.png"></a><span style="font-size: 32px"> Hónap </span><a href="index.php?next_month=1"><img width="24px" height="24px" src="assets/images/right_arrow.png"></a><br>
    <br>
    <?php if($user_storage_handler->authorize(["admin"])): ?>
        <a href="assets/components/create_appointment.php">Új időpont meghirdetése</a><br>
    <?php endif ?>

    <!--Ha nem vagy bejelentkezve, jelentkezéskor a bejelentkezés oldalra vigyen.-->
</body>
</html>