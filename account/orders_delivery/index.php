<?php
session_start();
$pageName = "delivery";
$rootF = "../..";
include($rootF."/apay/pay.api.php");
include($rootF."/configuration.php");
set_time_limit(5);
page::cmd();

$access = auth::checkUserAccess($secureKey);
$allData = array();
$buildClient = "";
$uid = "";
$level = "";
$userData = "";
$cc = "am";
$levelArray = array();
$user_country = 0;
if(!$access){
	header("location:../../login");
}else{
	$uid = $_COOKIE["suid"];
	$level = auth::getUserLevel($uid);
	page::accessByLevel($level[0]["user_level"],$pageName);
	$levelArray = explode(",",$level[0]["user_level"]);
	$userData = auth::checkUserExistById($uid);
	$cc = $userData[0]["lang"];
    $user_country = $userData[0]["country_short"];
	if(is_file("lang/language_{$cc}.php"))
	{
		include("lang/language_{$cc}.php");	
	}else{
		include("lang/language_am.php");
	}
}
$op_price = ucfirst(strtolower($userData[0]["username"]));
$get_lvl = explode(',', $level[0]["user_level"]);
if(isset($_REQUEST['show_delivery'])){
	$op_price =  ucfirst(strtolower($_REQUEST['show_delivery']));
}
$root = true;
include("../flower_orders/products/engine/engine.php");
include("../flower_orders/products/engine/storage.php");
storage::$user_id = $userData[0]['id'];
if(isset($_SESSION['storage'])){
    storage::$selected_storage = $_SESSION['storage'];
}else{
    storage::$selected_storage = storage::get_default();
}
if(!storage::user_storage_enabled()){
    storage::$selected_storage = storage::get_default();
}
$engine = new engine();
$regionData = page::getRegionFromCC($cc);
date_default_timezone_set ("Asia/Yerevan");
$today_is = date('Y-m-d');
if(isset($_REQUEST['show_delivery_date'])){
	$today_is =  $_REQUEST['show_delivery_date'];
}
$count_price = getwayConnect::getwayData("SELECT SUM( dp.name ) as total_earn 
FROM  `rg_orders` AS ro,  `drive_prices` AS dp,  `delivery_drivers` AS dd, delivery_deliverer as del
WHERE ro.delivery_price = dp.id
AND del.name = '{$userData[0]["username"]}'
AND ro.deliverer =  del.id
AND ro.delivery_type = dd.id AND MONTH(ro.delivery_date) = MONTH('{$today_is}') AND YEAR(ro.delivery_date) = YEAR('{$today_is}') ");

$count_price = ($count_price[0]['total_earn']) ? $count_price[0]['total_earn'] : '0.00';

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

if(isset($_REQUEST['show_wiki']) && $_REQUEST['show_wiki']){
	$ord = getwayConnect::getwayData("SELECT rg_orders.receiver_street, delivery_street.coordinates FROM rg_orders RIGHT JOIN delivery_street on rg_orders.receiver_street=delivery_street.code  where rg_orders.id='{$_REQUEST['id']}'")[0];
	echo json_encode($ord);
	exit;
}
if(isset($_REQUEST['getorderDownloads']) && $_REQUEST['getorderDownloads']){
    $order_id = $_REQUEST['order_id'];
    $result = getwayConnect::getwayData("SELECT * FROM order_xml_download where order_id = '" . $order_id . "'");
    print json_encode($result);die;
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
if(isset($_REQUEST['get_stock_prods']) && $_REQUEST['get_stock_prods'] != ''){
    $street = getwayConnect::getwayData("SELECT * from jos_vm_product_stock_href LEFT JOIN orders_products_data ON jos_vm_product_stock_href.stock_product_id = orders_products_data.id where product_id = '{$_REQUEST['get_stock_prods']}'");
    print json_encode($street);
    exit;
}
$driverInfo;
$drivePricesArray = Array();
// if(max(page::filterLevel(40, $levelArray)) != 0 ){
    $driverInfo = getwayConnect::getwayData("SELECT * from delivery_deliverer where name = '" . $userData[0]['username'] . "'");
    $driverPrices = getwayConnect::getwayData("SELECT * from drive_prices");
    foreach($driverPrices as $key=>$value){
    	$drivePricesArray[$value['id']] = $value;
    }
// }
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
		<link rel="stylesheet" href="<?=$rootF?>/template/account/sidebar.css">
		<!-- Bootstrap minified CSS -->
		<link rel="stylesheet" href="<?=$rootF?>/template/bootstrap/css/bootstrap.min.css">
		<!-- Bootstrap optional theme -->
		<link rel="stylesheet" href="<?=$rootF?>/template/bootstrap/css/bootstrap-theme.min.css">
		<link rel="stylesheet" href="<?=$rootF?>/template/datepicker/css/datepicker.css">
		<link rel="stylesheet" href="<?=$rootF?>/template/rangedate/daterangepicker.css" />
		<link rel="stylesheet" href="index.css?v=1" />
		<style type="text/css">
			@media (min-width: 1200px){
				.container{
					width:1800px;
				}
			}
			.productDesc{
				color: gray;
			}
			.moodBtn, .mood {
				max-height: 30px;
				max-width: 30px;
			}
			.nextActionBtn, .action {
				max-height: 30px;
				max-width: 30px;
			}
			@media all and (max-width: 600px) {
				.nextActionBtn {
					margin-top: 22px;
				}
				.moodBtn {
					margin-top: 22px;
				}
			}
			.rightAddress {
				visibility: hidden;
				max-width: 60%;
				display: inline-block;
				margin-left: 15px;
			}
			.selectedMood {
				background: grey;
				padding: 3px;
				margin-right: 3px;
			}
			.selectedNextAction {
				background: grey;
				padding: 3px;
				margin-right: 3px;
			}
			.reason_icon {
				max-width: 35px;
				max-height: 34px;
			}
			.check_reminder {
				color: red;
			}
			.pekIconRed{
	            width: 35px;
	            height: 35px;
	            margin-left:10px;
			}
			.pekIconRed:hover{
				cursor:pointer;
			}
		</style> 
		<title>Flower Orders</title>
	</head>
	<body>
		<nav class="navbar navbar-inverse navbar-fixed-top">
		<div class="container">
		  <div class="navbar-header">
			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
			<span class="sr-only">Toggle navigation</span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
		      </button>
		    <a class="navbar-brand" href="#">RG-SYSTEM / <?=strtoupper($userData[0]["full_name_am"]);?></a>
		  </div>
		  <div id="navbar" class="navbar-collapse collapse" aria-expanded="false">
		    <ul class="nav navbar-nav">
			    <?php
				if (strtolower($userData[0]["username"]) == "hovik" || strtolower($userData[0]["username"]) == "norik") { 
				
				} else {
				?>
					<li><a href="#price" id="totalPrice">AMD:<?=$count_price;?></a></li>	
			    <?php 
				} 
				echo page::buildMenu($level[0]["user_level"]);
				$fData = page::buildFilter($level[0]["user_level"],$pageName);
				for($fi = 0 ; $fi < count($fData);$fi++){
					echo "<li>{$fData[$fi][1]}</li>";
				}
			  ?>
			  
			</ul>
		      </li>
		    </ul>
		  </div><!--/.nav-collapse -->
		</div>
	      </nav>
		<ol class="breadcrumb" id="activeFilters" style="position:fixed;top:51px;width: 100%">
		 
		</ol>
		<?php
			if(!empty(array_intersect(array(49,99),explode(',',$level[0]["user_level"]))))
			{
		?>
				<div style="float: right;font-size:20px;padding-left:50px;" ><br><a href="../flower_orders/products/" target="_blank">Ապրանքներ</a>&nbsp;&nbsp;&nbsp;</div>
				<?php
                $shuka = $engine->categoryDanger(1,false,storage::$user_id,storage::$selected_storage);
                $arevtur = $engine->categoryDanger(1,true,storage::$user_id,storage::$selected_storage);
				$date = $engine->getLastEdit(false);
				$passed = strtotime($engine->dateutc()) - strtotime($date);
				$hours = round($passed/3600);
				$minutes = round($passed/60);
				$par = "";
				if($hours > 5)
				{
					$par =  "<img src=\"../flower_orders/products/template/images/par-par.gif\" align=\"left\" height=\"50\">";
				}
				?>
				<div style="float: right;font-size:10px;padding-left:50px;" ><br><?=($shuka > 5 && $shuka < 10 || $arevtur > 5 && $arevtur < 10)? "<img src=\"../flower_orders/products/template/images/warning-white.gif\"  align=\"left\" height=\"50px\">":"<img src=\"../flower_orders/products/template/images/warning.gif\" align=\"left\" height=\"50px\">"?>
				<strong style="font-size:20px">Շուկա(<strong style="color:<?=($shuka <= 0)?"green":"red";?>;"><?=$shuka?></strong>)
				Առևտուր(<strong style="color:<?=($arevtur <= 0)?"green":"red";?>;"><?=$arevtur?></strong>)<?=$par?></strong>
				<br>
				<strong style="font-size:12px">Փոփոխվել է<?=($minutes > 60 ) ? " {$hours} ժամ առաջ:" : " {$minutes} րոպե առաջ:";?></strong>
				</div>				
			<?php
				}
			?>
		<div class="container" style="margin-top:85px;width: 100%">
			<div id="incomplited" style="font-size: 24px;font-weight: bold;">
			<?php
				if(max(page::filterLevel(3,$levelArray)) >= 43)
				{
			?>
				<button onclick="sendMail()">SEND MAIL</button>
			<?=(getwayConnect::getwayCount("SELECT * FROM rg_orders WHERE delivery_status = 2") > 0) ? "<strong style=\"color:#ff0000\">".getwayConnect::getwayCount("SELECT * FROM rg_orders WHERE delivery_status = 2")."</strong>" : 0;?><img src="<?=$rootF;?>/template/icons/status/2.png">
			<?php }?>	
			</div>
			<?php
					if(in_array(99,$levelArray))
					{
				?>
					<form action="" method="GET">
						<input name="show_delivery" type="text" placeholder="username" value="<?=(isset($_REQUEST['show_delivery'])) ? $_REQUEST['show_delivery'] : '';?>">
						<input addon="date" name="show_delivery_date" type="text" placeholder="date" value="<?=(isset($_REQUEST['show_delivery_date']))? $_REQUEST['show_delivery_date'] : '';?>"><button>SHOW</button>
					</form>
			<?php }?>	
			
			<button name="adf" id="17" onclick="filter(this,true);" value="<?=date("Y-m-d", time() - 86400);?>"><?=YESTERDAY;?></button>
			<button name="adf" id="17" onclick="filter(this,true);" value="<?=date("Y-m-d");?>"><?=TODAY;?></button>
			<button name="adf" id="17" onclick="filter(this,true);" value="<?=date("Y-m-d", time() + 86400);?>"><?=TOMORROW;?></button>
			<button type="button" class="btn btn-default" id="showRelatedImages" data-clicked="0">
				Ցուցադրել նկարներով
			</button>
			<div class="table">
			<table  class="table table-bordered">
			  <thead>
			    <tr class="success">
				
			       
					
					<th><div id="loading"><img src="<?=$rootF;?>/template/icons/loader.gif"></div>#<strong id="onC"></strong></th>
			        <th><?=DELIVERY_DAY;?></th>
				<th><?= (empty(array_intersect(array(10), explode(",", $level[0]["user_level"])))) ? ((defined('ORDERED_PRODUCTS')) ? ORDERED_PRODUCTS : 'ORDERED_PRODUCTS') : ((defined('TO_MEET')) ? TO_MEET : 'TO_MEET'); ?></th>
				<th><?= (empty(array_intersect(array(10), explode(",", $level[0]["user_level"])))) ? ((defined('RECEIVER_ADDRESS')) ? RECEIVER_ADDRESS : 'RECEIVER_ADDRESS') : ((defined('TO_WHERE')) ? TO_WHERE : 'TO_WHERE'); ?></td>
				<th><?= (empty(array_intersect(array(10), explode(",", $level[0]["user_level"])))) ? ((defined('ORDER_RECEIVER')) ? ORDER_RECEIVER : 'ORDER_RECEIVER') : ((defined('WHERE_TO_BE')) ? WHERE_TO_BE : 'WHERE_TO_BE'); ?></th>
				<th><?=ORDER_SENDER;?></th>
			    </tr>
			  </thead>
			  <tbody id="dataTable">
			    <!--data table-->
			  </tbody>
			</table>
		      </div>
		<div class='total_delivery_price'></div>
			<nav style="width: 100%;text-align: center;">
				<ul class="pagination" id="buildPages">
				  <li class="active"><a href="#">1</a></li>
				</ul>
		        </nav>
		</div>
		<input type='hidden' value='<?php echo print json_encode($drivePricesArray) ?>' class='drivePricesArray'>
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
        <script src="<?=$rootF?>/template/js/imagelightbox.min.js"></script>
		
		<script>
			var timoutSet = null;
			var data ={};
			var send_data = "";
			var data_type = "flower";
			var fromP = 0;
			var toP = 570;
			var drive_prices = <?=page::getJsonData("drive_prices");?>;
			var delivery_reciever = <?=page::getJsonData("delivery_receiver");?>;
			var drivers = <?=page::getJsonData("delivery_deliverer",false,true);?>;
            var driver_name = <?=page::getJsonData("delivery_deliverer");?>;
			var timeType = <?=page::getJsonData("delivery_time");?>;
			var subregionType = <?=page::getJsonData("delivery_subregion","code");?>;
			var streetType= <?=page::getJsonData("delivery_street","code");?>;
			var statusTitle = <?=page::getJsonData("delivery_status");?>;
			var driver_id = (drivers['<?=(strtolower($userData[0]["username"]));?>']) ? drivers['<?=(strtolower($userData[0]["username"]));?>'] : 3;

			var currentdate = new Date(); 
    		var current_datetime = currentdate.getFullYear() + "-"
                + (currentdate.getMonth()+1)  + "-" 
                + currentdate.getDate() + " "  
                + currentdate.getHours() + ":"  
                + currentdate.getMinutes() + ":" 
                + currentdate.getSeconds();
            var status_xml_approve_array = [1,6,3,7,11,12,13];
    		var payment_type_array = [15,13,12,11,16,23,5];
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
		                        for(var i = 0 ; i < resp.length ;i++){
		                            html+= '<li> ' + resp[i].downloaded_datetime +  ' </li>'
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
			data["ccf"] = {"filter":7,"value":<?=($user_country > 0) ? $user_country : $regionData['id'];?>};
			if ('<?=(in_array(49,explode(',',$level[0]["user_level"])))? 49 : 0;?>' < 48) {
				
				data["drvfltr"] = {"filter":25,"value":driver_id<?=(strtolower($userData[0]["username"]) == 'mushegh') ? '+",2"' : ((strtolower($userData[0]["username"]) == 'norik') ? '+",28"': '');?>};
			} else if(<?= $userData[0]['id'] == 27 ? "true": 'false' ?>) {
				data["drvfltr"] = {"filter":25,"value":driver_id};
			}
			if(<?=(int)(in_array(48,explode(',',$level[0]["user_level"])))?>){
				data["sellpointf"] = {"filter":19,"value":"1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,23,24,25,26,27,28,29,30,31,32,33,34,35,36"};
			}
			function firstToUpperCase( str ) {
				return str.substr(0, 1).toUpperCase() + str.substr(1);
			}
			// Added By Hrach
		    $(document).on('click',".img_for_stock_prods",function(){
		        var prod_id = $(this).data('prod-id');
		        var order_id = $(this).data('order-id');
		        $.ajax({
		            type: 'post',
		            url: location.href,
		            data: {
		                get_stock_prods: prod_id
		            },
		            success: function(resp){
		                resp = JSON.parse(resp);
		                if ( resp.length > 0 ){
		                    $(".div_for_stock_prods_" + order_id + "_" +prod_id).removeClass('hidden')
		                    var html = '';
		                    for( var i = 0 ; i < resp.length ; i++ ){
		                        html += "<b>" + resp[i]['product_name'] + " - " + resp[i]['count'] + " հատ </b><br>";
		                    }
		                    $(".div_for_stock_prods_" + order_id + "_" +prod_id).empty();
		                    $(".div_for_stock_prods_" + order_id + "_" +prod_id).append(html);
		                }
		                else{
		                    $(".div_for_stock_prods_" + order_id + "_" +prod_id).empty();
		                    $(".div_for_stock_prods_" + order_id + "_" +prod_id).append('<b>Բաղադրություն չգտնվեց</b>');
		                }
		            }
		        })
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
			data["orderF"] = {"filter":12,"value":"delivery_time ASC"};
			data["adf"] = {"filter":17,"value":"<?=date("Y-m-d");?>"};
			function filter(el,onfilter) {
				$("#loading").css("display","block");
				if (onfilter) {
					fromP = 0;
					if(data["adf"]){
					 //data["orderF"] = {"filter":12,"value":"delivery_time ASC"};
					}
					if(data["drf"]){
						//data["orderF"] = {"filter":12,"value":"delivery_date DESC"};
					}
				}
				if (el) {
					if (!el.value || el.value == null || el.value == "") {
						delete data[el.name];
					}else{
						//data.push([el.id] = el.value;
						data[el.name] = {"filter":el.id,"value":el.value};
					}
				}
				if (onfilter) {
					if(data["orderF"]){
						if(data["orderF"].value.search(/ASC/g) > 0)
						{
							$("[id="+data["orderF"].filter+"]").each(function(){
								if($(this).val() == data["orderF"].value)
								{
									var TempValue = $(this).val();
									TempValue = TempValue.replace(/ASC/g,"DESC");
									$(this).val(TempValue);
									console.log($(this).val());
								}
							});
						}
						if(data["orderF"].value.search(/DESC/g) > 0)
						{
							$("[id="+data["orderF"].filter+"]").each(function(){
								if($(this).val() == data["orderF"].value)
								{
									var TempValue = $(this).val();
									TempValue = TempValue.replace(/DESC/g,"ASC")
									$(this).val(TempValue);
									console.log($(this).val());
								}
							});
						}
						
					}
				}
				var activeFilter = "";
				var mu;
				for(mu in data)
				{
					if($("#"+data[mu].filter).attr("placeholder"))
					{
						activeFilter += "<li class=\"active\">"+$("#"+data[mu].filter).attr("placeholder")+":"+data[mu].value+"</li>";
					}else if($("#"+data[mu].filter).find(":selected").text()){
						activeFilter += "<li class=\"active\">"+$("#"+data[mu].filter).find(":selected").text()+"</li>";
					}else if($("#"+data[mu].filter).text()){
						//activeFilter += "<li class=\"active\">"+$("#"+data[mu].filter).text()+"</li>";
					}
				}
				$("#activeFilters").html(activeFilter);
				var data_encode = base64_encode(json_encode(data));
				if (data) {
					send_data = "&encodedData="+data_encode;
				}else{
					send_data = "";
				}
				var userFriendly = "class=\"active\"";
				var first = false;
				clearTimeout(timoutSet);
				timoutSet = setTimeout(function(){
				//start
				var drivePricesArray = $(".drivePricesArray").val();
				drivePricesArray = drivePricesArray.slice(0,-1);
				drivePricesArray = JSON.parse(drivePricesArray);
				var drivePricesArrayNew = $.map(drivePricesArray, function(value, index) {
				    return [value];
				});
				var drivePriceLast = [];
				for(var j = 0 ; j < drivePricesArrayNew.length ; j++){
					drivePriceLast[drivePricesArrayNew[j][0]] = drivePricesArrayNew[j];
				}
				var total_delivery_price = 0;
				$.get("<?=$rootF?>/data.php?cmd=data&page="+data_type+send_data+"&paginator="+fromP+":"+toP, function (get_data){
					var CCo = 0;
					var tableData = get_data.data;
					var countP = get_data.count;
					fromP = buildPaginator(countP,fromP,toP);
					var htmlData = "";
					var showA = "";
					// console.log(get_data)
					setTimeout(function(){
						countDeliveryPrice();
					},4000)
					if (countP > 0) {
						for(var i = 0;i < tableData.length;i++)
						{
							var d = tableData[i];
							if(d.delivery_status == "13" || d.delivery_status == "12" <?=(!empty(array_intersect(array(49,99),explode(',',$level[0]["user_level"])))) ? '|| d.delivery_status == "11"' : '';?> || d.delivery_status == "7" || d.delivery_status == "6" || d.delivery_status == "1" || d.delivery_status == "3" ){
							
							if (first) {
								showA = userFriendly;
								first = false;
							}else{
								showA = "";
								first = true;
							}
							
							var co = 0;
							var monthNames =  new Array("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");
							
							var myDate = d.delivery_date.split("-"); 
							if((myDate[0] + myDate[1] + myDate[2]) == 0)
							{
								var newDate = myDate[2]+"-"+myDate[1]+"-"+myDate[0];
							}else{
								var newDate = myDate[2]+"-"+monthNames[myDate[1]-1]+"-"+myDate[0];
							}
							
							htmlData += "<tr data-street='" + streetType[d.receiver_street]+ "' data-status='" + d.delivery_status + "' data-id='"+d.id+"' "+showA+">";
                                var driverN = (driver_name[d.deliverer]) ? driver_name[d.deliverer] : '';

                                var delvrr = (d.deliverer > 0) ? "<img width=\"40px\" style=\"position:absolute;right:0;top:0;z-index:1;\" src=\"<?=$rootF?>/template/icons/drivers/"+d.deliverer+".png\" title=\""+driverN+"\">" : '';
							//#1
							var timeToDiff = '';
							var car_color = 'none';
							// console.log(d);
							var yellow_ush = false;
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
                            		var timeToDiff = yearMonthDateFormat(timeToDiff);
									var timeDiff = (new Date(timeToDiff).getTime() - new Date(d.delivered_at).getTime());
									var minDiff =   Math.floor((timeDiff % 86400000) / 3600000) * 60 + Math.round(((timeDiff % 86400000) % 3600000) / 60000);
									if(minDiff > 30) {
										car_color = "green";
									} else if (minDiff <= 30 && minDiff >= 0){
										car_color = "yellow";
									} else if(minDiff < 0) {
										car_color = "red";
										yellow_ush = true;
										console.log(car_color + " " + d.id + " " + yellow_ush);
									}
								}
							}
							htmlData += "<td style=\"min-width:50px;\" nowrap>N-"+d.id+"<br/><img src=\"<?=$rootF?>/template/icons/status/"+d.delivery_status+".png\" title=\""+statusTitle[d.delivery_status]+"\"><br/><div style=\"position:relative;max-width:90px;\"><img style='border: 1px solid "+car_color+"' src=\"<?=$rootF?>/template/icons/deliver/"+d.delivery_type+".png\">"+delvrr+"</div>";
							if (yellow_ush == true){
								htmlData += "<p class='' style='color:black!important;background:yellow;font-size:11px;margin:0 0 0px'>Ուշացած...</p>";
							}
								htmlData += "<span class='choosed_delivery_price'>"+ parseFloat(drive_prices[d.delivery_price])+"</span> Դրամ";
								htmlData += "</td>";
							
							if(!timeType[d.delivery_time])
							{
								timeType[d.delivery_time] = "";
							}
							window.bacik = '';
							if (d.greetings_card){
								window.bacik = "<div class=\"article\"  style='display: inherit'><button data-order-id='" + d.id + "' class=\"readMoreButtonAjaxGreetingCard\"><img width=\"15px\" height=\"15px\" src=\"ico/greeting.png\"/> "+d.greetings_card+"</button><div class='display-none forAjaxGreetingCardValue_" + d.id + "'><span></span></div></div>";
							}

							window.nflorist = '';
							if (d.notes_for_florist){
								window.nflorist = "<div class=\"article\" style='display: inherit' ><button data-order-id='" + d.id + "' class=\"readMoreButtonAjaxFloristNote\"><img width=\"15px\" height=\"15px\" src=\"ico/notes.png\"/> "+d.notes_for_florist+"</button><div class='display-none forAjaxFloristNoteValue_" + d.id + "'><span style='color:red'></span></div></div>";
							}
                                // var zoomimage =(d.image_exist > 0) ? "<br><button style=\"background: none;border: 0;\" onclick=\"zoom_img("+d.id+")\"><img src=\"<?=$rootF?>/template/icons/zoom.png\"/></button>" : '';
                                var zoomimage = "<img src='<?=$rootF?>/template/icons/zoom.png' class='showChoosenRelated' data-clicked=0 data-status='"+d.delivery_status+"' data-id='"+d.id+"' title='Նկարներով'>"
                            var taxIconRed = '';
                            if(($.inArray(d.delivery_status, status_xml_approve_array) !== -1 && $.inArray(d.payment_type, payment_type_array) !== -1 && d.delivery_region == 1) || d.sell_point == 44 ){
                                taxIconRed += "<img src='../../template/images/pek.png' class='pekIconRed showHideUploadedTimeXML' data-order-id='" + d.id + "'>";
                                taxIconRed += "<div><ul class='display-none textShowUploadedTime_" + d.id + "'></ul></div>";
                            }
							htmlData += "<td>"+newDate+"<br/>"+timeType[d.delivery_time];
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
							if(d.delivery_reason != undefined){
								htmlData += '<br>';
								if(d.delivery_reason == 1){
									htmlData += "<img src='./ico/love.png' class='reason_icon'>"
								} else if(d.delivery_reason == 9){
									htmlData += "<img src='./ico/sqo.png' class='reason_icon'>"
								} else if([2, 4, 10, 12, 15, ].indexOf(d.delivery_reason) > -1){
									htmlData += "<img src='./ico/birthday.png' class='reason_icon'>"
								}
							}
							var flourist_delviery_status_display = Array(12,3,6,7);
                            if(flourist_delviery_status_display.includes(d.delivery_status)){
                                htmlData += "<span style='color:red' class='flourist_name'><img title='" + d.flourist + "' src='../../template/images/florists/"  + d.flourist_id +  ".gif'></span>";
                            }
							htmlData += zoomimage+taxIconRed+bacik+nflorist+"<br/></td>";
							var actiondata = "";
							
							
							htmlData += "<td>"+d.product+actiondata;
							
							htmlData += "<div class='related_images'></div>";
							htmlData += "<div class='product_images'></div>";
							htmlData += "<div class='out_images'></div>";
							htmlData += "</td>";

							actiondata = "";
							
							var drv_st1 = "";
							var drv_st2 = "";
							if (d.delivery_status == '13') {
								drv_st1 = "checked";
							}else if (d.delivery_status == '11'){
								drv_st2 = "checked";
							}
							if(d.delivery_status != "3" && d.delivery_status != "6"){
								//console.log(d.delivery_status);
								
								actiondata += "<p style=\"margin-top:5px;\"><span style=\"display: inline-block;margin-left:15px;\"><label ><input type=\"radio\" name=\"driver_status-"+d.id+"\" onclick=\"onit("+d.id+")\" value=\"13\" "+drv_st1+"/><img width=\"25px\" src=\"ico/ok.png\"/><?=DRV_CONFIRM;?></label><br>";
								actiondata += "<label ><input type=\"radio\" name=\"driver_status-"+d.id+"\" onclick=\"refuse("+d.id+")\"  value=\"11\" "+drv_st2+"/><img width=\"25px\" src=\"ico/refuse.png\"/><?=DRV_REFUSE;?></label></span></p>";
							}
							if(!subregionType[d.receiver_subregion])
							{
								subregionType[d.receiver_subregion] = d.receiver_subregion;
							}
							if(!streetType[d.receiver_street] && streetType[d.receiver_street] != ""){
								streetType[d.receiver_street] = d.receiver_street;
							}
							htmlData += "<td>";
							if(d.payment_type != undefined && d.payment_type == 12){
								htmlData += " <span class='check_reminder'>";
							}
							htmlData += streetType[d.receiver_street]+" "+d.receiver_address;
							if(d.receiver_floor != '' && d.receiver_floor != null){
								htmlData += "<br><?=(defined('RECEIVER_FLOOR')) ? RECEIVER_FLOOR : 'բնակարան՝';?>  " + d.receiver_floor  + ",";
							}
							if(d.receiver_entrance != '' && d.receiver_entrance != null){
								htmlData += "<br><?=(defined('RECEIVER_ENTRANCE')) ? RECEIVER_ENTRANCE : 'մուտք՝';?>  " + d.receiver_entrance + "-րդ,";
							}
							if(d.receiver_door_code != '' && d.receiver_door_code != null){
								htmlData += "<br><?=(defined('RECEIVER_DOOR_CODE')) ? RECEIVER_DOOR_CODE : 'դռան կոդ՝';?>  " + d.receiver_door_code + " ";
							}
							htmlData += "<br/>("+subregionType[d.receiver_subregion]+" <?=STATE;?>)";
							if(d.payment_type != undefined && d.payment_type == 12){
								htmlData += "<strong> ֊ (Դուրս գալ կտրոնով) </strong></span>";
							}
							htmlData += actiondata+"</td>";

							actiondata = "";
							if(d.delivery_status != "3" && d.delivery_status != "6"){
								if(d.delivery_status == "13"){
									actiondata += "<p style=\"margin-top:5px; display:inline-block;\"><span style=\"display: inline-block;vertical-align: text-bottom;\"><button onclick=\"onroad("+d.id+")\"><img width=\"75px\" src=\"ico/onroad.png\"/></button></span></p>";
								}
							}
								actiondata += "<span class='mapLink'></span>";	
							if(d.delivery_status == "6"){
								actiondata += "<div style=\"display:inline-block; margin-left: 5px;\">";
								if((d.delivery_price == 0 && <?=(max(page::filterLevel(4,$levelArray)) >= 49 ) ? "true" : "false";?>) || (d.delivery_price == 0 ) || '<?=(isset($level[0]) && in_array(99,explode(',',$level[0]["user_level"])))? 99 : 0;?>' > 98){
								//actiondata += ('<?=(isset($userData[0])) ? strtolower($userData[0]["username"]) : '';?>' == "hovik" && (d.sell_point == 13 || d.sell_point == 19)) ? " (Anahit.am)" : '';

								actiondata += "<select style=\"max-width:90px \"  id=\"actualReceiver-"+d.id+"\" onchange=\"enable_price("+d.id+")\">";
								actiondata += "<option value=\"0\"><?=SELECT_ACTUAL_RECEIVER;?></option>";
								actiondata += '<?=trim(preg_replace('/\s+/', ' ', page::buildOptions("delivery_receiver")));?>';
								actiondata += "</select>";
								}

								if((<?=(max(page::filterLevel(4,$levelArray)) >= 49 ) ? "true" : "false";?>) || '<?=(in_array(99,explode(',',$level[0]["user_level"])))? 99 : 0;?>' > 98 || (<?= (strpos($userData[0]['user_level'], '40') !== false) ? "true":"false"?>) || (<?= (strpos($userData[0]['user_level'], '41') !== false) ? "true":"false"?>)){
									// var delivery_first_time = '';
									// if(d.travel_time_end){
									// 	delivery_first_time = d.travel_time_end;
									// 	delivery_first_time+= ":00";
									// }
									// else{
									// 	if(d.delivery_time){
									// 		delivery_first_time = timeType[d.delivery_time].split('-')[1];
									// 		if (delivery_first_time.indexOf('.') > -1)
									// 		{
									// 			delivery_first_time = delivery_first_time.replace('.',":");
									// 		}
									// 		delivery_first_time+= ":00";
									// 	}
									// }
									// var timeDiff = (new Date(current_datetime.split(' ')[0] + " " + delivery_first_time).getTime() - new Date(d.changed_status_by_driver_date).getTime());

         //    						var minDiff =   Math.floor((timeDiff % 86400000) / 3600000) * 60 + Math.round(((timeDiff % 86400000) % 3600000) / 60000);
									// if(delivery_first_time != ''){
										<?php
											// if($userData[0]['username'] != 'Hovik'){
												if($driverInfo[0]['delivery_price'] == 1){
													?>

														// if(minDiff < 20){
															actiondata += " <select style=\"max-width:90px;\"  id=\"drivePrice-"+d.id+"\">";
															actiondata += "<option value=\"0\">Գին</option>";
															for(var key = 0 ; key < drivePricesArrayNew.length ; key++){
																if(drivePricesArrayNew[key]['id'] != 4){
																	if(drivePricesArrayNew[key]['id'] == d.delivery_price){
																		actiondata+= "<option selected value='" + drivePricesArrayNew[key]['id'] + "'>" + (drivePricesArrayNew[key]['name']).toLocaleString() + "</option>";
																	}
																	else{
																		actiondata+= "<option value='" + drivePricesArrayNew[key]['id'] + "'>" + (drivePricesArrayNew[key]['name']).toLocaleString() + "</option>";
																	}
																}
															}
															actiondata += "</select>";						
														// }
													<?php
												}
												else{
													?>
														actiondata+= "<input type='hidden' value='3' id=\"drivePrice-"+d.id+"\">"
													<?php
												}
												// else{
													?>
														// if(minDiff > 20){
															// actiondata += " <select style=\"max-width:90px;\"  id=\"drivePrice-"+d.id+"\">";
															// actiondata += "<option value=\"0\">Գին</option>";
															// for(var key = 0 ; key < drivePricesArrayNew.length ; key++){
															// 	if(drivePricesArrayNew[key]['id'] != 4){
															// 		if(drivePricesArrayNew[key]['id'] == d.delivery_price){
															// 			actiondata+= "<option selected value='" + drivePricesArrayNew[key]['id'] + "'>" + (drivePricesArrayNew[key]['name']).toLocaleString() + "</option>";
															// 		}
															// 		else{
															// 			actiondata+= "<option value='" + drivePricesArrayNew[key]['id'] + "'>" + (drivePricesArrayNew[key]['name']).toLocaleString() + "</option>";
															// 		}
															// 	}
															// }
															// actiondata += "</select>";
														// }
														// else{
														// 	actiondata += " <select style=\"max-width:90px;\"  id=\"drivePrice-"+d.id+"\">";
														// 	actiondata += "<option value=\"4\">-100</option>";
														// 	actiondata += "</select>";
														// }
																						
													<?php
												// }
											// }
										?>
									// }
								}
								
								if(d.delivery_status != "3" && d.delivery_status != "6"){
									actiondata += '<img src="./ico/mood_1.png" class="moodBtn '+ ( (d.receiver_mood == 1) ? 'selectedMood': '') +'" data-mood="1">';
									actiondata += '<img src="./ico/mood_2.png" class="moodBtn '+ ( (d.receiver_mood == 2) ? 'selectedMood': '') +'" data-mood="2">';
									actiondata += '<img src="./ico/mood_3.png" class="moodBtn '+ ( (d.receiver_mood == 3) ? 'selectedMood': '') +'" data-mood="3">';
									actiondata += '<img src="./ico/mood_4.png" class="moodBtn '+ ( (d.receiver_mood == 4) ? 'selectedMood': '') +'" data-mood="4">';
									actiondata += '<input type="hidden" id="receiver_mood" value="'+d.receiver_mood+'">';

									actiondata += '<img src="/template/icons/1.png" data-order-id="' + d.id + '" class="nextActionBtn '+ ( (d.next_action == 1) ? 'selectedNextAction': '') +'" data-next-action="1" style="width:43px" title="Office">';
									actiondata += '<img src="/template/icons/2.png" data-order-id="' + d.id + '" class="nextActionBtn '+ ( (d.next_action == 2) ? 'selectedNextAction': '') +'" data-next-action="2" style="width:43px;margin-left:5px;margin-right:5px;" title="Ավարտ">';
									actiondata += '<img src="/template/icons/3.png" data-order-id="' + d.id + '" class="nextActionBtn '+ ( (d.next_action == 3) ? 'selectedNextAction': '') +'" data-next-action="3" style="width:43px" title="Հաջորդ պատվեր">';
									actiondata += '<input type="hidden" id="next_action" value="'+d.next_action+'">';
									actiondata += '<div class="nextOrderId' + d.id + '" style="display:inline-block"></div>';

									actiondata += "<p style=\"margin-top:34px; display:inline-block;\"><button onclick=\"send_drivedata("+d.id+")\"><?=SUBMIT_DELIVERED;?><img width=\"25px\" src=\"ico/ok.png\"/></button></p>";
								}else{
									actiondata += '<img src="./ico/mood_1.png" class="moodBtn '+( ( d.receiver_mood == 1 ) ? 'selectedMood': '' ) +'" data-mood="1">';
									actiondata += '<img src="./ico/mood_2.png" class="moodBtn '+( ( d.receiver_mood == 2 ) ? 'selectedMood': '' ) +'" data-mood="2">';
									actiondata += '<img src="./ico/mood_3.png" class="moodBtn '+( ( d.receiver_mood == 3 ) ? 'selectedMood': '' ) +'" data-mood="3">';
									actiondata += '<img src="./ico/mood_4.png" class="moodBtn '+( ( d.receiver_mood == 4 ) ? 'selectedMood': '' ) +'" data-mood="4">';
									actiondata += '<input type="hidden" id="receiver_mood" value="'+d.receiver_mood+'">';

									actiondata += '<img src="/template/icons/1.png" data-order-id="' + d.id + '" class="nextActionBtn '+ ( (d.next_action == 1) ? 'selectedNextAction': '') +'" data-next-action="1" style="width:43px" title="Office">';
									actiondata += '<img src="/template/icons/2.png" data-order-id="' + d.id + '" class="nextActionBtn '+ ( (d.next_action == 2) ? 'selectedNextAction': '') +'" data-next-action="2" style="width:43px;margin-left:5px;margin-right:5px;" title="Ավարտ">';
									actiondata += '<img src="/template/icons/3.png" data-order-id="' + d.id + '" class="nextActionBtn '+ ( (d.next_action == 3) ? 'selectedNextAction': '') +'" data-next-action="3" style="width:43px" title="Հաջորդ պատվեր">';
									actiondata += '<input type="hidden" id="next_action" value="'+d.next_action+'">';
									actiondata += '<div class="nextOrderId' + d.id + '" style="display:inline-block"></div>';

									actiondata += "<p style=\"margin-top:34px; display:inline-block;\"><button onclick=\"send_drivedata("+d.id+")\">Փոփոխել</button></p>";
								}

								if(drivePriceLast[d.delivery_price]){
									actiondata += " <b style='color:red'>" + drivePriceLast[d.delivery_price]['name'] + "</b> ";
								}
								console.log(d.delivery_price)
								if(d.delivery_price != 0 && d.delivery_price > 2){
									total_delivery_price = total_delivery_price + parseInt(drivePriceLast[d.delivery_price]['name']);
								}
								actiondata += "</div>";
							}
							actiondata += '<br>';
							htmlData += "<td>"+d.receiver_name+"<br>("+d.receiver_phone+")"+actiondata;
							htmlData += "<label for='wrongAddress" + d.id + "'>Սխալ հասցե  </label>"
							htmlData += "<input type='checkbox' data-id='"+d.id+"' class='wrongAddress' id='wrongAddress" + d.id + "'";
							if(d.right_address.length > 0){
								htmlData += "checked='checked'";	
							}
							htmlData += ">";
							htmlData += '<input type="text" class="rightAddress form-control" value="'+d.right_address+'"';
							if(d.right_address.length > 0){
								htmlData += 'style="visibility: visible;"';	
								htmlData += '>';
								htmlData += '<button type="button" class="saveRightAddress" data-id="'+d.id+'">Պահպանել</button>';
							} else {
								htmlData += '>';
								htmlData += '<button type="button" style="visibility: hidden;" class="saveRightAddress" data-id="'+d.id+'">Պահպանել</button>';
							}
							htmlData += "</td>";
							
							actiondata = "";
							if(d.sell_point == "22")
							{
							//#12
								htmlData += "<td>&nbsp;</td>";
							} else {
								htmlData += "<td>";
								if(d.anonym == 0){
									htmlData += d.sender_name+"<br>("+d.sender_region+")";
								}
								htmlData += "<hr>";
								if(d.delivery_price > 0){
									<?php if (isset($userData[0]) && strtolower($userData[0]["username"]) == "hovik" || strtolower($userData[0]["username"]) == "norik") { ?>
										htmlData += "<br>Ստացող:"+delivery_reciever[d.who_received];
									<?php }else { ?>
										htmlData += "<br>Ստացող:"+delivery_reciever[d.who_received];
									<?php } ?>
								}
								htmlData += "</td>";
							}
							
						   htmlData += "</tr>";
						  
							CCo++;
							countP = CCo;
							$("#shopCT").html(countP);
							}else{
								$("#shopCT").html(countP);
							}
						}

						$("#onC").html("("+countP+")");
					}
					$('#dataTable').html(htmlData);
					$("#loading").css("display","none");
				});
				//end
				
			},2000);
				return false;
			}
			setTimeout(function(){
				countDeliveryPrice();
			},4000)
			function yearMonthDateFormat(dateTime){
				dateTime = dateTime.split(' ')
				var dateD = dateTime[0].split('-');
				var result = dateD[2] + "-" + dateD[1] + " " + dateD[0] + " " + dateTime[1];
				return result;
			}
			function countDeliveryPrice(){
				var choosed_delivery_price = $(".choosed_delivery_price");
				var total_delivery_price_display = 0;
				if(choosed_delivery_price.length > 0){
					for(var i = 0; i < choosed_delivery_price.length;i++){
						total_delivery_price_display = total_delivery_price_display + parseInt($(choosed_delivery_price[i]).html());
					}
					$(".total_delivery_price").html("<b>Ցուցակի ընդհանուր առաքման գումար` " + total_delivery_price_display + "</b>");
				}
				else{
					$(".total_delivery_price").html("<b>Ցուցակի ընդհանուր առաքման գումար` 0</b>");
				}
				addWiki();
			}
			filter(null);
			$('#menuDrop .dropdown-menu').on({
				"click":function(e){
			      e.stopPropagation();
			    }
			});
			function addWiki(){
				$('#dataTable tr').each((ind, elem) => {
					let id = $(elem).attr('data-id');
					$.ajax({
						type: 'post',
						url: location.href,
						data: {
							show_wiki: true,
							id: id
						},
						success: function(resp){
							if(resp != null){
								let c_data = JSON.parse(resp);
								if(c_data != null && c_data['coordinates'] != null && c_data['coordinates'] != ''){
									let c_html = ' <a target="blank" href="https://www.google.com/maps/dir/Yervand+Kochar+Street,+Yerevan,+Armenia/'+c_data['coordinates']+'/@40.2000815,44.4699913,12z/data=!3m1!4b1!4m12!4m11!1m5!1m1!1s0x406abcf595178223:0xf074b89337f1809!2m2!1d44.5177361!2d40.1713366!1m3!2m2!1d45.18!2d40.38!3e0">Դիտել Ճանապարհը </a>';
									$(elem).find('.mapLink').html(c_html);	
								}
							}
						}
					})
				});
			}
			function showCount(el)
			{
				toP = el.value;
				filter(null,true);
			}
			$(document).on('click', "button.read-more", function() {
			
				var elem = $(this).parent().find(".text");
				if(elem.hasClass("short"))
				{
					elem.removeClass("short").addClass("full");
		    
				}
				else
				{
					elem.removeClass("full").addClass("short");
		    
				}
			});
			$(document).ready(function(){
				setTimeout(function(){
					$('#dataTable tr').each((ind, element) => {
						var id = $(element).attr('data-id');
						let $self = $(element);
						$.ajax({
							type: 'post',
							url: location.href,
							data: {
								id: id,
								checkImages: true
							},
							success: function(resp){
								let data = JSON.parse(resp);
								if(data != undefined && data.showImages != undefined){
									if(data.showImages == false){
										$self.find('.showChoosenRelated').css('display', 'none');
									}
								}
							}
						})
					});
				}, 3000);

			})
			$(document).on('click', "button.show-ALL", function() {
			
				var elem = $("div").find(".text");
				if(elem.hasClass("short"))
				{
					elem.removeClass("short").addClass("full");
		    
				}
				else
				{
					elem.removeClass("full").addClass("short");
		    
				}
			});

			function refuse(id){
				request_call('&id='+id+'&delivery_status=11&changed_status_by_driver_date='+current_datetime);
			}
			function onit(id){
				request_call('&id='+id+'&delivery_status=13&changed_status_by_driver_date='+current_datetime);
				alert('Շնորհակալություն, Դուք հաստատեցիք որ այս պատվերի առաքումը կարող եք կատարել նշված ժամանակահատվածում։');
			}
			function onroad(id){
				request_call('&id='+id+'&delivery_status=6');
			}
			function send_drivedata(id){
				if($('#receiver_mood').val() && $('#receiver_mood').val() != 0){
					let mood = $('#receiver_mood').val();
					let next_action = $('#next_action').val();
					let next_action_id = $('#next_action_id').val();
					var who_received = $('#actualReceiver-'+id).val();
					var drive_price = $('#drivePrice-'+id).val();
					if($('#drivePrice-'+id).val() != 0 && $('#actualReceiver-'+id).val() != 0 && $('#next_action').val()){
						var selectedNextAction = $(".selectedNextAction");
						if(selectedNextAction.length == 0){
							alert('Նշեք հաջորդ քայլը!'); 
						}
						else{
							alert('Շնորհակալություն ձեր առաքման ծանուցումը գրանցվեց համակարգում!')

							$.ajax({
								type: "POST",
								url: "ajax.php",
								data: {
									"add_options_to_log": true,
									"order_id": id,
									"who_received": who_received,
									"drive_price": drive_price,
									"mood": mood,
									"next_action": next_action,
								},
								success: function(response){
									console.log(response)
								}
							})
							if($('#drivePrice-'+id).length){
								request_call('&id='+id+'&who_received='+$('#actualReceiver-'+id).val()+'&delivery_price='+$('#drivePrice-'+id).val()+"&delivery_status=3&receiver_mood="+mood+"&next_action="+next_action+"&next_action_id="+next_action_id);
							}
							else{
								request_call('&id='+id+'&who_received='+$('#actualReceiver-'+id).val()+"&delivery_status=3&receiver_mood="+mood+"&next_action="+next_action+"&next_action_id="+next_action_id);
							}
							$.get("ajax.php?update_total_earns=true", function (get_data){
							if(get_data.status && get_data.status == "ok"){
								$("#totalPrice").html('AMD:'+get_data.msg);
							}});
						}
					}else{
						alert('Նշեք բոլոր տվյալները'); 
					}
				} else {
					alert('Նշեք ստացողի տրամադրություն!');
				}
			}
			function enable_price(id){
				if($('#actualReceiver-'+id).val() != 0){
					// $('#drivePrice-'+id).removeAttr('disabled');
					// $('#drivePrice-'+id).css('visibility', 'visible');
				}else{
					$('#drivePrice-'+id).attr('disabled','disabled');
				}
			}
			function request_call(call_data){
				$.get("ajax.php?update_order=true"+call_data, function (get_data){
					if(get_data.status && get_data.status == "ok"){
						filter(null);
					}
				});
			}
			function buildPaginator(tCount,pfrom,pto){
				var htmlP = "";
				var pagesC = Math.ceil(tCount/pto);
				var vNum = 0;
				if (pagesC > 1) {
					for(var i = 0; i < pagesC; i++)
					{
						var pNum = i+1;
						
						if (vNum == pfrom) {
							htmlP += "<li class=\"active\"><a href=\"#\" onclick=\"return false;\">"+pNum+"</a></li>";	
						}else{
							htmlP += "<li ><a href=\"#\" onclick=\"loadData("+vNum+","+pto+");return false;\">"+pNum+"</a></li>";
						}
						vNum = pto+vNum;
					}
				}
				$("#buildPages").html(htmlP);
				return vNum;
			}
			function loadData(v1,v2)
			{
				fromP = v1;
				filter(null);
			}
			if ($('[addon="rangedate"]')) {
				$('[addon="rangedate"]').dateRangePicker({shortcuts : 
				{
					'prev-days': [3,5,7],
					'prev': ['week','month','year'],
					'next-days':null,
					'next':null
				}}).bind('datepicker-apply',function(){filter(this,true);});
			}
			if ($('[addon="date"]')) {
				$('[addon="date"]').datepicker({format: 'yyyy-mm-dd'}).on('changeDate',function(){
					//filter(this,true);
				});
			}
			function totalResset()
			{
				$("input[type=text]").each(function(){$(this).val('');});
				$("select").each(function(){$(this).val('');});
				$("#showCount").val("570");
				data ={};
				toP = 570;
				filter(null,this);
			}
			function sendMail()
			{
				var getMails = "";
				$("input:checkbox[id^='mailToSend']").each(function(){
					
					if($(this).is(":checked"))
					{
						getMails += $(this).val()+",";
					}
					if(!getMails)
					{	
						$(this).prop( "disabled", false );
					}
				});
				if(getMails)
				{
					window.open("mail/?mails="+getMails, "", "toolbar=yes, scrollbars=yes, resizable=yes,width=800, height=400");
				}
			}
			function CheckAccounting(orderId)
			{
				window.open("products/?cmd=check&orderId="+orderId, "", "toolbar=yes, scrollbars=yes, resizable=yes,width=970, height=600");
			}
			function selectAll(type)
			{
				$("input:checkbox[id^='mailToSend']").each(function(){
					
					if(type)
					{
						$(this).prop('checked', true);
					}else{
						$(this).prop('checked', false);
					}
				});
			}
			function checkAll(data)
			{
				if(data.checked)
				{
					selectAll(true);
				}else{
					selectAll();
				}
			}
			///image parser
            // function zoom_img(id){
            //     $.get("<?=$rootF?>/data.php?cmd=order_images&itemId="+id, function (get_data){
			// 		$('a[data-imagelightbox="' + id + '"]').remove();
            //         if(get_data.data.images) {
            //             if (!$('a[data-imagelightbox="' + id + '"]').length) {
            //                 var imd = get_data.data.images;
            //                 for(var u = 0; u < imd.length;u++){
            //                     $('body').append('<a href="../flower_orders/product_images/'+imd[u].image_source+'" data-imagelightbox="'+id+'" style="display:none"><img src="../flower_orders/product_images/'+imd[u].image_source+'" alt="'+imd[u].image_note+'"></a>');

            //                 }
            //             }

            //             var selectorF = 'a[data-imagelightbox="' + id + '"]';
            //             var instanceF = $(selectorF).imageLightbox(
            //                 {
            //                     quitOnImgClick: false,
            //                     onLoadStart: function() { captionOff(); activityIndicatorOn(); },
            //                     onLoadEnd:	 function() { captionOn(); activityIndicatorOff(); },
            //                     onEnd:		 function() { captionOff(); activityIndicatorOff(); }
            //                 });
            //             instanceF.switchImageLightbox( 0 );
            //         }else{
            //             alert('<?=(defined('NKAR_CHKA')) ? NKAR_CHKA : 'NKAR_CHKA';?>');
            //         }
            //     });
            // }
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
			$('body').on('click', '#showRelatedImages', function(){
				if($(this).attr('data-clicked') == 0){
					$('a[data-imagelightbox]').remove();
					$('.related_images').html('');
					$('.out_images').html('');
					$('.product_images').html('');
					$('a[data-imagelightbox]').remove();
					$(this).attr('data-clicked', 1);
					$('#dataTable tr').each( (ind, element) => {
						var id = $(element).attr('data-id');
						$.get("<?=$rootF?>/data.php?cmd=related_images&itemId=" + id, function (get_data) {
							if (!$('a[data-imagelightbox="' + id + '"]').length) {
								if (get_data.data.related_images) {
									var imc = get_data.data.related_images;
									var path = "../flower_orders/jos_product_images/";
									for (var u = 0; u < imc.length; u++) {
										$('body').append('<a href="'+path + imc[u].image_source + '" data-imagelightbox="' + id + '" style="display:none"><img src="'+path+ + imc[u].image_source + '" alt="' + imc[u].image_note + '"></a>');
										let relatedHtml = '';
										if(imc[u].for_purchase){
											relatedHtml = '<div class="col-sm-12" style="clear: both;border:4px solid red">';
										}
										else{
											relatedHtml = '<div class="col-sm-12" style="clear: both">';

										}
										relatedHtml += '<img onclick="zoom_img('+id+')" src="'+path + imc[u].image_source +'" alt="' + imc[u].image_note + '" style="width: auto; max-width:100px; height: 90px; float: left;">';
										relatedHtml += '<span>'+ imc[u].changed_name;
										<?php
											if(max(page::filterLevel(3, $levelArray)) >= 33)
											{
										?>
												relatedHtml += " ($"+Number(imc[u].price).toFixed(2)+")";
										<?php
											} 
										?>
										relatedHtml += '</span>';
										relatedHtml += '<br>';
										relatedHtml += '<span class="productDesc">'+imc[u].short_desc+'</span>';
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
										$('body').append('<a href="product_images/' + image_path + "/" + imd.images[u].image_source + '" data-imagelightbox="' + id + '" style="display:none"><img src="product_images/' + image_path + "/" + imd.images[u].image_source + '" alt="' + imd.images[u].image_note + '"></a>');
										otherProductHtml = '<div class="col-sm-12">';
										otherProductHtml += '<img onclick="zoom_img('+id+')" src="../flower_orders/product_images/'  + image_path + "/" +imd.images[u].image_source +'" style="width: auto; max-width:100px; height: 90px; float: left;">';
										otherProductHtml += '<span>'+imd.images[u].image_note+'</span>';
										<?php
											if(max(page::filterLevel(3, $levelArray)) >= 33)
											{
												?>
												otherProductHtml += " ($"+Number(imd.images[u].price).toFixed(2)+")";
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
										$(element).find('.out_images').append('<div class="col-sm-12"><img onclick="zoom_out('+id+')" src="../flower_orders/'+path + imd[u].fileName +'" style="width: auto; max-width:130px; height: 90px;"></div>');
									}
								}                        
							}
							
						});
					}) 
				} else {
					$(this).attr('data-clicked', 0);
					$('.related_images').html('');
					$('.out_images').html('');
					$('.product_images').html('');
					$('a[data-imagelightbox]').remove();
				}
			});
			$('body').on('click', '.showChoosenRelated', function(){
				let id = $(this).attr('data-id');
				let $self = $(this);
				if($(this).attr('data-clicked') == 0){
					$('a[data-imagelightbox="'+id+'"]').remove();
					$self.closest('tr').find('.related_images').html('');
					$self.closest('tr').find('.out_images').html('');
					$self.closest('tr').find('.product_images').html('');
					$self.attr('data-clicked', 1);
					$.get("<?=$rootF?>/data.php?cmd=related_images&itemId=" + id, function (get_data) {
						if (!$('a[data-imagelightbox="' + id + '"]').length) {
								if (get_data.data.related_images) {
									var imc = get_data.data.related_images;
									var path = "../flower_orders/jos_product_images/";
									for (var u = 0; u < imc.length; u++) {
										$('body').append('<a href="'+path + imc[u].image_source + '" data-imagelightbox="' + id + '" style="display:none"><img src="'+path+ + imc[u].image_source + '" alt="' + imc[u].image_note + '"></a>');
										let relatedHtml = '<div class="col-sm-12" style="clear: both;">';
										relatedHtml += '<img onclick="zoom_img('+id+')" src="'+path + imc[u].image_source +'" alt="' + imc[u].image_note + '" style="width: auto; max-width:100px; height: 90px; float: left;">';
										relatedHtml += '<span>'+ imc[u].changed_name;
										<?php
											if(max(page::filterLevel(3, $levelArray)) >= 33)
											{
										?>
												relatedHtml += " ($"+Number(imc[u].price).toFixed(2)+")";
										<?php
											} 
										?>
										relatedHtml += '</span>';
										relatedHtml += '<br>';
										relatedHtml += '<span class="productDesc">'+imc[u].short_desc+'</span><br>' + "<img src='../../template/icons/baxadrutyun.jpg' class='img_for_stock_prods' data-prod-id='" + imc[u].related_id + "' data-order-id='" + imc[u].id + "' style='height:30px;margin-left:7px;margin-bottom:10px' ><div class='div_for_stock_prods_" + imc[u].id + "_" + imc[u].related_id + "'></div>";
										relatedHtml += '</div>';
										$self.closest('tr').find('.related_images').append(relatedHtml);
									}
								}
								if (get_data.data.images) {
									var imd = get_data.data.images;
									for (var u = 0; u < imd.length; u++) {
										$('body').append('<a href="product_images/' + imd[u].image_source + '" data-imagelightbox="' + id + '" style="display:none"><img src="../flower_orders/product_images/' + imd[u].image_source + '" alt="' + imd[u].image_note + '"></a>');
										otherProductHtml = '<div class="col-sm-12">';
										otherProductHtml += '<img onclick="zoom_img('+id+')" src="../flower_orders/product_images/'+imd[u].image_source +'" style="width: auto; max-width:100px; height: 90px; float: left;">';
										otherProductHtml += '<span>'+imd[u].image_note+'</span>';
										<?php
											if(max(page::filterLevel(3, $levelArray)) >= 33)
											{
												?>
												otherProductHtml += " ($"+Number(imd[u].price).toFixed(2)+")";
										<?php
											} 
											?>
										otherProductHtml += '<br>';
										otherProductHtml += '<span class="productDesc">'+imd[u].product_desc+'</span></div>';
										$self.closest('tr').find('.product_images').append(otherProductHtml);
									}
								}                    
							}
							if (get_data.data.out_images) {
								if (!$('a[data-imagelightbox="out' + id + '"]').length) {
									var imd = get_data.data.out_images;
									var path = "product_out_images/";
									for (var u = 0; u < imd.length; u++) {
										$('body').append('<a href="'+path + imd[u].fileName + '" data-imagelightbox="out' + id + '" style="display:none"><img src="'+path+ + imd[u].fileName + '"></a>');
										$self.closest('tr').find('.out_images').append('<div class="col-sm-12"><img onclick="zoom_out('+id+')" src="../flower_orders/'+path + imd[u].fileName +'" style="width: auto; max-width:130px; height: 90px;"></div>');
									}
								}                        
							}
						
					});
				} else {
					$(this).attr('data-clicked', 0);
					$self.closest('tr').find('.related_images').html('');
					$self.closest('tr').find('.out_images').html('');
					$self.closest('tr').find('.product_images').html('');
					$('a[data-imagelightbox="'+id+'"]').remove();
				}
			});
			$('body').on('click', '.moodBtn', function(){
				let mood = $(this).attr('data-mood');
				$('#receiver_mood').val(mood);
				$('.moodBtn').removeClass('selectedMood');
				$(this).addClass('selectedMood');
			})
			$('body').on('click', '.nextActionBtn', function(){
				var order_id = $(this).attr('data-order-id');
				let nextAction = $(this).attr('data-next-action');
				$('#next_action').val(nextAction);
				$('.nextActionBtn').removeClass('selectedNextAction');
				$(this).addClass('selectedNextAction');
				if(nextAction == 3){
					var orders = $('#dataTable tr');
					var option_html = '';
					for(var i = 0;i<orders.length;i++){
						if($(orders[i]).attr('data-status') == 1 || $(orders[i]).attr('data-status') == 13){
							option_html+="<option>" + $(orders[i]).attr('data-id') + " " + $(orders[i]).attr('data-street') + "</option>";
						}
					}
					$(".nextOrderId" + order_id).html('<select id="next_action_id" name="next_action_id">' + option_html + "</select>");
				}
				else{
					$(".nextOrderId" + order_id).html("");
				}
			})
			$('body').on('change', '.wrongAddress', function(){
				if($(this).is(":checked")){
					$(this).siblings('.rightAddress').css('visibility', 'visible');
					$(this).siblings('.saveRightAddress').css('visibility', 'visible');
				}
				else{
					let order_id = $(this).attr('data-id');
					$.ajax({
						type: "POST",
						url: "ajax.php",
						data: {
							"order_id": order_id,
							"right_address_false": true,
							"value": '',
						},
						success: function(response){
							alert('Շնորհակալություն');
							console.log(response)
						}
					})
					$(this).siblings('.rightAddress').css('visibility', 'hidden');
					$(this).siblings('.saveRightAddress').css('visibility', 'hidden');
				}
			});
			$('body').on('click', '.saveRightAddress', function(){
				let right_address = $(this).siblings('.rightAddress').val();
				let order_id = $(this).attr('data-id');
				$.ajax({
					type: "POST",
					url: "ajax.php",
					data: {
						"order_id": order_id,
						"right_address": right_address,
					},
					success: function(response){
						alert('Շնորհակալություն, Ձեր նշած սխալ հասցեն պահպանվեց համակարգում')
						console.log(response)
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

                activityIndicatorOn = function()
                {
                    $( '<div id="imagelightbox-loading"><div></div></div>' ).appendTo( 'body' );
                },
                activityIndicatorOff = function()
                {
                    $( '#imagelightbox-loading' ).remove();
                },


                // OVERLAY

                overlayOn = function()
                {
                    $( '<div id="imagelightbox-overlay"></div>' ).appendTo( 'body' );
                },
                overlayOff = function()
                {
                    $( '#imagelightbox-overlay' ).remove();
                },


                // CLOSE BUTTON

                closeButtonOn = function( instance )
                {
                    $( '<button type="button" id="imagelightbox-close" title="Close"></button>' ).appendTo( 'body' ).on( 'click touchend', function(){ $( this ).remove(); instance.quitImageLightbox(); return false; });
                },
                closeButtonOff = function()
                {
                    $( '#imagelightbox-close' ).remove();
                },


                // CAPTION

                captionOn = function()
                {
                    var description = $( 'a[href="' + $( '#imagelightbox' ).attr( 'src' ) + '"] img' ).attr( 'alt' );
                    if( description !== undefined && description.length > 0 )
                        $( '<div id="imagelightbox-caption">' + description + '</div>' ).appendTo( 'body' );
                },
                captionOff = function()
                {
                    $( '#imagelightbox-caption' ).remove();
                },


                // NAVIGATION

                navigationOn = function( instance, selector )
                {
                    var images = $( selector );
                    if( images.length )
                    {
                        var nav = $( '<div id="imagelightbox-nav"></div>' );
                        for( var i = 0; i < images.length; i++ )
                            nav.append( '<button type="button"></button>' );

                        nav.appendTo( 'body' );
                        nav.on( 'click touchend', function(){ return false; });

                        var navItems = nav.find( 'button' );
                        navItems.on( 'click touchend', function()
                        {
                            var $this = $( this );
                            if( images.eq( $this.index() ).attr( 'href' ) != $( '#imagelightbox' ).attr( 'src' ) )
                                instance.switchImageLightbox( $this.index() );

                            navItems.removeClass( 'active' );
                            navItems.eq( $this.index() ).addClass( 'active' );

                            return false;
                        })
                            .on( 'touchend', function(){ return false; });
                    }
                },
                navigationUpdate = function( selector )
                {
                    var items = $( '#imagelightbox-nav button' );
                    items.removeClass( 'active' );
                    items.eq( $( selector ).filter( '[href="' + $( '#imagelightbox' ).attr( 'src' ) + '"]' ).index( selector ) ).addClass( 'active' );
                },
                navigationOff = function()
                {
                    $( '#imagelightbox-nav' ).remove();
                },


                // ARROWS

                arrowsOn = function( instance, selector )
                {
                    var $arrows = $( '<button type="button" class="imagelightbox-arrow imagelightbox-arrow-left"></button><button type="button" class="imagelightbox-arrow imagelightbox-arrow-right"></button>' );

                    $arrows.appendTo( 'body' );

                    $arrows.on( 'click touchend', function( e )
                    {
                        e.preventDefault();

                        var $this	= $( this ),
                            $target	= $( selector + '[href="' + $( '#imagelightbox' ).attr( 'src' ) + '"]' ),
                            index	= $target.index( selector );

                        if( $this.hasClass( 'imagelightbox-arrow-left' ) )
                        {
                            index = index - 1;
                            if( !$( selector ).eq( index ).length )
                                index = $( selector ).length;
                        }
                        else
                        {
                            index = index + 1;
                            if( !$( selector ).eq( index ).length )
                                index = 0;
                        }

                        instance.switchImageLightbox( index );
                        return false;
                    });
                },
                arrowsOff = function()
                {
                    $( '.imagelightbox-arrow' ).remove();
                };
                //end images
		</script>
		
	</body>
</html>