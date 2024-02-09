<?php
session_start();
date_default_timezone_set("Asia/Yerevan");

$pageName = "orders";
$rootF = "../..";
include($rootF."/apay/pay.api.php");
include($rootF."/configuration.php");
$access = auth::checkUserAccess($secureKey);
$allData = array();
$buildClient = "";
$uid = "";
$level = "";
$operator = "";
$orderId = null;
$created = "";
$orderData = null;
$cc = "am";
$actioned = false;
$titleHelp = "NEW";
$postId = "";
$root = true;
if(!$access){
	header("location:../../login");
}else{
	$uid = $_COOKIE["suid"];
	$level = auth::getUserLevel($uid);
        $operator = page::getOperator($uid);
	page::accessByLevel($level[0]["user_level"],$pageName);
	$userData = auth::checkUserExistById($uid);
	$cc = $userData[0]["lang"];
	
	$postArray = array("bonus_type","delivery_date","delivery_time","delivery_time_manual","delivery_region","receiver_name","product","price","currency","receiver_subregion","receiver_street","receiver_address","receiver_phone","greetings_card","delivery_type","ontime","delivery_status","order_source","order_source_optional","payment_type","payment_optional","sender_name","sender_region","sender_address","sender_phone","sender_email","notes","notes_for_florist","sell_point","keyword");
	
	if(isset($_REQUEST["orderId"]))
	{
		$orderId = htmlentities($_REQUEST["orderId"]);
		if($orderData = getwayConnect::getwayData("SELECT * FROM rg_orders WHERE id = '{$orderId}'"))
		{
			$created = $orderData[0]["created_date"].",".$orderData[0]["operator"];
		}else{
			$orderId = null;
		}
		$titleHelp = "EDIT";
	}
}
function checkaddslashes($str){        
    if(strpos(str_replace("\'",""," $str"),"'")!=false){
        return addslashes($str);
	} else{
        return $str;
	}
}
page::cmd();
if(is_file("lang/language_{$cc}.php"))
	{
		include("lang/language_{$cc}.php");	
	}else{
		include("lang/language_am.php");
	}

if(isset($_REQUEST["insert_order"]))
	{
		$actionQuery = "";
		foreach($_REQUEST as $key => $value)
		{
			//$value = htmlentities($value);
			/*$value = str_replace('"',"",$value);
			$value = str_replace("'","",$value);
			$value = str_replace("%22","",$value);
			$value = str_replace("&quot;","",$value);
			$value = str_replace("&amp;","",$value);
			$value = str_replace("quot;","",$value);*/
			if(in_array($key,$postArray))
			{
				$value = checkaddslashes($value);
				$actionQuery .= "{$key} = '{$value}', ";
			} 
		}
		$value = "<br>Added: {$_REQUEST["operator"]}<br>".date("Y-M-d H:i:s",time());
		$cDate = date("Y-m-d");
		//die($cDate);
		$actionQuery .= "log = '{$value}' , operator='{$_REQUEST["operator"]}' , created_date = '{$cDate}'";
		$actionQuery = rtrim($actionQuery,", ");
		$postId = getwayConnect::getwaySend("INSERT INTO rg_orders SET {$actionQuery}",true);
		if($postId)
		{
			$actioned = 1;
			//echo "<script>window.location.replace(\"../\");</script>";
		}else{
			$actioned = 4;
		}
		
	}
	else if(isset($_REQUEST["update_order"]))
	{
		$actionQuery = "";
		foreach($_REQUEST as $key => $value)
		{
			//$value = htmlentities($value);
			/*$value = str_replace('"',"",$value);
			$value = str_replace("'","",$value);
			$value = str_replace("%22","",$value);
			$value = str_replace("&quot;","",$value);
			$value = str_replace("&amp;","",$value);
			$value = str_replace("quot;","",$value);*/
			if(in_array($key,$postArray))
			{
				$value = checkaddslashes($value);
				$actionQuery .= " {$key} = '{$value}', ";
			} 
		}
		$value = "<br>Edit: {$_REQUEST["operator"]}<br>".date("Y-M-d H:i:s",time());
		$actionQuery .= "log = CONCAT(log,'{$value}') ";
		$actionQuery = rtrim($actionQuery,", ");
		//print_r($postArray);
		//echo $actionQuery;
		if(isset($_REQUEST["id"]))
		{
			
			$id =  htmlentities($_REQUEST["id"]);
			//echo "UPDATE rg_orders SET {$actionQuery} WHERE id='{$id}'";
			if(getwayConnect::getwaySend("UPDATE rg_orders SET {$actionQuery} WHERE id='{$id}'"))
			{
				if($cc == "fr")
				{
					$xe = getwayConnect::getwayData("SELECT name FROM currency WHERE id = '{$_REQUEST["currency"]}'");
					$xe = (isset($xe[0]["name"])) ? $xe[0]["name"] : null;
					$confirm_link = '<a href="http://regard-group.com/rg-system/flower_orders">http://regard-group.com/rg-system/flower_orders</a>';
					$to = 'takciparis@gmail.com';//"dxjan@ya.ru";
					$subject = 'L"ordre a été changé '.$id;
					$message = 'Livirasion date,time: '.$_REQUEST["delivery_date"].','.$_REQUEST["delivery_time"].' '.$_REQUEST["delivery_time_manual"].'<br> Prix: '.$_REQUEST["price"].' '.$xe.'<br> Address:  '.$_REQUEST["receiver_subregion"].' '.$_REQUEST["receiver_address"].'<br> Sil vous plaît confirmer! '.$confirm_link.'<br> / '.$_REQUEST["operator"];
					$from = 'sales@flowers-armenia.com';
					$headers = "MIME-Version: 1.0" . "\r\n";
					$headers .= "Content-type:text/html;charset=utf-8" . "\r\n";
					$headers .= "From: <".$from.">" . "\r\n";
					$headers .= "Cc: <".$from.">" . "\r\n";
					
					mail($to, $subject, $message, $headers);
				}
				$actioned = 2;
				
				//header("location:../");
			}else{
				$actioned = 4;
			}
			//var_dump($isUpdated);
			//die();
		}
		
	}
