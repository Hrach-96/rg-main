<?php
session_start();
$pageName = "flower";
$rootF = "../..";
include($rootF."/apay/pay.api.php");
include($rootF."/configuration.php");
page::cmd();
$access = auth::checkUserAccess($secureKey);
$allData = array();
$buildClient = "";
$uid = "";
$level = "";
$userData = "";
$cc = "am";
$data = "";
$country_code = 0;
if(!$access){
    header("location:../../login");
}else{
    $uid = $_COOKIE["suid"];
    $level = auth::getUserLevel($uid);
    page::accessByLevel($level[0]["user_level"],$pageName);
    $levelArray = explode(",",$level[0]["user_level"]);
    $userData = auth::checkUserExistById($uid);
    $cc = $userData[0]["lang"];
    $country_code = $userData[0]['country_short'];
    if($country_code == 4){
        $cc = 'es';
    }
    if(is_file("lang/language_{$cc}.php"))
    {
        include("lang/language_{$cc}.php"); 
    }else{
        include("lang/language_am.php");
    }
}
function GetOffOnAction($variable){
    $off_on_actions = getwayConnect::getwayData("SELECT * from off_on where variable = '" . $variable . "'");
    return $off_on_actions[0]['action'];
}
$print_page_without_products = GetOffOnAction('print_page_without_products');
if(isset($_REQUEST["orderId"]))
{
    
    $orderId = htmlentities($_REQUEST["orderId"]);
    $accounted = getwayConnect::getwayData("SELECT accounted FROM rg_orders WHERE id = {$orderId}");
    $hasCount = getwayConnect::getwayData("SELECT count(*) as `count` FROM orders_products_accounting WHERE order_id = {$orderId}");
    if($accounted[0]["accounted"] <= 0 && $hasCount[0]['count'] <= 0)
    {
        if($print_page_without_products == 1){
            header("location:products/?cmd=out&orderId={$orderId}&manual=true"); //comment to remove product out requirement
        }
    } else {
        getwayConnect::getwaySend("UPDATE rg_orders set printed='1' where id='{$orderId}'");
    }
    $data = getwayConnect::getwayData("SELECT * FROM rg_orders WHERE id = '{$orderId}'");
    $florist_info = getwayConnect::getwayData("SELECT * FROM user WHERE id = '" . $data[0]['flourist_id'] . "'");
    $payment_type_array = array('15','23','16','24','25','11','12','13','26','27','28','30','31','5','19');
    $payment_type = $data[0]['payment_type'];
    $sell_point_partner = $data[0]['sell_point'];
    $delivery_region = $data[0]['delivery_region'];

    $deliverer_info = getwayConnect::getwayData("SELECT * FROM `delivery_deliverer` WHERE `id` = " . $data[0]['deliverer']);
    $tax_number_of_check_info_show = getwayConnect::getwayData("SELECT * FROM `tax_numbers_of_check` WHERE `order_id` = '" . $orderId ."'");
    if($tax_number_of_check_info_show){
        $tax_number_hdm_text = $tax_number_of_check_info_show[0]['hdm_tax'];
    }

    $barcelona = false;
    $sell_point = "";
    if(isset($data[0]) && isset($data[0]["sell_point"])){
        $sell_point_data = getwayConnect::getwayData("SELECT `name` FROM `delivery_sellpoint` WHERE id = '{$data[0]["sell_point"]}'");
        if(isset($sell_point_data[0]) && isset($sell_point_data[0]['name'])){
            $sell_point = $sell_point_data[0]['name'];
            //echo $rootF."/template/images/sellpoints/{$sell_point}.png";
            if(is_file($rootF."/template/images/sellpoints/{$sell_point}.png")){
                $sell_point = $rootF."/template/images/sellpoints/{$sell_point}.png";
            }else{
                $sell_point = false;
            }
        }
    }
    if($sell_point_data[0]['name'] == 'Anahit.am' || $sell_point_data[0]['name'] == 'Anahit-Flowers.com' || $sell_point_data[0]['name'] == 'Menu.am' || $sell_point_data[0]['name'] == 'Buy.am'){
        $logo_print = 'anahit-flowers-logo.gif';
    }
    else if($sell_point_data[0]['name'] == 'www.Regard-Flowers.com' ){
        $logo_print = 'regard-flowers-logo.png';
    }
    else if($sell_point_data[0]['name'] == 'Flowers-Barcelona.com' ){
        $barcelona = true;
        $logo_print = 'Flowers-Barcelona.com.png';
    }
    else{
        $logo_print = 'flowers-armenia-logo.png';
    }

    $data[0]["receiver_street"] = (isset($data[0]["receiver_street"])) ? $data[0]["receiver_street"] : "";
    $recStr = getwayConnect::getwayData("SELECT * FROM delivery_street WHERE code = '{$data[0]['receiver_street']}'");
    $data[0]["receiver_street"] = (isset($recStr[0]["name"])) ? $recStr[0]["name"] : $data[0]['receiver_street'] ;
    $delTime = getwayConnect::getwayData("SELECT * FROM delivery_time WHERE id = '{$data[0]["delivery_time"]}'");
    $data[0]["delivery_time"] = isset($delTime[0]["name"]) ? $delTime[0]["name"] : "";
    
    $related_info = "";
    
    $organisation_name = null;
    if(isset($data[0]['organisation']) && $data[0]['organisation'] != 0){
        $organisation = getwayConnect::getwayData("SELECT * FROM organisations where id = '{$data[0]['organisation']}'");
        if(isset($organisation) && !empty($organisation)){
            $organisation_name = $organisation[0]['name_am'];
        }
    }
    $related_products = getwayConnect::getwayData("SELECT order_related_product_description.*, jos_vm_product.product_sku FROM order_related_product_description RIGHT JOIN jos_vm_product on order_related_product_description.related_id = jos_vm_product.product_id  where order_id='{$_REQUEST['orderId']}'");
    if(isset($related_products) && !empty($related_products)){
        foreach($related_products as $related_product){
            if(isset($related_product['name']) && $related_product['name'] != ''){
                $related_info = $related_info. ", ". $related_product['name'];
            }
        }
    }

    $delivery_images = getwayConnect::getwayData("SELECT * FROM delivery_images where rg_order_id='{$_REQUEST['orderId']}'");
    
    if(isset($delivery_images) && !empty($delivery_images)){
        foreach($delivery_images as $delivery_image){
            if(isset($delivery_image['image_note']) && $delivery_image['image_note'] != ''){
                $related_info = $related_info. ", ". $delivery_image['image_note'];
            }
        }
    }
    $related_info = ltrim($related_info, ',');

}else{
   echo '<meta http-equiv="refresh" content="1;URL=../flower_orders" />';
   die();
}

