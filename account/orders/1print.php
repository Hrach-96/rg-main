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
if(!$access){
	header("location:../../login");
}else{
	$uid = $_COOKIE["suid"];
	$level = auth::getUserLevel($uid);
	page::accessByLevel($level[0]["user_level"],$pageName);
	$levelArray = explode(",",$level[0]["user_level"]);
	$userData = auth::checkUserExistById($uid);
	$cc = $userData[0]["lang"];
	if(is_file("lang/language_{$cc}.php"))
	{
		include("lang/language_{$cc}.php");	
	}else{
		include("lang/language_am.php");
	}
}


if(isset($_REQUEST["orderId"]))
{
	$orderId = htmlentities($_REQUEST["orderId"]);
	$data = getwayConnect::getwayData("SELECT * FROM rg_orders WHERE id = '{$orderId}'");
	$data[0]["receiver_street"] = (isset($data[0]["receiver_street"])) ? $data[0]["receiver_street"] : "";
	$recStr = getwayConnect::getwayData("SELECT * FROM delivery_street WHERE code = '{$data[0]['receiver_street']}'");
	$data[0]["receiver_street"] = (isset($recStr[0]["name"])) ? $recStr[0]["name"] : $data[0]['receiver_street'] ;
	$delTime = getwayConnect::getwayData("SELECT * FROM delivery_time WHERE id = '{$data[0]["delivery_time"]}'");
	$data[0]["delivery_time"] = isset($delTime[0]["name"]) ? $delTime[0]["name"] : "";
}else{
   echo '<meta http-equiv="refresh" content="1;URL=../flower_orders" />';
   die();
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
<body>

<?php
if($cc == "fr"){
include_once("lang/language_fr.php");

$data[0]['receiver_subregion'] = isset($data[0]['receiver_subregion']) ? $data[0]['receiver_subregion']: '';
$receiverAdd = getwayConnect::getwayData("SELECT * FROM delivery_subregion WHERE code = '{$data[0]['receiver_subregion']}'");
$data[0]['receiver_subregion'] = isset($receiverAdd[0]["name"]) ? $receiverAdd[0]["name"] : $data[0]['receiver_subregion'];
$data[0]["delivery_time"] = isset($data[0]["delivery_time"]) ? $data[0]["delivery_time"] : '';
$data[0]["delivery_time_manual"] = $data[0]["delivery_time_manual"];
if(empty($data[0]["delivery_time"])){$delivery_time = $data[0]["delivery_time_manual"];}else{$delivery_time = $data[0]["delivery_time"];}

    echo '
<div style="width:400px;">

<table class="print_table table-hover" style="text-align:left;">

	<tr>
        <th><a title="print" onClick="window.print();" target="_blank" style="cursor:pointer; color:#000000"><img src="http://www.flowers-paris.ru/images/top/ru/top1-logo.jpg" width="150"/></a></th>
<td align="center" style="font-size:16px; font-weight:bold; padding-bottom:20px">&nbsp;&nbspDétails de la Commande<span style="float:right; font-size:18px"><br>N-'. $data[0]["id"].'</span></td>
    </tr>


    <tr>
        <th>'.DELIVERY_DATE.': </th><td><div class="td_div_border" ><strong style="font-size:24px;">('.$delivery_time.')</strong>, '.$data[0]["delivery_date"].'</div></td>
    </tr>
    <tr>
        <th>'.ORDER_RECEIVER.': </th><td><div class="td_div_border">'.$data[0]["receiver_name"].'</div></td>
    </tr>
    <tr><th>'.DELIVERY_ADDRESS.': </th><td><div class="td_div_border">'.$data[0]["receiver_address"].'</div></td></tr>
    <tr>
    	<th>'.RECEIVER_PHONE.': </th><td><div class="td_div_border">'.$data[0]["receiver_phone"].'</div></td>
    </tr>
    <tr>
    	<th>'.SENDER.': </th><td><div class="td_div_border">'.$data[0]["sender_name"].'</div></td>
    </tr>
    <tr>
    	<th>'.SENDER_ADDRESS_1.': </th><td><div class="td_div_border">'.$data[0]["sender_region"].'</div></td>
    </tr>
    <tr>
    	<th>'.ORDER.': </th><td><div class="td_div_border">'.$data[0]["product"].'</div></td>
    </tr>
	<tr>
    	<th>'.GREETING_CARD_1.': </th><td><div class="td_div_border">'.strlen(trim($data[0]["greetings_card"])).'</div></td>
    </tr>
    <tr>
    	<th><a title="close" onclick="history.back();" style="cursor:pointer; color:#000000"><br>'.SIGNATURE.'</a></th><td>_______________________________</td>
    </tr>
    <tr>
        <th></th><td style="font-size:10px; color:grey;" align="right">'.OUR_ADDRESS.'</td><br>

    </tr>

</table>
</div>
';}else{
include_once("lang/language_am.php");
$data[0]['receiver_subregion'] = isset($data[0]['receiver_subregion']) ? $data[0]['receiver_subregion']: '';
$receiverAdd = getwayConnect::getwayData("SELECT * FROM delivery_subregion WHERE code = '{$data[0]['receiver_subregion']}'");
$data[0]['receiver_subregion'] = isset($receiverAdd[0]["name"]) ? $receiverAdd[0]["name"] : $data[0]['receiver_subregion'];
$data[0]["delivery_time"] = isset($data[0]["delivery_time"]) ? $data[0]["delivery_time"] : '';
$data[0]["delivery_time_manual"] = $data[0]["delivery_time_manual"];
if(!empty($data[0]["delivery_time_manual"])){$delivery_time = $data[0]["delivery_time_manual"];}else{$delivery_time = $data[0]["delivery_time"];}
if($userData[0]["username"] == "ani"){
    echo '

<div style="width:400px; float:right">

<table class="print_table table-hover" style="text-align:left;">

	<tr>
        <th ><img src="'.$rootF.'/template/icons/black-and-white-logo.jpg" width="150" height="60"/></th><td style="font-size:14px; font-weight:bold; padding-bottom:20px">'.ORDER_DETAILS.'<a title="Տպել" onClick="window.print();" target="_blank" style="cursor:pointer; color:#000000"><span style="float:right; font-size:18px">N-'. $data[0]["id"].'</span></a></td>
    </tr>
    <tr>
        <th>'.DELIVERY_DATE.'`</th><td><div class="td_div_border"><strong style="font-size:18px;">('.$delivery_time.')</strong>, '.$data[0]["delivery_date"].'</div></td>
    </tr>
    <tr>
        <th>'.ORDER_RECEIVER.'`</th><td><div class="td_div_border">'.$data[0]["receiver_name"].'</div></td>
    </tr>
    <tr><th>'.DELIVERY_ADDRESS.'`</th><td><div class="td_div_border">'.$data[0]["receiver_street"].', '.$data[0]["receiver_address"].'  ('.$data[0]["receiver_subregion"].' '.STATE.')</div></td></tr>
    <tr>
    	<th>'.RECEIVER_PHONE.'`</th><td><div class="td_div_border">'.$data[0]["receiver_phone"].'</div></td>
    </tr>
    <tr>
    	<th>'.SENDER.'`</th><td><div class="td_div_border">'.$data[0]["sender_name"].'</div></td>
    </tr>
    <tr>
    	<th>'.SENDER_ADDRESS_1.'`</th><td><div class="td_div_border">'.$data[0]["sender_region"].'</div></td>
    </tr>
    <tr>
    	<th>'.ORDER.'`</th><td><div class="td_div_border">'.$data[0]["product"].'</div></td>
    </tr>
	<tr>
    	<th>'.GREETING_CARD_1.'`</th><td><div class="td_div_border">'.strlen(trim($data[0]["greetings_card"])).'</div></td>
    </tr>
    <tr>
    	<th><a title="Փակել" onclick="history.back();" style="cursor:pointer; color:#000000"><br>'.SIGNATURE.'`</a></th><td>_______________________</td>
    </tr>
    <tr>
        <th></th><td style="font-size:10px; color:grey;" align="right">'.OUR_ADDRESS.'</td><br>

    </tr>

</table>
</div>
';
}else{

echo '

<div style="width:400px">

<table class="print_table table-hover" style="text-align:left;">

	<tr>
        <th ><img src="'.$rootF.'/template/icons/black-and-white-logo.jpg" width="150" height="60"/></th><td style="font-size:14px; font-weight:bold; padding-bottom:20px">'.ORDER_DETAILS.'<a title="Տպել" onClick="window.print();" target="_blank" style="cursor:pointer; color:#000000"><span style="float:right; font-size:18px">N-'. $data[0]["id"].'</span></a></td>
    </tr>
    <tr>
        <th>'.DELIVERY_DATE.'`</th><td><div class="td_div_border"><strong style="font-size:18px;">('.$delivery_time.')</strong>, '.$data[0]["delivery_date"].'</div></td>
    </tr>
    <tr>
        <th>'.ORDER_RECEIVER.'`</th><td><div class="td_div_border">'.$data[0]["receiver_name"].'</div></td>
    </tr>
    <tr><th>'.DELIVERY_ADDRESS.'`</th><td><div class="td_div_border">'.$data[0]["receiver_street"].', '.$data[0]["receiver_address"].'  ('.$data[0]["receiver_subregion"].' '.STATE.')</div></td></tr>
    <tr>
    	<th>'.RECEIVER_PHONE.'`</th><td><div class="td_div_border">'.$data[0]["receiver_phone"].'</div></td>
    </tr>
    <tr>
    	<th>'.SENDER.'`</th><td><div class="td_div_border">'.$data[0]["sender_name"].'</div></td>
    </tr>
    <tr>
    	<th>'.SENDER_ADDRESS_1.'`</th><td><div class="td_div_border">'.$data[0]["sender_region"].'</div></td>
    </tr>
    <tr>
    	<th>'.ORDER.'`</th><td><div class="td_div_border">'.$data[0]["product"].'</div></td>
    </tr>
	<tr>
    	<th>'.GREETING_CARD_1.'`</th><td><div class="td_div_border">'.strlen(trim($data[0]["greetings_card"])).'</div></td>
    </tr>
    <tr>
    	<th><a title="Փակել" onclick="history.back();" style="cursor:pointer; color:#000000"><br>'.SIGNATURE.'`</a></th><td>_______________________</td>
    </tr>
    <tr>
        <th></th><td style="font-size:10px; color:grey;" align="right">'.OUR_ADDRESS.'</td><br>

    </tr>

</table>
</div>
';
}
}
?>
<!-- initialize library-->
		<!-- Latest jquery compiled and minified JavaScript -->
		<script src="http://code.jquery.com/jquery-latest.min.js"></script>
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
</body>
</html>