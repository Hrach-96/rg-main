<?php
/**
 * Created by PhpStorm.
 * User: Hrach Dev
 * Date: 4/04/21
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
// Get Sku code
$query_result = getwayConnect::$db->query("SELECT * FROM sku_info");
$total_result = [];
foreach ($query_result as $row) {
    $total_result[] = $row;
}
$icon_images = [];
$icon_images_jquery = '';
$handle = opendir('../../template/images/sku_images/');
while($file = readdir($handle)){
  if($file !== '.' && $file !== '..'){
    $icon_images[] = $file;
    $icon_images_jquery.= $file . '|';
  }
}
$icon_images_jquery = substr($icon_images_jquery, 0, -1);
$array_rod = array(
    ''=>'',
    '0'=>'',
    '1'=>'Множественное число',
    '2'=>'Мужской',
    '3'=>'Женский',
    '4'=>'Средний'
);
if(isset($_POST['generateNePriceFn'])){
    print "<pre>";
    $query_products = 'SELECT * FROM jos_vm_product where product_sku like "%' . $_POST['sku_code'] . '%"';
    $products = getwayConnect::getwayData($query_products);
    foreach($products as $key=>$product){
        $query_stock_products = "SELECT * FROM `jos_vm_product_stock_href` LEFT JOIN orders_products_data ON jos_vm_product_stock_href.stock_product_id = orders_products_data.id LEFT JOIN orders_products ON jos_vm_product_stock_href.stock_product_id = orders_products.product_data_id WHERE product_id = '" . $product['product_id'] . "'";
        $stockproducts = getwayConnect::getwayData($query_stock_products);
        $array = [];
        $flowers_total_count = 0;
        $sweet_total_count = 0;
        $baloons_total_count = 0;
        if(count($stockproducts) > 0){
            foreach($stockproducts as $key=>$value){
                if($value['product_type_id'] == 1){
                    $flowers_total_count = $flowers_total_count + $value['count'];
                }
                else if($value['product_type_id'] == 6){
                    $sweet_total_count = $sweet_total_count + $value['count'];
                }
                else if($value['product_type_id'] == 7){
                    $baloons_total_count = $baloons_total_count + $value['count'];
                }
                $new_array = [
                    'count' => $value['count'],
                    'pNetcost' => $value['pNetcost'],
                ];
                $array[$value['id']] = $new_array;
            }
            addToTotalPricesStockTable($product['product_id'],$array);
            updateToTotalFlowersSweetStockTable($product['product_id'],$flowers_total_count,$sweet_total_count,$baloons_total_count);
        }
    }
}
function updateToTotalFlowersSweetStockTable($product_id,$flowers_total_count,$sweet_total_count,$baloons_total_count){
    getwayConnect::getwaySend('UPDATE `jos_vm_product_stock_total_prices` SET flowers_total_count ="' . $flowers_total_count . '",sweet_total_count ="' . $sweet_total_count . '",baloons_total_count ="' . $baloons_total_count . '" where product_id = ' . $product_id);

}
function addToTotalPricesStockTable($product_id,$outcount){
    $product_row_exist_on_total = getwayConnect::getwayData("SELECT * FROM jos_vm_product_stock_total_prices where product_id = '{$product_id}'");
    $exist_total_row = false;
    if(count($product_row_exist_on_total) > 0){
        $exist_total_row = true;
    }
    $total_pprice = 0;
    $total_pnetcost = 0;
    $total_int_partner_price = 0;
    $total_arm_partner_price = 0;
    foreach($outcount as $key=>$value){
        $stockProductInfo = getwayConnect::getwayData("SELECT * FROM orders_products where product_data_id = '{$key}' and pNetcost = '{$value['pNetcost']}'");
        if(!$stockProductInfo){
            $id = $key + 50; 
            $stockProductInfo = getwayConnect::getwayData("SELECT * FROM orders_products where product_data_id = '{$id}' and pNetcost = '{$value['pNetcost']}'");
            if(!$stockProductInfo){
                $stockProductInfo = getwayConnect::getwayData("SELECT * FROM orders_products where id = '{$key}' and pNetcost = '{$value['pNetcost']}'");
                if(!$stockProductInfo){
                    $stockProductInfo = getwayConnect::getwayData("SELECT * FROM orders_products where id = '{$id}' and pNetcost = '{$value['pNetcost']}'");
                }
            }
        }
        $count_product = $value['count'];
        if (strpos($count_product, '-') !== false) {
            $count_product = str_replace("-","",$count_product);
        }
        // $total_pprice = $total_pprice + $value['pNetcost'] * $count_product;
        $total_pprice = $total_pprice + $stockProductInfo[0]['pprice'] * $count_product;
        // $total_pnetcost = $total_pnetcost + $value['pNetcost'] * $count_product;
        $total_pnetcost = $total_pnetcost + $stockProductInfo[0]['pNetcost'] * $count_product;
        $total_int_partner_price = $total_int_partner_price + $stockProductInfo[0]['int_partner_price'] * $count_product;
        $total_arm_partner_price = $total_arm_partner_price + $stockProductInfo[0]['arm_partner_price'] * $count_product;
    }
    
    if($exist_total_row){
        getwayConnect::getwaySend('UPDATE `jos_vm_product_stock_total_prices` SET total_pprice ="' . $total_pprice . '",total_pnetcost ="' . $total_pnetcost . '",total_int_partner_price ="' . $total_int_partner_price . '",total_arm_partner_price ="' . $total_arm_partner_price . '",last_modified_date ="' . date('Y-m-d h:i:s') . '" where product_id = ' . $product_id);
    }
    else{
        getwayConnect::getwaySend("INSERT INTO `jos_vm_product_stock_total_prices` (`total_pprice`, `total_pnetcost`, `total_int_partner_price`, `total_arm_partner_price`,`product_id`,`last_modified_date`) VALUES('{$total_pprice}', '{$total_pnetcost}', '{$total_int_partner_price}', '{$total_arm_partner_price}','{$product_id}','" . date('Y-m-d h:i:s') . "')");
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


    <title>Sku Codes</title>
    <style type="text/css">
        table .custom_class_for_icon_part{
            padding:0!important;
            text-align: center;
        }
        table .custom_class_width_th{
            min-width:300px;
        }
        table .custom_class_width_th_small{
            min-width:150px;
        }
    </style>
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

<div class="header-space"></div>
<div class="toggle_filter">
    Filter
    <span class="glyphicon glyphicon-chevron-down filter_open" aria-hidden="true"></span>
    <span class="glyphicon glyphicon-chevron-up filter_close" aria-hidden="true"></span>
</div>
<button type="button" class="btn btn-default btn_download_in_csv"><img src="/images/excel.png" style="width: 20px;" title='Download Sku Infos In Excell' ></button>
<button type="button" class="btn btn-default btn_update_publish_unpublish">Update Pulish/Unpublish</button>
<div class=" col-xs-12 par_data_table margin-top">
    <input type='hidden' class='icon_images_jquery' value="<?php echo $icon_images_jquery ?>">
    <table id="data_table" class="table table-bordered table-hover">

        <thead>
        <tr>
            <th>
                #
            </th>
            <th>
                SKU Code
            </th>
            <th>
                Armenian responsible
            </th>
            <th>
                Russian responsible
            </th>
            <th>
                English responsible
            </th>
            <th>
                Brand Name
            </th>
            <th>
                Title Keyword
            </th>
            <th>
                Title Emoji
            </th>
            <th>
                Quantity
            </th>
            <th>
                Icon
            </th>
            <th>
                Depend From Price
            </th>
            <th>
                Depended Price
            </th>
            <th>
                Everythime Available
            </th>
            <th>
                Prepare Time 1
            </th>
            <th>
                Prepare Time 2
            </th>
            <th>
                Design Time 1
            </th>
            <th>
                Design Time 2
            </th>
            <th class='custom_class_width_th'>
                Am Title
            </th>
            <th class='custom_class_width_th'>
                Am Desc
            </th>
            <th class='custom_class_width_th'>
                Ru Title
            </th>
            <th class='custom_class_width_th'>
                Ru Desc
            </th>
            <th class='custom_class_width_th'>
                En Title
            </th>
            <th class='custom_class_width_th'>
                En Desc
            </th>
            <th class='custom_class_width_th'>
                Spa Title
            </th>
            <th class='custom_class_width_th'>
                Spa Desc
            </th>
            <th class='custom_class_width_th'>
                De Title
            </th>
            <th class='custom_class_width_th'>
                De Desc
            </th>
            <th class='custom_class_width_th'>
                Fr Title
            </th>
            <th class='custom_class_width_th'>
                Fr Desc
            </th>
            <th>
                Publisheds
            </th>
            <th>
                Unpublisheds
            </th>
            <th>
                F-A.com fixed price
            </th>
            <th>
                F-A.am Fixed price
            </th>
            <th>
                F-A.com %
            </th>
            <th>
                F-A.am %
            </th>
            <th>
                Anahit %
            </th>
            <th>
                High-price Partners %
            </th>
            <th>
                Low-cost Partners %
            </th>
            <th>
                Ru rod
            </th>
            <th>
                Fr rod
            </th>
            <th>
                De rod
            </th>
            <th>
                ATG Code
            </th>
            <th>
                Գործողություն
            </th>
        </tr>
        </thead>
        <tbody>
        <?php

        foreach ($total_result as $key => $value) {
            ?>

            <tr >
                <td attr="calckeys">
                    <?= $value['id'] ?>
                </td>
                <td>
                    <?php
                        if($userData['id'] == 4){
                            ?>
                                <span style='color:#337ab7' data-sku-code="<?= $value['sku_code'] ?>" class='btn_generate_btn'><?= $value['sku_code'] ?></span>
                            <?php
                        }
                        else{
                            echo $value['sku_code'];
                        }
                    ?>
                </td>
                <td>
                    <?= $value['arm_responsible'] ?>
                </td>
                <td>
                    <?= $value['rus_responsible'] ?>
                </td>
                <td>
                    <?= $value['en_responsible'] ?>
                </td>
                <td>
                    <?= $value['brand_name'] ?>
                </td>
                <td>
                    <?= $value['title_keyword'] ?>
                </td>
                <td>
                    <?= $value['title_emoji'] ?>
                </td>
                <td>
                    <?= $value['quantity'] ?>
                </td>
                <td class='custom_class_for_icon_part'>
                    <?php
                        if($value['icon'] != ''){
                            ?>
                                <img src="../../template/images/sku_images/<?= $value['icon'] ?>" style='height:40px;'>
                                <span style='display:none'><?= $value['icon'] ?></span>
                            <?php
                        }
                    ?>
                </td>
                <td>
                    <?php
                        if($value['depends_from_price'] == 1){
                            ?>
                                Yes
                            <?php
                        }
                        else{
                            ?>
                                No
                            <?php
                        }
                    ?>
                </td>
                <td>
                    <?= $value['dependent_price'] ?>
                </td>
                <td>
                    <?php
                        if($value['everytime_available'] == 1){
                            ?>
                                Yes
                            <?php
                        }
                        else{
                            ?>
                                No
                            <?php
                        }
                    ?>
                </td>
                <td>
                    <?= $value['prepare_time_1'] ?>
                </td>
                <td>
                    <?= $value['prepare_time_2'] ?>
                </td>
                <td>
                    <?= $value['design_time_1'] ?>
                </td>
                <td>
                    <?= $value['design_time_2'] ?>
                </td>
                <td class='custom_class_width_th'>
                    <?= $value['am_title'] ?>
                </td>
                <td class='custom_class_width_th'>
                    <?= $value['am_desc'] ?>
                </td>
                <td class='custom_class_width_th'>
                    <?= $value['ru_title'] ?>
                </td>
                <td class='custom_class_width_th'>
                    <?= $value['ru_desc'] ?>
                </td>
                <td class='custom_class_width_th'>
                    <?= $value['en_title'] ?>
                </td>
                <td class='custom_class_width_th'>
                    <?= $value['en_desc'] ?>
                </td>
                <td class='custom_class_width_th'>
                    <?= $value['spa_title'] ?>
                </td>
                <td class='custom_class_width_th'>
                    <?= $value['spa_desc'] ?>
                </td>
                <td class='custom_class_width_th'>
                    <?= $value['de_title'] ?>
                </td>
                <td class='custom_class_width_th'>
                    <?= $value['de_desc'] ?>
                </td>
                <td class='custom_class_width_th'>
                    <?= $value['fr_title'] ?>
                </td>
                <td class='custom_class_width_th'>
                    <?= $value['fr_desc'] ?>
                </td>
                <td>
                    <?= $value['publisheds'] ?>
                </td>
                <td>
                    <?= $value['unpublisheds'] ?>
                </td>
                <td>
                    <?= $value['f_a_com_fixed_price'] ?>
                </td>
                <td>
                    <?= $value['f_a_am_fixed_price'] ?>
                </td>
                <td>
                    <?= $value['f_a_com_procent'] ?>
                </td>
                <td>
                    <?= $value['f_a_am_procent'] ?>
                </td>
                <td>
                    <?= $value['anahit_procent'] ?>
                </td>
                <td>
                    <?= $value['high_price_partners_procent'] ?>
                </td>
                <td>
                    <?= $value['low_cost_partners_procent'] ?>
                </td>
                <td class='custom_class_width_th_small' data-val="<?= $value['ru_rod'] ?>">
                    <?= $array_rod[$value['ru_rod']] ?>
                </td>
                <td class='custom_class_width_th_small' data-val="<?= $value['fr_rod'] ?>">
                    <?= $array_rod[$value['fr_rod']] ?>
                </td>
                <td class='custom_class_width_th_small' data-val="<?= $value['de_rod'] ?>">
                    <?= $array_rod[$value['de_rod']] ?>
                </td>
                <td>
                    <?= $value['atg_code'] ?>
                </td>
                <td>
                    <a href="javascript:void(0)" class="edit_item" title="Փոփոխել">
                        <i class="glyphicon glyphicon-edit"></i>
                    </a>

                    <a href="javascript:void(0)" class="save_item" title="Պահպանել" style="display: none">
                        <i class="glyphicon glyphicon-floppy-save"></i>
                    </a>

                    <!-- <a href="javascript:void(0)" class="close_edit_item" title="Փակել" style="display: none">
                        <i class="glyphicon glyphicon-remove"></i>
                    </a> -->

                </td>
            </tr>


        <?php } ?>
        </tbody>

    </table>
</div>
<hr style=" visibility: hidden">
<hr style=" visibility: hidden">
<hr style=" visibility: hidden">
<hr style=" visibility: hidden">

<div style="text-align: center" class="form-group margin-top">
    <div id="add-info-table">
        <div class="col-xs-12 text-center" style='margin-top:20px'>
            <h2>Add New Sku Code Info</h2>
        </div>
        <div class="col-xs-12" style='margin-top:20px'>
            <div class="col-xs-12 col-sm-6 col-md-2 col-lg-2 form-group">
                <label for='add_sku_code' >Sku Code</label>
                <input id='add_sku_code'  type="text" id="price" name="price" style=" height: 50px" class="form-control" placeholder="Sku Code">
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-2 form-group">
                <label for='brand_name' >Brand Name</label>
                <input type="text" id="brand_name" name="brand_name" style=" height: 50px" class="form-control" placeholder="Brand Name">
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-2 form-group">
                <label for='title_keyword' >Title Keyword</label>
                <input type="text" id="title_keyword" name="title_keyword" style=" height: 50px" class="form-control" placeholder="Title Keyword">
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-2 form-group">
                <label for='title_emoji' >Title Emoji</label>
                <input type="text" id="title_emoji" name="title_emoji" style=" height: 50px" class="form-control" placeholder="Title Emoji">
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-2 form-group">
                <label for='quantity' >Quantity</label>
                <input type="text" id="quantity" name="quantity" style=" height: 50px" class="form-control" placeholder="Quantity">
            </div>
            <div class="col-xs-12 col-sm-6 col-md-2 col-lg-2 form-group">
                <label>Icon</label>
                <select class="form-control addIconImage" style="height: 50px">
                    <option>Ընտրել</option>
                    <?php
                        foreach($icon_images as $key=>$value){
                            ?>
                                <option>
                                    <?php echo $value ?>
                                </option>
                            <?php
                        }
                    ?>
                </select>
            </div>
            <div class="col-xs-12 col-sm-6 col-md-2 col-lg-2 form-group">
                <label>Depends price</label>
                <select class="form-control addNewSkuDependPrice" style="height: 50px">
                    <option value="no">No</option>
                    <option value="yes">Yes</option>
                </select>
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-2 form-group dependent_price_div" style='display:none'>
                <label for='dependent_price' >Depended Price</label>
                <input type="text" id="dependent_price" name="dependent_price" style=" height: 50px;border:1px solid red" class="form-control" placeholder="Depended Price">
            </div>
            <div class="col-xs-12 col-sm-6 col-md-2 col-lg-2 form-group">
                <label>Everythime</label>
                <select class="form-control everytimeAvailable" style="height: 50px">
                    <option value="no">No</option>
                    <option value="yes">Yes</option>
                </select>
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-2 form-group">
                <label for='prepare_time_1' >Prepare Time 1</label>
                <input type="text" id="prepare_time_1" name="prepare_time_1" style=" height: 50px" class="form-control" placeholder="Prepare Time 1">
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-2 form-group">
                <label for='prepare_time_2' >Prepare Time 2</label>
                <input type="text" id="prepare_time_2" name="prepare_time_2" style=" height: 50px" class="form-control" placeholder="Prepare Time 2">
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-2 form-group">
                <label for='design_time_1' >Design Time 1</label>
                <input type="time" id="design_time_1" value="<?php echo date("H:i", strtotime(date("H:i"))) ?>" name="design_time_1" style=" height: 50px" class="form-control" placeholder="Design Time 1">
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-2 form-group">
                <label for='design_time_2' >Design Time 2</label>
                <input type="time" id="design_time_2" name="design_time_2" style=" height: 50px" class="form-control" value="<?php echo date("H:i", strtotime(date("H:i"))) ?>" placeholder="Design Time 2">
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-2 form-group">
                <label for='am_title' >Am Title</label>
                <input type="text" id="am_title" name="am_title" style=" height: 50px" class="form-control" placeholder="AM title">
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-2 form-group">
                <label for='am_desc' >Am Desc</label>
                <input type="text" id="am_desc" name="am_desc" style=" height: 50px" class="form-control" placeholder="AM desc">
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-2 form-group">
                <label for='ru_title' >Ru Title</label>
                <input type="text" id="ru_title" name="ru_title" style=" height: 50px" class="form-control" placeholder="Ru title">
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-2 form-group">
                <label for='ru_desc' >Ru Desc</label>
                <input type="text" id="ru_desc" name="ru_desc" style=" height: 50px" class="form-control" placeholder="Ru desc">
            </div>
            <div class="col-xs-12 col-sm-6 col-md-2 col-lg-2 form-group">
                <label>Ru rod</label>
                <select class="form-control ru_rod" style="height: 50px">
                    <option value="1">Множественное число</option>
                    <option value="2">Мужской</option>
                    <option value="3">Женский</option>
                    <option value="4">Средний</option>
                </select>
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-2 form-group">
                <label for='en_title' >En Title</label>
                <input type="text" id="en_title" name="en_title" style=" height: 50px" class="form-control" placeholder="En title">
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-2 form-group">
                <label for='en_desc' >En Desc</label>
                <input type="text" id="en_desc" name="en_desc" style=" height: 50px" class="form-control" placeholder="En desc">
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-2 form-group">
                <label for='fr_title' >Fr Title</label>
                <input type="text" id="fr_title" name="fr_title" style=" height: 50px" class="form-control" placeholder="Fr title">
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-2 form-group">
                <label for='fr_desc' >Fr Desc</label>
                <input type="text" id="fr_desc" name="fr_desc" style=" height: 50px" class="form-control" placeholder="Fr desc">
            </div>
            <div class="col-xs-12 col-sm-6 col-md-2 col-lg-2 form-group">
                <label>Fr rod</label>
                <select class="form-control fr_rod" style="height: 50px">
                    <option value="1">Множественное число</option>
                    <option value="2">Мужской</option>
                    <option value="3">Женский</option>
                    <option value="4">Средний</option>
                </select>
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-2 form-group">
                <label for='de_title' >De Title</label>
                <input type="text" id="de_title" name="de_title" style=" height: 50px" class="form-control" placeholder="De title">
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-2 form-group">
                <label for='de_desc' >De Desc</label>
                <input type="text" id="de_desc" name="de_desc" style=" height: 50px" class="form-control" placeholder="De desc">
            </div>
            <div class="col-xs-12 col-sm-6 col-md-2 col-lg-2 form-group">
                <label>De rod</label>
                <select class="form-control de_rod" style="height: 50px">
                    <option value="1">Множественное число</option>
                    <option value="2">Мужской</option>
                    <option value="3">Женский</option>
                    <option value="4">Средний</option>
                </select>
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-2 form-group">
                <label for='atg_code' >ATG Code</label>
                <input type="text" id="atg_code" name="atg_code" style=" height: 50px" class="form-control atg_code" placeholder="ATG Code">
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-2 form-group">
                <label for='spa_title' >Spa Title</label>
                <input type="text" id="spa_title" name="spa_title" style=" height: 50px" class="form-control" placeholder="Spa title">
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-2 form-group">
                <label for='spa_desc' >Spa Desc</label>
                <input type="text" id="spa_desc" name="spa_desc" style=" height: 50px" class="form-control" placeholder="Spa desc">
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-2 form-group">
                <label for='publisheds' >Publisheds</label>
                <input type="text" id="publisheds" name="publisheds" style=" height: 50px" class="form-control" placeholder="Publisheds">
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-2 form-group">
                <label for='unpublisheds' >Unpublisheds</label>
                <input type="text" id="unpublisheds" name="unpublisheds" style=" height: 50px" class="form-control" placeholder="Unpublisheds">
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-2 form-group">
                <label for='f_a_com_fixed_price' >F-A.com fixed price</label>
                <input type="text" id="f_a_com_fixed_price" name="f_a_com_fixed_price" style=" height: 50px" class="form-control" placeholder="F-A.com fixed price">
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-2 form-group">
                <label for='f_a_am_fixed_price' >F-A.am fixed price</label>
                <input type="text" id="f_a_am_fixed_price" name="f_a_am_fixed_price" style=" height: 50px" class="form-control" placeholder="F-A.am fixed price">
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-2 form-group">
                <label for='f_a_com_procent' >F-A.com %</label>
                <input type="text" id="f_a_com_procent" name="f_a_com_procent" style=" height: 50px" class="form-control" placeholder="F-A.com %">
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-2 form-group">
                <label for='f_a_am_procent' >F-A.am %</label>
                <input type="text" id="f_a_am_procent" name="f_a_am_procent" style=" height: 50px" class="form-control" placeholder="F-A.am %">
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-2 form-group">
                <label for='anahit_procent' >Anahit %</label>
                <input type="text" id="anahit_procent" name="anahit_procent" style=" height: 50px" class="form-control" placeholder="Anahit %">
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-2 form-group">
                <label for='high_price_partners_procent' >High-price Partners %</label>
                <input type="text" id="high_price_partners_procent" name="high_price_partners_procent" style=" height: 50px" class="form-control" placeholder="High-price Partners %">
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-2 form-group">
                <label for='low_cost_partners_procent' >Low-cost Partners %</label>
                <input type="text" id="low_cost_partners_procent" name="low_cost_partners_procent" style=" height: 50px" class="form-control" placeholder="Low-cost Partners %">
            </div>
        </div>
        <div class="col-xs-12 text-center " style='margin-top:20px'>
            <input type="submit" id="save" class="btn btn-lg btn-primary center-block" style="height: 50px" value="Add New Sku" >
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
    $(document).ready(function () {
        $(document).on('click',"#save",function(){
            add_new_sku_code();
        })
        $(document).on('click',".btn_download_in_csv",function(){
            setTimeout(function(){
                window.open('download-excel-main.php?download_sku_excell=true', '_blank');
            },500)
        })
        $(document).on('click',".btn_update_publish_unpublish",function(){
            var post_object = {
                posting: "update_publish_unpublish",
            }
            $.post("/account/control/data.php", post_object).done(function (data) {
                alert('Sku Info are updated')
                location.reload()
            });
        })
        $(document).on("click",".btn_generate_btn",function(){
            var sku_code = $(this).attr('data-sku-code');
            generateNePriceFn(sku_code);
        })
        function generateNePriceFn(sku_code){
            console.log(sku_code)
            $.ajax({
                type:"post",
                data:{generateNePriceFn:true,sku_code:sku_code},
                success:function(res){
                    alert('Prices Are Updated');
                }
            })
        }
        function add_new_sku_code() {
            var sku_code = $("#add_sku_code").val();
            var am_title = $("#am_title").val();
            var ru_title = $("#ru_title").val();
            var en_title = $("#en_title").val();
            var fr_title = $("#fr_title").val();
            var de_title = $("#de_title").val();
            var spa_title = $("#spa_title").val();
            var am_desc = $("#am_desc").val();
            var ru_desc = $("#ru_desc").val();
            var en_desc = $("#en_desc").val();
            var fr_desc = $("#fr_desc").val();
            var de_desc = $("#de_desc").val();
            var spa_desc = $("#spa_desc").val();
            var prepare_time_1 = $("#prepare_time_1").val();
            var prepare_time_2 = $("#prepare_time_2").val();
            var design_time_1 = $("#design_time_1").val();
            var design_time_2 = $("#design_time_2").val();
            var quantity = $("#quantity").val();
            var publisheds = $("#publisheds").val();
            var unpublisheds = $("#unpublisheds").val();
            var f_a_com_fixed_price = $("#f_a_com_fixed_price").val();
            var f_a_am_fixed_price = $("#f_a_am_fixed_price").val();
            var f_a_com_procent = $("#f_a_com_procent").val();
            var f_a_am_procent = $("#f_a_am_procent").val();
            var anahit_procent = $("#anahit_procent").val();
            var high_price_partners_procent = $("#high_price_partners_procent").val();
            var low_cost_partners_procent = $("#low_cost_partners_procent").val();
            var title_emoji = $("#title_emoji").val();
            var brand_name = $("#brand_name").val();
            var title_keyword = $("#title_keyword").val();
            var addNewSkuDependPrice = $(".addNewSkuDependPrice").val();
            var ru_rod = $(".ru_rod").val();
            var fr_rod = $(".fr_rod").val();
            var de_rod = $(".de_rod").val();
            var atg_code = $(".atg_code").val();
            var addIconImage = $(".addIconImage").val();
            var dependent_price = $("#dependent_price").val();
            var everytimeAvailable = $(".everytimeAvailable").val();
            var post_object = {
                posting: "insert",
                addNewSkuDependPrice: addNewSkuDependPrice,
                ru_rod: ru_rod,
                fr_rod: fr_rod,
                de_rod: de_rod,
                atg_code: atg_code,
                addIconImage: addIconImage,
                dependent_price: dependent_price,
                everytimeAvailable: everytimeAvailable,
                sku_code: sku_code,
                am_title: am_title,
                ru_title: ru_title,
                en_title: en_title,
                fr_title: fr_title,
                de_title: de_title,
                spa_title: spa_title,
                am_desc: am_desc,
                ru_desc: ru_desc,
                en_desc: en_desc,
                fr_desc: fr_desc,
                de_desc: de_desc,
                spa_desc: spa_desc,
                prepare_time_1: prepare_time_1,
                prepare_time_2: prepare_time_2,
                design_time_1: design_time_1,
                design_time_2: design_time_2,
                quantity: quantity,
                publisheds: publisheds,
                unpublisheds: unpublisheds,
                f_a_com_fixed_price: f_a_com_fixed_price,
                f_a_am_fixed_price: f_a_am_fixed_price,
                f_a_com_procent: f_a_com_procent,
                f_a_am_procent: f_a_am_procent,
                anahit_procent: anahit_procent,
                high_price_partners_procent: high_price_partners_procent,
                low_cost_partners_procent: low_cost_partners_procent,
                title_emoji: title_emoji,
                brand_name: brand_name,
                title_keyword: title_keyword
            };
            $.post("/account/control/data.php", post_object).done(function (data) {
                alert('Sku Info Are Added')
                location.reload()
            });

        }

        $('#data_table').DataTable({
            "autoWidth": true,
            "scrollX": true,
            "columnDefs": [
                { "width": "100px", "targets": [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,21,21,22,23,24,25] }
              ]
        });

        $(document).on('change','.addNewSkuDependPrice',function () {
            var val = $(this).val();
            if(val == 'yes'){
                $(".dependent_price_div").slideDown(200)
            }
            else{
                $(".dependent_price_div").slideUp(200)
            }
        })
        $('#data_table').on('click','.edit_item',function () {
            var this_tr = $(this).parents('tr');
            // var id = Number(this_tr.find('td').eq(0).text());

            var text_arr = [];
            for (var i = 0; i < 42; i++) {
                var curr_td = this_tr.find('td').eq(i + 1);
                if(i >= 37 && i < 40){
                    text_arr[i] = $(curr_td).attr('data-val');
                }
                else{
                    text_arr[i] = curr_td.text().trim();
                }
                curr_td.attr('data-old-text', text_arr[i])
            }
            var icon_images_jquery = $(".icon_images_jquery").val();
            icon_images_jquery = icon_images_jquery.split('|');
            var icon_images_jquery_select = "<select class='form-control icon_images_new'>";
            for(var i = 0 ; i < icon_images_jquery.length ; i++){
                var selected = '';
                if(text_arr[5] == icon_images_jquery[i]){
                    selected = 'selected';
                }
                icon_images_jquery_select+= "<option " + selected + ">" + icon_images_jquery[i] + "</option>"; 
            }
            icon_images_jquery_select+="</select>";
            var inp_arr = [];
            inp_arr[0] = '<input type="text" name="sku_code" value="' + text_arr[0] + '" class="form-control sku_code">';
            inp_arr[1] = '<input type="text" name="arm_responsible" value="' + text_arr[1] + '" class="form-control arm_responsible">';
            inp_arr[2] = '<input type="text" name="rus_responsible" value="' + text_arr[2] + '" class="form-control rus_responsible">';
            inp_arr[3] = '<input type="text" name="en_responsible" value="' + text_arr[3] + '" class="form-control en_responsible">';
            inp_arr[4] = '<input type="text" name="brand_name" value="' + text_arr[4] + '" class="form-control brand_name">';
            inp_arr[5] = '<input type="text" name="title_keyword" value="' + text_arr[5] + '" class="form-control title_keyword">';
            inp_arr[6] = '<input type="text" name="title_emoji" value="' + text_arr[6] + '" class="form-control title_emoji">';
            inp_arr[7] = '<input type="text" name="quantity" value="' + text_arr[7] + '" class="form-control quantity">';
            inp_arr[8] = icon_images_jquery_select;
            
            inp_arr[9] = '<select class="depended_from_price form-control" name="depended_from_price">' +
                '<option class="depended_from_price_Yes" value="Yes">Yes</option>' +
                '<option class="depended_from_price_No" value="No">No</option>' +
                '</select>';
            inp_arr[10] = '<input type="text" name="dependent_price" value="' + text_arr[10] + '" class="form-control dependent_price">';
            inp_arr[11] = '<select class="everytime_available form-control" name="everytime_available">' +
                '<option class="everytime_available_Yes" value="Yes">Yes</option>' +
                '<option class="everytime_available_No" value="No">No</option>' +
                '</select>';
            inp_arr[12] = '<input type="text" name="prepare_time_1" value="' + text_arr[12] + '" class="form-control prepare_time_1">';
            inp_arr[13] = '<input type="text" name="prepare_time_2" value="' + text_arr[13] + '" class="form-control prepare_time_2">';
            inp_arr[14] = '<input type="time" name="design_time_1" value="' + text_arr[14] + '" class="form-control design_time_1">';
            inp_arr[15] = '<input type="time" name="design_time_2" value="' + text_arr[15] + '" class="form-control design_time_2">';
            inp_arr[16] = '<input type="text" name="am_title" value="' + text_arr[16] + '" class="form-control am_title">';
            inp_arr[17] = '<input type="text" name="am_desc" value="' + text_arr[17] + '" class="form-control am_desc">';
            inp_arr[18] = '<input type="text" name="ru_title" value="' + text_arr[18] + '" class="form-control ru_title">';
            inp_arr[19] = '<input type="text" name="ru_desc" value="' + text_arr[19] + '" class="form-control ru_desc">';
            inp_arr[20] = '<input type="text" name="en_title" value="' + text_arr[20] + '" class="form-control en_title">';
            inp_arr[21] = '<input type="text" name="en_desc" value="' + text_arr[21] + '" class="form-control en_desc">';
            inp_arr[22] = '<input type="text" name="spa_title" value="' + text_arr[22] + '" class="form-control spa_title">';
            inp_arr[23] = '<input type="text" name="spa_desc" value="' + text_arr[23] + '" class="form-control spa_desc">';
            inp_arr[24] = '<input type="text" name="de_title" value="' + text_arr[24] + '" class="form-control de_title">';
            inp_arr[25] = '<input type="text" name="de_desc" value="' + text_arr[25] + '" class="form-control de_desc">';
            inp_arr[26] = '<input type="text" name="fr_title" value="' + text_arr[26] + '" class="form-control fr_title">';
            inp_arr[27] = '<input type="text" name="fr_desc" value="' + text_arr[27] + '" class="form-control fr_desc">';
            inp_arr[28] = '<input type="text" name="publisheds" value="' + text_arr[28] + '" class="form-control publisheds">';
            inp_arr[29] = '<input type="text" name="unpublisheds" value="' + text_arr[29] + '" class="form-control unpublisheds">';
            inp_arr[30] = '<input type="text" name="f_a_com_fixed_price" value="' + text_arr[30] + '" class="form-control f_a_com_fixed_price">';
            inp_arr[31] = '<input type="text" name="f_a_am_fixed_price" value="' + text_arr[31] + '" class="form-control f_a_am_fixed_price">';
            inp_arr[32] = '<input type="text" name="f_a_com_procent" value="' + text_arr[32] + '" class="form-control f_a_com_procent">';
            inp_arr[33] = '<input type="text" name="f_a_am_procent" value="' + text_arr[33] + '" class="form-control f_a_am_procent">';
            inp_arr[34] = '<input type="text" name="anahit_procent" value="' + text_arr[34] + '" class="form-control anahit_procent">';
            inp_arr[35] = '<input type="text" name="high_price_partners_procent" value="' + text_arr[35] + '" class="form-control high_price_partners_procent">';
            inp_arr[36] = '<input type="text" name="low_cost_partners_procent" value="' + text_arr[36] + '" class="form-control low_cost_partners_procent">';
            inp_arr[37] = '<select class="ru_rod custom_class_width_th_small form-control" name="ru_rod">' +
                '<option value="1" class="ru_rod_1">Множественное число</option><option value="2" class="ru_rod_2">Мужской</option><option value="3" class="ru_rod_3">Женский</option><option value="4" class="ru_rod_4">Средний</option>'+
                '</select>';
            inp_arr[38] = '<select class="fr_rod custom_class_width_th_small form-control" name="fr_rod">' +
                '<option value="1" class="fr_rod_1">Множественное число</option><option value="2" class="fr_rod_2">Мужской</option><option value="3" class="fr_rod_3">Женский</option><option value="4" class="fr_rod_4">Средний</option>'+
                '</select>';
            inp_arr[39] = '<select class="de_rod custom_class_width_th_small form-control" name="de_rod">' +
                '<option value="1" class="de_rod_1">Множественное число</option><option value="2" class="de_rod_2">Мужской</option><option value="3" class="de_rod_3">Женский</option><option value="4" class="de_rod_4">Средний</option>'+
                '</select>';
            inp_arr[40] = '<input type="text" name="atg_code" value="' + text_arr[40] + '" class="form-control atg_code">';

            for (i = 0; i < 41; i++) {
                this_tr.find('td').eq(i + 1).html(inp_arr[i])
            }
            var dependet_from_price_class = text_arr[9] === 'Yes' ? 'Yes' : 'No';
            this_tr.find('.depended_from_price .depended_from_price_' + dependet_from_price_class).attr('selected', 'selected');
            var everytime_available_class = text_arr[11] === 'Yes' ? 'Yes' : 'No';
            this_tr.find('.everytime_available .everytime_available_' + everytime_available_class).attr('selected', 'selected');
            this_tr.find('.ru_rod .ru_rod_' + text_arr[37]).attr('selected', 'selected');
            this_tr.find('.fr_rod .fr_rod_' + text_arr[38]).attr('selected', 'selected');
            this_tr.find('.de_rod .de_rod_' + text_arr[39]).attr('selected', 'selected');
            showSaveCancelButtons(this_tr);
            restartValidate();
        });
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
        var array_rod = Array();
            array_rod['0'] = '';
            array_rod['1'] = 'Множественное число';
            array_rod['2'] = 'Мужской';
            array_rod['3'] = 'Женский';
            array_rod['4'] = 'Средний';
        $('#data_table').on('click','.save_item',function () {
            var this_tr = $(this).parents('tr');
            var id = Number(this_tr.find('td').eq(0).text());

            this_tr.find('td').eq(1).find('input').val();
            var sku_code = this_tr.find('td').find($(".sku_code")).val();
            var am_title = this_tr.find('td').find($(".am_title")).val();
            var ru_title = this_tr.find('td').find($(".ru_title")).val();
            var en_title = this_tr.find('td').find($(".en_title")).val();
            var fr_title = this_tr.find('td').find($(".fr_title")).val();
            var de_title = this_tr.find('td').find($(".de_title")).val();
            var spa_title = this_tr.find('td').find($(".spa_title")).val();
            var am_desc = this_tr.find('td').find($(".am_desc")).val();
            var ru_desc = this_tr.find('td').find($(".ru_desc")).val();
            var en_desc = this_tr.find('td').find($(".en_desc")).val();
            var fr_desc = this_tr.find('td').find($(".fr_desc")).val();
            var de_desc = this_tr.find('td').find($(".de_desc")).val();
            var spa_desc = this_tr.find('td').find($(".spa_desc")).val();
            var prepare_time_1 = this_tr.find('td').find($(".prepare_time_1")).val();
            var prepare_time_2 = this_tr.find('td').find($(".prepare_time_2")).val();
            var design_time_1 = this_tr.find('td').find($(".design_time_1")).val();
            var design_time_2 = this_tr.find('td').find($(".design_time_2")).val();
            var quantity = this_tr.find('td').find($(".quantity")).val();
            var publisheds = this_tr.find('td').find($(".publisheds")).val();
            var unpublisheds = this_tr.find('td').find($(".unpublisheds")).val();
            var f_a_com_fixed_price = this_tr.find('td').find($(".f_a_com_fixed_price")).val();
            var f_a_am_fixed_price = this_tr.find('td').find($(".f_a_am_fixed_price")).val();
            var f_a_com_procent = this_tr.find('td').find($(".f_a_com_procent")).val();
            var f_a_am_procent = this_tr.find('td').find($(".f_a_am_procent")).val();
            var anahit_procent = this_tr.find('td').find($(".anahit_procent")).val();
            var high_price_partners_procent = this_tr.find('td').find($(".high_price_partners_procent")).val();
            var low_cost_partners_procent = this_tr.find('td').find($(".low_cost_partners_procent")).val();
            var title_emoji = this_tr.find('td').find($(".title_emoji")).val();
            var arm_responsible = this_tr.find('td').find($(".arm_responsible")).val();
            var rus_responsible = this_tr.find('td').find($(".rus_responsible")).val();
            var en_responsible = this_tr.find('td').find($(".en_responsible")).val();
            var brand_name = this_tr.find('td').find($(".brand_name")).val();
            var title_keyword = this_tr.find('td').find($(".title_keyword")).val();
            var depended_from_price = this_tr.find('td').find($(".depended_from_price")).val();
            var icon_images_new = this_tr.find('td').find($(".icon_images_new")).val();
            var dependent_price = this_tr.find('td').find($(".dependent_price")).val();
            var everytime_available = this_tr.find('td').find($(".everytime_available")).val();
            var ru_rod = this_tr.find('td').find($(".ru_rod")).val();
            var fr_rod = this_tr.find('td').find($(".fr_rod")).val();
            var de_rod = this_tr.find('td').find($(".de_rod")).val();
            var atg_code = this_tr.find('td').find($(".atg_code")).val();
            var data = {
                id: id,
                posting: "update",
                depended_from_price: depended_from_price,
                icon_images_new: icon_images_new,
                dependent_price: dependent_price,
                everytime_available: everytime_available,
                atg_code: atg_code,
                ru_rod: ru_rod,
                fr_rod: fr_rod,
                de_rod: de_rod,
                sku_code: sku_code,
                am_title: am_title,
                ru_title: ru_title,
                en_title: en_title,
                fr_title: fr_title,
                de_title: de_title,
                spa_title: spa_title,
                am_desc: am_desc,
                ru_desc: ru_desc,
                en_desc: en_desc,
                fr_desc: fr_desc,
                de_desc: de_desc,
                spa_desc: spa_desc,
                prepare_time_1: prepare_time_1,
                prepare_time_2: prepare_time_2,
                design_time_1: design_time_1,
                design_time_2: design_time_2,
                quantity: quantity,
                publisheds: publisheds,
                unpublisheds: unpublisheds,
                f_a_com_fixed_price: f_a_com_fixed_price,
                f_a_am_fixed_price: f_a_am_fixed_price,
                f_a_com_procent: f_a_com_procent,
                f_a_am_procent: f_a_am_procent,
                anahit_procent: anahit_procent,
                high_price_partners_procent: high_price_partners_procent,
                low_cost_partners_procent: low_cost_partners_procent,
                title_emoji: title_emoji,
                arm_responsible: arm_responsible,
                rus_responsible: rus_responsible,
                en_responsible: en_responsible,
                brand_name: brand_name,
                title_keyword: title_keyword
            };
            $.ajax({
                url: "/account/control/data.php",
                method: 'post',
                data: data,
                success: function (result) {
                    this_tr.find('td').eq(1).text(sku_code);
                    this_tr.find('td').eq(2).text(arm_responsible);
                    this_tr.find('td').eq(3).text(rus_responsible);
                    this_tr.find('td').eq(4).text(en_responsible);
                    this_tr.find('td').eq(5).text(brand_name);
                    this_tr.find('td').eq(6).text(title_keyword);
                    this_tr.find('td').eq(7).text(title_emoji);
                    this_tr.find('td').eq(8).text(quantity);
                    this_tr.find('td').eq(9).empty();
                    this_tr.find('td').eq(10).append("<img src='../../template/images/sku_images/" + icon_images_new + "' style='height:40px;'><span style='display:none'>" + icon_images_new + "</span>");
                    this_tr.find('td').eq(11).text(depended_from_price);
                    this_tr.find('td').eq(12).text(dependent_price);
                    this_tr.find('td').eq(13).text(everytime_available);
                    this_tr.find('td').eq(14).text(prepare_time_1);
                    this_tr.find('td').eq(15).text(prepare_time_2);
                    this_tr.find('td').eq(16).text(design_time_1);
                    this_tr.find('td').eq(17).text(design_time_2);
                    this_tr.find('td').eq(18).text(am_title);
                    this_tr.find('td').eq(19).text(am_desc);
                    this_tr.find('td').eq(20).text(ru_title);
                    this_tr.find('td').eq(21).text(ru_desc);
                    this_tr.find('td').eq(22).text(en_title);
                    this_tr.find('td').eq(23).text(en_desc);
                    this_tr.find('td').eq(24).text(spa_title);
                    this_tr.find('td').eq(25).text(spa_desc);
                    this_tr.find('td').eq(26).text(de_title);
                    this_tr.find('td').eq(27).text(de_desc);
                    this_tr.find('td').eq(28).text(fr_title);
                    this_tr.find('td').eq(29).text(fr_desc);
                    this_tr.find('td').eq(30).text(publisheds);
                    this_tr.find('td').eq(31).text(unpublisheds);
                    this_tr.find('td').eq(32).text(f_a_com_fixed_price);
                    this_tr.find('td').eq(33).text(f_a_am_fixed_price);
                    this_tr.find('td').eq(34).text(f_a_com_procent);
                    this_tr.find('td').eq(35).text(f_a_am_procent);
                    this_tr.find('td').eq(36).text(anahit_procent);
                    this_tr.find('td').eq(37).text(high_price_partners_procent);
                    this_tr.find('td').eq(38).text(low_cost_partners_procent);
                    this_tr.find('td').eq(39).text(array_rod[ru_rod]);
                    this_tr.find('td').eq(40).text(array_rod[fr_rod]);
                    this_tr.find('td').eq(41).text(array_rod[de_rod]);
                    this_tr.find('td').eq(42).text(atg_code);
                    hideSaveCancelButtons(this_tr)
                }
            });


        });

        $('#data_table').on('click','.close_edit_item',function () {
            var this_tr = $(this).parents('tr');

            this_tr.find('td').eq(1).find('input').val();

            for (var i = 0; i < 27; i++) {
                var curr_td = this_tr.find('td').eq(i + 1);
                var text = curr_td.attr('data-old-text');
                curr_td.text(text)
            }

            hideSaveCancelButtons(this_tr)

        });

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
        
    });

</script>
</body>
</html>