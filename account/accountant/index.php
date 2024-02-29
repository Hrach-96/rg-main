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
$pageName = "accountant";
$rootF = "../..";

include($rootF . "/apay/pay.api.php");
include($rootF . "/configuration.php");
include("../flower_orders/lang/language_am.php");
include("AccountingAssets.php");

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
//ddd(auth::roleExist(16));
//dde($levelArray);

$userData = auth::checkUserExistById($uid);
$cc = $userData[0]["lang"];
$user_country = $userData[0]["country_short"];


$strict_country = ($user_country > 0) ? 'AND `delivery_region` = 4 ' : '';
$root = true;

$get_lvl = explode(',', $level[0]["user_level"]);

$regionData = page::getRegionFromCC($cc);
date_default_timezone_set("Asia/Yerevan");

function getConstant($value)
{
    if (defined($value)) {
        return constant($value);
    } else {
        return $value;
    }
}

$userData = $userData[0];

//
//if (in_array (19  , $levelArray)) {
//    include_once 'UserLevel19.php';
//} else if (in_array ( 18  , $levelArray)) {
//     include_once 'UserLevel18.php';
//} else if (in_array ( 17  , $levelArray)) {
//    include_once 'UserLevel17.php';
//}

if (!(isset($userData["id"]) && (int)$userData["id"] > 0)) {
    header("location:../../login");
}
if (auth::roleExist(18)) {
    $userListArray = Accounting::getUsersList();
} else {
    $userListArray[$userData['id']] = $userData['username'];
}
//dde($userListArray);
$currencyListArray = Accounting::getCurrenciesList();
$defaultDate = date("Y-m-d");

$filterArray = array();
$filterprice = "";
$filtertarget = "";
$userid = $userData['id'];


if (isset($_POST["posting"]) && $_POST["posting"] == "filter") {

    if (isset($_POST["price"])) {
        $filterArray["price"] = $filterprice = $_POST["price"];
    }

    if (isset($_POST["target"])) {
        $filtertarget = $filterArray["target"] = $_POST["target"];
    }

    if (isset($_POST["actiontype"])) {
        $filterArray["actiontype"] = $_POST["actiontype"];
    }

    if (isset($_POST["users"]) && auth::roleExist(18)) {
        $userid = $filterArray["users"] = $_POST["users"];
    } else {
        $filterArray["users"] = $userData['id'];
    }

    if (isset($_POST["currencies"])) {
        $filterArray["currencies"] = $_POST["currencies"];
    }

    if (isset($_POST["date_start"]) && $_POST["date_start"]) {
        $oDate = new DateTime($_POST["date_start"]);
        $sqlDate = $oDate->format("Y-m-d");
        $filterArray["date_start"] = $sqlDate;
    }

    if (isset($_POST["date_end"]) && $_POST["date_end"]) {
        $oDate = new DateTime($_POST["date_end"]);
        $sqlDate = $oDate->format("Y-m-d");
        $filterArray["date_end"] = $sqlDate;
    }
}


$actiontype = 0;

$arrayObjects = Accounting::getAllObjectsAccountant($defaultDate, $actiontype, $filterArray,$userData);


$query_result = getwayConnect::$db->query("SELECT SUM(`price`) as total_price,`currency` FROM `accounting` WHERE `userid`=" . $userid . " AND `status_archive`=0 GROUP BY `currency`");

$total_result = [];
foreach ($query_result as $row) {
    $total_result[$row['currency']] = $row;
}