include("functions.orders.php");
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
		<meta http-equiv="cache-control" content="max-age=0" />
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
			.article {}
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
		<title><?=$titleHelp;?> <?=RG_ORDER_SYSTEM;?></title>
		<?php
		if($actioned == 1)
		{
			echo "<script>alert(\"".SAVED."\");</script>";
			echo "<script>window.location.replace(\"order.php?orderId={$postId}\");</script>";
			exit;
			
		}else if($actioned == 2){
			echo "<script>alert(\"".SAVED."\");</script>";
			echo "<script>window.location.replace(\"order.php?orderId={$_REQUEST["orderId"]}\");</script>";
			exit;
		}else if($actioned == 4){
			echo "<script>alert(\"FAIL TO SAVE\");</script>";
			echo "<script>window.location.replace(\"order.php?orderId={$_REQUEST["orderId"]}\");</script>";
			exit;
		}
		?>
	</head>
	<body>
<form method="post" action="" id="new_order_form" class="form-horizontal" role="form" onsubmit="checkAll(this)">
    <div align="center" style="margin:5px;">
        <input type="hidden" name="bonus_type" value="3" id="option3">
        &nbsp; <?=C_OPERATOR;?>: <span style="color:red;font-size:20px;"&gt;<strong><?=$operator?></strong>
    </div>
    <?php if($orderId != null){ 
		$edOperator = checkStatus($orderId);
		$edOperator["o"] = (isset($edOperator["o"])) ? $edOperator["o"] : "";		
	?><div align="center" style="margin:5px;"><strong><?=ucfirst(C_OPERATOR);?>:<?=$created?></strong></div>
	<?php if($operator != $edOperator["o"]){ ?>
	<div align="center" style="margin:5px;font-size:20px">EDITING BY:<strong style="color:red;" id="editorOperator"><?=ucfirst($edOperator["o"]);?></strong></div>
	<?php 
		}
	}?>
