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

storage::$user_id = $userData[0]['id'];
if (isset($_SESSION['storage'])) {
    storage::$selected_storage = $_SESSION['storage'];
} else {
    storage::$selected_storage = storage::get_default();
}
if (!storage::user_storage_enabled()) {
    storage::$selected_storage = storage::get_default();
}
if(strpos($userData[0]['user_level'], '222') !== false){
    header("location:../../info/view.php");
}
$engine = new engine();
$get_lvl = explode(',', $level[0]["user_level"]);
//empty(array_intersect(array(89),explode(",",$get_lvl[0])))
$regionData = page::getRegionFromCC($cc);
date_default_timezone_set("Asia/Yerevan");
$pahest = (strtolower($cc) != 'am') ? '`country` = {$cc}' : '';
$access_token_parameters = array();
$curl = curl_init("http://new.regard-group.ru/currency.php");
curl_setopt($curl,CURLOPT_POST,true);
curl_setopt($curl,CURLOPT_POSTFIELDS,$access_token_parameters);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
$currencyResult = curl_exec($curl);
curl_close($curl);
$currencyValues = json_decode($currencyResult);
function GetExchangeForCurrency($currency){
    $access_token_parameters = array(
        'ISO'    =>  $currency,
    );
    $curl = curl_init("http://anahit.am/apay/cba.php");    // we init curl by passing the url
    curl_setopt($curl,CURLOPT_POST,true);   // to send a POST request
    curl_setopt($curl,CURLOPT_POSTFIELDS,$access_token_parameters);   // indicate the data to send
    curl_setopt($curl,CURLOPT_TIMEOUT,1);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);   // to return the transfer as a string of the return value of curl_exec() instead of outputting it out directly.
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);   // to stop cURL from verifying the peer's certificate.
    $result = curl_exec($curl);   // to perform the curl session
    curl_close($curl);   // to close the curl session
    
    return ($result = json_decode($result)) ? $result->Rate : 0;
}

       /* RUB_currency = <?= GetExchangeForCurrency('RUB') ?> || RUB_currency;
        USD_currency = <?= GetExchangeForCurrency('USD') ?> || USD_currency;
        EUR_currency = <?= GetExchangeForCurrency('EUR') ?> || EUR_currency;
        IRR_currency = <?= GetExchangeForCurrency('IRR') ?> || IRR_currency;
        GBP_currency = <?= GetExchangeForCurrency('GBP') ?> || GBP_currency;  */
/////by Haykaz
function getConstant($value){
    if (defined($value)) { 
        return constant($value);
    } else {
        return $value;
    }
}
$stream_opts = [
    "ssl" => [
        "verify_peer"=>false,
        "verify_peer_name"=>false,
    ]
];
$driversArray = FlowersForm::getDriversList();
$selectionOption = "";

foreach ($driversArray  as $keyDriver => $valueDriver){
    if($valueDriver['id'] >= 20){
        $selectionOption .= '<option value="'.$keyDriver.'">'. $valueDriver["name"].'</option>';
    }
    else{
        $selectionOption .= '<option value="'.$keyDriver.'">'. $valueDriver["full_name"].'</option>';
    }
}
$reasonsArray = FlowersForm::getDeliveryReasonList();
$selectionOptionReason = "";

foreach ($reasonsArray  as $keyReason => $valueReason){
    $selectionOptionReason .= '<option value="'.$keyReason.'">'. getConstant($valueReason["name"]).'</option>';
}
$languagePrimaryArray = FlowersForm::getLanguagePrimary();
$selectionOptionPrimaryLanguage = "";

foreach ($languagePrimaryArray  as $keyPrimaryLanguage => $valuePrimaryLanguage){
    $selectionOptionPrimaryLanguage .= '<option value="'.$keyPrimaryLanguage.'">'. getConstant($valuePrimaryLanguage["name"]).'</option>';
}
$whoReceivedArray = FlowersForm::getWhoReceveid();
$selectionOptionWhoReceived = "";

foreach ($whoReceivedArray  as $keyWhoReceived => $valueWhoReceived){
    $selectionOptionWhoReceived .= '<option value="'.$keyWhoReceived.'">'. getConstant($valueWhoReceived["name"]).'</option>';
}
/// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
 
//$driversArray = FlowersForms::getDriversList() ;
//print_r($driversArray);
///H

$query_flourists = "SELECT * FROM user where (id = 27 OR user_level=30) AND user_active=1 ";
$flourists = getwayConnect::getwayData($query_flourists,PDO::FETCH_ASSOC);

$florist_filter_query = "SELECT * FROM user where user_level = 30 AND user_active=1";
$flourist_filters = getwayConnect::getwayData($florist_filter_query,PDO::FETCH_ASSOC);

$operator_filter_query = "SELECT * FROM user where user_level = 36 AND user_active=1";
$operator_filters = getwayConnect::getwayData($operator_filter_query,PDO::FETCH_ASSOC);

$deliverer_filter_query = "SELECT * FROM delivery_deliverer where active=1 and cc='am'";
$deliverer_filters = getwayConnect::getwayData($deliverer_filter_query,PDO::FETCH_ASSOC);

