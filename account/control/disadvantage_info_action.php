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
function checkaddslashes($str){        
    if(strpos(str_replace("\'",""," $str"),"'")!=false){
        return addslashes($str);
    } else{
        return $str;
    }
}
$title = '';
$malus_unit = '';
$cost = '';
$category_id = '';
$info_type = 'add_disadvantage';
$categories = getwayConnect::getwayData("SELECT * FROM disadvantages_categories");
if(isset($_GET['id'])){
    $information = getwayConnect::getwayData("SELECT * FROM `disadvantages_list` WHERE `id` = '" . $_GET['id'] . "'");
    $title = $information[0]['title'];
    $malus_unit = $information[0]['malus_unit'];
    $cost = $information[0]['cost'];
    $category_id = $information[0]['category_id'];
    $info_type = 'update_disadvantage';
}
if(isset($_POST['info_type'])){
    if($_POST['info_type'] == 'add_disadvantage'){
        $title = $_POST['title'];
        $malus_unit = $_POST['malus_unit'];
        $cost = $_POST['cost'];
        $category_id = $_POST['category_id'];
        getwayConnect::getwaySend("INSERT INTO disadvantages_list (title,malus_unit,cost,category_id) VALUES ('" . checkaddslashes($title) . "','" . $malus_unit . "','" . $cost . "','" . $category_id . "')");
        header("Location: /account/control/disadvantages_info.php");
    }
    else if ( $_POST['info_type'] == 'update_disadvantage'){
        $title = $_POST['title'];
        $malus_unit = $_POST['malus_unit'];
        $cost = $_POST['cost'];
        $category_id = $_POST['category_id'];
        getwayConnect::getwaySend("UPDATE disadvantages_list set title='" . checkaddslashes($title) . "', malus_unit='" . $malus_unit . "', cost = '" . $cost . "', category_id='" . $category_id . "' where id = '" . $_GET['id'] . "'");
       
        header("Location: /account/control/disadvantages_info.php");
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
    <title>Disadvantage</title>
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
            <form method='post'>
                <input type='hidden' name='info_type'  value="<?php echo $info_type?>">
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" class="form-control" value="<?php echo $title ?>" id="title" name="title" placeholder="Enter Title">
                </div>
                <div class="form-group">
                    <label for="malus_unit">Malus Unit</label>
                    <input type="number" class="form-control" value="<?php echo $malus_unit ?>" id="malus_unit" name="malus_unit" placeholder="Malus Unit">
                </div>
                <div class="form-group">
                    <label for="cost">Cost</label>
                    <input type="number" class="form-control" value="<?php echo $cost ?>" id="cost" name="cost" placeholder="Cost">
                </div>

                <div class="form-group">
                    <label for="category_id">Category</label>
                    <select class='form-control' id='category_id' name='category_id'>
                        <?php
                            foreach($categories as $category){
                                ?>
                                    <option <?php echo ($category_id == $category['id'])? 'selected':'' ?> value="<?php echo $category['id'] ?>">
                                        <?php
                                            echo $category['name'];
                                        ?>
                                    </option>
                                <?php
                            }
                        ?>
                    </select>
                </div>
                <input type='submit' class='btn btn-danger float-right'>
            </form>
        </div>
    </div>

<script src="https://code.jquery.com/jquery-latest.min.js"></script>
<script src="<?= $rootF ?>/template/bootstrap/js/bootstrap.min.js"></script>
</body>
</html>