function getUserPosition($userInfo){
    $pos = Array();
    if(strpos($userInfo['user_level'], '36') !== false){
        $pos[] = 'operators';
    }
    if(strpos($userInfo['user_level'], '99') !== false){
        $pos[] = 'operators';
        $pos[] = 'flourist';
        $pos[] = 'driver';
        $pos[] = 'hotel';
    }
    if(strpos($userInfo['user_level'], '30') !== false){
        $pos[] = 'flourist';
    }
    if(strpos($userInfo['user_level'], '40') !== false){
        $pos[] = 'driver';
    }
    if(strpos($userInfo['user_level'], '18') !== false){
        $pos[] = 'hotel';
    }
    return $pos;
}
if(isset($_REQUEST['getUnreadPosts']) && $_REQUEST['getUnreadPosts']){
    $user_id = $userid;
    $userPosition = getUserPosition($userData);
    $userPositionSql = '(';
    foreach($userPosition as $key => $position){
        if($key == 0){
            $userPositionSql.= $position . " = 1 ";
        }
        else{
            $userPositionSql.= ' or ' . $position . " = 1 ";
        }
    }
    $userPositionSql.= ")" ;
    $sql = "SELECT * FROM info_posts where " . $userPositionSql . " and user_id <> " . $user_id . " and deleted_date = '0000-00-00 00:00:00'";
    $postCounts = getwayConnect::getwayData($sql);
    $unreadCount = 0;
    foreach($postCounts as $value){
        $sqlCheckView = "SELECT * FROM info_post_view where user_id = '" . $user_id ."' and post_id = '" . $value['id'] . "'" ;
        $sqlCheckViewRow = getwayConnect::getwayData($sqlCheckView);
        if(!$sqlCheckViewRow){
            $unreadCount++;
        }
    }
    echo json_encode($unreadCount);die;
}
$disadvantage_categories = getwayConnect::getwayData("SELECT * FROM `disadvantages_categories`");
if(isset($_REQUEST['get_disadvantage_users']) && $_REQUEST['get_disadvantage_users']){
    $users_ids_array = explode(',',$_POST['users_ids']);
    $result = Array();
    foreach($users_ids_array as $user_id){
        $userInfo = getwayConnect::getwayData("SELECT * FROM user WHERE id = " . $user_id)[0];
        $result[]= $userInfo;
    }
    print json_encode($result);die;
}
if(isset($_REQUEST['insertDisadvantage']) && $_REQUEST['insertDisadvantage']){
    $fileName = '';
    if(isset($_FILES['file']['name'])){
        $path_parts = pathinfo($_FILES["file"]["name"]);
        $extension = $path_parts['extension'];
        $target_dir = "../../disadvantage_files/";
        $fileName = md5(date('Y-m-d H:i:s')).".".$extension;
        $target_file = $target_dir . $fileName;
        move_uploaded_file($_FILES["file"]["tmp_name"], $target_file);
    }
    $user_id = $_POST['user_id'];
    $list_id = $_POST['list_id'];
    $disadvantageListInfo = getwayConnect::getwayData("SELECT * FROM disadvantages_list WHERE id = " . $list_id);
    $description = $_POST['description'];
    getwayConnect::getwaySend("INSERT INTO disadvantage_users_by_order (user_id, order_id,description,d_list_id,added_by_user_id,created_date,file_path) VALUES ('{$user_id}' , '1' ,'{$description}','{$list_id}','{$userData['id']}','" . date('Y-m-d H:i:s') . "','{$fileName}')");
    return true;die;
}
if(isset($_REQUEST['get_disadvantage_list']) && $_REQUEST['get_disadvantage_list']){
    $user_id = $_REQUEST['user_id'];
    $userInfo = getwayConnect::getwayData("SELECT * FROM user WHERE id = " . $user_id)[0];
    $user_id = $userInfo['id'];
    $result = Array();
    $disadvantage = getwayConnect::getwayData("SELECT * FROM disadvantages_categories WHERE user_level like '%" . $user_id."%'");
    if($disadvantage){
        $result[] = $disadvantage[0]['id'];
    }
    $return_result = Array();
    foreach($result as $value){
        $res = getwayConnect::getwayData("SELECT * FROM disadvantages_list WHERE category_id = " . $value);
        foreach($res as $r){
            $return_result[] = $r;
        }
    }
    print json_encode($return_result);die;
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
    <link rel="stylesheet" href="index_css.css">


    <title>Accounting</title>
    <style type="text/css">
        .highlight {
            background-color: yellow;
            color: black;
            font-size: 12px;
        }

        @media print {
            .hidden-print {
                display: none !important;
            }

            .article .text.short {
                height: 100%;
                overflow: auto;
            }
        }
        .d-none{
            display:none!important;
        }
        .cursorPointer:hover{
            cursor:pointer;
        }
    </style>
</head>
<body>
<!--<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <a class="navbar-brand" href="#"><? /* //= strtoupper($userData[0]["username"]); */ ?></a>
        </div>
        <div id="navbar" class="navbar-collapse collapse" aria-expanded="false">
            <ul class="nav navbar-nav">
                <? /*= page::buildMenu($level[0]["user_level"]) */ ?>
                <li class="dropdown" id="menuDrop">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                       aria-expanded="false">
                        <? /*= (defined('FILTER')) ? FILTER : 'FILTER'; */ ?>
                        <span class="caret"></span></a>
                    <ul class="dropdown-menu" role="menu" style="text-align:center;">
                        <?php
/*                        $fData = page::buildFilter($level[0]["user_level"], $pageName);
                        for ($fi = 0; $fi < count($fData); $fi++) {
                            echo "<li>{$fData[$fi][1]}</li>";
                        }
                        */ ?>
                    </ul>
                </li>
                <?php /*if (max(page::filterLevel(3, $levelArray)) >= 33): */ ?>
                    <li>
                        <a href="order.php"
                           target="_blank">
                            <? /*= (defined('ADD_NEW_ORDER')) ? ADD_NEW_ORDER : 'ADD_NEW_ORDER'; */ ?>
                        </a>
                    </li>
                <?php /*endif; */ ?>

            </ul>
        </div>
    </div>
</nav>-->
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
            <a class="navbar-brand" href="#">RG-SYSTEM / <?php echo strtoupper($userData["username"]); ?></a>
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

<div class="header-space"></div>
<div class="toggle_filter">
    Filter
    <span class="glyphicon glyphicon-chevron-down filter_open" aria-hidden="true"></span>
    <span class="glyphicon glyphicon-chevron-up filter_close" aria-hidden="true"></span>
</div>
<div class="head-filter-par row">
    <div class="head-filter hide_filter col-md-12 col-lg-7">
        <form method="POST" action="">

            <input type="hidden" name="posting" value="filter">
            <div class="row">
                <div class="col-xs-12">
                    <!--            <div class="col-xs-12 col-sm-6 col-md-2">-->
                    <input type="text" name="target" placeholder="Նպատակ" class="col-xs-12 col-sm-12 col-md-2"
                           value="<?= $filtertarget ?>">
                    <!--            </div>-->
                    <!--            <div class="col-xs-12 col-sm-6 col-md-2">-->
                    <input type="text" name="price" placeholder="Գին" value="<?= $filterprice ?>"
                           class="col-xs-12 col-sm-6 col-md-2 input">
                    <!--            </div>-->

                    <select name="actiontype" class="col-xs-12 col-sm-6 col-md-2">

                        <option value="0">Ստատուս</option>
                        <option value="1"<?php if (isset($_POST["actiontype"]) && $_POST["actiontype"] == 1) echo "selected" ?>>
                            Մուտք
                        </option>
                        <option value="2" <?php if (isset($_POST["actiontype"]) && $_POST["actiontype"] == 2) echo "selected" ?>>
                            Ելք
                        </option>
                        <?php if (auth::roleExist(19)) { ?>
                            <option value="3" <?php if (isset($_POST["actiontype"]) && $_POST["actiontype"] == 3) echo "selected" ?>>
                                Արխիվ
                            </option>
                        <?php } ?>
                    </select>


                    <select name="users" class="col-xs-12 col-sm-6 col-md-2">
                        <?php if (auth::roleExist(18)) { ?>
                            <option value="0">Օգտագործողները</option>
                        <?php } ?>
                        <?php


                        foreach ($userListArray as $key => $value) {
                            if (isset($_POST["users"]) && $_POST["users"] == $key) {
                                ?>
                                <option value="<?= $key ?>" selected><?= $value ?></option>
                                <?php
                            } else {
                                ?>
                                <option value="<?= $key ?>"><?= $value ?></option>
                                <?php
                            }

                        }
                        ?>
                    </select>

                    <select name="currencies" class="col-xs-12 col-sm-6 col-md-1">
                        <?php
                        if (in_array(16, $levelArray)) {
                            ?>
                            <option value="AMD">AMD</option>
                            <?php
                        } else {
                            ?>
                            <option value="0" selected>ALL</option>
                            <?php

                            foreach ($currencyListArray as $key => $value) {
                                if (isset($_POST["currencies"]) && $_POST["currencies"] == $value) {
                                    ?>
                                    <option value="<?= $value ?>" selected><?= $value ?></option>
                                    <?php

                                } else {
                                    ?>
                                    <option value="<?= $value ?>"><?= $value ?></option>
                                    <?php
                                }
                            }
                        }
                        ?>
                    </select>
                    <!--            </div>-->
                    <!--            <div class="col-xs-12 col-sm-6 col-md-2">-->
                    <!--                <input name="date" type="date" value="-->
                    <? //= $defaultDate ?><!--" class="col-xs-12 col-sm-6 col-md-2">-->
                    <input type="text" name="date_start"
                           value="<?= isset($_POST["date_start"]) ? $_POST["date_start"] : '' ?>"
                           class="col-xs-12 col-sm-6 col-md-1 start_date"
                           placeholder="Start date">
                    <input type="text" name="date_end"
                           value="<?= isset($_POST["date_end"]) ? $_POST["date_end"] : '' ?>"
                           class="col-xs-12 col-sm-6 col-md-1 end_date"
                           placeholder="End date">
                    <!--            </div>-->
                    <!--            <div class="col-xs-12 col-sm-12 col-md-1">-->
                    <input type="submit" class="col-xs-12 col-sm-12 col-md-1" value="Որոնել">
                    <!--            </div>-->
                </div>
            </div>
            <div class="clearfix"></div>

        </form>
    </div>
    <div class="row">
        <div class="col-md-12 col-lg-5 ">
            <div class="input-output col-xs-12 col-sm-7 col-md-7">
                <!--                    <div class="row">-->
                <label id="radiobut1" class="form-control"
                       style="background-color: #d8d8d8;color: darkred;width:75%"
                       onclick="setAction (1)">
                    <input type="radio" id="radio1" name="acvtiontype" checked
                           onclick="setAction (1)">Գումարի Մուտք</label>

                <label id="radiobut2" class="form-control" style="width:16%;color:#fff"
                       onclick="setAction (2)">
                    <input type="radio" id="radio2" name="acvtiontype" onclick="setAction (2)">Գումարի Ելք</label>
                <!--                    </div>-->
            </div>

            <div class="col-xs-12 col-sm-5 col-md-5 user-name"
                 id="usernameid">
                 <a href="../../info/index.php" target="_blank">
                    <img src="../flower_orders/ico/important-announcement.jpg"  alt="Important Announcement" class="peopleIcon" width="80" style="max-width: 100px;">
                </a>
                 <?php
                    $types = ['jpeg', 'png', 'JPEG', 'jpg'];
                    foreach($types as $type){
                        if(file_exists('../user_images/' . $userData['uid']. ".". $type)){
                        ?>
                            <img src="<?= '../user_images/' . $userData['uid']. '.'. $type ?>" alt="" width="50" height="50">
                        <?php
                        }
                    }
                 ?>
                 <?php print_r($userData["username"]); ?></div>
        </div>
    </div>

    <!--    <div style="clear: both"></div>-->
</div>
<div class='row'>
    <div class='col-md-1'>
        <button type="button" class="btn btn-default" onclick="openMail(50408, 9)">Հերթափոխի Հանձնում</button>
    </div>
    <div class="col-md-11">
        <div class='col-md-1'>
            <img class='add_disadvantage cursorPointer' style='margin-left:50px;' id='add_disadvantage' src="../../template/icons/bonus/1.png">
        </div>
        <div class='col-md-2'>
            <select class='form-control disadvantageCategory d-none'>
                <option value=''>Team</option>
                <?php
                    foreach($disadvantage_categories as $category){
                        ?>
                            <option value="<?php echo $category['user_level'] ?>"><?php echo $category['name'] ?></option>
                        <?php
                    }
                ?>
            </select>
        </div>
        <div class='col-md-2'>
            <select class='form-control disadvantage_user_select d-none'>
            </select>
        </div>
        <div class='col-md-2'>
            <div class='disadvantage_list d-none'>
                <select class='form-control disatvantage_list_select'></select>
            </div>
        </div>
        <div class='col-md-5'>
            <div class='d-none disadvantage_description_div'>
                <textarea class='form-control disadvantage_description' row='3' style='width:40%;float:left' placeholder="Այլ մանրամասներ"></textarea>
                <input type='file' title='Կցել Ֆայլ' id='disadvantage_file' >
                <button type='button' style='margin-bottom: 10px;float:right' class='btn btn-primary addDisadvantageToDB'>Ավելացնել</button>
            </div>
        </div>
        
    </div>
</div>
<div class=" col-xs-12 par_data_table margin-top">
    <a href="/info/" target='_blank' class='unreadPosts'></a>
    <table id="data_table" class="table table-bordered table-hover">

        <thead>
        <tr>
            <th>
                #
            </th>
            <th>
                Նպատակ
            </th>
            <th>
                Քանակ
            </th>
            <th>
                Գումար
            </th>
            <th>
                Արտաժույթ
            </th>
            <th>
                Գործողություն
            </th>
            <th>
                Ամսաթիվ
            </th>
            <th>
                Օգտագործող
            </th>
            <?php if (auth::roleExist(19)) { ?>

                <th>
                    Գործողություն
                </th>
            <?php } ?>
        </tr>
        </thead>
        <tbody>
        <?php

        foreach ($arrayObjects as $key => $value) {
            ?>

            <tr <?php if ($value["actiontype"] == 1) {
                echo 'style="background-color: #f1f8e9;"';
            } ?> >
                <td attr="calckeys">
                    <?= $key ?>
                </td>
                <td>
                    <?= $value["purpose"] ?>
                </td>
                <td>
                    <?= $value["quantity"] ?>
                </td>
                <td attr="price_<?= $key ?>">
                    <?= numberFormat($value["price"]) ?>
                </td>
                <td attr="currency_<?= $key ?>">
                    <?= $value["currency"] ?>
                </td>
                <td>
                    <?php
                    if ($value["actiontype"] == 1) {
                        echo "Մուտք";
                    } else if ($value["actiontype"] == 2) {
                        echo "Ելք";
                    }

                    ?>
                </td>
                <td>
                    <?= Accounting::getCorrectedDate( date("Y-m-d H:i:s", strtotime('+4 hours', strtotime($value["cdate"]))) ) ?>
                </td>
                <td>
                    <?= $value["username"] . "  (".$value['balance'].") "?>
                </td>
                <?php if (auth::roleExist(19)) { ?>
                    <td>
                        <a href="javascript:void(0)" class="edit_item" title="Փոփոխել">
                            <i class="glyphicon glyphicon-edit"></i>
                        </a>

                        <a href="javascript:void(0)" class="save_item" title="Պահպանել" style="display: none">
                            <i class="glyphicon glyphicon-floppy-save"></i>
                        </a>

                        <a href="javascript:void(0)" class="close_edit_item" title="Փակել" style="display: none">
                            <i class="glyphicon glyphicon-remove"></i>
                        </a>
                        <a href="javascript:void(0)" class="archive_item" title="Արխիվացնել">
                            <i class="glyphicon glyphicon-inbox"></i>
                        </a>

                    </td>
                <?php } ?>
            </tr>


        <?php } ?>
        </tbody>

    </table>
</div>
<hr style=" visibility: hidden">
<hr style=" visibility: hidden">
<hr style=" visibility: hidden">
<hr style=" visibility: hidden">

<div style="bottom:0; width: 100%">

    <table style="width: 100%" class="table table-bordered table-hover">
        <tr>
            <td>
                <span id="GBPTOTAL"></span> /
                <span><?php echo isset($total_result['GBP']) ? $total_result['GBP']['total_price'] : '0' ?> GBP </span>
            </td>
            <td>
                <span id="EURTOTAL"></span> /
                <span><?php echo isset($total_result['EUR']) ? $total_result['EUR']['total_price'] : '0' ?> EUR </span>
            </td>
            <td>
                <span id="RUBTOTAL"></span> /
                <span><?php echo isset($total_result['RUB']) ? $total_result['RUB']['total_price'] : '0' ?> RUB </span>
            </td>
            <td>
                <span id="AMDTOTAL"></span> /
                <span><?php echo isset($total_result['AMD']) ? $total_result['AMD']['total_price'] : '0' ?> AMD </span>
            </td>
            <td>
                <span id="USDTOTAL"></span> /
                <span><?php echo isset($total_result['USD']) ? $total_result['USD']['total_price'] : '0' ?> USD </span>
            </td>
            <td>
                <span id="IRRTOTAL"></span> /
                <span><?php echo isset($total_result['IRR']) ? $total_result['IRR']['total_price'] : '0' ?> IRR </span>
            </td>
        </tr>
    </table>

</div>
<div style="text-align: center" class="form-group margin-top">
    <div id="add-info-table">
        <div class="col-xs-12">

            <div class="col-xs-12 col-sm-6 col-md-2 col-lg-1 form-group">
                <input type="text" id="price" name="price" style=" height: 50px"
                       class="form-control required validate_number_float"
                       placeholder="Գումար">
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-8 form-group">
                <input type="text" id="target" name="target" style=" height: 50px"
                       class="form-control check_length required" data-minlength="4"
                       placeholder="Նպատակ">
            </div>
            <div class="col-xs-12 col-sm-6 col-md-2 col-lg-1 form-group">
                <input type="text" id="quantity" name="quantity" style=" height: 50px"
                       class="form-control validate_number_float"
                       placeholder="Քանակ">
            </div>

            <div class="col-xs-12 col-sm-6 col-md-2 col-lg-1 form-group">
                <select id="selectedcurrency" name="selectedcurrency" class="form-control" style="height: 50px">
                    <?php
                    if (in_array(16, $levelArray)) {
                        ?>
                        <option class="currency_AMD" value="AMD">AMD</option>
                        <?php
                    } else {
                        foreach ($currencyListArray as $key => $value) {

                            ?>
                            <option class="currency_<?= $value ?>" value="<?= $value ?>"><?= $value ?></option>
                            <?php
                        }
                    }
                    ?>
                </select>
            </div>


            <div class="col-xs-12 col-sm-12 col-md-2 col-lg-1 form-group">
                <input type="submit" id="save" class="btn btn-lg btn-primary center-block" style="height: 50px"
                       value="Մուտք" onclick="post_new_value ()">
            </div>


        </div>
    </div>

</div>

<script src="https://code.jquery.com/jquery-latest.min.js"></script>
<script src="<?= $rootF ?>/template/bootstrap/js/bootstrap.min.js"></script>

<script src="<?= $rootF ?>/template/DataTables/datatables.js"></script>
<script src="<?= $rootF ?>/template/rangedate/moment.min.js"></script>
<script src="<?= $rootF ?>/template/datepicker/js/bootstrap-datepicker.js"></script>
<script src="<?= $rootF ?>/js/validator.js"></script>

<script>
        function openMail(id, type){
            window.open("../flower_orders/mail/?mails="+id+"&content_id="+type, "", "toolbar=yes, scrollbars=yes, resizable=yes,width=1300, height=800");
        }
    $(document).ready(function () {
        $('#data_table').DataTable({
            // "autoWidth": true,
            // "scrollX": true
            // "sScrollX": '100%'


        });
        $('#data_table')
            .on( 'order.dt',  function () {
                console.log('Order');
                setTimeout(function (){
                    calculatePrice(0, null)
                }, 500);
            })
            .on( 'search.dt', function () {
                console.log('Search');
                setTimeout(function (){
                    calculatePrice(0, null)
                }, 500);
            })
            .on( 'page.dt',   function () {
                console.log('Page');
                setTimeout(function (){
                    calculatePrice(0, null)
                }, 500);
            })
            .on( 'length.dt', function ( e, settings, len ) {
                console.log( 'New page length: '+len );
                setTimeout(function (){
                    calculatePrice(-1, null)
                }, 500);
            });

        $('.start_date').datepicker()
        $('.end_date').datepicker()

    });

    function numberFormating(n) {
        n = Number(n);
        var value = n.toLocaleString(
            {minimumFractionDigits: 0}
        );
        return value
    }

    function numberReFormating(n) {

        var value = n.replace(/,/g, '');
        return Number(value)
    }
    setTimeout(function(){
        $.ajax({
            type: 'post',
            url: location.href,
            data: {
                getUnreadPosts: true
            },
            success: function(resp){
                if(resp >= 1){
                    $(".unreadPosts").html("<marquee style='background:red;color:#fff;width:50%;'>Հարգելի <?php echo $userData['full_name_am'] ?> դուք ունեք " + resp + " չկարդացաց կարևոր հայտարարություն, որ հարկավոր է կարդալ և հաստատել․</marquee><script>setTimeout(function(){window.location.href = '/info/malus.php';},900000);")
                }
            }
        })
    }, 3000)

    function post_new_value() {

        var selectedActionType = 0;

        if ($('#radio1:checked').val() == "on") {
            selectedActionType = 1;
        }

        if ($('#radio2:checked').val() == "on") {
            selectedActionType = 2;
        }

        var selectedcurrency = $("#selectedcurrency").val();


        var doPost = false;

        // if (!jQuery.isNumeric(quantity)) {
        //     doPost = false;
        //     $("#quantity").val("");
        //     $("#quantity").css({"background-color": 'red'});
        // } else {
        //     $("#quantity").css({"background-color": 'white'});
        // }

        // if (!jQuery.isNumeric(price)) {
        //     doPost = false;
        //     $("#price").val("");
        //     $("#price").css({"background-color": 'red'});
        // } else {
        //     $("#price").css({"background-color": 'white'});
        //
        // }
        //
        // if (target.length < 4) {
        //     doPost = false;
        //     $("#target").val("");
        //     $("#target").css({"background-color": 'red'});
        // } else {
        //     $("#target").css({"background-color": 'white'});
        // }
        check_valid('add-info-table');

        if (check_valid('add-info-table')) {
            doPost = true;
        }

        if (doPost) {

            var target = $("#target").val();
            var quantity = $("#quantity").val();
            var price = $("#price").val();
            var balance = parseInt($('#AMDTOTAL').parent().find('span:last').text().split(' ')[0]);
            if(selectedActionType == 1){
                balance += parseInt(price);
            } else {
                balance -= parseInt(price);
            }
            var post_object = {
                posting: "insert",
                actiontype: selectedActionType,
                selectedcurrency: selectedcurrency,
                target: target,
                quantity: quantity,
                price: price,
                balance: balance
            };


            $.post("/account/accountant/data.php", post_object).done(function (data) {

                try {

                    if (parseInt(data) > 0) {

                        var selectedActionTypeText = $('.input-output input:checked').parent().text();

                        if (selectedActionType == 1) {
                            row += '<td>Մուտք</td>';
                        }

                        if (selectedActionType == 2) {
                            row += '<td>Ելք</td>';
                            price = price * -1;
                        }

                        var formating_price = numberFormating(price)


                        var row = '<tr>';
                        row += '<td>' + data + '</td>';

                        row += '<td>' + target + '</td>';
                        row += '<td>' + quantity + '</td>';
                        row += '<td attr="price">' + formating_price + '</td>';
                        row += '<td attr="price">' + selectedcurrency + '</td>';


                        row += '<td>' + selectedActionTypeText + '</td>';

                        row += '<td>' + getCurrentDateAndTime() + '</td>';
                        row += '<td>' + getUserName() + "  ("+ balance +")" +'</td>';
                        row += '</tr>';

                        $("#data_table").append(row);
                        $("#price").val("");
                        $("#price").css({"background-color": 'white'});
                        $("#target").val("");
                        $("#target").css({"background-color": 'white'});
                        $("#quantity").val("");
                        $("#quantity").css({"background-color": 'white'});

                        calculatePrice(parseFloat(price), selectedcurrency)
                        $('#AMDTOTAL').parent().find('span:last').text(balance + ' AMD');
                    } else {
                        alert("DATA ARE NOT INSERTED");
                    }

                } catch (inserte) {
                    alert("DATA ARE NOT INSERTED" + inserte);
                }

            });
        }

    }


    function getUserName() {
        return $("#usernameid").text();
    }

    function getCurrentDateAndTime() {
        var monthNames = new Array("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");

        var currentdate = new Date();
        var result = currentdate.getDate() + "-"
            + monthNames[currentdate.getMonth() + 1] + "-"
            + currentdate.getFullYear()
            + " " + currentdate.getHours() + ":" + currentdate.getMinutes();
        return result;
    }


    function setAction(action1or2) {
        if (action1or2 == 1) {
            $("#radiobut1").css({"background-color": '#d8d8d8', "color": "darkred", "width": "75%"});
            $("#radio1").prop("checked", true);

            $("#radiobut2").css({"background-color": 'white', "color": "#fff", "width": "18%"});

            $("#radio2").prop("checked", false);
            $('#save').val('Մուտք');            

        }

        if (action1or2 == 2) {
            $("#radiobut2").css({"background-color": '#d8d8d8', "color": "darkred", "width": "75%"});
            $("#radio2").prop("checked", true);

            $("#radiobut1").css({"background-color": 'white', "color": "#fff", "width": "18%"});
            $("#radio1").prop("checked", false);
            $('#save').val('Ելք');
        }
    }


    function calculatePrice(selprice, selcurrenct) {

        if (selprice > 0) {
            var priceinfo = $("#" + selcurrenct + "TOTAL").text();
            if (priceinfo.trim().length > 3) {
                var res = priceinfo.split(" ");
                var pricefinal = selprice + parseFloat(res[0].trim());
                $("#" + selcurrenct + "TOTAL").text(pricefinal + " " + selcurrenct);
            } else {
                $("#" + selcurrenct + "TOTAL").text(selprice + " " + selcurrenct);
            }

        } else if(selprice == -1) {
            $('#AMDTOTAL, #GBPTOTAL, #EURTOTAL, #RUBTOTAL, #USDTOTAL, #IRRTOTAL').text('0');
                
                $("[attr='calckeys']").each(function (index) {
                    var key = $(this).text();
                    var pricekey = "price_" + key.trim();
                    var currencykey = "currency_" + key.trim();

                    var price = parseFloat(numberReFormating($("[attr='" + pricekey + "']").text().trim()));

                    var currency = $("[attr='" + currencykey + "']").text().trim();

                    var priceinfo = $("#" + currency + "TOTAL").text();
                        // console.log(price)
                    if (priceinfo.trim().length > 3) {
                        var res = priceinfo.split(" ");


                        var pricefinal = price + parseFloat(numberReFormating(res[0]));
                        
                        $("#" + currency + "TOTAL").text(numberFormating(pricefinal) + " " + currency);
                    } else {
                        $("#" + currency + "TOTAL").text(numberFormating(price) + " " + currency);
                    }
                });
        } else {

            $("[attr='calckeys']").each(function (index) {
                var key = $(this).text();
                var pricekey = "price_" + key.trim();
                var currencykey = "currency_" + key.trim();

                var price = parseFloat(numberReFormating($("[attr='" + pricekey + "']").text().trim()));
                console.log(price);
                var currency = $("[attr='" + currencykey + "']").text().trim();

                var priceinfo = $("#" + currency + "TOTAL").text();

                if (priceinfo.trim().length > 3) {
                    var res = priceinfo.split(" ");


                    var pricefinal = price + parseFloat(numberReFormating(res[0]));
                    $("#" + currency + "TOTAL").text(numberFormating(pricefinal) + " " + currency);
                } else {
                    $("#" + currency + "TOTAL").text(numberFormating(price) + " " + currency);
                }
            });

        }


    }


    $(document).ready(function () {


        calculatePrice(0, null);


        $(".filter_open").click(function () {
            $('.head-filter').removeClass('hide_filter');
            $(".filter_open").hide();
            $(".filter_close").show();

        })
        $(".filter_close").click(function () {
            $('.head-filter').addClass('hide_filter');
            $(".filter_open").show();
            $(".filter_close").hide();

        })

        <?php if (auth::roleExist(19)){ ?>
        function showSaveCancelButtons(this_tr) {
            this_tr.find('.edit_item').hide();
            this_tr.find('.save_item').show();
            this_tr.find('.close_edit_item').show();
        }

        function hideSaveCancelButtons(this_tr) {
            this_tr.find('.edit_item').show();
            this_tr.find('.save_item').hide();
            this_tr.find('.close_edit_item').hide();
        }


        $('#data_table').on('click','.edit_item',function () {
            var this_tr = $(this).parents('tr');
            // var id = Number(this_tr.find('td').eq(0).text());

            var text_arr = [];
            for (var i = 0; i < 5; i++) {
                var curr_td = this_tr.find('td').eq(i + 1);
                text_arr[i] = curr_td.text().trim();
                curr_td.attr('data-old-text', text_arr[i])
            }

            var inp_arr = [];
            inp_arr[0] = '<input type="text" name="target" value="' + text_arr[0] + '" class="target form-control check_length required" data-minlength="4">';
            inp_arr[1] = '<input type="text" name="quantity"  value="' + text_arr[1] + '" class="quantity form-control validate_number_float">';

            inp_arr[2] = '<input type="text" name="price"  value="' + numberReFormating(text_arr[2]) + '" class="price form-control required validate_number_float">';
            inp_arr[3] = '<select name="selectedcurrency" class="selectedcurrency form-control selectedcurrency">' + $('#selectedcurrency').html() + '</select>';

            inp_arr[4] = '<select class="action-type form-control required" name="acvtiontype">' +
                '<option class="mutq" value="1">Մուտք</option>' +
                '<option class="elq" value="2">Ելք</option>' +
                '</select>';

            for (i = 0; i < 5; i++) {
                this_tr.find('td').eq(i + 1).html(inp_arr[i])
            }


            this_tr.find(".currency_" + text_arr[3]).attr('selected', 'selected');

            var mutq_class = text_arr[4] === 'Մուտք' ? 'mutq' : 'elq';
            this_tr.find('.action-type .' + mutq_class).attr('selected', 'selected');

            showSaveCancelButtons(this_tr);
            restartValidate();
        });

        $('#data_table').on('click','.save_item',function () {
            var this_tr = $(this).parents('tr');
            var id = Number(this_tr.find('td').eq(0).text());

            this_tr.find('td').eq(1).find('input').val();
            var target = $('.target').val();
            var quantity = $('.quantity').val();
            var selectedCurrency = $('.selectedcurrency').val();
            var selectedActionType = $('.action-type').val();
            var selectedActionTypeText = $('.action-type option:selected').text();
            if(selectedActionType == 1){
                var price = Math.abs($('.price').val());
            } else {
                var price = -1 * Math.abs($('.price').val());
            }
            var data = {
                id: id,
                posting: "update",
                action_type: selectedActionType,
                selected_currency: selectedCurrency,
                target: target,
                quantity: quantity,
                price: price
            };
            $.ajax({
                url: "/account/accountant/data.php",
                method: 'post',
                data: data,
                success: function (result) {

                    var res = JSON.parse(result);
                    if (res.message === 'ok') {


                        if (selectedActionType == 2 && price > 0) {
                            price = price * -1;
                        }
                        var formating_price = numberFormating(price);
                        this_tr.find('td').eq(1).text(target);
                        this_tr.find('td').eq(2).text(quantity);
                        this_tr.find('td').eq(3).text(formating_price);
                        this_tr.find('td').eq(4).text(selectedCurrency);
                        this_tr.find('td').eq(5).text(selectedActionTypeText);


                        hideSaveCancelButtons(this_tr)

                        calculatePrice(parseFloat(price), selectedcurrency)
                    } else {
                        alert('error')
                    }
                }
            });


        });

        $('#data_table').on('click','.close_edit_item',function () {
            var this_tr = $(this).parents('tr');

            this_tr.find('td').eq(1).find('input').val();

            for (var i = 0; i < 5; i++) {
                var curr_td = this_tr.find('td').eq(i + 1);
                var text = curr_td.attr('data-old-text');
                curr_td.text(text)
            }

            hideSaveCancelButtons(this_tr)

        });
        $(document).on("click",".add_disadvantage",function(){
            var plusIcon = $(this);
            $(plusIcon).addClass('d-none');
            $(".disadvantageCategory").removeClass('d-none');
        })
        $(document).on("change",".disadvantageCategory",function(){
            var users_ids = $(this).val();
            if(users_ids != ''){
                $.ajax({
                    url: location.href,
                    type: 'post',
                    data: {
                        get_disadvantage_users: true,
                        users_ids: users_ids
                    },
                    success: function(resp){
                        $(".disadvantage_user_select").removeClass('d-none');
                        $(".disadvantage_user_select").html('');
                        resp = JSON.parse(resp);
                        var html = "<option value=''>Member</option>";
                        for(var i = 0;i<resp.length;i++){
                            html+= "<option value='" + resp[i]['id'] + "'>";
                            if(resp[i]['full_name_am']){
                                html+= resp[i]['full_name_am'];
                            }
                            else{
                                html+= resp[i]['username'];
                            }
                            html+="</option>";
                        }
                        $(".disadvantage_user_select").html(html);
                    }
                })
            }
        })
        $(document).on("change",".disadvantage_user_select",function(){
            var val = $('.disadvantage_user_select').val();
            if(val != ''){
                $.ajax({
                    url: location.href,
                    type: 'post',
                    data: {
                        get_disadvantage_list: true,
                        user_id: val
                    },
                    success: function(resp){
                        resp = JSON.parse(resp);
                        if(resp.length > 0){
                            $(".disadvantage_list").removeClass('d-none');
                            $(".disadvantage_description_div").removeClass('d-none');
                            var html = "<option>Տեսակ</option>";
                            for(var i = 0;i< resp.length;i++){
                                html+= "<option value='" + resp[i]['id'] + "'>";
                                    html+= resp[i]['title'];
                                html+= "</option>";
                            }
                            $(".disatvantage_list_select").html(html);
                        }
                        else{
                            alert('Թերությունների ցուցակ չգտնվեց!');
                            $(".disadvantage_description").val('');
                            $(".disadvantage_list").addClass('d-none');
                            $(".add_disadvantage").removeClass('d-none');
                            $(".disadvantage_description_div").addClass('d-none');
                            $(".disadvantage_user_select").addClass('d-none');
                            $(".disadvantageCategory").val('');
                            $(".disadvantageCategory").addClass('d-none');
                        }
                    }
                })
            }
            else{
                $(".disatvantage_list_select").html('');
            }
        })
        $(document).on("click",".addDisadvantageToDB",function(){
            var answer = confirm("Are you sure ?");
            if(answer){
                var category_id = $(".disadvantageCategory").val();
                if(category_id.length > 0){
                    $(".disadvantageCategory").css({'border':'1px solid #ccc'})
                }
                else{
                    $(".disadvantageCategory").css({'border':'1px solid red'})
                    alert('Please select Category!');
                    return false;
                }
                var user_id = $(".disadvantage_user_select").val();
                if(user_id > 0){
                    $(".disadvantage_user_select").css({'border':'1px solid #ccc'})
                }
                else{
                    alert('Please select user!');
                    $(".disadvantage_user_select").css({'border':'1px solid red'})
                    return false;
                }
                var list_id = $(".disatvantage_list_select").val();
                if(list_id > 0){
                    $(".disatvantage_list_select").css({'border':'1px solid #ccc'})
                }
                else{
                    alert('Please select some type!');
                    $(".disatvantage_list_select").css({'border':'1px solid red'})
                    return false;
                }
                var description = $(".disadvantage_description").val();
                var form_data = new FormData();
                form_data.append("insertDisadvantage", true);
                form_data.append("user_id", user_id);
                form_data.append("list_id", list_id);
                form_data.append("description", description);
                form_data.append("file", document.getElementById('disadvantage_file').files[0]);
                $.ajax({
                    url: location.href,
                    method:"POST",
                    data: form_data,
                    contentType: false,
                    cache: false,
                    processData: false,
                    success:function(data)
                    {
                        if(data == 2){
                            var bonus_type = $('input[name="bonus_type"]');
                            $(bonus_type).attr('disabled','disabled');
                            $('input[name="bonus_type"][value="2"]').attr('checked',true);
                        }
                        $(".disadvantage_description").val('');
                        $("#disadvantage_file").val('');
                        $(".disadvantage_list").addClass('d-none');
                        $(".add_disadvantage").removeClass('d-none');
                        $(".disadvantage_description_div").addClass('d-none');
                        $(".disadvantage_user_select").addClass('d-none');
                        $(".disadvantageCategory").val('');
                        $(".disadvantageCategory").addClass('d-none');
                    }
                });
            }
        })
        $('#data_table').on('click','.archive_item',function () {
            var this_tr = $(this).parents('tr');
            var id = Number(this_tr.find('td').eq(0).text());

            if (confirm("Are you sure?")) {


                $.ajax({
                    url: "/account/accountant/data.php",
                    method: 'post',
                    data: {
                        action: 'archiving',
                        id: id
                    },
                    success: function (result) {

                        var res = JSON.parse(result);
                        if (res.message === 'ok') {
                            this_tr.remove()
                        } else {
                            alert('error')
                        }
                    }
                });
            }
            return false;
        });
        <?php } ?>
        
    });

</script>
</body>
</html>