<div align="center" style="margin:5px;">
<table style="border:0px none; max-width: 950px;">
    <tbody>
        <tr>
            <td>
            <!--tablestart block 1-->
            <div style="border: 3px solid #F0AD4E; width:auto; height:auto; border-radius:7px; padding:20px; padding-bottom:0; ">
            <table border="0">
                <tbody>
                    <tr>
                        <td><label><?=DELIVERY_DAY;?>: *</label></td>
                        <td><input value="<?=(isset($orderData[0]["delivery_date"])) ? $orderData[0]["delivery_date"] : ""?>" type="text" name="delivery_date" class="required form-control datepicker hasDatepicker" id="delivery_date" addon="date" required></td>
                    </tr>
                    <tr>
                        <td><label><?=DELIVERY_TIME;?>: </label></td>
                        <td><select onclick="" name="delivery_time" class="form-control" id="delivery_time" style="width: 100px; float: left;">
                                <option value=""><?=SELECT_FROM_LIST;?>:</option>
                                <?php
					$active = (isset($orderData[0]["delivery_time"])) ? $orderData[0]["delivery_time"] : false;
					echo page::buildOptions("delivery_time",$active);
				?>
                            </select>
                            <input value="<?=(isset($orderData[0]["delivery_time_manual"])) ? $orderData[0]["delivery_time_manual"] : ""?>" type="text" name="delivery_time_manual"  id="time_manual" class="form-control" style="width: 95px; float: left;">
                        </td>
                    </tr>
                    <tr>
                        <td><label><?=COUNTRY;?>: *</label></td>
                        <td><select name="delivery_region" id="b_region" class="form-control required" onchange="buildRegions(1,this,<?=(isset($orderData[0]["delivery_region"])) ? $orderData[0]["delivery_region"] : "null"?>)" required>
                                <option value=""><?=SELECT_FROM_LIST;?>:</option>
				<?php
					$active = (isset($orderData[0]["delivery_region"])) ? $orderData[0]["delivery_region"] : false;
					echo page::buildOptions("delivery_region",$active,false,array(20,21));
				?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><label><?=ORDER_RECEIVER;?>: </label></td>
                        <td><input value="<?=(isset($orderData[0]["receiver_name"])) ? $orderData[0]["receiver_name"] : ""?>" type="text" class="form-control" name="receiver_name" id="receiver_name"></td>
                    </tr>
                    <tr>
                        <td><label><?=RECEIVER_STATE;?>: </label></td>
                        <td><select <?=(isset($orderData[0]["receiver_subregion"])) ? "": "disabled=\"disabled\""?> name="receiver_subregion" id="b_subregion" class="form-control" onchange="buildRegions(2,this);">
                                <option value="0"><?=SELECT_FROM_LIST;?>:</option>
				<?php
					if(isset($orderData[0]["receiver_subregion"]))
					{
						$active = $orderData[0]["receiver_subregion"];
						echo page::buildOptions("delivery_subregion",$active,$orderData[0]["delivery_region"]);
					}
				?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><label><?=RECEIVER_STREET;?>: </label></td>
                        <td>
				<!--onchange="ChangeSelectedValue(this.selectedIndex.text);"-->
                            <select <?=(isset($orderData[0]["receiver_street"])) ? "": "disabled=\"disabled\"";?> name="receiver_street" id="b_street" class="form-control" >
                                <option value="E-1"></option>
				<?php
					if(isset($orderData[0]["receiver_street"]))
					{
						$active = $orderData[0]["receiver_street"] ;
						$active = (!page::isStreetCode($active)) ? page::getStreetCodeByName($active) : $active;
						echo page::buildOptions("delivery_street",$active,$orderData[0]["receiver_subregion"]);
					}
				?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><label><?=RECEIVER_HOME;?> : </label></td>
                        <td><input value="<?=(isset($orderData[0]["receiver_address"])) ? str_replace("\"","'",$orderData[0]["receiver_address"]) : ""?>" type="text" class=" form-control" name="receiver_address" id="receiver_address"></td>
                    </tr>
                    <tr>
                        <td><label><?=RECEIVER_PHONE;?>: </label></td>
                        <td><input value="<?=(isset($orderData[0]["receiver_phone"])) ? $orderData[0]["receiver_phone"] : ""?>" type="text" class="form-control" name="receiver_phone" id="receiver_phone"></td>
                    </tr>
                    <tr>
                        <td><label><?=GREETING_CARD_TEXT;?>: </label></td>
                        <td><textarea class="form-control" name="greetings_card" id="greetings_card" cols="20" rows="7"><?=(isset($orderData[0]["greetings_card"])) ? $orderData[0]["greetings_card"] : ""?></textarea>
			</td>
                    </tr>
                    <tr>
                        <td><label><?=DELIVERY_TYPE;?>: </label></td>
                        <td> <label id="hovik_delivery" style="padding:2px;">
                            <input type="radio" name="delivery_type" value="1" id="radio_hovik" <?=(isset($orderData[0]["delivery_type"]) && $orderData[0]["delivery_type"] == 1) ? "checked" : ""?>>
                            <img src="<?=$rootF?>/template/icons/deliver/1.png" width="70px" title="Հովիկ" ></label>
                            
							<label id="norik_delivery">
                            <input type="radio" name="delivery_type" value="2" id="radio_norik" <?=(isset($orderData[0]["delivery_type"]) && $orderData[0]["delivery_type"] == 2) ? "checked" : ""?>>
                            <img src="<?=$rootF?>/template/icons/deliver/2.png" width="70px" title="Նորիկ"></label>
							
							<label id="paris_delivery">
                            <input type="radio" name="delivery_type" value="4" id="radio_paris" <?=(isset($orderData[0]["delivery_type"]) && $orderData[0]["delivery_type"] == 4) ? "checked" : ""?>>
                            <img src="<?=$rootF?>/template/icons/deliver/4.png" width="70px" title="Իգոր"></label>    
                              
                            <label id="bike_delivery" style="padding:2px;">
                            <input type="radio" name="delivery_type" value="3" id="radio_bike" <?=(isset($orderData[0]["delivery_type"]) && $orderData[0]["delivery_type"] == 3) ? "checked" : ""?>>
                            <img src="<?=$rootF?>/template/icons/deliver/3.png" title="Հեծանվորդ"></label>
                              
                            <label id="menu_delivery">
                            <input type="radio" name="delivery_type" value="5" id="radio_menu" <?=(isset($orderData[0]["delivery_type"]) && $orderData[0]["delivery_type"] == 5) ? "checked" : ""?>>
                            <img src="<?=$rootF?>/template/icons/deliver/5.png" width="70px" title="Menu.am"></label>  
							
							<label id="buy_am_delivery">
                            <input type="radio" name="delivery_type" value="6" id="radio_buy_am" <?=(isset($orderData[0]["delivery_type"]) && $orderData[0]["delivery_type"] == 6) ? "checked" : ""?>>
                            <img src="<?=$rootF?>/template/icons/deliver/6.png" width="70px" title="Buy.am"></label>  
							
                        </td>
                    </tr>
                    <tr>
                        <td><label><?=HOW_WAS_DELIVERED;?>: </label></td>
                        <td><label id="true_time" style="padding:10px; padding-right:55px">
                            <input type="radio" name="ontime" value="1" id="true_time" <?=(isset($orderData[0]["ontime"]) && $orderData[0]["ontime"] == 1) ? "checked" : ""?>>
                            <img src="<?=$rootF?>/template/icons/ontime/1.png"></label>
                            <label id="bicycle-delivery">
                            <input type="radio" name="ontime" value="2" id="false_time" <?=(isset($orderData[0]["ontime"]) && $orderData[0]["ontime"] == 2) ? "checked" : ""?>>
                            <img src="<?=$rootF?>/template/icons/ontime/2.png"></label>
                        </td>
                    </tr>
                </tbody>
            </table>
            </div>
            <!--tableend block 1-->
            </td>

            <td>
            <!--tablestart block 2-->
            <div style=" min-width:454px; border:3px solid #D9534F; margin-left:10px;  height:100%; border-radius:7px; padding:20px; ">
            <table border="0">
                <tbody>
                    <tr>
                        <td><label><?=STATUS;?>: *</label></td>
                        <td><select name="delivery_status" id="delivery_status" class="form-control required" required>
                                <option value=""><?=SELECT_FROM_LIST;?>:</option>
                                <?php
					$active = (isset($orderData[0]["delivery_status"])) ? $orderData[0]["delivery_status"] : false;
					echo page::buildOptions("delivery_status",$active);
				?>
                            </select>
                        </td>
                    </tr>
                   
                    <tr>
                        <td><label><?=SENDER_NAME;?>: </label></td>
                        <td><input value="<?=(isset($orderData[0]["sender_name"])) ? $orderData[0]["sender_name"] : ""?>" type="text" name="sender_name" class="form-control" id="sender_name"></td>
                    </tr>
                    <tr>
                        <td><label><?=SENDER_COUNTRY;?>: </label></td>
                        <td><input value="<?=(isset($orderData[0]["sender_region"])) ? $orderData[0]["sender_region"] : ""?>" type="text" class="form-control" name="sender_region" id="sender_region"></td>
                    </tr>
                    <tr>
                        <td><label><?=SENDER_ADDRESS;?>: </label></td>
                        <td><input value="<?=(isset($orderData[0]["sender_address"])) ? $orderData[0]["sender_address"] : ""?>" type="text" class="form-control" name="sender_address" id="sender_address"></td>
                    </tr>
                    <tr>
                        <td><label><?=SENDER_PHONE;?>: </label></td>
                        <td><input value="<?=(isset($orderData[0]["sender_phone"])) ? $orderData[0]["sender_phone"] : ""?>" type="text" class="form-control" name="sender_phone" id="sender_phone"></td>
                    </tr>
                    <tr>
                        <td><label><?=SENDER_EMAIL;?>: </label></td>
                        <td><input value="<?=(isset($orderData[0]["sender_email"])) ? $orderData[0]["sender_email"] : ""?>" type="email" class="form-control" placeholder="Enter email" name="sender_email" id="sender_email"></td>
                    </tr>
                </tbody>
            </table>
            </div>
            <!--tableend block 2-->
            </td>
        </tr>
    </tbody>
