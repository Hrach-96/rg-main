<?php
session_start();
$pageName = "flower";
$rootF = "../..";

//by Haykaz
include_once $_SERVER['DOCUMENT_ROOT']."/controls/FlowersForms.php";
////H

include($rootF . "/apay/pay.api.php");
include($rootF . "/configuration.php");

page::cmd();

$access = auth::checkUserAccess($secureKey);
$allData = array();
$buildClient = "";
$uid = "";
$level = "";
$userData = "";
$cc = "am";
$user_country = '0';
if (!$access) {
    header("location:../../login");
} else {
    $uid = $_COOKIE["suid"];
    $level = auth::getUserLevel($uid);
    page::accessByLevel($level[0]["user_level"], $pageName);
    $levelArray = explode(",", $level[0]["user_level"]);
    $userData = auth::checkUserExistById($uid);
    $cc = $userData[0]["lang"];
    $user_country = $userData[0]["country_short"];
    if (is_file("lang/language_{$cc}.php")) {
        include("lang/language_{$cc}.php");
    } else {
        include("../flower_orders/lang/language_am.php");
    }
}
if(isset($_REQUEST['download_region_data'])){
	$daInfo = getwayConnect::getwayData("SELECT * FROM `delivery_street` WHERE `sub_code` = '{$_REQUEST['region']}' ORDER BY `name_en`");
	$array_column_names = [];
	array_push($array_column_names,'#');
	array_push($array_column_names,'Name');
	array_push($array_column_names,'Sub Code');
	array_push($array_column_names,'Old Name');
	array_push($array_column_names,'Name Ru');
	array_push($array_column_names,'Name En');
	array_push($array_column_names,'Distance');
	array_push($array_column_names,'Delivery Time');
	array_push($array_column_names,'Zone');
	array_push($array_column_names,'Wiki Url');
	array_push($array_column_names,'Delivery Price');
	array_push($array_column_names,'Coordinates');
	$data = [
	    $array_column_names
	];
    foreach($daInfo as $key=>$value){
        $array_value_names = [];
        $updated_by = '';
        if($value['updated_by']){
            $updated_by = getwayConnect::getwayData("SELECT * FROM `user` WHERE `id` = '{$value['updated_by']}'")[0]['full_name_am'];
        }
		array_push($array_value_names,$value['id']);
		array_push($array_value_names,$value['name']);
		array_push($array_value_names,$value['name']);
		array_push($array_value_names,$value['sub_code']);
		array_push($array_value_names,$value['code']);
		array_push($array_value_names,$value['old_name']);
		array_push($array_value_names,$value['name_ru']);
		array_push($array_value_names,$value['name_en']);
		array_push($array_value_names,$value['distance']);
		array_push($array_value_names,$value['delivery_time']);
		array_push($array_value_names,$value['zone']);
		array_push($array_value_names,$value['wiki_url']);
		array_push($array_value_names,$value['delivery_price']);
		array_push($array_value_names,$value['coordinates']);
		$data[] = $array_value_names;
	}
	$file = 'Regions  - ' . date("Y-m-d H-i-s") .  '.csv';
    $first_csv = fopen($file,'a');
    foreach ($data as $fields) {
        fprintf($first_csv, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($first_csv, $fields);
    }
    fclose($first_csv);
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='.basename($file));
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    ob_clean();
    flush();
    readfile($file);
    unlink($file);
}
if(isset($_REQUEST['download_sku_excell'])){
	$query_result = getwayConnect::$db->query("SELECT * FROM sku_info");
	$total_result = [];
	foreach ($query_result as $row) {
	    $total_result[] = $row;
	}

	$array_column_names = [];
	array_push($array_column_names,'#');
	array_push($array_column_names,'SKU Code');
	array_push($array_column_names,'Brand Name');
	array_push($array_column_names,'Title Keyword');
	array_push($array_column_names,'Title Emoji');
	array_push($array_column_names,'Quantity');
	array_push($array_column_names,'Depend From Price');
	array_push($array_column_names,'Depended Price');
	array_push($array_column_names,'Everythime Available');
	array_push($array_column_names,'Prepare Time 1');
	array_push($array_column_names,'Prepare Time 2');
	array_push($array_column_names,'Design Time 1');
	array_push($array_column_names,'Design Time 2');
	array_push($array_column_names,'Am Title');
	array_push($array_column_names,'Am Desc');
	array_push($array_column_names,'Ru Title');
	array_push($array_column_names,'Ru Desc');
	array_push($array_column_names,'En Title');
	array_push($array_column_names,'En Desc');
	array_push($array_column_names,'Spa Title');
	array_push($array_column_names,'Spa Desc');
	array_push($array_column_names,'De Title');
	array_push($array_column_names,'Fr Title');
	array_push($array_column_names,'Fr Desc');
	array_push($array_column_names,'De Desc');
	array_push($array_column_names,'Publisheds');
	array_push($array_column_names,'Unpublisheds');
	array_push($array_column_names,'F-A.com fixed price');
	array_push($array_column_names,'F-A.am Fixed price');
	array_push($array_column_names,'F-A.com %');
	array_push($array_column_names,'F-A.am %');
	array_push($array_column_names,'Anahit %');
	array_push($array_column_names,'High-price Partners %');
	array_push($array_column_names,'Low-cost Partners %');
	array_push($array_column_names,'Low-cost Partners %');
	$data = [
	    $array_column_names
	];
    foreach($total_result as $key=>$value){
        $array_value_names = [];
		array_push($array_value_names,$value['id']);
		array_push($array_value_names,$value['sku_code']);
		array_push($array_value_names,$value['brand_name']);
		array_push($array_value_names,$value['title_keyword']);
		array_push($array_value_names,$value['title_emoji']);
		array_push($array_value_names,$value['quantity']);
		if($value['depends_from_price'] == 1){
			array_push($array_value_names,'Yes');
        }
        else{
			array_push($array_value_names,'No');
        }
		array_push($array_value_names,$value['dependent_price']);
		if($value['everytime_available'] == 1){
			array_push($array_value_names,'Yes');
        }
        else{
			array_push($array_value_names,'No');
        }
		array_push($array_value_names,$value['prepare_time_1']);
		array_push($array_value_names,$value['prepare_time_2']);
		array_push($array_value_names,$value['design_time_1']);
		array_push($array_value_names,$value['design_time_2']);
		array_push($array_value_names,$value['am_title']);
		array_push($array_value_names,$value['am_desc']);
		array_push($array_value_names,$value['ru_title']);
		array_push($array_value_names,$value['ru_desc']);
		array_push($array_value_names,$value['en_title']);
		array_push($array_value_names,$value['en_desc']);
		array_push($array_value_names,$value['spa_title']);
		array_push($array_value_names,$value['spa_desc']);
		array_push($array_value_names,$value['de_title']);
		array_push($array_value_names,$value['de_desc']);
		array_push($array_value_names,$value['fr_title']);
		array_push($array_value_names,$value['fr_desc']);
		array_push($array_value_names,$value['publisheds']);
		array_push($array_value_names,$value['unpublisheds']);
		array_push($array_value_names,$value['f_a_com_fixed_price']);
		array_push($array_value_names,$value['f_a_am_fixed_price']);
		array_push($array_value_names,$value['f_a_com_procent']);
		array_push($array_value_names,$value['f_a_am_procent']);
		array_push($array_value_names,$value['anahit_procent']);
		array_push($array_value_names,$value['high_price_partners_procent']);
		array_push($array_value_names,$value['low_cost_partners_procent']);
		$data[] = $array_value_names;
    }
    $file = 'Sku Info  - ' . date("Y-m-d H-i-s") .  '.csv';
    $first_csv = fopen($file,'a');
    foreach ($data as $fields) {
        fprintf($first_csv, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($first_csv, $fields);
    }
    fclose($first_csv);
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='.basename($file));
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    ob_clean();
    flush();
    readfile($file);
    unlink($file);
}
?>