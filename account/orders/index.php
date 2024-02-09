<?php
session_start();
$pageName = "orders";
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
		    <a class="navbar-brand" href="#">RG-SYSTEM / <?=strtoupper($userData[0]["username"]);?></a>
		  </div>
		  <div id="navbar" class="navbar-collapse collapse" aria-expanded="false">
		    <ul class="nav navbar-nav">
		      <?=page::buildMenu($level[0]["user_level"])?>
		      <li class="dropdown" id="menuDrop">
						<li><a href="order.php" target="_blank"><?=ADD_NEW_ORDER;?></a></li>
		      </li>
		    </ul>
		  </div><!--/.nav-collapse -->
		</div>
	      </nav>
		<ol class="breadcrumb" id="activeFilters" style="position:fixed;top:51px;width: 100%">
		 
		</ol>
		<div class="container" style="margin-top:85px;width: 100%">
			<div id="incomplited" style="font-size: 24px;font-weight: bold;"><?=RG_ORDER_SYSTEM;?>
		
			</div>
			<div class="table">
			<table  class="table table-bordered">
			  <thead>
			    <tr class="success">
						<th>
								<div id="loading">
									<img src="<?=$rootF;?>/template/icons/loader.gif">
								</div>#<strong id="onC"></strong>
						</th>
					  <th><?=DELIVERY_DAY;?></th>
					  <th><?=DELIVERY_STATUS;?></th>
						<th><?=ORDER_DAY;?></th>
						<th><?=ORDER_RECEIVER;?></th>
						<th><?=RECEIVER_ADDRESS;?></td>
						<th><?=RECEIVER_PHONE;?></td>
						<th><?=GREETING_CARD;?></th>
						<th><?=ORDER_SENDER;?></th>
						<th><?=SENDER_PHONE;?><?="<br/>".SENDER_EMAIL;?></td>
						<th><?=(max(page::filterLevel(3,$levelArray)) >= 33) ? SENDER_ADDRESS : SENDER_COUNTRY ;?></td>
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
			
			function filter(el,onfilter) {
				$("#loading").css("display","block");
				data["pttf"] = {"filter":24,"value":15};
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
					console.log(countP);
					fromP = buildPaginator(countP,fromP,toP);
					
					var htmlData = "";
					var showA = "";
					if (countP > 0) {
						for(var i = 0;i < tableData.length;i++)
						{
							var d = tableData[i];
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
							//#1
							htmlData += "<td style=\"min-width:50px;\" nowrap><a href=\"order.php?orderId="+d.id+"\" target=\"_blank\">N-"+d.id+"</a></td>";
							
							//#2
							if(!timeType[d.delivery_time])
							{
								timeType[d.delivery_time] = "";
							}
							htmlData += "<td><strong>"+newDate+"</strong><br/>"+timeType[d.delivery_time]+"("+d.delivery_time_manual+")<br/><div style=\"position:relative;height:35px;\"><img width=\"70px\" style=\"position:absolute;top:0;\" src=\"<?=$rootF?>/template/icons/deliver/"+d.delivery_type+".png\"><img width=\"35px\" style=\"position:absolute;top:0;right:0;\" src=\"<?=$rootF?>/template/icons/ontime/"+d.ontime+".png\"></div></td>";
							
							//#3
							htmlData += "<td><img src=\"<?=$rootF?>/template/icons/status/"+d.delivery_status+".png\" title=\""+statusTitle[d.delivery_status]+"\"></td>";
							
							//#4
							var mycDate = d.created_date.split("-");
							var newcDate = mycDate[2]+"-"+monthNames[mycDate[1]-1]+"-"+mycDate[0];
							htmlData += "<td nowrap>"+newcDate+"<br/>"+firstToUpperCase(d.operator)+"</td>";
											
							//#5
							htmlData += "<td>"+d.receiver_name+"</td>";
							
							//#6
							if(!subregionType[d.receiver_subregion])
							{
								subregionType[d.receiver_subregion] = d.receiver_subregion;
							}
							if(!streetType[d.receiver_street] && streetType[d.receiver_street] != ""){
								streetType[d.receiver_street] = d.receiver_street;
							}
							htmlData += "<td>"+streetType[d.receiver_street]+" "+d.receiver_address+"<br/>("+subregionType[d.receiver_subregion]+" <?=STATE;?>)</td>";
							
							//#7
							htmlData += "<td>"+d.receiver_phone+"</td>";
							
							//#8
							co = d.greetings_card;
							htmlData += "<td><div class=\"article\" ><button class=\"read-more\">VIEW "+co.length+"</button><div class=\"text short\">"+d.greetings_card+"</div></div></td>";
							//#9
							//co = d.sender_name+d.sender_region+d.sender_address+d.sender_phone+d.sender_email;
							htmlData += "<td>"+d.sender_name+"</td>";
							//#10
							htmlData += "<td>"+d.sender_phone+"<?php if(max(page::filterLevel(3,$levelArray)) >= 33){?><br/>"+d.sender_email+"<?php }?></td>";
							//htmlData += "<td></td>";
							//#11
							htmlData +="<td> "+d.sender_region+"</td>";
							
						  htmlData += "</tr>";

							CCo++;
							countP = CCo;
							$("#shopCT").html(countP);
												
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
			
			
		</script>
		
	</body>
</html>