// Hrach added
if(isset($_REQUEST['setPrintedTrue']) && $_REQUEST['setPrintedTrue']){
    $order_id = $_REQUEST['order_id'];
    getwayConnect::getwaySend("UPDATE rg_orders set printed='1' where id='{$order_id}'");
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="style.css" rel="stylesheet" type="text/css">

</head>
<style>
    .print_table th{
        padding-bottom: 8px;
        font-size: 12px;
    }
</style>
<body>

<?php
if($cc == "fr"){
include_once("lang/language_fr.php");

$data[0]['receiver_subregion'] = isset($data[0]['receiver_subregion']) ? $data[0]['receiver_subregion']: '';
$receiverAdd = getwayConnect::getwayData("SELECT * FROM delivery_subregion WHERE code = '{$data[0]['receiver_subregion']}'");
$data[0]['receiver_subregion'] = isset($receiverAdd[0]["name"]) ? $receiverAdd[0]["name"] : $data[0]['receiver_subregion'];
$data[0]["delivery_time"] = isset($data[0]["delivery_time"]) ? $data[0]["delivery_time"] : '';
//$data[0]["delivery_time_manual"] = $data[0]["delivery_time_manual"];
//sellpoint init
$image_logo = ($sell_point) ? $sell_point : "https://www.flowers-paris.ru/images/top/ru/top1-logo.jpg";
if(empty($data[0]["delivery_time"])){$delivery_time = $data[0]["delivery_time_manual"];}else{$delivery_time = $data[0]["delivery_time"];}
    $full_sender_name_print = $data[0]["sender_name"];
    $full_sender_region_print = " " . $data[0]["sender_region"];
    $full_sender_country_print = getwayConnect::getwayData("SELECT * FROM countries WHERE id = '{$data[0]['sender_country']}'")[0]['name_am'];
    if($data[0]["anonym"] == 1){
        $full_sender_name_print = '';
        $full_sender_region_print = '';
        $full_sender_country_print = '';
    }
    echo '
<div style="width:488px;">

<table class="print_table table-hover" style="text-align:left;">

    <tr>
        <th><a title="print" onClick="window.print();" class="printClassUpdate" data-order-id="' . $data[0]["id"] . '" target="_blank" style="cursor:pointer; color:#000000"><img src="/template/images/sellpoints/'.$logo_print.'" width="150"/></a></th>
<td align="center" style="font-size:16px; font-weight:bold; padding-bottom:20px">&nbsp;&nbspDétails de la Commande<span style="float:right; font-size:18px"><br>N-'. $data[0]["id"].'</span></td>
    </tr>


    <tr>
        <th>'.DELIVERY_DATE.'`<br>Delivery date: </th><td><div class="td_div_border" ><strong style="font-size:24px;">('.$delivery_time.')</strong>, '.$data[0]["delivery_date"].'</div></td>
    </tr>
    <tr>
        <th>'.ORDER_RECEIVER.'`<br>Receiver: </th><td><div class="td_div_border">'.$data[0]["receiver_name"].'</div></td>
    </tr>
    <tr><th>'.DELIVERY_ADDRESS.'`<br>Delivery address: </th><td><div class="td_div_border">'.$data[0]["receiver_address"].'</div></td></tr>
    <tr>
        <th>'.RECEIVER_PHONE.'`<br>Receiver phone: </th><td><div class="td_div_border">'.$data[0]["receiver_phone"].'</div></td>
    </tr>
    <tr>
        <th>'.SENDER.'`<br>Sender name: </th><td><div class="td_div_border">'.$full_sender_name_print.'</div></td>
    </tr>
    <tr>
        <th>'.SENDER_ADDRESS_1.'`<br>Sender address: </th><td><div class="td_div_border">'.$full_sender_country_print . $full_sender_region_print.'</div></td>
    </tr>
    <tr>
        <th>'.ORDER.'`<br>Order description: </th><td><div class="td_div_border">'.$data[0]["product"].'</div></td>
    </tr>
    <tr>
        <th>'.GREETING_CARD_1.'`<br>Greetings card </th><td><div class="td_div_border">N: '.trim($data[0]["greetings_card"]).'</div></td>
    </tr>
    <tr>
        <th><a title="close" onclick="history.back();" style="cursor:pointer; color:#000000">'.RECEIVER_SIGNATURE.'`<br>Signature of receiver:</a></th><td>_______________________________</td>
    </tr>
    <tr>
        <th colspan="2" style="font-size:11px; color:grey;">'.OUR_ADDRESS_PRINT.'<br>'.OUR_ADDRESS_PRINT_ENG.'</th>
    </tr>


</table>
</div>
';} else if($country_code == 4){
//include_once("lang/language_es.php");

$data[0]['receiver_subregion'] = isset($data[0]['receiver_subregion']) ? $data[0]['receiver_subregion']: '';
$receiverAdd = getwayConnect::getwayData("SELECT * FROM delivery_subregion WHERE code = '{$data[0]['receiver_subregion']}'");
$data[0]['receiver_subregion'] = isset($receiverAdd[0]["name"]) ? $receiverAdd[0]["name"] : $data[0]['receiver_subregion'];
$data[0]["delivery_time"] = isset($data[0]["delivery_time"]) ? $data[0]["delivery_time"] : '';
//$data[0]["delivery_time_manual"] = $data[0]["delivery_time_manual"];
    $image_logo = ($sell_point) ? $sell_point : "https://www.flowers-barcelona.com/images/flowers-barcelona-com-logo-sp.png";
if(empty($data[0]["delivery_time"])){$delivery_time = $data[0]["delivery_time_manual"];}else{$delivery_time = $data[0]["delivery_time"];}
    $full_sender_name_print = $data[0]["sender_name"];
    $full_sender_region_print = " " . $data[0]["sender_region"];
    $full_sender_country_print = getwayConnect::getwayData("SELECT * FROM countries WHERE id = '{$data[0]['sender_country']}'")[0]['name_en'];
    if($data[0]["anonym"] == 1){
        $full_sender_name_print = '';
        $full_sender_region_print = '';
        $full_sender_country_print = '';
    }
    echo '
<div style="width:488px;">

<table class="print_table table-hover" style="text-align:left;">

    <tr>
        <th><a title="print" onClick="window.print();" class="printClassUpdate" data-order-id="' . $data[0]["id"] . '" target="_blank" style="cursor:pointer; color:#000000"><img src="/template/images/sellpoints/'.$logo_print.'" width="150"/></a></th>
<td align="center" style="font-size:16px; font-weight:bold; padding-bottom:20px">&nbsp;&nbspDétails de la Commande<span style="float:right; font-size:18px"><br>N-'. $data[0]["id"].'</span></td>
    </tr>


    <tr>
        <th>'.DELIVERY_DATE.'`<br>Delivery date: </th><td><div class="td_div_border" ><strong style="font-size:24px;">('.$delivery_time.')</strong>, '.$data[0]["delivery_date"].'</div></td>
    </tr>
    <tr>
        <th>'.ORDER_RECEIVER.'`<br>Receiver: </th><td><div class="td_div_border">'.$data[0]["receiver_name"].'</div></td>
    </tr>
    <tr><th>'.DELIVERY_ADDRESS.'`<br>Delivery address:</th><td><div class="td_div_border">';
    if($organisation_name != ''){
        echo $organisation_name. ' - ';
    }
    echo $data[0]["receiver_street"].', '.$data[0]["receiver_address"];
    if(isset($data[0]["receiver_floor"]) && $data[0]["receiver_floor"] != ''){
        echo ', apartamento '.$data[0]["receiver_floor"];
    }
    if(isset($data[0]["receiver_entrance"]) && $data[0]["receiver_entrance"] != ''){
        echo ', entrada al edificio '.$data[0]["receiver_entrance"];
    }
    if(isset($data[0]["receiver_door_code"]) && $data[0]["receiver_door_code"] != ''){
        echo ', codigo de la puerta '.$data[0]["receiver_door_code"];
    }
    echo '  </div></td></tr>
    <tr>
        <th>'.RECEIVER_PHONE.'`<br>Receiver phone: </th><td><div class="td_div_border">'.$data[0]["receiver_phone"].'</div></td>
    </tr>
    <tr>
        <th>'.SENDER.'`<br>Sender name: </th><td><div class="td_div_border">'.$full_sender_name_print.'</div></td>
    </tr>
    <tr>
        <th>'.SENDER_ADDRESS_1.'`<br>Sender address: </th><td><div class="td_div_border">' . $full_sender_country_print . $full_sender_region_print.'</div></td>
    </tr>
    <tr>
        <th>'.ORDER.'`<br>Order description: </th><td><div class="td_div_border">'.$data[0]["product"].'</div></td>
    </tr>
    <tr>
        <th>'.GREETING_CARD_1.'`<br>Greetings card </th><td><div class="td_div_border">N: '.trim($data[0]["greetings_card"]).'</div></td>
    </tr>
    <tr>
        <th><a title="close" onclick="history.back();" style="cursor:pointer; color:#000000"><br>Firma del destinatario:`<br>Signature of receiver:</a></th><td>_______________________________</td>
    </tr>
    <tr>
        <th colspan="2" style="font-size:11px; color:grey;">Address: 14 Calle Rogent street, 08026 Barcelona</th>
    </tr>

</table>
</div>
';} else{
include_once("lang/language_am.php");
$data[0]['receiver_subregion'] = isset($data[0]['receiver_subregion']) ? $data[0]['receiver_subregion']: '';
$receiverAdd = getwayConnect::getwayData("SELECT * FROM delivery_subregion WHERE code = '{$data[0]['receiver_subregion']}'");
$data[0]['receiver_subregion'] = isset($receiverAdd[0]["name"]) ? $receiverAdd[0]["name"] : $data[0]['receiver_subregion'];
$data[0]["delivery_time"] = isset($data[0]["delivery_time"]) ? $data[0]["delivery_time"] : '';
//$data[0]["delivery_time_manual"] = $data[0]["delivery_time_manual"];
    $image_logo = ($sell_point) ? $sell_point : $rootF."/template/icons/black-and-white-logo.jpg";
if(!empty($data[0]["delivery_time_manual"])){
    $delivery_time = $data[0]["delivery_time_manual"];
    if(!empty($data[0]["travel_time_end"])){
        $delivery_time = $data[0]["delivery_time_manual"] . " - ". $data[0]["travel_time_end"];
    }
}else{$delivery_time = $data[0]["delivery_time"];}
$levels = explode(",",$level[0]["user_level"]);
if(in_array(30,$levels)){
    $full_sender_name_print = $data[0]["sender_name"];
    $full_sender_region_print = " " . $data[0]["sender_region"];
    $full_sender_country_print = getwayConnect::getwayData("SELECT * FROM countries WHERE id = '{$data[0]['sender_country']}'")[0]['name_am'];
    if($data[0]["anonym"] == 1){
        $full_sender_name_print = '';
        $full_sender_region_print = '';
        $full_sender_country_print = '';
    }
    if($barcelona){
        echo '

            <div style="width:488px">

            <table class="print_table table-hover" style="text-align:left;">

                <tr>
                    <th ><img src="/template/images/sellpoints/'.$logo_print.'" width="150"/></th><td style="font-size:14px; font-weight:bold; padding-bottom:20px">'.ORDER.'<br>Order<a title="Տպել" onClick="window.print();" class="printClassUpdate" data-order-id="' . $data[0]["id"] . '" target="_blank" style="cursor:pointer; color:#000000"><span style="float:right; font-size:18px">N-'. $data[0]["id"].'</span></a></td>
                </tr>
                <tr>
                    <th>'.DELIVERY_DATE.'`<br>Delivery date:</th><td><div class="td_div_border"><strong style="font-size:18px;">('.$delivery_time.')</strong>, '.$data[0]["delivery_date"].'</div></td>
                </tr>
                <tr>
                    <th>'.ORDER_RECEIVER.'`<br>Receiver:</th><td><div class="td_div_border">'.$data[0]["receiver_name"].'</div></td>
                </tr>
                <tr><th>'.DELIVERY_ADDRESS.'`<br>Delivery address:</th><td><div class="td_div_border">';
                if($organisation_name != ''){
                    echo $organisation_name. ' - ';
                }
                echo $data[0]["receiver_street"].', '.$data[0]["receiver_address"];
                if(isset($data[0]["receiver_floor"]) && $data[0]["receiver_floor"] != ''){
                    echo ', բն․ '.$data[0]["receiver_floor"];
                }
                if(isset($data[0]["receiver_entrance"]) && $data[0]["receiver_entrance"] != ''){
                    echo ', մուտք '.$data[0]["receiver_entrance"];
                }
                if(isset($data[0]["receiver_door_code"]) && $data[0]["receiver_door_code"] != ''){
                    echo ', կոդ '.$data[0]["receiver_door_code"];
                }
                $armStates = Array('Կոտայքի մարզ','Լոռու մարզ','Տավուշի մարզ','Սյունիքի մարզ','Վայոց ձորի մարզ','Արմավիրի մարզ','Շիրակի մարզ','Արարատի մարզ','Արագածոտնի մարզ','Գեղարքունիքի մարզ');
                $erevanSubregions = Array('Աջափնյակ','Ավան','Արաբկիր','Դավթաշեն','Էրեբունի','Կենտրոն','Մալաթիա-Սեբաստիա','Նոր Նորք','Նորք Մարաշ','Նուբարաշեն','Շենգավիթ','Քանաքեռ-Զեյթուն');
                $state;
                if(!in_array($data[0]["receiver_subregion"],$armStates)){
                    $state = STATE;
                }
                if(in_array($data[0]["receiver_subregion"],$erevanSubregions)){
                    $state.= " Երևան";
                }

                echo '  ('.$data[0]["receiver_subregion"].' '.$state.')</div></td></tr>
                <tr>
                    <th>'.RECEIVER_PHONE.'`<br>Receiver phone:</th><td><div class="td_div_border">'.$data[0]["receiver_phone"].'</div></td>
                </tr>
                <tr>
                    <th>'.SENDER.'`<br>Sender name:</th><td><div class="td_div_border">'.$full_sender_name_print.'</div></td>
                </tr>
                <tr>
                    <th>'.SENDER_ADDRESS_1.'`<br>Sender address:</th><td><div class="td_div_border">' . $full_sender_country_print . $full_sender_region_print.'</div></td>
                </tr>
                <tr>
                    <th>'.ORDER.'`<br>Order description:</th><td><div class="td_div_border">';
                    echo $related_info;
                    // if($data[0]["product"] != ''){
                    //     echo ', '. $data[0]["product"];
                    // }
                    echo '</div></td>
                </tr>
                <tr>
                    <th>'.GREETING_CARD_1.'`<br>Greetings card</th><td><div class="td_div_border">N: '.trim($data[0]["greetings_card"]).'</div></td>
                </tr>
                <tr>
                    <th><a title="Փակել" onclick="history.back();" style="cursor:pointer; color:#000000">'.RECEIVER_SIGNATURE.'`<br>Signature of receiver:</a></th><td>_______________________</td>
                </tr>
                <tr>
                    <th colspan="2" style="font-size:11px; color:grey;">'.OUR_ADDRESS_PRINT.'<br>'.OUR_ADDRESS_PRINT_ENG.'</th>
                </tr>

            </table>
            </div>
            ';
    }
    else{
        echo '

        <div style="width:488px;float:left">

        <table class="print_table table-hover" style="text-align:left;">

            <tr>
                <th ><img src="/template/images/sellpoints/'.$logo_print.'" width="150"/></th><td style="font-size:14px; font-weight:bold; padding-bottom:20px">'.ORDER.'<br>Order<br>' . ORDER_RUSSIAN .  ' <a title="Տպել" onClick="window.print();" class="printClassUpdate" data-order-id="' . $data[0]["id"] . '" target="_blank" style="cursor:pointer; color:#000000"><span style="float:right; font-size:18px">N-'. $data[0]["id"].'</span></a>
                ';
                 if((in_array($payment_type, $payment_type_array) && $delivery_region == 1) || ($sell_point_partner == 16 || $sell_point_partner == 15 || $sell_point_partner == 44 || $sell_point_partner == 45 || $sell_point_partner == 48)){
                        echo '<br><p style="float:left;margin:0">Ուղղեկցող փաստաթուղթ - ';
                        if($tax_number_hdm_text == ''){
                            echo ' <img height="30px" style="float:right" src="' . $rootF . '/template/icons/important/important.gif"></label>';
                        }
                        else{
                            echo ' ' . $tax_number_hdm_text;
                        }
                        echo '</p>';
                    }
                echo '
                </td>
            </tr>
            <tr>
                <th>'.DELIVERY_DATE.'`<br>Delivery date:<br>' . DELIVERY_DATE_RUS . ':</th><td><div class="td_div_border"><strong style="font-size:18px;">('.$delivery_time.')</strong>, '.$data[0]["delivery_date"].'</div></td>
            </tr>
            <tr>
                <th>'.ORDER_RECEIVER.'`<br>Receiver:<br>' . ORDER_RECEIVER_RUS . ':</th><td><div class="td_div_border">'.$data[0]["receiver_name"].'</div></td>
            </tr>
            <tr><th>'.DELIVERY_ADDRESS.'`<br>Delivery address:<br>' . DELIVERY_ADDRESS_RUS . ':</th><td><div class="td_div_border">';
            if($organisation_name != ''){
                echo $organisation_name. ' - ';
            }
            echo $data[0]["receiver_street"].', '.$data[0]["receiver_address"];
            if(isset($data[0]["receiver_floor"]) && $data[0]["receiver_floor"] != ''){
                echo ', բն․ '.$data[0]["receiver_floor"];
            }
            if(isset($data[0]["receiver_entrance"]) && $data[0]["receiver_entrance"] != ''){
                echo ', մուտք '.$data[0]["receiver_entrance"];
            }
            if(isset($data[0]["receiver_door_code"]) && $data[0]["receiver_door_code"] != ''){
                echo ', կոդ '.$data[0]["receiver_door_code"];
            }
            echo '  ('.$data[0]["receiver_subregion"].' '.STATE.')</div></td></tr>
            <tr>
                <th>'.RECEIVER_PHONE.'`<br>Receiver phone:<br>' . RECEIVER_PHONE_RUS . ':</th><td><div class="td_div_border">'.$data[0]["receiver_phone"].'</div></td>
            </tr>
            <tr>
                <th>'.SENDER.'`<br>Sender name:<br>' . SENDER_RUS . ':</th><td><div class="td_div_border">'.$full_sender_name_print.'</div></td>
            </tr>
            <tr>
                <th>'.SENDER_ADDRESS_1.'`<br>Sender address:<br>' . SENDER_ADDRESS_1_RUS . ':</th><td><div class="td_div_border">' . $full_sender_country_print . $full_sender_region_print.'</div></td>
            </tr>
            <tr>
                <th>'.ORDER.'`<br>Order description:<br>' . ORDER_DESCRIPTION_RUS . ':</th><td><div class="td_div_border">';
                echo $related_info;
                // if($data[0]["product"] != ''){
                //     echo ', '. $data[0]["product"];
                // }
                echo '</div></td>
            </tr>
            <tr>
                <th>'.GREETING_CARD_1.'`<br>Greetings card<br>' . GREETING_CARD_1_RUS . ':</th><td><div class="td_div_border">N: '.trim($data[0]["greetings_card"]).'</div></td>
            </tr>
            <tr>
                <th colspan="2">'.TEXT_ARM_INFO_1.'<br>' . TEXT_ENG_INFO_1 . '<br>' . TEXT_RUS_INFO_1 . '</th>
            </tr>
            <tr>
                <th><a title="Փակել" onclick="history.back();" style="cursor:pointer; color:#000000">'.RECEIVER_SIGNATURE.'`<br>Signature of receiver:</a></th><td>_______________________</td>
            </tr>
            <tr>
                <th>Գնահատել առաքված Պատվերը՝<br> <input type="checkbox">' . ARM_BAD . ' ' . ENG_BAD . ' ' . RUS_BAD . '<br><input type="checkbox"> '  . ARM_WELL . ' ' . ENG_WELL . ' ' . RUS_WELL . '<br><input type="checkbox">' . ARM_GREAT . ' ' . ENG_GREAT . ' ' . RUS_GREAT . '</th>
                <th>' . DELIVERER . '՝ ' . $deliverer_info[0]['full_name'] . ' ,մատակարար ' . $florist_info[0]['full_name_am'] . ' ։ Գնահատել պատվերը<br> <input type="checkbox">' . ARM_BAD . ' ' . ENG_BAD . ' ' . RUS_BAD . '<br><input type="checkbox"> '  . ARM_WELL . ' ' . ENG_WELL . ' ' . RUS_WELL . '<br><input type="checkbox">' . ARM_GREAT . ' ' . ENG_GREAT . ' ' . RUS_GREAT . '</th>
            </tr>
            <tr>
                <th colspan="2" style="font-size:11px; color:grey;">'.OUR_ADDRESS_PRINT.'<br>'.OUR_ADDRESS_PRINT_ENG.'<br>'.OUR_ADDRESS_PRINT_RUS.'</th>
            </tr>

        </table>
        </div>
        <div style="width:300px;float:left">
        ';
            echo TEXT_ARM_INFO_2 . "<br>" . TEXT_ENG_INFO_2 . "<br>" . TEXT_RUS_INFO_2 . '
        </div>
        ';
    }

}else{
    $full_sender_name = $data[0]["sender_name"];
    $full_receiver_name = $data[0]["receiver_name"];
    if($image_logo == '../../template/images/sellpoints/Flowers-Barcelona.com.png'){
        $sender_name = '';
        $sender_l_name = '';
        $full_name_sender =  explode( ' ' , $data[0]["sender_name"] );
        $full_name_receiver =  explode( ' ' , $data[0]["receiver_name"] );
        $sender_first_name = $full_name_sender[0];
        $sender_last_name = $full_name_sender[1];
        $receiver_first_name = $full_name_receiver[0];
        $receiver_last_name = $full_name_receiver[1];
        if($sender_first_name != ''){
            if(isset($full_name_sender[0])){
                $first_names_sender = getwayConnect::getwayData("SELECT * FROM translate_of_names where first_name_arm ='{$sender_first_name}'");
                if(empty($first_names_sender[0]['first_name_eng'])){
                    $sender_name = $sender_first_name;
                }
                else{
                    $sender_name = $first_names_sender[0]['first_name_eng'];
                }
            }
        }
        if($sender_last_name != ''){
            if(isset($full_name_sender[0])){
                $last_names_sender = getwayConnect::getwayData("SELECT * FROM translate_of_names where last_name_arm ='{$sender_last_name}'");
                if(empty($last_names_sender[0]['last_name_eng'])){
                    $sender_l_name = $sender_last_name;
                }
                else{
                    $sender_l_name = $last_names_sender[0]['last_name_eng'];
                }
            }
        }
        if($receiver_first_name != ''){
            if(isset($full_name_receiver[0])){
                $first_names_receiver = getwayConnect::getwayData("SELECT * FROM translate_of_names where first_name_arm ='{$receiver_first_name}'");
                if(empty($first_names_receiver[0]['first_name_eng'])){
                    $receiver_name = $receiver_first_name;
                }
                else{
                    $receiver_name = $first_names_receiver[0]['first_name_eng'];
                }
            }
        }
        if($receiver_last_name != ''){
            if(isset($full_name_receiver[0])){
                $last_names_receiver = getwayConnect::getwayData("SELECT * FROM translate_of_names where last_name_arm ='{$receiver_last_name}'");
                if(empty($last_names_receiver[0]['last_name_eng'])){
                    $receiver_l_name = $receiver_last_name;
                }
                else{
                    $receiver_l_name = $last_names_receiver[0]['last_name_eng'];
                }
            }
        }
        $full_sender_name = $sender_name . ' ' . $sender_l_name;
        $full_receiver_name = $receiver_name . ' ' . $receiver_l_name;
    }
    if($barcelona){
        echo '

            <div style="width:488px">

            <table class="print_table table-hover" style="text-align:left;">

                <tr>
                    <th ><img src="/template/images/sellpoints/'.$logo_print.'" width="150"/></th><td style="font-size:14px; font-weight:bold; padding-bottom:20px">'.ORDER.'<br>Order<a title="Տպել" onClick="window.print();" class="printClassUpdate" data-order-id="' . $data[0]["id"] . '" target="_blank" style="cursor:pointer; color:#000000"><span style="float:right; font-size:18px">N-'. $data[0]["id"].'</span></a></td>
                </tr>
                <tr>
                    <th>'.DELIVERY_DATE.'`<br>Delivery date:</th><td><div class="td_div_border"><strong style="font-size:18px;">('.$delivery_time.')</strong>, '.$data[0]["delivery_date"].'</div></td>
                </tr>
                <tr>
                    <th>'.ORDER_RECEIVER.'`<br>Receiver:</th><td><div class="td_div_border">'.$full_receiver_name.'</div></td>
                </tr>
                <tr><th>'.DELIVERY_ADDRESS.'`<br>Delivery address:</th><td><div class="td_div_border">';
                if($organisation_name != ''){
                    echo $organisation_name. ' - ';
                }
                echo $data[0]["receiver_street"].', '.$data[0]["receiver_address"];
                if(isset($data[0]["receiver_floor"]) && $data[0]["receiver_floor"] != ''){
                    echo ', բն․ '.$data[0]["receiver_floor"];
                }
                if(isset($data[0]["receiver_entrance"]) && $data[0]["receiver_entrance"] != ''){
                    echo ', մուտք '.$data[0]["receiver_entrance"];
                }
                if(isset($data[0]["receiver_door_code"]) && $data[0]["receiver_door_code"] != ''){
                    echo ', կոդ '.$data[0]["receiver_door_code"];
                }
                $armStates = Array('Կոտայքի մարզ','Լոռու մարզ','Տավուշի մարզ','Սյունիքի մարզ','Վայոց ձորի մարզ','Արմավիրի մարզ','Շիրակի մարզ','Արարատի մարզ','Արագածոտնի մարզ','Գեղարքունիքի մարզ');
                $erevanSubregions = Array('Աջափնյակ','Ավան','Արաբկիր','Դավթաշեն','Էրեբունի','Կենտրոն','Մալաթիա-Սեբաստիա','Նոր Նորք','Նորք Մարաշ','Նուբարաշեն','Շենգավիթ','Քանաքեռ-Զեյթուն');
                $state;
                if(in_array($data[0]["receiver_subregion"],$armStates)){
                    $state = STATE;
                }
                if(in_array($data[0]["receiver_subregion"],$erevanSubregions)){
                    $state.= " Երևան";
                }
                $full_sender_name_print = $full_sender_name;
                $full_sender_region_print = " " . $data[0]["sender_region"];
                $full_sender_country_print = getwayConnect::getwayData("SELECT * FROM countries WHERE id = '{$data[0]['sender_country']}'")[0]['name_am'];
                if($data[0]["anonym"] == 1){
                    $full_sender_name_print = '';
                    $full_sender_region_print = '';
                    $full_sender_country_print = '';
                }
                echo '  ('.$data[0]["receiver_subregion"].' '.$state.')</div></td></tr>
                <tr>
                    <th>'.RECEIVER_PHONE.'`<br>Receiver phone:</th><td><div class="td_div_border">'.$data[0]["receiver_phone"].'</div></td>
                </tr>
                <tr>
                    <th>'.SENDER.'`<br>Sender name:</th><td><div class="td_div_border">'. $full_sender_name_print . '</div></td>
                </tr>
                <tr>
                    <th>'.SENDER_ADDRESS_1.'`<br>Sender address:</th><td><div class="td_div_border">'.$full_sender_country_print . $full_sender_region_print.'</div></td>
                </tr>
                <tr>
                    <th>'.ORDER.'`<br>Order description:</th><td><div class="td_div_border">';
                    echo $related_info;
                    // if($data[0]["product"] != ''){
                    //     echo ', '. $data[0]["product"];
                    // }
                    echo '</div></td>
                </tr>
                <tr>
                    <th>'.GREETING_CARD_1.'`<br>Greetings card</th><td><div class="td_div_border">N: '.trim($data[0]["greetings_card"]).'</div></td>
                </tr>
                <tr>
                    <th><a title="Փակել" onclick="history.back();" style="cursor:pointer; color:#000000">'.RECEIVER_SIGNATURE.'`<br>Signature of receiver:</a></th><td>_______________________</td>
                </tr>
                <tr>
                    <th colspan="2" style="font-size:11px; color:grey;">'.OUR_ADDRESS_PRINT.'<br>'.OUR_ADDRESS_PRINT_ENG.'</th>
                </tr>

            </table>
            </div>
            ';
    }
    else{
        echo '

            <div style="width:488px;float:left">

            <table class="print_table table-hover" style="text-align:left;">

                <tr>
                    <th ><img src="/template/images/sellpoints/'.$logo_print.'" width="150"/></th><td style="font-size:14px; font-weight:bold; padding-bottom:20px">'.ORDER.'<br>Order<br>' . ORDER_RUSSIAN . '<a title="Տպել" onClick="window.print();" class="printClassUpdate" data-order-id="' . $data[0]["id"] . '" target="_blank" style="cursor:pointer; color:#000000"><span style="float:right; font-size:18px">N-'. $data[0]["id"].'</span></a>
                    ';
                    if((in_array($payment_type, $payment_type_array) && $delivery_region == 1) || ($sell_point_partner == 16 || $sell_point_partner == 15 || $sell_point_partner == 44 || $sell_point_partner == 45 || $sell_point_partner == 48)){
                        echo '<br><p style="float:left;margin:0">Ուղղեկցող փաստաթուղթ - ';
                        if($tax_number_hdm_text == ''){
                            echo ' <img height="30px" style="float:right" src="' . $rootF . '/template/icons/important/important.gif"></label>';
                        }
                        else{
                            echo ' ' . $tax_number_hdm_text;
                        }
                        echo '</p>';
                    }
                    echo '
                    </td>
                </tr>
                <tr>
                    <th>'.DELIVERY_DATE.'`<br>Delivery date:<br>' . DELIVERY_DATE_RUS . ':</th><td><div class="td_div_border"><strong style="font-size:18px;">('.$delivery_time.')</strong>, '.$data[0]["delivery_date"].'</div></td>
                </tr>
                <tr>
                    <th>'.ORDER_RECEIVER.'`<br>Receiver:<br>' . ORDER_RECEIVER_RUS . ':</th><td><div class="td_div_border">'.$full_receiver_name.'</div></td>
                </tr>
                <tr><th>'.DELIVERY_ADDRESS.'`<br>Delivery address:<br>' . DELIVERY_ADDRESS_RUS . ':</th><td><div class="td_div_border">';
                if($organisation_name != ''){
                    echo $organisation_name. ' - ';
                }
                echo $data[0]["receiver_street"].', '.$data[0]["receiver_address"];
                if(isset($data[0]["receiver_floor"]) && $data[0]["receiver_floor"] != ''){
                    echo ', բն․ '.$data[0]["receiver_floor"];
                }
                if(isset($data[0]["receiver_entrance"]) && $data[0]["receiver_entrance"] != ''){
                    echo ', մուտք '.$data[0]["receiver_entrance"];
                }
                if(isset($data[0]["receiver_door_code"]) && $data[0]["receiver_door_code"] != ''){
                    echo ', կոդ '.$data[0]["receiver_door_code"];
                }
                $armStates = Array('Կոտայքի մարզ','Լոռու մարզ','Տավուշի մարզ','Սյունիքի մարզ','Վայոց ձորի մարզ','Արմավիրի մարզ','Շիրակի մարզ','Արարատի մարզ','Արագածոտնի մարզ','Գեղարքունիքի մարզ');
                $erevanSubregions = Array('Աջափնյակ','Ավան','Արաբկիր','Դավթաշեն','Էրեբունի','Կենտրոն','Մալաթիա-Սեբաստիա','Նոր Նորք','Նորք Մարաշ','Նուբարաշեն','Շենգավիթ','Քանաքեռ-Զեյթուն');
                $state;
                if(in_array($data[0]["receiver_subregion"],$armStates)){
                    $state = STATE;
                }
                if(in_array($data[0]["receiver_subregion"],$erevanSubregions)){
                    $state.= " Երևան";
                }
                $full_sender_name_print = $full_sender_name;
                $full_sender_region_print = " " . $data[0]["sender_region"];
                $full_sender_country_print = getwayConnect::getwayData("SELECT * FROM countries WHERE id = '{$data[0]['sender_country']}'")[0]['name_am'];
                if($data[0]["anonym"] == 1){
                    $full_sender_name_print = '';
                    $full_sender_region_print = '';
                    $full_sender_country_print = '';
                }
                echo '  ('.$data[0]["receiver_subregion"].' '.$state.')</div></td></tr>
                <tr>
                    <th>'.RECEIVER_PHONE.'`<br>Receiver phone:<br>' . RECEIVER_PHONE_RUS . ':</th><td><div class="td_div_border">'.$data[0]["receiver_phone"].'</div></td>
                </tr>
                <tr>
                    <th>'.SENDER.'`<br>Sender name:<br>' . SENDER_RUS . ':</th><td><div class="td_div_border">'. $full_sender_name_print . '</div></td>
                </tr>
                <tr>
                    <th>'.SENDER_ADDRESS_1.'`<br>Sender address:<br>' . SENDER_ADDRESS_1_RUS . ':</th><td><div class="td_div_border">'.$full_sender_country_print . $full_sender_region_print.'</div></td>
                </tr>
                <tr>
                    <th>'.ORDER.'`<br>Order description:<br>' . ORDER_DESCRIPTION_RUS . ':</th><td><div class="td_div_border">';
                    echo $related_info;
                    // if($data[0]["product"] != ''){
                    //     echo ', '. $data[0]["product"];
                    // }
                    echo '</div></td>
                </tr>
                <tr>
                    <th>'.GREETING_CARD_1.'`<br>Greetings card:<br>' . GREETING_CARD_1_RUS . ':</th><td><div class="td_div_border">N: '.trim($data[0]["greetings_card"]).'</div></td>
                </tr>
                <tr>
                    <th colspan="2">'.TEXT_ARM_INFO_1.'<br>' . TEXT_ENG_INFO_1 . '<br>' . TEXT_RUS_INFO_1 . '</th>
                </tr>
                <tr>
                    <th><a title="Փակել" onclick="history.back();" style="cursor:pointer; color:#000000">'.RECEIVER_SIGNATURE.'`<br>Signature of receiver:<br>' . RECEIVER_SIGNATURE_RUS . ':</a></th><td>_______________________</td>
                </tr>
                <tr>
                    <th>Գնահատել առաքված Պատվերը՝<br> <input type="checkbox">' . ARM_BAD . ' ' . ENG_BAD . ' ' . RUS_BAD . '<br><input type="checkbox"> '  . ARM_WELL . ' ' . ENG_WELL . ' ' . RUS_WELL . '<br><input type="checkbox">' . ARM_GREAT . ' ' . ENG_GREAT . ' ' . RUS_GREAT . '</th>
                    <th>' . DELIVERER . '՝ ' . $deliverer_info[0]['full_name'] . ' ,մատակարար ' . $florist_info[0]['full_name_am'] . ' ։ Գնահատել պատվերը<br> <input type="checkbox">' . ARM_BAD . ' ' . ENG_BAD . ' ' . RUS_BAD . '<br><input type="checkbox"> '  . ARM_WELL . ' ' . ENG_WELL . ' ' . RUS_WELL . '<br><input type="checkbox">' . ARM_GREAT . ' ' . ENG_GREAT . ' ' . RUS_GREAT . '</th>
                </tr>
                <tr>
                    <th colspan="2" style="font-size:11px; color:grey;">'.OUR_ADDRESS_PRINT.'<br>'.OUR_ADDRESS_PRINT_ENG.'<br>'.OUR_ADDRESS_PRINT_RUS.'</th>
                </tr>

            </table>
            </div>
            <div style="width:300px;float:left">
            ';
                echo TEXT_ARM_INFO_2 . "<br>" . TEXT_ENG_INFO_2 . "<br>" . TEXT_RUS_INFO_2 . '
            </div>
            ';
    }

}
}
?>
<!-- initialize library-->
        <!-- Latest jquery compiled and minified JavaScript -->
        <script src="https://code.jquery.com/jquery-latest.min.js"></script>
        <!-- Bootstrap minified JavaScript -->
        <script src="<?=$rootF?>/template/bootstrap/js/bootstrap.min.js"></script>
        <!--end initialize library-->
        <!-- Menu Toggle Script -->
        <!-- Bootstrap minified JavaScript -->
        <script src="<?=$rootF?>/template/js/accounting.min.js"></script>
        <script src="<?=$rootF?>/template/datepicker/js/bootstrap-datepicker.js"></script>
        <script src="<?=$rootF?>/template/js/phpjs.js"></script>
        <script src="<?=$rootF?>/template/rangedate/moment.min.js"></script>
        <script src="<?=$rootF?>/template/rangedate/jquery.daterangepicker.js"></script>
        <script type="text/javascript">
            $(document).ready(function(){
                $(document).on("click",".printClassUpdate",function(){
                    var order_id = $(this).attr('data-order-id');
                    $.ajax({
                        url: location.href,
                        type: 'post',
                        data: {
                            setPrintedTrue: true,
                            order_id: order_id
                        },
                        success: function(resp){
                        }
                    })
                })
            })
        </script>
</body>
</html>