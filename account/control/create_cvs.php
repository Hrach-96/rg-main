<?php
	session_start();
	$pageName = "control";
	$rootF = "../..";
	include($rootF."/apay/pay.api.php");

	include($rootF."/configuration.php");
	include("actions.class.php");
	$access = auth::checkUserAccess($secureKey);
	$allData = array();
	$buildClient = "";
	$uid = "";
	$level = "";
	$userData = "";
	$cc = "am";
	$notify = "";
	include("../flower_orders/lang/language_am.php");
	$constants = get_defined_constants();
	if(!$access){
	    header("location:../../login");
	}else{
	    $uid = $_COOKIE["suid"];
	    $level = auth::getUserLevel($uid);
	    page::accessByLevel($level[0]["user_level"],$pageName);
	    $levelArray = explode(",",$level[0]["user_level"]);
	    $userData = auth::checkUserExistById($uid);
	    $cc = $userData[0]["lang"];
	}
	$delivery_reason_show = getwayConnect::getwayData("SELECT * FROM delivery_reason");
	$delivery_status_show = getwayConnect::getwayData("SELECT * FROM delivery_status where active = 1");
	$delivery_payment_show = getwayConnect::getwayData("SELECT * FROM `delivery_payment`");
	$delivery_sellpoint_show = getwayConnect::getwayData("SELECT * FROM `delivery_sellpoint`");
	$primary_language_array = getwayConnect::getwayData("SELECT * FROM `delivery_language`");
	$regions_array = getwayConnect::getwayData("SELECT * from countries where active = 1 ORDER BY `ordering` ASC, name_am");

	if(isset($_REQUEST['get_order_data_filter_custom']) && $_REQUEST['get_order_data_filter_custom']){
		$custom_sql_form = $_REQUEST['custom_sql_form'];
	    $language_translate = $_REQUEST['language_translate'];
		$sql = "SELECT id,sender_email,sender_phone,sender_name from rg_orders " .$custom_sql_form;
		$orders = getwayConnect::getwayData($sql);
	    foreach($orders as $key=>$value){
	    	$full_sender_name_order = explode(' ' , $value['sender_name']);
            $first_sender_name_order = $full_sender_name_order[0];
            $last_sender_name_order = $full_sender_name_order[1];
            $first_tranaslated_name = get_first_name_by_value($first_sender_name_order,$language_translate);
            $last_tranaslated_name = get_last_name_by_value($last_sender_name_order,$language_translate);
            if(!empty($first_tranaslated_name)){
            	$orders[$key]['translated_first_names'] = $first_tranaslated_name[0]['first_name_'.$language_translate];
            }
            else{
            	$orders[$key]['translated_first_names'] = $first_tranaslated_name;
            }
            if(!empty($last_tranaslated_name)){
            	$orders[$key]['translated_last_names'] = $last_tranaslated_name[0]['last_name_'.$language_translate];
            }
            else{
            	$orders[$key]['translated_last_names'] = $last_sender_name_order;
            }
	    }
	    $result = ['orders'=>$orders,'sql'=>$sql];
	    print json_encode($result);die;
	}
	if(isset($_REQUEST['get_order_data_filter']) && $_REQUEST['get_order_data_filter']){
		$from_date = $_REQUEST['from_date'];
	    $to_date = $_REQUEST['to_date'];
	    $date_form = $_REQUEST['date_form'];
	    $delivery_reason = $_REQUEST['delivery_reason'];
	    $primary_language = $_REQUEST['primary_language'];
	    $delivery_payment = $_REQUEST['delivery_payment'];
	    $delivery_sellpoint = $_REQUEST['delivery_sellpoint'];
	    $sender_country = $_REQUEST['sender_country'];
	    $delivery_status = $_REQUEST['delivery_status'];
	    $operator_notes = $_REQUEST['operator_notes'];
	    $language_translate = $_REQUEST['language_translate'];
	    $sql = "SELECT rg_orders.id,sender_email,sender_phone,sender_name from rg_orders";
	    if(!empty($operator_notes)){
			$sql.= " LEFT JOIN order_notes on rg_orders.id = order_notes.order_id ";
	    }
	    if(!empty($from_date)){
	    	$sql.= " where " . $date_form . " >= '" . $from_date . "'";
	    }
	    if(!empty($delivery_reason) && $delivery_reason[0] != 'All'){
			if(count($delivery_reason) > 0){
				$delivery_reason_string = implode(",", $delivery_reason);
				$sql.= " AND delivery_reason in ( " . $delivery_reason_string . " ) ";
			}
		}
	    if(!empty($primary_language) && $primary_language[0] != 'All'){
			if(count($primary_language) > 0){
				$primary_language_string = implode(",", $primary_language);
				$sql.= " AND delivery_language_primary in ( " . $primary_language_string . " ) ";
			}	
		}
	    if(!empty($delivery_payment) && $delivery_payment[0] != 'All'){
			if(count($delivery_payment) > 0){
				$payment_type_string = implode(",", $delivery_payment);
				$sql.= " AND payment_type in ( " . $payment_type_string . " ) ";
			}	
		}
	    if(!empty($delivery_sellpoint) && $delivery_sellpoint[0] != 'All'){
			if(count($delivery_sellpoint) > 0){
				$delivery_sellpoint_string = implode(",", $delivery_sellpoint);
				$sql.= " AND sell_point in ( " . $delivery_sellpoint_string . " ) ";
			}	
		}
	    if(!empty($sender_country) && $sender_country[0] != 'All'){
			if(count($sender_country) > 0){
				$sender_country_string = implode(",", $sender_country);
				$sql.= " AND sender_country in ( " . $sender_country_string . " ) ";
			}	
		}
	    if(!empty($delivery_status) && $delivery_status[0] != 'All'){
			if(count($delivery_status) > 0){
				$delivery_status_string = implode(",", $delivery_status);
				$sql.= " AND delivery_status in ( " . $delivery_status_string . " ) ";
			}	
		}
		if(!empty($to_date)){
			$sql.= " AND " . $date_form . " <= '" . $to_date . "'";
	    }
		if(!empty($operator_notes)){
			$sql.= " AND order_notes.type_id = '2' AND order_notes.value like '%" . $operator_notes . "%'";
	    }
	    $orders = getwayConnect::getwayData($sql);
	    foreach($orders as $key=>$value){
	    	$full_sender_name_order = explode(' ' , $value['sender_name']);
            $first_sender_name_order = $full_sender_name_order[0];
            $last_sender_name_order = $full_sender_name_order[1];
            $first_tranaslated_name = get_first_name_by_value($first_sender_name_order,$language_translate);
            $last_tranaslated_name = get_last_name_by_value($last_sender_name_order,$language_translate);
            if(!empty($first_tranaslated_name)){
            	$orders[$key]['translated_first_names'] = $first_tranaslated_name[0]['first_name_'.$language_translate];
            }
            else{
            	$orders[$key]['translated_first_names'] = $first_tranaslated_name;
            }
            if(!empty($last_tranaslated_name)){
            	$orders[$key]['translated_last_names'] = $last_tranaslated_name[0]['last_name_'.$language_translate];
            }
            else{
            	$orders[$key]['translated_last_names'] = $last_sender_name_order;
            }
	    }
	    $result = ['orders'=>$orders,'sql'=>$sql];
	    print json_encode($result);die;
	}
	function get_first_name_by_value($first_name,$language){
        return getwayConnect::getwayData("SELECT first_name_" . $language . " FROM translate_of_names where  ( first_name_eng = '" . $first_name . "' or first_name_rus = '" . $first_name . "' or first_name_arm = '" . $first_name . "') ");
    }
	function get_last_name_by_value($last_name,$language){
        return getwayConnect::getwayData("SELECT last_name_" . $language . " FROM translate_of_names where  ( last_name_eng = '" . $last_name . "' or last_name_rus = '" . $last_name . "' or last_name_arm = '" . $last_name . "') ");
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Apay gateway">
    <meta name="keywords" content="paypal, payment,visa ,mastercard,payment getway,payment gateway">
    <meta name="author" content="Hrach Avagyan, Ruben Mnatsakanyan">
    <link rel="stylesheet" href="<?=$rootF?>/template/account/sidebar.css">
    <link rel="stylesheet" href="<?=$rootF?>/template/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?=$rootF?>/template/bootstrap/css/bootstrap-theme.min.css">
    <link rel="stylesheet" href="<?=$rootF?>/template/datepicker/css/datepicker.css">
    <link rel="stylesheet" href="<?=$rootF?>/template/rangedate/daterangepicker.css" />
    <title>Create CVS</title>
    <style type="text/css">
    	.table_for_result{
    		width:1000px;
    		margin:auto;
    	}
    	.div_for_count_result{
    		width:270px;
    		font-weight:bolder;
    		font-size:20px;
    		text-align:center;
    		margin:auto;
    	}
    	#generated_sql{
    		margin-top:50px;
    		font-size:16px;
    		font-weight:bolder;
    	}
    </style>
