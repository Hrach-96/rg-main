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
        include("lang/language_am.php");
    }
}
$travel_operators = array(1, 2, 78);
$strict_country = ($user_country > 0) ? 'AND `delivery_region` = 4 ' : '';
$root = true;
include("products/engine/engine.php");
include("products/engine/storage.php");

$operators = getwayConnect::getwayData("SELECT * FROM `user`");
$operators_array;
foreach($operators as $key=>$value){
    $operators_array[$value['username']] = $value;
}

$ordersId = $_REQUEST['ordersArray'];
$ordersIdArray = explode(',',$ordersId);
    $data = [
        [ 
            'Id','Մեկնման օրը','Ժամը','Երկիր','Ստացող','Գին','Արտարժույթ','AMD Գին','Ստացողի Համայնքը','Ստացողի Փողոցը','Ստացողի շենք, Բնակարան, Հարկ, Մուտք, Կոդ','Առաքիչ','Բողոքի / Դժգոհության տեսակը','Ստատուս','Աղբյուր','Աղբյուրի տվյալներ','Վճարման Ձև','Վճարման տվյալներ','Բոնուս / Մալուս / Ոչինչ','Ուղարկողի Անուն','Ուղարկողի Երկիր','Վաճառքի կետ','Մատակարար','Պատասխանատու','Կրկնակի ստուգված','ՀԱ N','ՀՎՀՀ','Օպերատոր','Ուղարկողի Հեռ․','Ուղարկողի Էլ․ Փոստ','Բացիկի տեքստ','Ստուգողի Մեկնաբանություն:','Օպերատորի Նշումներ','Ցուցում Վարորդին','Վճարել','Հաստատել','Առաքում',
        ]
    ];
    foreach($ordersIdArray as $key=>$value){
        $order_info = getwayConnect::getwayData("SELECT delivery_date,notes_for_florist,notes,greetings_card,controller_note,operator,sender_email,sender_phone,delivery_time,delivery_region,receiver_name,price,currency,total_price_amd,payment_type,receiver_subregion,receiver_street,receiver_address,receiver_floor,receiver_tribute,receiver_entrance,receiver_door_code,deliverer,delivery_status,order_source,order_source_optional,payment_optional,bonus_type,sender_name,sender_country,sell_point,flourist_id,operator_name,confirmed,delivery_time.name FROM rg_orders LEFT JOIN delivery_time on rg_orders.delivery_time = delivery_time.id where rg_orders.id='{$value}'");
        $delivery_street = '';
        $sql = "SELECT * FROM delivery_street WHERE code = '" . $order_info[0]['receiver_street'] . "'";
        $delivery_street = getwayConnect::getwayData($sql);
        $deliverer = '';
        $sql = "SELECT * FROM delivery_deliverer WHERE id = '" . $order_info[0]['deliverer'] . "'";
        $deliverer = getwayConnect::getwayData($sql);
        $hdm_hvhh_info = '';
        $sql = "SELECT * FROM tax_numbers_of_check WHERE order_id = '" . $value . "'";
        $hdm_hvhh_info = getwayConnect::getwayData($sql);

        $complain = '';
        $sql = "SELECT * FROM complain_of_orders left join complain_types on complain_of_orders.type_id = complain_types.id WHERE complain_of_orders.order_id = '" . $value . "'";
        $complain = getwayConnect::getwayData($sql);

        $delivery_status = '';
        $sql = "SELECT * FROM delivery_status WHERE id = '" . $order_info[0]['delivery_status'] . "'";
        $delivery_status = getwayConnect::getwayData($sql);
        $order_source = '';
        $sql = "SELECT * FROM delivery_source WHERE id = '" . $order_info[0]['order_source'] . "'";
        $order_source = getwayConnect::getwayData($sql);
        $sender_country = '';
        $sql = "SELECT * FROM countries WHERE id = '" . $order_info[0]['sender_country'] . "'";
        $sender_country = getwayConnect::getwayData($sql);
        $payment_type = '';
        $sql = "SELECT * FROM delivery_payment WHERE id = '" . $order_info[0]['payment_type'] . "'";
        $payment_type = getwayConnect::getwayData($sql);
        $sell_point = '';
        $sql = "SELECT * FROM delivery_sellpoint WHERE id = '" . $order_info[0]['sell_point'] . "'";
        $sell_point = getwayConnect::getwayData($sql);
        $flourist_id = '';
        $sql = "SELECT * FROM user WHERE id = '" . $order_info[0]['flourist_id'] . "'";
        $flourist_id = getwayConnect::getwayData($sql);
        $operator_name = '';
        $sql = "SELECT * FROM user WHERE id = '" . $order_info[0]['operator_name'] . "'";
        $operator_name = getwayConnect::getwayData($sql);
        $bonus_type = 'Բոնուս';
        if($order_info[0]['bonus_type'] == 1){
            $bonus_type = 'Բոնուս';
        }
        else if($order_info[0]['bonus_type'] == 2){
            $bonus_type = 'Մալուս';
        }
        else if($order_info[0]['bonus_type'] == 3){
            $bonus_type = 'Ոչինչ';
        }
        $delivery_region = 'Հայաստան';
        if($order_info[0]['delivery_region'] == 1){
            $delivery_region = 'Հայաստան';
        }
        else if($order_info[0]['delivery_region'] == 2){
            $delivery_region = 'Ֆրանսիա';
        }
        else if($order_info[0]['delivery_region'] == 3){
            $delivery_region = 'Մոսկվա';
        }
        else if($order_info[0]['delivery_region'] == 4){
            $delivery_region = 'Իսպանիա';
        }
        else if($order_info[0]['delivery_region'] == 5){
            $delivery_region = 'Արտերկիր(հիմա չունենք)';
        }
        else if($order_info[0]['delivery_region'] == 6){
            $delivery_region = 'Tehran';
        }
        $currency = 'AMD';
        if($order_info[0]['currency'] == 3){
            $currency = 'AMD';
        }
        else if($order_info[0]['currency'] == 4){
            $currency = 'EUR';
        }
        else if($order_info[0]['currency'] == 5){
            $currency = 'GBP';
        }
        else if($order_info[0]['currency'] == 6){
            $currency = 'IRR';
        }
        else if($order_info[0]['currency'] == 2){
            $currency = 'RUB';
        }
        else if($order_info[0]['currency'] == 1){
            $currency = 'USD';
        }
        $sender_phone = 'N';
        $sender_email = 'N';
        if($order_info[0]['sender_phone'] != ''){
            $sender_phone = 'Y';
        }
        if($order_info[0]['sender_email'] != ''){
            $sender_email = 'Y';
        }
        $greetings_card = '';
        if($order_info[0]['greetings_card'] > 0){
            $greetings_card = getwayConnect::getwayData("SELECT * FROM `order_notes` WHERE `order_id` = '{$value}' and type_id='1' ")[0]['value'];
        }
        $controller_note = '';
        if($order_info[0]['controller_note'] > 0){
            $controller_note = getwayConnect::getwayData("SELECT * FROM `order_notes` WHERE `order_id` = '{$value}' and type_id='4' ")[0]['value'];
        }
        $notes = '';
        if($order_info[0]['notes'] > 0){
            $notes = getwayConnect::getwayData("SELECT * FROM `order_notes` WHERE `order_id` = '{$value}' and type_id='2' ")[0]['value'];
        }
        $notes_for_florist = '';
        if($order_info[0]['notes_for_florist'] > 0){
            $notes_for_florist = getwayConnect::getwayData("SELECT * FROM `order_notes` WHERE `order_id` = '{$value}' and type_id='3' ")[0]['value'];
        }
        $mail_log = getwayConnect::getwayData("SELECT content_type, `count` FROM mail_log where order_id={$value}");
        $mail_logs = [];
        foreach($mail_log as $mail_count){
            $mail_logs[$mail_count['content_type']] = $mail_count['count'];
        }
        $vjarel_notification = '';
        $hastatvac_notification = '';
        $araqum_notification = '';
        if($mail_logs[1]){
            $vjarel_notification = $mail_logs[1];
        }
        if($mail_logs[2]){
            $hastatvac_notification = $mail_logs[2];
        }
        if($mail_logs[5]){
            $araqum_notification = $mail_logs[5];
        }
        $data[] = [$value,$order_info[0]['delivery_date'],$order_info[0]['name'],$delivery_region,$order_info[0]['receiver_name'],$order_info[0]['price'],$currency,$order_info[0]['total_price_amd'],$order_info[0]['receiver_subregion'],$delivery_street[0]['name'],$order_info[0]['receiver_address']. ", " . $order_info[0]['receiver_floor'] . ', ' . $order_info[0]['receiver_tribute'] . ", " . $order_info[0]['receiver_entrance']. ', ' . $order_info[0]['receiver_door_code'],$deliverer[0]['full_name'],$complain[0]['type'] . "(" . $complain[0]['reason'] .")" ,$delivery_status[0]['name_am'],$order_source[0]['name'],$order_info[0]['order_source_optional'],$payment_type[0]['name_am'] ,$order_info[0]['payment_optional'],$bonus_type,$order_info[0]['sender_name'],$sender_country[0]['name_am'],$sell_point[0]['name'],ucfirst($flourist_id[0]['username']),ucfirst($operator_name[0]['username']),$order_info[0]['confirmed'],$hdm_hvhh_info[0]['hdm_tax'],$hdm_hvhh_info[0]['hvhh_tax'],($operators_array[$order_info[0]['operator']]['full_name_am'] != '')?$operators_array[$order_info[0]['operator']]['full_name_am'] : $operators_array[$order_info[0]['operator']]['username'],$sender_phone,$sender_email,$greetings_card,$controller_note,$notes,$notes_for_florist,$vjarel_notification,$hastatvac_notification,$araqum_notification];
    }
    $file = 'Order list ' . date("Y-m-d H-i-s") .  '.csv';
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
?>