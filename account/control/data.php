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

if (isset($_POST["posting"]) && $_POST["posting"] == "update") {
    $id = $_POST["id"];
    $sku_code = $_POST["sku_code"];
    $arm_responsible = $_POST["arm_responsible"];
    $rus_responsible = $_POST["rus_responsible"];
    $en_responsible = $_POST["en_responsible"];
    $am_title = $_POST["am_title"];
    $ru_title = $_POST["ru_title"];
    $en_title = $_POST["en_title"];
    $fr_title = $_POST["fr_title"];
    $de_title = $_POST["de_title"];
    $spa_title = $_POST["spa_title"];
    $am_desc = $_POST["am_desc"];
    $ru_desc = $_POST["ru_desc"];
    $en_desc = $_POST["en_desc"];
    $spa_desc = $_POST["spa_desc"];
    $de_desc = $_POST["de_desc"];
    $fr_desc = $_POST["fr_desc"];
    $prepare_time_1 = $_POST["prepare_time_1"];
    $prepare_time_2 = $_POST["prepare_time_2"];
    $design_time_1 = $_POST["design_time_1"];
    $design_time_2 = $_POST["design_time_2"];
    $quantity = $_POST["quantity"];
    $publisheds = $_POST["publisheds"];
    $unpublisheds = $_POST["unpublisheds"];
    $f_a_com_fixed_price = $_POST["f_a_com_fixed_price"];
    $f_a_am_fixed_price = $_POST["f_a_am_fixed_price"];
    $f_a_com_procent = $_POST["f_a_com_procent"];
    $f_a_am_procent = $_POST["f_a_am_procent"];
    $anahit_procent = $_POST["anahit_procent"];
    $high_price_partners_procent = $_POST["high_price_partners_procent"];
    $low_cost_partners_procent = $_POST["low_cost_partners_procent"];
    $title_emoji = $_POST["title_emoji"];
    $brand_name = $_POST["brand_name"];
    $title_keyword = $_POST["title_keyword"];
    $depended_from_price = $_POST["depended_from_price"];
    $addIconImage = $_POST["icon_images_new"];
    $dependent_price = $_POST["dependent_price"];
    $everytime_available_post = $_POST["everytime_available"];
    $ru_rod = $_POST["ru_rod"];
    $fr_rod = $_POST["fr_rod"];
    $de_rod = $_POST["de_rod"];
    $atg_code = $_POST["atg_code"];
    $depends_from_price = 0;
    if($depended_from_price == 'Yes'){
        $depends_from_price = 1;
    }
    else{
        $dependent_price = '';
    }
    $everytime_available = 0;
    if($everytime_available_post == 'Yes'){
        $everytime_available = 1;
    }
    $query = "Update sku_info set sku_code='" . $sku_code . "',am_title='" . $am_title . "',en_responsible='" . $en_responsible . "',arm_responsible='" . $arm_responsible . "',rus_responsible='" . $rus_responsible . "',ru_title='" . $ru_title . "',en_title='" . $en_title . "',spa_title='" . $spa_title . "',de_title='" . $de_title . "',fr_title='" . $fr_title . "',am_desc='" . $am_desc . "',ru_desc='" . $ru_desc . "',en_desc='" . $en_desc . "',spa_desc='" . $spa_desc . "',de_desc='" . $de_desc . "',fr_desc='" . $fr_desc . "',prepare_time_1='" . $prepare_time_1 . "',prepare_time_2='" . $prepare_time_2 . "',design_time_1='" . $design_time_1 . "',design_time_2='" . $design_time_2 . "',dependent_price='" . $dependent_price . "',depends_from_price='" . $depends_from_price . "',everytime_available='" . $everytime_available . "',quantity='" . $quantity . "',publisheds='" . $publisheds . "',unpublisheds='" . $unpublisheds . "',f_a_com_fixed_price='" . $f_a_com_fixed_price . "',f_a_am_fixed_price='" . $f_a_am_fixed_price . "',f_a_com_procent='" . $f_a_com_procent . "',f_a_am_procent='" . $f_a_am_procent . "',anahit_procent='" . $anahit_procent . "',high_price_partners_procent='" . $high_price_partners_procent . "',low_cost_partners_procent='" . $low_cost_partners_procent . "',title_emoji='" . $title_emoji . "',brand_name='" . $brand_name . "',title_keyword='" . $title_keyword . "',icon='" . $addIconImage . "',ru_rod='" . $ru_rod . "',fr_rod='" . $fr_rod . "',de_rod='" . $de_rod . "',atg_code='" . $atg_code . "' where id = '" . $id . "'";
    getwayConnect::getwaySend($query);
    return true;
}
if (isset($_POST["posting"]) && $_POST["posting"] == "update_publish_unpublish") {
    $query_result = getwayConnect::$db->query("SELECT * FROM sku_info");
    $total_result = [];
    foreach ($query_result as $row) {
        $total_result[] = $row;
    }
    foreach($total_result as $key=>$value){
        $query = "SELECT * FROM jos_vm_product where product_sku like '%" . $value['sku_code'] . "%'";
        $products = getwayConnect::getwayData($query);
        $product_publish = 0;
        $product_unpublish = 0;
        foreach($products as $product){
            if($product['product_publish'] == 'Y'){
                $product_publish++;
            }
            else{
                $product_unpublish++;
            }
        }
        getwayConnect::getwaySend('UPDATE sku_info SET publisheds = "' . $product_publish . '", unpublisheds = "' . $product_unpublish . '" WHERE id = ' . $value['id']);
    }
}
if (isset($_POST["posting"]) && $_POST["posting"] == "insert") {
        $sku_code = $_POST["sku_code"];
        $am_title = $_POST["am_title"];
        $ru_title = $_POST["ru_title"];
        $en_title = $_POST["en_title"];
        $fr_title = $_POST["fr_title"];
        $de_title = $_POST["de_title"];
        $spa_title = $_POST["spa_title"];
        $am_desc = $_POST["am_desc"];
        $ru_desc = $_POST["ru_desc"];
        $en_desc = $_POST["en_desc"];
        $spa_desc = $_POST["spa_desc"];
        $de_desc = $_POST["de_desc"];
        $fr_desc = $_POST["fr_desc"];
        $prepare_time_1 = $_POST["prepare_time_1"];
        $prepare_time_2 = $_POST["prepare_time_2"];
        $design_time_1 = $_POST["design_time_1"] . ":00";
        $design_time_2 = $_POST["design_time_2"] . ":00";
        $quantity = $_POST["quantity"];
        $publisheds = $_POST["publisheds"];
        $unpublisheds = $_POST["unpublisheds"];
        $f_a_com_fixed_price = $_POST["f_a_com_fixed_price"];
        $f_a_am_fixed_price = $_POST["f_a_am_fixed_price"];
        $f_a_com_procent = $_POST["f_a_com_procent"];
        $f_a_am_procent = $_POST["f_a_am_procent"];
        $anahit_procent = $_POST["anahit_procent"];
        $high_price_partners_procent = $_POST["high_price_partners_procent"];
        $low_cost_partners_procent = $_POST["low_cost_partners_procent"];
        $title_emoji = $_POST["title_emoji"];
        $brand_name = $_POST["brand_name"];
        $title_keyword = $_POST["title_keyword"];
        $addNewSkuDependPrice = $_POST["addNewSkuDependPrice"];
        $ru_rod = $_POST["ru_rod"];
        $fr_rod = $_POST["fr_rod"];
        $de_rod = $_POST["de_rod"];
        $atg_code = $_POST["atg_code"];
        $addIconImage = $_POST["addIconImage"];
        $dependent_price = $_POST["dependent_price"];
        $everytimeAvailable = $_POST["everytimeAvailable"];
        $depends_from_price = 0;
        if($addNewSkuDependPrice == 'yes'){
            $depends_from_price = 1;
        }
        else{
            $dependent_price = '';
        }
        $everytime_available = 0;
        if($everytimeAvailable == 'yes'){
            $everytime_available = 1;
        }
        $query = "INSERT INTO sku_info (sku_code,am_title, ru_title, en_title, spa_title, de_title, fr_title,am_desc, ru_desc, en_desc, spa_desc, de_desc, fr_desc, prepare_time_1,prepare_time_2,design_time_1,design_time_2,dependent_price,depends_from_price,everytime_available,quantity,publisheds,unpublisheds,f_a_com_fixed_price,f_a_am_fixed_price,f_a_com_procent,f_a_am_procent,anahit_procent,high_price_partners_procent,low_cost_partners_procent,title_emoji,brand_name,title_keyword,icon,ru_rod,fr_rod,de_rod,atg_code) VALUES ('" . $sku_code . "','" . $am_title . "', '" . $ru_title . "' , '" . $en_title ."' , '" . $spa_title . "' , '" . $de_title . "',  '" . $fr_title . "','" . $am_desc . "', '" . $ru_desc . "' , '" . $en_desc ."' , '" . $spa_desc . "' , '" . $de_desc . "',  '" . $fr_desc . "', '" . $prepare_time_1 . "', '" . $prepare_time_2 . "', '" . $design_time_1 . "', '" . $design_time_2 . "', '" . $dependent_price ."', '" . $depends_from_price ."','" . $everytime_available ."', '" . $quantity ."', '" . $publisheds ."', '" . $unpublisheds ."', '" . $f_a_com_fixed_price ."', '" . $f_a_am_fixed_price ."', '" . $f_a_com_procent ."', '" . $f_a_am_procent ."', '" . $anahit_procent ."', '" . $high_price_partners_procent ."','" . $low_cost_partners_procent ."', '" . $title_emoji ."', '" . $brand_name ."', '" . $title_keyword ."', '" . $addIconImage ."', '" . $ru_rod ."', '" . $fr_rod ."', '" . $de_rod ."', '" . $atg_code ."')";
        getwayConnect::getwaySend($query);
        return true;
    }