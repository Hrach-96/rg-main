<?php
session_start();
$pageName = "travel";
$rootF = "../..";
include($rootF."/apay/pay.api.php");

include($rootF."/configuration.php");
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
}
page::cmd();
if(is_file("../flower_orders/lang/language_{$cc}.php")){
	include("../flower_orders/lang/language_{$cc}.php");	
}else{
	include("../flower_orders/lang/language_am.php");
}


?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<!-- Meta, title, CSS, favicons, etc. -->
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="description" content="">
		<meta name="keywords" content="">
		<meta name="author" content="Davit G.">
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
		<link rel="stylesheet" href="<?=$rootF?>/template/datetimepicker/css/bootstrap-datetimepicker.min.css" />
		<style type="text/css">
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
			.remove_button{
			    font-size: 15px;
				margin: 0;
				padding: 0;
				border: 1px solid #666;
				background: white;
				color: red;
				padding-left: 3px;
				padding-right: 3px;
				padding-bottom: 2px;
				line-height: 1;
				text-align: center;
				margin-top: 3px;
				max-width: 19px;
				display: inline-block;
				margin-left: 5px;
				margin-bottom: 0;
			}
			
			.add_button{
				display: inline-block;
			    border: 1px solid #ababab;
			    padding: 3px;
			    background: #f7f7f7;
			}
			.remove_button:hover,.add_button:hover{
				cursor:pointer;
				border-color:#777;
			}
			.item_list ol{
				padding-left: 20px;
			}
			.item_list li{
    			margin-right: 5px;
			}
			.item_list li:hover{
				border-bottom: #666 1px solid;
			}
			@media print {
			  .hidden-print {
				display: none !important;
			  }
			}
		</style> 
		<title>Travel</title>
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
					</ul>
				</div><!--/.nav-collapse -->
			</div>
	    </nav>
		<?php
			$hotel_on_array = array(3,4,5,6);
			$filter_service = (isset($_REQUEST['service_type']) && (int)$_REQUEST['service_type'] > 0) ? $_REQUEST['service_type'] : false;
			
			$filter_status = (isset($_REQUEST['status']) && (int)$_REQUEST['status'] > 0) ? $_REQUEST['status'] : false;
			
			$filter_currency = (isset($_REQUEST['currency']) && (int)$_REQUEST['currency'] > 0) ? $_REQUEST['currency'] : false;
			
			$filter_partner = (isset($_REQUEST['partner']) && (int)$_REQUEST['partner'] > 0) ? $_REQUEST['partner'] : false;
			
			$filter_hotel = (isset($_REQUEST['hotel']) && (int)$_REQUEST['hotel'] > 0) ? $_REQUEST['hotel'] : false;
			
			$filter_date = (isset($_REQUEST['date_range'])) ? $_REQUEST['date_range'] : false;
			
			$filter_date_by = (isset($_REQUEST['filter_date_by']) && (int)$_REQUEST['filter_date_by'] > 0) ? $_REQUEST['filter_date_by'] : 0;
			
			$selected_page = (isset($_REQUEST['page']) && (int)$_REQUEST['page'] > 0) ? $_REQUEST['page'] : 1;
			$selected_on_page = (isset($_REQUEST['on_page']) && (int)$_REQUEST['on_page'] > 0) ? $_REQUEST['on_page'] : 100;
			
			
			$filters_array = array();
			if($filter_service){
				$filters_array[] = "DT.`travel_Services` = {$filter_service}";
			}
			if($filter_status){
				$filters_array[] = "DT.`travel_status` = {$filter_status}";
			}
			if($filter_currency){
				$filters_array[] = "DT.`travel_currency` = {$filter_currency}";
			}
			if($filter_partner){
				$filters_array[] = "DT.`travel_partneID` = {$filter_partner}";
			}
			if($filter_hotel){
				$filters_array[] = "DH.`id` = {$filter_hotel}";
			}
			
			if($filter_date){
				$explode_date = explode(" to ",$filter_date);
				if(!$filter_date_by || $filter_date_by <= 0){
					if(count($explode_date) > 1){
						if(strtotime($explode_date[0]) && strtotime($explode_date[1])){
							$filters_array[] = "DATE(DT.`travel_date`) >= '{$explode_date[0]}' AND DATE(DT.`travel_date`) <= '{$explode_date[1]}'";
						}else if(strtotime($filter_date)){
							$filters_array[] = "DATE(DT.`travel_date`) = '{$filter_date}'";
						}
					}else if(strtotime($explode_date[0])){
						$filters_array[] = "DATE(DT.`travel_date`) = '{$explode_date[0]}'";
					}
				}else if($filter_date_by > 0){
					if($filter_date_by == 1){
						if(count($explode_date) > 1){
							if(strtotime($explode_date[0]) && strtotime($explode_date[1])){
								$filters_array[] = "DATE(DHB.`check_in`) >= '{$explode_date[0]}' AND DATE(DHB.`check_in`) <= '{$explode_date[1]}'";
							}else if(strtotime($filter_date)){
								$filters_array[] = "DATE(DHB.`check_in`) = '{$explode_date[0]}'";
							}
						}else if(strtotime($explode_date[0])){
							$filters_array[] = "DATE(DHB.`check_in`) = '{$explode_date[0]}'";
						}
					}else if($filter_date_by == 2){
						if(count($explode_date) > 1){
							if(strtotime($explode_date[0]) && strtotime($explode_date[1])){
								$filters_array[] = "DATE(DHB.`check_out`) >= '{$explode_date[0]}' AND DATE(DHB.`check_out`) <= '{$explode_date[1]}'";
							}else if(strtotime($filter_date)){
								$filters_array[] = "DATE(DHB.`check_out`) = '{$explode_date[0]}'";
							}
						}else if(strtotime($explode_date[0])){
							$filters_array[] = "DATE(DHB.`check_out`) = '{$explode_date[0]}'";
						}
					}
				}
			}
			if(count($filters_array) > 0){
				$filters_array = "WHERE \n".implode(" AND ",$filters_array);
			}else{
				$filters_array = "";
			}
			
			$count_data = getwayConnect::getwayCount("SELECT count(*)
						FROM `data_travel` AS DT
							LEFT JOIN `data_status` AS DS ON DS.`id` = DT.`travel_status`
							LEFT JOIN `currency` AS C ON C.`id` = DT.`travel_currency`
							LEFT JOIN `travel_partner` AS TP ON TP.`id` = DT.`travel_partneID`
							LEFT JOIN `travel_hotel_relation` AS THR ON THR.`travel_id` = DT.`id`
							LEFT JOIN `data_hotel_booking` AS DHB ON DHB.`id` = THR.`hotel_booking_id`
							LEFT JOIN `data_hotels` AS DH ON DH.`id` = DHB.`hotel_id`
						{$filters_array}");
				
			if($count_data > 0 && $count_data > $selected_on_page){
				$pages_count = round($count_data/$selected_on_page);
				if($selected_page > $pages_count){
					$selected_page = $pages_count;
				}
			}else{
				$selected_page = 0;
			}
			if($selected_page > 1){
				$selected_page = ($selected_page-1)*$selected_on_page;
			}else{
				$selected_page = 0;
			}
			$query = "SELECT DT.*,
							 DS.`name` AS status_name,
							 C.`name` AS currency_name,
							 TP.`name` AS partner_name,
							 DH.name AS hotel_name
						FROM `data_travel` AS DT
							LEFT JOIN `data_status` AS DS ON DS.`id` = DT.`travel_status`
							LEFT JOIN `currency` AS C ON C.`id` = DT.`travel_currency`
							LEFT JOIN `travel_partner` AS TP ON TP.`id` = DT.`travel_partneID`
							LEFT JOIN `travel_hotel_relation` AS THR ON THR.`travel_id` = DT.`id`
							LEFT JOIN `data_hotel_booking` AS DHB ON DHB.`id` = THR.`hotel_booking_id`
							LEFT JOIN `data_hotels` AS DH ON DH.`id` = DHB.`hotel_id`
						{$filters_array} GROUP BY DT.`id` ORDER BY DT.`id` DESC
						LIMIT {$selected_page},{$selected_on_page};";
			//echo $query;,
							 //DHB.check_in,
							 //DHB.check_out
			//travel_hotel_relation
			//travel_id
			//hotel_booking_id
			
			//data_hotel_booking
			//hotel_id
			
			//data_hotels
			//id
			//name
			$sum_total = array();
			$currency_text = '';
			$data = getwayConnect::getwayData($query,PDO::FETCH_ASSOC);
			/*if(in_array($filter_service,$hotel_on_array)){
				$hotel_query = "SELECT * FROM `data_hotel_booking` WHERE `id` = ";
			}*/
		?>
           
		<div class="container" style="margin-top:81px;width: 100%">
			<form method="GET" name="filters" action="" class="hidden-print">
				<ol class="breadcrumb">
					<li>
						<select name="service_type">
							<option value="0">SERVICE TYPE</option>
							<?=page::buildOptions("data_service",$filter_service);?>
						</select>
					</li>
					<li>
						<select name="status">
							<option value="0">STATUS</option>
							<?=page::buildOptions("data_status",$filter_status);?>
						</select>
					</li>
					<li>
						<select name="partner">
							<option value="0">PARTNER</option>
							<?=page::buildOptions("travel_partner",$filter_partner);?>
						</select>
					</li>
					<li>
						<select name="currency">
							<option value="0">CURRENCY</option>
							<?=page::buildOptions("currency",$filter_currency);?>
						</select>
					</li>
					<li>
						<select name="hotel" id="hotels" style="max-width:95px;">
							<option value="0">Hotel</option>
							<?=page::buildOptions("data_hotels",$filter_hotel);?>
						</select>
					</li><!---->
					<li>
						<select name="filter_date_by">
							<option value="0" <?=((int)$filter_date_by == 0) ? 'selected="selected"' : '';?>>Created on</option>
							<option value="1" <?=((int)$filter_date_by == 1) ? 'selected="selected"' : '';?>>Check in</option>
							<option value="2" <?=((int)$filter_date_by == 2) ? 'selected="selected"' : '';?>>Check out</option>
						</select>
						<input type="text" name="date_range" id="37" placeholder="Date" addon="rangedate" style="margin:2px;" value="<?=$filter_date;?>"/>
					</li>
				</ol>
				<ol class="breadcrumb">
					<li>
						<label for="page">PAGE</label>
						<select name="page" id="page">
							<option value="0">1</option>
							<?php
							if($count_data > 0 && $count_data > $selected_on_page){
								$pages_count = round($count_data/$selected_on_page);
								for($i = 2;$i <= $pages_count;$i++){
									$selected = (isset($_REQUEST['page']) && (int)$_REQUEST['page'] == $i) ? 'selected="selected"' : '';
									echo "<option value=\"{$i}\" {$selected}>{$i}</option>";
								}
							}
							?>
						</select>
					</li>
					<li>
						<label for="on_page">ON PAGE</label>
						<select name="on_page" id="on_page">
							<option value="100">100</option>
							<option value="500" <?=((int)$selected_on_page == 500) ? 'selected="selected"' : '';?>>500</option>
							<option value="1000" <?=((int)$selected_on_page == 1000) ? 'selected="selected"' : '';?>>1000</option>
							<option value="5000" <?=((int)$selected_on_page == 5000) ? 'selected="selected"' : '';?>>5000</option>
							<option value="10000" <?=((int)$selected_on_page == 10000) ? 'selected="selected"' : '';?>>10000</option>
							<option value="50000" <?=((int)$selected_on_page == 50000) ? 'selected="selected"' : '';?>>50000</option>
							<option value="100000" <?=((int)$selected_on_page == 100000) ? 'selected="selected"' : '';?>>100000</option>
							<option value="500000" <?=((int)$selected_on_page == 500000) ? 'selected="selected"' : '';?>>500000</option>
							<option value="1000000" <?=((int)$selected_on_page == 1000000) ? 'selected="selected"' : '';?>>1000000</option>
						</select>
					</li>
					<li>
						<button name="filter">FILTER</button>
					</li>
				</ol>
			</form>
				
			<div class="table">
			<table  class="table table-bordered">
			  <thead>
			    <tr class="success">
			    <th>#</th>
			    <th>Date</th>
			    <th>Price</th>
				<th>Currency</th>
			    <th>Status</th>
				<th>Name</th>
				<th>Hotel</th>
			    </tr>
			  </thead>
			  <tbody id="dataTable">
			    <!--data table-->
				<?php
					// echo $query; commented by Ruben so that not to print the SQL query
					$strange_amd = 0;
					if(is_array($data) && count($data) > 0){
						foreach($data as $key => $value){
							$partner_name = ($value['travel_partneID'] > 0) ? $value['partner_name']." / " : ''; 
							$value['travel_price'] = ((int)$value['travel_price'] > 0) ? $value['travel_price'] : 0;
							
							$value['travel_customerName'] = (strlen($value['travel_customerName']) > 0) ? $value['travel_customerName'] : '-';
							$price = ($value['currency_name'] == 'AMD' && $value['travel_price'] < 1000) ? '<div style="color:red;">'.number_format((float)$value['travel_price'], 2, ',', '.').'</div>' : number_format((float)$value['travel_price'], 2, ',', '.');
							if($value['currency_name'] == 'AMD' && $value['travel_price'] < 1000){
								$strange_amd += $value['travel_price'];
							}
							echo "<tr>
									<td>{$value['id']}</td>
									<td>{$value['travel_date']}</td>
									<td style=\"text-align:right;\">{$price}</td>
									<td>{$value['currency_name']}</td>
									<td>{$value['status_name']}</td>
									<td>{$partner_name}{$value['travel_customerName']}</td>
									<td>{$value['hotel_name']}</td>
								  </tr>
								";
							if($value['travel_price'] > 0){
								if(!isset($sum_total[$value['currency_name']])){
									$sum_total[$value['currency_name']] = $value['travel_price'];
								}else{
									$sum_total[$value['currency_name']] += $value['travel_price'];
								}
								
							}
							$price = 0;
						}
					}else{
						echo "<tr style=\"text-align:center;\">
								<td>-</td>
								<td>-</td>
								<td>-</td>
								<td>-</td>
								<td>-</td>
								<td>-</td>
								<td>-</td>
							  </tr>";
					}
					if(count($sum_total) > 0){
						foreach($sum_total as $curr => $st){
							$st = number_format((int)$st, 2, ',', '.');
							$amd_val = ($curr == "AMD") ? "<stong style=\"color:red;\">". number_format((int)$strange_amd, 2, ',', '.')."</strong>" : '';
							echo "<tr>
										<td></td>
										<td style=\"text-align:right;\"><strong>Total:</strong></td>
										<td style=\"text-align:right;\"><strong>{$st}</strong></td>
										<td><stong>{$curr}</strong></td>
										<td style=\"text-align:left;\">{$amd_val}</td>
										<td></td>
										<td></td>
									  </tr>";
						}
					}else{
						echo "<tr>
								<td></td>
								<td style=\"text-align:right;\"><strong>Total:</strong></td>
								<td style=\"text-align:right;\"><strong>0</strong></td>
								<td><stong>N/A</strong></td>
								<td></td>
								<td></td>
								<td></td>
							  </tr>";
					}
					
				?>
			  </tbody>
			</table>
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
		<script src="<?=$rootF?>/template/datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
		<script type="text/javascript">
			if ($('[addon="rangedate"]')) {
				$('[addon="rangedate"]').dateRangePicker();
			}
		</script>
	</body>
</html>