</head>
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
					<li>
						<a href="../control/?cmd=control">Control</a>
					</li>
					<li>
						<a href="../control/?cmd=exit">Logout</a>
					</li>
					<li>
						<a href="../control/?cmd=flower_orders">Flower_orders</a>
					</li>
					<li>
						<a href="../control/?cmd=orders_delivery">Orders_delivery</a>
					</li>
					<li>
						<a href="../control/?cmd=travel_orders">Travel_orders</a>
					</li>
					<li>
						<a href="/account/accountant">ACCOUNTING</a>
					</li>
					<li>
						<a href="/print.php">PRINT</a>
					</li>
	            </ul>
	        </div>
	    </div>
	</nav>
	<div class='col-md-12'>
		<div class='col-md-2' style='margin-top:60px'>
			<select class='form-control date_form'>
				<option value="delivery_date">Delivery Date</option>
				<option value="created_date">Created Date</option>
			</select>
		</div>
		<div class='col-md-2' style='margin-top:60px'>
			<input type='date' class='from_date mt-1 form-control' value="<?= date("Y-m-d") ?>">
		</div>
		<div class='col-md-2' style='margin-top:60px'>
			<input type='date' class='to_date mb-1 form-control' value="<?= date("Y-m-d") ?>">
		</div>
		<div class='col-md-2' style='margin-top:60px'>
			<select class='form-control delivery_reason custom-select' multiple >
				<option value='All'>Բոլորը</option>
				<?php
					foreach( $delivery_reason_show as $key => $value ) {
						?>
							<option value="<?=$value['id']?>"><?= (isset($constants[$value['name']])) ? $constants[$value['name']] : $value['name'] ?></option>
						<?php
					}
				?>
			</select>
		</div>
		<div class='col-md-2' style='margin-top:60px'>
			<select class='form-control primary_language custom-select' multiple >
				<option value='All'>Բոլորը</option>
				<?php
					foreach( $primary_language_array as $key => $value ) {
						?>
							<option value="<?=$value['id']?>"><?= (isset($constants[$value['name']])) ? $constants[$value['name']] : $value['name'] ?></option>
						<?php
					}
				?>
			</select>
		</div>
		<div class='col-md-2' style='margin-top:60px'>
			<select class='form-control delivery_status custom-select' multiple >
				<option value='All'>Բոլորը</option>
				<?php
					foreach( $delivery_status_show as $key => $value ) {
						?>
							<option value="<?=$value['id']?>"><?= (isset($constants[$value['name']])) ? $constants[$value['name']] : $value['name'] ?></option>
						<?php
					}
				?>
			</select>
		</div>
		<div class='col-md-2' style='margin-top:60px'>
			<select class='form-control delivery_payment custom-select' multiple >
				<option value='All'>Բոլորը</option>
				<?php
					foreach( $delivery_payment_show as $key => $value ) {
						?>
							<option value="<?=$value['id']?>"><?= (isset($constants[$value['name']])) ? $constants[$value['name']] : $value['name'] ?></option>
						<?php
					}
				?>
			</select>
		</div>
		<div class='col-md-2' style='margin-top:60px'>
			<select class='form-control delivery_sellpoint custom-select' multiple >
				<option value='All'>Բոլորը</option>
				<?php
					foreach( $delivery_sellpoint_show as $key => $value ) {
						?>
							<option value="<?=$value['id']?>"><?= (isset($constants[$value['name']])) ? $constants[$value['name']] : $value['name'] ?></option>
						<?php
					}
				?>
			</select>
		</div>
		<div class='col-md-1' style='margin-top:60px'>
			<label for='id_field' >Id</label>
			<input type='checkbox' id="id_field" style='width: 15px;margin-top: -8px;' class='form-control'>
		</div>
		<div class='col-md-1' style='margin-top:60px'>
			<label for='full_name_field' >Full Name</label>
			<input type='checkbox' id="full_name_field" style='width: 15px;margin-top: -8px;' class='form-control'>
		</div>
		<div class='col-md-1' style='margin-top:60px'>
			<label for='first_name_field' >First Name</label>
			<input type='checkbox' id="first_name_field" style='width: 15px;margin-top: -8px;' class='form-control'>
		</div>
		<div class='col-md-1' style='margin-top:60px'>
			<label for='last_name_field' >Last Name</label>
			<input type='checkbox' id="last_name_field" style='width: 15px;margin-top: -8px;' class='form-control'>
		</div>
		<div class='col-md-1' style='margin-top:60px'>
			<label for='phone_field' >Phone</label>
			<input type='checkbox' id="phone_field" style='width: 15px;margin-top: -8px;' class='form-control'>
		</div>
		<div class='col-md-1' style='margin-top:60px'>
			<label for='email_field' >Emai</label>
			<input type='checkbox' id="email_field" style='width: 15px;margin-top: -8px;' class='form-control'>
		</div>
		<div class='col-md-2' style='margin-top:60px'>
			<select class='form-control sender_country custom-select' multiple >
				<option value='All'>Բոլորը</option>
				<?php
					foreach( $regions_array as $key => $value ) {
						?>
							<option value="<?=$value['id']?>"><?= $value['name_am'] ?></option>
						<?php
					}
				?>
			</select>
		</div>
		
		<div class='col-md-2' style='margin-top:60px'>
			<select class='form-control language_translate'  >
				<option value='eng'>English</option>
				<option value='rus'>Russian</option>
				<option value='arm'>Armenian</option>
			</select>
		</div>
		<div class='col-md-2' style='margin-top:60px'>
			<textarea id ='operator_notes' class='form-control' placeholder='Operator Notes'></textarea>
		</div>
		<div class='col-md-1' style='margin-top:60px'>
			<label for='print_cvs_checkbox' style='float:left'>Make Cvs</label>
			<input type='checkbox' id="print_cvs_checkbox" style='width: 15px;float:right;margin-top: -8px;' class='form-control print_cvs'>
		</div>
		<div class='col-md-2' style='margin-top:60px'>
			<button class='btn btn-success btn_for_find_orders'>Check</button>
		</div>
	</div>
	<div class='col-md-12' style='margin-top:60px'>
		<div class='col-md-4'>
			<textarea class='form-control custom_sql_form' placeholder='SELECT * FROM rg_orders'></textarea>
		</div>
		<div class='col-md-2'>
			<button class='btn btn-success btn_for_find_orders_custom'>Sql Custom Check</button>
		</div>
	</div>
	<div class='div_for_count_result'></div>
	<table class='table border table_for_result'><table>
	<div class='col-md-12 text-center' id='generated_sql'></div>