</table>
</div>
<div align="center">
    <button type="submit" name="<?=(isset($orderData[0]["id"]))? "update_order" : "insert_order"?>" class="btn btn-primary" ><?=(isset($orderData[0]["id"]))? SAVE : ADD;?></button>
    &nbsp;<input type="button" class="btn btn-danger" value="<?=CLOSE;?>" onclick="document.location.href='../flower_orders';">
    &nbsp;<input type="reset" class="btn btn-warning" value="<?=RESET;?>">
    <input type="hidden" value="<?=$operator?>" name="operator">
    <?php
	if(isset($orderData[0]["id"])){
    ?>
	<input type="hidden" value="<?=$orderData[0]["id"]?>" name="id">
    <?php	
	}
    ?>
</div>
</form>
<link rel="stylesheet" href="<?=$rootF?>/template/account/sidebar.css">
		<!-- Bootstrap minified CSS -->
		<link rel="stylesheet" href="<?=$rootF?>/template/bootstrap/css/bootstrap.min.css">
		<!-- Bootstrap optional theme -->
		<link rel="stylesheet" href="<?=$rootF?>/template/bootstrap/css/bootstrap-theme.min.css">
		<link rel="stylesheet" href="<?=$rootF?>/template/datepicker/css/datepicker.css">
		<link rel="stylesheet" href="<?=$rootF?>/template/rangedate/daterangepicker.css" />
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
                <script type="text/javascript">
				function removeAttrb(){
					$("#delivery_date").removeAttr("required");
					$("#b_region").removeAttr("required");
					$("#product").removeAttr("required");
					$("#price").removeAttr("required");
					$("#currency").removeAttr("required");
				}
				<?php 
				if(isset($orderData[0]["bonus_type"]) && $orderData[0]["bonus_type"] == 2){
					echo "removeAttrb();";
				}
				?>
				function addAttrb(){
					$("#delivery_date").attr("required","required");
					$("#b_region").attr("required","required");
					$("#product").attr("required","required");
					$("#price").attr("required","required");
					$("#currency").attr("required","required");
				}
                    var allRegions = <?=page::buildAllRegions()?>;
                    function buildRegions(type,el,activeItem)
                    {
			var selectedItem = "";
                        if (type == null) {
                            var dhtml = "<option>------</option>";
                            var next = "";
                            var current= "";
                            for(var i=0;i < allRegions.length;i++)
                            {
                                next = allRegions[i].region.code;
				
                                if (current != next && allRegions[i].region.name != "") {
					if (allRegions[i].region.code == activeItem) {
						selectedItem = "selected";
						
					}else{
						selectedItem = "";
					}
					console.log(activeItem);
                                    dhtml += "<option value=\""+allRegions[i].region.code+"\" "+selectedItem+">"+allRegions[i].region.name+"</option>";
                                    current = next;
                                }
                                
                            }
                            $("#b_region").html(dhtml);
                        }
                        if (type == 1){
                            var dhtml = "<option>------</option>";
                            var next = "";
                            var current= "";
			    
                            $("#b_street").html("");
                            $("#b_street").attr("disabled","disabled");
                            if (!el.value) {
                                $("#b_subregion").attr("disabled","disabled");
                                $("#b_subregion").html(dhtml);
                                return false;
                            }
                            $("#b_subregion").removeAttr("disabled");
                            for(var i=0;i < allRegions.length;i++)
                            {
                                if (allRegions[i].region.code == el.value) {
                                    if (allRegions[i].region.sub_region) {
                                        next = allRegions[i].region.sub_region.name;
					
                                        if (current != next && allRegions[i].region.sub_region.name != "") {
						if (allRegions[i].region.sub_region.code == activeItem) {
							selectedItem = "selected";
						}else{
							selectedItem = "";
						}
                                            dhtml += "<option value=\""+allRegions[i].region.sub_region.code+"\""+selectedItem+">"+allRegions[i].region.sub_region.name+"</option>";
                                            current = next;
                                        }
                                    }
                                }
                            }
                            $("#b_subregion").html(dhtml);
                        }
                        if (type == 2){
                            var dhtml = "<option>------</option>";
                            var next = "";
                            var current= "";
                            
                            if (!el.value) {
                                $("#b_street").attr("disabled","disabled");
                                $("#b_street").html(dhtml);
                                return false;
                            }
                            $("#b_street").removeAttr("disabled");
                            for(var i=0;i < allRegions.length;i++)
                            {
                                if (allRegions[i].region.sub_region.code == el.value) {
                                    if (allRegions[i].region.sub_region.street) {
                                        next = allRegions[i].region.sub_region.street.name;
                                        if (current != next && allRegions[i].region.sub_region.street.name != "") {
                                            dhtml += "<option value=\""+allRegions[i].region.sub_region.street.code+"\">"+allRegions[i].region.sub_region.street.name+"</option>";
                                            current = next;
                                        }
                                    }
                                }
                            }
                            $("#b_street").html(dhtml);
                        }
                        return false;
                    }
                    //buildRegions(null,null,<?=(isset($orderData[0]["delivery_region"])) ? $orderData[0]["delivery_region"] : "null"?>);
                    function check(el,type)
                    {
                        if (type == 1) {
                            if (el.value.search(/^[0-9]+$/) == -1 && el.value != "") {
                               el.value = /[0-9]*/.exec(el.value);
                               alert("Only Numeric allowed!");
                            }
                        }
                        if (type == 2) {
                            if (!el.value) {
                                $("#"+el.id).css("border-color","#FF0000");
                                return false;
                            }else{
				$("#"+el.id).css("border-color","#CCC");
			    }
                        }
                        return true;
                    }
                    function checkAll(el)
                    {
                        var cont = true;
                        $('[required]').each(function(){
                                if (this.value == "") {
                                    alert("Important fields muust be filled!")
				    $("#"+this.id).css("border-color","#FF0000");
                                    cont = false;
                                    alert("REQUIRED FIELDS NOT SET");
                                    return false;
                                }
                            });
                       
                        if (cont) {
                            return true;
                        }else{
                            return false;
                        }
                        
                    }
                    if ($('[addon="date"]')) {
			$('[addon="date"]').datepicker({format: 'yyyy-mm-dd'});
		    }
		    var orderId = "<?=$orderId;?>";
                function pull()
				{
					$.get("pull.php?pid="+prderId, function (get_data){
						//console.log(get_data);
						if($("#editorOperator"))
						{
							$("#editorOperator").html(get_data.o.charAt(0).toUpperCase()+get_data.o.slice(1));
						}
					});
				}
		//pull();
		//setInterval(pull,8000);
                </script>
                </body>
</html>