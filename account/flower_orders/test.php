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
$root = true;
include("products/engine/engine.php");
$engine = new engine();

$regionData = page::getRegionFromCC($cc);
date_default_timezone_set ("Asia/Yerevan");
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
		<style>
			.article {word-wrap: break-word; max-width:125px;}
			.article .text {
				font-size: 13px;
				line-height: 17px;
				font-family: arial;
			}
			.article .text.short {
				height: 0px;
				overflow: hidden;
			}
			.article .text.full {
			     
			}
			.read-more {
				
			}
			.date-picker-wrapper{
				z-index:99999999;
			}
			.datepicker{
				z-index:99999999;
			}
			hr{
				line-height: 0;
				margin-top:2px;
				margin-bottom:2px;
				border-color:#666;
			}
			#loading{
				position: fixed;
				z-index:9999999999;
				top: -6px;
				left: -8px;
				display: block;
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
		    <a class="navbar-brand" href="#">RG-SYSTEM</a>
		  </div>
		  <div id="navbar" class="navbar-collapse collapse" aria-expanded="false">
		    <ul class="nav navbar-nav">
		      <?=page::buildMenu($level[0]["user_level"])?>
			  <?php
					if(max(page::filterLevel(3,$levelArray)) >= 33)
					{}
				?>
		      <li class="dropdown" id="menuDrop">
			<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><?=FILTER;?> <span class="caret"></span></a>
			<ul class="dropdown-menu" role="menu" style="text-align:center;">
			  <?php
				$fData = page::buildFilter($level[0]["user_level"],$pageName);
				for($fi = 0 ; $fi < count($fData);$fi++)
				{
					//echo "<li class=\"divider\"></li>";
					//echo "<li class=\"dropdown-header\">{$fData[$fi][0]}</li>";
					echo "<li>{$fData[$fi][1]}</li>";
				}
			  ?>
			  
			</ul>
			
			  <li><a href="order.php" target="_blank"><?=ADD_NEW_ORDER;?></a></li>
			  <?php
					//level
			  ?>
		      </li>
		    </ul>
		  </div><!--/.nav-collapse -->
		</div>
	      </nav>
		<ol class="breadcrumb" id="activeFilters" style="position:fixed;top:51px;width: 100%">
		 
		</ol>
		<div class="container" style="margin-top:85px;width: 100%">
			<div id="incomplited" style="font-size: 24px;font-weight: bold;"><?=RG_ORDER_SYSTEM;?>
			<?php
					if(max(page::filterLevel(3,$levelArray)) >= 33)
					{
				?>
				<button onclick="sendMail()">SEND MAIL</button>
			<?=(getwayConnect::getwayCount("SELECT * FROM rg_orders WHERE delivery_status = 2") > 0) ? "<strong style=\"color:#ff0000\">".getwayConnect::getwayCount("SELECT * FROM rg_orders WHERE delivery_status = 2")."</strong>" : 0;?><img src="<?=$rootF;?>/template/icons/status/2.png">
			<?php }?>
			<?php
					if($cc == "am")
					{
				?>
				<div style="float: right;font-size:20px;padding-left:50px;" ><br><a href="products/index.php" target="_blank">Ապրանքներ</a>&nbsp;&nbsp;&nbsp;</div>
				<?php
				$shuka = $engine->categoryDanger(1);
				$arevtur = $engine->categoryDanger(1,true);
				$date = $engine->getLastEdit(false);
				$passed = strtotime($engine->dateutc()) - strtotime($date);
				$hours = round($passed/3600);
				$minutes = round($passed/60);
				$par = "";
				if($hours > 5)
				{
					$par =  "<img src=\"products/template/images/par-par.gif\" align=\"left\" height=\"50\">";
				}
				?>
				<div style="float: right;font-size:10px;padding-left:50px;" ><br><?=($shuka > 5 && $shuka < 10 || $arevtur > 5 && $arevtur < 10)? "<img src=\"products/template/images/warning-white.gif\"  align=\"left\" height=\"50px\">":"<img src=\"products/template/images/warning.gif\" align=\"left\" height=\"50px\">"?>
				<strong style="font-size:20px">Շուկա(<strong style="color:<?=($shuka <= 0)?"green":"red";?>;"><?=$shuka?></strong>)
				Առևտուր(<strong style="color:<?=($arevtur <= 0)?"green":"red";?>;"><?=$arevtur?></strong>)<?=$par?></strong>
				<br>
				<strong style="font-size:12px">Փոփոխվել է<?=($minutes > 60 ) ? " {$hours} ժամ առաջ:" : " {$minutes} րոպե առաջ:";?></strong>
				</div>
				<div style="float: right;font-size:10px;padding-left:50px;" ><br><a href="products/index.php?request=arajark" target="_blank"><strong style="font-size:20px">Ինչ Առաջարկել</strong><img src="products/template/images/cancellation.jpg" height="50px"></a>&nbsp;</div>
				
				<?php
					}
				?>
			</div>
			
			<?php
					if(max(page::filterLevel(3,$levelArray)) >= 33)
					{
				?>
			<button name="drf" id="1" onclick="filter(this,true);" value="<?=date("Y-m-d");?> to <?=date("Y-m-d");?>"><?=TODAY;?>(<?=(max(page::filterLevel(3,$levelArray)) >= 33) ? getwayConnect::getwayCount("SELECT * FROM rg_orders WHERE delivery_date = '".date("Y-m-d")."' ") : "<strong id=\"shopCT\"></strong>";?>)</button>
			<button name="drf" id="1" onclick="filter(this,true);" value="<?=date("Y-m-d", time() + 86400);?> to <?=date("Y-m-d", time() + 86400);?>"><?=TOMORROW;?>(<?=getwayConnect::getwayCount("SELECT * FROM rg_orders WHERE delivery_date = '".date("Y-m-d", time() + 86400)."'");?>)</button>
			<button name="drf" id="1" onclick="filter(this,true);" value="<?=date("Y-m-d", time() - 86400);?> to <?=date("Y-m-d", time() - 86400);?>"><?=YESTERDAY;?>(<?=getwayConnect::getwayCount("SELECT * FROM rg_orders WHERE delivery_date = '".date("Y-m-d", time() - 86400)."'");?>)</button>
			<button onclick="totalResset();" value=""><?=RESET;?></button>
			<button onclick="filter(null,true);" ><?=REFRESH;?></button>
			<select id="showCount" onchange="showCount(this);">
				<option value="70" selected>70</option>
				<option value="100">100</option>
				<option value="1000">1000</option>
				<option value="10000">10000</option>
				<option value="false">ALL</option>
			</select>
					<?php
					}else{
					?>
					<button name="adf" id="17" onclick="filter(this,true);" value="<?=date("Y-m-d");?>"><?=TODAY;?></button>
			<button name="adf" id="17" onclick="filter(this,true);" value="<?=date("Y-m-d", time() + 86400);?>"><?=TOMORROW;?></button>
					<?php
					}
					?>
			<div class="table">
			<table  class="table table-bordered">
			  <thead>
			    <tr class="success">
				
			       
					<?php
					if(max(page::filterLevel(3,$levelArray)) >= 33)
					{
					?>
					<th><div id="loading"><img src="<?=$rootF;?>/template/icons/loader.gif"></div><input type="checkbox" onclick="checkAll(this);"><button name="orderF" id="12" onclick="filter(this,true);" value="id ASC">#<strong id="onC"></strong></button></th>
			        <th><img src="<?=$rootF;?>/template/icons/bonus_title.png"></th>
					<th><button name="orderF" id="12" onclick="filter(this,true);" value="delivery_date DESC"><?=DELIVERY_DAY;?></button></th>
					<?php
					}else{
					?>
					<th><div id="loading"><img src="<?=$rootF;?>/template/icons/loader.gif"></div>#<strong id="onC"></strong></th>
			        <th><?=DELIVERY_DAY;?></th>
					<?php
					}
					?>
			        <th><?=(max(page::filterLevel(3,$levelArray)) < 33) ? DELIVERY_STATUS : "<img src=\"{$rootF}\/template/icons/exit.png\">";?></th>
				
				
				<?php
					if(max(page::filterLevel(3,$levelArray)) >= 33)
					{
				?>
				<th><button name="orderF" id="12" onclick="filter(this,true);" value="created_date DESC"><?=ORDER_DAY;?></button></th>
				<th><?=ORDER_SOURCE;?></th>
				<th><img src="<?=$rootF?>/template/icons/price.png"></th>
				<?php
					}
				?>
				<th><?=ORDERED_PRODUCTS;?></th>
				<th><?=ORDER_RECEIVER;?></th>
				<?php
					if(max(page::filterLevel(3,$levelArray)) < 33)
					{
				?>
				<th><?=NOTES_FOR_FLORIST;?></th>
				<?php
					}
				?>
				<th><?=RECEIVER_ADDRESS;?></td>
				<th><?=RECEIVER_PHONE;?></td>
				<?php
				if(max(page::filterLevel(3,$levelArray)) < 33)
				{
				?>
				<th><?=GREETING_CARD;?></th>
				<?php
				}else{
				?>
				<th><?=NOTES;?></th>
				<?php
					}
				?>
				<?=(max(page::filterLevel(3,$levelArray)) >= 33) ? "" : "<th>".HOW_WAS_DELIVERED."</th>";?>
				<th><?=ORDER_SENDER;?></th>
				<th><?=SENDER_PHONE;?><?=(max(page::filterLevel(3,$levelArray)) >= 33) ? "<br/>".SENDER_EMAIL : "";?></td>
				<th><?=(max(page::filterLevel(3,$levelArray)) >= 33) ? SENDER_ADDRESS : SENDER_COUNTRY ;?></td>
				<?php
					if(max(page::filterLevel(3,$levelArray)) >= 33)
					{
				?>
				
				<th><?=GREETING_CARD;?></th>
				<th><?=(max(page::filterLevel(3,$levelArray)) >= 33) ? NOTES_FOR_FLORIST : "<strong style=\"color:#ff0000\">".NOTES_FOR_FLORIST."</strong>";?></th>
				
				<th><?=REFERRER_SITE;?><br/>Refferal</th>
				<th><img src="<?=$rootF;?>/template/icons/info.png"></th>
				<?php
					}
				?>
				<?=(max(page::filterLevel(3,$levelArray)) < 33) ? "<th><img src=\"{$rootF}/template/icons/info.png\"></th>" : "";?>
			    </tr>
			  </thead>
			  <tbody id="dataTable">
			    <!--data table-->
			  </tbody>
			</table>
		      </div>
			<nav style="width: 100%;text-align: center;">
				<ul class="pagination" id="buildPages">
				  <li class="active"><a href="#">1</a></li>
				</ul>
		        </nav>
		</div>
		
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
		<style>
			#editor{
				position: fixed;
				padding: 5px;
				bottom: 0;
				right: 0;
				width: 35px;
				height: 35px;
				background: #FFF;
				border: 1px solid #666;
			}
			#editorMessage{
				border: 1px solid #C0C0C0;
				margin-bottom: 2px;
			}
			#editorMenu{
				width:100%;
				margin-bottom: 5px;
				text-align:right;
				background:#C8C8C8;
			}
			#dataMsg{
				width:100%;
				height:100%;
				overflow-y: auto;
			}
			</style>
			<div id="editor">
			<div id="editorMenu"><button id="editMM" onclick="mmwindow();" style="width:25px;height:25px;">+</button></div>
			<div id="dataMsg">
				
			</div>
			<script>
				function mmwindow()
				{
					if($("#editor").height() > 25)
					{
						$("#editor").height(25);
						$("#editor").width(25);
						$("#editMM").html("+");
					}else{
						$("#editor").height(350);
						$("#editor").width(350);
						$("#editMM").html("-");
					}
				}
				function getEditors(){
					$.get("pull.php?editors", function (get_data){
						var editors_data = "";
						for(var k = 0;k < get_data.length;k++)
						{
							var eNum = k+1;
							editors_data += '<div id="editorMessage"><strong>'+eNum+'.</strong>OPERATOR:<strong>'+get_data[k].operator+'</strong> EDITING:<strong>'+get_data[k].pid+'</strong></div>';
						}
						$("#dataMsg").html(editors_data);
					});
				}
				getEditors();
				setInterval(getEditors,5000);
			</script>
			</div>
		<script>
			var timoutSet = null;
			var data ={};
			var send_data = "";
			var data_type = "flower";
			var fromP = 0;
			var toP = 70;
			var payType = <?=page::getJsonData("delivery_payment");?>;
			var sourceType = <?=page::getJsonData("delivery_source");?>;
			var timeType = <?=page::getJsonData("delivery_time");?>;
			var sellPoint = <?=page::getJsonData("delivery_sellpoint");?>;
			var subregionType = <?=page::getJsonData("delivery_subregion","code");?>;
			var streetType= <?=page::getJsonData("delivery_street","code");?>;
			var statusTitle = <?=page::getJsonData("delivery_status");?>;
			<?php if($cc == "fr" || max(page::filterLevel(3,$levelArray)) < 33){?>
			data["ccf"] = {"filter":7,"value":<?=$regionData["id"]?>};
			<?php }?>
			function firstToUpperCase( str ) {
				return str.substr(0, 1).toUpperCase() + str.substr(1);
			}
			<?php				
			if(max(page::filterLevel(3,$levelArray)) < 33)
			{
			?>
			data["orderF"] = {"filter":12,"value":"delivery_time ASC"};
			data["adf"] = {"filter":17,"value":"<?=date("Y-m-d");?>"};
			<?php
			}
			?>
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
					//<li class=\"active\">Data</li>
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
				//console.log(data_encode);
				//console.log(base64_decode(data_encode));
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
				$.get("<?=$rootF?>/data.php?cmd=data&page="+data_type+send_data+"&paginator="+fromP+":"+toP, function (get_data){
					//console.log(get_data);
					var CCo = 0;
					var tableData = get_data.data;
					var countP = get_data.count;
					fromP = buildPaginator(countP,fromP,toP);
					
					var htmlData = "";
					var showA = "";
					if (countP > 0) {
						for(var i = 0;i < tableData.length;i++)
						{
							var d = tableData[i];
							<?php
							
							if(max(page::filterLevel(3,$levelArray)) < 33)
							{
							?>
							if(d.delivery_status == "7" || d.delivery_status == "6" || d.delivery_status == "1" || d.delivery_status == "3" ){
							<?php
							}
							?>
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
							
							htmlData += "<tr "+showA+">";
							<?php
							
							if(max(page::filterLevel(3,$levelArray)) >= 33)
							{
							?>
							//#1
							htmlData += "<td style=\"min-width:50px;\" nowrap><a href=\"order.php?orderId="+d.id+"\" target=\"_blank\">N-"+d.id+"</a><br/><a href=\"print.php?orderId="+d.id+"\" target=\"_blank\"><img src=\"<?=$rootF?>/template/icons/print.png\"></a><br><input id=\"mailToSend\" type=\"checkbox\" value=\""+d.id+"\" disabled></td>";
							<?php
							}else{
							?>
							//#1
							htmlData += "<td style=\"min-width:50px;\" nowrap>N-"+d.id+"<br/><a href=\"print.php?orderId="+d.id+"\" ><img src=\"<?=$rootF?>/template/icons/print.png\"></a></td>";
							<?php
							}
							if(max(page::filterLevel(3,$levelArray)) >= 33)
							{
							?>
							//#2
							htmlData += "<td><img src=\"<?=$rootF?>/template/icons/bonus/"+d.bonus_type+".png\"><br/><img src=\"<?=$rootF?>/template/icons/region/"+d.delivery_region+".png\"></td>";
							//#3
							if(!timeType[d.delivery_time])
							{
								timeType[d.delivery_time] = "";
							}
							
							htmlData += "<td><strong>"+newDate+"</strong><br/>"+timeType[d.delivery_time]+d.delivery_time_manual+"<br/><div style=\"position:relative;height:35px;\"><img style=\"position:absolute;top:0;\" src=\"<?=$rootF?>/template/icons/deliver/"+d.delivery_type+".png\"><img width=\"35px\" style=\"position:absolute;top:0;right:0;\" src=\"<?=$rootF?>/template/icons/ontime/"+d.ontime+".png\"></div></td>";
							//#4
							htmlData += "<td><img src=\"<?=$rootF?>/template/icons/status/"+d.delivery_status+".png\" title=\""+statusTitle[d.delivery_status]+"\"></td>";
							var mycDate = d.created_date.split("-");
							var newcDate = mycDate[2]+"-"+monthNames[mycDate[1]-1]+"-"+mycDate[0];
							htmlData += "<td nowrap>"+newcDate+"<br/>"+firstToUpperCase(d.operator)+"</td>";
							//#6
							var sType = "";
							if (d.order_source != "0") {
								sType = sourceType[d.order_source];
							}else{
								sType = "";
							}
							htmlData += "<td>"+sType+"<hr/>"+d.order_source_optional+"</td>";
							
							//#7
							var pType = "";
							if (d.payment_type != "0") {
								pType = payType[d.payment_type];
							}else{
								pType = "";
							}
							htmlData += "<td><img src=\"<?=$rootF?>/template/icons/currency/"+d.currency+".png\" width=\"20px\">"+number_format(d.price,'2',',','.')+"<hr/>"+pType+"<hr/>"+d.payment_optional+"</td>";
							<?php
								}else{
							?>
							if(!timeType[d.delivery_time])
							{
								timeType[d.delivery_time] = "";
							}
							htmlData += "<td nowrap>"+newDate+"<br/>"+timeType[d.delivery_time]+d.delivery_time_manual+"<br/><img src=\"<?=$rootF?>/template/icons/deliver/"+d.delivery_type+".png\"><br/></td>";
							htmlData += "<td><img src=\"<?=$rootF?>/template/icons/status/"+d.delivery_status+".png\" title=\""+statusTitle[d.delivery_status]+"\"></td>";
							<?php
								}
							?>
							htmlData += "<td style=\"min-width:200px;\">"+d.product+"</td>";
							htmlData += "<td>"+d.receiver_name+"</td>";
							<?php
							if(max(page::filterLevel(3,$levelArray)) < 33)
							{
							?>
							
							co = d.notes_for_florist;
							htmlData += "<td><div style=\"max-width:135px;word-wrap: break-word;\"><strong style=\"color:#ff0000;\">"+d.notes_for_florist+"</strong></div></td>";
							
							<?php
							}
							?>
							if(!subregionType[d.receiver_subregion])
							{
								subregionType[d.receiver_subregion] = d.receiver_subregion;
							}
							if(!streetType[d.receiver_street] && streetType[d.receiver_street] != ""){
								streetType[d.receiver_street] = d.receiver_street;
							}
							htmlData += "<td>"+streetType[d.receiver_street]+" "+d.receiver_address+"<br/>("+subregionType[d.receiver_subregion]+" <?=STATE;?>)</td>";
							htmlData += "<td>"+d.receiver_phone+"</td>";
							<?php
							if(max(page::filterLevel(3,$levelArray)) < 33)
							{
							?>
							co = d.greetings_card;
							htmlData += "<td><div class=\"article\" ><button class=\"read-more\">VIEW "+co.length+"</button><div class=\"text short\">"+d.greetings_card+"</div></div></td>";
							<?php
							}else{
							?>
							co = d.notes;
							htmlData += "<td width=\"110\"><div class=\"article\"><button class=\"read-more\">VIEW "+co.length+"</button><div class=\"text short\">"+d.notes+"</div></div></td>";
							<?php
							}
							?>
							<?php if(max(page::filterLevel(3,$levelArray)) >= 33){}else{?>htmlData +="<td><img src=\"<?=$rootF?>/template/icons/ontime/"+d.ontime+".png\"></td>";<?php } ?>
							
							//#12
							//co = d.sender_name+d.sender_region+d.sender_address+d.sender_phone+d.sender_email;
							htmlData += "<td>"+d.sender_name+"</td>";
							htmlData += "<td>"+d.sender_phone+"<?php if(max(page::filterLevel(3,$levelArray)) >= 33){?><br/>"+d.sender_email+"<?php }?></td>";
							//htmlData += "<td></td>";
							<?php
							if(max(page::filterLevel(3,$levelArray)) >= 33)
							{
							?>
							htmlData += "<td>"+d.sender_address+"<br/>"+d.sender_region+"</td>";
							//htmlData += "<td></td>";
							<?php
							}else{
							?>
							htmlData +="<td> "+d.sender_region+"</td>";
							<?php
							}
							?>
							<?php
							if(max(page::filterLevel(3,$levelArray)) >= 33)
							{
							?>
							//#13
							
							co = d.greetings_card;
							htmlData += "<td><div class=\"article\" ><button class=\"read-more\">VIEW "+co.length+"</button><div class=\"text short\">"+d.greetings_card+"</div></div></td>";
							
							//#14
							co = d.notes_for_florist;
							htmlData += "<td><div class=\"article\"><button class=\"read-more\">VIEW "+co.length+"</button><div class=\"text short\">"+d.notes_for_florist+"</div></div></td>";
							
							if(!sellPoint[d.sell_point])
							{
								sellPoint[d.sell_point] = "";
							}
							//#15
							htmlData += "<td>"+sellPoint[d.sell_point]+"<br/>"+d.keyword+"</td>";
							//#16
							//htmlData += "<td></td>";
							//#17
							co = d.log;
							htmlData += "<td nowrap><div class=\"article\"><button class=\"read-more\">VIEW "+co.length+"</button><div class=\"text short\">"+d.log+"</div></div></td>";
							
							<?php
							}else{
							?>
							htmlData +="<td> By "+firstToUpperCase(d.operator)+"</td>";
							<?php
							}
							?>
						   htmlData += "</tr>";
						   <?php
							
							if(max(page::filterLevel(3,$levelArray)) < 33)
							{
							?>
							CCo++;
							countP = CCo;
							$("#shopCT").html(countP);
							}else{
								$("#shopCT").html(countP);
							}
							<?php
							}
							?>
							
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
			filter(null);
			$('#menuDrop .dropdown-menu').on({
				"click":function(e){
			      e.stopPropagation();
			    }
			});
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
				$('[addon="date"]').datepicker({format: 'yyyy-mm-dd'}).on('changeDate',function(){filter(this,true);});
			}
			function totalResset()
			{
				$("input[type=text]").each(function(){$(this).val('');});
				$("select").each(function(){$(this).val('');});
				$("#showCount").val("70");
				data ={};
				toP = 70;
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
		</script>
		
	</body>
</html>