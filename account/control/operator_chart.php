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
	if(isset($_REQUEST['get_operators_info']) && $_REQUEST['get_operators_info']){
	    $operators = getwayConnect::getwayData("SELECT * FROM `user`");
	    $operators_array;
	    foreach($operators as $key=>$value){
	        $operators_array[$value['username']] = $value;
	    }
	    print json_encode($operators_array);die;
	}
	$operators_to_show = getwayConnect::getwayData("SELECT * FROM user where user_level like '%36%' order by user_active DESC");
	$time_to_show = getwayConnect::getwayData("SELECT * FROM filter_time_shifts");
	if(isset($_REQUEST['getordersforoperator']) && $_REQUEST['getordersforoperator']){
		$from_date = $_REQUEST['from_date'];
	    $to_date = $_REQUEST['to_date'];
	    $time_filter = $_REQUEST['time_filter'];
	    $sql = "SELECT * from rg_orders";
	    if(!empty($from_date)){
	    	$sql.= " where created_date >= '" . $from_date . "'";
	    }
	    if(!empty($time_filter)){
			if(count($time_filter) > 1){
				$sql.= " AND ( ";
				foreach($time_filter as $key => $value){
					$time_parts = explode('-', $value);
					$sql.= " created_time >= '" .$time_parts[0] . "' AND created_time <= '" . $time_parts[1] . "' OR";
				}
				$sql = substr($sql, 0, -2);
				$sql.= " )";
			}
			else{
				$time_parts = explode('-', $time_filter[0]);
				$sql.= " AND created_time >= '" .$time_parts[0] . "' AND created_time <= '" . $time_parts[1] . "'" ;
			}
		}
		if(!empty($to_date)){
			$sql.= " AND created_date <= '" . $to_date . "'";
	    }
	    $operators = $_REQUEST['operator_filter'];
	    $result = [];
	    foreach( $operators as $key => $value ){
	    	$array = [];
		    $hastatvac = getwayConnect::getwayData($sql . "AND operator = '" . $value . "' AND  delivery_status = 1 ");
		    $anavart = getwayConnect::getwayData($sql . "AND operator = '" . $value . "' AND  delivery_status = 2 ");
		    $araqvac = getwayConnect::getwayData($sql . "AND operator = '" . $value . "' AND  delivery_status = 3 ");
		    $chexyal = getwayConnect::getwayData($sql . "AND operator = '" . $value . "' AND  delivery_status = 4 ");
		    $bac_toxnvac = getwayConnect::getwayData($sql . "AND operator = '" . $value . "' AND  delivery_status = 5 ");
		    $janaparhin = getwayConnect::getwayData($sql . "AND operator = '" . $value . "' AND  delivery_status = 6 ");
		    $veradarcrac = getwayConnect::getwayData($sql . "AND operator = '" . $value . "' AND  delivery_status = 7 ");
		    $komunikacia = getwayConnect::getwayData($sql . "AND operator = '" . $value . "' AND  delivery_status = 8 ");
		    $dublikat = getwayConnect::getwayData($sql . "AND operator = '" . $value . "' AND  delivery_status = 9 ");
		    $avtomat = getwayConnect::getwayData($sql . "AND operator = '" . $value . "' AND  delivery_status = 10 ");
		    $hrajarvele_araqel = getwayConnect::getwayData($sql . "AND operator = '" . $value . "' AND  delivery_status = 11 ");
		    $patrast = getwayConnect::getwayData($sql . "AND operator = '" . $value . "' AND  delivery_status = 12 ");
		    $hastatvac_araqichi_koxmic = getwayConnect::getwayData($sql . "AND operator = '" . $value . "' AND  delivery_status = 12 ");
		    $array['hastatvac_araqichi_koxmic'] = count($hastatvac_araqichi_koxmic);
		    $array['patrast'] = count($patrast);
		    $array['hrajarvele_araqel'] = count($hrajarvele_araqel);
		    $array['avtomat'] = count($avtomat);
		    $array['dublikat'] = count($dublikat);
		    $array['komunikacia'] = count($komunikacia);
		    $array['veradarcrac'] = count($veradarcrac);
		    $array['janaparhin'] = count($janaparhin);
		    $array['bac_toxnvac'] = count($bac_toxnvac);
		    $array['chexyal'] = count($chexyal);
		    $array['araqvac'] = count($araqvac);
		    $array['anavart'] = count($anavart);
		    $array['hastatvac'] = count($hastatvac);
		    $result[$value] = $array;
	    }
	    print json_encode($result);die;
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
    <meta name="author" content="Hrach Avagyan, Ruben Mnatsakanyan">
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
    <title>Operator Charts</title>
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
	        </div><!--/.nav-collapse -->
	    </div>
	</nav>
	<div class='col-md-12'>
		<div class='col-md-2' style='margin-top:60px'>
			<input type='date' class='from_date mt-1 form-control'>
		</div>
		<div class='col-md-2' style='margin-top:60px'>
			<input type='date' class='to_date mb-1 form-control'>
		</div>
		<div class='col-md-2' style='margin-top:60px'>
			<select class='form-control operator_filter custom-select' multiple >
				<?php
					foreach( $operators_to_show as $key => $value ) {
						?>
							<option value="<?=$value['username']?>"><?= ($value['full_name_am'] != '')? $value['full_name_am'] : $value['username']?></option>
						<?php
					}
				?>
			</select>
		</div>
		<div class='col-md-2' style='margin-top:60px'>
			<select class='form-control time_filter custom-select' multiple >
				<?php
					foreach( $time_to_show as $key => $value ) {
						?>
							<option value="<?=$value['time_start']?>-<?=$value['time_end']?>"><?=$value['time_start']?> - <?=$value['time_end']?></option>
						<?php
					}
				?>
			</select>
		</div>
		<div class='col-md-2' style='margin-top:60px'>
			<button class='btn btn-success btn_for_find_orders'>Check</button>
		</div>
	</div>
	<div class='col-md-12 for_charts'></div>
	<script src="https://code.highcharts.com/highcharts.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
	<script type="text/javascript">
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
		$(document).ready(function(){
			$(document).on("click",".btn_for_find_orders",function(){
				var from_date = $(".from_date").val();
				var to_date = $(".to_date").val();
				var operator_filter = $(".operator_filter").val();
				var time_filter = $(".time_filter").val();
				if(from_date && !$.isEmptyObject(operator_filter)){
					$.ajax({
						url: location.href,
			            type: 'post',
			            data: {
			                getordersforoperator: true,
			                from_date:from_date,
			                to_date:to_date,
			                operator_filter:operator_filter,
			                time_filter:time_filter
			            },
			            success: function(resp){
			            	if( resp.length > 4 ){
								$(".for_charts").empty();
			            		resp = JSON.parse(resp);
								for ( var i = 0 ; i < operator_filter.length ; i++ ){
									$(".for_charts").append("<div class='col-md-4 float-left' id='Chart_" + operator_filter[i] + "' style='margin-top:100px'></div>");
									CreateChart( operator_filter[i] , 'Chart_' + operator_filter[i] , resp[operator_filter[i]]);
								}
			            	}
			            }
					})
				}
			})
		})
		// var colors = ['#FF530D', '#E82C0C', '#FF0000', '#E80C7A', '#E80C7A', '#E80C7A', '#E80C7A', '#E80C7A', '#E80C7A', '#E80C7A', '#E80C7A', '#E80C7A', '#E80C7A'];
		function CreateChart(operator_name,conainer_id,data){
			Highcharts.chart(conainer_id, {
			    chart: {
			        plotBackgroundColor: null,
			        plotBorderWidth: null,
			        plotShadow: false,
			        type: 'pie',
			     //    plotOptions: {
				    //     pie: { //working here
				    //         colors: colors
				    //     }
				    // },
			    },
			    tooltip: {
		            style: {
		                fontSize:'18px'
		            }
		        },
				// colors:colors,
			    title: {
			        text:  operators_info[operator_name]['full_name_am'].split(' ')


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
			            name: 'Հաստատված',
			            size: 17,
			            y: data['hastatvac'],
			            // sliced: true,
			            // selected: true
			        }, {
			            name: 'Անավարտ',
			            color: 'red',
			            y: data['anavart']
			        }, {
			            name: 'Առաքված',
			            y: data['araqvac']
			        }, {
			            name: 'Չեղյալ',
			            y: data['chexyal']
			        }, {
			            name: 'Բաց թողնված',
			            color: 'black',
			            y: data['bac_toxnvac']
			        }, {
			            name: 'Ճանապարհին',
			            y: data['janaparhin']
			        }, {
			            name: 'Վերադարձրած',
			            y: data['veradarcrac']
			        }, {
			            name: 'Կոմունիկացիա',
			            color: 'blue',
			            y: data['komunikacia']
			        }, {
			            name: 'Դուբլիկատ',
			            y: data['dublikat']
			        }, {
			            name: 'Ավտոմատ',
			            y: data['avtomat']
			        }, {
			            name: 'Հրաժարվել է առաքել',
			            y: data['hrajarvele_araqel']
			        }, {
			            name: 'Պատրաստ',
			            y: data['patrast']
			        }, {
			            name: 'Հաստատված առաքիչի կողմից',
			            y: data['hastatvac_araqichi_koxmic']
			        }]
			    }]
			});
		}
	</script>
</body>
</html>