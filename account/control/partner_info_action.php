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
$name = '';
$level_u = '';
$company_name = '';
$phone = '';
$email = '';
$hvhh_number = '';
$working_terms = '';
$info_type = 'add_partner';
if(isset($_GET['partner_id'])){
    $partner_info = getwayConnect::getwayData("SELECT * FROM `delivery_sellpoint` WHERE `id` = '" . $_GET['partner_id'] . "'");
    $name = $partner_info[0]['name'];
    $level_u = $partner_info[0]['level'];
    $company_name = $partner_info[0]['company_name'];
    $phone = $partner_info[0]['phone'];
    $email = $partner_info[0]['email'];
    $hvhh_number = $partner_info[0]['hvhh_number'];
    $working_terms = $partner_info[0]['working_terms'];
    $info_type = 'update_partner';
}
if(isset($_POST['info_type'])){
    if($_POST['info_type'] == 'add_partner'){
        $uid = bin2hex(openssl_random_pseudo_bytes(10));
        $name = $_POST['name'];
        $level_u = $_POST['level'];
        $company_name = $_POST['company_name'];
        $phone = $_POST['phone'];
        $email = $_POST['email'];
        $hvhh_number = $_POST['hvhh_number'];
        $working_terms = $_POST['working_terms'];
        getwayConnect::getwaySend("INSERT INTO delivery_sellpoint (name,level,company_name,phone,email,hvhh_number,working_terms) VALUES ('" . $name . "','" . $level_u . "','" . $company_name . "','" . $phone . "','" . $email . "','" . $hvhh_number . "','" . $working_terms . "')");
        header("Location: /account/control/partner_info.php");
    }
    else if ( $_POST['info_type'] == 'update_partner'){
        $name = $_POST['name'];
        $level_u = $_POST['level'];
        $company_name = $_POST['company_name'];
        $phone = $_POST['phone'];
        $email = $_POST['email'];
        $hvhh_number = $_POST['hvhh_number'];
        $working_terms = $_POST['working_terms'];
        getwayConnect::getwaySend("UPDATE delivery_sellpoint set name='" . $name . "', level='" . $level_u . "', company_name = '" . $company_name . "', phone='" . $phone . "', email='" . $email . "', hvhh_number='" . $hvhh_number . "', working_terms='" . $working_terms . "' where id = '" . $_GET['partner_id'] . "'");
        header("Location: /account/control/partner_info.php");
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
    <title>s</title>
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
        <div class='col-md-6 col-md-offset-3'>
            <form method='post'>
                <input type='hidden' name='info_type'  value="<?php echo $info_type?>">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" class="form-control" value="<?php echo $name ?>" id="name" name="name" placeholder="Enter Name">
                </div>
                <div class="form-group">
                    <label for="level">Level</label>
                    <input type="text" class="form-control" value="<?php echo $level_u ?>" id="level" name="level" placeholder="Enter User Level">
                </div>
                <div class="form-group">
                    <label for="company_name">Company Name</label>
                    <input type="text" class="form-control" value="<?php echo $company_name ?>" id="company_name" name="company_name" placeholder="Enter Company Name">
                </div>
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="number" class="form-control" value="<?php echo $phone ?>" id="phone" name="phone" placeholder="Enter Phone">
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="text" class="form-control" value="<?php echo $email ?>" id="email" name="email" placeholder="Enter Email">
                </div>
                <div class="form-group">
                    <label for="hvhh_number">ՀՎՀՀ համար</label>
                    <input type="text" class="form-control" value="<?php echo $hvhh_number ?>" id="hvhh_number" name="hvhh_number" placeholder="Enter ՀՎՀՀ համար">
                </div>
                <div class="form-group">
                    <label for="working_terms">Working Terms</label>
					<textarea class="form-control mt-3 addPostTextarea" id='working_terms' name='working_terms' placeholder='Working Terms'><?php echo $working_terms?></textarea>
                </div>
                <input type='submit' class='btn btn-danger float-right'>
            </form>
        </div>
    </div>
<script type="text/javascript" src='/info/js/htmlEditor.js'></script>
<script src="/info/js/tinymce/tinymce.min.js" referrerpolicy="origin"></script>
<script src="https://code.jquery.com/jquery-latest.min.js"></script>
<script src="<?= $rootF ?>/template/bootstrap/js/bootstrap.min.js"></script>
<script type="text/javascript">
	tinymce.init({
	    selector: '#working_terms',
	  });
</script>
</body>
</html>