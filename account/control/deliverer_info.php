<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 4/17/18
 * Time: 11:00 AM
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
$pageName = "Sku Codes";
$rootF = "../..";

include($rootF . "/apay/pay.api.php");
include($rootF . "/configuration.php");
include("../flower_orders/lang/language_am.php");

page::cmd();
$access = auth::checkUserAccess($secureKey);
$allData = array();
$buildClient = "";

if (!$access) {
    header("location:../../login");
}

$uid = $_COOKIE["suid"];
$level = auth::getUserLevel($uid);


$levelArray = explode(",", $level[0]["user_level"]);

$userData = auth::checkUserExistById($uid);
$cc = $userData[0]["lang"];
$user_country = $userData[0]["country_short"];

$regionData = page::getRegionFromCC($cc);
date_default_timezone_set("Asia/Yerevan");

$userData = $userData[0];

if (!(isset($userData["id"]) && (int)$userData["id"] > 0)) {
    header("location:../../login");
}
$deliverer_name = '';
$deliverer_full_name = '';
$deliverer_phone = '';
$deliverer_level = '';
$deliverer_ordering = '';
$deliverer_note = '';
$info_type = 'add_deliverer';
if(isset($_GET['deliverer_id'])){
    $deliverer_info = getwayConnect::getwayData("SELECT * FROM `delivery_deliverer` WHERE `id` = '" . $_GET['deliverer_id'] . "'");
    $deliverer_name = $deliverer_info[0]['name'];
    $deliverer_full_name = $deliverer_info[0]['full_name'];
    $deliverer_phone = $deliverer_info[0]['phone'];
    $deliverer_level = $deliverer_info[0]['level'];
    $deliverer_ordering = $deliverer_info[0]['ordering'];
    $deliverer_note = $deliverer_info[0]['note'];
    $info_type = 'update_deliverer';
}
if(isset($_POST['info_type'])){
    if($_POST['info_type'] == 'add_deliverer'){
        $name = $_POST['name'];
        $full_name = $_POST['full_name'];
        $phone = $_POST['phone'];
        $level = $_POST['level'];
        $ordering = $_POST['ordering'];
        $note = $_POST['note'];
        getwayConnect::getwaySend("INSERT INTO delivery_deliverer (name,full_name,phone,level,ordering,note) VALUES ('" . $name . "','" . $full_name . "','" . $phone . "','" . $level . "','" . $ordering . "','" . $note . "')");
        header("Location: /account/control/delivery_driver.php");
    }
    else if ( $_POST['info_type'] == 'update_deliverer'){
        $name = $_POST['name'];
        $full_name = $_POST['full_name'];
        $phone = $_POST['phone'];
        $level = $_POST['level'];
        $ordering = $_POST['ordering'];
        $note = $_POST['note'];
        getwayConnect::getwaySend("UPDATE delivery_deliverer set name='" . $name . "', note='" . $note . "', full_name = '" . $full_name . "', phone='" . $phone . "', level='" . $level . "', ordering='" . $ordering . "' where id = '" . $_GET['deliverer_id'] . "'");
        header("Location: /account/control/delivery_driver.php");
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="<?= $rootF ?>/template/account/sidebar.css">
    <link rel="stylesheet" href="<?= $rootF ?>/template/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= $rootF ?>/template/bootstrap/css/bootstrap-theme.min.css">
    <link rel="stylesheet" href="<?= $rootF ?>/template/datepicker/css/datepicker.css">
    <link rel="stylesheet" href="<?= $rootF ?>/template/rangedate/daterangepicker.css"/>
    <link rel="stylesheet" href="<?= $rootF ?>/template/DataTables/datatables.css"/>
    <link rel="stylesheet" href="../accountant/index_css.css">
    <title>Deliverer</title>
</head>
<body>
<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar"
                    aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#">RG-SYSTEM / <?= strtoupper($userData[0]["username"]); ?></a>
        </div>
        <div id="navbar" class="navbar-collapse collapse" aria-expanded="false">
            <ul class="nav navbar-nav">
                <?= page::buildMenu($level[0]["user_level"]) ?>
                <li class="dropdown" id="menuDrop">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                       aria-expanded="false"><?= (defined('FILTER')) ? FILTER : 'FILTER'; ?> <span class="caret"></span></a>
                    <ul class="dropdown-menu" role="menu" style="text-align:center;">
                        <?php
                        $fData = page::buildFilter($level[0]["user_level"], $pageName);
                        for ($fi = 0; $fi < count($fData); $fi++) {
                            echo "<li>{$fData[$fi][1]}</li>";
                        }
                        ?>
                    </ul>
                    <?php if (auth::roleExist(33)): ?>
                    <!--                    --><?php //if (max(page::filterLevel(3, $levelArray)) >= 33): ?>
                </li>
                <li><a href="order.php"
                       target="_blank"><?= (defined('ADD_NEW_ORDER')) ? ADD_NEW_ORDER : 'ADD_NEW_ORDER'; ?></a></li>
            <?php endif; ?>
                </li>
            </ul>
        </div>
    </div>
</nav>
<div class='row' style='margin-top:88px'>
    <div class='col-md-3 col-md-offset-4'>
        <form method='post'>
            <input type='hidden' name='info_type'  value="<?php echo $info_type?>">
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" class="form-control" value="<?php echo $deliverer_name ?>" id="name" name="name" placeholder="Enter Name">
            </div>
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" class="form-control" value="<?php echo $deliverer_full_name ?>" id="full_name" name="full_name" placeholder="Enter Full Name">
            </div>
            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="text" class="form-control" value="<?php echo $deliverer_phone ?>" id="phone" name="phone" placeholder="Enter Phone">
            </div>
            <div class="form-group">
                <label for="level">Level</label>
                <input type="text" class="form-control" value="<?php echo $deliverer_level ?>" id="level" name="level" placeholder="Enter Level">
            </div>
            <div class="form-group">
                <label for="ordering">Ordering</label>
                <input type="number" class="form-control" value="<?php echo $deliverer_ordering ?>" id="ordering" name="ordering" placeholder="Enter Ordering">
            </div>
            <div class="form-group">
                <label for="note">Note</label>
                <input type="text" class="form-control" value="<?php echo $deliverer_note ?>" id="note" name="note" placeholder="Enter Ordering">
            </div>
            <input type='submit' class='btn btn-danger'>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-latest.min.js"></script>
<script src="<?= $rootF ?>/template/bootstrap/js/bootstrap.min.js"></script>
</body>
</html>