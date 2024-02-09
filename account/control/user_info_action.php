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
$username = '';
$limit = '';
$user_level = '';
$lang = '';
$earnings_rate = '';
$full_name_en = '';
$full_name_ru = '';
$full_name_am = '';
$phone_number = '';
$password = '';
$info_type = 'add_user';
if(isset($_GET['user_id'])){
    $user_info = getwayConnect::getwayData("SELECT * FROM `user` WHERE `id` = '" . $_GET['user_id'] . "'");
    $username = $user_info[0]['username'];
    $limit = $user_info[0]['limit'];
    $user_level = $user_info[0]['user_level'];
    $lang = $user_info[0]['lang'];
    $earnings_rate = $user_info[0]['earnings_rate'];
    $full_name_en = $user_info[0]['full_name_en'];
    $full_name_ru = $user_info[0]['full_name_ru'];
    $full_name_am = $user_info[0]['full_name_am'];
    $phone_number = $user_info[0]['phone_number'];
    $info_type = 'update_user';
}
if(isset($_POST['info_type'])){
    if($_POST['info_type'] == 'add_user'){
        $uid = bin2hex(openssl_random_pseudo_bytes(10));
        $username = $_POST['username'];
        $limit = $_POST['limit'];
        $user_level = $_POST['user_level'];
        $lang = $_POST['lang'];
        $earnings_rate = $_POST['earnings_rate'];
        $full_name_en = $_POST['full_name_en'];
        $full_name_ru = $_POST['full_name_ru'];
        $full_name_am = $_POST['full_name_am'];
        $phone_number = $_POST['phone_number'];
        $password = auth::hash($_POST['password'],$secureKey);
        getwayConnect::getwaySend("INSERT INTO user (username,user.limit,user_level,lang,earnings_rate,full_name_en,full_name_ru,full_name_am,phone_number,password,uid) VALUES ('" . $username . "','" . $limit . "','" . $user_level . "','" . $lang . "','" . $earnings_rate . "','" . $full_name_en . "','" . $full_name_ru . "','" . $full_name_am . "','" . $phone_number . "','" . $password . "','" . $uid . "')");
        header("Location: /account/control/user_info.php");
    }
    else if ( $_POST['info_type'] == 'update_user'){
        $username = $_POST['username'];
        $limit = $_POST['limit'];
        $user_level = $_POST['user_level'];
        $lang = $_POST['lang'];
        $earnings_rate = $_POST['earnings_rate'];
        $full_name_en = $_POST['full_name_en'];
        $full_name_ru = $_POST['full_name_ru'];
        $full_name_am = $_POST['full_name_am'];
        $phone_number = $_POST['phone_number'];
        if(isset($_FILES['profile_img']['name'])){
            $path_parts = pathinfo($_FILES["profile_img"]["name"]);
            $extension = $path_parts['extension'];
            $target_dir = "../user_images/";
            $fileName = $user_info[0]['uid'].".".$extension;
            $target_file = $target_dir . $fileName;
            move_uploaded_file($_FILES["profile_img"]["tmp_name"], $target_file);
        }
        if(mb_strlen($_POST['password']) > 3){
            $password = auth::hash($_POST['password'],$secureKey);
            getwayConnect::getwaySend("UPDATE user set username='" . $username . "', user.limit='" . $limit . "', user_level = '" . $user_level . "', lang='" . $lang . "', earnings_rate='" . $earnings_rate . "', full_name_en='" . $full_name_en . "', full_name_ru='" . $full_name_ru . "', full_name_am='" . $full_name_am . "', password='" . $password . "', phone_number='" . $phone_number . "' where id = '" . $_GET['user_id'] . "'");
        }
        else{
            getwayConnect::getwaySend("UPDATE user set username='" . $username . "', user.limit='" . $limit . "', user_level = '" . $user_level . "', lang='" . $lang . "', earnings_rate='" . $earnings_rate . "', full_name_en='" . $full_name_en . "', full_name_ru='" . $full_name_ru . "', full_name_am='" . $full_name_am . "', phone_number='" . $phone_number . "' where id = '" . $_GET['user_id'] . "'");
        }
        header("Location: /account/control/user_info.php");
    }
}

?>
<!DOCTYPE html>
<html>
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
    <title>User Info</title>
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
        <div class='col-md-2 col-md-offset-5'>
            <form method='post' enctype="multipart/form-data">
                <input type='hidden' name='info_type'  value="<?php echo $info_type?>">
                <div class="form-group">
                    <label for="name">Username</label>
                    <input type="text" class="form-control" value="<?php echo $username ?>" id="username" name="username" placeholder="Enter Username">
                </div>
                <div class="form-group">
                    <label for="limit">Limit</label>
                    <input type="text" class="form-control" value="<?php echo $limit ?>" id="limit" name="limit" placeholder="Limit">
                </div>
                <div class="form-group">
                    <label for="user_level">User level</label>
                    <input type="text" class="form-control" value="<?php echo $user_level ?>" id="user_level" name="user_level" placeholder="Enter User Level">
                </div>
                <div class="form-group">
                    <label for="lang">Language</label>
                    <input type="text" class="form-control" value="<?php echo $lang ?>" id="lang" name="lang" placeholder="Enter Language">
                </div>
                <div class="form-group">
                    <label for="earnings_rate">Earnings Rate</label>
                    <input type="number" class="form-control" value="<?php echo $earnings_rate ?>" id="earnings_rate" name="earnings_rate" placeholder="Enter Rate">
                </div>
                <div class="form-group">
                    <label for="full_name_en">En Full Name</label>
                    <input type="text" class="form-control" value="<?php echo $full_name_en ?>" id="full_name_en" name="full_name_en" placeholder="Enter En Full Name">
                </div>
                <div class="form-group">
                    <label for="full_name_ru">Ru Full Name</label>
                    <input type="text" class="form-control" value="<?php echo $full_name_ru ?>" id="full_name_ru" name="full_name_ru" placeholder="Enter Ru Full Name">
                </div>
                <div class="form-group">
                    <label for="full_name_am">Am Full Name</label>
                    <input type="text" class="form-control" value="<?php echo $full_name_am ?>" id="full_name_am" name="full_name_am" placeholder="Enter Am Full Name">
                </div>
                <?php
                    if(isset($_GET['user_id'])){
                        ?>
                            <div class="form-group">
                                <label for="profile_img">Image</label>
                                <input type="file" name='profile_img' id="profile_img">
                                <img src="../user_images/<?php echo $user_info[0]['uid'] ?>.jpg" style='max-width:100px;margin:10px'>
                            </div>
                        <?php
                    }
                ?>
                <div class="form-group">
                    <label for="phone_number">Phone Number</label>
                    <input type="text" class="form-control" value="<?php echo $phone_number ?>" id="phone_number" name="phone_number" placeholder="Enter Phone Number">
                </div>
                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="text" class="form-control" id="password" name="password" placeholder="Enter Phone Number">
                </div>
                <input type='submit' class='btn btn-danger float-right'>
            </form>
        </div>
    </div>

<script src="https://code.jquery.com/jquery-latest.min.js"></script>
<script src="<?= $rootF ?>/template/bootstrap/js/bootstrap.min.js"></script>
</body>
</html>