</body>
</html>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script type="text/javascript">
	$(document).ready(function(){
		$(document).on("click",".btn_for_find_orders_custom",function(){
			var custom_sql_form = $(".custom_sql_form").val();
			var language_translate = $(".language_translate").val();
			var print_cvs = false;
			if($(".print_cvs").prop("checked") == true){
				print_cvs = true;
			}
			$.ajax({
				url: location.href,
	            type: 'post',
	            data: {
	                get_order_data_filter_custom: true,
	                custom_sql_form:custom_sql_form,
	                language_translate:language_translate,
	            },
	            success: function(resp){
	            	resp = JSON.parse(resp);
	            	if(resp.orders.length > 0){
	            		add_info_result(resp,print_cvs,'default')
	            	}
	            }
			})
		})
		function add_info_result(resp,print_cvs,language){
			var id_field = false;
			var full_name_field = false;
			var first_name_field = false;
			var last_name_field = false;
			var phone_field = false;
			var email_field = false;
			var get_parametr_url = '';
			if($("#id_field").prop("checked") == true){
				id_field = 'full_name';
			}
			if($("#full_name_field").prop("checked") == true){
				full_name_field = 'full_name';
			}
			if($("#first_name_field").prop("checked") == true){
				first_name_field = 'first_name';
			}
			if($("#last_name_field").prop("checked") == true){
				last_name_field = 'last_name';
			}
			if($("#phone_field").prop("checked") == true){
				phone_field = 'phone';
			}
			if($("#email_field").prop("checked") == true){
				email_field = 'email';
			}
			// create dynamic table
			var table_dynamic_content_html = '';
			table_dynamic_content_html+= '<tr>';
			if(id_field){
				table_dynamic_content_html+= '<th>Id</th>';
				get_parametr_url+= 'id,';
			}
			if(full_name_field){
				table_dynamic_content_html+= '<th>Full Name</th>';
				get_parametr_url+= 'full_name,';
			}
			if(first_name_field){
				table_dynamic_content_html+= '<th>First Name</th>';
				get_parametr_url+= 'first_name,';
			}
			if(last_name_field){
				table_dynamic_content_html+= '<th>Last Name</th>';
				get_parametr_url+= 'last_name,';
			}
			if(phone_field){
				table_dynamic_content_html+= '<th>Phone</th>';
				get_parametr_url+= 'phone,';
			}
			if(email_field){
				table_dynamic_content_html+= '<th>Email</th>';
				get_parametr_url+= 'email,';
			}
			get_parametr_url = get_parametr_url.replace(/,\s*$/, "");
			table_dynamic_content_html+= '</tr>';
    		var orders_string = '';
    		for(var i = 0 ; i < resp.orders.length;i++){
    			orders_string+= resp.orders[i].id + ",";
				table_dynamic_content_html+= '<tr>';

				if(id_field){
					table_dynamic_content_html+= '<th><a href="http://new.regard-group.ru/account/flower_orders/order.php?orderId=' + resp.orders[i].id + '" target="_blank">' + resp.orders[i].id + '</a></th>';
				}
				if(full_name_field){
					table_dynamic_content_html+= '<th>' + resp.orders[i].sender_name + '</th>';
				}
				if(first_name_field){
					table_dynamic_content_html+= '<th>' + resp.orders[i].translated_first_names + '</th>';
				}
				if(last_name_field){
					table_dynamic_content_html+= '<th>' + resp.orders[i].translated_last_names + '</th>';
				}
				if(phone_field){
					table_dynamic_content_html+= '<th>' + resp.orders[i].sender_phone + '</th>';
				}
				if(email_field){
					table_dynamic_content_html+= '<th>' + resp.orders[i].sender_email + '</th>';
				}
				table_dynamic_content_html+= '</tr>';
    		}
    		$(".table_for_result").html(table_dynamic_content_html);
    		$(".div_for_count_result").html("Գտնվեց՝ " + resp.orders.length + " Պատվեր <hr>");
    		orders_string = orders_string.slice(0,-1);
    		if(print_cvs){
    			window.open('download-excel.php?language=' + language + '&ordersArray=' + orders_string + '&fields=' + get_parametr_url, '_blank');
    		}
    		$("#generated_sql").html(resp.sql);
		}
		$(document).on("click",".btn_for_find_orders",function(){
			var print_cvs = false;
			if($(".print_cvs").prop("checked") == true){
				print_cvs = true;
			}
			var date_form = $(".date_form").val();
			var from_date = $(".from_date").val();
			var to_date = $(".to_date").val();
			var delivery_reason = $(".delivery_reason").val();
			var primary_language = $(".primary_language").val();
			var delivery_status = $(".delivery_status").val();
			var delivery_payment = $(".delivery_payment").val();
			var operator_notes = $("#operator_notes").val();
			var delivery_sellpoint = $(".delivery_sellpoint").val();
			var language_translate = $(".language_translate").val();
			var sender_country = $(".sender_country").val();
			$.ajax({
				url: location.href,
	            type: 'post',
	            data: {
	                get_order_data_filter: true,
	                from_date:from_date,
	                date_form:date_form,
	                to_date:to_date,
	                delivery_reason:delivery_reason,
	                primary_language:primary_language,
	                delivery_payment:delivery_payment,
	                operator_notes:operator_notes,
	                delivery_sellpoint:delivery_sellpoint,
	                sender_country:sender_country,
	                delivery_status:delivery_status,
	                language_translate:language_translate
	            },
	            success: function(resp){
	            	$(".table_for_result").empty();
	            	resp = JSON.parse(resp);
	            	add_info_result(resp,print_cvs,language_translate)
	            	if(resp.orders.length > 0){
	            	}
	            }
			})
		})
	})
</script>