$products = getwayConnect::getwayData('SELECT orders_products.*, orders_products_data.product_image as product_image, 
        orders_products_data.product_name as product_name, orders_products_data.product_description as product_desciption FROM orders_products 
        RIGHT JOIN orders_products_data on orders_products.product_data_id = orders_products_data.id
        RIGHT JOIN storage_product on orders_products.id = storage_product.product_id AND storage_id = 1
        where pimportant = 1');

if(isset($_GET) && isset($_GET['product_id'])){
    $data = getwayConnect::getwayData("SELECT jos_vm_product.*, jos_vm_product_price.* from jos_vm_product
        RIGHT JOIN jos_vm_product_price on jos_vm_product.product_id = jos_vm_product_price.product_id
        WHERE jos_vm_product.product_id IN (SELECT related_product_id from product_related where product_id = '{$_GET["product_id"]}') order by product_price
    ");
    echo json_encode($data);
    exit;
}
if(isset($_REQUEST['checkImages']) && $_REQUEST['checkImages']){
    $checkImage = getwayConnect::getwayData("SELECT * from order_related_products where order_id='{$_REQUEST['id']}'");
    $delImage = getwayConnect::getwayData("SELECT * from delivery_images where rg_order_id='{$_REQUEST['id']}'");
    if((isset($checkImage) && !empty($checkImage[0]) && $checkImage[0]['jos_vm_product_id'] != '') || (isset($delImage) && !empty($delImage[0]))){
        echo json_encode(array('showImages' => true));
    } else {
        echo json_encode(array('showImages' => false));
    }
    exit;
}
if(isset($_REQUEST['getOrderInfo']) && $_REQUEST['getOrderInfo']){
    $sql = "SELECT * FROM rg_orders where id = " . $_REQUEST['order_id'];
    $orderInfo = getwayConnect::getwayData($sql);
    echo json_encode($orderInfo[0]);die;

}
if(isset($_REQUEST['getUnreadPosts']) && $_REQUEST['getUnreadPosts']){
    $user_id = $userData[0]['id'];
    $userPosition = getUserPosition($userData[0]);
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
// Added By Hrach 08/12/19
if(isset($_REQUEST['checkpending']) && $_REQUEST['checkpending']){
    $orderInfos = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_status = 2 and id > 60000 and control_pending = 0");
    if(!empty($orderInfos)){
        foreach( $orderInfos as $key => $value ){
            $overminutes =  strtotime(date('Y-m-d H:i:s')) - strtotime($value['created_date']  . " " .$value['created_time']);
            if( $overminutes > 1800 ){
                getwayConnect::getwaySend("UPDATE rg_orders set control_pending='1' where id='{$value['id']}'");
            }
        }
    }
}

if(isset($_REQUEST['checkAnavartLikeCron']) && $_REQUEST['checkAnavartLikeCron']){
    $userName = $userData[0]['username'];
    $user_id = $userData[0]['id'];
    // $userName = 'rg006';
    // $user_id = 30;
    $result = "";
    if($_COOKIE['check_anavart_for_send_user_' . $user_id]){

    }
    else{
        setcookie('check_anavart_for_send_user_' . $user_id, true, time() + (3400), "/");
        $getAnavartOrders = getwayConnect::getwayData("SELECT * FROM rg_orders where operator_name='" . $userName . "' and delivery_status='2'");
        if(count($getAnavartOrders) > 0){
            $orderVariables = "";
            $orderWithoutLinksVariables = "";
            foreach($getAnavartOrders as $key=>$value){
                $todayAnavartNshum = getwayConnect::getwayData("SELECT * FROM pending_info where order_id = '" . $value['id'] . "' and operator_id = '" . $user_id . "' and created_date > '" . date('Y-m-d') . " 00:00:00'");
                if(!$todayAnavartNshum){
                    $orderVariables.="<a target='_blank' href='https://new.regard-group.ru/account/flower_orders/order.php?orderId=" . $value['id'] . "'>" . $value['id'] . "</a>,";
                    $orderWithoutLinksVariables.= $value['id'] . ",";
                }
            }
            if($orderVariables != ""){
                $orderVariables = substr($orderVariables, 0, -1);
                $orderWithoutLinksVariables = substr($orderWithoutLinksVariables, 0, -1);
                $result = " Հարգելի " . explode(' ',$userData[0]['full_name_am'])[0] . " դուք ունեք կցված " . $orderWithoutLinksVariables . " Անավարտ պատվերներ որոնցով հարկավոր է այսօր զբաղվել   ";
                //$to      = 'notification@regard-group.com';
                $to      = 'delivery@flowers-armenia.am';
                // $to      = 'jigyarov.jigyar@gmail.com';
                $subject =  "Անավարտի ծանուցում " . explode(' ',$userData[0]['full_name_am'])[0] . " - ի համար";
                $message = " Հարգելի " . explode(' ',$userData[0]['full_name_am'])[0] . " դուք ունեք կցված " . $orderVariables . " Անավարտ պատվերներ որոնցով հարկավոր է այսօր զբաղվել   ";
                $message.=  "<br><br> RG System checking notification time: <span style='color:grey'> " . date('d-M-Y H:i:s') . "</span>";
                $headers = 'From: autocontrol@regard-group.com' . "\r\n" .
                    'Reply-To: autocontrol@regard-group.com' . "\r\n" .
                    'Content-Type: text/html; charset=UTF-8' . "\r\n" .
                    'Bcc: hrach.avvagyan@gmail.com' . "\r\n" .
                    'X-Mailer: PHP/' . phpversion();
                $headers .= "";
                mail($to, $subject, $message, $headers);
            }
        }
    }
    print $result;die;
}
if(isset($_REQUEST['showalertnotification']) && $_REQUEST['showalertnotification']){
    $pending_orders_info = getwayConnect::getwayData("SELECT * FROM rg_orders where operator_name='{$userData[0]['username']}' and id > 60000 and delivery_status = '2'");
    if(count($pending_orders_info) > 0){
        $currentMinute = date('i');
        if($currentMinute == '01'){
            $orders = [];
            $orders['operator_name'] = $userData[0]['full_name_am'];
            $orders['orders'] = [];
            foreach( $pending_orders_info as $key=>$value ){
                $currency = '';
                if($value['currency'] == '1'){
                    $currency = "$";
                }
                else if ($value['currency'] == '2'){
                    $currency = '₽';
                }
                else if ($value['currency'] == '3'){
                    $currency = '֏';
                }
                else if ($value['currency'] == '4'){
                    $currency = '€';
                }
                $orders['orders'][] = $value['id'] . " ( " . $value['price'] . "  " . $currency .  " ) ";
            }
            print json_encode($orders);die;
        }
    }
    return false;
}
if(isset($_REQUEST['get_operators_info']) && $_REQUEST['get_operators_info']){
    $operators = getwayConnect::getwayData("SELECT * FROM `user`");
    $operators_array;
    foreach($operators as $key=>$value){
        $operators_array[$value['username']] = $value;
    }
    print json_encode($operators_array);die;
}
$lastStart = getwayConnect::getwayData("SELECT * from worked_hours where user_id = '" . $userData[0]['id'] . "' order by id desc limit 1");
if(isset($_REQUEST['add_start_time']) && $_REQUEST['add_start_time']){
    getwayConnect::getwaySend("INSERT INTO worked_hours (start_date,start_time,end_time,user_id) VALUES ('" . date("y:m:d") . "','" . $_REQUEST['start_time']."','','" . $userData[0]['id'] . "')");
    return true;
}
if(isset($_REQUEST['add_end_time']) && $_REQUEST['add_end_time']){
    $end_time = $_REQUEST['end_time'];
    if($end_time == "00:00:00"){
        $end_time = "00:00:15";
    }
    $total_hours = strtotime(date("y-m-d") . " " . $end_time) - strtotime($lastStart[0]['start_date'] . " " . $lastStart[0]['start_time']);
    getwayConnect::getwaySend("UPDATE worked_hours set end_date='" . date("y:m:d") . "', total_worked = '" . $total_hours . "', end_time='" . $end_time . "' where id = '" . $_REQUEST['date_id'] . "'");
    return true;
}
if(isset($_REQUEST['getNoteForOrder']) && $_REQUEST['getNoteForOrder']){
    $order_id = $_REQUEST['order_id'];
    $notes_type = getwayConnect::getwayData("SELECT * FROM `order_notes_types` WHERE `type` = 'notes'");
    $notes_row = getwayConnect::getwayData("SELECT * FROM `order_notes` WHERE `type_id` = '{$notes_type[0]['id']}' and order_id = '{$order_id}'");
    print json_encode($notes_row);die;
}
if(isset($_REQUEST['getGreetingCardForOrder']) && $_REQUEST['getGreetingCardForOrder']){
    $order_id = $_REQUEST['order_id'];
    $greeting_card_type = getwayConnect::getwayData("SELECT * FROM `order_notes_types` WHERE `type` = 'greetings_card'");
    $greeting_card_row = getwayConnect::getwayData("SELECT * FROM `order_notes` WHERE `type_id` = '{$greeting_card_type[0]['id']}' and order_id = '{$order_id}'");
    print json_encode($greeting_card_row);die;
}
if(isset($_REQUEST['getFloristNoteForOrder']) && $_REQUEST['getFloristNoteForOrder']){
    $order_id = $_REQUEST['order_id'];
    $florist_note_type = getwayConnect::getwayData("SELECT * FROM `order_notes_types` WHERE `type` = 'notes_for_florist'");
    $florist_note_row = getwayConnect::getwayData("SELECT * FROM `order_notes` WHERE `type_id` = '{$florist_note_type[0]['id']}' and order_id = '{$order_id}'");
    print json_encode($florist_note_row);die;
}
if(isset($_REQUEST['get_anvart_rows']) && $_REQUEST['get_anvart_rows']){
    $order_id = $_REQUEST['order_id'];
    $rows = getwayConnect::getwayData("SELECT * FROM pending_info LEFT JOIN user on pending_info.operator_id = user.id where order_id = '" . $order_id . "' and status = '1'");
    foreach($rows as $key=>$row){
        $rows[$key]['created_date_format'] = date("Y-M-d H:i:s",strtotime($row['created_date']));
    }
    print json_encode($rows);die;
}
// $desc_tax_accounts = [];
// if ($file = fopen("../../desc_tax_account_2020.txt", "r")) {
//     while(!feof($file)) {
//         $line = fgets($file);
//         $info = explode('|', $line);
//         if(count($info) > 1){
//             $desc_tax_accounts[] = $info[0];
//         }
//     }
//     fclose($file);
// }
$desc_tax_accounts = getwayConnect::getwayData('SELECT * FROM `product_adg_codes`');

if(isset($_REQUEST['getorderDownloads']) && $_REQUEST['getorderDownloads']){
    $order_id = $_REQUEST['order_id'];
    $tax_number_of_check_info_show = getwayConnect::getwayData("SELECT * FROM `tax_numbers_of_check` WHERE `order_id` = '" . $order_id ."'");
    $result = getwayConnect::getwayData("SELECT * FROM order_xml_download where order_id = '" . $order_id . "'");
    $resultHdm = getwayConnect::getwayData("SELECT * FROM order_hdm_printed where order_id = '" . $order_id . "'");
    $product_type_quantity = getwayConnect::getwayData("SELECT * FROM `order_tax_info` where order_tax_info.rg_order_id = '" . $order_id .  "'");
    $tax_type_count_array = Array();
    if(count($product_type_quantity) > 0){
        foreach($product_type_quantity as $key=>$value){
            // $tax_type_count_array[] = Array('quantity' => $value['quantity'],'type' => $desc_tax_accounts[$value['tax_account_id']-1]);
            $tax_type_count_array[] = Array('quantity' => $value['quantity'],'type' => $desc_tax_accounts[$value['tax_account_id']]['product_type_title']);
        }
    }
    $array = Array('result'=>$result,'hdm_invoice'=>$tax_number_of_check_info_show[0]['hdm_tax'],'tax_type_count_array'=>$tax_type_count_array,'resultHdm'=>$resultHdm);
    print json_encode($array);die;
}
if(isset($_REQUEST['get_stock_prods']) && $_REQUEST['get_stock_prods'] != ''){
    $street = getwayConnect::getwayData("SELECT * from jos_vm_product_stock_href LEFT JOIN orders_products_data ON jos_vm_product_stock_href.stock_product_id = orders_products_data.id where product_id = '{$_REQUEST['get_stock_prods']}'");
    print json_encode($street);
    exit;
}
if(isset($_REQUEST['get_stock_default_prods']) && $_REQUEST['get_stock_default_prods'] != ''){
    $query_accounted = "SELECT orders_products_accounting.order_id,
    orders_products_accounting.count,
    orders_products_accounting.order_product_id,
    orders_products_data.product_name,
    orders_products.id,
    orders_products.pprice as pprice,
    orders_products_accounting.pNetcost 
    FROM orders_products_accounting 
    JOIN orders_category_product ON orders_category_product.products_id = orders_products_accounting.product 
    JOIN orders_products ON orders_products_accounting.product = orders_products.product_data_id 
    JOIN orders_products_data ON orders_products_data.id = orders_products.product_data_id
    WHERE orders_products_accounting.order_id = {$_REQUEST['order_id']} and orders_products_accounting.order_product_id = {$_REQUEST['get_stock_default_prods']}";
    $accounted_products = getwayConnect::getwayData($query_accounted);
    print json_encode($accounted_products);die;
}
if(isset($_REQUEST['get_prepair_note_for_prod']) && $_REQUEST['get_prepair_note_for_prod'] != ''){
    $street = getwayConnect::getwayData("SELECT * from jos_vm_product_prepair_note where prod_id = '{$_REQUEST['get_prepair_note_for_prod']}'");
    print json_encode($street);
    exit;
}
if(isset($_REQUEST['insert_prod_prepair_note']) && $_REQUEST['insert_prod_prepair_note'] != ''){
    $prepair_note = getwayConnect::getwayData("SELECT * from jos_vm_product_prepair_note where prod_id = '{$_REQUEST['insert_prod_prepair_note']}'");
    if(empty($prepair_note)){
        getwayConnect::getwaySend("INSERT INTO jos_vm_product_prepair_note (prod_id,prepair_note) VALUES ('{$_REQUEST['insert_prod_prepair_note']}','" . $_REQUEST['val']."')");
    }
    else{
        if($_REQUEST['val'] == ''){

            getwayConnect::getwaySend("DELETE FROM jos_vm_product_prepair_note WHERE prod_id = '" . $_REQUEST['insert_prod_prepair_note'] . "'");
        }
        else{
            getwayConnect::getwaySend("UPDATE jos_vm_product_prepair_note set prepair_note='" . $_REQUEST['val']."' where prod_id = '" . $_REQUEST['insert_prod_prepair_note'] . "'");
        }
    }
    exit;
}
$access_for_excel = [38,4];
if(isset($_REQUEST['getorderlog']) && $_REQUEST['getorderlog']){
    $order_id = $_REQUEST['order_id'];
    $check_table_count = substr($_REQUEST['order_id'], 0, 2);
    $table_count;
    if($check_table_count >= 45 && $check_table_count < 50){
        $table_count = '45_50';
    }
    if($check_table_count >= 50 && $check_table_count < 55){
        $table_count = '50_55';
    }
    if($check_table_count >= 55 && $check_table_count < 60){
        $table_count = '55_60';
    }
    if($check_table_count >= 60 && $check_table_count < 65){
        $table_count = '60_65';
    }
    if($check_table_count >= 65 && $check_table_count <= 70){
        $table_count = '65_70';
    }
    if($check_table_count >= 70 && $check_table_count <= 75){
        $table_count = '70_75';
    }
    if($check_table_count >= 75 && $check_table_count <= 80){
        $table_count = '75_80';
    }
    if($check_table_count >= 80 && $check_table_count <= 85){
        $table_count = '80_85';
    }
    if($check_table_count >= 85 && $check_table_count <= 90){
        $table_count = '85_90';
    }
    if($check_table_count >= 90 && $check_table_count <= 95){
        $table_count = '90_95';
    }
    $result = [];
    $order_log = getwayConnect::getwayData("SELECT * FROM log_" . $table_count . " LEFT JOIN delivery_status ON log_" . $table_count . ".current_status_id = delivery_status.id left join user on log_" . $table_count . ".operator_id = user.id where order_id='{$order_id}'");
    $result['order_log'] = $order_log;
    print json_encode($result);die;
}
//
$invoice_not_send_malus = GetOffOnAction('invoice_not_send_malus');
$not_delivered_informed_on_time_malus = GetOffOnAction('not_delivered_informed_on_time_malus');
if($_SERVER['REMOTE_ADDR'] == '87.241.131.72'){
    // $orderInfo = getwayConnect::getwayData("SELECT id,delivery_status,sell_point FROM rg_orders where id='77316' and delivery_date >= '" . date('Y-m-d', strtotime('-2 days')) . "'");
    // print "<pre>";
    // var_dump($orderInfo,date('Y-m-d', strtotime('-2 days')));die;
}

    
if(isset($_REQUEST['checkConfirmationEmailNotification']) && $_REQUEST['checkConfirmationEmailNotification']){
    $order_id = $_POST['order_id'];
    $sellPointsInfo = getwayConnect::getwayData("SELECT * FROM delivery_sellpoint where inform_delivery=1");
    $orderInfo = getwayConnect::getwayData("SELECT id,delivery_status,sell_point FROM rg_orders where id='{$order_id}'");
    $result['order_id'] = $order_id;
    $result['response'] = true;
    if($order_id > 66373){
        if($orderInfo[0]['delivery_status'] == 1){
            $sellpointsArray = Array();
            foreach($sellPointsInfo as $value){
                $sellpointsArray[] = $value['id'];
            }
            if(in_array($orderInfo[0]['sell_point'], $sellpointsArray)){
                $mail_log = getwayConnect::getwayData("SELECT content_type,count FROM mail_log where order_id={$order_id} and content_type = 2");
                if($mail_log){
                    if($mail_log[0]['count'] == 0){
                        $result['response'] = false;
                    }
                }
                else{
                    $result['response'] = false;
                }
            }
        }
    }
    print json_encode($result);die;
}
if(isset($_REQUEST['countOfStockProducts']) && $_REQUEST['countOfStockProducts']){
    $order_ids = $_REQUEST['order_ids'];
    $result = Array();
    foreach($order_ids as $order_id){
        $relateds = getwayConnect::getwayData("SELECT * FROM order_related_products where order_id='" . $order_id . "'");
        if($relateds[0]['jos_vm_product_id']){
            $jos_vm_product_ids = explode(',',$relateds[0]['jos_vm_product_id']);
            foreach($jos_vm_product_ids as $id){
                $stockInfo = getwayConnect::getwayData("SELECT * from jos_vm_product_stock_href LEFT JOIN orders_products_data ON jos_vm_product_stock_href.stock_product_id = orders_products_data.id where product_id = '" . $id . "'");
                foreach($stockInfo as $stock){
                    if($stock['count'] > 0){
                        $stockProductInfo = getwayConnect::getwayData("SELECT * from orders_products where product_data_id = '" . $stock['stock_product_id'] . "'");
                        if(array_key_exists($stock['stock_product_id'],$result)){
                            $result[$stock['stock_product_id']]['count'] = $result[$stock['stock_product_id']]['count'] + $stock['count'];
                        }
                        else{
                            $array = Array();
                            $array['name'] = $stock['product_name'];
                            $array['count'] = $stock['count'];
                            $array['pnetcost'] = $stockProductInfo[0]['pNetcost'];
                            $result[$stock['stock_product_id']] = $array;
                        }
                        // $result[$stock['stock_product_id']] = $array;
                    }
                }
            }
        }
    }
    array_multisort( array_column($result, "count"), SORT_DESC, $result );
    print json_encode($result);die;
}
if(isset($_REQUEST['getAdditionalPrice']) && $_REQUEST['getAdditionalPrice']){
    $additionalPrice = getwayConnect::getwayData("SELECT * FROM additional_delivery_prices where order_id='{$_REQUEST['order_id']}'");
    $delivery_price = getwayConnect::getwayData("SELECT * FROM drive_prices where id='{$_REQUEST['delivery_price_id']}'");
    $result = Array();
    $result['additionalPrice'] = $additionalPrice;
    $result['delivery_price'] = $delivery_price;
    print json_encode($result);die;
}
if(isset($_REQUEST['checkLate']) && $_REQUEST['checkLate']){
    $l_order = getwayConnect::getwayData("SELECT * FROM rg_orders where id='{$_REQUEST['l_id']}'");
    $mail_logs = getwayConnect::getwayData("SELECT * FROM mail_log where order_id='{$_REQUEST['l_id']}'");
    $content_logs = [];
    foreach($mail_logs as $mail_log){
        $content_logs[] = $mail_log['content_type'];
    }
    $late = '';
    $conf_late = 1;
    $is_conf_late = false;
    $del_late = 5;
    $is_del_late = false;
    $conf_diff = 0;
    $del_diff = 0;
    $edited = false;
    if(isset($l_order) && isset($l_order[0]) && !empty($l_order[0])){
        if(isset($l_order[0]['late']) && $l_order[0]['late'] != ''){
            $already_lates = explode(",", $l_order[0]['late']);
        } else {
            $already_lates = [];
        }
        
        $now = new DateTime(date('Y-m-d H:i:s'));
        if($l_order[0]['delivery_status'] == 2 || $l_order[0]['order_source'] == 1 || $l_order[0]['delivery_status'] == 10 ){
            $last_operator = getwayConnect::getwayData("SELECT * FROM `user_login` LEFT JOIN user on user_login.user_id = `user`.uid WHERE user_level = '36' ORDER BY user_login.id DESC LIMIT 1;");
            $conf = $now->diff(new DateTime($l_order[0]['created_date'].' '. $l_order[0]['created_time']));
            $conf_diff = abs($conf->d*24*60 + $conf->h*60 + $conf->i);
            if($conf_diff > 20 && !in_array(1, $content_logs)){
                if(!in_array($conf_late, $already_lates)){
                    // Added By Hrach
                    $for_sale_point = array('16','15','22','24','32','37');
                    if( !in_array($l_order[0]['sell_point'], $for_sale_point)){
                        if( $l_order[0]['id'] > 47000 ){
                            $to      = 'auto-malus@regard-group.com';
                            // $to      = 'ceo@regard-group.com';
                            if ( $l_order[0]['delivery_status'] == 2 || $l_order[0]['delivery_status'] == 10){
                                $subject = "1-Malus for " .  ucfirst($last_operator[0]['username']) . " as of not sending Invoice to " .  $l_order[0]['id'] .'  customer on time.';
                            }
                            else {
                                $subject = "1-Malus for " .  ucfirst($l_order[0]['operator']) . " as of not sending Invoice to " .  $l_order[0]['id'] .'  customer on time.';
                            }
                            $created_date_full = $l_order[0]['created_date'] . " " . $l_order[0]['created_time'];
                            $created_full_time_strtotime = strtotime($created_date_full)+ 20*60;
                            $create_date_full_after = date("Y-m-d H:i:s",$created_full_time_strtotime);
                            $message = 'As of Not Sending Invoice in 20 minutes. Created in ' . $l_order[0]['created_date'] . " " . $l_order[0]['created_time'] . ', the latest action time was ' . $create_date_full_after . '.';
                            $headers = 'From: autocontrol@regard-group.com' . "\r\n" .
                                'Reply-To: autocontrol@regard-group.com' . "\r\n" .
                                'X-Mailer: PHP/' . phpversion();
                            if($invoice_not_send_malus == 1){
                                mail($to, $subject, $message, $headers);
                            }
                        }
                    }
                    //
                    $already_lates[] = $conf_late;
                    $edited = true;
                }
                $is_conf_late = true;
            }
        }
        if($l_order[0]['delivery_status'] == 3){
            $last_operator = getwayConnect::getwayData("SELECT * FROM `user_login` LEFT JOIN user on user_login.user_id = `user`.uid WHERE user_level = '36' ORDER BY user_login.id DESC LIMIT 1;");
            if(isset($l_order[0]['delivered_at']) && $l_order[0]['delivered_at'] != ''){
                $del = $now->diff(new DateTime($l_order[0]['delivered_at']));
                $del_diff = abs($del->d*24*60 + $del->h*60 + $del->i);
            }
            
            if($del_diff > 20 && !in_array(5, $content_logs)){
                if(!in_array($del_late, $already_lates)){
                    // Added By Hrach
                    $for_sale_point = array('16','15','22','24','32','37');
                    if( !in_array($l_order[0]['sell_point'], $for_sale_point) ){
                        if( $l_order[0]['id'] > 47000 ){
                            $delivered_date_full = $l_order[0]['delivered_at'];
                            $delivered_full_time_strtotime = strtotime($delivered_date_full)+ 20*60;
                            $deliver_date_full_after = date("Y-m-d H:i:s",$delivered_full_time_strtotime);
                           // $to      = 'auto-malus@regard-group.com';
							$to      = 'delivery@flowers-armenia.am'; // added by Ruben on 28 Dec, but review need 
                            // $to      = 'ceo@regard-group.com';
                            $subject = $l_order[0]['id'] . ' - Delivery-missing Auto-Malus for ' . $last_operator[0]['username'];
                            $message = 'As of Not Sending Delivery Info in 20 minutes. Delivered in ' . $l_order[0]['delivered_at'] . ', the latest action time was ' . $deliver_date_full_after . '.';
                            $headers = 'From: autocontrol@regard-group.com' . "\r\n" .
                                'Reply-To: autocontrol@regard-group.com' . "\r\n" .
                                'X-Mailer: PHP/' . phpversion();
                            if($not_delivered_informed_on_time_malus == 1){
                                mail($to, $subject, $message, $headers);
                            }
                        }
                    }
                    //
                    $already_lates[] = $del_late;
                    $edited = true;
                }
                $is_del_late = true;
            }
        }
        
        if(count($already_lates) > 0 && $edited){
            $lates = implode(",", $already_lates);
            getwayConnect::getwaySend("UPDATE rg_orders set late='{$lates}' where id='{$_REQUEST['l_id']}'");
        }
    }
    if($edited){
        echo json_encode(array('conf_late' => $is_conf_late, 'del_late' => $is_del_late));
    } else {
        echo json_encode(array('conf_late' => false, 'del_late' => false));
    }
    exit;
}
$currencValArray = Array('1'=>'USD','2'=>'RUB','3'=>'AMD','4'=>'EUR','5'=>'GBP','6'=>'IRR');
if(isset($_REQUEST['update_disadvantage_user']) && $_REQUEST['update_disadvantage_user']){
    getwayConnect::getwaySend("UPDATE disadvantage_users_by_order SET `description` = '" . $_POST['newContent'] . "' WHERE `id` = '" . $_POST['row_id'] . "'");
    return true;
}
if(isset($_REQUEST['remove_disadvantage_row']) && $_REQUEST['remove_disadvantage_row']){
    $row_id = $_POST['row_id'];
    $order_id = $_POST['order_id'];
    getwayConnect::getwayData("DELETE from disadvantage_users_by_order where id=" . $row_id);
    $disadvantageForOrder = getwayConnect::getwayData("SELECT * FROM disadvantage_users_by_order WHERE order_id = " . $order_id);
    $disableMalus = true;
    $orderInfo = getwayConnect::getwayData("SELECT * FROM `rg_orders` WHERE `id` = '{$order_id}'");
    $added_operator_info = getwayConnect::getwayData("SELECT * FROM `user` WHERE `username` = '{$orderInfo[0]['operator']}'");
    foreach($disadvantageForOrder as $value){
        $disadvantageListInfo = getwayConnect::getwayData("SELECT * FROM disadvantages_list WHERE id = " . $value['d_list_id']);
        if($disadvantageListInfo[0]['malus_unit'] > 0){
            if($value['user_id'] == $added_operator_info[0]['id']){
                $disableMalus = false;
            }
        }
    }
    if($disableMalus == true){
        getwayConnect::getwaySend("UPDATE `rg_orders` SET `disadvantage_status` = '0', `bonus_type` = '0' WHERE `id` = '{$order_id}'");
        print 2;die;
    }
    return true;
}
if(isset($_REQUEST['getStatisticForDriver']) && $_REQUEST['getStatisticForDriver']){
    $dateRangedate[0] = date('Y-m-01');
    $dateRangedate[1] = date('Y-m-t');
    if($_POST['dateRangedate']){
        $dateRangedate = explode(' to ',$_POST['dateRangedate']);
    }
    $driverInfo = getwayConnect::getwayData("SELECT * FROM `delivery_deliverer` where user_id ='" . $_POST['user_id']. "'");
    $result = Array();
    if($driverInfo){
        $driverInfo = $driverInfo[0];
        $orders = getwayConnect::getwayData("SELECT * FROM `rg_orders` where deliverer = '" . $driverInfo['id']. "' and created_date >= '" . $dateRangedate[0] . "' and created_date <= '" . $dateRangedate[1] . "' and delivery_price > 3");
        $drive_price_array = Array();
        foreach($orders as $key=>$order){
            $drive_price_array[$order['delivery_price']][] = $order['id'];
        }
    }
    $result['drive_price_array'] = $drive_price_array;
    echo json_encode($result);die;
    var_dump($driverInfo);die;
}
if(isset($_REQUEST['getStatisticForFlorist']) && $_REQUEST['getStatisticForFlorist']){
    $dateRangedate[0] = date('Y-m-01');
    $dateRangedate[1] = date('Y-m-t');
    if($_POST['dateRangedate']){
        $dateRangedate = explode(' to ',$_POST['dateRangedate']);
    }
    $orders = getwayConnect::getwayData("SELECT * from rg_orders where flourist_id='" . $_POST['user_id']. "' and created_date >= '" . $dateRangedate[0] . "' and created_date <= '" . $dateRangedate[1] . "' and delivery_status = 3");
    $result = Array();
    $result['prepared_count'] = count($orders);
    echo json_encode($result);exit;
}
if(isset($_REQUEST['getStatisticForUser']) && $_REQUEST['getStatisticForUser']){
    $dateRangedate[0] = date('Y-m-01');
    $dateRangedate[1] = date('Y-m-t');
    if($_POST['dateRangedate']){
        $dateRangedate = explode(' to ',$_POST['dateRangedate']);
    }
    $selectedUserInfo = getwayConnect::getwayData("SELECT * from user where id=" . $_POST['user_id'])[0];
    $orders_09_18 = getwayConnect::getwayData("SELECT * from rg_orders where operator='" . $selectedUserInfo['username']. "' and created_time > '09:00:01' and created_time < '18:00:00' and created_date >= '" . $dateRangedate[0] . "' and created_date <= '" . $dateRangedate[1] . "'");
    $orders_18_00 = getwayConnect::getwayData("SELECT * from rg_orders where operator='" . $selectedUserInfo['username']. "' and created_time > '18:00:01' and created_time < '23:59:59' and created_date >= '" . $dateRangedate[0] . "' and created_date <= '" . $dateRangedate[1] . "'");
    $orders_00_09 = getwayConnect::getwayData("SELECT * from rg_orders where operator='" . $selectedUserInfo['username']. "' and created_time > '00:00:01' and created_time < '08:59:59' and created_date >= '" . $dateRangedate[0] . "' and created_date <= '" . $dateRangedate[1] . "'");
    $bonus_orders = getwayConnect::getwayData("SELECT * from rg_orders where operator='" . $selectedUserInfo['username']. "' and created_date >= '" . $dateRangedate[0] . "' and created_date < '" . $dateRangedate[1] . "'  and bonus_type=1");
    $payed_orders = getwayConnect::getwayData("SELECT * from rg_orders where operator='" . $selectedUserInfo['username']. "' and created_date >= '" . $dateRangedate[0] . "' and created_date < '" . $dateRangedate[1] . "'  and delivery_status in(1,3,6,7,11,12,13,14)");
    $not_payed_orders = getwayConnect::getwayData("SELECT * from rg_orders where operator='" . $selectedUserInfo['username']. "' and created_date >= '" . $dateRangedate[0] . "' and created_date < '" . $dateRangedate[1] . "'  and delivery_status in(2,4,5,8,9,10)");
    $result = Array();
    $payed_orders_day = 0;
    $communication_orders_day = 0;
    $bac_toxnvac_orders_day = 0;
    $chexyal_orders_day = 0;
    $payed_orders_status_id = Array(1,3,6,7,11,12,13,14);
    foreach($orders_09_18 as $key=>$value){
        if(in_array($value['delivery_status'],$payed_orders_status_id)){
            if($value['order_source'] == 1 && $value['bonus_type'] == 3){

            }
            else{
                $payed_orders_day++;
            }
            if($value['order_source'] != 1){
                if($value['bonus_type'] != 3){
                }
            }
        }
        if($value['delivery_status'] == 8){
            if($value['delivery_reason'] != 44 && $value['delivery_reason'] != 45){
                $communication_orders_day++;
            }
        }
        if($value['delivery_status'] == 5){
            $bac_toxnvac_orders_day++;
        }
        if($value['delivery_status'] == 4){
            $chexyal_orders_day++;
        }
    }
    $payed_orders_evening = 0;
    $communication_orders_evening = 0;
    $bac_toxnvac_orders_evening = 0;
    $chexyal_orders_evening = 0;
    foreach($orders_18_00 as $key=>$value){
        if(in_array($value['delivery_status'],$payed_orders_status_id)){
            if($value['order_source'] == 1 && $value['bonus_type'] == 3){

            }
            else{
                    $payed_orders_evening++;
            }
            if($value['order_source'] != 1){
                if($value['bonus_type'] != 3){
                }
            }
        }
        if($value['delivery_status'] == 8){
            if($value['delivery_reason'] != 44 && $value['delivery_reason'] != 45){
                $communication_orders_evening++;
            }
        }
        if($value['delivery_status'] == 5){
            $bac_toxnvac_orders_evening++;
        }
        if($value['delivery_status'] == 4){
            $chexyal_orders_evening++;
        }
    }
    $payed_orders_night = 0;
    $communication_orders_night = 0;
    $bac_toxnvac_orders_night = 0;
    $chexyal_orders_night = 0;
    foreach($orders_00_09 as $key=>$value){
        if(in_array($value['delivery_status'],$payed_orders_status_id)){
             if($value['order_source'] == 1 && $value['bonus_type'] == 3){

            }
            else{
                    $payed_orders_night++;
            }
            if($value['order_source'] != 1){
                if($value['bonus_type'] != 3){
                }
            }
        }
        if($value['delivery_status'] == 8){
            if($value['delivery_reason'] != 44 && $value['delivery_reason'] != 45){
                $communication_orders_night++;
            }
        }
        if($value['delivery_status'] == 5){
            $bac_toxnvac_orders_night++;
        }
        if($value['delivery_status'] == 4){
            $chexyal_orders_night++;
        }
    }

    $orders = getwayConnect::getwayData("SELECT * from rg_orders where operator='" . $selectedUserInfo['username']. "' and created_date >= '" . $dateRangedate[0] . "' and created_date <= '" . $dateRangedate[1] . "'");
    $total_bonuses = 0;
    foreach($orders as $key=>$value){
        if($value['bonus_type'] == 1){
            $tot_price_amd = $currencyValues->$currencValArray[$value['currency']] * $value['price'];
            if($tot_price_amd >= 12000 && $tot_price_amd <= 59999){
                $total_bonuses = $total_bonuses + 1;
            } else if($tot_price_amd >= 60000 && $tot_price_amd <= 139999){
                $total_bonuses = $total_bonuses + 2;
            } else if($tot_price_amd >= 140000 && $tot_price_amd <= 199999){
                $total_bonuses = $total_bonuses + 3;
            } else if($tot_price_amd >= 200000 && $tot_price_amd <= 319999){
                $total_bonuses = $total_bonuses + 5;
            } else if($tot_price_amd >= 320000){
                $total_bonuses = $total_bonuses + 10;
            }
        }
    }

    $result['payed_orders_day'] = $payed_orders_day;
    $result['total_bonuses'] = $total_bonuses;
    $result['payed_orders_evening'] = $payed_orders_evening;
    $result['payed_orders_night'] = $payed_orders_night;
    $result['communication_orders_day'] = $communication_orders_day;
    $result['communication_orders_evening'] = $communication_orders_evening;
    $result['communication_orders_night'] = $communication_orders_night;
    $result['bac_toxnvac_orders_day'] = $bac_toxnvac_orders_day;
    $result['bac_toxnvac_orders_evening'] = $bac_toxnvac_orders_evening;
    $result['bac_toxnvac_orders_night'] = $bac_toxnvac_orders_night;
    $result['chexyal_orders_day'] = $chexyal_orders_day;
    $result['chexyal_orders_evening'] = $chexyal_orders_evening;
    $result['chexyal_orders_night'] = $chexyal_orders_night;
    $result['bonus_orders'] = count($bonus_orders);
    $result['not_payed_orders'] = count($not_payed_orders);
    $result['payed_orders'] = count($payed_orders);
    $result['not_payed_procent'] = number_format((count($not_payed_orders) * 100)/count($payed_orders),2);
    echo json_encode($result);exit;
}
function convertPriceToAmd($currency,$price,$currencyValues){
    var_dump($currency,$price,$currencyValues);die;

}
if(isset($_REQUEST['getPeopleDisadvantages']) && $_REQUEST['getPeopleDisadvantages']){
    $result = Array();
    $dateRangedate[0] = date('Y-m-01');
    $dateRangedate[1] = date('Y-m-t');
    if($_POST['dateRangedate']){
        $dateRangedate = explode(' to ',$_POST['dateRangedate']);
    }
    $disadvantages = getwayConnect::getwayData("SELECT * from disadvantage_users_by_order where user_id=" . $_POST['user_id'] . " and created_date > '" . $dateRangedate[0] . "' and created_date < '" . $dateRangedate[1] . "'");
    $selectedUserInfo = getwayConnect::getwayData("SELECT * from user where id=" . $_POST['user_id'])[0];
    foreach($disadvantages as $key=>$value){
        $disadvantageListInfo = getwayConnect::getwayData("SELECT * from disadvantages_list where id=" . $value['d_list_id']);
        $addedUserInfo = getwayConnect::getwayData("SELECT * from user where id=" . $value['added_by_user_id']);
        $disadvantages[$key]['list_title'] = $disadvantageListInfo[0]['title'];
        $disadvantages[$key]['malus'] = $disadvantageListInfo[0]['malus_unit'];
        $disadvantages[$key]['addedUserInfo'] = $addedUserInfo[0];
    }
    $result['disadvantages'] = $disadvantages;
    $result['selectedUserInfo'] = $selectedUserInfo;
    $result['from_date'] = $dateRangedate[0];
    $result['to_date'] = $dateRangedate[1];
    echo json_encode($result);exit;
}
if(isset($_REQUEST['getMyselfPeopleDisadvantages']) && $_REQUEST['getMyselfPeopleDisadvantages']){
    $result = Array();
    $dateRangedate[0] = date('Y-m-01');
    $dateRangedate[1] = date('Y-m-t');
    if($_POST['dateRangedate']){
        $dateRangedate = explode(' to ',$_POST['dateRangedate']);
    }
    $disadvantages = getwayConnect::getwayData("SELECT * from disadvantage_users_by_order where added_by_user_id=" . $_POST['user_id'] . " and created_date > '" . $dateRangedate[0] . "' and created_date < '" . $dateRangedate[1] . "'");
    $selectedUserInfo = getwayConnect::getwayData("SELECT * from user where id=" . $_POST['user_id'])[0];
    foreach($disadvantages as $key=>$value){
        $disadvantageListInfo = getwayConnect::getwayData("SELECT * from disadvantages_list where id=" . $value['d_list_id']);
        $member_info = getwayConnect::getwayData("SELECT * from user where id=" . $value['user_id']);
        $disadvantages[$key]['list_title'] = $disadvantageListInfo[0]['title'];
        $disadvantages[$key]['malus'] = $disadvantageListInfo[0]['malus_unit'];
        $disadvantages[$key]['member_info'] = $member_info[0];
    }
    $result['disadvantages'] = $disadvantages;
    $result['selectedUserInfo'] = $selectedUserInfo;
    $result['from_date'] = $dateRangedate[0];
    $result['to_date'] = $dateRangedate[1];
    echo json_encode($result);exit;
}
if(isset($_REQUEST['getPeopleTasks']) && $_REQUEST['getPeopleTasks']){
    // $users = getwayConnect::getwayData("SELECT `id`, `uid`, `username` from user where user_active=1");
    // $data = [];
    // if(isset($users) && !empty($users)){
    //     foreach($users as $user){
    //         $user_task = getwayConnect::getwayData("SELECT count(rg_orders.id) as ord_count from rg_orders RIGHT JOIN delivery_deliverer on delivery_deliverer.id=rg_orders.deliverer  where (operator='{$user['username']}' || userId='{$user['id']}' || flourist_id='{$user['id']}' || operator_name='{$user['username']}' || delivery_deliverer.name='{$user['username']}') AND rg_orders.delivery_date=CURDATE() AND rg_orders.delivery_status IN (1,3,6,7,11,12,13,14)");
    //         $data[] = array('us_id' => $user['uid'], 'ord_count' => $user_task[0]['ord_count'], 'username' => $user['username'], 'id' => $user['id']);
    //     }
    // }
    // echo json_encode($data);
    // exit;
    $users = getwayConnect::getwayData("SELECT * from user where user_active=1 order by ordering");
    echo json_encode($users);
    exit;
}
    function GetOffOnAction($variable){
        $off_on_actions = getwayConnect::getwayData("SELECT * from off_on where variable = '" . $variable . "'");
        return ($off_on_actions[0]) ? $off_on_actions[0]['action'] : null;
    }
    function GetVariableValue($variable){
        $valueInfo = getwayConnect::getwayData("SELECT * from variable_value where variable = '" . $variable . "'");
        return $valueInfo;
    }
    $timeDifferenceLast = strtotime(date("Y-m-d H:i:s"));
    if(isset($lastStart[0])){
        $timeDifferenceLast -= strtotime($lastStart[0]['start_date'] . " " . $lastStart[0]['start_time']);
    }
    $orders_page_working_hours = GetOffOnAction('orders_page_working_hours');
    $orders_page_yellow_warning = GetOffOnAction('orders_page_yellow_warning');
    $orders_page_checklate_ajax = GetOffOnAction('orders_page_checklate_ajax');
    $ajax_check_confirmation_mail = GetOffOnAction('ajax_check_confirmation_mail');
    $calculate_salary_in_list = GetOffOnAction('calculate_salary_in_list');
    $calculate_advertisement_in_list = GetOffOnAction('calculate_advertisement_in_list');
    $calculate_other_costs_in_list = GetOffOnAction('calculate_other_costs_in_list');
    $orders_page_price_list_from_cba = GetOffOnAction('orders_page_price_list_from_cba');
    $list_receiver_fields = GetOffOnAction('list_receiver_fields');
    $list_action_fields = GetOffOnAction('list_action_fields');
    $show_pnetcost_procent_in_list = GetOffOnAction('show_pnetcost_procent_in_list');
    $calculate_salary_in_list_value = GetVariableValue('calculate_salary_in_list');
    $calculate_advertisement_in_list_value = GetVariableValue('calculate_advertisement_in_list');
    $calculate_other_costs_in_list_value = GetVariableValue('calculate_other_costs_in_list');
    $show_alert_notification_operators1 = GetOffOnAction('show_alert_notification_operators1');
    $ajax_check_anavart_operators_mail = GetOffOnAction('ajax_check_anavart_operators_mail');

    $sendRequestAnavartCheck = 0;
    if($ajax_check_anavart_operators_mail == 1){
        if (strpos($userData[0]['user_level'], '36') !== false) {
            $sendRequestAnavartCheck = 1;
        }
    }

    $simple_florist_ids = array('90','91','92','93','94','95','96','97','98','99','100','101','102','114','115','116','117','118');



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
        getwayConnect::getwaySend("INSERT INTO disadvantage_users_by_order (user_id, order_id,description,d_list_id,added_by_user_id,created_date,file_path) VALUES ('{$user_id}' , '1' ,'{$description}','{$list_id}','{$userData[0]['id']}','" . date('Y-m-d H:i:s') . "','{$fileName}')");
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
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Apay gateway">
    <meta name="keywords" content="paypal, payment,visa ,mastercard,payment getway,payment gateway">
    <meta name="author" content="Davit Gabrielyan, Ruben Mnatsakanyan">
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    <link rel="stylesheet" href="<?= $rootF ?>/template/account/sidebar.css">
    <!-- Bootstrap minified CSS -->
    <link rel="stylesheet" href="<?= $rootF ?>/template/bootstrap/css/bootstrap.min.css">
    <!-- Bootstrap optional theme -->
    <link rel="stylesheet" href="<?= $rootF ?>/template/bootstrap/css/bootstrap-theme.min.css">
    <link rel="stylesheet" href="<?= $rootF ?>/template/datepicker/css/datepicker.css">
    <link rel="stylesheet" href="<?= $rootF ?>/template/rangedate/daterangepicker.css"/>
    <link rel="stylesheet" href="index_css.css"/>
    <title>Flower Orders</title>
    <style type="text/css">
        .d-none{
            display:none!important;
        }
        .highlight{
            background-color: yellow;color:black;font-size: 12px;
        }
        .show-print {
            display: none !important;
        }
        .hover_cursor_pointer:hover{
            cursor:pointer;
        }
        .show_log_of_order:hover{
            cursor:pointer;
        }
        @media print {
            .hidden-print {
                display: none !important;
            }
            .show-print {
                display: inline !important;
            }
            .article .text.short {
                height: 100%;
                overflow: auto;
            }
        }
        .prices_list_price {
            color: red !important; 
        }
        .urgentProductsDiv {
            max-width: 20%;
            max-height: 700px;
            display: inline-block;
            text-align: center;
            overflow-y: scroll;
            position: fixed;
            top: 200px;
            right: 0;
        }
        .product {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
            background: gray;
        }
        .productImage {
            max-height: 140px;
            max-width: 130px;
        }
        .productInfo {
            width: 1000px;
            height: 800px;
            display: none;
            overflow-x: scroll;
            border: 1px solid gray;
            padding-top: 2%;
            z-index: 1000;
            position: absolute;
            top: 300px;
            left: 250px;
        }
        .relatedProduct {
            max-width: 200px;
            max-height: 200px;
            text-align: center;
            display: inline-block;
            margin-top: 50px;
            margin-left: 35px;
        }
        .relatedProductImage {
            max-width: 100px;
            max-height: 100px;
        }
        .relatedProductName {
            word-break: break-word;
        }
        .productName {
            margin-left: 15px;
        }
        .link-button {
            background-color: green;
            color: white;
            border: none;
            margin-left: 5px;
            border-radius: 20px;
        }
        .unpublished {
            position: absolute;
            top: 0;
            left: 0;
        }
        .unpublishedProd {
            position: relative;
            top: -120px;
            left: 0;
        }
        .relatedProductPrice {
            position: relative;
            left: -40px;
        }
        .productDesc {
            color: blue;
            display: inline-block;
        }
        .rightAddress {
            color: red;
        }
        .imgDiv {
            display: inline-block;
        }
        .w-size {
            padding-left: 0;
            background: url('./ico/w.png') 1px 20px no-repeat;
            width: 32px;
            background-position-y: 20px;
            background-position-x: 1px;
            right: 0px;
            top: 50px;
        }
        .h-size {
            padding-top: 7px;
            padding-left: 7px;
            background: url('./ico/h.png') no-repeat;
            margin-left: 5px;
            margin-right: 5px;
            top: 50px;
        }
        .h-size, .w-size {
            position: relative;
            height: 32px;
            color: #909090;
            font-size: 11px;
            display: inline-block;
        }
        .relatedProductTitle{
            display: inline-block;
            width: 50%;
            margin-top: 15px;
            float: right;
        }
        .receiverMood {
            max-height: 30px;
            max-width: 30px;
        }
        .bonus-image {
            margin-left: 15px;
            max-height: 40px;
            max-width: 40px;
        }
        .created_time {
            font-size: 10px;
        }
        p.created_time {
            margin-bottom: 0px !important;
            margin-top: 12px;
        }
        .showChoosenRelated {
            display: inline;
            margin-left: 5px;
            max-width: 50%;
        }
        .reason_icon {
            max-width: 30px;
            max-height: 30px;
        }
        .check_reminder {
            color: red;
            font-weight: bold;
        }
        .fireImg {
            max-width: 20px;
            max-height: 20px;
        }
        .outimg{
            margin-top: 3px;
        }
        .out_images{
            display: inline-block;
        }
        .emailIco {
            max-width: 25px;
        }
        .wrongData {
            padding: 5px;
            background: yellow;
            color: red;
        }
        .peopleIcon {
            max-width: 50px;
            margin-left: 15px;
        }
        .peopleDiv {
            /*min-width: 400px;*/
            display: inline-block;
            margin-left: 15px;
        }
        .peopleImagesSpan {
            padding: 5px;
        }
        .peopleImages {
            max-width: 42px;
        }
        .warningCount {
            position: relative;
            top: 8px;
            margin-left: 10px;
        }
        .anavartShowRowsIcon:hover{
            cursor:pointer;
        }
        .color_red{
            color:red;
        }
        .text_bolder{
            font-weight:bolder;
        }
        .showAnavartRows{
            float:left;
            margin-top:10px;
            border-left:3px solid red;
        }
        .pekIconRed{
            width: 30px;
            height: 30px;
            margin-left:10px;
        }
        .hdmIconRed{
            width: 35px;
            height: 35px;
            margin-left:10px;
        }
        .pekIconRed:hover{
            cursor:pointer;
        }
        .display-none{
            display:none;
        }
        .for_rememberText_hdm{
            color:red;
            font-size:15px;
            font-weight:bolder;
        }
    </style>
    <?php
        if($orders_page_yellow_warning == 0){
            ?>
                <style type="text/css">
                    .wrongData{
                        display:none;
                    }
                </style>
            <?php
        }
    ?>
</head>
<body>
    <input type='hidden' class='show_alert_notification_operators1' value="<?=$show_alert_notification_operators1?>">
    <input type='hidden' class='ajax_check_anavart_operators_mail' value="<?=$ajax_check_anavart_operators_mail?>">
    <input type='hidden' class='sendRequestAnavartCheck' value="<?=$sendRequestAnavartCheck?>">
    <input type='hidden' class='orders_page_price_list_from_cba' value="<?=$orders_page_price_list_from_cba?>">
    <input type='hidden' class='usd_value_currency' value="<?=$currencyValues->USD?>">
    <input type='hidden' class='rub_value_currency' value="<?=$currencyValues->RUB?>">
    <input type='hidden' class='eur_value_currency' value="<?=$currencyValues->EUR?>">
    <input type='hidden' class='irr_value_currency' value="<?=$currencyValues->IRR?>">
    <input type='hidden' class='gbp_value_currency' value="<?=$currencyValues->GBP?>">
  
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
            <a class="navbar-brand" href="/info/" target='_blank'>RG / <?= strtoupper($userData[0]["username"]); ?></a>
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
                    <?php if (max(page::filterLevel(3, $levelArray)) >= 33): ?>
                <li><a href="order.php"
                       target="_blank"><?= (defined('ADD_NEW_ORDER')) ? ADD_NEW_ORDER : 'ADD_NEW_ORDER'; ?></a></li>
            <?php endif; ?>
                </li>
            </ul>
        </div>
    </div>
</nav>
     
<ol class="breadcrumb" id="activeFilters"
    style="position:fixed;top:51px;width: 100%;z-index: 99;border-bottom:dashed #777 1px;">
</ol>

<div class="container" style="margin-top:60px;width: 100%">
    <?php
        if($orders_page_working_hours == 1){
            ?>
                <div style='width:30%;margin-bottom:10px;display:inline-block;float:right'>
                    <div style='text-align:center;width:360px;border:1px dotted #adadad;padding: 2px;float:right'>
                        <?php
                            if($timeDifferenceLast > 54000 || !isset($lastStart[0])){
                                ?>
                                    <input type="time" class="start_time" value="<?=date('H:i:s')?>">
                                    <button class='btn_for_save_start_time' style='margin-top: 10px'>Okay</button>
                                <?php
                            }
                            else{
                                ?>
                                    <input type="time" disabled="true" class="start_time" value="<?=$lastStart[0]['start_time'] ?>">
                                <?php
                            }
                        ?>
                        <?php
                            if($timeDifferenceLast < 54000 && $lastStart[0]['end_time'] == "00:00:00"){
                                ?>
                                    <input type="time" class="end_time" data-id="<?=$lastStart[0]['id']?>" value="<?=date('H:i:s') ?>">
                                    <button class='btn_for_save_end_time' style='margin-top: 10px'>Okay</button>
                                <?php
                            }
                            else if(isset($lastStart[0]) && $lastStart[0]['end_time'] != "00:00:00"){
                                ?>
                                    <input type="time" class="end_time" disabled='true' value="<?=$lastStart[0]['end_time']?>">
                                <?php
                            }
                            else{
                                ?>
                                    <input type="time" class="end_time" disabled='true' value="<?=date('H:i:s')?>">
                                <?php
                            }
                        ?>
                    </div>
                </div>
            <?php
        }
    ?>

    <h3 style='width:70%' class="hidden-print"><?= (defined('RG_ORDER_SYSTEM')) ? RG_ORDER_SYSTEM : 'RG_ORDER_SYSTEM'; ?><br>
    <a href="/info/" target='_blank' class='unreadPosts'></a>

        <span class='showAlertMessageOperator' style="float:right;font-style: italic;float: right; color: cornflowerblue;">

        </span>
    </h3>
    <?php
    //(isset($userData[0]["username"])
    $this_month_total = 0;
    $last_month_total = 0;
    if(isset($userData[0]["username"]) && isset($userData[0]["earnings_rate"])){
        $json_currency = ($jsc_data = file_get_contents('currency.json')) ? json_decode($jsc_data,true) : false;
        $last_year = date("Y",strtotime('first day of last month'));
        $last_month = date('m', strtotime('first day of last month'));
        $this_year = date("Y");
        $this_month = date('m');
        $last_month_query = "SELECT price,currency,pNetcost FROM `rg_orders` 
                                WHERE `operator` = '{$userData[0]["username"]}'
                                    AND 
                                `order_defect` = 0 
                                    AND 
                                `out_defect` = 0 
                                    AND 
                                `pNetcost` > 0
                                    AND 
                                YEAR(`created_date`) = '{$last_year}'
                                    AND 
                                MONTH(`created_date`) = '{$last_month}'
                                    AND 
                                `delivery_status` = 3
                                ";
                                //echo $last_month_query;
        $this_month_query = "SELECT price,currency,pNetcost FROM `rg_orders` 
                                WHERE `operator` = '{$userData[0]["username"]}' 
                                    AND 
                                `order_defect` = 0 
                                    AND 
                                `out_defect` = 0 
                                    AND
                                `pNetcost` > 0
                                    AND 
                                YEAR(`created_date`) = '{$this_year}'
                                    AND 
                                MONTH(`created_date`) = '{$this_month}'
                                    AND 
                                `delivery_status` = 3
                                ";
        $this_month_data = getwayConnect::getwayData($this_month_query,PDO::FETCH_ASSOC);
        $last_month_data = getwayConnect::getwayData($last_month_query,PDO::FETCH_ASSOC);
        if(is_array($json_currency)){
            if(is_array($this_month_data) && $this_month_data> 0){
                foreach($this_month_data as $tmd){
                    if(isset($json_currency[$tmd['currency']]) && isset($json_currency[$tmd['currency']][0])){
                        //echo  $json_currency[$tmd['currency']][1].'*'.$tmd['price'].'='.($json_currency[$tmd['currency']][1]*$tmd['price'])."\n";
                        $tmd['price'] = (float)($json_currency[$tmd['currency']][1]*$tmd['price']);
                        $real_value = (float)$tmd['price']-(float)$tmd['pNetcost'];
                        $this_month_total += (float)(($real_value*(float)$userData[0]["earnings_rate"])/100);
                    }
                }
            }
            if(is_array($last_month_data) && $last_month_data> 0){
                foreach($last_month_data as $lmd){
                    if(isset($json_currency[$lmd['currency']]) && isset($json_currency[$lmd['currency']][0])){
                        //echo  $json_currency[$lmd['currency']][1].'*'.$lmd['price'].'='.($json_currency[$lmd['currency']][1]*$lmd['price'])."\n";
                        $lmd['price'] = (float)($json_currency[$lmd['currency']][1]*$lmd['price']);
                        $real_value = (float)$lmd['price']-(float)$lmd['pNetcost'];
                        $last_month_total += (float)(($real_value*(float)$userData[0]["earnings_rate"])/100);
                    }
                }
            }
        }
    }
    ?>
    <!-- <div class="hidden-print" style="max-width: 250px; display: inline-block;">
    Last month: <?=number_format((float)$last_month_total,2,'.',',');?><br/>
    This month: <?=number_format((float)$this_month_total,2,'.',',');?><br/>
    </div> -->
    <div style="display: inline-block; top: -13px; position: relative;" class="hidden-print">
        <?php
            $types = ['jpeg', 'png', 'JPEG', 'jpg'];
            foreach($types as $type){
                if(file_exists('../user_images/' . $userData[0]['uid']. ".". $type)){
                ?>
                    <div class="btn-group" role="group" aria-label="...">
                        <img src="<?= '../user_images/' . $userData[0]['uid']. '.'. $type ?>" alt="" width="50" height="50" class="pull-right">
                    </div>
                <?php
                }
            }
        ?>
        <div class="btn-group" role="group" aria-label="...">
                <img src="./ico/people.jpg" class="peopleIcon" alt="People Involved in Process" data-clicked="0">
                <div class="peopleDiv">
                </div>
                <img class='add_disadvantage hover_cursor_pointer' id='add_disadvantage' src="../../template/icons/bonus/1.png">

        </div>
        <div class="col-md-12">
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
    <input type='hidden' value="<?=($orders_page_checklate_ajax == 1)? '1' : '0' ?>" class='orders_page_checklate_ajax'>
    <input type='hidden' value="<?=($ajax_check_confirmation_mail == 1)? '1' : '0' ?>" class='ajax_check_confirmation_mail'>
    <input type='hidden' value="<?=($calculate_salary_in_list == 1)? $calculate_salary_in_list_value[0]['value'] : '0' ?>" class='calculate_salary_in_list'>
    <input type='hidden' value="<?=($calculate_advertisement_in_list == 1)? $calculate_advertisement_in_list_value[0]['value'] : '0' ?>" class='calculate_advertisement_in_list'>
    <input type='hidden' value="<?=($calculate_other_costs_in_list == 1)? $calculate_other_costs_in_list_value[0]['value'] : '0' ?>" class='calculate_other_costs_in_list'>
    <div></div>
    <div style="display: inline-block;" class="hidden-print">
        <div class="btn-group" role="group" aria-label="...">
            <?php
            if (max(page::filterLevel(3, $levelArray)) >= 33) {
                ?>
                
                <select name="chng_prod" class="btn btn-default" id="slct_prd_type" onchange="filter(null, true);">
                    <option value="flower" <?= !in_array($userData[0]['id'], $travel_operators)? 'selected': ''?>>Ծաղիկներ</option>
                    <option value="travel" <?= !in_array($userData[0]['id'], $travel_operators)? '': 'selected'?>>Travel</option>
                    <option value="all">Բոլորը</option>
                </select>
                <button class="btn btn-default" name="drf" id="1" onclick="filter(this,true);" value="<?= date("Y-m-d"); ?> to <?= date("Y-m-d"); ?>"><?= (defined('TODAY')) ? TODAY : 'TODAY'; ?>
                    (<?php if(max(page::filterLevel(3, $levelArray)) >= 33) {
                        echo getwayConnect::getwayCount("SELECT count(*) FROM rg_orders WHERE (delivery_date = '" . date("Y-m-d") . "' OR created_date =  '" . date("Y-m-d") . "') {$strict_country}");
                        echo '/';
                        echo getwayConnect::getwayCount("SELECT count(*) FROM rg_orders WHERE (delivery_date = '" . date("Y-m-d") . "' OR created_date =  '" . date("Y-m-d") . "') AND delivery_status in (1,3,6,7,11,12,13,14) {$strict_country}");
                    } else {
                            echo "<strong id=\"shopCT\"></strong>"; 
                        }
                    ?>)
                </button>
                
                <button class="btn btn-default" name="drf" id="1" onclick="filter(this,true);" value="<?= date("Y-m-d", time() + 86400); ?> to <?= date("Y-m-d", time() + 86400); ?>"><?= (defined('TOMORROW')) ? TOMORROW : 'TOMORROW'; ?>
                    (<?php if(max(page::filterLevel(3, $levelArray)) >= 33) {
                        echo getwayConnect::getwayCount("SELECT count(*) FROM rg_orders WHERE (delivery_date = '" . date("Y-m-d", time() + 86400) . "' OR created_date = '" . date("Y-m-d", time() + 86400) . "') {$strict_country}");
                        echo '/';
                        echo getwayConnect::getwayCount("SELECT count(*) FROM rg_orders WHERE (delivery_date = '" . date("Y-m-d", time() + 86400) . "' OR created_date = '" . date("Y-m-d", time() + 86400) . "') AND delivery_status in (1,3,6,7,11,12,13,14) {$strict_country}");
                    } else {
                            echo getwayConnect::getwayCount("SELECT count(*) FROM rg_orders WHERE (delivery_date = '" . date("Y-m-d", time() + 86400) . "' OR created_date = '" . date("Y-m-d", time() + 86400) . "') {$strict_country}");                    
                        }
                    ?>)
                </button>
                
                <button class="btn btn-default" name="drf" id="1" onclick="filter(this,true);" value="<?= date("Y-m-d", time() - 86400); ?> to <?= date("Y-m-d", time() - 86400); ?>"><?= (defined('YESTERDAY')) ? YESTERDAY : 'YESTERDAY'; ?>
                    (<?php if(max(page::filterLevel(3, $levelArray)) >= 33) {
                        echo getwayConnect::getwayCount("SELECT count(*) FROM rg_orders WHERE (delivery_date = '" . date("Y-m-d", time() + 86400) . "' OR created_date = '" . date("Y-m-d", time() - 86400) . "') {$strict_country}");
                        echo '/';
                        echo getwayConnect::getwayCount("SELECT count(*) FROM rg_orders WHERE (delivery_date = '" . date("Y-m-d", time() + 86400) . "' OR created_date = '" . date("Y-m-d", time() - 86400) . "') AND delivery_status in (1,3,6,7,11,12,13,14) {$strict_country}");
                    } else {
                            echo getwayConnect::getwayCount("SELECT count(*) FROM rg_orders WHERE (delivery_date = '" . date("Y-m-d", time() + 86400) . "' OR created_date = '" . date("Y-m-d", time() - 86400) . "') {$strict_country}");                    
                        }
                    ?>)
                </button>
                <?php if (max(page::filterLevel(3, $levelArray)) >= 33) { ?>
                    <button class="btn btn-default" name="drf" id="1" onclick="filter(this,true);" value="<?= date("Y-m-d", time() + 86400); ?> to <?= date("Y-m-d", time() + 259200 ); ?>"><?= (defined('TOMORROW3')) ? TOMORROW3 : 'Վաղը + 3օր'; ?>
                        (<?php if(max(page::filterLevel(3, $levelArray)) >= 33) {
                            echo getwayConnect::getwayCount("SELECT count(*) FROM rg_orders WHERE ((delivery_date >= '" . date("Y-m-d", time() + 86400) . "' AND delivery_date <= '" . date("Y-m-d", time() + 259200) ."')  OR (created_date >= '" . date("Y-m-d", time() + 86400) . "') AND created_date <= '" . date("Y-m-d", time() + 259200) . "' ) {$strict_country}");
                            echo '/';
                            echo getwayConnect::getwayCount("SELECT count(*) FROM rg_orders WHERE ((delivery_date >= '" . date("Y-m-d", time() + 86400) . "' AND delivery_date <= '" . date("Y-m-d", time() + 259200) ."')  OR (created_date >= '" . date("Y-m-d", time() + 86400) . "') AND created_date <= '" . date("Y-m-d", time() + 259200) . "' ) AND delivery_status in (1,3,6,7,11,12,13,14) {$strict_country}");
                        } else {
                                echo getwayConnect::getwayCount("SELECT count(*) FROM rg_orders WHERE ((delivery_date >= '" . date("Y-m-d", time() + 86400) . "' AND delivery_date <= '" . date("Y-m-d", time() + 259200) ."')  OR (created_date >= '" . date("Y-m-d", time() + 86400) . "') AND created_date <= '" . date("Y-m-d", time() + 259200) . "' ) {$strict_country}");                    
                            }
                        ?>)
                    </button>
                    <button class="btn btn-default" name="drf" id="1" onclick="filter(this,true);" value="<?= date("Y-m-d", time() + 86400); ?> to <?= date("Y-m-d", time() + 1296000 ); ?>"><?= (defined('TOMORROW15')) ? TOMORROW15 : 'Վաղը + 15օր'; ?>
                        (<?php if(max(page::filterLevel(3, $levelArray)) >= 33) {
                            echo getwayConnect::getwayCount("SELECT count(*) FROM rg_orders WHERE ((delivery_date >= '" . date("Y-m-d", time() + 86400) . "' AND delivery_date <= '" . date("Y-m-d", time() + 1296000) ."')  OR (created_date >= '" . date("Y-m-d", time() + 86400) . "') AND created_date <= '" . date("Y-m-d", time() + 1296000) . "' ) {$strict_country}");
                            echo '/';
                            echo getwayConnect::getwayCount("SELECT count(*) FROM rg_orders WHERE ((delivery_date >= '" . date("Y-m-d", time() + 86400) . "' AND delivery_date <= '" . date("Y-m-d", time() + 1296000) ."')  OR (created_date >= '" . date("Y-m-d", time() + 86400) . "') AND created_date <= '" . date("Y-m-d", time() + 1296000) . "' ) AND delivery_status in (1,3,6,7,11,12,13,14) {$strict_country}");
                        } else {
                                echo getwayConnect::getwayCount("SELECT count(*) FROM rg_orders WHERE ((delivery_date >= '" . date("Y-m-d", time() + 86400) . "' AND delivery_date <= '" . date("Y-m-d", time() + 1296000) ."')  OR (created_date >= '" . date("Y-m-d", time() + 86400) . "') AND created_date <= '" . date("Y-m-d", time() + 1296000) . "' ) {$strict_country}");
                            }
                        ?>) 
                    </button>
                <?php } ?>
                <button class="btn btn-default" onclick="totalResset();" value=""><?= (defined('RESET')) ? RESET : 'RESET'; ?></button>
                <button class="btn btn-default" onclick="filter(null,true);"><?= (defined('REFRESH')) ? REFRESH : 'REFRESH'; ?></button>
                <?php
            } else {
                ?>
                <select name="chng_prod" class="btn btn-default" id="slct_prd_type" onchange="filter(null, true);">
                    <option value="flower" selected>Ծաղիկներ</option>
                    <option value="travel">Travel</option>
                    <option value="all">Բոլորը</option>
                </select>
                <button class="btn btn-default" name="adf" id="17" onclick="filter(this,true);" value="<?= date("Y-m-d", time() - 86400); ?>"><?= (defined('YESTERDAY')) ? YESTERDAY : 'YESTERDAY'; ?></button>
                <button class="btn btn-default" name="adf" id="17" onclick="filter(this,true);" value="<?= date("Y-m-d"); ?>"><?= (defined('TODAY')) ? TODAY : 'TODAY'; ?></button>
                <button class="btn btn-default" name="adf" id="17" onclick="filter(this,true);" value="<?= date("Y-m-d", time() + 86400); ?>"><?= (defined('TOMORROW')) ? TOMORROW : 'TOMORROW'; ?></button>

                
                <button class="btn btn-default" name="drf" id="1" onclick="filter(this,true);" value="<?= date("Y-m-d", time() + 86400); ?> to <?= date("Y-m-d", time() + 259200 ); ?>"><?= (defined('TOMORROW3')) ? TOMORROW3 : 'Վաղը + 3օր'; ?>
                    (<?php 
                            echo getwayConnect::getwayCount("SELECT count(*) FROM rg_orders WHERE ((delivery_date >= '" . date("Y-m-d", time() + 86400) . "' AND delivery_date <= '" . date("Y-m-d", time() + 259200) ."')  OR (created_date >= '" . date("Y-m-d", time() + 86400) . "') AND created_date <= '" . date("Y-m-d", time() + 259200) . "' ) {$strict_country}");
                    ?>)
                </button>
                <button class="btn btn-default" name="drf" id="1" onclick="filter(this,true);" value="<?= date("Y-m-d", time() + 86400); ?> to <?= date("Y-m-d", time() + 1296000 ); ?>"><?= (defined('TOMORROW15')) ? TOMORROW15 : 'Վաղը + 15օր'; ?>
                    (<?php 
                            echo getwayConnect::getwayCount("SELECT count(*) FROM rg_orders WHERE ((delivery_date >= '" . date("Y-m-d", time() + 86400) . "' AND delivery_date <= '" . date("Y-m-d", time() + 1296000) ."')  OR (created_date >= '" . date("Y-m-d", time() + 86400) . "') AND created_date <= '" . date("Y-m-d", time() + 1296000) . "' ) {$strict_country}");
                    ?>) 
                </button>
                <button class="btn btn-default" onclick="totalResset();" value=""><?= (defined('RESET')) ? RESET : 'RESET'; ?></button>
                <button class="btn btn-default" onclick="filter(null,true);"><?= (defined('REFRESH')) ? REFRESH : 'REFRESH'; ?></button>
            <?php
            }
            ?>
            <select class="btn btn-default"  style="height: 34px;" id="showCount" onchange="showCount(this);">
                    <option value="50" selected>50</option>
                    <option value="100" >100</option>
                    <option value="200">200</option>
                    <option value="500">500</option>
                    <option value="1000">1000</option>
                    <option value="10000">10000</option>
                    <!--<option value="false">ALL</option>-->
                </select>
            <select name="chng_user" class="btn btn-default" id="slct_user" onchange="filter(null, true);">
                <option value="0" selected>Բոլորը</option>
                <?php 
                    foreach($flourist_filters as $florist_filter){
                        echo "<option value='".$florist_filter['id']."'>".ucfirst((defined($florist_filter['username'])) ?  constant($florist_filter['username']) : $florist_filter['username'])."</option>";
                    }
                    foreach($operator_filters as $operator_filter){
                        echo "<option value='".$operator_filter['username']."'>".ucfirst($operator_filter['username'])."</option>";
                    }
                    foreach($deliverer_filters as $deliverer_filter){
                        echo "<option value='del_".$deliverer_filter['id']."'>".ucfirst($deliverer_filter['name'])."</option>";
                    }
                ?>
            </select>

            
              <div style="display: inline-block;float:right;" class="hidden-print">
                    <?php
                    if(!in_array($userData[0]['id'], array(88,89,90,91,92,93,94,95,96,97,98,99,100,101,102))){
                        if ($cc == "am") {
                            $shuka = $engine->categoryDanger(1, false, storage::$user_id, storage::$selected_storage);
                            $arevtur = $engine->categoryDanger(1, true, storage::$user_id, storage::$selected_storage);
                            $date = $engine->getLastEdit(false);
                            $passed = strtotime($engine->dateutc()) - strtotime($date);
                            $hours = round($passed / 3600);
                            $minutes = round($passed / 60);
                            $par = "";
                            if ($hours > 5) {
                                // $par = "<img src=\"products/template/images/par-par.gif\" align=\"left\" height=\"50\">";
                            }
                            ?>
                            <!-- <span style="font-size:20px;display: inline-block;">
                                <a href="../../print.php" target="_blank">
                                    <strong>ՊԱՏՎԵՐԻ ՍՏԱՏՈՒՍ</strong>
                                </a>
                            </span> -->
                            <!-- <span style="font-size:20px;display: inline-block;">
                                <a href="products/index.php?request=arajark" target="_blank">
                                    <strong><?= (defined('WHAT_TO_ADVICE')) ? WHAT_TO_ADVICE : 'WHAT_TO_ADVICE'; ?></strong>
                                    <img src="products/template/images/cancellation.jpg" height="50px"/>
                                </a>
                            </span> -->
                                <?php
                                    if(!in_array($userData[0]['id'], $simple_florist_ids)){
                                        ?>
                            <a href="../../info/index.php" target="_blank">
                                <img src="./ico/important-announcement.jpg"  alt="Important Announcement" class="peopleIcon" width="80" style="max-width: 100px;">
                            </a>
                                        
                            <div style="font-size:10px;padding-left:50px;display: inline-block;">
                                <strong style="font-size:14px">(<?= storage::selected_name(); ?>)</strong>

                                <br>
                                            <strong style="font-size:12px"><?= (defined('CHANGED')) ? CHANGED : 'CHANGED'; ?><?= ($minutes > 60) ? " {$hours} ".((defined('HOUR_AGO')) ? HOUR_AGO : 'HOUR_AGO').":" : " {$minutes} ".((defined('MINUTE_AGO')) ? MINUTE_AGO : 'MINUTE_AGO').":"; ?></strong>
                                        <?php
                                    }
                                ?>
                            </div>
                            <br>
                            <?php
                        }
                    }
                    ?>
                </div>
        </div>
        <br/><br/>
        
        <div class="btn-group" id="incomplited" style="font-weight: bold;">
            <?php
                if(!in_array($userData[0]['id'], $simple_florist_ids)){
                 ?>
                    <button title="<?= (defined('IS_DEFECTED')) ? IS_DEFECTED : 'IS_DEFECTED'; ?>" class="btn btn-default" name="isdefected" id="35" onclick="filter(this,true);" value="1 to 1" data-filter-name="<?= (defined('IS_DEFECTED')) ? IS_DEFECTED : 'IS_DEFECTED'; ?>"><img src="<?= $rootF; ?>/template/images/problematic.png" height="20" />(<?=getwayConnect::getwayCount("SELECT count(*) FROM `rg_orders` WHERE `out_defect` = 1 OR `order_defect` = 1 ");?>)</button>
                 
                 <?php   
                }
            ?>
            <!-- <button title="<?= (defined('IS_IMPORTANT')) ? IS_IMPORTANT : 'IS_IMPORTANT'; ?>" class="btn btn-default" name="isimportant" id="48" onclick="filter(this,true);" value="1 to 1" data-filter-name="<?= (defined('IS_IMPORTANT')) ? IS_IMPORTANT : 'IS_IMPORTANT'; ?>"> -->
                    <!-- <img src="<?= $rootF; ?>/template/images/important.png" height="20" />(<?php // echo getwayConnect::getwayCount("SELECT count(*) FROM `rg_orders` WHERE `important` = 1");?>) -->
            <!-- </button> -->
             <?php
                if (max(page::filterLevel(3, $levelArray)) >= 33){
                    ?>
                    <button title="<?= (defined('IS_COMPLAIN')) ? IS_COMPLAIN : 'IS_COMPLAIN'; ?>" class="btn btn-default" name="isimportant" id="49" onclick="filter(this,true);" value="1 to 1" data-filter-name="<?= (defined('IS_COMPLAIN')) ? IS_COMPLAIN : 'IS_COMPLAIN'; ?>"><img src="<?= $rootF; ?>/template/images/complain.png" height="20" />(<?=getwayConnect::getwayCount("SELECT count(*) FROM `rg_orders` WHERE `complain` = 1");?>)</button>
                    <?php
                }
            ?>
           <?php
                if(GetOffOnAction('gnman_entaka_filter_in_list') == 1){
                    if(!in_array($userData[0]['id'], $simple_florist_ids)){
                    ?>
                        <button title="Գնման Ենթակա" class="btn btn-default" name="isforpurchase" id="51" onclick="filter(this,true);" value="1 to 1" data-filter-name="<?= (defined('FOR_PURCHASE')) ? FOR_PURCHASE : 'FOR_PURCHASE'; ?>"><img src="<?= $rootF; ?>/template/icons/buttons/for_purchase.png" height="20" />(<?=getwayConnect::getwayCount("SELECT count(*) FROM `rg_orders` WHERE `for_purchase` = 1");?>)</button>
                    <?php
                    }
                }
            ?>

            <?php if(max(page::filterLevel(3, $levelArray)) >= 34): ?>
                <button onclick="sendMail()" class="btn btn-default" title="<?= (defined('SEND_MAIL')) ? SEND_MAIL : 'SEND_MAIL'; ?>"><img src="<?= $rootF; ?>/template/icons/buttons/send_email.png" height="20" /></button>
                <button title='Անավարտ Պատվերներ' class="btn btn-default" style="max-height: 34px;"  name="stf" id="2" onclick="filter(this,true);" value="2" data-filter-name="<?= (defined('PANDING_ORDER')) ? PANDING_ORDER : 'PANDING_ORDER'; ?>">
                    <?= (getwayConnect::getwayCount("SELECT count(*) FROM rg_orders WHERE delivery_status = 2") > 0) ? "<strong style=\"color:#ff0000\">" . getwayConnect::getwayCount("SELECT count(*) FROM rg_orders WHERE delivery_status = 2") . "</strong>" : 0; ?>
                    <img src="<?= $rootF; ?>/template/icons/status/2.png"  height="20" />
                </button>
                <a href="/account/OpenedLogs?from_date=<?php echo date('Y-m-d', strtotime('-3 day', strtotime(date('Y-m-d')))) ?>&user_id=all&type=pending_info" target='_blank' title="Opened" class="btn btn-default">
                    <img src="https://www.flowers-armenia.com/images/live-contact/live-support-message-icon.png" height="18" /></a>
                <!-- <a href='/account/PendingNotes' target='_blank' title='Անավարտ Պատվերների Նշումներ' class="btn btn-default">
                 <img src="https://www.flowers-armenia.com/images/live-contact/live-support-message-icon.png"  height="18" />
                </a> -->
                <div class="btn-group" role="group" id="printyfy">
                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <?= Hide_on_Print;?>
                      <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">
                      <li><label for="id_hide">ID</label><input type="checkbox" id="id_hide" onclick="hide_on_print($(this),'hide-1')" checked/></li>
                      <li><label for="flag_hide"><?=COUNTRY;?></label><input type="checkbox" id="flag_hide" onclick="hide_on_print($(this),'hide-2')" checked/></li>
                      <li><label for="day_hide"><?= (empty(array_intersect(array(89), explode(",", $get_lvl[0])))) ? ((defined('DELIVERY_DAY')) ? DELIVERY_DAY : 'DELIVERY_DAY') : ((defined('TRANSFER_DAY')) ? TRANSFER_DAY : 'TRANSFER_DAY'); ?></label><input type="checkbox" id="day_hide" onclick="hide_on_print($(this),'hide-3')" /></li>
                      <li><label for="status_hide"><?=STATUS;?></label><input type="checkbox" id="status_hide" onclick="hide_on_print($(this),'hide-4')" checked/></li>
                      <li><label for="cday_hide"><?=ORDER_DAY;?></label><input type="checkbox" id="cday_hide" onclick="hide_on_print($(this),'hide-5')" checked/></li>
                      <li><label for="source_hide"><?=ORDER_SOURCE;?></label><input type="checkbox" id="source_hide" onclick="hide_on_print($(this),'hide-6')" checked/></li>
                      <li><label for="price_hide"><?=ORDER_PRICE?></label><input type="checkbox" id="price_hide" onclick="hide_on_print($(this),'hide-7')" checked/></li>
                      <li><label for="product_hide"><?= (empty(array_intersect(array(89), explode(",", $get_lvl[0])))) ? ((defined('ORDERED_PRODUCTS')) ? ORDERED_PRODUCTS : 'ORDERED_PRODUCTS') : ((defined('TO_MEET')) ? TO_MEET : 'TO_MEET'); ?></label><input type="checkbox" id="product_hide" onclick="hide_on_print($(this),'hide-8')" /></li>
                      <li><label for="receiver_hide"><?= (empty(array_intersect(array(89), explode(",", $get_lvl[0])))) ? ((defined('ORDER_RECEIVER')) ? ORDER_RECEIVER : 'ORDER_RECEIVER') : ((defined('WHERE_TO_BE')) ? WHERE_TO_BE : 'WHERE_TO_BE'); ?></label><input type="checkbox" id="receiver_hide" onclick="hide_on_print($(this),'hide-9')" /></li>
                      <li><label for="rAddress_hide"><?= (empty(array_intersect(array(89), explode(",", $get_lvl[0])))) ? ((defined('RECEIVER_ADDRESS')) ? RECEIVER_ADDRESS : 'RECEIVER_ADDRESS') : ((defined('TO_WHERE')) ? TO_WHERE : 'TO_WHERE'); ?></label><input type="checkbox" id="rAddress_hide" onclick="hide_on_print($(this),'hide-10')" /></li>
                      <li><label for="rPhone_hide"><?=ORDER_RECEIVER;?></label><input type="checkbox" id="rPhone_hide" onclick="hide_on_print($(this),'hide-11')" checked/></li>
                      <li><label for="notes_hide"><?=NOTES_FOR_ALL;?></label><input type="checkbox" id="notes_hide" onclick="hide_on_print($(this),'hide-12')" checked/></li>
                      <li><label for="sender_hide"><?=ORDER_SENDER?></label><input type="checkbox" id="sender_hide" onclick="hide_on_print($(this),'hide-14')" checked/></li>
                      <li><label for="sContact_hide"><?=SENDER_CONTACS;?></label><input type="checkbox" id="sContact_hide" onclick="hide_on_print($(this),'hide-15')" checked/></li>
                      <li><label for="sAddress_hide"><?=SENDER_ADDRESS;?></label><input type="checkbox" id="sAddress_hide" onclick="hide_on_print($(this),'hide-16')" checked/></li>
                      <li><label for="gCard_hide"><?=GREETING_CARD;?></label><input type="checkbox" id="gCard_hide" onclick="hide_on_print($(this),'hide-17')" checked checked/></li>
                      <li><label for="nFlorist_hide"><?= (empty(array_intersect(array(89), explode(",", $get_lvl[0])))) ? ((defined('NOTES_FOR_ALL')) ? NOTES_FOR_ALL : 'NOTES_FOR_ALL') : ((defined('NOTES_FOR_DRIVER')) ? NOTES_FOR_DRIVER : 'NOTES_FOR_DRIVER'); ?></label><input type="checkbox" id="nFlorist_hide" onclick="hide_on_print($(this),'hide-18')" /></li>
                      <li><label for="sPoint_hide"><?=SELL_POINT;?></label><input type="checkbox" id="sPoint_hide" onclick="hide_on_print($(this),'hide-19')" checked/></li>
                      <li><label for="log_hide">Log</label><input type="checkbox" id="log_hide" onclick="hide_on_print($(this),'hide-20')" checked/></li>
                    </ul>
                </div>
            <?php endif;?>
            <button type="button" class="btn btn-default" id="showRelatedImages" data-clicked="0" title='Ցուցադրել նկարներով'>
                <img src="<?= $rootF; ?>/template/icons/buttons/show_with_images.png" height="20" />
            </button>
            <?php
                if($userData[0]['id'] == 4 || $userData[0]['id'] == 38){
                    ?>
                    <button type="button" class="btn btn-default display-none" id="showCucanishner" data-clicked="0" title='Ցուցադրել Ցուցանիշներով'>
                        <img src="<?= $rootF; ?>/template/icons/buttons/show_with_procent.png" height="20" />
                    </button>
            <?php
                }
            ?>
            <?php
                if (max(page::filterLevel(3, $levelArray)) >= 33) {
                    ?>
                        <button type="button" class="btn btn-default" onclick="openMail(50408, 7)">Հերթափոխի Ընդունում</button>
                    <?php
                }
            ?>
            <?php
                if(in_array($userData[0]['id'],$access_for_excel)){
                    ?>
                        <button type="button" class="btn btn-default" onclick="createExcellForCurrentList()"><img src="/images/excel.png" style="width: 20px;" title="excel" ></button>
                    <?php
                }
            ?>
            <button type="button" class="btn btn-default" onclick="checkCountStockProducts()"><img src="/images/t.png" style="width: 20px;" title="Stock" ></button>
            
            <?php
                if (max(page::filterLevel(3, $levelArray)) < 33) {
                    if(!in_array($userData[0]['id'], $simple_florist_ids)){
                    ?>
                        <a href='http://new.regard-group.ru/account/flower_orders/products/index.php?cmd=out' type="button" class="btn btn-default"><img src="/images/out_image.png" style="width: 20px;" title="Ելք" ></a>
                    <?php
                    }
                }
                if(!in_array($userData[0]['id'], $simple_florist_ids)){
            ?>
                <span class="warningCount">
                    <span id="warningCount"></span>
                    &nbsp;Warnings
                </span>
                <strong style="font-size:20px;padding-left:50px;">
                    <a href="products/index.php" target="_blank"><?= (defined('PRODUCTS')) ? PRODUCTS : 'PRODUCTS'; ?></a>&nbsp;&nbsp;&nbsp;
                </strong> 
                <?php /* <br><br><?= ($shuka > 5 && $shuka < 10 || $arevtur > 5 && $arevtur < 10) ? "<img src=\"products/template/images/warning-white.gif\"  align=\"left\" height=\"50px\">" : (($shuka > 0 || $arevtur > 0) ? "<img src=\"products/template/images/warning.gif\" align=\"left\" height=\"50px\">" : '') ?> */ ?>
                <strong style="font-size:20px"><?= (defined('MARKET')) ? MARKET : 'MARKET'; ?>(<strong
                        style="color:<?= ($shuka <= 0) ? "green" : "red"; ?>;"><?= $shuka ?></strong>)<?= (defined('TRADE')) ? TRADE : 'TRADE'; ?>(<strong
                        style="color:<?= ($arevtur <= 0) ? "green" : "red"; ?>;"><?= $arevtur ?></strong>)<?= $par ?>
                </strong>  <a href="https://www.flowers-armenia.am/hdm/adg_history.php?m234c68knaixzo2459fgkk23emda3243nf6mfodrxkzot32orejk45jk4ikgfds435j2g34kdsa934k=d9hv8kf6ld5l3nyb438psm58uxjiz8fkj45n19fmsdfk&fasfav523fgedfv=124fgsdgcvc4c32s" target="_blank"> Էլ. ՀԴՄ </a>
            <?php
                }
            ?>
        </div>
    </div>
  
    <div style="clear: both"></div>
        
    <div class="table">
          
        <table class="table table-bordered">
            <thead>
                
                
            <tr class="success">
               
                <?php
                if (max(page::filterLevel(3, $levelArray)) >= 33) {
                    ?>
                    <th class="hide-1" nowrap="nowrap">
                        <div id="loading" class="hidden-print"><img src="<?= $rootF; ?>/template/icons/loader.gif"></div>
                        <input type="checkbox" onclick="checkAll(this);">
                        <button class="btn btn-default" name="orderF" id="12" onclick="filter(this,true);" value="`id` ASC">#<strong
                                id="onC"></strong></button>
                    </th>
                    <th class="hide-2 hidden-print"><img src="<?= $rootF; ?>/template/icons/bonus_title.png"></th>
                    <th class="hide-3">
                    <?= (empty(array_intersect(array(89), explode(",", $get_lvl[0])))) ? ((defined('DELIVERY_DAY')) ? DELIVERY_DAY : 'DELIVERY_DAY') : ((defined('TRANSFER_DAY')) ? TRANSFER_DAY : 'TRANSFER_DAY'); ?>
                        <div class="btn-group" id="orderbyDayHour" style="font-weight: bold;min-width: 100px;">
                            <!--<button class="btn btn-default" ></button>-->
                                <button class="btn btn-default" name="orderF" id="12" onclick="filter(this,true);"
                                value="delivery_date DESC"><?=DAY?></button>
                                <button class="btn btn-default" name="orderF" id="38" onclick="filter(this,true);"
                                value="delivery_time,delivery_time_manual DESC"><?=HOUR?></button>
                        </div>
                    </th>
                    <?php
                } else {
                    ?>
                    <th class="hide-1 hidden-print">
                        <div id="loading"><img src="<?= $rootF; ?>/template/icons/loader.gif"></div>
                        #<strong id="onC"></strong></th>
                    <th class="hide-2">
                        <?= (defined('DELIVERY_DAY')) ? DELIVERY_DAY : 'DELIVERY_DAY'; ?>
                        <div class="btn-group" id="orderbyDayHour" style="font-weight: bold;min-width: 100px;">
                            <!--<button class="btn btn-default" ></button>-->
                                <button class="btn btn-default" name="orderF" id="12" onclick="filter(this,true);"
                                value="delivery_date DESC"><?=DAY?></button>
                                <button class="btn btn-default" name="orderF" id="38" onclick="filter(this,true);"
                                value="delivery_time,delivery_time_manual DESC"><?=HOUR?></button>
                        </div>
                    </th>
                    <?php
                }
                ?>
                <?php 
                    if (strpos($userData[0]['user_level'], '89') !== false) {
                ?>
                <?php
                    if($list_action_fields == 1){
                        ?>
                            <th class="hidden-print">Գործողություն</th>
                        <?php
                    }
                ?>
                <?php } ?>  
                <th class="hide-4 hidden-print">
                    <?php if(max(page::filterLevel(3, $levelArray)) < 33){
                        echo ((defined('DELIVERY_STATUS')) ? DELIVERY_STATUS : 'DELIVERY_STATUS');
                        ?>
                        <button class="btn btn-default" name="orderF" id="12" onclick="filter(this,true);"
                        value="FIELD(delivery_status, '12', '1', '3', '6', '7', '11', '13', '2', '4', '5', '8', '9', '10')">Ստատուս</button>
                        <?php } else { 
                            echo "<img src=\"{$rootF}\/template/icons/exit.png\">"; 
                        }
                    ?>
                </th>

        

    <th class="hide-8"><?= (empty(array_intersect(array(89), explode(",", $get_lvl[0])))) ? ((defined('ORDERED_PRODUCTS')) ? ORDERED_PRODUCTS : 'ORDERED_PRODUCTS') : ((defined('TO_MEET')) ? TO_MEET : 'TO_MEET'); ?></th>            
                <?php
                    if (max(page::filterLevel(3, $levelArray)) < 33) {
                        ?>
                        <!-- <th class="hide-22 hidden-print"><?= (defined('NOTES_FOR_FLORIST')) ? NOTES_FOR_FLORIST : 'NOTES_FOR_FLORIST'; ?></th> -->
                        <?php
                    }
                    if(!in_array($userData[0]['id'], $simple_florist_ids)){
                ?>   
                <th class="hide-10">
                <?= (empty(array_intersect(array(89), explode(",", $get_lvl[0])))) 
                        ? ((defined('RECEIVER_ADDRESS')) ? RECEIVER_ADDRESS : 'RECEIVER_ADDRESS') 
                        : ((defined('TO_WHERE')) ? TO_WHERE : 'TO_WHERE'); ?>
                 </th>
                <?php } ?>
                 
                <?php
                if (max(page::filterLevel(3, $levelArray)) >= 33) {
                    ?>
                    <?php 
                    /// by Haykaz
                    
                    $levelsOfUser =$userData[0]["user_level"];
                    
                    // if (strpos($levelsOfUser, '89') !== false) {
                    ?>
                    <th class="hide-9"><?= (empty(array_intersect(array(89), explode(",", $get_lvl[0])))) ? ((defined('ORDER_RECEIVER')) ? ORDER_RECEIVER : 'ORDER_RECEIVER') : ((defined('WHERE_TO_BE')) ? WHERE_TO_BE : 'WHERE_TO_BE'); ?></th>
                    <?php //} ?>
                    
                    <th class="hide-5 hidden-print">
                        <button class="btn btn-default" name="orderF" id="12" onclick="filter(this,true);" value="created_date DESC"><?= (defined('ORDER_DAY')) ? ORDER_DAY : 'ORDER_DAY'; ?></button>
                    </th>
                    <th class="hide-6 hidden-print">
                        <button class="btn btn-default" name="orderF" id="12" onclick="filter(this,true);" value="order_source DESC">
                            <?= (defined('ORDER_SOURCE')) ? ORDER_SOURCE : 'ORDER_SOURCE'; ?>
                        </button>
                    </th>
                    <?php
                    if (empty(array_intersect(array(89), explode(",", $get_lvl[0])))) {
                        ?>
                        <th class="hide-7 hidden-print"><img src="<?= $rootF ?>/template/icons/price.png" onclick="viewHidePrice();"></th>
                        <?php
                    }

                }
                ?>
                <?php
                /*
                if (max(page::filterLevel(3, $levelArray)) < 33) {
                    ?>
                    <th class="hide-12 hidden-print"><?= (defined('GREETING_CARD')) ? GREETING_CARD : 'GREETING_CARD'; ?></th>
                    <?php
                } */ 
                ?>
                <?php 
                /// by Haykaz
                
                $levelsOfUser =$userData[0]["user_level"];
                
                if ((strpos($levelsOfUser, '30') !== false || strpos($levelsOfUser, '31') !== false) && strpos($levelsOfUser, '89') == false) {
                ?>
                <!-- <th class="hidden-print">Գործողություն</th> -->
                <?php
                    if(!in_array($userData[0]['id'], $simple_florist_ids)){
                        ?>
                            <th class="hide-9"><?= (empty(array_intersect(array(89), explode(",", $get_lvl[0])))) ? ((defined('ORDER_RECEIVER')) ? ORDER_RECEIVER : 'ORDER_RECEIVER') : ((defined('WHERE_TO_BE')) ? WHERE_TO_BE : 'WHERE_TO_BE'); ?></th>
                        <?php
                    }
                ?>
                <?php } ?>

                <?php
                    if (max(page::filterLevel(3, $levelArray)) > 33) {
                ?>
                <th class="hide-11 hidden-print">
                <?= (defined('RECEIVER_PHONE')) ? RECEIVER_PHONE : 'RECEIVER_PHONE'; ?></td>
                <?php
                }
                ?>
                <?php /* if (min(page::filterLevel(3, $levelArray)) > 33){
                    ?>
                    <th class="hide-12 hidden-print">
                        <button class="btn btn-default show-ALL"><?= (defined('NOTES')) ? NOTES : 'NOTES'; ?></button>
                    </th>
                    <?php
                } */
                ?>
                <?php
                    if(!in_array($userData[0]['id'], $simple_florist_ids)){
                        ?>
                            <?=(max(page::filterLevel(3, $levelArray)) >= 33) ? "" : "<th class=\"hide-13\">Փոխել Առաքիչին</th>"; ?>
                        <?php
                    }
                ?>
                <?php
                    if (max(page::filterLevel(3, $levelArray)) > 33) {
                ?>

                <?php
                    if($list_action_fields == 1 && strpos($userData[0]['user_level'], '36') !== false ){
                        ?>
                            <th class="hidden-print">Գործողություն</th>
                        <?php
                    }
                ?>


                <th class="hide-14 hidden-print"><?= (defined('ORDER_SENDER')) ? ORDER_SENDER : 'ORDER_SENDER'; ?></th>
                <?php
                    }
                ?>
                <?php if(max(page::filterLevel(3, $levelArray)) > 33){ ?>
                    <th class="hide-15 hidden-print">
                    <?= (defined('SENDER_PHONE')) ? SENDER_PHONE : 'SENDER_PHONE'; ?><?= (max(page::filterLevel(3, $levelArray)) >= 33) ? "<br/>" . ((defined('E_MAIL')) ? E_MAIL : 'E_MAIL') : ""; ?></td>
                <?php
                    }
                ?>
                <?php if(max(page::filterLevel(3, $levelArray)) > 33){ ?>
                <th class="hide-16 hidden-print">
                <?= (max(page::filterLevel(3, $levelArray)) >= 33) ? ((defined('SENDER_ADDRESS')) ? SENDER_ADDRESS : 'SENDER_ADDRESS') : ((defined('SENDER_COUNTRY')) ? SENDER_COUNTRY : 'SENDER_COUNTRY'); ?></td>
                <?php
                    }
                ?>
                <?php
                function changeNotes()
                {
                    global $get_lvl;
                    return (empty(array_intersect(array(89), explode(",", $get_lvl[0])))) ? ((defined('NOTES_FOR_FLORIST')) ? NOTES_FOR_FLORIST : 'NOTES_FOR_FLORIST') : ((defined('NOTES_FOR_DRIVER')) ? NOTES_FOR_DRIVER : 'NOTES_FOR_DRIVER');
                }

                if (max(page::filterLevel(3, $levelArray)) >= 33) {
                    ?>

                    <!-- <th class="hide-17 hidden-print"><?= (defined('GREETING_CARD')) ? GREETING_CARD : 'GREETING_CARD'; ?></th> -->
                    <!-- <th class="hide-18"><?= (max(page::filterLevel(3, $levelArray)) >= 33) ? changeNotes() : "<strong style=\"color:#ff0000\">" . ((defined('NOTES_FOR_FLORIST')) ? NOTES_FOR_FLORIST : 'NOTES_FOR_FLORIST') . "</strong>"; ?></th> -->

                    <th class="hide-19 hidden-print"><?= (defined('SELL_POINT')) ? SELL_POINT : 'SELL_POINT'; ?></th>
                     <!-- <th class="hide-20 hidden-print"><img src="<?= $rootF; ?>/template/icons/info.png"></th> -->
                    <?php
                }
                ?>
                <?= (max(page::filterLevel(3, $levelArray)) < 33) ? "<th class=\"hide-21\"><img src=\"{$rootF}/template/icons/info.png\"></th>" : ""; ?>
                
            </tr>
            </thead>
            <tbody id="dataTable">
            <!--data table-->
            </tbody>
        </table>
    </div>
    <nav style="width: 100%;text-align: center;">
        <ul class="pagination" id="buildPages">
            <!-- <li class="active"><a href="#">1</a></li> -->
        </ul>
    </nav>

    <?php 
        if( !in_array($userData[0]['id'], array(1, 2, 27, 35, 87)) ){
    ?>
    <div class="urgentProductsDiv hidden-print">
        <button data-dz-remove="" class="btn btn-danger btn-xs unpublished BtnForHideBestOffer"><i class="glyphicon glyphicon-remove"></i></button>
        <?php 
        if(!in_array($userData[0]['id'], $simple_florist_ids)){
            if(isset($products) && !empty($products)){
                echo "<b>Այսօրվա Լավագույն և <br>Հրատապ Առաջարկները!</b><br>";
                foreach($products as $product){
                    echo "<div class='product' data-id='".$product['id']."' data-name='".$product['product_name']."' data-img='../flower_orders/products/images/".$product['product_image']."' data-int-price='" . $product['int_partner_price'] ."' data-arm-price='" . $product['arm_partner_price'] . "' data-price='".$product['pprice']."'>";
                    echo "<img class='productImage' src='../flower_orders/products/images/".$product['product_image']."'>";
                    if($product['pstatuse'] != '1'){
                    }
                    echo "<br>";
                    echo "<span>".$product['product_name']."</span>";
                    echo "</div>";
                }
            }
        }
        ?>
    </div>
    <div class="productInfo">
            
    </div>
    <?php 
        }
    ?>
</div>
<!-- Added By Hrach -->
<div class="modal fade" id="checkCountStockOfCurrentList" tabindex="-1" role="dialog" aria-labelledby="count_of_list">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="totalCount">Օգտագորված Ապրանքներ</h4>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <td>Ապրանքի Անուն</td>
                        <td>Քանակ</td>
                        <td>Ինքնարժեք</td>
                    </tr>
                    </thead>
                    <tbody class="count_stock_body">
                    </tbody>
                    <tbody>
                        <tr>
                            <td>
                                Ընդհանուր Հանրագումար
                            </td>
                            <td></td>
                            <td class='totalPnetcostModal'></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="change_log" tabindex="-1" role="dialog" aria-labelledby="log_data">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="log_data">Change Log <span class='for_order_number'></span></h4>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <td>Id</td>
                        <td>Log</td>
                        <td>Date</td>
                        <td>Current Status</td>
                        <td>User Name</td>
                    </tr>
                    </thead>
                    <tbody class="log_table_body">
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="disadvantage_modal" tabindex="-1" role="dialog" aria-labelledby="Issues">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <span class="full_name_selected_user"></span> <span class='selectedDateDisplayPart'></span>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                  <ul class="nav nav-tabs">
                    <li class="active"><a class='activeTabEach' data-toggle="tab" href="#main">Main</a></li>
                    <li class="statistic_tab"><a data-toggle="tab" href="#statistic">Statistic</a></li>
                    <li class="statistic_tab_florist"><a data-toggle="tab" href="#statistic_florist">Statistic</a></li>
                    <li class="statistic_tab_driver"><a data-toggle="tab" href="#statistic_driver">Statistic</a></li>
                    <?php
                    	if(in_array($userData[0]['id'], array(4,38,86))){
                    		?>
                    			<li class="managerial_tab"><a data-toggle="tab" href="#managerial">Managerial</a></li>
                    		<?php
                    	}
                    ?>
                  </ul>

                  <div class="tab-content">
                    <div id="main" class="tab-pane fade in active">
                     <table class="table table-bordered">
                        <thead>
                        <tr>
                            <td>Id</td>
                            <td>Title</td>
                            <td>Description</td>
                            <td>Malus</td>
                            <td>Created</td>
                        </tr>
                        </thead>
                        <tbody class="disadvantage_table_body">
                        </tbody>
                    </table>
                    </div>
                    <?php
                    $managerial_sent = 0;
                    	if(in_array($userData[0]['id'], array(4,38,86))){
                    		$managerial_sent = 1;
                    		?>
			                    <div id="managerial" class="tab-pane fade in">
			                     <table class="table table-bordered">
			                        <thead>
			                        <tr>
			                            <td>Id</td>
			                            <td>Member</td>
			                            <td>Title</td>
			                            <td>Description</td>
			                            <td>Malus</td>
			                            <td>Created</td>
			                        </tr>
			                        </thead>
			                        <tbody class="managerial_table_body">
			                        </tbody>
			                    </table>
			                    </div>
                    		<?php
                    	}
                    ?>
			        <input type='hidden' class='managerial_sent' value="<?php echo $managerial_sent ?>" >
                    <div id="statistic_florist" class="tab-pane fade">
                        <h5>Պատրաստված - <span class="prepared_all_shift"></span></h5>
                        
                    </div>
                    <div id="statistic_driver" class="tab-pane fade">
                        <h5>Done - <span data-click=1 class='calculation_all_orders hover_cursor_pointer'></span><br><div class='calculation_display_orders' style="display:none;overflow:auto"></div></h5>
                        
                    </div>
                    <div id="statistic" class="tab-pane fade">
                        <h5>Day Shift 09.01 - 18.00 
                            <select data-shift="day" class="shift_days_dropdown">
                                <option>Days</option>
                                <?php
                                    for($i = 1;$i<31;$i++){
                                        ?>
                                            <option><?php echo $i ?></option>
                                        <?php
                                    }
                                ?>
                            </select>
                            <span style="font-size:15px" class="total_procent_for_day"></span>
                        </h5>
                        <h5>Վճարված պատվերներ - <span class="payed_day_shift"></span><span class='payed_day_shift_procent'></span></h5>
                        <h5>Կոմունիկացիա - <span class="communication_orders_day"></span><span class='communication_orders_day_procent' ></span></h5>
                        <h5>Բաց թողնված - <span class="bac_toxnvac_orders_day"></span><span class='bac_toxnvac_orders_day_procent' ></span></h5>
                        <h5>Չեղյալ - <span class="chexyal_orders_day"></span><span class='chexyal_orders_day_procent' ></span></h5>
                        <hr>
                        <h5>Evening Shift 18.01 - 00.00
                            <select data-shift="evening" class="shift_days_dropdown">
                                <option>Days</option>
                                <?php
                                    for($i = 1;$i<31;$i++){
                                        ?>
                                            <option><?php echo $i ?></option>
                                        <?php
                                    }
                                ?>
                            </select>
                            <span style="font-size:15px" class="total_procent_for_evening"></span>
                        </h5>
                        <h5>Վճարված պատվերներ - <span class="payed_evening_shift"></span><span class='payed_evening_shift_procent'></span> </h5>
                        <h5>Կոմունիկացիա - <span class="communication_orders_evening"></span><span class='communication_orders_evening_procent'></span></h5>
                        <h5>Բաց թողնված - <span class="bac_toxnvac_orders_evening"></span><span class='bac_toxnvac_orders_evening_procent'></span> </h5>
                        <h5>Չեղյալ - <span class="chexyal_orders_evening"></span><span class='chexyal_orders_evening_procent'></span> </h5>
                        <hr>
                        <h5>Night Shift  00.01 - 09.00
                            <select data-shift="night" class="shift_days_dropdown">
                                <option>Days</option>
                                <?php
                                    for($i = 1;$i<31;$i++){
                                        ?>
                                            <option><?php echo $i ?></option>
                                        <?php
                                    }
                                ?>
                            </select>
                            <span style="font-size:15px" class="total_procent_for_night"></span>
                        </h5>
                        <h5>Վճարված պատվերներ - <span class="payed_night_shift"></span><span class='payed_night_shift_procent'></span></h5>
                        <h5>Կոմունիկացիա - <span class="communication_orders_night"></span><span class='communication_orders_night_procent'></span></h5>
                        <h5>Բաց թողնված - <span class="bac_toxnvac_orders_night"></span><span class='bac_toxnvac_orders_night_procent'></span></h5>
                        <h5>Չեղյալ - <span class="chexyal_orders_night"></span><span class='chexyal_orders_night_procent'></span></h5>
                        <hr>
                        <h5>Բոնուս - <span class='bonus_orders'></span> / Մալուս - <span class="malus_orders"></span> | <span class='malus_procent'></span>%</h5>
                        <h5>Վճարված - <span class='payed_orders'></span> / Չվճարված - <span class="not_payed_orders"></span> | <span class='not_payed_procent'></span>%</h5>
                        <hr><br>
                        <div class='col-md-12 for_charts'></div>

                    </div>
                  </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!--  -->
<input type='hidden' class='userDataLevelNumber' value="<?=$userData[0]['user_level'] ?>">
<input type='hidden' class='OffOnOfStugelButton' value="<?=GetOffOnAction('orders_page_stugel_button') ?>">
<input type='hidden' class='display_image_email_button' value="<?=GetOffOnAction('display_image_email_button') ?>">
<input type='hidden' class='OffOnOfPatrastButton' value="<?=GetOffOnAction('orders_page_patrast_button') ?>">
<input type='hidden' class='OffOnOfAraqumButton' value="<?=GetOffOnAction('orders_page_araqum_button') ?>">
<input type='hidden' class='OffOnOfAraqvecButton' value="<?=GetOffOnAction('orders_page_araqvec_button') ?>">
<!-- initialize library-->
<!-- Latest jquery compiled and minified JavaScript -->
<script src="https://code.jquery.com/jquery-latest.min.js"></script>
<!-- Bootstrap minified JavaScript -->
<script src="<?= $rootF ?>/template/bootstrap/js/bootstrap.min.js"></script>
<script src="https://code.highcharts.com/highcharts.js"></script>
<!--end initialize library-->
<!-- Menu Toggle Script -->
<!-- Bootstrap minified JavaScript -->
<script src="<?= $rootF ?>/template/js/accounting.min.js"></script>
<script src="<?= $rootF ?>/template/datepicker/js/bootstrap-datepicker.js"></script>
<script src="<?= $rootF ?>/template/js/phpjs.js"></script>
<script src="<?= $rootF ?>/template/rangedate/moment.min.js"></script>
<script src="<?= $rootF ?>/template/rangedate/jquery.daterangepicker.js"></script>
<script src="<?= $rootF ?>/template/js/imagelightbox.min.js"></script>

<div id="editor">
    <div id="editorMenu">
        <button id="editMM" onclick="mmwindow();" style="width:25px;height:25px;">+</button>
    </div>
    <div id="dataMsg">

    </div>
    <script src="pull.js?v=<?= rand(1, 99); ?>"></script>
</div>

<script type="text/javascript">
    var loggedUserid =<?=$userData[0]["id"]?>;
    var loggedUserLevel = "<?=$userData[0]["user_level"]?>";
    var loggedUserLevelArray = loggedUserLevel.split(',');
    var isFloristLoggedIn = loggedUserLevelArray.indexOf("30");
    var drive_prices = <?=page::getJsonData("drive_prices");?>;
    var floristLogin = false;
    if(isFloristLoggedIn >= 0){
        floristLogin = true;
    }
    // Added By Hrach
    $(document).on('click','.BtnForHideBestOffer',function(){
        $(".urgentProductsDiv").remove();
    })
    // gnman entaka only for Hovik
    $(document).on('change','.forPurchase',function(){
        var user_id = $(this).attr('data-user');
        if(user_id != 27){
            $(this).attr('disabled',true)
        }
    })
    var operators_info;
    $.ajax({
        url: location.href,
        type: 'post',
        data: {
            get_operators_info: true,
        },
        success: function(resp){
            operators_info = JSON.parse(resp);
        }
    })
    var monthNames = new Array("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");
    function replaceDatetimeFormat(datetime){
        if(datetime != null){
            var arr = datetime.split(" ");
            var mycDate = arr[0].split("-");
            return mycDate[2] + "-" + monthNames[mycDate[1] - 1] + "-" + mycDate[0] + " " + arr[1];
        }
        return datetime;
    }
    $(document).on('click','.readMoreButtonAjaxNotes',function(){
        var order_id = $(this).attr('data-order-id');
        if($(".forAjaxNotesValue_"+order_id).hasClass('display-none')){
            $(".forAjaxNotesValue_"+order_id).removeClass('display-none')
            $.ajax({
                url: location.href,
                type: 'post',
                data: {
                    getNoteForOrder: true,
                    order_id: order_id,
                },
                success: function(resp){
                    if(resp != '[]'){
                        resp = JSON.parse(resp);
                        $(".forAjaxNotesValue_"+order_id + " span").html(resp[0].value + "<br>")
                    }
                }
            })
        }
        else{
            $(".forAjaxNotesValue_"+order_id).addClass('display-none')
        }
    })
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
    $(document).on('click','.readMoreButtonAjaxGreetingCard',function(){
        var order_id = $(this).attr('data-order-id');
        if($(".forAjaxGreetingCardValue_"+order_id).hasClass('display-none')){
            $(".forAjaxGreetingCardValue_"+order_id).removeClass('display-none')
            $.ajax({
                url: location.href,
                type: 'post',
                data: {
                    getGreetingCardForOrder: true,
                    order_id: order_id,
                },
                success: function(resp){
                    if(resp != '[]'){
                        resp = JSON.parse(resp);
                        $(".forAjaxGreetingCardValue_"+order_id + " span").html(resp[0].value + "<br>")
                    }
                }
            })
        }
        else{
            $(".forAjaxGreetingCardValue_"+order_id + " span").html('')
            $(".forAjaxGreetingCardValue_"+order_id).addClass('display-none')
        }
    })
    $(document).on('click','.readMoreButtonAjaxFloristNote',function(){
        var order_id = $(this).attr('data-order-id');
        if($(".forAjaxFloristNoteValue_"+order_id).hasClass('display-none')){
            $(".forAjaxFloristNoteValue_"+order_id).removeClass('display-none')
            $.ajax({
                url: location.href,
                type: 'post',
                data: {
                    getFloristNoteForOrder: true,
                    order_id: order_id,
                },
                success: function(resp){
                    if(resp != '[]'){
                        resp = JSON.parse(resp);
                        $(".forAjaxFloristNoteValue_"+order_id + " span").html(resp[0].value + "<br>")
                    }
                }
            })
        }
        else{
            $(".forAjaxFloristNoteValue_"+order_id + " span").html('')
            $(".forAjaxFloristNoteValue_"+order_id).addClass('display-none')
        }
    })
    
    var userDataLevelNumber = $(".userDataLevelNumber").val();
    var userDataLevelNumber_array = userDataLevelNumber.split(',')

    $(document).on('click','.btn_for_save_end_time',function(){
        var end_time = $(".end_time").val();
        var date_id = $(".end_time").attr('data-id');
        $.ajax({
            url: location.href,
            type: 'post',
            data: {
                add_end_time: true,
                end_time: end_time,
                date_id: date_id,
            },
            success: function(resp){
                alert("Շնորհակալություն <?= $userData[0]['full_name_am'] ?>, Դուք ավարտեցիք այսօրվա ձեր աշխատանքային օրը` " + end_time + ", մաղթում ենք հաճելի հանգիստ!")
                location.reload()
            }
        })
    })
    $(document).on('click','.btn_for_save_start_time',function(){
        var start_time = $(".start_time").val()
        $.ajax({
            url: location.href,
            type: 'post',
            data: {
                add_start_time: true,
                start_time: start_time,
            },
            success: function(resp){
                alert("Հարգելի <?= $userData[0]['full_name_am'] ?> , Դուք սկսեցիք այսօրվա ձեր աշխատանքային օրը " + start_time + "` մաղթում ենք հաճելի օր!")
                location.reload()
            }
        })
    })
    var status_xml_approve_array = [1,6,3,7,11,12,13];
    var payment_type_array = [15,23,16,24,25,11,12,13,26,27,28,30,31,5,19,33];
    $(document).on('click','.showHideUploadedTimeXML',function(){
        var order_id = $(this).attr('data-order-id');
        if($(".textShowUploadedTime_" + order_id).hasClass('display-none')){

            $.ajax({
                url: location.href,
                type: 'post',
                data: {
                    getorderDownloads: true,
                    order_id: order_id,
                },
                success: function(resp){
                    if(resp.length > 5){
                        resp = JSON.parse(resp);
                        var html = '';
                        if(resp.resultHdm.length > 0){
                            html+="Էլ․ ՀԴՄ՝ Տպված է"
                            for(var i = 0 ; i < resp.resultHdm.length ;i++){
                                html+= '<li> ' + resp.resultHdm[i].printed_time + '</li>'
                            }
                            html+="<hr>";
                        }
                        if(resp.result.length > 0){
                            html+="Հարկային հաշիվ՝ դուրս գրված է"
                            for(var i = 0 ; i < resp.result.length ;i++){
                                html+= '<li> ' + resp.result[i].downloaded_datetime + " " + resp.hdm_invoice +   ' </li>'
                            }
                        }
                        for(var i = 0 ; i < resp.tax_type_count_array.length ;i++){
                            html+= '<li> ' + resp.tax_type_count_array[i].type + "` " + resp.tax_type_count_array[i].quantity +   ' հատ </li>'
                        }
                        $(".textShowUploadedTime_" + order_id).removeClass('display-none')
                        $(".textShowUploadedTime_" + order_id).html(html)
                    }
                }
            })
        }
        else{
            $(".textShowUploadedTime_" + order_id).addClass('display-none')
            $(".textShowUploadedTime_" + order_id).html('')
        }
    })
    $(document).on('click','.show_log_of_order',function(){
        $('#change_log').modal('show');
        $(".log_table_body").empty();
        var order_id = $(this).data('order-id');
        $(".for_order_number").html(order_id);
        $.ajax({
            url: location.href,
            type: 'post',
            data: {
                getorderlog: true,
                order_id: order_id,
            },
            success: function(resp){
                if(resp.length > 5){
                    resp = JSON.parse(resp);
                    for(var i = 0 ; i < resp.order_log.length ; i++ ){
                        var html="<tr>";
                                html+="<td>";
                                    html+= i+10
                                html+="</td>";
                                html+="<td>";
                                    html+=resp.order_log[i].description
                                html+="</td>";
                                html+="<td>";
                                    html+=resp.order_log[i].date
                                html+="</td>";
                                html+="<td>";
                                    if(resp.order_log[i].name_am == 'Վճարումը դեռ չկատարված'){
                                        html+='Անավարտ';
                                    }
                                    else{
                                        html+=resp.order_log[i].name_am
                                    }
                                html+="</td>";
                                html+="<td>";
                                    if(resp.order_log[i]['full_name_am'] != ''){
                                        html+=resp.order_log[i].full_name_am
                                    }
                                    else{
                                        html+=resp.order_log[i].username
                                    }
                                html+="</td>";
                            html+="</tr>";
                        $(".log_table_body").append(html);
                    }
                }
            }
        })
    })
    // 
    var partners_ids = [<?=($pid_data = getwayConnect::getwayData("SELECT `filter_value` FROM `global_filters` WHERE `name` = 'FLOWERS_PARTNERS'")) ? $pid_data[0]['filter_value'] : '';?>];
    var timoutSet = null;
    var data = {};
    var send_data = "";
    var data_type = "flower";
    var fromP = 0;
    var toP = <?=(isset($userData[0]["username"]) && strtolower($userData[0]["username"]) == "ani") ? 50 : 50;?>;//listi erkarutyun #1
    var whoreceived = <?=page::getJsonData("delivery_receiver");?>;
    var payType = <?=page::getJsonData("delivery_payment");?>;
    var sourceType = <?=page::getJsonData("delivery_source");?>;
    var timeType = <?=page::getJsonData("delivery_time");?>;
    var sellPoint = <?=page::getJsonData("delivery_sellpoint");?>;
    var subregionType = <?=page::getJsonData("delivery_subregion", "code");?>;
    var streetType = <?=page::getJsonData("delivery_street", "code");?>;
    var statusTitle = <?=page::getJsonData("delivery_status");?>;
    var recLang = <?=page::getJsonData("delivery_language");?>;
    var driver_name = <?=page::getJsonData("delivery_deliverer");?>;
    var delivery_reason = <?=page::getJsonData("delivery_reason");?>;
    var primary_language = <?=page::getJsonData("delivery_language");?>;
    var who_received = <?=page::getJsonData("delivery_receiver");?>;
    var driver_car = <?=page::getJsonData("delivery_drivers");?>;
    var order_reason = <?=page::getJsonData("delivery_reason");?>;
    window.sum_overall = {"total":0,"spend":0,"percent":0,"left_over":0};
    window.pNum = 0;
    var orders_page_price_list_from_cba = $(".orders_page_price_list_from_cba").val();
    var RUB_currency = $(".rub_value_currency").val();
    var USD_currency = $(".usd_value_currency").val();
    var EUR_currency = $(".eur_value_currency").val();
    var IRR_currency = $(".irr_value_currency").val();
    var GBP_currency = $(".gbp_value_currency").val();
    console.log(RUB_currency,USD_currency,EUR_currency,IRR_currency,GBP_currency)
    if(orders_page_price_list_from_cba == 1){
        console.log('Price Exchange from CBA');
    }
    var vjarvacIds = Array(1,3,6,12,13)
    window.fromPageCount = 0;
    var global_filter_text = '';
    var order_currency = {
        "USD": USD_currency,
        "1": USD_currency,
        "EUR": EUR_currency,
        "4": EUR_currency,
        "RUB": RUB_currency,
        "2": RUB_currency,
        "IRR": IRR_currency,
        "6": IRR_currency,
        "GBP": GBP_currency,
        "5": GBP_currency,
        "convert": function ($ISO, $price) {
            if (this[$ISO]) {
                return this[$ISO] * $price;
            } else {
                return $price;
            }
        },
        "pfp": function ($total, $actual) {
            return ($total > 0) ? (100 * $actual) / $total : 0;
        }
    };

    <?php
    if(max(page::filterLevel(3, $levelArray)) >= 33)
    {
    ?>
    $(document).ready(function(){
        
        // let today = new Date();
        // today.setMonth(today.getMonth() - 3);
        // let date_default_val = today.toLocaleDateString() + " to ";
        // today.setMonth(today.getMonth() + 6);
        // date_default_val += today.toLocaleDateString();
        // $('[addon="rangedate"]').val(date_default_val);
        // data['drf'] = {
        //     filter: 1,
        //     value: date_default_val
        // }

    })
    <?php } ?>
    function firstToUpperCase(str) {
        return str.substr(0, 1).toUpperCase() + str.substr(1);
    }
    <?php
    if(max(page::filterLevel(3, $levelArray)) < 33)
    {
    ?>
    data["orderF"] = {"filter": 12, "value": "`delivery_time` ASC"};
    data["adf"] = {"filter": 17, "value": "<?=date("Y-m-d");?>"};
    <?php
    }
    ?>
    function filter(el, onfilter) {

        <?php if($user_country > 0){?>
        data["ccf"] = {"filter": 50, "value":<?=$user_country?>};
        <?php }?>
        if (el) {
            var element = jQuery("#" + el.id + " option:selected");
            if (element.attr("data-prel")) {
                hfilter(element.attr("data-prel"));
            }
        }
        $("#loading").css("display", "block");
        if (onfilter) {
            fromP = 0;
            if (data["adf"]) {
                //data["orderF"] = {"filter":12,"value":"delivery_time ASC"};
            }
            if (data["drf"]) {
                //data["orderF"] = {"filter":12,"value":"delivery_date DESC"};
            }
            
        }
        if (data['globalf']) {
            if($("input[name='globalf']").val().length <= 0){
                global_filter_text = false;
                delete data['globalf'];
            }
        }else{
            global_filter_text = false;
        }
        if($("input[name='globalf']").val()){
            if ($("input[name='globalf']").val().length > 0) {
                global_filter_text = $("input[name='globalf']").val();
            }else{
                global_filter_text = false;
            }
        }
        if (el) {
            if (!el.value || el.value == null || el.value == "") {
                delete data[el.name];
            } else {
                //data.push([el.id] = el.value;
                if(el.name == 'isdefected'){
                    data = {};
                }else{
                    delete data['isdefected'];
                }
                if(el.name == 'isforpurchase'){
                    data = {};
                }else{
                    delete data['isforpurchase'];
                }
                data[el.name] = {"filter": el.id, "value": el.value};
            }
        }
        if(el){
            if($(el).attr('name') == 'adf'){
                delete data['drf'];       
            } else if($(el).attr('name') == 'drf'){
                delete data['adf'];
            }
        }
        if (onfilter) {
            if (data["orderF"]) {
                if (data["orderF"].value.search(/ASC/g) > 0) {
                    $("[id=" + data["orderF"].filter + "]").each(function () {
                        if ($(this).val() == data["orderF"].value) {
                            var TempValue = $(this).val();
                            TempValue = TempValue.replace(/ASC/g, "DESC");
                            $(this).val(TempValue);
                        }
                    });
                }
                if (data["orderF"].value.search(/DESC/g) > 0) {
                    $("[id=" + data["orderF"].filter + "]").each(function () {
                        if ($(this).val() == data["orderF"].value) {
                            var TempValue = $(this).val();
                            TempValue = TempValue.replace(/DESC/g, "ASC")
                            $(this).val(TempValue);
                        }
                    });
                }

            }
            delete data['pg_flt'];
            data['pg_flt'] = $('#slct_prd_type').val();
            delete data['pg_usr_flt'];
            data['pg_usr_flt'] = $('#slct_user').val();
        } else {
            delete data['pg_flt'];
            data['pg_flt'] = $('#slct_prd_type').val();
        }
        var activeFilter = "";
        var mu;
        for (mu in data) {
            //<li class=\"active\">Data</li>
        if ($(el).attr("data-filter-name")) {
                activeFilter += "<li class=\"active\">" + $("button[id = " + data[mu].filter+"]").attr("data-filter-name") + "</li>";
            } else if ($("#" + data[mu].filter).attr("placeholder")) {
                activeFilter += "<li class=\"active\">" + $("#" + data[mu].filter).attr("placeholder") + ":" + data[mu].value + "</li>";
            } else if ($("#" + data[mu].filter).find(":selected").text()) {
                activeFilter += "<li class=\"active\">" + $("#" + data[mu].filter).find(":selected").text() + "</li>";
            } else if ($("#" + data[mu].filter).text()) {
                //activeFilter += "<li class=\"active\">"+$("#"+data[mu].filter).text()+"</li>";
            }
        }
        // console.log(data)
        $("#activeFilters").html(activeFilter);
        var data_encode = base64_encode(json_encode(data));
        //console.log(data_encode);
        //console.log(base64_decode(data_encode));
        if (data) {
            send_data = "&encodedData=" + data_encode;
        } else {
            send_data = "";
        }
        var firstConnectMethods = [];
        firstConnectMethods[13] = 'Viber';
        firstConnectMethods[14] = 'WhatsApp';
        firstConnectMethods[11] = 'Phone';
        firstConnectMethods[2]  = 'Live Chat';
        firstConnectMethods[3]  = 'Skype';
        firstConnectMethods[10] = 'Email';
        firstConnectMethods[18] = 'Telegram';
        var userFriendly = "class=\"active\"";
        var first = false;
        clearTimeout(timoutSet);
        timoutSet = setTimeout(function () {
            //start
            // console.log(data_type, data)
            $.get("<?=$rootF?>/data.php?cmd=data&page=" + data_type + send_data + "&paginator=" + fromP + ":" + toP, function (get_data) {
                var CCo = 0;
                var tableData = get_data.data;
                var countP = get_data.count;
                var is_defect = "";
                var is_important = "";
                var out_text = '<?=(defined("OUT")) ? OUT : "OUT";?>';
                var check_text = '<?=(defined("CHECK")) ? CHECK : "CHECK";?>';
                // fromP = buildPaginator(countP, fromP, toP,pNum);
                sum_overall.total = 0;
                sum_overall.spend = 0;
                sum_overall.percent = 0;
                sum_overall.left_over = 0;
                var htmlData = "";
                var showA = "";
                if (countP > 0) {
                    for (var i = 0; i < tableData.length; i++) {
                        var d = tableData[i];
                        window.partner_icon = (partners_ids.indexOf(parseInt(d.sell_point)) > -1) ? '1<img width="25" src="<?=$rootF?>/template/icons/partner_icon.png" title="<?=(defined("PARTNER")) ? PARTNER : "PARTNER";?>"/><span class="color_red text_bolder">#' + d.order_source_optional + '</span> ':'';
                        window.costage = '';
                        <?php
                        // for admin part
                        if(!empty(array_intersect(array(99), $get_lvl))){
                        ?>
                        d.price = number_format(d.price, '0', ',', '');
                        var price_color = 'inherit';
                        if(d.payment_type == 18){
                            price_color ='red';
                        }
                        if (d.price > 0) {
                            //buy.am 16 20% commission
                            //memu.am 37 25% commission
                            var $price_differ = 0;
                            var $total_price = order_currency.convert(d.currency, d.price);

                            if (d.sell_point == 16) {
                                var $price_differ = ($total_price * 20 ) / 100;
                                $total_price = $total_price - $price_differ;
                            } else if (d.sell_point == 15) {
                                var $price_differ = ($total_price * 25 ) / 100;
                                $total_price = $total_price - $price_differ;
                            } else if (d.sell_point == 32) {
                                var $price_differ = ($total_price * 20 ) / 100;
                                $total_price = $total_price - $price_differ;
                            }
                            var $left_over_price = $total_price - d.pNetcost;
                            var $percent = order_currency.pfp(d.pNetcost, $left_over_price);
                            sum_overall.total += parseInt($total_price);
                            sum_overall.spend += parseInt(d.pNetcost);
                            sum_overall.percent += parseInt($percent);
                            sum_overall.left_over += parseInt($left_over_price);
                            //console.log($percent,$total_price,$left_over_price);
                            <?php
                                if($show_pnetcost_procent_in_list == 1){
                            ?>
                            $sales_index_text_color = '';
                            if ($percent <= 20) {
                                $sales_index_text_color = 'red';
                            } else if ($percent > 20 && $percent <= 30) {
                                $sales_index_text_color = 'Black';
                            } else if ($percent > 30 && $percent <= 50){
                                $sales_index_text_color = 'Yellow';
                            } else if ($percent > 50 && $percent <= 75) {
                                $sales_index_text_color = 'darkslategrey';
                            } else if ($percent > 75 && $percent <= 100) {
                                $sales_index_text_color = 'Magenta';
                            }  else if ($percent > 100 && $percent <= 150) {
                                $sales_index_text_color = 'Lightgreen';
                            }  else if ($percent > 150 && $percent <= 200) {
                                $sales_index_text_color = 'Blue';
                            } else if ($percent > 0 && $percent > 200) {
                                $sales_index_text_color = 'Green';
                            }
                            $cucanishDivPart = "<span style='background-color:" + $sales_index_text_color + ";padding:1px 5px'>" + Math.ceil($percent)/100 + "</span>";
                            <?php
                                }
                            ?>
                            <?php
                                if(max(page::filterLevel(3, $levelArray)) < 33 || max(page::filterLevel(3, $levelArray)) == 36) {
                            ?>
                                    costage = "<br/><span style='color: "+price_color+"'>" + number_format($total_price, '0', ',', '.') + " AMD " + "</span>">;
                            <?php
                                } else {
                            ?>
                                costage = "<br/><span style='color: "+price_color+"'>" + number_format($total_price, '0', ',', '.') + "</span> ";
                                <?php
                                    if($show_pnetcost_procent_in_list == 1){
                                    ?>
                                        costage+= "/" + number_format(d.pNetcost, '0', ',', '.') + " / " + $cucanishDivPart + " ";
                                <?php
                                    }
                                ?>
                            <?php } ?>

                        }
                        <?php
                        }
                        // end of admin part
                        //for others start
                        else{
                        ?>
                        d.price = number_format(d.price, '0', ',', '');
                        var $total_price = order_currency.convert(d.currency, d.price);
                        //console.log(d.sell_point);
                        var sellpoints = [43,42,41,40,39,37,36,35,34,33,31,30,29,28,27,26,25,24,19,18,17,16,15,13];
                        if (d.price > 0 && (sellpoints.indexOf(d.sell_point)) && $total_price <= 30000) {

                            //buy.am 16 15%
                            //memu.am 15 25%
                            var $price_differ = 0;
                            var price_color = 'inherit';
                            if(d.payment_type == 18){
                                price_color ='red';
                            }
                            if (d.sell_point == 16) {
                                var $price_differ = ($total_price * 20 ) / 100;
                                $total_price = $total_price - $price_differ;
                            } else if (d.sell_point == 15) {
                                var $price_differ = ($total_price * 25 ) / 100;
                                $total_price = $total_price - $price_differ;
                            } else if (d.sell_point == 32) {
                                var $price_differ = ($total_price * 20 ) / 100;
                                $total_price = $total_price - $price_differ;
                            }
                            var $left_over_price = $total_price - d.pNetcost;

                            var $left_over_price = $total_price - d.pNetcost;
                            var $percent = order_currency.pfp(d.pNetcost, $left_over_price);
                            if ($percent <= 20) {
                                $sales_index_text_color = 'red';
                            } else if ($percent > 20 && $percent <= 30) {
                                $sales_index_text_color = 'Black';
                            } else if ($percent > 30 && $percent <= 50){
                                $sales_index_text_color = 'Yellow';
                            } else if ($percent > 50 && $percent <= 75) {
                                $sales_index_text_color = 'darkslategrey';
                            } else if ($percent > 75 && $percent <= 100) {
                                $sales_index_text_color = 'Magenta';
                            }  else if ($percent > 100 && $percent <= 150) {
                                $sales_index_text_color = 'Lightgreen';
                            }  else if ($percent > 150 && $percent <= 200) {
                                $sales_index_text_color = 'Blue';
                            } else if ($percent > 0 && $percent > 200) {
                                $sales_index_text_color = 'Green';
                            }
                            $cucanishDivPart = "<span style='background-color:" + $sales_index_text_color + ";padding:1px 5px'>" + Math.ceil($percent)/100 + "</span>";
                            <?php
                                if(max(page::filterLevel(3, $levelArray)) < 33 || max(page::filterLevel(3, $levelArray)) == 36) {
                            ?>
                                    costage = "<br/><span style='color: "+price_color+"'>" + number_format($total_price, '0', ',', '.') + " AMD </span>";
                            <?php
                                } else {
                            ?>
                                costage = "<br/><span style='color: "+price_color+"'>" + number_format($total_price, '0', ',', '.') + " AMD </span> / " + number_format(d.pNetcost, '0', ',', '.') + " AMD / " + $cucanishDivPart + " ";
                            <?php } ?>
                        }
                        <?php
                        }
                        //for others end
                        ?>
                        
                        <?php
                        if(!empty(array_intersect(array(89), explode(",", $get_lvl[0]))))
                        {
                        ?>
                        if (true) {//d.delivery_type == "2" || d.delivery_type == "4"
                            <?php
                            }
                            ?>
                            <?php

                            if(max(page::filterLevel(3, $levelArray)) < 33) {
                            ?>
                            
                            if(<?=(isset($userData[0]["username"]) && strtolower($userData[0]["username"]) == "ani") ? 'd.delivery_type == "10" || d.delivery_type == "7" ':'false'?>){
                                continue;
                            }
                            if (d.delivery_status == "13" || d.delivery_status == "12" || d.delivery_status == "11" || d.delivery_status == "7" || d.delivery_status == "6" || d.delivery_status == "1" || d.delivery_status == "3") {
                                <?php
                                }
                                ?>
                                if (first) {
                                    // showA = userFriendly;
                                    showA = "Active";
                                    first = false;
                                } else {
                                    showA = "";
                                    first = true;
                                }

                                var co = 0;
                                var monthNames = new Array("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");

                                var myDate = d.delivery_date.split("-");
                                if ((myDate[0] + myDate[1] + myDate[2]) == 0) {
                                    var newDate = myDate[2] + "-" + myDate[1] + "-" + myDate[0];
                                } else {
                                    var newDate = myDate[2] + "-" + monthNames[myDate[1] - 1] + "-" + myDate[0];
                                }
                                is_defect = (d.out_defect == 1) ? '<a href="products/?cmd=out&orderId=' + d.id + '&manual=true\""><img width="50" src="products/template/images/animated/animated-flower-image-0001.gif"/></a>' : '';
                                is_defect_description = (d. order_defect == 1) ? '<img width="25" src="<?=$rootF?>/template/images/red_r.png"/>' : '';
                                is_important = (d.important == 1) ? '<img width="25" src="<?=$rootF?>/template/icons/important/important.gif"/>' : '';
                                <?php
                                if(max(page::filterLevel(3, $levelArray)) < 33)
                                    {
                                ?>
                                    if (d.delivery_region == 4){
                                        console.log('barcelona');
                                        htmlData += "<tr style='display:none' data-id='"+d.id+"' class='" + showA;
                                    }
                                    else{
                                        htmlData += "<tr data-id='"+d.id+"' class='" + showA;
                                    }
                                <?php
                                }
                                else{
                                    ?>
                                        htmlData += "<tr data-id='"+d.id+"' class='" + showA;
                                    <?php
                                }
                                ?>
                                htmlData += "<tr data-id='"+d.id+"' class='" + showA;
                                if(d.delivery_status == 2 || d.delivery_status == 3 || d.order_source == 1){
                                    htmlData += " robot ";
                                }
                                htmlData += "' data-status='"+d.delivery_status+"'>";
                                // var check_defected = "<br><button onclick=\"CheckAccounting(" + d.id + ")\">"+check_text+"</button>";
                                var check_defected = "<br>";
                                let print_icon_border = 'inherit';
                                if(d.printed != 0){
                                    print_icon_border = 'red !important';
                                }
                                <?php

                                if(max(page::filterLevel(3, $levelArray)) >= 33)
                                {
                                ?>
                                //#1
                                htmlData += "<td class=\"hide-1 hidden-print\" style=\"min-width:50px;\" nowrap><a href=\"order.php?orderId=" + d.id + "\" target=\"_blank\">N-" + d.id + "</a><br/>";
                                <?php
                                    if(!in_array($userData[0]['id'], $travel_operators)){
                                ?>
                                    htmlData += "<button class=\"hidden-print printBtnCheckErt \" data-order-id='" + d.id + "'' data-href=\"print.php?orderId=" + d.id + "\" target=\"_blank\"><img src=\"<?=$rootF?>/template/icons/print.png\" style='border: 1px solid "+print_icon_border+"'></button>&nbsp;<?=((empty(array_intersect(array(99), explode(",", $get_lvl[0]))))) ? '<a class=\"hidden-print\" target=\"_blank\" href=\"products/?cmd=out&orderId="+d.id+"&manual=true\">'.((defined("OUT")) ? OUT : "OUT").'</a>' : ''?><br><input class=\"hidden-print\" id=\"mailToSend\" type=\"checkbox\" value=\"" + d.id + "\" >";
                                <?php 
                                    }
                                ?>
                                htmlData +="</td>";
                                // htmlData += "<td class=\"hide-1 hidden-print\" style=\"min-width:50px;\" nowrap><a href=\"order.php?orderId=" + d.id + "\" target=\"_blank\">N-" + d.id + "</a><br/><a class=\"hidden-print\" href=\"products/?cmd=out&orderId="+d.id+"&manual=true\" target=\"_blank\"><img src=\"<?=$rootF?>/template/icons/print.png\" style='border: 1px solid "+print_icon_border+"'></a>&nbsp;<?=((empty(array_intersect(array(99), explode(",", $get_lvl[0]))))) ? '<a class=\"hidden-print\" target=\"_blank\" href=\"products/?cmd=out&orderId="+d.id+"&manual=true\">'.((defined("OUT")) ? OUT : "OUT").'</a>' : ''?><br><button class=\"hidden-print\" onclick=\"CheckAccounting(" + d.id + ")\">"+check_text+"</button><br><input class=\"hidden-print\" id=\"mailToSend\" type=\"checkbox\" value=\"" + d.id + "\" disabled></td>";
                                htmlData += "<td class='show-print'>N-" + d.id + "</td>";
                                <?php
                                }else{
                                ?>
                                //#1

                                htmlData += "<td class=\"hide-1 hidden-print\" style=\"min-width:50px;\" nowrap>N-" + d.id + "<br/>";
                                <?php
                                    if(!in_array($userData[0]['id'], $travel_operators)){
                                ?>
                                    htmlData += "<a class=\"hidden-print\" href=\"print.php?orderId=" + d.id + "\" ><img src=\"<?=$rootF?>/template/icons/print.png\"  style='border: 1px solid "+print_icon_border+"'></a>&nbsp;<a class=\"hidden-print\" href=\"products/?cmd=out&orderId=" + d.id + "&manual=true\">"+out_text+"</a>" + check_defected;
                                <?php } ?>
                                htmlData +="</td>";
                                // htmlData += "<td class=\"hide-1 hidden-print\" style=\"min-width:50px;\" nowrap>N-" + d.id + "<br/><a class=\"hidden-print\" href=\"products/?cmd=out&orderId=" + d.id + "&manual=true\" ><img src=\"<?=$rootF?>/template/icons/print.png\"  style='border: 1px solid "+print_icon_border+"'></a>&nbsp;<a class=\"hidden-print\" href=\"products/?cmd=out&orderId=" + d.id + "&manual=true\">"+out_text+"</a>" + check_defected + "</td>";
                                htmlData += "<td class='show-print'>N-" + d.id + "</td>";
                                <?php
                                }
                                if(max(page::filterLevel(3, $levelArray)) >= 33)
                                {
                                    //for operators  and admin part
                                ?>
                                //#2
                                htmlData += "<td class=\"hide-2 hidden-print\"><img src=\"<?=$rootF?>/template/icons/bonus/" + d.bonus_type + ".png\"><br/><img src=\"<?=$rootF?>/template/icons/region/" + d.delivery_region + ".png\"></td>";
                                //#3
                                if (!timeType[d.delivery_time]) {
                                    timeType[d.delivery_time] = "";
                                }

                                var driverN = (driver_name[d.deliverer]) ? driver_name[d.deliverer] : '';
                                var deliveryReasonN = (delivery_reason[d.delivery_reason]) ? delivery_reason[d.delivery_reason] : '';
                                var deliveryStatus = (statusTitle[d.delivery_status]) ? statusTitle[d.delivery_status] : '';
                                var PrimaryLanguage = (primary_language[d.delivery_language_primary]) ? primary_language[d.delivery_language_primary] : '';
                                var WhoReceived = (who_received[d.who_received]) ? who_received[d.who_received] : '';
                                var delvrr = (d.deliverer > 0) ? "<img id=\"drvnameimg_" + d.id + "\" " 
                                        + "width=\"25px\" style=\"position:absolute;left:0;top:0;z-index:1;\" src=\"<?=$rootF?>/template/icons/drivers/" 
                                        + d.deliverer + ".png\" title=\"" + driverN + "\">" : '';
                                var carN = (driver_car[d.delivery_type]) ? " title=\"" + driver_car[d.delivery_type] + "\"" : '';
                               
                                var timeToDiff = '';
                                var car_color = 'none';
                                // console.log(d);
                                if(d.delivery_status == 3){
                                    
                                    if(d.travel_time_end != ''){
                                            timeToDiff = d.delivery_date + " " + d.travel_time_end;
                                    } else if (d.delivery_time_manual != ''){
                                        timeToDiff = d.delivery_date + " " + d.delivery_time_manual;
                                    }
                                    else if (d.delivery_time_range != null){
                                        var d_price_end = d.delivery_time_range.split('-')[1];
                                        d_price_end = d_price_end.replace('.',':');
                                        timeToDiff = d.delivery_date + " " + d_price_end;
                                    }
                                    timeToDiff += ":00";
                                    if(d.delivered_at != null){
                                        timeToDiff = timeToDiff.replace("00:00:00", "23:59:00");
                                        var timeDiff = (new Date(timeToDiff).getTime() - new Date(d.delivered_at).getTime());
                                        var minDiff =   Math.floor((timeDiff % 86400000) / 3600000) * 60 + Math.round(((timeDiff % 86400000) % 3600000) / 60000);
                                        var car_text = '';
                                        if(minDiff > 30) {
                                            car_color = "green";
                                        } else if (minDiff <= 30 && minDiff >= 0){
                                            car_color = "yellow";
                                        } else if(minDiff < 0) {
                                            car_text="<p class='' style='color:black!important;background:yellow;font-size:11px;margin:0 0 0px'>Ուշացած...</p>";
                                            car_color = "red";
                                        }
                                    }
                                }
                                htmlData += "<td  class=\"hide-3\" style='min-width: 120px;'>";
                                <?php
                                    if(!in_array($userData[0]['id'], $travel_operators)){
                                ?>
                                        if(d.deliverer == 7 ){
                                            htmlData += '<p class="wrongData">Հարկավոր է նշել առաքում կատարողին</p>';
                                        }
                                <?php }?>
                                htmlData += "<strong>" 
                                        + newDate + "</strong><br/>" 
                                        + timeType[d.delivery_time];
                                        if(d.delivery_time_manual != '' || d.travel_time_end != ''){
                                            htmlData += "("
                                            if(d.delivery_time_manual != ''){
                                                htmlData += d.delivery_time_manual;
                                            }
                                            if(d.delivery_time_manual != '' && d.travel_time_end != ''){
                                                htmlData += " - ";
                                            }
                                            if(d.travel_time_end != ''){
                                                htmlData += d.travel_time_end; 
                                            }
                                            htmlData += ")";
                                        }
                                        htmlData += "<br/><div class=\"hidden-print\" style=\"position:relative;height:50px;\">" + delvrr + "<img id=\"carImage_" + d.id + "\" class='displayAdditionalPrice' data-order-id='" + d.id + "' data-delivery-price='" + d.delivery_price + "' width=\"70px\" style=\"position:absolute;top:0; border: 1px solid " + car_color + " \" title='" + d.delivered_at + "' src=\"<?=$rootF?>/template/icons/deliver/";
                                        // if([12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42].includes(d.deliverer)){
                                            // htmlData += d.deliverer;
                                        // } else {
                                            htmlData += d.delivery_type;
                                        // }
                                        htmlData += ".png\" " + carN
                                        + "><img width=\"50px\" style=\"position:absolute;top:0;right:0;\" src=\"<?=$rootF?>/template/icons/ontime/" 
                                        + d.ontime + ".png\"></div>";
                                //#4
                                if(d.delivered_at != null && d.delivery_status == 3){
                                    htmlData += "<p class='created_time'>" + replaceDatetimeFormat(d.delivered_at) + "</p>" + car_text;
                                }
                                if(d.operator_name != null){
                                    htmlData += "<p style='color:red; margin-bottom: 0px;'>";
                                    if(d.operator_name == 'Robot'){
                                        htmlData += "#" + d.operator_name;
                                    }
                                    else{
                                        if(operators_info[d.operator_name]){
                                            var operator_first_name = operators_info[d.operator_name].full_name_am.split(' ');
                                            htmlData +=  "#" + operator_first_name[0];
                                        }
                                        else{
                                            htmlData += "#" + d.operator_name;
                                        }
                                    }
                                    htmlData += "</p>"
                                }
                                if(d.confirmed == 1){
                                    htmlData += "<img style='height:25px' src=\"<?=$rootF?>/template/icons/confirmed/" + d.confirmed + ".png\" '><div class='additionalPriceDisplay_" + d.id + "'></div></td>";
                                }

                                 // gortsoghutyun to come here
                                 <?php 
                                if(max(page::filterLevel(8, $levelArray)) >= 89 || max(page::filterLevel(9, $levelArray)) >= 99)
                                {
                                ?>
                                    
                                    
                                    <?php
                                        if($list_action_fields == 1){
                                            ?>
                                            // Gortsoghutyun tab
                                            htmlData += "<td class='hidden-print'>";
                                            
                                            <?php
                                            
                                            $levelsOfUser =$userData[0]["user_level"];
                                            
                                            if (strpos($levelsOfUser, '89') !== false) {
                                            
                                            ?>  
                                                
                                                
                                            var showSelect = "SELECT";  
                                            if (d.deliverer > 0 ) {
                                                showSelect =  driverN;
                                            } 
                                            htmlData += '<select style="width:100px" id="driver_'+d.id+ '" ><option value="' + d.deliverer + '">'+ showSelect +'</option> <?php echo $selectionOption ?></select><br>';
                                            htmlData += '<hr>';
                                            htmlData += '<select id="stage_'+d.id+ '"  style="width:100px">';
                                            if (d.stage > 0) {
                                                htmlData += '<option value="'+ d.stage + '">' +  d.stage + '</option>'
                                            } 
                                        
                                            
                                            htmlData    += '<option value="0"></option>'
                                                        + '<option value="1">1</option>'
                                                        + '<option value="2">2</option>'
                                                        + '<option value="3">3</option>'
                                                        + '<option value="4">4</option>'
                                                        + '<option value="5">5</option>'
                                                        + '<option value="6">6</option>'
                                                        + '<option value="7">7</option>'
                                                        + '<option value="8">8</option>'
                                                        + '<option value="9">9</option>'
                                                        + '<option value="10">10</option>'
                                                        + '</select>';
                                            htmlData += '<hr>';
                                            if (d.step > 0) {
                                                htmlData += 'S <input  id="step_'+d.id+  '" style="width:45px" type="text" placeholder="step" value="' + d.step + '">';
                                            }else {
                                                htmlData += 'S <input  id="step_'+d.id+  '" style="width:45px" type="text" placeholder="step" >';
                                            }
                                            <?php 
                                            
                                            if (strpos($levelsOfUser, '89') !== false || strpos($levelsOfUser, '30') !== false || strpos($levelsOfUser, '31') !== false) {
                                            ?>
                                            
                                            
                                            if (d.quantity  > 0 ) {
                                                htmlData += '<input id="quantity_'+d.id+  '" style="width:35px" type="text" placeholder="quantity" value="' +  d.quantity + '">';
                                            } else {
                                                htmlData += '<input id="quantity_'+d.id+  '" style="width:35px" type="text" placeholder="quantity" >';
                                            }
                                            htmlData += '<hr>';
                                            htmlData += '<button style="width:100px" id="clickbutton_' + d.id + '" onclick="onclickSaveButton('+d.id+')" >SAVE</button>';
                                            
                                            
                                            <?php
                                            }
                                            
                                    ?>
                                    /// >>>>>>>>>>>>>>>>>>>>..
                                    htmlData += "</td>";
                                            <?php
                                        }
                                            ?>
                                     <?php
                                        }
                                    ?>
                                <?php 
                                }
                                ?>
                                var showSelectReason = "SELECT";  
                                if (d.deliverer > 0 ) {
                                    showSelectReason =  deliveryReasonN;
                                } 
                                var showSelectPrimaryLanguage = "SELECT";  
                                if (d.deliverer > 0 ) {
                                    showSelectPrimaryLanguage =  PrimaryLanguage;
                                } 
                                var showSelectWhoReceived = "SELECT";  
                                if (d.who_received.length > 0 ) {
                                    showSelectWhoReceived =  WhoReceived;
                                } 
                                //END  Gortsoghutyun tab
                                
                                htmlData += "<td class=\"hide-4 hidden-print\"><img class='statusImage show_log_of_order' data-order-id='" + d.id +"' src=\"<?=$rootF?>/template/icons/status/" + d.delivery_status + ".png\" title=\"" + statusTitle[d.delivery_status] + "\">" + is_defect + is_defect_description ;
                                if(d.important == 1){
                                    htmlData += "<img src=\"<?=$rootF?>/template/icons/important/important.gif\">";
                                }
                                htmlData += "</td>";
                                 
                                // apranq to be moved here
                                // var zoomimage = (d.image_exist > 0) ? "<br><button style=\"background: none;border: 0;\" onclick=\"zoom_img(" + d.id + ")\" class=\"hidden-print\"><img src=\"<?=$rootF?>/template/icons/zoom.png\"/></button>" : '';
                                actiondata = "";                                
                                htmlData += "<td class=\"hide-8\" style=\"min-width:400px;\">";
                                htmlData += "<div class='related_images'></div>";
                                htmlData += "<div class='product_images'></div>";
                                htmlData += d.product + actiondata;
                                // htmlData += zoomimage;
                                // htmlData += "<br>";
                                htmlData += "<div class='out_images'></div>";
                                <?php 
                                    if(max(page::filterLevel(3, $levelArray)) > 33){
                                ?>
                                    co = d.notes;
                                    if(co > 0 ){
                                        htmlData += "<div class=\"article hidden-print\" style=\"display: inline-block;\"><button data-order-id='" + d.id + "' class=\"readMoreButtonAjaxNotes\">Նշում " + co;
                                        if(d.bonus_info != undefined && d.bonus_info.length > 0){
                                            htmlData += ' (' + d.bonus_info.length + ')';
                                        }
                                        htmlData += "</button><div class='display-none forAjaxNotesValue_" + d.id + "' style=\"max-width: 100px\">" ;
                                        var bonus_malus_text = '<br><span></span>';
                                        if(d.bonus_type == 1 || d.bonus_type == 2){
                                            bonus_malus_text+= " B-M: ";
                                        }
                                        if(d.first_connect){
                                            bonus_malus_text += firstConnectMethods[d.first_connect];
                                        }
                                        htmlData +=bonus_malus_text;
                                        htmlData += "</div></div>";
                                    }
                                    //#13
                                    co = d.greetings_card;
                                    if(co > 0){
                                        htmlData += "<div class=\"article hidden-print\" style=\"display: inline-block;\"><button data-order-id='" + d.id + "' class=\"readMoreButtonAjaxGreetingCard\">Բացիկ " + co + "</button><div class='display-none forAjaxGreetingCardValue_" + d.id + "' style=\"max-width: 100px\"> ";
                                        htmlData += "<br><span></span>";
                                        htmlData += "<button type='button' onclick='printBacik("+d.id+")'>Տպել բացիկը</button>";
                                        htmlData += "</div></div>";
                                    }

                                    //#14
                                    co = d.notes_for_florist;
                                    if(co > 0){
                                        htmlData += "<div class=\"article hidden-print\" style=\"display: inline-block;\"><button data-order-id='" + d.id + "' class=\"readMoreButtonAjaxFloristNote\">Ֆլորիստին " + co + "</button><div class='display-none forAjaxFloristNoteValue_" + d.id + "' style=\"max-width: 100px\"><span></span></div></div>";
                                    }

                                <?php
                                }
                                ?>
                                htmlData += "<br>";
                                if(d.delivery_reason != undefined){
                                    if(d.delivery_reason == 1){
                                        htmlData += "<img src='./ico/love.png' title='Սիրո առիթով' class='reason_icon'>"
                                    } else if(d.delivery_reason == 9){
                                        htmlData += "<img src='./ico/sqo.png' title='Սքո առիթով' class='reason_icon'>"
                                    } else if([2, 4, 10, 12, 15, ].indexOf(d.delivery_reason) > -1){
                                        htmlData += "<img src='./ico/birthday.png' title='Ծննդյան առիթով' class='reason_icon'>"
                                    }
                                }
                                htmlData += partner_icon;
                                if(d.flourist != null){
                                    var flourist_delviery_status_display = Array(12,3,6,7);

                                    if(flourist_delviery_status_display.includes(d.delivery_status)){
                                        htmlData += "<span style='color:red' class='flourist_name'><img title='" + d.flourist + "' src='../../template/images/florists/"  + d.flourist_id +  ".gif'></span>";
                                    }
                                    else{
                                        htmlData += "<span style='color:red' class='flourist_name'>" + d.flourist + "</span>";
                                    }

                                } else {
                                    htmlData += "<span style='color:red' class='flourist_name'></span>";
                                }
                                // htmlData += "<button type='button' class='form-control showChoosenRelated' data-clicked=0 data-status='"+d.delivery_status+"' data-id='"+d.id+"'>Նկարներով</button>";
                                if(d.delivery_status == 2 || d.delivery_status == 5){
                                    htmlData +='<img class="anavartShowRowsIcon" style="width:20px;margin-left:10px;margin-right:10px" src="../../template/icons/status/2.png" data-order-id="' + d.id +'" title="Անավարտ">'
                                }
                                htmlData += "<img src='../../template/icons/zoom.png' class='showChoosenRelated' data-clicked=0 data-status='"+d.delivery_status+"' data-id='"+d.id+"' title='Նկարներով'>"
                                if(d.sell_point == 15 || d.sell_point == 16 || d.sell_point == 44 || d.payment_type == 11 || d.payment_type == 12){
                                    htmlData += "<img src='../../template/images/hdm.jpg' class='hdmIconRed' title='ՀԴՄ'>";
                                }
                                if(d.have_real_image == 1){
                                    htmlData += "<img class='hover_cursor_pointer' onclick=\"CheckAccounting(" + d.id + ")\" style='width:30px' src='../../template/images/camera.png'>";
                                }
                                if(($.inArray(d.delivery_status, status_xml_approve_array) !== -1 && $.inArray(d.payment_type, payment_type_array) !== -1 && d.delivery_region == 1) || d.sell_point == 44 || d.sell_point == 45 || d.sell_point == 48 ){
                                    if(d.sell_point != 15 && d.sell_point != 16){
                                        <?php
                                            if(!in_array($userData[0]['id'], $simple_florist_ids)){
                                        ?>
                                            htmlData += "<img src='../../template/images/pek.png' class='pekIconRed showHideUploadedTimeXML' data-order-id='" + d.id + "'>";
                                            htmlData += "<div><ul class='display-none textShowUploadedTime_" + d.id + "'></ul></div>";
                                            <?php
                                           }
                                        ?>
                                    }
                                }
                                htmlData += "<br>";
                                <?php
                                    if(!in_array($userData[0]['id'], $travel_operators)){
                                ?>
                                    if(d.late != null && d.late != '' && d.late.indexOf(1) > -1){
                                        htmlData += "<img src='./ico/fire.png' class='fireImg' title='Չափազանց ուշացված'>";
                                    }
                                    htmlData += "<button type='button' class='mailButton vjarelMailButton hidden-print' ";
                                    if(d.late != null && d.late != '' && d.late.indexOf(1) > -1){
                                        htmlData += ' style="border: 2px solid red;" ';
                                    }
                                    htmlData += " onclick='openMail("+d.id+", 1)'>Վճարել</button>";
                                    // if(d.sender_email != ''){
                                        // Added By Hrach
                                        var color;
                                        if(d.out_operator_defect == 1 && d.out_defect == 1){
                                            color = "green";
                                        }
                                        else if( d.out_operator_defect == 1 && d.out_defect == 0 ){
                                            color = "yellow"
                                        }
                                        htmlData += "<button type='button' class='mailButton hastatelMailButton hidden-print' onclick='openMail("+d.id+", 2)'>Հաստատել</button>";
                                        if($(".display_image_email_button").val() == 1){
                                            htmlData += "<button type='button' class='mailButton nkarMailButton hidden-print' onclick='openMail("+d.id+", 8)'>Ուղարկել Նկար</button>";
                                        }
                                        if($(".OffOnOfStugelButton").val() == 1){
                                            htmlData += "<button class=\"hidden-print stugelMailButton mailButton\" style='border:2px solid " + color +"' onclick=\"CheckAccounting(" + d.id + ")\">"+check_text+"</button><br>";
                                        }
                                        color = "normal";
                                        if($(".OffOnOfPatrastButton").val() == 1){
                                            htmlData += "<button type='button' class='mailButton patrastMailButton hidden-print' onclick='openMail("+d.id+", 3)'>Պատրաստ</button>";
                                        }
                                        if($(".OffOnOfAraqumButton").val() == 1){
                                            htmlData += "<button type='button' class='mailButton araqumMailButton hidden-print' onclick='openMail("+d.id+", 4)'>Առաքում</button>";
                                        }
                                        if(d.late != null && d.late != '' && d.late.indexOf(5) > -1){
                                            htmlData += "<img src='./ico/fire.png' title='Չափազանց ուշացված' class='fireImg'>";
                                        }
                                        if($(".OffOnOfAraqvecButton").val() == 1){
                                            htmlData += "<button type='button' class='mailButton araqvecMailButton hidden-print' ";
                                            if(d.late != null && d.late != '' && d.late.indexOf(5) > -1){
                                                htmlData += ' style="border: 2px solid red;" ';
                                            }
                                            htmlData += " onclick='openMail("+d.id+", 5)'>Առաքվեց</button>";
                                        }
                                    // }
                                    if(d.sender_email != null && d.sender_email != ''){
                                        htmlData += "<img src='./ico/email-send.png' class='emailIco' title='Email կա'>";
                                    }
                                    if(d.sender_phone != null && d.sender_phone != ''){
                                        htmlData += "<img src='./ico/mobile-send.png' class='emailIco' title='Հեռախոսահամար կա'>";
                                    }
                                        htmlData += '<img onclick="CheckAccounting(' + d.id + ')" class="external_links hover_cursor_pointer" data-product-id="410" src="../../images/external_link.png" style="height:15px;margin-left:5px;height:15px">';
                                <?php
                                    }
                                    $delivery_reason_show = GetOffOnAction('orders_page_delivery_reason');
                                    $delivery_status_show = GetOffOnAction('orders_page_order_status');
                                    $delivery_receiver_show = GetOffOnAction('orders_page_receiver');
                                    $delivery_primary_language_show = GetOffOnAction('orders_page_delivery_primary_language');
                                ?>
                                <?php
                                    if($delivery_reason_show == 1){
                                        ?>
                                            htmlData += '<br><br><div style="float:left;text-align:center"><select style="width:175px;margin-top:20px" id="delivery_reason_'+d.id+ '" ><option value="' + d.delivery_reason + '">'+ showSelectReason +'</option> <?php echo $selectionOptionReason ?></select><br><button style="margin-top:10px" id="ReasonSaveButton_' + d.id + '" onclick="onclickSaveButtonDeliverReason(' + d.id + ')" style="width:100px">Save</button></div>';
                                        <?php
                                    }
                                    if($delivery_primary_language_show == 1){
                                        ?>
                                            htmlData += '<div style="float:right;text-align:center"><select style="width:175px;margin-top:20px" id="delivery_primary_language_'+d.id+ '" ><option value="' + d.delivery_language_primary + '">'+ showSelectPrimaryLanguage +'</option> <?php echo $selectionOptionPrimaryLanguage ?></select><br><button id="PrimaryLanguageSaveButton_' + d.id + '" style="margin-top:10px" onclick="onclickSaveButtonPrimaryLanguage(' + d.id + ')" style="width:100px">Save</button></div>';
                                        <?php
                                    }
                                    if($delivery_status_show == 1){
                                        ?>
                                            htmlData +='<div style="float:left;text-align:center"><select style="width:175px;margin-top:20px" id="delivery_status_'+d.id+ '" >';
                                            if(d.delivery_status == 1){
                                                htmlData += '<option selected value="1">Հաստատված</option>';
                                            }
                                            else{
                                                htmlData += '<option value="1">Հաստատված</option>';
                                            }
                                            if(d.delivery_status == 3){
                                                htmlData += '<option selected value="3">Առաքված</option>';
                                            }
                                            else{
                                                htmlData += '<option value="3">Առաքված</option>';
                                            }
                                            if(d.delivery_status == 6){
                                                htmlData += '<option selected value="6">Ճանապարհին</option>';
                                            }
                                            else{
                                                htmlData += '<option value="6">Ճանապարհին</option>';
                                            }
                                            if(d.delivery_status == 7){
                                                htmlData += '<option selected value="7">Վերադարձրած</option>';
                                            }
                                            else{
                                                htmlData += '<option value="7">Վերադարձրած</option>';
                                            }
                                            if(d.delivery_status == 12){
                                                htmlData += '<option selected value="12">Պատրաստ</option>';
                                            }
                                            else{
                                                htmlData += '<option value="12">Պատրաստ</option>';
                                            }
                                            htmlData += '</select><br><button id="StatusSaveButton_' + d.id + '" style="margin-top:10px" onclick="onclickSaveButtonStatus(' + d.id + ')" style="width:100px">Save</button></div>';
                                        <?php
                                    }
                                    if($delivery_receiver_show == 1){
                                        ?>
                                            htmlData += '<div style="float:right;text-align:center"><select style="width:175px;margin-top:20px" id="who_received_'+d.id+ '" ><option value="' + d.who_received + '">'+ showSelectWhoReceived +'</option> <?php echo $selectionOptionWhoReceived ?></select><br><button id="WhoReceivedSaveButton_' + d.id + '" style="margin-top:10px" onclick="onclickSaveButtonWhoReceived(' + d.id + ')" style="width:100px">Save</button></div>';
                                        <?php
                                    }
                                ?>
                                if(d.delivery_status == 2 || d.delivery_status == 5){
                                    htmlData +='<div class="showAnavartRows anavartDivFor_' + d.id + '"></div>'
                                }
                                htmlData += "</td>";

                                    htmlData += "<td class=\"hide-10\">";
                                    <?php
                                        if(!in_array($userData[0]['id'], $travel_operators)){
                                    ?>
                                        if(subregionType[d.receiver_subregion] == undefined || d.receiver_subregion == '------' || d.receiver_subregion == 0){
                                            htmlData += "<span class='wrongData'>";
                                        }
                                    <?php } ?>
                                    htmlData += subregionType[d.receiver_subregion]; // wrongData if empty or 0 value
                                    // new version for states of armenia : added by Hrach 04 03 2020
                                     var armStates = Array('kotayq','lori','tavush','syunik','vayoc_dzor','armavir','shirak','ararat','aragatsotn','gexarquniq');
                                    if($.inArray(d.receiver_subregion,armStates) !== -1){
                                        htmlData += " մարզ";
                                    }
                                    else{
                                        // old version only
                                        htmlData += " <?=(defined('STATE')) ? STATE : 'STATE';?> /-/ ";
                                    }
                                    <?php
                                        if(!in_array($userData[0]['id'], $travel_operators)){
                                    ?>
                                        if(subregionType[d.receiver_subregion] == undefined || d.receiver_subregion == '------' || d.receiver_subregion == 0){
                                            htmlData += "</span>";
                                        }
                                    <?php } ?>
                                    htmlData += "<hr>";
                                    if(d.organisation != null && d.organisation != 0){
                                        htmlData += d.organisation_name + ' - ';
                                    }
                                    <?php
                                        if(!in_array($userData[0]['id'], $travel_operators)){
                                    ?>
                                        if((streetType[d.receiver_street] == null || streetType[d.receiver_street] == '') && (d.sell_point != 15 && d.sell_point != 16)){
                                            htmlData += "<span class='wrongData'>Ուղղել թերի լրացված հասցեն</span>";
                                        } else {
                                            if(d.receiver_street != '' ){
                                                htmlData += streetType[d.receiver_street] + ', ';
                                            }
                                        }
                                    <?php } else { ?>
                                        htmlData += streetType[d.receiver_street] + ', ';
                                    <?php } ?>
                                    htmlData += d.receiver_address;
                                    if(d.receiver_entrance != '' && d.receiver_entrance != null){
                                        htmlData += "<br> <?=(defined('RECEIVER_ENTRANCE')) ? RECEIVER_ENTRANCE : '';?>  " + d.receiver_entrance + " <br>";
                                    }
                                    if(d.receiver_floor != '' && d.receiver_floor != null){
                                        htmlData += " <?=(defined('RECEIVER_FLOOR')) ? RECEIVER_FLOOR : '';?>  " + d.receiver_floor  + " <br>";
                                    }
                                    if(d.receiver_door_code != '' && d.receiver_door_code != null){
                                        htmlData += " <?=(defined('RECEIVER_DOOR_CODE')) ? RECEIVER_DOOR_CODE : '';?>  " + d.receiver_door_code + " ";
                                    }
                                    if(d.payment_type != undefined && d.payment_type == 12){
                                        htmlData += " <span class='check_reminder'> - (Դուրս գալ կտրոնով)</span>";
                                    }
                                    if(d.right_address != null && d.right_address != ''){
                                        htmlData += "<br>";
                                        htmlData += "<span class='rightAddress'>Առաքիչի ուղղած հասցե:<br><img height='25px' src='../../template/icons/important/important.gif'><br> "+ d.right_address +"</span>";
                                    }
                                    htmlData += "</td>";
                                    <?php
                                if(max(page::filterLevel(3, $levelArray)) > 33){                                
                                ?>
                                    var whoreceived_show = (whoreceived[d.who_received]) ? "<br>#" + whoreceived[d.who_received] + "#" : '';

                                    htmlData += "<td class=\"hide-9\">";
                                    <?php
                                        if(!in_array($userData[0]['id'], $travel_operators)){
                                    ?>
                                        if((d.receiver_name == null || d.receiver_name == '') && (d.sell_point != 15 && d.sell_point != 16) && partner_icon ==''){
                                            htmlData += '<span class="wrongData">Ստացողի անունը գրանցված չէ</span>';
                                        } else {
                                            htmlData += d.receiver_name;
                                        }
                                    <?php } else { ?>
                                        htmlData += d.receiver_name;
                                    <?php }?>
                                    htmlData += "<span style='color: red;'>" + whoreceived_show + "</span>";
                                    <?php
                                        if(!in_array($userData[0]['id'], $travel_operators)){
                                    ?>
                                        if(d.receiver_mood > 0){
                                            htmlData += '<img src="./ico/mood_'+d.receiver_mood+'.png" class="receiverMood" alt="Receiver Mood">';
                                        }
                                        <?php
                                            if($list_receiver_fields == 1){
                                                ?>
                                                    htmlData += '<br><br><select name="new_flourist" required class="new_flourist hidden-print" style="min-width: 105px; max-width: 115px; "><option value="">Պատասխանատու</option>';
                                                    <?php foreach ($flourists as $flourist){ ?>
                                                            htmlData += "<option";
                                                            if(d.flourist_id != null && d.flourist_id == <?= $flourist['id'] ?>) {
                                                                htmlData +=  " selected='selected'";
                                                            }
                                                            htmlData += " value='<?= $flourist['id'] ?>'>"+<?= (defined($flourist['username'])) ?  "'".constant($flourist['username'])."'" : "'".$flourist['username']."'"; ?>+"</option>";
                                                    <?php } ?>
                                                    htmlData += '</select><br>';
                                                    htmlData += "<input type='number' placeholder='այսօր' class='flourist_change_day hidden-print' style='width: 55px;' required>";
                                                    htmlData += "<button type='button' class='changeFlourist hidden-print' data-order='" + d.id + "' >OK</button>";
                                                <?php
                                            }
                                        ?>
                                    <?php
                                        }
                                    ?>
                                    htmlData += "</td>";
                                <?php
                                }
                                ?>

                                var mycDate = d.created_date.split("-");
                                var newcDate = mycDate[2] + "-" + monthNames[mycDate[1] - 1] + "-" + mycDate[0];
                                htmlData += "<td class=\"hide-5 hidden-print\" nowrap>" + newcDate+ "<br>" + "<span class='created_time'>"+ d.created_time +"</span><br/>";
                                if(d.operator == 'Robot'){
                                    htmlData += d.operator;
                                }
                                else{
                                    var operator_first_name = operators_info[d.operator]['full_name_am'].split(' ');
                                    htmlData +=  operator_first_name[0];
                                }
                                if(d.bonus_type == 1){
                                    let act_price = parseFloat(number_format($total_price, '0', ',', '.') * 1000);
                                    if(act_price >= 12000 && act_price <= 59999){
                                        htmlData += "<img src='./ico/1-bonus.jpg' class='bonus-image'>";
                                    } else if(act_price >= 60000 && act_price <= 139999){
                                        htmlData += "<img src='./ico/2-and-3-bonuses.jpg' class='bonus-image'>";
                                    } else if(act_price >= 140000 && act_price <= 199999){
                                        htmlData += "<img src='./ico/2-and-3-bonuses.jpg' class='bonus-image'>";
                                    } else if(act_price >= 200000 && act_price <= 319999){
                                        htmlData += "<img src='./ico/4-or-more-bonuses.jpg' class='bonus-image'>";
                                    } else if(act_price >= 320000){
                                        htmlData += "<img src='./ico/4-or-more-bonuses.jpg' class='bonus-image'>";
                                    }
                                }
                                htmlData += "</td>";
                                //#6
                                var sType = "";
                                if (d.order_source != "0") {
                                    sType = sourceType[d.order_source];
                                } else {
                                    sType = "";
                                }
                                htmlData += "<td class=\"hide-6 hidden-print\" style=\"min-width:140px;word-break: break-all;\">" + sType + "<hr/>" + d.order_source_optional + "</td>";

                                //#7
                                var pType = "";
                                if (d.payment_type != "0") {
                                    pType = payType[d.payment_type];
                                } else {
                                    pType = "";
                                }
                                <?php
                                if(empty(array_intersect(array(89), explode(",", $get_lvl[0])))){
                                ?>

                                htmlData += "<td class=\"hide-7 hidden-print\"><div class=\"prices_list\"><img src=\"<?=$rootF?>/template/icons/currency/" + d.currency + ".png\" width=\"20px\">";
                                if(!d.confirmed){
                                    htmlData += "<span class='prices_list_price'>" + number_format(d.price, '2', ',', '.') + "</span>";
                                } else {
                                    htmlData += number_format(d.price, '2', ',', '.');
                                }
                                htmlData += costage + "<hr style=\"height:5px;\" title=\""+number_format(sum_overall.total, '0', ',', '.')+"/"+number_format(sum_overall.spend, '0', ',', '.')+"/"+number_format(sum_overall.percent, '0', ',', '.')+"\"/>" + pType + "<hr/>" + d.payment_optional + "</div></td>";
                                <?php
                                }
                                
                                
                                } 
                                //end for operators and admin part
                                else{
                                    //start for florists and araqich

                                    
                                    
                                    
                                ?>
                                if (!timeType[d.delivery_time]) {
                                    timeType[d.delivery_time] = "";
                                }

                                var driverN = (driver_name[d.deliverer]) ? driver_name[d.deliverer] : '';
                                var delvrr = (d.deliverer > 0) ? "<img width=\"25px\" style=\"position:absolute;left:0;top:0;z-index:1;\" src=\"<?=$rootF?>/template/icons/drivers/" + d.deliverer + ".png\" title=\"" + driverN + "\">" : '';
                                var carN = (driver_car[d.delivery_type]) ? " title=\"" + driver_car[d.delivery_type] + "\"" : '';
                                var timeToDiff = '';
                                var car_color = 'none';
                                if(d.delivery_status == 3){
                                    if(d.travel_time_end != ''){
                                            timeToDiff = d.delivery_date + " " + d.travel_time_end;
                                    } else if (d.delivery_time_manual != ''){
                                        timeToDiff = d.delivery_date + " " + d.delivery_time_manual;
                                    }
                                    else if (d.delivery_time_range != null){
                                        timeToDiff = d.delivery_date + " " + d.delivery_time_range.split('-')[1];
                                    }
                                    timeToDiff += ":00";
                                    if(d.delivered_at != null){
                                        var timeDiff = (new Date(timeToDiff).getTime() - new Date(d.delivered_at).getTime());
                                        var minDiff =   Math.floor((timeDiff % 86400000) / 3600000) * 60 + Math.round(((timeDiff % 86400000) % 3600000) / 60000);
                                        if(minDiff > 30) {
                                            car_color = "green";
                                        } else if (minDiff <= 30 && minDiff >= 0){
                                            car_color = "yellow";
                                        } else if(minDiff < 0) {
                                            car_color = "red";
                                        }
                                    }
                                }
                                htmlData += "<td class=\"hide-2 hidden-print\" nowrap>";
                                <?php
                                    if(!in_array($userData[0]['id'], $travel_operators)){
                                ?>
                                        if(d.deliverer == 7){
                                            htmlData += '<p class="wrongData">Հարկավոր է նշել առաքում կատարողին!</p>';
                                        }
                                <?php }?>
                                htmlData += newDate + "<br/>" + timeType[d.delivery_time];
                                if(d.delivery_time_manual != '' || d.travel_time_end != ''){
                                    htmlData += "("
                                    if(d.delivery_time_manual != ''){
                                        htmlData += d.delivery_time_manual;
                                    }
                                    if(d.delivery_time_manual != '' && d.travel_time_end != ''){
                                        htmlData += " - ";
                                    }
                                    if(d.travel_time_end != ''){
                                        htmlData += d.travel_time_end; 
                                    }
                                    htmlData += ")";
                                }
                                
                                htmlData += "<div style=\"position:relative;\"><img id=\"carImage_" + d.id + "\" src=\"<?=$rootF?>/template/icons/deliver/" + d.delivery_type + ".png\" " + carN + " style='border: 1px solid "+car_color+"'>" + delvrr + "</div>";
                                if(d.delivered_at != null && d.delivery_status == 3){
                                    htmlData += "<p class='created_time'>" + d.delivered_at + "</p>";
                                }
                                htmlData += "</td>";
                                var total_price_order = '';
                                if(d.sell_point == 15 || d.sell_point == 16 || d.sell_point == 45){
                                    total_price_order = getTotalPriceOfOrder(d.price,d.currency,d.sell_point);
                                }
                                htmlData += "<td class=\"hide-3 hidden-print\" style='min-width: 120px;'><img class='statusImage' src=\"<?=$rootF?>/template/icons/status/" + d.delivery_status + ".png\" title=\"" + statusTitle[d.delivery_status] + "\">" + is_defect + is_defect_description + is_important + is_important +"<br>" + total_price_order + "</td>";


                                
                                actiondata = "";
                                <?php if(!in_array($userData[0]['id'], array(88,89,90,91,92,93,94,95,96,97,98,99,100,101,102))){ ?>
                                // var zoomimage = (d.image_exist > 0) ? "<br><button style=\"background: none;border: 0;\" onclick=\"zoom_img(" + d.id + ")\" class=\"hidden-print\"><img src=\"<?=$rootF?>/template/icons/zoom.png\"/></button>" : '';
                                <?php
                                    if(!in_array($userData[0]['id'], $simple_florist_ids)){
                                ?>

                                    if(d.payment_type == 11 || d.payment_type == 12){
                                        actiondata += "<br><img src=\"<?=$rootF?>/template/icons/currency/" + d.currency + ".png\" width=\"20px\"><span class='prices_list_price'>" + number_format(d.price, '2', ',', '.') + "</span>" + costage;
                                    }
                                    if (d.delivery_status != "3" && d.delivery_status != "6") {
                                        //console.log(d.delivery_status);
                                        actiondata += "<p style=\"margin-top:5px;\"><span style=\"display: inline-block;vertical-align: text-bottom;\"><button onclick=\"onroad(" + d.id + ")\"><img width=\"75px\" src=\"ico/onroad.png\"/></button></span>";
                                    }
                                <?php
                                    }
                                ?>
                                if (d.delivery_status != "12" && d.delivery_status != "3" && d.delivery_status != "6") {
                                     <?php
                                        if(!in_array($userData[0]['id'], $simple_florist_ids)){
                                        ?>
                                            actiondata += "<span style=\"display: inline-block;vertical-align: text-bottom;\"><button onclick=\"product_ready(" + d.id + ")\"><img width=\"32px\" src=\"<?=$rootF?>/template/icons/status/12.png\"/></button></span></p>";
                                    <?php
                                        }
                                    ?>
                                }
                                <?php } ?>
                                //apranq to be moved here too
                                

                                htmlData += "<td class=\"hide-8\" style=\"min-width:400px;\">";
                                htmlData += "<div class='related_images'></div>";
                                htmlData += "<div class='product_images'></div>";
                                htmlData += d.product + actiondata;
                                // htmlData += zoomimage;
                                htmlData += "<div class='out_images'></div>";
                                <?php
                                if(max(page::filterLevel(3, $levelArray)) < 33)
                                    {
                                ?>
                                co = d.greetings_card;
                                if(co > 0){
                                    htmlData += "<div class=\"article\" style=\" display:inline-block; \"><button data-order-id='" + d.id + "' class=\"readMoreButtonAjaxGreetingCard\">Բացիկ " + co + "</button><div class='display-none forAjaxGreetingCardValue_" + d.id + "'>" ;
                                    htmlData += "<br><span></span>";
                                    htmlData += "<button type='button' onclick='printBacik("+d.id+")'>Տպել բացիկը</button>";
                                    htmlData += "</div></div>";
                                }
                                <?php
                                    } 
                                ?>
                                var flourist_delviery_status_display = Array(12,3,6,7);
                                if(d.flourist != null){
                                    if(flourist_delviery_status_display.includes(d.delivery_status)){
                                        htmlData += "<br><span style='color:red' class='flourist_name'><img title='" + d.flourist + "' src='../../template/images/florists/" + d.flourist_id + ".gif'>  </span>";
                                    }
                                    else{
                                        htmlData += "<br><span style='color:red' class='flourist_name'>" + d.flourist.charAt(0).toUpperCase() + d.flourist.slice(1) + "  </span>";
                                    }

                                } else {
                                    htmlData += "<br><span style='color:red' class='flourist_name'></span>";
                                }
                                 htmlData += partner_icon;
                                if(d.delivery_reason != undefined){
                                    if(d.delivery_reason == 1){
                                        htmlData += "<img src='./ico/love.png' title='Սիրո առիթով' class='reason_icon'>"
                                    } else if(d.delivery_reason == 9){
                                        htmlData += "<img src='./ico/sqo.png' title='Սքո առիթով' class='reason_icon'>"
                                    } else if([2, 4, 10, 12, 15].indexOf(d.delivery_reason) > -1){
                                        htmlData += "<img src='./ico/birthday.png' title='Ծննդյան առիթով' class='reason_icon'>"
                                    }
                                }
                                
                                // htmlData += "<button type='button' class='form-control showChoosenRelated' data-clicked=0 data-status='"+d.delivery_status+"' data-id='"+d.id+"'>Նկարներով</button>";
                                htmlData += "<img src='../../template/icons/zoom.png' class='showChoosenRelated' data-clicked=0 data-status='"+d.delivery_status+"' data-id='"+d.id+"' title='Նկարներով'>";
                                if(userDataLevelNumber_array.indexOf('30') >= 0 || userDataLevelNumber_array.indexOf('40') >= 0 ){
                                    if(d.have_real_image == 1){
                                        htmlData += "<img class='hover_cursor_pointer' onclick=\"CheckAccounting(" + d.id + ")\" style='width:30px' src='../../template/images/camera.png'>";

                                    }
                                    if(($.inArray(d.delivery_status, status_xml_approve_array) !== -1 && $.inArray(d.payment_type, payment_type_array) !== -1 && d.delivery_region == 1) || d.sell_point == 44 || d.sell_point == 45 || d.sell_point == 48){
                                        // htmlData+='<div class="for_rememberText_hdm"> Դուրս գալ ուղեկցող Հարկային Հաշվով </div>'
                                        if(d.sell_point != 15 && d.sell_point != 16){
                                            <?php
                                                if(!in_array($userData[0]['id'], $simple_florist_ids)){
                                            ?>
                                                htmlData += "<img src='../../template/images/pek.png' class='pekIconRed showHideUploadedTimeXML' data-order-id='" + d.id + "'>";
                                                htmlData += "<div><ul class='display-none textShowUploadedTime_" + d.id + "'></ul></div>";
                                            <?php
                                            }
                                            ?>
                                        }
                                    }
                                }
                                co = d.notes_for_florist;
                                if(co != ''){
                                 htmlData += "<div style=\"max-width:135px;word-wrap: break-word;\"><strong style=\"color:#ff0000;\"><button data-order-id='" + d.id + "' class=\"readMoreButtonAjaxFloristNote\">" + co + "</button><div class='display-none forAjaxFloristNoteValue_" + d.id + "' style=\"max-width: 100px\"><span></span></div></div></strong></div>";
                                }
                                if (d.delivery_region == 4){
                                htmlData += "<font style='color: red; font-weight: bold; font-size:20px;'>BARCELONA</font><br>";
                                } else{
                                htmlData += "<br>";
                                }
                                <?php 
                                    if($userData[0]['id'] == 13){
                                ?>
                                // if(d.sender_email != ''){
                                    // htmlData += "<button type='button' onclick='openMail("+d.id+", 3)'>Պատրաստ</button>";
                                    // htmlData += "<button type='button' onclick='openMail("+d.id+", 4)'>Առաքում</button>";
                                    // htmlData += "<button type='button' onclick='openMail("+d.id+", 5)'>Առաքվեց</button>";
                                // }
                                <?php 
                                    }
                                ?>
                                htmlData += "</td>";                

                                <?php
                                if(max(page::filterLevel(3, $levelArray)) < 33)
                                {
                                ?>
                                // co = d.notes_for_florist;
                                // htmlData += "<td class=\"hide-22 hidden-print\"><div style=\"max-width:135px;word-wrap: break-word;\"><strong style=\"color:#ff0000;\"><button data-order-id='" + d.id + "' class=\"readMoreButtonAjaxFloristNote\">" + co + "</button><div class='display-none forAjaxFloristNoteValue_" + d.id + "' style=\"max-width: 100px\"><span></span></div></div></strong></div></td>";

                                <?php
                                }
                                ?>
                                

                                <?php
                                    //end for florists and araqich
                                }
                                ?>
                                
                                if (!subregionType[d.receiver_subregion]) {
                                    subregionType[d.receiver_subregion] = d.receiver_subregion;
                                }
                                if (!streetType[d.receiver_street] && streetType[d.receiver_street] != "") {
                                    streetType[d.receiver_street] = '';
                                }
                                
                                <?php
                                    if(strpos($levelsOfUser, '30') !== false || strpos($levelsOfUser, '31') !== false){
                                        if(!in_array($userData[0]['id'], $simple_florist_ids)){
                                ?>
                                        htmlData += "<td class=\"hide-10\">";
                                        if(d.organisation != null && d.organisation != 0){
                                            htmlData += d.organisation_name + ' - ';
                                        }
                                        <?php
                                            if(!in_array($userData[0]['id'], $travel_operators)){
                                        ?>
                                            if((streetType[d.receiver_street] == null || streetType[d.receiver_street] == '') && (d.sell_point != 15 && d.sell_point != 16)){
                                                htmlData += "<span class='wrongData'>Հնարավոր է հասցեն լինի թերի</span>";
                                            } else {
                                                htmlData += streetType[d.receiver_street];
                                            }
                                        <?php } else { ?>
                                            htmlData += streetType[d.receiver_street];
                                        <?php } ?>
                                        htmlData += ", " + d.receiver_address;
                                        if(d.receiver_tribute != ''){
                                            htmlData += " հարկ  " + d.receiver_tribute + " ";
                                        }
                                        if(d.receiver_entrance != ''){
                                            htmlData += "<br> <?=(defined('RECEIVER_ENTRANCE')) ? RECEIVER_ENTRANCE : '';?>  " + d.receiver_entrance + " <br>";
                                        }
                                        if(d.receiver_floor != ''){
                                            htmlData += " <?=(defined('RECEIVER_FLOOR')) ? RECEIVER_FLOOR : '';?>  " + d.receiver_floor + " <br>";
                                        }
                                        if(d.receiver_door_code != ''){
                                            htmlData += " <?=(defined('RECEIVER_DOOR_CODE')) ? RECEIVER_DOOR_CODE : '';?>  " + d.receiver_door_code;
                                        }
                                        if(d.payment_type != undefined && d.payment_type == 12){
                                            htmlData += " <span class='check_reminder'> - (Դուրս գալ կտրոնով)</span>";
                                        }
                                        htmlData += "<br/>(" + subregionType[d.receiver_subregion] + " <?=(defined('STATE')) ? STATE : 'STATE';?>)</td>";
                                <?php }?>
                                if(d.right_address != null && d.right_address != ''){
                                    htmlData += "<br>";
                                    htmlData += "<span class='rightAddress'>Առաքիչի ուղղած հասցե:<br><img height='25px' src='../../template/icons/important/important.gif'><br> "+ d.right_address +"</span>";
                                }
                                actiondata = "";
                                
                                <?php
                                    }
                                ?>
                                <?php 
                                if(max(page::filterLevel(3, $levelArray)) < 33){
                                ?>
                                    var whoreceived_show = (whoreceived[d.who_received]) ? "<br>#" + whoreceived[d.who_received] + "#" : '';
                                    <?php
                                        if(!in_array($userData[0]['id'], $simple_florist_ids)){
                                    ?>
                                        htmlData += "<td class=\"hide-9\">";
                                        <?php
                                            if(!in_array($userData[0]['id'], $travel_operators)){
                                        ?>
                                            if((d.receiver_name == null || d.receiver_name == '') && (d.sell_point != 15 && d.sell_point != 16) && partner_icon ==''){
                                                htmlData += '<span class="wrongData">Ստացողի անուն գրանցված չէ</span>';
                                            } else {
                                                htmlData += d.receiver_name;
                                            }
                                        <?php } else { ?>
                                            htmlData += d.receiver_name;
                                        <?php }?>
                                        htmlData += "<span style='color: red;'>" + whoreceived_show + "</span>";
                                        <?php
                                            if(!in_array($userData[0]['id'], $travel_operators)){
                                        ?>
                                            if(d.receiver_mood > 0){
                                                htmlData += '<img src="./ico/mood_'+d.receiver_mood+'.png" class="receiverMood" alt="Ստացողի էմոցիան">';
                                            }
                                            <?php
                                            if(!in_array($userData[0]['id'], $travel_operators)){
                                            ?>
                                                if(d.receiver_phone == '' && (d.sell_point != 15 && d.sell_point != 16)){
                                                    htmlData += "<span class='wrongData'>Ստացողի հեռախոսահամարը գրանցված չէ</span>";
                                                } else if(/(^\+[0-9]{6,})(,?\s?\+?[0-9]{6,})*/.exec(d.receiver_phone) == null){
                                                    htmlData += "<br><span class='wrongData'>"+ d.receiver_phone +"</span>";
                                                } else {
                                                    htmlData += "<br>" + d.receiver_phone;
                                                }
                                            <?php } else { ?>
                                                htmlData += "<br>" + d.receiver_phone;
                                            <?php } 
                                                if($list_receiver_fields == 1){
                                            ?>
                                                htmlData += '<br><br><select name="new_flourist" required class="new_flourist hidden-print" style="min-width: 105px; max-width: 115px; "><option value="">Պատասխանատու</option>';
                                                <?php foreach ($flourists as $flourist){ ?>
                                                        htmlData += "<option";
                                                        if(d.flourist_id != null && d.flourist_id == <?= $flourist['id'] ?>) {
                                                            htmlData +=  " selected='selected'";
                                                        }
                                                        htmlData += " value='<?= $flourist['id'] ?>'>"+<?= (defined($flourist['username'])) ?  "'".constant($flourist['username'])."'" : "'".$flourist['username']."'"; ?>+"</option>";
                                                <?php } ?>
                                                htmlData += '</select><br>';
                                                htmlData += "<input type='number' placeholder='այսօր' class='flourist_change_day hidden-print' style='width: 55px;' required>";
                                                htmlData += "<button type='button' class='changeFlourist hidden-print' data-order='" + d.id + "' >OK</button>";
                                            <?php
                                                }
                                            ?>
                                        <?php
                                            }
                                        ?>
                                        htmlData += "</td>";
                                <?php 
                                    }
                                    }
                                ?>
                                
                                <?php
                                    if (max(page::filterLevel(3, $levelArray)) > 33) {
                                ?>
                                htmlData += "<td class=\"hide-11 hidden-print\">";
                                <?php
                                    if(!in_array($userData[0]['id'], $travel_operators)){
                                ?>
                                    if(d.receiver_phone == '' && (d.sell_point != 15 && d.sell_point != 16)){
                                        htmlData += "<span class='wrongData'>Ստացողի հեռախոսահամարը գրանցված չէ</span>";
                                    } else if(/(^\+[0-9]{6,})(,?\s?\+?[0-9]{6,})*/.exec(d.receiver_phone) == null){
                                        htmlData += "<span class='wrongData'>"+ d.receiver_phone +"</span>";
                                    } else {
                                        htmlData += d.receiver_phone;
                                    }
                                <?php } else { ?>
                                    htmlData += d.receiver_phone;
                                <?php } ?>
                                htmlData += "</td>";
                                <?php
                                    }
                                ?>
                                <?php
                                if(max(page::filterLevel(8, $levelArray)) >= 89 || max(page::filterLevel(9, $levelArray)) >= 99){
                                // if(max(page::filterLevel(3, $levelArray)) >= 33){
                                }else{?>
                                
                                
                                    // Gortsoghutyun tab for Florists
                                    <?php
                                        if(!in_array($userData[0]['id'], $simple_florist_ids) && $list_action_fields == 1){
                                    ?>
                                        htmlData += "<td class=\"hide-13 hidden-print\">";
                                        var showSelect = "SELECT";  
                                        if (d.deliverer > 0 ) {
                                            showSelect =  driverN;
                                        }
                                        htmlData += '<select style="width:100px" id="driver_'+d.id+ '" ><option value="' + d.deliverer + '">'+ showSelect +'</option> <?php echo $selectionOption ?></select><br>';
                                        
                                        htmlData += '<hr>';
                                        htmlData += '<select id="stage_'+d.id+ '"  style="width:100px">';
                                        
                                        if (d.stage > 0) {
                                            htmlData += '<option value="'+ d.stage + '">' +  d.stage + '</option>'
                                        }       
                                        htmlData    += '<option value="0"></option>'
                                                    + '<option value="1">1</option>'
                                                    + '<option value="2">2</option>'
                                                    + '<option value="3">3</option>'
                                                    + '<option value="4">4</option>'
                                                    + '<option value="5">5</option>'
                                                    + '<option value="6">6</option>'
                                                    + '<option value="7">7</option>'
                                                    + '<option value="8">8</option>'
                                                    + '<option value="9">9</option>'
                                                    + '<option value="10">10</option>'
                                                    + '</select>';
                                        htmlData += '<hr>';
                                        if (d.step > 0) {
                                            htmlData += 'S <input  id="step_'+d.id+  '" style="width:45px" type="text" placeholder="step" value="' + d.step + '">';
                                        }else {
                                            htmlData += 'S <input  id="step_'+d.id+  '" style="width:45px" type="text" placeholder="step" >';
                                        }
                                        if (d.quantity  > 0 ) {
                                            htmlData += '<input id="quantity_'+d.id+  '" style="width:35px" type="text" placeholder="quantity" value="' +  d.quantity + '">';
                                        } else {
                                            htmlData += '<input id="quantity_'+d.id+  '" style="width:35px" type="text" placeholder="quantity" >';
                                        }
                                        htmlData += '<hr>';
                                        htmlData += '<button style="width:100px" id="clickbutton_' + d.id + '" onclick="onclickSaveButton('+d.id+')" >Փոխել</button>';
                                        htmlData += "</td>";         
                                        <?php } ?>                   
                                
                
                                
                                //was before gortsoxutyun added by Ruben 
                                //htmlData += "<td class=\"hide-13 hidden-print\"><img src=\"<?=$rootF?>/template/icons/ontime/" + d.ontime + ".png\"></td>";
                                <?php } ?>

                                //#12
                                //co = d.sender_name+d.sender_region+d.sender_address+d.sender_phone+d.sender_email;
                                var pr_lng = (recLang[d.delivery_language_primary]) ? recLang[d.delivery_language_primary] : 'N/A';
                                var sc_lng = (recLang[d.delivery_language_secondary]) ? recLang[d.delivery_language_secondary] : 'N/A';
                                var drsn = (order_reason[d.delivery_reason]) ? '<br/>(' + order_reason[d.delivery_reason] + ')' : '';
                                <?php
                                    if (max(page::filterLevel(3, $levelArray)) > 33) {
                                ?>
                                htmlData += "<td class=\"hide-14 hidden-print\">";
                                if(d.anonym == 0){
                                    <?php
                                        if(!in_array($userData[0]['id'], $travel_operators)){
                                    ?>
                                        if((d.sender_name == null || d.sender_name == '') && (d.sell_point != 15 && d.sell_point != 16) && partner_icon ==''){
                                            htmlData += '<span class="wrongData">Ուղարկողի անուն գրանցված չէ</span>';
                                        } else {
                                            htmlData += d.sender_name;
                                        }
                                    <?php } else { ?>
                                        htmlData += d.sender_name;
                                    <?php } ?>
                                }
                                <?php if(max(page::filterLevel(3, $levelArray)) > 33){ ?>
                                    htmlData += "<br>(" + pr_lng + "," + sc_lng + ")" ;
                                <?php
                                    }
                                ?>
                                htmlData += drsn + "<br>";
                                <?php if(max(page::filterLevel(3, $levelArray)) <= 33){ ?>
                                    htmlData += "<span class='created_time'>"+ d.created_time +"</span><br/>";
                                <?php } ?>
                                htmlData += "</td>";
                                <?php
                                    }
                                ?>
                                <?php if(max(page::filterLevel(3, $levelArray)) > 33){ ?>
                                    htmlData += "<td class=\"hide-15 hidden-print\">";
                                    if(d.anonym == 0){
                                        <?php
                                            if(!in_array($userData[0]['id'], $travel_operators)){
                                        ?>
                                            if(d.sender_phone == '' && (d.sell_point != 15 && d.sell_point != 16) && partner_icon ==''){
                                                htmlData += "<span class='wrongData'>Ուղարկողի հեռախոսահամար գրանցված չէ</span>";
                                            } else if(/(^\+[0-9]{6,})(,?\s?\+?[0-9]{6,})*/.exec(d.sender_phone) == null){
                                                htmlData += "<span class='wrongData'>"+ d.sender_phone +"</span>";
                                            } else {
                                                htmlData += d.sender_phone;
                                            }
                                        <?php } else { ?>
                                            htmlData += d.sender_phone;                                    
                                        <?php } ?>
                                        <?php
                                            if(max(page::filterLevel(3, $levelArray)) >= 33){
                                        ?>
                                            htmlData += "<br/>";
                                            <?php
                                                if(!in_array($userData[0]['id'], $travel_operators)){
                                            ?>
                                                if(d.sender_email == '' && (d.sell_point != 15 && d.sell_point != 16) && partner_icon ==''){
                                                    htmlData += "<span class='wrongData'>Ուղարկողի E-mail գրանցված չէ</span>";
                                                } else {
                                                    htmlData += d.sender_email;
                                                }
                                            <?php } else { ?>
                                                htmlData += d.sender_email;                                    
                                            <?php } ?>
                                        <?php } ?>
                                    }
                                    htmlData += "</td>";
                                <?php } ?>
                                //htmlData += "<td></td>";
                                <?php
                                if(max(page::filterLevel(3, $levelArray)) >= 33)
                                {
                                ?>
                                htmlData += "<td class=\"hide-16\">";
                                    if(d.anonym == 0){
                                        <?php
                                            if(!in_array($userData[0]['id'], $travel_operators)){
                                        ?>
                                            if((d.sender_country == null || d.sender_country == '' || d.sender_country == 0) && (d.sell_point != 15 && d.sell_point != 16) && partner_icon ==''){
                                                htmlData += '<span class="wrongData">Ուղարկողի Երկիրը գրանցված չէ</span>';
                                            }
                                        <?php } ?>
                                        htmlData += d.sender_address + "<br/>" + d.sender_region;
                                    }
                                htmlData +=  "</td>";
                                //htmlData += "<td></td>";
                                <?php
                                }else{
                                ?>
                                // htmlData += "<td class=\"hide-16\"> ";
                                //     if(d.anonym == 0){
                                //         htmlData +=  d.sender_region ;
                                //     }
                                // htmlData += "</td>";
                                <?php
                                }
                                ?>
                                <?php
                                if(max(page::filterLevel(3, $levelArray)) >= 33)
                                {
                                ?>
                                if (!sellPoint[d.sell_point]) {
                                    sellPoint[d.sell_point] = "";
                                }
                                //#15
                                htmlData += "<td class=\"hide-19 hidden-print\">" + sellPoint[d.sell_point] + "<br/>";
                                htmlData += "<a href='https://whatismyipaddress.com/ip/"+ d.keyword +"'  target='_blank' style='text-decoration: underline; color: black;'>" + d.keyword + "</td>";
                                //#16
                                //htmlData += "<td></td>";
                                //#17
                                co = d.log;
                                // htmlData += "<td class=\"hide-20 hidden-print\" nowrap><div class=\"article\"><button class=\"read-more\">VIEW " + co.length + "</button><div class=\"text short\">" + d.log + "</div></div></td>";

                                <?php
                                }else{
                                ?>
                                htmlData += "<td class=\"hide-17 hidden-print\" > By ";
                                if(d.operator == 'Robot'){
                                    htmlData += d.operator;
                                }
                                else{
                                    var operator_first_name = operators_info[d.operator]['full_name_am'].split(' ');
                                    htmlData +=  operator_first_name[0];
                                }
                                <?php if(max(page::filterLevel(3, $levelArray)) <= 33){ ?>
                                    htmlData += "<br><span class='created_time'>"+ d.created_time +"</span><br/>";
                                <?php } ?>
                                htmlData += "</td>";
                                <?php
                                }
                                ?>
                                htmlData += "</tr>";
                                <?php

                                if(!empty(array_intersect(array(89), explode(",", $get_lvl[0])))){
                                ?>
                                CCo++;
                                countP = CCo;
                                $("#shopCT").html(countP);
                            } else {
                                $("#shopCT").html(countP);
                            }
                            <?php
                            }
                            ?>
                            <?php

                            if(max(page::filterLevel(3, $levelArray)) < 33)
                            {
                            ?>
                            CCo++;
                            countP = CCo;
                            $("#shopCT").html(countP);
                        } else {
                            $("#shopCT").html(countP);
                        }
                        <?php
                        }
                        ?>

                    }
                    $("#onC").html("(" + countP + ")");
                }
                
                //htmlData = htmlData.replace(new RegExp(global_filter_text, 'g'),'<span style="background-color: yellow;color:black;">'+global_filter_text+'</span>');
                $('#dataTable').html(htmlData);
                
                if (global_filter_text != false) {
                    highlight(global_filter_text, $('#dataTable').html());
                }
                $("#loading").css("display", "none");
                let showRelated = <?= (max(page::filterLevel(3, $levelArray)) < 33) ? "true" : "false" ?>;
                if(showRelated){
                    $('#showRelatedImages').attr('data-clicked', 0);
                    $('#showRelatedImages').trigger('click');
                }
            });
            //end
        }, 1000);
        setTimeout(() => {
            $('#dataTable tr').each(function(index, elem){
                let ord_id = $(elem).attr('data-id');
                if(ord_id != undefined){
                    $.get("<?=$rootF?>/data.php?cmd=mail_log&order_id='"+ord_id+"'", function(resp_data){
                        let c1 = resp_data.data[1] != undefined ? resp_data.data[1] : 0;
                        let c2 = resp_data.data[2] != undefined ? resp_data.data[2] : 0;
                        let c3 = resp_data.data[3] != undefined ? resp_data.data[3] : 0;
                        let c4 = resp_data.data[4] != undefined ? resp_data.data[4] : 0;
                        let c5 = resp_data.data[5] != undefined ? resp_data.data[5] : 0;
                        let c6 = resp_data.data[6] != undefined ? resp_data.data[6] : 0;
                        let c8 = resp_data.data[8] != undefined ? resp_data.data[8] : 0;
                        // Added,Changed By Hrach
                        if( c1 > 0 ){
                            $(elem).find('.vjarelMailButton').html('Վճարել ('+ c1 + ')');
                        }
                        if( c2 > 0 ){
                            $(elem).find('.hastatelMailButton').html('Հաստատել ('+ c2 +')');
                        }
                        if( c8 > 0 ){
                            $(elem).find('.nkarMailButton').html('Ուղարկել Նկար ('+ c8 +')');
                        }
                        if( c6 > 0 ){
                            if($(".OffOnOfStugelButton").val() == 1){
                                $(elem).find('.stugelMailButton').html('Ստուգել ('+ c6 +')');
                            }
                        }
                        if( c3 > 0 ){
                            if($(".OffOnOfPatrastButton").val() == 1){
                                $(elem).find('.patrastMailButton').html('Պատրաստ ('+ c3 +')');
                            }
                        }
                        if( c4 > 0 ){
                            if($(".OffOnOfAraqumButton").val() == 1){
                                console.log($(elem).find('.mailButton').eq(4))
                                $(elem).find('.araqumMailButton').html('Առաքում ('+ c4 +')');
                            }
                        }
                        if( c5 > 0 ){
                            if($(".OffOnOfAraqvecButton").val() == 1){
                                $(elem).find('.araqvecMailButton').html('Առաքվեց ('+ c5 +')');
                            }
                        }
                        //
                    })
                }
            });
        }, 2000);
        setTimeout(() => {
            let w_count = $('.wrongData').length;
            $('#warningCount').html(w_count);
        },6000)
        return false;
    }
    filter(null);
    $('#menuDrop .dropdown-menu').on({
        "click": function (e) {
            e.stopPropagation();
        }
    });
    function showCount(el) {
        toP = el.value;
        filter(null, true);
    }
    function getTotalPriceOfOrder(price,currency,sell_point){
        //buy.am 16 15% commission
        //memu.am 37 25% commission
        var $price_differ = 0;
        var $total_price = order_currency.convert(currency, price);

        if (sell_point == 16) {
            var $price_differ = ($total_price * 20 ) / 100;
            $total_price = $total_price - $price_differ;
        } else if (sell_point == 15) {
            var $price_differ = ($total_price * 25 ) / 100;
            $total_price = $total_price - $price_differ;
        } else if (sell_point == 32) {
            var $price_differ = ($total_price * 20 ) / 100;
            $total_price = $total_price - $price_differ;
        }
        return $total_price;
    }
    $(document).on('click', ".printBtnCheckErt", function () {
        var href = $(this).attr('data-href');
        var order_id = $(this).attr('data-order-id');
        $.ajax({
            type: 'post',
            url: location.href,
            data: {
                getOrderInfo: true,
                order_id: order_id
            },
            success: function(resp){
                resp = JSON.parse(resp);
                if(jQuery.inArray(resp.delivery_status, vjarvacIds) !== -1){
                    if(resp.stage == 0){
                        alert('Երթ նշված չէ!');
                    }
                }
                setTimeout(function(){
                    window.open(href);
                    // window.location.href = href;
                },200)
            }
        })
    })
    $(document).on('click', "button.read-more", function () {

        var elem = $(this).parent().find(".text");
        if (elem.hasClass("short")) {
            elem.removeClass("short").addClass("full");

        }
        else {
            elem.removeClass("full").addClass("short");

        }
    });
    $(document).on('click', "button.show-ALL", function () {

        var elem = $("div").find(".text");
        if (elem.hasClass("short")) {
            elem.removeClass("short").addClass("full");

        }
        else {
            elem.removeClass("full").addClass("short");

        }
    });
    $(document).ready(function(){
        setTimeout(function(){
            let showRelated = <?= (max(page::filterLevel(3, $levelArray)) < 33) ? "true" : "false" ?>;
            if(showRelated){
                $('#showRelatedImages').trigger('click');
            }
        }, 2000)
        setTimeout(function(){
            $.ajax({
                type: 'post',
                url: location.href,
                data: {
                    getUnreadPosts: true
                },
                success: function(resp){
                    if(resp >= 1){
                        $(".unreadPosts").html("<marquee style='background:red;color:#fff;width:100%;'>Հարգելի <?php echo $userData[0]['full_name_am'] ?> դուք ունեք " + resp + " չկարդացաց կարևոր հայտարարություն, որ հարկավոր է կարդալ և հաստատել․</marquee><script>setTimeout(function(){window.location.href = '/info/malus.php';},900000)")

                    }
                }
            })
        }, 3000)
    })
    $(document).ready(function(){
        // 8888888
        // setTimeout(function(){
        //     $('#dataTable tr').each((ind, element) => {
        //         var id = $(element).attr('data-id');
        //         let $self = $(element);
        //         $.ajax({
        //             type: 'post',
        //             url: location.href,
        //             data: {
        //                 id: id,
        //                 checkImages: true
        //             },
        //             success: function(resp){
        //                 let data = JSON.parse(resp);
        //                 if(data != undefined && data.showImages != undefined){
        //                     if(data.showImages == false){
        //                         $self.find('.showChoosenRelated').css('display', 'none');
        //                     }
        //                 }
        //             }
        //         })
        //     });
        // }, 3000);

    })
    function zoom_img(id) {
        // $.get("<?=$rootF?>/data.php?cmd=order_images&itemId=" + id, function (get_data) {
        //     $('a[data-imagelightbox="' + id + '"]').remove();
        //     if (get_data.data.images) {
        //         if (!$('a[data-imagelightbox="' + id + '"]').length) {
        //             var imd = get_data.data.images;
        //             for (var u = 0; u < imd.length; u++) {
        //                 $('body').append('<a href="product_images/' + imd[u].image_source + '" data-imagelightbox="' + id + '" style="display:none"><img src="product_images/' + imd[u].image_source + '" alt="' + imd[u].image_note + '"></a>');

        //             }
        //         }

                var selectorF = 'a[data-imagelightbox="' + id + '"]';
                var instanceF = $(selectorF).imageLightbox(
                    {
                        quitOnImgClick: false,
                        onLoadStart: function () {
                            captionOff();
                            activityIndicatorOn();
                        },
                        onLoadEnd: function () {
                            captionOn();
                            activityIndicatorOff();
                        },
                        onEnd: function () {
                            captionOff();
                            activityIndicatorOff();
                        }
                    });
                instanceF.switchImageLightbox(0);
            // } else {
            //     alert('<?=(defined('NKAR_CHKA')) ? NKAR_CHKA : 'NKAR_CHKA';?>');
            // }
        // });
    }
    $('body').on('click', '#showCucanishner', function(){
        var delivery_date = $('input[name=adf]').val();
        var calculate_salary_in_list = $(".calculate_salary_in_list").val();
        var calculate_advertisement_in_list = $(".calculate_advertisement_in_list").val();
        var calculate_other_costs_in_list = $(".calculate_other_costs_in_list").val();
        var ordersCount = 1
        var salary_for_each_order = 0;
        var advertisement_for_each_order = 0;
        var other_costs_for_each_order = 0;
        if(delivery_date){
            ordersCount = $('#dataTable tr').length;
            salary_for_each_order = calculate_salary_in_list/ordersCount;
            advertisement_for_each_order = calculate_advertisement_in_list/ordersCount;
            other_costs_for_each_order = calculate_other_costs_in_list/ordersCount;
        }
        $('#dataTable tr').each( (ind, element) => {
           var id = $(element).attr('data-id');
           $.ajax({
            type: 'POST',
            url: 'ajax.php',
            data: {
                'isset_out_prod': true,
                'id': id,
            },
            success: function(response){
                if(response){
                    for(var i = 0 ; i < response.length ; i++){
                        var order_product_sold_price = Math.ceil(response[i]['order_product_sold_price']);
                        var total_inqnarjeq = Math.ceil(response[i]['total_inqnarjeq']);
                        var total_inqnarjeq_show = "Ինքնարժեք ` " + total_inqnarjeq;
                        if(delivery_date && total_inqnarjeq != 0){
                            if(calculate_salary_in_list != 0){
                                total_inqnarjeq+= Math.ceil(salary_for_each_order);
                                total_inqnarjeq_show+= ", Աշխ․ ՝ " + Math.ceil(salary_for_each_order);
                            }
                            if(calculate_advertisement_in_list != 0){
                                total_inqnarjeq+= Math.ceil(advertisement_for_each_order);
                                total_inqnarjeq_show+= ", Գով․ ՝ " + Math.ceil(advertisement_for_each_order);
                            }
                            if(calculate_other_costs_in_list != 0){
                                total_inqnarjeq+= Math.ceil(other_costs_for_each_order);
                                total_inqnarjeq_show+= ", Այլ ՝ " + Math.ceil(other_costs_for_each_order);
                            }
                        }
                        var cucanishNumber = Math.ceil(getCucanishNumber(total_inqnarjeq, order_product_sold_price-total_inqnarjeq));
                        var getcolorOfCucanish = getColorOfCucanish(cucanishNumber);
                        var html = "<hr><p>Վաճառված գինը `" + order_product_sold_price + "</p>";
                        html += "<p>" + total_inqnarjeq_show + "</p>";
                        html += "<p>Ցուցանիշ `<span style='padding:3px;background-color:" + getcolorOfCucanish + "'> " + cucanishNumber/100 + "%</span></p>";
                        $(".div_cucanish_for_" + id + "_" + response[i]['order_product_id']).append(html);
                    }
                }
            }
        })
        })
    })
    function getColorOfCucanish(cucanishNumber)
    {
        var cucanishNumber_text_color = 'black';
        if (cucanishNumber <= 20) {
            cucanishNumber_text_color = 'red';
        } else if (cucanishNumber > 20 && cucanishNumber <= 30) {
            cucanishNumber_text_color = 'Black';
        } else if (cucanishNumber > 30 && cucanishNumber <= 50){
            cucanishNumber_text_color = 'Yellow';
        } else if (cucanishNumber > 50 && cucanishNumber <= 75) {
            cucanishNumber_text_color = 'darkslategrey';
        } else if (cucanishNumber > 75 && cucanishNumber <= 100) {
            cucanishNumber_text_color = 'Magenta';
        } else if (cucanishNumber > 100 && cucanishNumber <= 150) {
            cucanishNumber_text_color = 'Lightgreen';
        } else if (cucanishNumber > 150 && cucanishNumber <= 200) {
            cucanishNumber_text_color = 'Blue';
        } else if (cucanishNumber > 0 && cucanishNumber > 100) {
            cucanishNumber_text_color = 'Green';
        }
        return cucanishNumber_text_color;
    }
    function getCucanishNumber(total_cost_price, left_over_price)
    {
        if(total_cost_price > 0){
            return (100 * left_over_price) / total_cost_price;
        }
        else{
            return 0;
        }
    }
    $('body').on('click', '#showRelatedImages', function(){
        console.log('clicked');
        if($(this).attr('data-clicked') == 0){
            $("#showCucanishner").removeClass('display-none');
            $('a[data-imagelightbox]').remove();
            $('.related_images').html('');
            $('.out_images').html('');
            $('.product_images').html('');
            $(this).attr('data-clicked', 1);
            $('#dataTable tr').each( (ind, element) => {
                var id = $(element).attr('data-id');
                $.get("<?=$rootF?>/data.php?cmd=related_images&itemId=" + id, function (get_data) {
                    if (!$('a[data-imagelightbox="' + id + '"]').length) {
                        if (get_data.data.related_images) {
                            var imc = get_data.data.related_images;
                            var path = "jos_product_images/";
                            for (var u = 0; u < imc.length; u++) {
                                $('body').append('<a href="'+path + imc[u].image_source + '" data-imagelightbox="' + id + '" style="display:none"><img src="'+path+ + imc[u].image_source + '" alt="' + imc[u].image_note + '"></a>');
                                let relatedHtml = '<div class="col-sm-12" style="clear: both;border-bottom:1px solid black;margin-bottom:5px">';
                                if(imc[u].for_purchase){
                                    relatedHtml += '<div class="imgDiv" style="border:2px solid red;padding:2px">';
                                }
                                else{
                                    relatedHtml += '<div class="imgDiv">';
                                }
                                relatedHtml += '<img onclick="zoom_img('+id+')" src="'+path + imc[u].image_source +'" alt="' + imc[u].image_note + '" style="width: auto; max-width:100px; height: 90px; float: left;">';
                                if(imc[u].product_width > 0){
                                    relatedHtml += '<a href="/account/flower_orders/jos_product_images/bigimages/' + imc[u].image_full_source + '" target="_blank"><div class="w-size">'+parseFloat(imc[u].product_width).toFixed(2)+'</div></a>';
                                }
                                if(imc[u].product_height > 0){
                                    relatedHtml += '<a href="/account/flower_orders/jos_product_images/bigimages/' + imc[u].image_full_source + '" target="_blank"><div class="h-size">'+parseFloat(imc[u].product_height).toFixed(2)+'</div></a>';
                                }
                                var prod_descrip = imc[u].short_desc.toLowerCase();
                                var sample_show = false;
                                if (prod_descrip.indexOf("նմուշ") >= 0){
                                    sample_show = true;
                                }
                                relatedHtml += '</div>';
                                relatedHtml += '<span class="relatedProductTitle div_cucanish_for_' + id + "_"  + imc[u].related_id + '">'+ imc[u].changed_name;
                                <?php
                                    if(max(page::filterLevel(3, $levelArray)) >= 33)
                                    {
                                ?>
                                        relatedHtml += " ($"+Number(imc[u].price).toFixed(2)+")";
                                <?php
                                    } 
                                ?>
                                if(sample_show){
                                    relatedHtml += "<img src='../../template/icons/sample.gif' style='height:63px;float:right;margin-left:7px;margin-bottom:10px'>";
                                }
                                relatedHtml += '</span>';
                                <?php
                                    if((max(page::filterLevel(3, $levelArray)) < 33 && !in_array($userData[0]['id'], array(88,89,90,91,92,93,94,95,96,97,98,99,100,101,102))) || in_array($userData[0]['id'], array(4, 87, 35)))
                                    {
                                ?>
                                    if($(element).attr('data-status') == "1" || $(element).attr('data-status') == "13" || $(element).attr('data-status') == "12" || $(element).attr('data-status') == "3"){
                                        <?php
                                            if(!in_array($userData[0]['id'], $simple_florist_ids)){
                                        ?>
                                            relatedHtml += '<label>Պատրաստ ';
                                            relatedHtml += '<input type="checkbox" class="productRelatedReady" data-order-id="'+imc[u].id+'" data-related-id="'+imc[u].related_id+'"';
                                            if(imc[u].related_ready){
                                                relatedHtml += 'checked';
                                                
                                            }
                                            relatedHtml += '>';
                                            relatedHtml += '</label>';

                                        relatedHtml += '<div class="forPurchaseDiv">';
                                        <?php
                                            if($userData[0]['id'] == '27'){
                                                ?>
                                                    relatedHtml += '<label>Գնման ենթակա  ';
                                                    var checked_for_purchase = '';
                                                    if(imc[u].for_purchase == 1){
                                                        checked_for_purchase = 'checked';
                                                    }
                                                    relatedHtml += '<input type="checkbox" ' + checked_for_purchase + ' class="forPurchase" data-id="'+imc[u].order_related_id+'" data-type="1" data-user="<?= $userData[0]['id']?>"';
                                                    relatedHtml += '>';
                                                    relatedHtml += '</label>';
                                                <?php
                                            }
                                            else{
                                                ?>
                                                    if(imc[u].for_purchase == 3){
                                                        relatedHtml+="<span style='color:red;'>Գնված! "
                                                    }
                                                    else{
                                                        relatedHtml += '<label>Գնման ենթակա  ';
                                                        var checked_for_purchase = '';

                                                        if(imc[u].for_purchase == 1){
                                                            checked_for_purchase = 'checked';
                                                        }
                                                        relatedHtml += '<input type="checkbox" ' + checked_for_purchase + ' class="forPurchase" data-id="'+imc[u].order_related_id+'" data-type="1" data-user="<?= $userData[0]['id']?>"';
                                                        if(imc[u].for_purchase){
                                                             <?php
                                                                if($userData[0]['id'] != 27){
                                                                    ?>
                                                                    relatedHtml += 'checked disabled';
                                                                    <?php
                                                                }
                                                                else{
                                                                    ?>
                                                                    relatedHtml += 'checked';   
                                                                    <?php
                                                                }
                                                            ?>
                                                        }
                                                        relatedHtml += '>';
                                                        relatedHtml += '</label>';
                                                    }
                                                <?php
                                            }
                                        ?>
                                        
                                        <?php
                                        }
                                        ?>
                                        relatedHtml += '<span class="who_requested">';
                                        if(imc[u].who_requested != '' && imc[u].who_requested != null){
                                            relatedHtml += imc[u].who_requested;
                                        }
                                        relatedHtml += '</span>';
                                        relatedHtml += '</div>';
                                    }
                                <?php
                                    } 
                                ?>
                                relatedHtml += '<br>';
                                relatedHtml += '<div class="col-md-12"><img src="../../template/icons/baxadrutyun.jpg" class="img_for_stock_prods" data-prod-id="' + imc[u].related_id + '" data-order-id="' + imc[u].id + '" style="height:30px;margin-left:7px;margin-bottom:10px;float:left" ><img src="../../template/icons/green-icon1.png" class="img_for_stock_default_prods" data-prod-id="' + imc[u].related_id + '" data-order-id="' + imc[u].id + '" style="height:30px;margin-bottom:10px;float:left" ><div  style="display:none;float:left;margin-top:5px;margin-left:7px" class="div_for_append_input_prepair_note_'  + imc[u].id + '_' +  imc[u].related_id + '"></div><div class="div_for_stock_prods_' + imc[u].id + "_" + imc[u].related_id + ' col-md-12" style="display:none;float:left"></div>' + '<div class="col-md-12"><span style="color:blue;display:none" class="productDesc">'+imc[u].short_desc+"</span></div>";
                                relatedHtml += "<div class='col-md-12'></div>" + '<div  style="display:none;color:blue" class="div_for_stock_default_prods_' + imc[u].id + '_' + imc[u].related_id + ' col-md-12"></div>';
                                relatedHtml += '</div>';
                                $(element).find('.related_images').append(relatedHtml);
                            }
                        }
                        if (get_data.data.images) {
                            var imd = get_data.data.images;
                            for (var u = 0; u < imd.images.length; u++) {
                                var image_path = '';
                                var order_created_date = imd.orderInfo[0].created_date;
                                order_created_date = order_created_date.split('-');
                                image_path =  order_created_date[1] + '-' + order_created_date[0].substr(2, 2);
                                $('body').append('<a href="product_images/' + image_path + '/' + imd.images[u].image_source + '" data-imagelightbox="' + id + '" style="display:none"><img src="product_images/' + image_path + '/' + imd.images[u].image_source + '" alt="' + imd.images[u].image_note + '"></a>');
                                otherProductHtml = '<div class="col-sm-12">';
                                otherProductHtml += '<img onclick="zoom_img('+id+')" src="product_images/' + image_path + '/'+imd.images[u].image_source +'" style="width: auto; max-width:100px; height: 90px; float: left;">';
                                otherProductHtml += '<span>'+imd.images[u].image_note+'</span>';
                                <?php
                                    if(max(page::filterLevel(3, $levelArray)) >= 33)
                                    {
                                        ?>
                                        otherProductHtml += " ($"+Number(imd.images[u].price).toFixed(2)+")";
                                <?php
                                    } 
                                ?>
                                
                                <?php
                                    if((max(page::filterLevel(3, $levelArray)) < 33 && !in_array($userData[0]['id'], array(88,89,90,91,92,93,94,95,96,97,98,99,100,101,102))) || in_array($userData[0]['id'], array(4, 87, 35)))
                                    {
                                ?>
                                    if($(element).attr('data-status') == "1" || $(element).attr('data-status') == "12"){
                                        <?php
                                            if(!in_array($userData[0]['id'], $simple_florist_ids)){
                                        ?>
                                        otherProductHtml += '<label>Պատրաստ ';
                                        otherProductHtml += '<input type="checkbox" class="productDeliveryReady" data-id="'+imd.images[u].id+'" data-order-id="'+imd.images[u].rg_order_id+'"';
                                        if(imd.images[u].ready){
                                            otherProductHtml += 'checked';
                                        }
                                        otherProductHtml += '>';
                                        otherProductHtml += '</label>';
                                        otherProductHtml += '<div class="forPurchaseDiv">';
                                        otherProductHtml += '<label>Գնման ենթակա  ';
                                        otherProductHtml += '<input type="checkbox" class="forPurchase" data-id="'+imd.images[u].id+'" data-type="2" data-user="<?= $userData[0]['id']?>"';
                                        if(imd.images[u].for_purchase){
                                             <?php
                                                if($userData[0]['id'] != 27){
                                                    ?>
                                                    otherProductHtml += 'checked disabled';
                                                    <?php
                                                }
                                                else{
                                                    ?>
                                                    otherProductHtml += 'checked';
                                                    <?php
                                                }
                                            ?>
                                        }
                                        otherProductHtml += '>';
                                        otherProductHtml += '</label>';
                                        <?php
                                            }
                                        ?>
                                        otherProductHtml += '<span class="who_requested">';
                                        if(imd.images[u].username != '' && imd.images[u].username != null){
                                            otherProductHtml += imd.images[u].username;
                                        }
                                        otherProductHtml += '</span>';
                                        otherProductHtml += '</div>';
                                    }
                                <?php
                                    } 
                                ?>
                                otherProductHtml += '<br>';
                                otherProductHtml += '<span class="productDesc">'+imd.images[u].product_desc+'</span></div>';
                                $(element).find('.product_images').append(otherProductHtml);
                            }
                        }                    
                    }
                    if (get_data.data.out_images) {
                        if (!$('a[data-imagelightbox="out' + id + '"]').length) {
                            var imd = get_data.data.out_images;
                            var path = "product_out_images/";
                            for (var u = 0; u < imd.length; u++) {
                                $('body').append('<a href="'+path + imd[u].fileName + '" data-imagelightbox="out' + id + '" style="display:none"><img src="'+path+ + imd[u].fileName + '"></a>');
                                $(element).find('.out_images').append('<div class="col-sm-3 outimg"><img onclick="zoom_out('+id+')" src="'+path + imd[u].fileName +'" style="width: auto; max-width:130px; height: 90px;"></div>');
                            }
                        }                        
                    }
                    
                });
            }) 
        } else {
            $("#showCucanishner").addClass('display-none');
            $(this).attr('data-clicked', 0);
            $('.related_images').html('');
            $('.out_images').html('');
            $('.product_images').html('');
            $('a[data-imagelightbox]').remove();
        }
    });
    $('body').on('change', '.productRelatedReady', function(){
        let val = 0;
        let $self = $(this);
        if($(this).prop('checked')){
            val = 1; 
        } else {
            val = 0;
        }
        let order_id = $(this).attr('data-order-id');
        let related_id = $(this).attr('data-related-id');
        $.get("ajax.php?change_product_related=true&order_id="+order_id+"&related_id="+related_id+"&val="+val, function (get_data) {
            if(get_data.finished != undefined && get_data.finished == 0){
                $self.closest('tr').find('.statusImage').attr('src', '../../template/icons/status/12.png')
            } else {
                $self.closest('tr').find('.statusImage').attr('src', '../../template/icons/status/1.png')
            }
        });
    });
    $('body').on('change', '.productDeliveryReady', function(){
        let val = 0;
        let $self = $(this);
        if($(this).prop('checked')){
            val = 1; 
        } else {
            val = 0;
        }
        let order_id = $(this).attr('data-order-id');
        let id = $(this).attr('data-id');
        $.get("ajax.php?change_product_delivery_related=true&delivery_id="+id+"&val="+val+"&order_id="+order_id, function (get_data) {
            if(get_data.finished != undefined && get_data.finished == 0){
                $self.closest('tr').find('.statusImage').attr('src', '../../template/icons/status/12.png')
            } else {
                $self.closest('tr').find('.statusImage').attr('src', '../../template/icons/status/1.png')
            }
        });
    });
    $('body').on('change', '.forPurchase', function(){
        var order_id = $(this).parent().closest('tr').attr('data-id');
        var update_to_null_for_purchase = true;
        var products_divs = $(this).parent().closest('.hide-8').find('.related_images').find('.col-sm-12');
        var upload_products_divs = $(this).parent().closest('.hide-8').find('.product_images').find('.col-sm-12');
        for(var i = 0 ; i < products_divs.length; i++){
            if($(products_divs[i]).find('.forPurchase').is(':checked')){
                update_to_null_for_purchase = false;
            }
        }
        for(var i = 0 ; i < upload_products_divs.length; i++){
            if($(upload_products_divs[i]).find('.forPurchase').is(':checked')){
                update_to_null_for_purchase = false;
            }
        }
        let $self = $(this);
        let val = 0;
        let user = 0;
        if($self.prop('checked')){
            val = 1;
            user = $self.attr('data-user');
        }
        if(val == 0){
            $(this).after('<span style="color:red"> Գնված! </span>');
        }
        console.log(val);
        var user_id = $(this).attr('data-user');
        let id = $self.attr('data-id');
        let type = $self.attr('data-type');
        $.ajax({
            type: 'POST',
            url: 'ajax.php',
            data: {
                'change_for_purchase': true,
                'forPurchase': val,
                'id': id,
                'type': type,
                'order_id': order_id,
                'user_id': user_id,
                'update_to_null_for_purchase': update_to_null_for_purchase,
                'user': user
            },
            success: function(response){
                $self.siblings('.who_requested').html(response.user)
            }
        })
    })
    function zoom_related(id){
        var selectorF = 'a[data-imagelightbox="related' + id + '"]';
        var instanceF = $(selectorF).imageLightbox(
            {
                quitOnImgClick: false,
                onLoadStart: function () {
                    captionOff();
                    activityIndicatorOn();
                },
                onLoadEnd: function () {
                    captionOn();
                    activityIndicatorOff();
                },
                onEnd: function () {
                    captionOff();
                    activityIndicatorOff();
                }
            });
        instanceF.switchImageLightbox(0);
    }
    function zoom_out(id){
        var selectorF = 'a[data-imagelightbox="out' + id + '"]';
        var instanceF = $(selectorF).imageLightbox(
            {
                quitOnImgClick: false,
                onLoadStart: function () {
                    captionOff();
                    activityIndicatorOn();
                },
                onLoadEnd: function () {
                    captionOn();
                    activityIndicatorOff();
                },
                onEnd: function () {
                    captionOff();
                    activityIndicatorOff();
                }
            });
        instanceF.switchImageLightbox(0);
    }
    var
        // ACTIVITY INDICATOR
        activityIndicatorOn = function () {
            $('<div id="imagelightbox-loading"><div></div></div>').appendTo('body');
        },
        activityIndicatorOff = function () {
            $('#imagelightbox-loading').remove();
        },

        // OVERLAY
        overlayOn = function () {
            $('<div id="imagelightbox-overlay"></div>').appendTo('body');
        },
        overlayOff = function () {
            $('#imagelightbox-overlay').remove();
        },

        // CLOSE BUTTON
        closeButtonOn = function (instance) {
            $('<button type="button" id="imagelightbox-close" title="Close"></button>').appendTo('body').on('click touchend', function () {
                $(this).remove();
                instance.quitImageLightbox();
                return false;
            });
        },
        closeButtonOff = function () {
            $('#imagelightbox-close').remove();
        },

        // CAPTION
        captionOn = function () {
            var description = $('a[href="' + $('#imagelightbox').attr('src') + '"] img').attr('alt');
            if (description !== undefined && description.length > 0)
                $('<div id="imagelightbox-caption">' + description + '</div>').appendTo('body');
        },
        captionOff = function () {
            $('#imagelightbox-caption').remove();
        },

        // NAVIGATION
        navigationOn = function (instance, selector) {
            var images = $(selector);
            if (images.length) {
                var nav = $('<div id="imagelightbox-nav"></div>');
                for (var i = 0; i < images.length; i++)
                    nav.append('<button type="button"></button>');

                nav.appendTo('body');
                nav.on('click touchend', function () {
                    return false;
                });

                var navItems = nav.find('button');
                navItems.on('click touchend', function () {
                    var $this = $(this);
                    if (images.eq($this.index()).attr('href') != $('#imagelightbox').attr('src'))
                        instance.switchImageLightbox($this.index());

                    navItems.removeClass('active');
                    navItems.eq($this.index()).addClass('active');

                    return false;
                })
                    .on('touchend', function () {
                        return false;
                    });
            }
        },
        navigationUpdate = function (selector) {
            var items = $('#imagelightbox-nav button');
            items.removeClass('active');
            items.eq($(selector).filter('[href="' + $('#imagelightbox').attr('src') + '"]').index(selector)).addClass('active');
        },
        navigationOff = function () {
            $('#imagelightbox-nav').remove();
        },

        // ARROWS
        arrowsOn = function (instance, selector) {
            var $arrows = $('<button type="button" class="imagelightbox-arrow imagelightbox-arrow-left"></button><button type="button" class="imagelightbox-arrow imagelightbox-arrow-right"></button>');

            $arrows.appendTo('body');

            $arrows.on('click touchend', function (e) {
                e.preventDefault();

                var $this = $(this),
                    $target = $(selector + '[href="' + $('#imagelightbox').attr('src') + '"]'),
                    index = $target.index(selector);

                if ($this.hasClass('imagelightbox-arrow-left')) {
                    index = index - 1;
                    if (!$(selector).eq(index).length)
                        index = $(selector).length;
                }
                else {
                    index = index + 1;
                    if (!$(selector).eq(index).length)
                        index = 0;
                }

                instance.switchImageLightbox(index);
                return false;
            });
        },
        arrowsOff = function () {
            $('.imagelightbox-arrow').remove();
        };
    function buildPaginator(tCount, pfrom, pto,pnum) {
        if(!pnum){
            pnum = 0;
        }
        pnum = parseInt(pnum);
        tCount= parseInt(tCount);
        pfrom = parseInt(pfrom);
        var htmlP = "";
        var pagesC = Math.ceil(tCount / pto);
        var max_page_value = (pagesC-1)*pto;
        var max_view = 15;
        var view_count = 0;
        if(pnum > 0){
            pnum--;
        }
        if((pagesC-pNum) < max_view && pNum < pagesC){
            var delta_value = pNum-(max_view - (pagesC-pNum));
            var delta_count = max_view;
            if((delta_value+1) != pNum){
                fromPageCount = (delta_value*pto);
                pnum = delta_value;
            }
        }
        var vNum = parseInt(fromPageCount);

        if (pagesC > 1) {
            if(pNum >= pagesC){
                htmlP = "<li class=\"active\"><a href=\"#\" onclick=\"return false;\">" + (pnum+1) + "</a></li>"+htmlP;
                for (var i = pnum; i < pagesC; i--) {
                    var cpNum = i;

                    vNum -= parseInt(pto);
                    view_count++;
                    if (cpNum == pNum) {

                    } else {
                        //cpNum = i-1;
                        htmlP = "<li ><a href=\"#\" onclick=\"loadData(" + vNum + "," + pto + ","+cpNum+");return false;\">" + cpNum + "</a></li>"+htmlP;
                    }
                    if(view_count >= max_view || view_count == pagesC){
                        break;
                    }
                }
                if(pNum > 1){
                    htmlP = "<li ><a href=\"#\" onclick=\"loadData(0,"+pto+",1);return false;\"><<<</a></li>"+htmlP;
                }
            }else{

                if(pNum > 1){
                    htmlP += "<li ><a href=\"#\" onclick=\"loadData(0,"+pto+",1);return false;\"><<<</a></li>";
                    var previus_data = ((pNum-2) > 0) ? (pNum-2)*pto : 0;
                    htmlP += "<li ><a href=\"#\" onclick=\"loadData("+previus_data+","+pto+","+(pNum-1)+");return false;\"><<</a></li>";
                }
                var cpNum = 0;
                for (var i = pnum; i < pagesC; i++) {
                    cpNum = i + 1;

                    view_count++;
                    if ((cpNum == pNum) || (pNum == 0 && cpNum == 1)) {
                        htmlP += "<li class=\"active\"><a href=\"#\" onclick=\"return false;\">" + cpNum + "</a></li>";
                    } else {
                        htmlP += "<li ><a href=\"#\" onclick=\"loadData(" + vNum + "," + parseInt(pto) + ","+cpNum+");return false;\">" + cpNum + "</a></li>";
                    }
                    vNum += parseInt(pto);
                    if(view_count >= max_view){
                        break;
                    }
                }
                if(pNum < pagesC){
                    var nex_data = parseInt(vNum+((cpNum+1)*parseInt(pto)));
                    htmlP += "<li ><a href=\"#\" onclick=\"loadData(" + nex_data + ","+parseInt(pto)+","+(cpNum+1)+");return false;\">>></a></li>";
                    htmlP += "<li ><a href=\"#\" onclick=\"loadData(" + max_page_value + ","+pto+","+pagesC+");return false;\">>>></a></li>";
                }
            }
        }
        $("#buildPages").html(htmlP);
        return vNum;
    }
    function loadData(v1, v2,pn) {
        fromP = v1;
        pNum = pn;
        fromPageCount = v1;
        filter(null);
    }
    if ($('[addon="rangedate"]')) {
        $('[addon="rangedate"]').each(function () {
            $(this).dateRangePicker({
                shortcuts: {
                    'prev-days': [3, 5, 7],
                    'prev': ['week', 'month', 'year'],
                    'next-days': null,
                    'next': null
                }
            }).bind('datepicker-apply', function () {
                filter(this, true);
            });
        });
    }
    if ($('[addon="date"]')) {
        $('[addon="date"]').datepicker({format: 'yyyy-mm-dd'}).on('changeDate', function () {
            filter(this, true);
        });
    }
    function totalResset() {
        $("input[type=text]").each(function () {
            $(this).val('');
        });
        $("select").each(function () {
            $(this).val('');
        });
        $('#slct_user').val('0');
        $('#slct_prd_type').val('flower');
        $("#showCount").val("50");
        data = {};
        <?php
        if(max(page::filterLevel(3, $levelArray)) < 33)
        {
        ?>
            data["orderF"] = {"filter": 12, "value": "`delivery_time` ASC"};
            data["adf"] = {"filter": 17, "value": "<?=date("Y-m-d");?>"};
        <?php
        }
        ?>
        toP = 50;//listi erkarutyun #2
        
        filter(null, this);
    }
    function sendMail() {
        var getMails = "";
        $("input:checkbox[id^='mailToSend']").each(function () {

            if ($(this).is(":checked")) {
                getMails += $(this).val() + ",";
            }
            if (!getMails) {
                $(this).prop("disabled", false);
            }
        });
        if (getMails) {
            window.open("mail/?mails=" + getMails, "", "toolbar=yes, scrollbars=yes, resizable=yes,width=800, height=400");
        }
    }
    function CheckAccounting(orderId) {
        window.open("products/?cmd=check&orderId=" + orderId, "", "toolbar=yes, scrollbars=yes, resizable=yes,width=1170, height=900");
    }
    function checkCountStockProducts(){
        var order_ids = Array();
        $('#dataTable tr').each( (ind, element) => {
            let r_id = $(element).attr('data-id');
            order_ids.push(r_id);
        })
        $.ajax({
            url: location.href,
            type: 'post',
            data: {
                countOfStockProducts: true,
                order_ids: order_ids
            },
            success: function(resp){
                var resp = JSON.parse(resp);
                var html = '';
                var totalPnetcost = 0;
                $.each(resp, function(index, value) {
                    totalPnetcost+= value.count*value.pnetcost;
                    html+= '<tr>';
                        html+= '<td>';
                            html+= value.name;
                        html+= '</td>';
                        html+= '<td>';
                            html+= value.count;
                        html+= '</td>';
                        html+= '<td>';
                            html+= (value.count*value.pnetcost);
                        html+= '</td>';
                    html+= '</tr>';
                });
                $(".count_stock_body").html(html);
                $(".totalPnetcostModal").html(totalPnetcost + " Դր․");
                $("#checkCountStockOfCurrentList").modal('show');
            }
        })
    }
    function createExcellForCurrentList(){
        var ordersArray = '';
        $('#dataTable tr').each(function(index, elem){
            var orderId = $(elem).attr('data-id');
            ordersArray += orderId + ',';
        })
        setTimeout(function(){
            window.open('download-excel.php?ordersArray=' + ordersArray, '_blank');
        },1500)

    }
    function openMail(id, type){
        window.open("mail/?mails="+id+"&content_id="+type, "", "toolbar=yes, scrollbars=yes, resizable=yes,width=1300, height=800");
    }
    function printBacik(orderId) {
        window.open("bacik.php?orderId=" + orderId, "", "toolbar=yes, scrollbars=yes, resizable=yes,width=970, height=600");
    }
    function onroad(id) {
        request_call('&id=' + id + '&delivery_status=6');
    }
    function product_ready(id) {
        request_call('&id=' + id + '&delivery_status=12');
    }
    function request_call(call_data) {
        $.get("ajax.php?update_order=true" + call_data, function (get_data) {
            if (get_data.status && get_data.status == "ok") {
                alert('Շնորհակալություն, գրանցվեց համակարգում,որ ապրանքը Պատրաստ է!');
                filter(null, true);
            }
        });
    }
    function selectAll(type) {
        $("input:checkbox[id^='mailToSend']").each(function () {

            if (type) {
                $(this).prop('checked', true);
            } else {
                $(this).prop('checked', false);
            }
        });
    }
    function checkAll(data) {
        if (data.checked) {
            selectAll(true);
        } else {
            selectAll();
        }
    }
    jQuery("[name=allfpf]").attr("disabled", "disabled");
    jQuery("[name=allfpf]").html("<option>---</option>");
    function hfilter(type) {
        var allFlPartners = <?=json_encode(getwayConnect::getwayData("SELECT `data_partners`.`sell_point_id` AS `value`,`delivery_sellpoint`.`name` FROM `data_partners` RIGHT JOIN `delivery_sellpoint` ON  `data_partners`.`sell_point_id` = `delivery_sellpoint`.`id` WHERE `data_partners`.`active` = 1 AND `data_partners`.`depend_on` = 'flower' ORDER BY `data_partners`.`ordering`", PDO::FETCH_ASSOC))?>;
        var allRTPartners = <?=json_encode(getwayConnect::getwayData("SELECT `data_partners`.`sell_point_id` AS `value`,`delivery_sellpoint`.`name` FROM `data_partners` RIGHT JOIN `delivery_sellpoint` ON  `data_partners`.`sell_point_id` = `delivery_sellpoint`.`id` WHERE `data_partners`.`active` = 1 AND `data_partners`.`depend_on` = 'travel' ORDER BY `data_partners`.`ordering`", PDO::FETCH_ASSOC))?>;
        var allOws = <?=json_encode(getwayConnect::getwayData("SELECT `data_partners`.`sell_point_id` AS `value`,`delivery_sellpoint`.`name` FROM `data_partners` RIGHT JOIN `delivery_sellpoint` ON  `data_partners`.`sell_point_id` = `delivery_sellpoint`.`id` WHERE `data_partners`.`active` = 1 AND `data_partners`.`depend_on` = 'ows' ORDER BY `data_partners`.`ordering`", PDO::FETCH_ASSOC))?>;
        var phtml = '<option value="">SELECT ONE</option>';
        jQuery("[name=allfpf]").removeAttr("disabled");
        if (type == "FLOWERS_PARTNERS") {
            for (var i = 0; i < allFlPartners.length; i++) {
                phtml += "<option value=\"" + allFlPartners[i].value + "\" >" + allFlPartners[i].name + "</option>";
            }
        } else if (type == "TRAVEL_PARTNERS") {
            for (var i = 0; i < allRTPartners.length; i++) {
                phtml += "<option value=\"" + allRTPartners[i].value + "\" >" + allRTPartners[i].name + "</option>";
            }
        } else if (type == "OTHER_WEBSITES") {
            for (var i = 0; i < allOws.length; i++) {
                phtml += "<option value=\"" + allOws[i].value + "\" >" + allOws[i].name + "</option>";
            }
        } else {
            jQuery("[name=allfpf]").attr("disabled", "disabled");
        }
        jQuery("[name=allfpf]").html(phtml);
        phtml = "";
    }
    function viewHidePrice() {
        $(".prices_list").toggle();
    }
    
    
    function highlight(text, object) {
        /*var term = text;
        term = term.replace(/(\s+)/, "(<[^>]+>)*$1(<[^>]+>)*");
        var pattern = new RegExp("(" + term + ")", "gi");

        object = object.replace(pattern, "<span style=\"background-color: yellow;color:black;font-size: 12px;\">$1</span>");
        object = object.replace(/(<span>[^<>]*)((<[^>]+>)+)([^<>]*<\/span>)/, "$1</span>$2<span style=\"background-color: yellow;color:black;font-size: 12px;\">$4");
        return object;*/

        // remove any old highlighted terms
        $('body').removeHighlight();

        // disable highlighting if empty
        if ( text ) {
            // highlight the new term
            $('body').highlight( text );
        }
    }
    jQuery.fn.highlight = function(pat) {
        function innerHighlight(node, pat) {
            var skip = 0;
            if (node.nodeType == 3) {
                var pos = node.data.toUpperCase().indexOf(pat);
                if (pos >= 0) {
                    var spannode = document.createElement('span');
                    spannode.className = 'highlight';
                    var middlebit = node.splitText(pos);
                    var endbit = middlebit.splitText(pat.length);
                    var middleclone = middlebit.cloneNode(true);
                    spannode.appendChild(middleclone);
                    middlebit.parentNode.replaceChild(spannode, middlebit);
                    skip = 1;
                }
            }
            else if (node.nodeType == 1 && node.childNodes && !/(script|style)/i.test(node.tagName)) {
                for (var i = 0; i < node.childNodes.length; ++i) {
                    i += innerHighlight(node.childNodes[i], pat);
                }
            }
            return skip;
        }
        return this.each(function() {
            innerHighlight(this, pat.toUpperCase());
        });
    };

    jQuery.fn.removeHighlight = function() {
        function newNormalize(node) {
            for (var i = 0, children = node.childNodes, nodeCount = children.length; i < nodeCount; i++) {
                var child = children[i];
                if (child.nodeType == 1) {
                    newNormalize(child);
                    continue;
                }
                if (child.nodeType != 3) { continue; }
                var next = child.nextSibling;
                if (next == null || next.nodeType != 3) { continue; }
                var combined_text = child.nodeValue + next.nodeValue;
                new_node = node.ownerDocument.createTextNode(combined_text);
                node.insertBefore(new_node, child);
                node.removeChild(child);
                node.removeChild(next);
                i--;
                nodeCount--;
            }
        }

        return this.find("span.highlight").each(function() {
            var thisParent = this.parentNode;
            thisParent.replaceChild(this.firstChild, this);
            newNormalize(thisParent);
        }).end();
    };
    $(document).on('click', '#printyfy .dropdown-menu', function (e) {
      e.stopPropagation();
    });
    function hide_on_print(obj,object_class){
        if(obj.is(':checked')){
            $("."+object_class).removeClass('hidden-print');
        }else{
            $("."+object_class).addClass('hidden-print');
        }
    }
        
        //// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
        function onclickSaveButtonPrimaryLanguage(idToSave){
            var delivery_primary_language_id = $( "#delivery_primary_language_" + idToSave ).val();
            var url = "<?=$rootF?>/data.php?cmd=updateprimarylanguage&id=" + idToSave 
                   + "&delivery_primary_language_id=" + delivery_primary_language_id;
            $("#PrimaryLanguageSaveButton_" + idToSave ).load( url, function(data) {
                if (data == "ok") {
                    $("#PrimaryLanguageSaveButton_" + idToSave ).text("Փոխվեց!");
                }
            });
        }
        //// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
        function onclickSaveButtonWhoReceived(ToSave){
            var who_received = $( "#who_received_" + ToSave ).val();
            var url = "<?=$rootF?>/data.php?cmd=updatewhoreceived&id=" + ToSave 
                   + "&who_received=" + who_received;
            $("#WhoReceivedSaveButton_" + ToSave ).load( url, function(data) {
                if (data == "ok") {
                    $("#WhoReceivedSaveButton_" + ToSave ).text("Փոխվեց!");
                }
            });
        }
        //// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
        function onclickSaveButtonStatus(idToSave){
            var delivery_status_id = $( "#delivery_status_" + idToSave).val();
            var url = "<?=$rootF?>/data.php?cmd=updatestatus&id=" + idToSave 
                   + "&delivery_status_id=" + delivery_status_id;
            $("#StatusSaveButton_" + idToSave ).load( url, function(data) {
                if (data == "ok") {
                    $("#StatusSaveButton_" + idToSave ).text("Փոխվեց!");
                }
            });
        }
        //// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
        function onclickSaveButtonDeliverReason(idToSave){
            var delivery_reason_id = $( "#delivery_reason_" + idToSave ).val();
            var url = "<?=$rootF?>/data.php?cmd=updatedeliveryreason&id=" + idToSave 
                   + "&delivery_reason_id=" + delivery_reason_id;
            $("#ReasonSaveButton_" + idToSave ).load( url, function(data) {
                if (data == "ok") {
                    $("#ReasonSaveButton_" + idToSave ).text("Փոխվեց!");
                }
            });
        }
        //// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
        function onclickSaveButton(idToSave){
         
                var driverId = $( "#driver_" + idToSave ).val();
                
                
                
                
                var step = 0;
                if ($( "#step_" + idToSave ).val() > 0) {
                   step  = $( "#step_" + idToSave ).val();
                }
              
                var stage = 0;
                if ( $( "#stage_" + idToSave ).val() > 0) {
                    stage = $( "#stage_" + idToSave ).val();
                }
                
                var quantity = 0;
                if ( $( "#quantity_" + idToSave).val() > 0) {
                    quantity =  $( "#quantity_" + idToSave).val();
                }
                
                var userid =<?=$userData[0]["id"]?>;
    
                var url = "<?=$rootF?>/data.php?cmd=updatedrive&id=" + idToSave 
                           + "&driverId=" + driverId 
                           + "&step="     + step 
                           + "&stage="    + stage 
                           + "&quantity=" + quantity 
                           + "&userid="   + userid;
    
               
                  $("#clickbutton_" + idToSave ).load( url, function(data) {
                      
                      
                      if (data == "ok") {
                          $("#drvnameimg_" + idToSave).attr("src", " ../../template/icons/drivers/" + driverId + ".png")
                          $("#carImage_" + idToSave).attr("src", "../../template/icons/deliver/" + driverId + ".png")
                          $("#clickbutton_" + idToSave ).text("Փոխվեց!");
                      }
                    
                    
                  });
                 
                          
    }  
        
    $('body').on('click', '.displayAdditionalPrice', function(){
        var delivery_price_id = $(this).attr('data-delivery-price');
        var order_id = $(this).attr('data-order-id');
        $.ajax({
            url: location.href,
            type: 'post',
            data: {
                getAdditionalPrice: true,
                order_id: order_id,
                delivery_price_id: delivery_price_id
            },
            success: function(resp){
                if(resp.length > 30){
                    resp = JSON.parse(resp);
                    if(resp){
                        var html = '';
                        var additionalPriceArray = resp.additionalPrice;
                        var deliveryPriceArray = resp.delivery_price;
                        for(var i = 0 ; i < additionalPriceArray.length;i++){
                            html+= additionalPriceArray[i]['price'] + " Դր․<br>";
                        }
                        for(var i = 0 ; i < deliveryPriceArray.length;i++){
                            html+= deliveryPriceArray[i].name + " Դր․<br>";
                        }
                        $(".additionalPriceDisplay_" + order_id).html(html);
                    }
                }
            }
        })
    })
    $('body').on('click', '.changeFlourist', function(){
        
        var d = new Date();
        if($(this).siblings('.flourist_change_day').val() != d.getDate()){
            $(this).siblings('.flourist_change_day').css('border', '1px solid red');
            alert('Todays date is wrong!');
        } else {
            var flourist = $(this).siblings('.new_flourist').val();
            var flourist_name = $(this).siblings('.new_flourist').children(':selected').text();
            var $self = $(this);
            var data = {
                changeFlourist: 'changeFlourist',
                order_id: $(this).attr('data-order'),
                flourist: flourist
            };
            $(this).siblings('.flourist_change_day').css('border', 'none');
            $.post("./ajax.php", {data: data}, function (response) {
                $self.closest('tr').find('.flourist_name').html(flourist_name);
            })
        }        

    })
    var orders_page_checklate_ajax = $(".orders_page_checklate_ajax").val();
    if(orders_page_checklate_ajax == 1){
        console.log('orders_page_checklate_ajax');
        let int = setInterval(() => {
        console.log('checkLate - Ajax Request - Worked');
            $('#dataTable tr.robot').each( (ind, element) => {
                let r_id = $(element).attr('data-id');
                $.ajax({
                    url: location.href,
                    type: 'post',
                    data: {
                        checkLate: true,
                        l_id: r_id
                    },
                    success: function(resp){
                        if(resp != undefined){
                            let l_data = JSON.parse(resp);
                            if(l_data.conf_late){
                                $(element).find('.mailButton').eq(0).before('<img src="./ico/fire.png" title="Չափազանց ուշացված" class="fireImg">');
                                $(element).find('.mailButton').eq(0).css('border', '2px solid red');
                            }
                            if(l_data.del_late){
                                $(element).find('.mailButton').eq(4).before('<img src="./ico/fire.png" title="Չափազանց ուշացված" class="fireImg">');
                                $(element).find('.mailButton').eq(4).css('border', '2px solid red');
                            }
                        }
                    }
                })
            })
        }, 30000);
    }

    var ajax_check_confirmation_mail = $(".ajax_check_confirmation_mail").val();
    if(ajax_check_confirmation_mail == 1){
        // console.log('Check Confirmation E-mail - Ajax Request - Worked');
        // let int = setInterval(() => {
        //     $('#dataTable tr').each( (ind, element) => {
        //         let r_id = $(element).attr('data-id');
        //         $.ajax({
        //             url: location.href,
        //             type: 'post',
        //             data: {
        //                 checkConfirmationEmailNotification: true,
        //                 order_id: r_id
        //             },
        //             success: function(resp){
        //                 resp = JSON.parse(resp);
        //                 if(resp.response === false){
        //                      var r = confirm("Հարգելի աշխատակից " + resp.order_id + " համարի պատվերը հաստատված է, սակայն հաստատման ծանուցումը ուղարկված չէ");
        //                       if (r == true) {
        //                         setTimeout(function(){
        //                             openMail(resp.order_id,2)
        //                         },1000)
        //                       }
        //                 }
        //             }
        //         })
        //     })
        // }, 840000);
    }
    
    // Added By Hrach  08/12/19
    let pending = setInterval(() => {
        $.ajax({
            url: location.href,
            type: 'post',
            data: {
                checkpending: true,
            },
            success: function(resp){
            }
        })
    }, 50000);
    if($(".show_alert_notification_operators1").val() == 1){
        console.log('SHOW ALERT NOTIFICATION WORKING!')
        let showalert = setInterval(() => {
            $.ajax({
                url: location.href,
                type: 'post',
                data: {
                    showalertnotification: true,
                },
                success: function(resp){
                    console.log(resp)
                    if( resp.length > 2 ){
                        resp = JSON.parse(resp);
                        var orderstext = '';
                        for( var i = 0 ; i < resp.orders.length ; i++ ){
                            orderstext+=resp.orders[i] + ","
                        }
                        orderstext = orderstext.slice(0,-1)
                        alert('Հարգելի ' + resp.operator_name + ", Դուք ունեք " + orderstext + " համարի պատվերներ , որոնցով պարտադիր պետք է զբաղվել !");
                        $(".showAlertMessageOperator").html(orderstext);
                    }
                }
            })
        }, 59000);
    }
    // end val123456789
    if($(".ajax_check_anavart_operators_mail").val() == 1){
        if($(".sendRequestAnavartCheck").val() == 1){
            console.log('Anavart Check')
            let showalert = setInterval(() => {
                $.ajax({
                    url: location.href,
                    type: 'post',
                    data: {
                        checkAnavartLikeCron: true,
                    },
                    success: function(resp){
                        if(resp.length > 10){
                            alert(resp);
                        }
                    }
                })
            }, 3600000);
        }

    }
    // end val123456789

        
    $('body').on('click', '.product', function(){
        let id = $(this).attr('data-id');
        let img = $(this).attr('data-img');
        let name = $(this).attr('data-name');
        let price = $(this).attr('data-price');
        let int_price = $(this).attr('data-int-price');
        let arm_price = $(this).attr('data-arm-price');
        let int_price_show = '';
        let arm_price_show = '';
        if(int_price > 0){
            int_price_show = "$ " + int_price ;
        }
        if(arm_price > 0){
            arm_price_show = arm_price + " դրամ " ;
        }
        $('.productInfo').html('');
        let html = "<div class='selectedProduct'>";
        html += "<img src='"+img+"' alt='"+name+"' height='250px;'>";
        html += "<span class='productName'>"+name+"</span>";
        html += "<span>  &nbsp;&nbsp; " + int_price_show + " " + arm_price_show + "</span>";
        html += "</div>";
        $('.productInfo').append(html);
        $('.productInfo').css('display', 'inline-block');
        $('.table').css('filter', 'blur(15px)');
        $.get("<?=$_SERVER['PHP_SELF']?>?product_id="+id, function(response){
            let results = JSON.parse(response)
            if(results.length > 0 ){
                results.forEach( result => {
                    let productHtml = '<div class="relatedProduct">';
                    productHtml += '<img class="relatedProductImage" src="./jos_product_images/'+result.product_thumb_image+'" title="'+result.product_s_desc+' - Description in Websites: ('+result.product_desc+')">';
                    productHtml += '</br>';
                    
                    <?php  if(max(page::filterLevel(3, $levelArray)) > 33){ ?>
                    productHtml += '<span class="relatedProductPrice" style="position: unset;" ><a href="http://10.0.0.65/flowers-armenia/index.php?page=shop.product_details&flypage=flypage.tpl&product_id='+result.product_id+'&option=com_virtuemart" target="_blank">'+result.product_sku+'</a> - '+parseFloat(result.product_price).toFixed(2)+ " " + result.product_currency+ '</span>';
                    productHtml += '</br>';
                    
                    let dprice = '';
                    if(result['product_discount_id'] > 0){
                        dprice = (Number(result['product_price']) - Number(result['product_price']) * (Number(result['product_discount_id'])/100)).toFixed(0);
                    } 
                     
                    if(result['product_discount_id'] > 0){
                    productHtml += '<span class="discounted_price">(On sale now! -'+result.product_discount_id+'% - $<b>'+dprice+'</b>)</span>';
                    productHtml += '</br>';
                    } 
                    
                    if(result['product_publish'] != 'Y'){
                        productHtml += '<button data-dz-remove="" class="btn btn-danger btn-xs unpublishedProd"><i class="glyphicon glyphicon-remove"></i></button>';
                    }
                    productHtml += '<span class="relatedProductName">'+result.product_name+'</span>';
                    productHtml += '<br>';
                    productHtml += '<button class="link-button" data-href="https://www.flowers-armenia.com/index.php?page=shop.product_details&category_id=1&flypage=flypage.tpl&product_id='+result.product_id+'&option=com_virtuemart" title="Flowers-Armenia.com">F-A.com</button>';
                    productHtml += '<button class="link-button" data-href="http://flowers-armenia.am/index.php?page=shop.product_details&flypage=caxikneri-araqum.tpl&product_id='+result.product_id+'&category_id=1&option=com_virtuemart" title="Flowers-Armenia.am">F-A.am</button>';
                    productHtml += '<button class="link-button" data-href="http://www.flowers-barcelona.com/index.php?page=shop.product_details&flypage=floristería-enviar-flores.tpl&product_id='+result.product_id+'&category_id=1&option=com_virtuemart" title="Flowers-Barcelona.com">FB</button>';
                    
                    <?php  } ?>
                    
                    productHtml += '</div>';
                    $('.productInfo').append(productHtml)
                });
            }
        })
    });
    // Added By Hrach
    $(document).on('click',".button_prod_prepair_note",function(){
        var prod_id = $(this).data('prod-id');
        var order_id = $(this).data('order-id');
        var val = $(".prod_prepair_note_" + order_id + "_" +prod_id).val();
        $.ajax({
            type: 'post',
            url: location.href,
            data: {
                insert_prod_prepair_note: prod_id,
                val: val
            },
            success: function(resp){
                console.log(resp)
                alert('Շնորհակալություն');
            }
        })
    })

    $(document).on('click',".img_for_stock_default_prods",function(){
        var prod_id = $(this).data('prod-id');
        var order_id = $(this).data('order-id');
        // if(loggedUserLevel)
        $.ajax({
            type: 'post',
            url: location.href,
            data: {
                get_stock_default_prods: prod_id,
                order_id: order_id,
            },
            success: function(resp){
                resp = JSON.parse(resp);
                if ( resp.length > 0 ){
                    $(".div_for_stock_default_prods_" + order_id + "_" +prod_id).slideToggle(50)
                    var html = '';
                    for( var i = 0 ; i < resp.length ; i++ ){
                console.log(resp[i]['product_name'],order_id);
                        html += "<b>✓ " + resp[i]['product_name'] + " - " + resp[i]['count'] + " հատ </b><br>";
                    }
                    $(".div_for_stock_default_prods_" + order_id + "_" +prod_id).empty();
                    $(".div_for_stock_default_prods_" + order_id + "_" +prod_id).append(html);
                }
                else{
                    $(".div_for_stock_default_prods_" + order_id + "_" +prod_id).empty();
                    $(".div_for_stock_default_prods_" + order_id + "_" +prod_id).append('<b>Ելք Արված չէ!</b>');
                    $(".div_for_stock_default_prods_" + order_id + "_" +prod_id).slideToggle(50)
                }
            }
        })
    })
    $(document).on('click',".img_for_stock_prods",function(){
        var prod_id = $(this).data('prod-id');
        var order_id = $(this).data('order-id');
        // if(loggedUserLevel)
            $.ajax({
                type: 'post',
                url: location.href,
                data: {
                    get_stock_prods: prod_id
                },
                success: function(resp){
                    resp = JSON.parse(resp);
                    if ( resp.length > 0 ){
                        $(".div_for_stock_prods_" + order_id + "_" +prod_id).slideToggle(50)
                        $(".div_for_stock_prods_" + order_id + "_" +prod_id).parent().find('.productDesc').slideToggle(50)
                        var html = '';
                        for( var i = 0 ; i < resp.length ; i++ ){
                            html += "<b>✓ " + resp[i]['product_name'] + " - " + resp[i]['count'] + " հատ </b><br>";
                        }
                        $(".div_for_stock_prods_" + order_id + "_" +prod_id).empty();
                        $(".div_for_stock_prods_" + order_id + "_" +prod_id).append(html);
                    }
                    else{
                        $(".div_for_stock_prods_" + order_id + "_" +prod_id).empty();
                        $(".div_for_stock_prods_" + order_id + "_" +prod_id).append('<b>Բաղադրություն չգտնվեց</b>');
                        $(".div_for_stock_prods_" + order_id + "_" +prod_id).slideToggle(50)
                        $(".div_for_stock_prods_" + order_id + "_" +prod_id).parent().find('.productDesc').slideToggle(50)
                    }
                }
            })
            $.ajax({
                type: 'post',
                url: location.href,
                data: {
                    get_prepair_note_for_prod: prod_id
                },
                success:function(resp){
                    resp = JSON.parse(resp);
                    if ( resp.length > 0 ){
                        $(".div_for_append_input_prepair_note_" + order_id + "_" +prod_id).slideToggle(50)
                        $(".div_for_append_input_prepair_note_" + order_id + "_" +prod_id).empty();
                        if(floristLogin){
                            $(".div_for_append_input_prepair_note_" + order_id + "_" +prod_id).append('<input class="prod_prepair_note_' + order_id + "_" + prod_id + '" type="text" value="' + resp[0]['prepair_note'] + '" style="width:230px;margin-left:5px" placeholder="Լրացրեք եթե օգտագործվել է այլ քանակ"><button class="button_prod_prepair_note" data-order-id="' + order_id + '" data-prod-id="' + prod_id + '">✓</button>');
                        }
                        else{
                            $(".div_for_append_input_prepair_note_" + order_id + "_" +prod_id).append("<span class='color_red'>" + resp[0]['prepair_note'] + "</span>");
                        }
                    }
                    else{
                        $(".div_for_append_input_prepair_note_" + order_id + "_" +prod_id).empty();
                        if(floristLogin){
                            $(".div_for_append_input_prepair_note_" + order_id + "_" +prod_id).append('<input class="prod_prepair_note_' + order_id + "_" +prod_id + '" type="text" placeholder="Լրացրեք եթե օգտագործվել է այլ քանակ" style="width:230px;margin-left:5px"><button class="button_prod_prepair_note" data-prod-id="' + prod_id + '" data-order-id="' + order_id + '">✓</button>');
                        }
                        else{
                            $(".div_for_append_input_prepair_note_" + order_id + "_" +prod_id).append("<span class='color_red'></span>");
                        }
                        $(".div_for_append_input_prepair_note_" + order_id + "_" +prod_id).slideToggle(50)
                    }
                }
            })
    })
    $('body').on('click', '.showChoosenRelated', function(){
        let id = $(this).attr('data-id');
        let $self = $(this);
        if($(this).attr('data-clicked') == 0){
            $('a[data-imagelightbox="'+id+'"]').remove();
            $self.closest('td').find('.related_images').html('');
            $self.closest('td').find('.out_images').html('');
            $self.closest('td').find('.product_images').html('');
            $self.attr('data-clicked', 1);
            $.get("<?=$rootF?>/data.php?cmd=related_images&itemId=" + id, function (get_data) {
                if (!$('a[data-imagelightbox="' + id + '"]').length) {
                    if (get_data.data.related_images) {
                        var imc = get_data.data.related_images;
                        var path = "jos_product_images/";
                        for (var u = 0; u < imc.length; u++) {
                            console.log(imc[u]);
                            $('body').append('<a href="'+path + imc[u].image_source + '" data-imagelightbox="' + id + '" style="display:none"><img src="'+path+ + imc[u].image_source + '" alt="' + imc[u].image_note + '"></a>');
                            let relatedHtml = '<div class="col-sm-12" style="clear: both;border-bottom:1px solid black;margin-bottom:5px">';
                            if(imc[u].for_purchase){
                                relatedHtml += '<div class="imgDiv" style="border:2px solid red;padding:2px">';
                            }
                            else{
                                relatedHtml += '<div class="imgDiv">';
                            }
                            relatedHtml += '<img onclick="zoom_img('+id+')" src="'+path + imc[u].image_source +'" alt="' + imc[u].image_note + '" style="width: auto; max-width:100px; height: 90px; float: left;">';
                            if(imc[u].product_width > 0){
                                relatedHtml += '<a href="/account/flower_orders/jos_product_images/bigimages/' + imc[u].image_full_source + '" target="_blank"><div class="w-size">'+parseFloat(imc[u].product_width).toFixed(2)+'</div></a>';
                            }
                            if(imc[u].product_height > 0){
                                relatedHtml += '<a href="/account/flower_orders/jos_product_images/bigimages/' + imc[u].image_full_source + '" target="_blank"><div class="h-size">'+parseFloat(imc[u].product_height).toFixed(2)+'</div></a>';
                            }
                            var prod_descrip = imc[u].short_desc.toLowerCase();
                            var sample_show = false;
                            if (prod_descrip.indexOf("նմուշ") >= 0){
                                sample_show = true;
                            }
                            relatedHtml += '</div>';
                            relatedHtml += '<span class="relatedProductTitle div_cucanish_for_' + id + "_"  + imc[u].related_id + '">'+ imc[u].changed_name;
                            <?php
                                if(max(page::filterLevel(3, $levelArray)) >= 33)
                                {
                            ?>
                                    relatedHtml += " ($"+Number(imc[u].price).toFixed(2)+")";
                            <?php
                                } 
                            ?>
                            if(sample_show){
                                relatedHtml += "<img src='../../template/icons/sample.gif' style='height:63px;float:right;margin-left:7px;margin-bottom:10px'>";
                            }
                            relatedHtml += '</span>';
                            <?php
                                if((max(page::filterLevel(3, $levelArray)) < 33 && !in_array($userData[0]['id'], array(88,89,90,91,92,93,94,95,96,97,98,99,100,101,102))) || in_array($userData[0]['id'], array(4, 87, 35)))
                                {
                            ?>
                                if($self.attr('data-status') == "1" || $self.attr('data-status') == "12"){
                                    <?php
                                    if(!in_array($userData[0]['id'], $simple_florist_ids)){
                                        ?>
                                            relatedHtml += '<label>Պատրաստ ';
                                            relatedHtml += '<input type="checkbox" class="productRelatedReady" data-order-id="'+imc[u].id+'" data-related-id="'+imc[u].related_id+'"';
                                            if(imc[u].related_ready){
                                                relatedHtml += 'checked';
                                                
                                            }
                                            relatedHtml += '>';
                                            relatedHtml += '</label>';
                                    
                                            relatedHtml += '<div class="forPurchaseDiv">';

                                            <?php
                                                if($userData[0]['id'] == '27'){
                                                    ?>
                                                        relatedHtml += '<label>Գնման ենթակա  ';
                                                        var checked_for_purchase = '';
                                                        if(imc[u].for_purchase == 1){
                                                            checked_for_purchase = 'checked';
                                                        }
                                                        relatedHtml += '<input type="checkbox" ' + checked_for_purchase + ' class="forPurchase" data-id="'+imc[u].order_related_id+'" data-type="1" data-user="<?= $userData[0]['id']?>"';
                                                        relatedHtml += '>';
                                                        relatedHtml += '</label>';
                                                    <?php
                                                }
                                                else{
                                                    ?>
                                                        if(imc[u].for_purchase == 3){
                                                            relatedHtml+="<span style='color:red;'>Գնված! "
                                                        }
                                                        else{
                                                            relatedHtml += '<label>Գնման ենթակա  ';
                                                            var checked_for_purchase = '';

                                                            if(imc[u].for_purchase == 1){
                                                                checked_for_purchase = 'checked';
                                                            }
                                                            relatedHtml += '<input type="checkbox" ' + checked_for_purchase + ' class="forPurchase" data-id="'+imc[u].order_related_id+'" data-type="1" data-user="<?= $userData[0]['id']?>"';
                                                            if(imc[u].for_purchase){
                                                                 <?php
                                                                    if($userData[0]['id'] != 27){
                                                                        ?>
                                                                        relatedHtml += 'checked disabled';
                                                                        <?php
                                                                    }
                                                                    else{
                                                                        ?>
                                                                        relatedHtml += 'checked';   
                                                                        <?php
                                                                    }
                                                                ?>
                                                            }
                                                            relatedHtml += '>';
                                                            relatedHtml += '</label>';
                                                        }
                                                    <?php
                                                }
                                            ?>
                                            
                                            <?php
                                            }
                                            ?>

                                    //         relatedHtml += '<input type="checkbox" class="forPurchase" data-id="'+imc[u].order_related_id+'" data-type="1" data-user="<?= $userData[0]['id']?>"';
                                    //         if(imc[u].for_purchase){
                                    //              <?php
                                    //                 if($userData[0]['id'] != 27){
                                    //                     ?>
                                    //                     relatedHtml += 'checked disabled';
                                    //                     <?php
                                    //                 }
                                    //                 else{
                                    //                     ?>
                                    //                     relatedHtml += 'checked';   
                                    //                     <?php
                                    //                 }
                                    //             ?>
                                    //         }
                                    //         relatedHtml += '>';
                                    //         relatedHtml += '</label>';
                                    // <?php
                                    //     }
                                    // ?>
                                    
                                    relatedHtml += '<span class="who_requested">';
                                    if(imc[u].who_requested != '' && imc[u].who_requested != null){
                                        relatedHtml += imc[u].who_requested;
                                    }
                                    relatedHtml += '</span>';
                                    relatedHtml += '</div>';
                                }
                            <?php
                                } 
                            ?>
                            relatedHtml += '<br>';
                            relatedHtml += "<div class='col-md-12'><img src='../../template/icons/baxadrutyun.jpg' class='img_for_stock_prods' data-prod-id='" + imc[u].related_id + "' data-order-id='" + imc[u].id + "' style='height:30px;margin-left:7px;margin-bottom:10px;float:left' ><img src='../../template/icons/green-icon1.png' class='img_for_stock_default_prods' data-prod-id='" + imc[u].related_id + "' data-order-id='" + imc[u].id + "' style='height:30px;margin-left:7px;margin-bottom:10px;float:left' ><div style='display:none;float:left;margin-top:5px;margin-left:7px' class='div_for_append_input_prepair_note_"  + imc[u].id + "_" +  + imc[u].related_id + "'></div></div>" + '<div  style="display:none" class="div_for_stock_prods_' + imc[u].id + '_' + imc[u].related_id + ' col-md-12"></div><div class="col-md-12"><span class="productDesc" style="color:blue;display:none">'+imc[u].short_desc+"</span></div><hr>";
                            relatedHtml += "<div class='col-md-12'></div>" + '<div  style="display:none;color:blue" class="div_for_stock_default_prods_' + imc[u].id + '_' + imc[u].related_id + ' col-md-12"></div>';
                            relatedHtml += '</div>';
                            $self.closest('td').find('.related_images').append(relatedHtml);
                        }
                    }
                    if (get_data.data.images) {
                        var imd = get_data.data.images;
                        for (var u = 0; u < imd.images.length; u++) {
                            console.log(imd.images[u]);
                            $('body').append('<a href="product_images/' + imd.images[u].image_source + '" data-imagelightbox="' + id + '" style="display:none"><img src="product_images/' + imd.images[u].image_source + '" alt="' + imd.images[u].image_note + '"></a>');
                            otherProductHtml = '<div class="col-sm-12">';
                            var created_date = get_data.data.order_created_date.split('-');
                            var year_short = created_date[0].substring(created_date[0].length-2)
                            var img_url_path = created_date[1] + "-" + year_short ;
                            otherProductHtml += '<img onclick="zoom_img('+id+')" src="product_images/' + img_url_path + '/' +imd.images[u].image_source +'" style="width: auto; max-width:100px; height: 90px; float: left;">';
                            otherProductHtml += '<span>'+imd.images[u].image_note+'</span>';
                            <?php
                                if(max(page::filterLevel(3, $levelArray)) >= 33)
                                {
                                    ?>
                                    otherProductHtml += " ($"+Number(imd.images[u].price).toFixed(2)+")";
                            <?php
                                } 
                            ?>
                            
                            <?php
                                if((max(page::filterLevel(3, $levelArray)) < 33 && !in_array($userData[0]['id'], array(88,89,90,91,92,93,94,95,96,97,98,99,100,101,102))) || in_array($userData[0]['id'], array(4, 87, 35)))
                                {
                            ?>
                                if($self.attr('data-status') == "1" || $self.attr('data-status') == "12"){
                                    otherProductHtml += '<label>Պատրաստ ';
                                    otherProductHtml += '<input type="checkbox" class="productDeliveryReady" data-id="'+imd.images[u].id+'" data-order-id="'+imd.images[u].rg_order_id+'"';
                                    if(imd.images[u].ready){
                                        otherProductHtml += 'checked';
                                    }
                                    otherProductHtml += '>';
                                    otherProductHtml += '</label>';
                                    otherProductHtml += '<div class="forPurchaseDiv">';
                                    otherProductHtml += '<label>Գնման ենթակա  ';
                                    otherProductHtml += '<input type="checkbox" class="forPurchase" data-id="'+imd.images[u].id+'" data-type="2" data-user="<?= $userData[0]['id']?>"';
                                    if(imd.images[u].for_purchase){
                                         <?php
                                            if($userData[0]['id'] != 27){
                                                ?>
                                                otherProductHtml += 'checked disabled';
                                                <?php
                                            }
                                            else{
                                                ?>
                                                otherProductHtml += 'checked';
                                                <?php
                                            }
                                        ?>
                                    }
                                    otherProductHtml += '>';
                                    otherProductHtml += '</label>';
                                    otherProductHtml += '<span class="who_requested">';
                                    if(imd.images[u].username != '' && imd.images[u].username != null){
                                        otherProductHtml += imd.images[u].username;
                                    }
                                    otherProductHtml += '</span>';
                                    otherProductHtml += '</div>';
                                }
                            <?php
                                } 
                            ?>
                            otherProductHtml += '<br>';
                            otherProductHtml += '<span class="productDesc">'+imd.images[u].product_desc+'</span></div>';
                            $self.closest('td').find('.product_images').append(otherProductHtml);
                        }
                    }                    
                }
                if (get_data.data.out_images) {
                    if (!$('a[data-imagelightbox="out' + id + '"]').length) {
                        var imd = get_data.data.out_images;
                        var path = "product_out_images/";
                        for (var u = 0; u < imd.length; u++) {
                            $('body').append('<a href="'+path + imd[u].fileName + '" data-imagelightbox="out' + id + '" style="display:none"><img src="'+path+ + imd[u].fileName + '"></a>');
                            $self.closest('td').find('.out_images').append('<div class="col-sm-3 outimg"><img onclick="zoom_out('+id+')" src="'+path + imd[u].fileName +'" style="width: auto; max-width:130px; height: 90px;"></div>');
                        }
                    }                        
                }
                
            });
        } else {
            $(this).attr('data-clicked', 0);
            $self.closest('td').find('.related_images').html('');
            $self.closest('td').find('.out_images').html('');
            $self.closest('td').find('.product_images').html('');
            $('a[data-imagelightbox="'+id+'"]').remove();
        }
    });

    $(document).mouseup(function(e) 
    {
        var container = $(".productInfo");
        // if the target of the click isn't the container nor a descendant of the container
        if (!container.is(e.target) && container.has(e.target).length === 0) 
        {
            container.css('display', 'none');
            $('.table').css('filter', 'blur(0)');
        }
    });

    $('body').on('click', '.link-button', function(){
        let link = $(this).attr('data-href');
        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val(link).select();
        document.execCommand("copy");
        $temp.remove();
        alert('Link Copied!');
    });

    $('body').on('click', '.peopleIcon', function(){
        $self = $(this);
        if($self.attr('data-clicked') == 0){
            $.ajax({
                type: 'post',
                url: location.href,
                data: {
                    getPeopleTasks: true
                },
                success: function(resp){
                    if(resp != undefined){
                        // let ord_counts = JSON.parse(resp);
                        // if(ord_counts.length > 0){
                        //     let or_html = '';
                        //     ord_counts.forEach(ord_count => {
                        //         if(ord_count['ord_count'] > 0){
                        //             or_html += '<span class="peopleImagesSpan">';
                        //             or_html += '<img class="peopleImages displayDisadvantages" data-user-id="' + ord_count['id'] + '" src="../user_images/'+ord_count['us_id']+'.jpg" title="'+ord_count['username']+'">';
                        //             or_html += ' '+ord_count['ord_count'];
                        //             or_html += '</span>';
                        //         }
                        //     });
                        //     $('.peopleDiv').html(or_html);
                        // }
                        var users = JSON.parse(resp);
                        var userHtmlTop = '';
                        for(var i = 0;i<users.length;i++){
                            userHtmlTop+= '<span class="peopleImagesSpan">';
                            userHtmlTop += '<img title="' + users[i]['full_name_am'] + '" data-user-level="' + users[i]['user_level'] + '" class="peopleImages displayDisadvantages hover_cursor_pointer" data-user-id="' + users[i]['id'] + '" src="../user_images/'+users[i]['uid']+'.jpg">';
                            userHtmlTop += '</span>';
                        }
                        $('.peopleDiv').html(userHtmlTop);
                        console.log(users);
                    }
                    $self.attr('data-clicked', 1);
                }
            })
        } else {
            $('.peopleDiv').html('');
            $self.attr('data-clicked', 0);
        }
    })
    function replaceDatetimeFormat(datetime){
        if(datetime != null){
            var arr = datetime.split(" ");
            var mycDate = arr[0].split("-");
            return mycDate[2] + "-" + monthNames[mycDate[1] - 1] + "-" + mycDate[0];
        }
        return datetime;
    }
    $(document).on('click','.displayDisadvantages',function(){
        var shifts = Array('day','evening','night');
        setTimeout(function(){
            $(".activeTabEach").click();
        },500)
        var total_maluses = 0;
        for(i = 0;i<shifts.length;i++){
            $(".payed_" + shifts[i] + "_shift_procent").html('');
            $(".communication_orders_" + shifts[i] + "_procent").html('');
            $(".bac_toxnvac_orders_" + shifts[i] + "_procent").html('');
            $(".chexyal_orders_" + shifts[i] + "_procent").html('');
            $(".total_procent_for_" + shifts[i]).html('');
        }
        $(".shift_days_dropdown").val($(".shift_days_dropdown option:first").val());
        var dateRangedate = $("input[addon*='rangedate']").val();
        var user_id = $(this).attr('data-user-id');
        var logedUser = <?php echo $userData[0]['id'] ?>;
        var managerial_sent = 0;
        var managerial_array = [4,38,86];
        if (managerial_array.indexOf(parseInt(user_id)) > -1) {
        	managerial_sent = 1;
        	$(".managerial_tab").css({'display':'block'});
        }
        else{
        	$(".managerial_tab").css({'display':'none'});
        }
        $(".statistic_tab").attr('data-user-id',user_id);
        if(parseInt(user_id) == logedUser || logedUser == 4 || logedUser == 38){
            $('#disadvantage_modal').modal('show');
            $(".disadvantage_table_body").empty();
            $.ajax({
                url: location.href,
                type: 'post',
                data: {
                    getPeopleDisadvantages: true,
                    user_id: user_id,
                    dateRangedate: dateRangedate,
                },
                success: function(resp){
                    resp = JSON.parse(resp);
                    var total_malus = 0;
                    var html = '';
                    for(var i = 0 ; i < resp.disadvantages.length ; i++ ){
                        var created_date = resp.disadvantages[i].created_date.split(' ');
                        var userAddInfo = resp.disadvantages[i].addedUserInfo;
                        total_malus+= resp.disadvantages[i].malus;
                        html+="<tr>";
                                html+="<td>";
                                    if(resp.disadvantages[i].order_id == 1){
                                        html+= resp.disadvantages[i].id;
                                    }
                                    else{
                                        html+= "<a target='_blank' href='https://new.regard-group.ru/account/flower_orders/order.php?orderId=" + resp.disadvantages[i].order_id + "'>" + resp.disadvantages[i].order_id + "</a>";
                                    }
                                html+="</td>";
                                html+="<td>";
                                    html+=resp.disadvantages[i].list_title
                                html+="</td>";
                                html+="<td>";
                                    html+= "<span class='edit_disadvantage_description_" + resp.disadvantages[i].id + "' data-desc='" + resp.disadvantages[i].description + "'>" + resp.disadvantages[i].description + "</span>";
                                    if(resp.disadvantages[i].file_path){
                                        html+= '<br><a href="https://new.regard-group.ru/disadvantage_files/' + resp.disadvantages[i].file_path + '" target="_blank"><img src="../../images/file.png" style="height:35px"></a>';
                                    }
                                html+="</td>";
                                html+="<td>";
                                    total_maluses = total_maluses + resp.disadvantages[i].malus;
                                    html+= '<span class="malusCount">' + resp.disadvantages[i].malus + '</span>';
                                html+="</td>";
                                html+="<td>";
                                    html+=replaceDatetimeFormat(created_date[0]) + " " + created_date[1];
                                    if(userAddInfo.full_name_am != ''){
                                        html+="<br>" + resp.disadvantages[i].addedUserInfo.full_name_am
                                    }
                                    else{
                                        html+="<br>" + resp.disadvantages[i].addedUserInfo.username
                                    }
                                     <?php
                                        if($userData[0]['id'] == 4){
                                        ?>
                                        html+='<br><i data-row-id="' + resp.disadvantages[i].id + '" data-order-id="' + resp.disadvantages[i].order_id + '" title="Ջնջել" class="glyphicon glyphicon-remove remove_disadvantage_item hover_cursor_pointer text-primary"></i>';
                                        html+='<i data-row-id="' + resp.disadvantages[i].id + '" title="Ջնջել" class="glyphicon glyphicon-edit edit_disadvantage_item hover_cursor_pointer text-primary" style="margin-left:7px"></i>';
                                    <?php
                                        }
                                    ?>

                                html+="</td>";
                            html+="</tr>";
                    }
                    $(".selectedDateDisplayPart").html(replaceDatetimeFormat(resp.from_date) + " - " + replaceDatetimeFormat(resp.to_date));
                    if(resp.selectedUserInfo.full_name_am != ''){
                        $(".full_name_selected_user").html(resp.selectedUserInfo.full_name_am);
                    }
                    else{
                        $(".full_name_selected_user").html(resp.selectedUserInfo.username);
                    }
                    if(resp.disadvantages.length){
                        totalCountDisadvantageMalus();
                        html+= '<tr>';
                            html+= '<td colspan="4">';
                                html+= 'Ընդհանուր մալուս՝'
                            html+= '</td>';
                            html+= '<td class="totalMalusValue">';
                                html+= '';
                            html+= '</td>';
                            html+= '<td>';
                            html+= '</td>';
                        html+= '</tr>';
                        $(".disadvantage_table_body").html(html);
                    }
                }
            })
			if(managerial_sent == 1){
				$.ajax({
	                url: location.href,
	                type: 'post',
	                data: {
	                    getMyselfPeopleDisadvantages: true,
	                    user_id: user_id,
	                    dateRangedate: dateRangedate,
	                },
	                success: function(resp){
	                    resp = JSON.parse(resp);
	                    var total_malus = 0;
	                    var html = '';
	                    for(var i = 0 ; i < resp.disadvantages.length ; i++ ){
	                        var created_date = resp.disadvantages[i].created_date.split(' ');
	                        var userAddInfo = resp.disadvantages[i].member_info;
	                        total_malus+= resp.disadvantages[i].malus;
	                        html+="<tr>";
	                                html+="<td>";
	                                    if(resp.disadvantages[i].order_id == 1){
	                                        html+= resp.disadvantages[i].id;
	                                    }
	                                    else{
	                                        html+= "<a target='_blank' href='https://new.regard-group.ru/account/flower_orders/order.php?orderId=" + resp.disadvantages[i].order_id + "'>" + resp.disadvantages[i].order_id + "</a>";
	                                    }
	                                html+="</td>";
	                                html+="<td>";
	                                    if(userAddInfo.full_name_am != ''){
	                                        html+="<br>" + resp.disadvantages[i].member_info.full_name_am
	                                    }
	                                    else{
	                                        html+="<br>" + resp.disadvantages[i].member_info.username
	                                    }
	                                html+="</td>";
	                                html+="<td>";
	                                    html+=resp.disadvantages[i].list_title
	                                html+="</td>";
	                                html+="<td>";
	                                    html+= "<span class='edit_disadvantage_description_" + resp.disadvantages[i].id + "' data-desc='" + resp.disadvantages[i].description + "'>" + resp.disadvantages[i].description + "</span>";
	                                    if(resp.disadvantages[i].file_path){
	                                        html+= '<br><a href="https://new.regard-group.ru/disadvantage_files/' + resp.disadvantages[i].file_path + '" target="_blank"><img src="../../images/file.png" style="height:35px"></a>';
	                                    }
	                                html+="</td>";
	                                html+="<td>";
	                                    total_maluses = total_maluses + resp.disadvantages[i].malus;
	                                    html+= '<span class="malusCountManagarial">' + resp.disadvantages[i].malus + '</span>';
	                                html+="</td>";
	                                html+="<td>";
	                                    html+=replaceDatetimeFormat(created_date[0]) + " " + created_date[1];
	                                     <?php
	                                        if($userData[0]['id'] == 4){
	                                        ?>
	                                        html+='<br><i data-row-id="' + resp.disadvantages[i].id + '" data-order-id="' + resp.disadvantages[i].order_id + '" title="Ջնջել" class="glyphicon glyphicon-remove remove_disadvantage_item hover_cursor_pointer text-primary"></i>';
	                                        html+='<i data-row-id="' + resp.disadvantages[i].id + '" title="Ջնջել" class="glyphicon glyphicon-edit edit_disadvantage_item hover_cursor_pointer text-primary" style="margin-left:7px"></i>';
	                                    <?php
	                                        }
	                                    ?>

	                                html+="</td>";
	                            html+="</tr>";
	                    }
	                    $(".selectedDateDisplayPart").html(replaceDatetimeFormat(resp.from_date) + " - " + replaceDatetimeFormat(resp.to_date));
	                    if(resp.selectedUserInfo.full_name_am != ''){
	                        $(".full_name_selected_user").html(resp.selectedUserInfo.full_name_am);
	                    }
	                    else{
	                        $(".full_name_selected_user").html(resp.selectedUserInfo.username);
	                    }
	                    if(resp.disadvantages.length){
	                        totalCountDisadvantageMalusManagarial();
	                        html+= '<tr>';
	                            html+= '<td colspan="4">';
	                                html+= 'Ընդհանուր մալուս՝'
	                            html+= '</td>';
	                            html+= '<td class="totalMalusValueManagarial">';
	                                html+= '';
	                            html+= '</td>';
	                            html+= '<td>';
	                            html+= '</td>';
	                        html+= '</tr>';
	                        $(".managerial_table_body").html(html);
	                    }
                        else{
                            $(".managerial_table_body").html(html);
                        }
	                }
	            })
			}
            var user_level = $(this).attr('data-user-level');
            if (user_level.indexOf('30') > -1) {
                $(".statistic_tab_florist").css({'display':'block'});
                $(".statistic_tab_driver").css({'display':'none'});
                $(".statistic_tab").css({'display':'none'});
                $.ajax({
                    type:"post",
                    data: {
                        getStatisticForFlorist: true,
                        user_id: user_id,
                        dateRangedate: dateRangedate,
                    },
                    success:function(res){
                        setTimeout(function(){
                            res = JSON.parse(res);
                            $(".prepared_all_shift").html(res.prepared_count);
                        },1000)
                    }
                })
            }
            if (user_level.indexOf('40') > -1) {
                $(".statistic_tab_driver").css({'display':'block'});
                $(".statistic_tab_florist").css({'display':'none'});
                $(".statistic_tab").css({'display':'none'});
                $.ajax({
                    type:"post",
                    data: {
                        getStatisticForDriver: true,
                        user_id: user_id,
                        dateRangedate: dateRangedate,
                    },
                    success:function(res){
                        setTimeout(function(){
                            res = JSON.parse(res);
                            var drive_price_array =res.drive_price_array;
                            var calculated_html = "";
                            var displayed_orders_html = "";
                            var total_need = 0;
                            $.each(drive_price_array, function(key,val) {         
                                calculated_html+= val.length + " * " + drive_prices[key] + ",";
                                total_need = total_need + (val.length * drive_prices[key]);
                                for(var i = 0;i< val.length;i++){
                                    displayed_orders_html+= val[i] + ",";
                                }
                            }); 
                            displayed_orders_html = displayed_orders_html.slice(0,-1);
                            calculated_html = calculated_html.slice(0,-1);
                            calculated_html+= " = " + total_need
                            $(".calculation_all_orders").html(calculated_html);
                            $(".calculation_display_orders").html(displayed_orders_html)
                        },1000)
                    }
                })
            }
            else{
                $(".statistic_tab").css({'display':'block'});
                $(".statistic_tab_florist").css({'display':'none'});
                $(".statistic_tab_driver").css({'display':'none'});
                $.ajax({
                    type:"post",
                    data: {
                        getStatisticForUser: true,
                        user_id: user_id,
                        dateRangedate: dateRangedate,
                    },
                    success:function(res){
                        setTimeout(function(){
                            res = JSON.parse(res);
                            $(".payed_day_shift").html(res.payed_orders_day);
                            $(".payed_evening_shift").html(res.payed_orders_evening);
                            $(".payed_night_shift").html(res.payed_orders_night);
                            $(".communication_orders_day").html(res.communication_orders_day);
                            $(".communication_orders_evening").html(res.communication_orders_evening);
                            $(".communication_orders_night").html(res.communication_orders_night);
                            $(".bac_toxnvac_orders_day").html(res.bac_toxnvac_orders_day);
                            $(".bac_toxnvac_orders_evening").html(res.bac_toxnvac_orders_evening);
                            $(".bac_toxnvac_orders_night").html(res.bac_toxnvac_orders_night);
                            $(".chexyal_orders_day").html(res.chexyal_orders_day);
                            $(".chexyal_orders_evening").html(res.chexyal_orders_evening);
                            $(".chexyal_orders_night").html(res.chexyal_orders_night);
                            $(".bonus_orders").html(res.total_bonuses);
                            $(".malus_orders").html(total_maluses);
                            $(".malus_procent").html(number_format((total_maluses * 100)/res.total_bonuses,2));
                            $(".payed_orders").html(res.payed_orders);
                            $(".not_payed_orders").html(res.not_payed_orders);
                            $(".not_payed_procent").html(res.not_payed_procent);

                            $(".for_charts").empty();
                            $(".for_charts").append("<div id='displayed_chart' style='margin-top:100px'></div>");
                            var total_payed_orders = res.payed_orders_day + res.payed_orders_evening + res.payed_orders_night;
                            var total_communication_orders = res.communication_orders_day + res.communication_orders_evening + res.communication_orders_night;
                            var total_bac_toxnvac_orders = res.bac_toxnvac_orders_day + res.bac_toxnvac_orders_evening + res.bac_toxnvac_orders_night;
                            var total_chexyal_orders = res.chexyal_orders_day + res.chexyal_orders_evening + res.chexyal_orders_night;
                            var resultVChart = Array();
                            resultVChart['total_payed_orders'] = total_payed_orders;
                            resultVChart['total_communication_orders'] = total_communication_orders;
                            resultVChart['total_bac_toxnvac_orders'] = total_bac_toxnvac_orders;
                            resultVChart['total_chexyal_orders'] = total_chexyal_orders;
                            CreateChart('displayed_chart',resultVChart);

                        },1000)
                    }
                })
            }
        }
    })
function CreateChart(conainer_id,resultVChart){
    Highcharts.chart(conainer_id, {
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false,
            type: 'pie',
        },
        title: {
            text:  'Statistic'
        },
        tooltip: {
            style: {
                fontSize:'18px'
            }
        },
        series: [{
            dataLabels: {
              style: {
                color: "black",
                fontSize: 16,
                textOutline: "none"
              }
            },
            data: [{
                name: 'Վճարված պատվերներ',
                size: 17,
                y: resultVChart['total_payed_orders'],
            }, {
                name: 'Կոմունիկացիա',
                fontSize: 40,
                color: 'red',
                y: resultVChart['total_communication_orders']
            }, {
                name: 'Բաց թողնված',
                y: resultVChart['total_bac_toxnvac_orders']
            }, {
                name: 'Չեղյալ',
                y: resultVChart['total_chexyal_orders']
            }]
        }]
    });
}
    $(document).on('click','.calculation_all_orders',function(){
        var click = $(this).attr('data-click');
        if(click == 1){
            $(this).attr('data-click',2);
            $(".calculation_display_orders").css({'display':'block'})
        }
        else{
            $(this).attr('data-click',1);
            $(".calculation_display_orders").css({'display':'none'})
        }
    })
    $(document).on("change",".shift_days_dropdown",function(){
        var selected_days = $(this).val();
        var shift = $(this).attr('data-shift');
        var payed_val = $(".payed_" + shift + "_shift").html();
        var total_pay_pr = payed_val/selected_days;
        $(".payed_" + shift + "_shift_procent").html(" / " + number_format(total_pay_pr,2) + " պատվերների միջին քանակը");

        var communication_val = $(".communication_orders_" + shift).html();
        communication_pr = communication_val/selected_days;
        $(".communication_orders_" + shift + "_procent").html(" / " + number_format(communication_pr,2) + " ցուցանիշ");

        var bac_toxnvac_val = $(".bac_toxnvac_orders_" + shift).html();
        var bac_toxnvac_pr = bac_toxnvac_val/selected_days;
        $(".bac_toxnvac_orders_" + shift + "_procent").html(" / " + number_format(bac_toxnvac_pr,2) + " ցուցանիշ");

        var chexyal_val = $(".chexyal_orders_" + shift).html();
        var chexyal_pr = chexyal_val/selected_days
        $(".chexyal_orders_" + shift + "_procent").html(" / " + number_format(chexyal_pr,2) + " ցուցանիշ");

        setTimeout(function(){
            var smal_pr = communication_pr + bac_toxnvac_pr + chexyal_pr;
            $(".total_procent_for_" + shift).html(number_format(total_pay_pr,2) + "/" + number_format(smal_pr,2) + " = " + number_format(total_pay_pr/smal_pr,2) + " արդյունավետության գործակից")
        },400)
    })
    function totalCountDisadvantageMalus(){
        setTimeout(function(){
            var malusHtmls = $(".malusCount");
            var total = 0;
            for(var i = 0;i<malusHtmls.length;i++){
                total+= parseInt($(malusHtmls[i]).html());
            }
            $(".totalMalusValue").html(total);
        },1000)
    }
    function totalCountDisadvantageMalusManagarial(){
        setTimeout(function(){
            var malusHtmls = $(".malusCountManagarial");
            var total = 0;
            for(var i = 0;i<malusHtmls.length;i++){
            	if($(malusHtmls[i]).html() > 0){
                	total+= parseInt($(malusHtmls[i]).html());
            	}
            }
            $(".totalMalusValueManagarial").html(total);
        },1000)
    }
    $(document).on('click','.edit_disadvantage_item',function(){
        var row_id = $(this).attr('data-row-id');
        var content = $(".edit_disadvantage_description_" + row_id).attr('data-desc');
        $(".edit_disadvantage_description_" + row_id).html('<textarea class="form-control new_desc_for_' + row_id + '">' + content + '</textarea><br><button class="btn btn-primary saveChangesDisadvantage" data-row-id="' + row_id + '">Change</button>');
    })
    $(document).on('click','.saveChangesDisadvantage',function(){
        var row_id = $(this).attr('data-row-id');
        var newContent = $(".new_desc_for_" + row_id).val();
        $.ajax({
            type:"post",
            data: {
                update_disadvantage_user: true,
                row_id: row_id,
                newContent: newContent,
            },
            success:function(res){
                $(".edit_disadvantage_description_" + row_id).html(newContent);
            }
        })
    })
    $(document).on('click','.remove_disadvantage_item',function(){
        if (window.confirm("Are you sure?")) {
            var row_id = $(this).attr('data-row-id');
            var order_id = $(this).attr('data-order-id');
            $(this).parent().parent().remove();
            totalCountDisadvantageMalus();
            $.ajax({
                type:"post",
                data: {
                    remove_disadvantage_row: true,
                    row_id: row_id,
                    order_id: order_id,
                },
                success:function(res){
                    console.log(res);
                }
            })
        }
    })
    // Added By Hrach
    $(document).on('click','.anavartShowRowsIcon',function(){
        var order_id = $(this).attr('data-order-id');
        $(".anavartDivFor_"+order_id).html('');
        $.ajax({
            url: location.href,
            type: 'post',
            data: {
                get_anvart_rows: true,
                order_id: order_id,
            },
            success: function(resp){
                if(resp.length > 5){
                    resp = JSON.parse(resp);
                    var html = '<ul>';
                    for(var i = 0; i < resp.length ; i++){
                        html+= "<li><span class='color_red'>" + resp[i]['created_date_format']  + "՝ " + resp[i]['full_name_am'] + "</span> ՝ " + resp[i]['description'] +  "</li>";
                    }
                    $(".anavartDivFor_"+order_id).append(html);
                }
                else{
                    var html = "<span class='color_red' style='margin-left:15px'>Տվյալ պատվերի համար անավարտի նշում չգտնվեց</span>";
                    $(".anavartDivFor_"+order_id).append(html);
                }
            }
        })
    })
</script>
<?//include($rootF."/livechat.php");?>
</body>
</html>