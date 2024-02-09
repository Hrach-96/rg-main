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
$travel_operators = array(1, 2, 78);
$strict_country = ($user_country > 0) ? 'AND `delivery_region` = 4 ' : '';
$root = true;

$ordersId = $_REQUEST['ordersArray'];
$language = $_REQUEST['language'];
$fields = $_REQUEST['fields'];
$ordersIdArray = explode(',',$ordersId);
$fieldsArray = explode(',',$fields);

$array_column_names = [];
if (in_array("id", $fieldsArray)){
    array_push($array_column_names,'Id');
}
if (in_array("full_name", $fieldsArray)){
    array_push($array_column_names,'Full Name');
}
if (in_array("first_name", $fieldsArray)){
    array_push($array_column_names,'First Name');
}
if (in_array("last_name", $fieldsArray)){
    array_push($array_column_names,'Last Name');
}
if (in_array("phone", $fieldsArray)){
    array_push($array_column_names,'Phone');
}
if (in_array("email", $fieldsArray)){
    array_push($array_column_names,'Email');
}
array_push($array_column_names,'Country');
array_push($array_column_names,'ZIP');
$data = [
    $array_column_names
];
$printed_orders_count = 0;
    foreach($ordersIdArray as $key=>$value){
        $order_info = getwayConnect::getwayData("SELECT sender_email,sender_phone,sender_name FROM rg_orders where rg_orders.id='{$value}'");
        $sender_name_order = $order_info[0]['sender_name'];
        $sender_phone_order = $order_info[0]['sender_phone'];
        $sender_email_order = $order_info[0]['sender_email'];
        $first_sender_name_order_finally = '';
        $last_sender_name_order_finally = '';
        $array_value_names = [];
        
        if ($sender_name_order == trim($sender_name_order) && strpos($sender_name_order, ' ') !== false) {
            $full_sender_name_order = explode(' ' , $sender_name_order);
            $first_sender_name_order = $full_sender_name_order[0];
            $last_sender_name_order = $full_sender_name_order[1];
            $first_tranaslated_name = get_first_name_by_value($first_sender_name_order,$language);
            if(!empty($first_tranaslated_name)){
                $first_sender_name_order_finally = $first_tranaslated_name[0]['first_name_'.$language];
            }
            else{
                $first_sender_name_order_finally = $first_sender_name_order;
            }
            $last_tranaslated_name = get_last_name_by_value($last_sender_name_order,$language);
            if(!empty($last_tranaslated_name)){
                $last_sender_name_order_finally = $last_tranaslated_name[0]['last_name_'.$language];
            }
            else{
                $last_sender_name_order_finally = $last_sender_name_order;
            }
        }
        else if($sender_name_order != ''){
            $first_tranaslated_name = get_first_name_by_value($sender_name_order,$language);
            if(!empty($first_tranaslated_name)){
                $first_sender_name_order_finally = $first_tranaslated_name[0]['first_name_'.$language];
            }
            else{
                $first_sender_name_order_finally = $sender_name_order;
            }
        }
        $sender_name_order = $first_sender_name_order_finally . " " . $last_sender_name_order_finally;
        if($sender_email_order != '' || $sender_phone_order != ''){
            $printed_orders_count ++;
            if (in_array("id", $fieldsArray)){
                array_push($array_value_names,$value);
            }
            if (in_array("full_name", $fieldsArray)){
                array_push($array_value_names,$sender_name_order);
            }
            if (in_array("first_name", $fieldsArray)){
                array_push($array_value_names,$first_sender_name_order_finally);
            }
            if (in_array("last_name", $fieldsArray)){
                array_push($array_value_names,$last_sender_name_order_finally);
            }
            if (in_array("phone", $fieldsArray)){
                array_push($array_value_names,$sender_phone_order);
            }
            if (in_array("email", $fieldsArray)){
                array_push($array_value_names,$sender_email_order);
            }
            $data[] = $array_value_names;
        }
    }
    $file = 'User list ( count - ' . $printed_orders_count .' ) ' . date("Y-m-d H-i-s") .  '.csv';
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
    
    // $file_path = $file;
    // $string = file_get_contents($file_path, FILE_USE_INCLUDE_PATH);
    // echo $string;
    // echo "<br><br><br>";
    // $string2 = str_replace('"', " ", $string);
    // echo $string2;
    // file_put_contents($file_path, $string2); 
    // readfile($file);

    unlink($file);

    function get_first_name_by_value($first_name,$language){
        return getwayConnect::getwayData("SELECT first_name_" . $language . " FROM translate_of_names where  ( first_name_eng = '" . $first_name . "' or first_name_rus = '" . $first_name . "' or first_name_arm = '" . $first_name . "') ");
    }
    function get_last_name_by_value($last_name,$language){
        return getwayConnect::getwayData("SELECT last_name_" . $language . " FROM translate_of_names where  ( last_name_eng = '" . $last_name . "' or last_name_rus = '" . $last_name . "' or last_name_arm = '" . $last_name . "') ");
    }
?>