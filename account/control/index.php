<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
$userInfo = getwayConnect::getwayData("SELECT * FROM user where uid = '" . $uid . "'");
page::cmd();
if(isset($_REQUEST["submit"]) && $_REQUEST["submit"] == "add")
{
	auth::userAdminReg($_REQUEST["username"],$_REQUEST["password"],$_REQUEST["level"],$secureKey);
	header("Refresh:0");
}
if(isset($_REQUEST["action"]) && $_REQUEST["action"] == "activate")
{
	if(isset($_REQUEST["mode"]) && isset($_REQUEST["uid"])){
		action::activation($_REQUEST["uid"],$_REQUEST["mode"]);
	}
}
// Added By Hrach
if(isset($_REQUEST['setSecureLogin']) && $_REQUEST['setSecureLogin']){
    $user_id = $_REQUEST['user_id'];
    $secure_auth = $_REQUEST['secure_auth'];
    getwayConnect::getwaySend("UPDATE user set secure_auth='" . $secure_auth."' where id = '" . $user_id . "'");
    return true;
}
/*if(is_file("lang/language_{$cc}.php"))
{
	include("lang/language_{$cc}.php");
}else{
	include("lang/language_am.php");
}*/
function GetOffOnAction($variable){
    $off_on_actions = getwayConnect::getwayData("SELECT * from off_on where variable = '" . $variable . "'");
    return ($off_on_actions[0]) ? $off_on_actions[0]['action'] : null;
}
$open_chart_page_control = GetOffOnAction('open_chart_page_control');
$open_send_mail_settings_page_control = GetOffOnAction('open_send_mail_settings_page_control');
$open_region_settings_page_control = GetOffOnAction('open_region_settings_page_control');
$open_organisations_settings_page_control = GetOffOnAction('open_organisations_settings_page_control');
$open_log_query_page_control = GetOffOnAction('open_log_query_page_control');
$open_open_chart_for_orders_page_control = GetOffOnAction('open_open_chart_for_orders_page_control');
$open_open_chart_for_operators_page_control = GetOffOnAction('open_open_chart_for_operators_page_control');
$open_worked_hours_page_control = GetOffOnAction('open_worked_hours_page_control');
$open_off_on_page_control = GetOffOnAction('open_off_on_page_control');
$open_loyal_customers_page_control = GetOffOnAction('open_loyal_customers_page_control');
$open_country_payment_page_control = GetOffOnAction('open_country_payment_page_control');
$open_sku_code_page_control = GetOffOnAction('open_sku_code_page_control');
$open_deliver_driver = GetOffOnAction('open_deliver_driver');

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
		<title>Controller</title>
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
		      <li class="dropdown" id="menuDrop">
			<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Filters <span class="caret"></span></a>
			<ul class="dropdown-menu" role="menu" style="text-align:center;">
			  <?php
				$fData = page::buildFilter($level[0]["user_level"],"control");
				for($fi = 0 ; $fi < count($fData);$fi++)
				{
					//echo "<li class=\"divider\"></li>";
					//echo "<li class=\"dropdown-header\">{$fData[$fi][0]}</li>";
					echo "<li>{$fData[$fi][1]}</li>";
				}
			  ?>

			</ul>
		      </li>
		    </ul>
		  </div><!--/.nav-collapse -->
		</div>
	      </nav>

		<div class="container" style="margin-top:81px;width: 100%">
			<p>
				<div class="btn-group" role="group" aria-label="...">
					<?php
						if($userInfo[0]['id'] == 4 || $open_chart_page_control == 1){
						?>
							<a href="chart_overall.php" class="btn btn-default">OPEN CHART <span class="glyphicon glyphicon-signal" aria-hidden="true"></a>
						<?php
						}
					?>
					<?php
						if($userInfo[0]['id'] == 4 || $open_send_mail_settings_page_control == 1){
						?>
							<a href="send_mail.php" class="btn btn-default">SEND MAIL SETTINGS <span class="glyphicon glyphicon-envelope" aria-hidden="true"></a>
						<?php
						}
					?>
					<?php
						if($userInfo[0]['id'] == 4 || $open_region_settings_page_control == 1){
						?>
							<a href="regions.php" class="btn btn-default">Regions SETTINGS</a>
						<?php
						}
					?>
					<?php
						if($userInfo[0]['id'] == 4 || $open_organisations_settings_page_control == 1){
						?>
							<a href="organisations.php" class="btn btn-default">Organisations SETTINGS</a>
						<?php
						}
					?>
					<?php
						if($userInfo[0]['id'] == 4 || $open_log_query_page_control == 1){
						?>
							<a href="./datatable/index.php" class="btn btn-default">Log Query</a>
						<?php
						}
					?>
					<?php
						if($userInfo[0]['id'] == 4 || $open_open_chart_for_orders_page_control == 1){
						?>
							<a href="order_chart.php" target='_blank' class="btn btn-default">OPEN CHART FOR ORDERS <span class="glyphicon glyphicon-signal" aria-hidden="true"></a>
						<?php
						}
					?>
					<?php
						if($userInfo[0]['id'] == 4 || $open_open_chart_for_operators_page_control == 1){
						?>
							<a href="operator_chart.php" target='_blank' class="btn btn-default">OPEN CHART FOR OPERATORS <span class="glyphicon glyphicon-signal" aria-hidden="true"></a>
						<?php
						}
					?>
					<?php
						if($userInfo[0]['id'] == 4 || $open_worked_hours_page_control == 1){
						?>
							<a href="worked_hours.php" target='_blank' class="btn btn-default">Worked Hours <span class="glyphicon glyphicon-cog" aria-hidden="true"></a>
						<?php
						}
					?>
					<?php
						if($userInfo[0]['id'] == 4 || $open_off_on_page_control == 1){
						?>
							<a href="off_on.php" target='_blank' class="btn btn-default">Off / On <span class="glyphicon glyphicon-adjust" aria-hidden="true"></a>
						<?php
						}
					?>
					<?php
						if($userInfo[0]['id'] == 4 || $open_loyal_customers_page_control == 1){
						?>
							<a href="loyal_customers.php" target='_blank' class="btn btn-default">Loyal Customers <span class="glyphicon glyphicon-floppy-save" aria-hidden="true"></a>
						<?php
						}
					?>
					<?php
						if($userInfo[0]['id'] == 4 || $open_country_payment_page_control == 1){
						?>
							<a href="country_payment.php" target='_blank' class="btn btn-default">Country Payment <span class="glyphicon glyphicon-globe" aria-hidden="true"></a>
						<?php
						}
					?>
					<?php
						if($userInfo[0]['id'] == 4 || $userInfo[0]['id'] == 38 || $open_sku_code_page_control == 1){
						?>
							<a href="sku_editor.php" target='_blank' class="btn btn-default">SKU editor <span class="glyphicon glyphicon-link" aria-hidden="true"></a>
						<?php
						}
					?>
					<?php
						if($userInfo[0]['id'] == 4 || $open_deliver_driver == 1){
						?>
							<a href="delivery_driver.php" target='_blank' class="btn btn-default">Delivery Drivers <span class="glyphicon glyphicon-map-marker" aria-hidden="true"></a>
						<?php
						}
					?>
					<?php
						if($userInfo[0]['id'] == 4){
						?>
							<a href="user_info.php" target='_blank' class="btn btn-default">Users Info <span class="glyphicon glyphicon-map-marker" aria-hidden="true"></a>
						<?php
						}
					?>
					<?php
						if($userInfo[0]['id'] == 4){
						?>
							<a href="partner_info.php" target='_blank' class="btn btn-default">Partners Info <span class="glyphicon glyphicon-star" aria-hidden="true"></a>
						<?php
						}
					?>
					<a href="translate_names_last_names.php" target='_blank' class="btn btn-default">Translate First and Last names<span class="glyphicon glyphicon-pencil" aria-hidden="true"></a>
					<?php
						if($userInfo[0]['id'] == 4){
						?>
							<a href="create_cvs.php" target='_blank' class="btn btn-default">Create CVS <span class="glyphicon glyphicon-map-marker" aria-hidden="true"></a>
						<?php
						}
					?>
					<?php
						if($userInfo[0]['id'] == 4){
						?>
							<a href="disadvantages_info.php" target='_blank' class="btn btn-default">Թերություններ</a>
						<?php
						}
					?>
					
				</div>
			</p>
			<?php if(in_array('99', $levelArray)){ ?>
				<form id="addUser" name="addU" method="POST" enctype="multipart/form-data">
					<input type="text" name="username" placeholder="username" required />
					<input type="text" name="password" placeholder="password" required />
					<input type="text" name="level" placeholder="access level" required />
					<input type="file" name="image" placeholder="user image" accept="image/png, image/jpeg, image/jpg" required />
					<input type="submit" name="submit" value="add">
				</form>
														
				<table class="table table-bordered">
					<thead>
						<tr>
						<th>id</th>
						<th>Uniq Id</th>
						<th>Username</th>
						<th>level</th>
						<th>Secure Login</th>
						<th>actions</th>
						</tr>
					</thead>
					<tbody>
					<?php
					$userList = auth::getUserListByUserActive();
					for($i = 0; $i < count($userList); $i++)
					{

					?>
						<tr>
						<td><?=$userList[$i]["id"]?></td>
						<td><a href="user.php?uid=<?=$userList[$i]["uid"]?>&pass=true" target="_blank"><?=$userList[$i]["uid"]?></a> </td>
						<td><a href="user.php?uid=<?=$userList[$i]["uid"]?>" target="_blank"><?=$userList[$i]["username"]?><br><?=$userList[$i]["full_name_am"]?></a></td>
						<td><?=$userList[$i]["user_level"]?></td>
						<td>
							<input type="checkbox" <?php echo ($userList[$i]['secure_auth'] == 1)? "checked" : '' ?> class="secureLoginEvent" data-user-id="<?=$userList[$i]["id"]?>">
						</td>
						<th><a href="?action=activate&mode=<?=$userList[$i]["user_active"]?>&uid=<?=$userList[$i]["id"]?>" title="<?=($userList[$i]["user_active"] == 1 ? 'Active' : 'Disable')?>"><img src="<?=$rootF?>/template/icons/actions/<?=$userList[$i]["user_active"]?>.png"/></a><!--<a href="?action=remove&uid=<?=$userList[$i]["id"]?>" title="remove"><img src="<?=$rootF?>/template/icons/actions/remove.png"></img></a>--></th>
						</tr>
						<?php
					}
					?>
					</tbody>
					</table>
				<?php } ?>
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
			
			$(document).ready(function(){
				$(document).on("change",".secureLoginEvent",function(){
					var user_id = $(this).attr('data-user-id');
					var secure_auth = 0;
					if($(this).is(':checked')){
						secure_auth = 1;
					}
					$.ajax({
		                url: location.href,
		                type: 'post',
		                data: {
		                    setSecureLogin: true,
		                    secure_auth: secure_auth,
		                    user_id: user_id,
		                },
		                success: function(resp){
		                    console.log(resp)
		                }
		            })
				})
			})
		</script>
	</body>
</html>