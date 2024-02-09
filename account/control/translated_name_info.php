<?php
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
$first_name_arm = '';
$first_name_eng = '';
$first_name_rus = '';
$last_name_arm = '';
$last_name_rus = '';
$last_name_eng = '';
$updated_date = '';
$info_type = 'add_info';
if(isset($_GET['info_id'])){
    $translate_of_names = getwayConnect::getwayData("SELECT * FROM `translate_of_names` WHERE `id` = '" . $_GET['info_id'] . "'");
    $first_name_arm = $translate_of_names[0]['first_name_arm'];
    $first_name_eng = $translate_of_names[0]['first_name_eng'];
    $first_name_rus = $translate_of_names[0]['first_name_rus'];
    $last_name_arm = $translate_of_names[0]['last_name_arm'];
    $last_name_rus = $translate_of_names[0]['last_name_rus'];
    $last_name_eng = $translate_of_names[0]['last_name_eng'];
    $updated_date = date("Y-m-d");
    $info_type = 'update_info';
}
if(isset($_POST['info_type'])){
    if($_POST['info_type'] == 'add_info'){
        $first_name_arm = $_POST['first_name_arm'];
        $first_name_eng = $_POST['first_name_eng'];
        $first_name_rus = $_POST['first_name_rus'];
        $last_name_arm = $_POST['last_name_arm'];
        $last_name_rus = $_POST['last_name_rus'];
        $last_name_eng = $_POST['last_name_eng'];
        $updated_date = date("Y-m-d");
        getwayConnect::getwaySend("INSERT INTO translate_of_names (first_name_arm,first_name_eng,first_name_rus,last_name_arm,last_name_rus,last_name_eng,operator_id,updated_date) VALUES ('" . $first_name_arm . "','" . $first_name_eng . "','" . $first_name_rus . "','" . $last_name_arm . "','" . $last_name_rus . "','" . $last_name_eng . "','" . $userData['id'] . "','" . $updated_date . "')");
        header("Location: /account/control/translate_names_last_names.php");
    }
    else if ( $_POST['info_type'] == 'update_info'){
        $first_name_arm = $_POST['first_name_arm'];
        $first_name_eng = $_POST['first_name_eng'];
        $first_name_rus = $_POST['first_name_rus'];
        $last_name_arm = $_POST['last_name_arm'];
        $last_name_rus = $_POST['last_name_rus'];
        $last_name_eng = $_POST['last_name_eng'];
        $updated_date = date("Y-m-d");
        
        getwayConnect::getwaySend("UPDATE translate_of_names set first_name_arm='" . $first_name_arm . "', updated_date='" . $updated_date . "', first_name_eng = '" . $first_name_eng . "', first_name_rus = '" . $first_name_rus . "', last_name_arm='" . $last_name_arm . "', last_name_rus='" . $last_name_rus . "', last_name_eng='" . $last_name_eng . "', operator_id='" . $userData['id'] . "' where id = '" . $_GET['info_id'] . "'");
        header("Location: /account/control/translate_names_last_names.php");
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
    <title>Info</title>
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
                <label for="first_name_arm">First Name Arm</label>
                <input type="text" class="form-control" value="<?php echo $first_name_arm ?>" id="first_name_arm" name="first_name_arm" placeholder="Enter Name">
            </div>
            <div class="form-group">
                <label for="first_name_eng">First Name Eng</label>
                <input type="text" class="form-control" value="<?php echo $first_name_eng ?>" id="first_name_eng" name="first_name_eng" placeholder="Enter First Name Eng">
            </div>
            <div class="form-group">
                <label for="first_name_rus">First Name Rus</label>
                <input type="text" class="form-control" value="<?php echo $first_name_rus ?>" id="first_name_rus" name="first_name_rus" placeholder="Enter First Name Rus">
            </div>
            <div class="form-group">
                <label for="last_name_arm">Last Name Arm</label>
                <input type="text" class="form-control" value="<?php echo $last_name_arm ?>" id="last_name_arm" name="last_name_arm" placeholder="Enter Last Name Arm">
            </div>
            <div class="form-group">
                <label for="last_name_rus">Last Name Rus</label>
                <input type="text" class="form-control" value="<?php echo $last_name_rus ?>" id="last_name_rus" name="last_name_rus" placeholder="Enter Last Name Rus">
            </div>
            <div class="form-group">
                <label for="last_name_eng">Last Name Eng</label>
                <input type="text" class="form-control" value="<?php echo $last_name_eng ?>" id="last_name_eng" name="last_name_eng" placeholder="Enter Last Name Eng">
            </div>
            <input type='submit' class='btn btn-danger'>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-latest.min.js"></script>
<script src="<?= $rootF ?>/template/bootstrap/js/bootstrap.min.js"></script>
</body>
</html>