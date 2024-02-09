<?php
session_start();
$pageName = "flower";
$rootF = "../..";


include_once $_SERVER['DOCUMENT_ROOT']."/controls/FlowersForms.php";

include($rootF . "/apay/pay.api.php");
include($rootF . "/configuration.php");
page::cmd();

$access = auth::checkUserAccess($secureKey);
$allData = array();
$buildClient = "";
$uid = "";
$level = "";
$userData = "";
$cc = "am";
$user_country = '0';
if (!$access) {
    header("location:../../login");
} else {
    $uid = $_COOKIE["suid"];
    $level = auth::getUserLevel($uid);
    page::accessByLevel($level[0]["user_level"], $pageName);
    $levelArray = explode(",", $level[0]["user_level"]);
    $userData = auth::checkUserExistById($uid);
    $cc = $userData[0]["lang"];
    $user_country = $userData[0]["country_short"];
    if (is_file("lang/language_{$cc}.php")) {
        include("lang/language_{$cc}.php");
    } else {
        include("lang/language_am.php");
    }
}
$strict_country = ($user_country > 0) ? 'AND `delivery_region` = 4 ' : '';
$root = true;
include("products/engine/engine.php");
include("products/engine/storage.php");


 



storage::$user_id = $userData[0]['id'];
if (isset($_SESSION['storage'])) {
    storage::$selected_storage = $_SESSION['storage'];
} else {
    storage::$selected_storage = storage::get_default();
}
if (!storage::user_storage_enabled()) {
    storage::$selected_storage = storage::get_default();
}

$engine = new engine();
$get_lvl = explode(',', $level[0]["user_level"]);
//empty(array_intersect(array(89),explode(",",$get_lvl[0])))
$regionData = page::getRegionFromCC($cc);
date_default_timezone_set("Asia/Yerevan");
$pahest = (strtolower($cc) != 'am') ? '`country` = {$cc}' : '';


function getConstant($value){
    if (defined($value)) { 
        return constant($value);
    } else {
        return $value;
    }
}

$driversArray = FlowersForm::getDriversList();
$selectionOption = "";

foreach ($driversArray  as $keyDriver => $valueDriver){
    $selectionOption .= '<option value="'.$keyDriver.'">'. getConstant($valueDriver["name"]).'</option>';
}
/// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
 
//$driversArray = FlowersForms::getDriversList() ;
//print_r($driversArray);



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
    <link rel="stylesheet" href="<?= $rootF ?>/template/account/sidebar.css">
    <!-- Bootstrap minified CSS -->
    <link rel="stylesheet" href="<?= $rootF ?>/template/bootstrap/css/bootstrap.min.css">
    <!-- Bootstrap optional theme -->
    <link rel="stylesheet" href="<?= $rootF ?>/template/bootstrap/css/bootstrap-theme.min.css">
    <link rel="stylesheet" href="<?= $rootF ?>/template/datepicker/css/datepicker.css">
    <link rel="stylesheet" href="<?= $rootF ?>/template/rangedate/daterangepicker.css"/>
    <link rel="stylesheet" href="index_css.css"/>
    <title>Flower Orders</title>
    <style type="text/css">
        .highlight{
            background-color: yellow;color:black;font-size: 12px;
        }
		@media print {
			.hidden-print {
				display: none !important;
			}
			.article .text.short {
				height: 100%;
				overflow: auto;
			}
		}
    </style>
</head>
<body>
    
  
<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar"
                    aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#">RG-SYSTEM / <?= strtoupper($userData[0]["username"]); ?></a>
        </div>
        <div id="navbar" class="navbar-collapse collapse" aria-expanded="false">
            <ul class="nav navbar-nav">
                <?= page::buildMenu($level[0]["user_level"]) ?>
                <li class="dropdown" id="menuDrop">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                       aria-expanded="false"><?= (defined('FILTER')) ? FILTER : 'FILTER'; ?> <span class="caret"></span></a>
                    <ul class="dropdown-menu" role="menu" style="text-align:center;">
                        <?php
                        $fData = page::buildFilter($level[0]["user_level"], $pageName);
                        for ($fi = 0; $fi < count($fData); $fi++) {
                            echo "<li>{$fData[$fi][1]}</li>";
                        }
                        ?>
                    </ul>
                    <?php if (max(page::filterLevel(3, $levelArray)) >= 33): ?>
                <li><a href="order.php"
                       target="_blank"><?= (defined('ADD_NEW_ORDER')) ? ADD_NEW_ORDER : 'ADD_NEW_ORDER'; ?></a></li>
            <?php endif; ?>
                </li>
            </ul>
        </div>
    </div>
</nav>
     
<ol class="breadcrumb" id="activeFilters"
    style="position:fixed;top:51px;width: 100%;z-index: 99;border-bottom:dashed #777 1px;">
</ol>

<div class="container" style="margin-top:85px;width: 100%">
    <h3 class="hidden-print"><?= (defined('RG_ORDER_SYSTEM')) ? RG_ORDER_SYSTEM : 'RG_ORDER_SYSTEM'; ?></h3>
	<?php
	//(isset($userData[0]["username"])
	$this_month_total = 0;
	$last_month_total = 0;
	if(isset($userData[0]["username"]) && isset($userData[0]["earnings_rate"])){
		$json_currency = ($jsc_data = file_get_contents('currency.json')) ? json_decode($jsc_data,true) : false;
		$last_year = date("Y",strtotime('first day of last month'));
		$last_month = date('m', strtotime('first day of last month'));
		$this_year = date("Y");
		$this_month = date('m');
		$last_month_query = "SELECT price,currency,pNetcost FROM `rg_orders` 
								WHERE `operator` = '{$userData[0]["username"]}'
									AND 
								`order_defect` = 0 
									AND 
								`out_defect` = 0 
									AND 
								`pNetcost` > 0
									AND 
								YEAR(`created_date`) = '{$last_year}'
									AND 
								MONTH(`created_date`) = '{$last_month}'
									AND 
								`delivery_status` = 3
								";
								//echo $last_month_query;
		$this_month_query = "SELECT price,currency,pNetcost FROM `rg_orders` 
								WHERE `operator` = '{$userData[0]["username"]}' 
									AND 
								`order_defect` = 0 
									AND 
								`out_defect` = 0 
									AND
								`pNetcost` > 0
									AND 
								YEAR(`created_date`) = '{$this_year}'
									AND 
								MONTH(`created_date`) = '{$this_month}'
									AND 
								`delivery_status` = 3
								";
		$this_month_data = getwayConnect::getwayData($this_month_query,PDO::FETCH_ASSOC);
		$last_month_data = getwayConnect::getwayData($last_month_query,PDO::FETCH_ASSOC);
		if(is_array($json_currency)){
			if(is_array($this_month_data) && $this_month_data> 0){
				foreach($this_month_data as $tmd){
					if(isset($json_currency[$tmd['currency']]) && isset($json_currency[$tmd['currency']][0])){
						//echo  $json_currency[$tmd['currency']][1].'*'.$tmd['price'].'='.($json_currency[$tmd['currency']][1]*$tmd['price'])."\n";
						$tmd['price'] = (float)($json_currency[$tmd['currency']][1]*$tmd['price']);
						$real_value = (float)$tmd['price']-(float)$tmd['pNetcost'];
						$this_month_total += (float)(($real_value*(float)$userData[0]["earnings_rate"])/100);
					}
				}
			}
			if(is_array($last_month_data) && $last_month_data> 0){
				foreach($last_month_data as $lmd){
					if(isset($json_currency[$lmd['currency']]) && isset($json_currency[$lmd['currency']][0])){
						//echo  $json_currency[$lmd['currency']][1].'*'.$lmd['price'].'='.($json_currency[$lmd['currency']][1]*$lmd['price'])."\n";
						$lmd['price'] = (float)($json_currency[$lmd['currency']][1]*$lmd['price']);
						$real_value = (float)$lmd['price']-(float)$lmd['pNetcost'];
						$last_month_total += (float)(($real_value*(float)$userData[0]["earnings_rate"])/100);
					}
				}
			}
		}
	}
	?>
	<div class="hidden-print">
	Last month: <?=number_format((float)$last_month_total,2,'.',',');?><br/>
	This month: <?=number_format((float)$this_month_total,2,'.',',');?><br/>
	</div>
    <div style="display: inline-block;" class="hidden-print">
        <div class="btn-group" role="group" aria-label="...">
            <?php
            if (max(page::filterLevel(3, $levelArray)) >= 33) {
                ?>
				
                <button class="btn btn-default" name="drf" id="1" onclick="filter(this,true);" value="<?= date("Y-m-d"); ?> to <?= date("Y-m-d"); ?>"><?= (defined('TODAY')) ? TODAY : 'TODAY'; ?>
                    (<?= (max(page::filterLevel(3, $levelArray)) >= 33) ? getwayConnect::getwayCount("SELECT count(*) FROM rg_orders WHERE (delivery_date = '" . date("Y-m-d") . "' OR created_date =  '" . date("Y-m-d") . "') {$strict_country}") : "<strong id=\"shopCT\"></strong>"; ?>)
                </button>
				
                <button class="btn btn-default" name="drf" id="1" onclick="filter(this,true);" value="<?= date("Y-m-d", time() + 86400); ?> to <?= date("Y-m-d", time() + 86400); ?>"><?= (defined('TOMORROW')) ? TOMORROW : 'TOMORROW'; ?>
                    (<?= getwayConnect::getwayCount("SELECT count(*) FROM rg_orders WHERE (delivery_date = '" . date("Y-m-d", time() + 86400) . "' OR created_date = '" . date("Y-m-d", time() + 86400) . "') {$strict_country}"); ?>)
                </button>
				
                <button class="btn btn-default" name="drf" id="1" onclick="filter(this,true);" value="<?= date("Y-m-d", time() - 86400); ?> to <?= date("Y-m-d", time() - 86400); ?>"><?= (defined('YESTERDAY')) ? YESTERDAY : 'YESTERDAY'; ?>
                    (<?= getwayConnect::getwayCount("SELECT count(*) FROM rg_orders WHERE (delivery_date = '" . date("Y-m-d", time() - 86400) . "' OR created_date = '" . date("Y-m-d", time() - 86400) . "') {$strict_country}"); ?>)
                </button>
                <button class="btn btn-default" onclick="totalResset();" value=""><?= (defined('RESET')) ? RESET : 'RESET'; ?></button>
                <button class="btn btn-default" onclick="filter(null,true);"><?= (defined('REFRESH')) ? REFRESH : 'REFRESH'; ?></button>
                <select class="btn btn-default"  style="height: 34px;" id="showCount" onchange="showCount(this);">
                    <option value="150" selected>150</option>
                    <option value="200">200</option>
                    <option value="1000">1000</option>
                    <option value="10000">10000</option>
                    <!--<option value="false">ALL</option>-->
                </select>
                <?php
            } else {
                ?>
                <button class="btn btn-default" name="adf" id="17" onclick="filter(this,true);" value="<?= date("Y-m-d", time() - 86400); ?>"><?= (defined('YESTERDAY')) ? YESTERDAY : 'YESTERDAY'; ?></button>
                <button class="btn btn-default" name="adf" id="17" onclick="filter(this,true);" value="<?= date("Y-m-d"); ?>"><?= (defined('TODAY')) ? TODAY : 'TODAY'; ?></button>
                <button class="btn btn-default" name="adf" id="17" onclick="filter(this,true);" value="<?= date("Y-m-d", time() + 86400); ?>"><?= (defined('TOMORROW')) ? TOMORROW : 'TOMORROW'; ?></button>

                <?php
            }
            ?>

        </div>
        <br/><br/>
		
        <div class="btn-group" id="incomplited" style="font-weight: bold;">
            <button class="btn btn-default" name="isdefected" id="35" onclick="filter(this,true);" value="1 to 1" data-filter-name="<?= (defined('IS_DEFECTED')) ? IS_DEFECTED : 'IS_DEFECTED'; ?>"><?= (defined('IS_DEFECTED')) ? IS_DEFECTED : 'IS_DEFECTED'; ?>(<?=getwayConnect::getwayCount("SELECT count(*) FROM `rg_orders` WHERE `out_defect` = 1 OR `order_defect` = 1 ");?>)</button>

            <?php if(max(page::filterLevel(3, $levelArray)) >= 34): ?>
                <button onclick="sendMail()" class="btn btn-default"><?= (defined('SEND_MAIL')) ? SEND_MAIL : 'SEND_MAIL'; ?></button>
                <button class="btn btn-default" style="max-height: 34px;"  name="stf" id="2" onclick="filter(this,true);" value="2" data-filter-name="<?= (defined('PANDING_ORDER')) ? PANDING_ORDER : 'PANDING_ORDER'; ?>">
                    <?= (getwayConnect::getwayCount("SELECT count(*) FROM rg_orders WHERE delivery_status = 2") > 0) ? "<strong style=\"color:#ff0000\">" . getwayConnect::getwayCount("SELECT count(*) FROM rg_orders WHERE delivery_status = 2") . "</strong>" : 0; ?>
                    <img src="<?= $rootF; ?>/template/icons/status/2.png" height="20" />
                </button>
				<div class="btn-group" role="group" id="printyfy">
					<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					  <?= Hide_on_Print;?>
					  <span class="caret"></span>
					</button>
					<ul class="dropdown-menu">
					  <li><label for="id_hide">ID</label><input type="checkbox" id="id_hide" onclick="hide_on_print($(this),'hide-1')" checked/></li>
					  <li><label for="flag_hide"><?=COUNTRY;?></label><input type="checkbox" id="flag_hide" onclick="hide_on_print($(this),'hide-2')" checked/></li>
					  <li><label for="day_hide"><?= (empty(array_intersect(array(89), explode(",", $get_lvl[0])))) ? ((defined('DELIVERY_DAY')) ? DELIVERY_DAY : 'DELIVERY_DAY') : ((defined('TRANSFER_DAY')) ? TRANSFER_DAY : 'TRANSFER_DAY'); ?></label><input type="checkbox" id="day_hide" onclick="hide_on_print($(this),'hide-3')" /></li>
					  <li><label for="status_hide"><?=STATUS;?></label><input type="checkbox" id="status_hide" onclick="hide_on_print($(this),'hide-4')" checked/></li>
					  <li><label for="cday_hide"><?=ORDER_DAY;?></label><input type="checkbox" id="cday_hide" onclick="hide_on_print($(this),'hide-5')" checked/></li>
					  <li><label for="source_hide"><?=ORDER_SOURCE;?></label><input type="checkbox" id="source_hide" onclick="hide_on_print($(this),'hide-6')" checked/></li>
					  <li><label for="price_hide"><?=ORDER_PRICE?></label><input type="checkbox" id="price_hide" onclick="hide_on_print($(this),'hide-7')" checked/></li>
					  <li><label for="product_hide"><?= (empty(array_intersect(array(89), explode(",", $get_lvl[0])))) ? ((defined('ORDERED_PRODUCTS')) ? ORDERED_PRODUCTS : 'ORDERED_PRODUCTS') : ((defined('TO_MEET')) ? TO_MEET : 'TO_MEET'); ?></label><input type="checkbox" id="product_hide" onclick="hide_on_print($(this),'hide-8')" /></li>
					  <li><label for="receiver_hide"><?= (empty(array_intersect(array(89), explode(",", $get_lvl[0])))) ? ((defined('ORDER_RECEIVER')) ? ORDER_RECEIVER : 'ORDER_RECEIVER') : ((defined('WHERE_TO_BE')) ? WHERE_TO_BE : 'WHERE_TO_BE'); ?></label><input type="checkbox" id="receiver_hide" onclick="hide_on_print($(this),'hide-9')" /></li>
					  <li><label for="rAddress_hide"><?= (empty(array_intersect(array(89), explode(",", $get_lvl[0])))) ? ((defined('RECEIVER_ADDRESS')) ? RECEIVER_ADDRESS : 'RECEIVER_ADDRESS') : ((defined('TO_WHERE')) ? TO_WHERE : 'TO_WHERE'); ?></label><input type="checkbox" id="rAddress_hide" onclick="hide_on_print($(this),'hide-10')" /></li>
					  <li><label for="rPhone_hide"><?=ORDER_RECEIVER;?></label><input type="checkbox" id="rPhone_hide" onclick="hide_on_print($(this),'hide-11')" checked/></li>
					  <li><label for="notes_hide"><?=NOTES_FOR_ALL;?></label><input type="checkbox" id="notes_hide" onclick="hide_on_print($(this),'hide-12')" checked/></li>
					  <li><label for="sender_hide"><?=ORDER_SENDER?></label><input type="checkbox" id="sender_hide" onclick="hide_on_print($(this),'hide-14')" checked/></li>
					  <li><label for="sContact_hide"><?=SENDER_CONTACS;?></label><input type="checkbox" id="sContact_hide" onclick="hide_on_print($(this),'hide-15')" checked/></li>
					  <li><label for="sAddress_hide"><?=SENDER_ADDRESS;?></label><input type="checkbox" id="sAddress_hide" onclick="hide_on_print($(this),'hide-16')" checked/></li>
					  <li><label for="gCard_hide"><?=GREETING_CARD;?></label><input type="checkbox" id="gCard_hide" onclick="hide_on_print($(this),'hide-17')" checked checked/></li>
					  <li><label for="nFlorist_hide"><?= (empty(array_intersect(array(89), explode(",", $get_lvl[0])))) ? ((defined('NOTES_FOR_ALL')) ? NOTES_FOR_ALL : 'NOTES_FOR_ALL') : ((defined('NOTES_FOR_DRIVER')) ? NOTES_FOR_DRIVER : 'NOTES_FOR_DRIVER'); ?></label><input type="checkbox" id="nFlorist_hide" onclick="hide_on_print($(this),'hide-18')" /></li>
					  <li><label for="sPoint_hide"><?=SELL_POINT;?></label><input type="checkbox" id="sPoint_hide" onclick="hide_on_print($(this),'hide-19')" checked/></li>
					  <li><label for="log_hide">Log</label><input type="checkbox" id="log_hide" onclick="hide_on_print($(this),'hide-20')" checked/></li>
					</ul>
				</div>
            <?php endif;?>
        </div>
    </div>
	
    <div style="display: inline-block;float:right;" class="hidden-print">
        <?php
        if ($cc == "am") {
            $shuka = $engine->categoryDanger(1, false, storage::$user_id, storage::$selected_storage);
            $arevtur = $engine->categoryDanger(1, true, storage::$user_id, storage::$selected_storage);
            $date = $engine->getLastEdit(false);
            $passed = strtotime($engine->dateutc()) - strtotime($date);
            $hours = round($passed / 3600);
            $minutes = round($passed / 60);
            $par = "";
            if ($hours > 5) {
                $par = "<img src=\"products/template/images/par-par.gif\" align=\"left\" height=\"50\">";
            }
            ?>

            <span style="font-size:20px;display: inline-block;">
                <a href="products/index.php?request=arajark" target="_blank">
                    <strong><?= (defined('WHAT_TO_ADVICE')) ? WHAT_TO_ADVICE : 'WHAT_TO_ADVICE'; ?></strong>
                    <img src="products/template/images/cancellation.jpg" height="50px"/>
                </a>
            </span>
            <div style="font-size:10px;padding-left:50px;display: inline-block;">
                <strong style="font-size:16px">(<?= storage::selected_name(); ?>)</strong>

                <strong style="font-size:20px;padding-left:50px;">
                    <a href="products/index.php" target="_blank"><?= (defined('PRODUCTS')) ? PRODUCTS : 'PRODUCTS'; ?></a>&nbsp;&nbsp;&nbsp;
                </strong>
                <br><br><?= ($shuka > 5 && $shuka < 10 || $arevtur > 5 && $arevtur < 10) ? "<img src=\"products/template/images/warning-white.gif\"  align=\"left\" height=\"50px\">" : (($shuka > 0 || $arevtur > 0) ? "<img src=\"products/template/images/warning.gif\" align=\"left\" height=\"50px\">" : '') ?>
                <strong style="font-size:20px"><?= (defined('MARKET')) ? MARKET : 'MARKET'; ?>(<strong
                        style="color:<?= ($shuka <= 0) ? "green" : "red"; ?>;"><?= $shuka ?></strong>)<?= (defined('TRADE')) ? TRADE : 'TRADE'; ?>(<strong
                        style="color:<?= ($arevtur <= 0) ? "green" : "red"; ?>;"><?= $arevtur ?></strong>)<?= $par ?>
                </strong>
                <br>
                <strong style="font-size:12px"><?= (defined('CHANGED')) ? CHANGED : 'CHANGED'; ?><?= ($minutes > 60) ? " {$hours} ".((defined('HOUR_AGO')) ? HOUR_AGO : 'HOUR_AGO').":" : " {$minutes} ".((defined('MINUTE_AGO')) ? MINUTE_AGO : 'MINUTE_AGO').":"; ?></strong>
            </div>
            <?php
        }
        ?>
    </div>
	<div style="clear: both"></div>
        
    <div class="table">
          
        <table class="table table-bordered">
            <thead>
                
                
            <tr class="success">
               
                <?php
                if (max(page::filterLevel(3, $levelArray)) >= 33) {
                    ?>
                    <th class="hide-1 hidden-print" nowrap="nowrap">
                        <div id="loading"><img src="<?= $rootF; ?>/template/icons/loader.gif"></div>
                        <input type="checkbox" onclick="checkAll(this);">
                        <button class="btn btn-default" name="orderF" id="12" onclick="filter(this,true);" value="`id` ASC">#<strong
                                id="onC"></strong></button>
                    </th>
                    <th class="hide-2 hidden-print"><img src="<?= $rootF; ?>/template/icons/bonus_title.png"></th>
                    <th class="hide-3">
					<?= (empty(array_intersect(array(89), explode(",", $get_lvl[0])))) ? ((defined('DELIVERY_DAY')) ? DELIVERY_DAY : 'DELIVERY_DAY') : ((defined('TRANSFER_DAY')) ? TRANSFER_DAY : 'TRANSFER_DAY'); ?>
						<div class="btn-group" id="orderbyDayHour" style="font-weight: bold;min-width: 100px;">
							<!--<button class="btn btn-default" ></button>-->
								<button class="btn btn-default" name="orderF" id="12" onclick="filter(this,true);"
                                value="delivery_date DESC"><?=DAY?></button>
								<button class="btn btn-default" name="orderF" id="38" onclick="filter(this,true);"
                                value="delivery_time,delivery_time_manual DESC"><?=HOUR?></button>
						</div>
                    </th>
                    <?php
                } else {
                    ?>
                    <th class="hide-1 hidden-print">
                        <div id="loading"><img src="<?= $rootF; ?>/template/icons/loader.gif"></div>
                        #<strong id="onC"></strong></th>
                    <th class="hide-2"><?= (defined('DELIVERY_DAY')) ? DELIVERY_DAY : 'DELIVERY_DAY'; ?></th>
                    <?php
                }
                ?>
                    
                <th class="hide-4 hidden-print"><?= (max(page::filterLevel(3, $levelArray)) < 33) ? ((defined('DELIVERY_STATUS')) ? DELIVERY_STATUS : 'DELIVERY_STATUS') : "<img src=\"{$rootF}\/template/icons/exit.png\">"; ?></th>

        
                
     <?php 
      $levelsOfUser =$userData[0]["user_level"];
       if (strpos($levelsOfUser, '89') !== false || strpos($levelsOfUser, '30') !== false || strpos($levelsOfUser, '31') !== false ) {
     ?>
       <th>Գործողություն</th>  
    <?php } else {  ?>
        <th style="width: 0px"></th>    
    <?php } ?>            
                 
                <th class="hide-10">
                <?= (empty(array_intersect(array(89), explode(",", $get_lvl[0])))) 
                        ? ((defined('RECEIVER_ADDRESS')) ? RECEIVER_ADDRESS : 'RECEIVER_ADDRESS') 
                        : ((defined('TO_WHERE')) ? TO_WHERE : 'TO_WHERE'); ?>
                 </th>
                <?php
                if (max(page::filterLevel(3, $levelArray)) >= 33) {
                    ?>
                    <th class="hide-5 hidden-print">
                        <button class="btn btn-default" name="orderF" id="12" onclick="filter(this,true);" value="created_date DESC"><?= (defined('ORDER_DAY')) ? ORDER_DAY : 'ORDER_DAY'; ?></button>
                    </th>
                    <th class="hide-6 hidden-print"><?= (defined('ORDER_SOURCE')) ? ORDER_SOURCE : 'ORDER_SOURCE'; ?></th>
                    <?php
                    if (empty(array_intersect(array(89), explode(",", $get_lvl[0])))) {
                        ?>
                        <th class="hide-7 hidden-print"><img src="<?= $rootF ?>/template/icons/price.png" onclick="viewHidePrice();"></th>
                        <?php
                    }

                }
                ?>
                <th class="hide-8"><?= (empty(array_intersect(array(89), explode(",", $get_lvl[0])))) ? ((defined('ORDERED_PRODUCTS')) ? ORDERED_PRODUCTS : 'ORDERED_PRODUCTS') : ((defined('TO_MEET')) ? TO_MEET : 'TO_MEET'); ?></th>
                <th class="hide-9"><?= (empty(array_intersect(array(89), explode(",", $get_lvl[0])))) ? ((defined('ORDER_RECEIVER')) ? ORDER_RECEIVER : 'ORDER_RECEIVER') : ((defined('WHERE_TO_BE')) ? WHERE_TO_BE : 'WHERE_TO_BE'); ?></th>
                <?php
                if (max(page::filterLevel(3, $levelArray)) < 33) {
                    ?>
                    <th class="hide-22 hidden-print"><?= (defined('NOTES_FOR_FLORIST')) ? NOTES_FOR_FLORIST : 'NOTES_FOR_FLORIST'; ?></th>
                    <?php
                }
                ?>
               
                <th class="hide-11 hidden-print">
                <?= (defined('RECEIVER_PHONE')) ? RECEIVER_PHONE : 'RECEIVER_PHONE'; ?></td>
                <?php
                if (max(page::filterLevel(3, $levelArray)) < 33) {
                    ?>
                    <th class="hide-12 hidden-print"><?= (defined('GREETING_CARD')) ? GREETING_CARD : 'GREETING_CARD'; ?></th>
                    <?php
                } else {
                    ?>
                    <th class="hide-12 hidden-print">
                        <button class="btn btn-default show-ALL"><?= (defined('NOTES')) ? NOTES : 'NOTES'; ?></button>
                    </th>
                    <?php
                }
                ?>
                <?=(max(page::filterLevel(3, $levelArray)) >= 33) ? "" : "<th class=\"hide-13\">" . ((defined('HOW_WAS_DELIVERED')) ? HOW_WAS_DELIVERED : 'HOW_WAS_DELIVERED') . "</th>"; ?>
                <th class="hide-14 hidden-print"><?= (defined('ORDER_SENDER')) ? ORDER_SENDER : 'ORDER_SENDER'; ?></th>
                <th class="hide-15 hidden-print">
                <?= (defined('SENDER_PHONE')) ? SENDER_PHONE : 'SENDER_PHONE'; ?><?= (max(page::filterLevel(3, $levelArray)) >= 33) ? "<br/>" . ((defined('E_MAIL')) ? E_MAIL : 'E_MAIL') : ""; ?></td>
                <th class="hide-16 hidden-print">
                <?= (max(page::filterLevel(3, $levelArray)) >= 33) ? ((defined('SENDER_ADDRESS')) ? SENDER_ADDRESS : 'SENDER_ADDRESS') : ((defined('SENDER_COUNTRY')) ? SENDER_COUNTRY : 'SENDER_COUNTRY'); ?></td>
                <?php
                function changeNotes()
                {
                    global $get_lvl;
                    return (empty(array_intersect(array(89), explode(",", $get_lvl[0])))) ? ((defined('NOTES_FOR_FLORIST')) ? NOTES_FOR_FLORIST : 'NOTES_FOR_FLORIST') : ((defined('NOTES_FOR_DRIVER')) ? NOTES_FOR_DRIVER : 'NOTES_FOR_DRIVER');
                }

                if (max(page::filterLevel(3, $levelArray)) >= 33) {
                    ?>

                    <th class="hide-17 hidden-print"><?= (defined('GREETING_CARD')) ? GREETING_CARD : 'GREETING_CARD'; ?></th>
                    <th class="hide-18"><?= (max(page::filterLevel(3, $levelArray)) >= 33) ? changeNotes() : "<strong style=\"color:#ff0000\">" . ((defined('NOTES_FOR_FLORIST')) ? NOTES_FOR_FLORIST : 'NOTES_FOR_FLORIST') . "</strong>"; ?></th>

                    <th class="hide-19 hidden-print"><?= (defined('SELL_POINT')) ? SELL_POINT : 'SELL_POINT'; ?></th>
                    <th class="hide-20 hidden-print"><img src="<?= $rootF; ?>/template/icons/info.png"></th>
                    <?php
                }
                ?>
                <?= (max(page::filterLevel(3, $levelArray)) < 33) ? "<th class=\"hide-21\"><img src=\"{$rootF}/template/icons/info.png\"></th>" : ""; ?>
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
<script src="<?= $rootF ?>/template/bootstrap/js/bootstrap.min.js"></script>
<!--end initialize library-->
<!-- Menu Toggle Script -->
<!-- Bootstrap minified JavaScript -->
<script src="<?= $rootF ?>/template/js/accounting.min.js"></script>
<script src="<?= $rootF ?>/template/datepicker/js/bootstrap-datepicker.js"></script>
<script src="<?= $rootF ?>/template/js/phpjs.js"></script>
<script src="<?= $rootF ?>/template/rangedate/moment.min.js"></script>
<script src="<?= $rootF ?>/template/rangedate/jquery.daterangepicker.js"></script>
<script src="<?= $rootF ?>/template/js/imagelightbox.min.js"></script>

<div id="editor">
    <div id="editorMenu">
        <button id="editMM" onclick="mmwindow();" style="width:25px;height:25px;">+</button>
    </div>
    <div id="dataMsg">

    </div>
    <script src="pull.js?v=<?= rand(1, 99); ?>"></script>
</div>
<script type="text/javascript">
	var partners_ids = [<?=($pid_data = getwayConnect::getwayData("SELECT `filter_value` FROM `global_filters` WHERE `name` = 'FLOWERS_PARTNERS'")) ? $pid_data[0]['filter_value'] : '';?>];
    var timoutSet = null;
    var data = {};
    var send_data = "";
    var data_type = "flower";
    var fromP = 0;
    var toP = <?=(isset($userData[0]["username"]) && strtolower($userData[0]["username"]) == "ani") ? 1000 : 150;?>;//listi erkarutyun #1
    var whoreceived = <?=page::getJsonData("delivery_receiver");?>;
    var payType = <?=page::getJsonData("delivery_payment");?>;
    var sourceType = <?=page::getJsonData("delivery_source");?>;
    var timeType = <?=page::getJsonData("delivery_time");?>;
    var sellPoint = <?=page::getJsonData("delivery_sellpoint");?>;
    var subregionType = <?=page::getJsonData("delivery_subregion", "code");?>;
    var streetType = <?=page::getJsonData("delivery_street", "code");?>;
    var statusTitle = <?=page::getJsonData("delivery_status");?>;
    var recLang = <?=page::getJsonData("delivery_language");?>;
    var driver_name = <?=page::getJsonData("delivery_deliverer");?>;
    var driver_car = <?=page::getJsonData("delivery_drivers");?>;
    var order_reason = <?=page::getJsonData("delivery_reason");?>;
    window.sum_overall = {"total":0,"spend":0,"percent":0,"left_over":0};
    window.pNum = 0;
    window.fromPageCount = 0;
    var global_filter_text = '';
    var order_currency = {
        "USD": 478.2,
        "1": 478.2,
        "EUR": 560.3,
        "4": 560.3,
        "RUB": 8.3,
        "2": 8.3,
        "IRR": 0.0177,
        "6": 0.0177,
        "GBP": 640.7,
        "5": 640.7,
        "convert": function ($ISO, $price) {
            if (this[$ISO]) {
                return this[$ISO] * $price;
            } else {
                return $price;
            }
        },
        "pfp": function ($total, $actual) {
            return ($total > 0) ? (100 * $actual) / $total : 0;
        }
    };

    function firstToUpperCase(str) {
        return str.substr(0, 1).toUpperCase() + str.substr(1);
    }
    <?php
    if(max(page::filterLevel(3, $levelArray)) < 33)
    {
    ?>
    data["orderF"] = {"filter": 12, "value": "`delivery_time` ASC"};
    data["adf"] = {"filter": 17, "value": "<?=date("Y-m-d");?>"};
    <?php
    }
    ?>
    function filter(el, onfilter) {

        <?php if($user_country > 0){?>
        data["ccf"] = {"filter": 7, "value":<?=$user_country?>};
        <?php }?>
        if (el) {
            var element = jQuery("#" + el.id + " option:selected");
            if (element.attr("data-prel")) {
                hfilter(element.attr("data-prel"));
            }
        }
        $("#loading").css("display", "block");
        if (onfilter) {
            fromP = 0;
            if (data["adf"]) {
                //data["orderF"] = {"filter":12,"value":"delivery_time ASC"};
            }
            if (data["drf"]) {
                //data["orderF"] = {"filter":12,"value":"delivery_date DESC"};
            }
            
        }
		if (data['globalf']) {
			if($("input[name='globalf']").val().length <= 0){
				global_filter_text = false;
				delete data['globalf'];
			}
		}else{
			global_filter_text = false;
		}
		if($("input[name='globalf']").val()){
			if ($("input[name='globalf']").val().length > 0) {
				global_filter_text = $("input[name='globalf']").val();
			}else{
				global_filter_text = false;
			}
		}
        if (el) {
            if (!el.value || el.value == null || el.value == "") {
                delete data[el.name];
            } else {
                //data.push([el.id] = el.value;
                if(el.name == 'isdefected'){
                    data = {};
                }else{
                    delete data['isdefected'];
                }
                data[el.name] = {"filter": el.id, "value": el.value};
            }
        }
        if (onfilter) {
            if (data["orderF"]) {
                if (data["orderF"].value.search(/ASC/g) > 0) {
                    $("[id=" + data["orderF"].filter + "]").each(function () {
                        if ($(this).val() == data["orderF"].value) {
                            var TempValue = $(this).val();
                            TempValue = TempValue.replace(/ASC/g, "DESC");
                            $(this).val(TempValue);
                        }
                    });
                }
                if (data["orderF"].value.search(/DESC/g) > 0) {
                    $("[id=" + data["orderF"].filter + "]").each(function () {
                        if ($(this).val() == data["orderF"].value) {
                            var TempValue = $(this).val();
                            TempValue = TempValue.replace(/DESC/g, "ASC")
                            $(this).val(TempValue);
                        }
                    });
                }

            }
        }
        var activeFilter = "";
        var mu;
        for (mu in data) {
            //<li class=\"active\">Data</li>
	    if ($(el).attr("data-filter-name")) {
                activeFilter += "<li class=\"active\">" + $("button[id = " + data[mu].filter+"]").attr("data-filter-name") + "</li>";
            } else if ($("#" + data[mu].filter).attr("placeholder")) {
                activeFilter += "<li class=\"active\">" + $("#" + data[mu].filter).attr("placeholder") + ":" + data[mu].value + "</li>";
            } else if ($("#" + data[mu].filter).find(":selected").text()) {
                activeFilter += "<li class=\"active\">" + $("#" + data[mu].filter).find(":selected").text() + "</li>";
            } else if ($("#" + data[mu].filter).text()) {
                //activeFilter += "<li class=\"active\">"+$("#"+data[mu].filter).text()+"</li>";
            }
        }
        $("#activeFilters").html(activeFilter);
        var data_encode = base64_encode(json_encode(data));
        //console.log(data_encode);
        //console.log(base64_decode(data_encode));
        if (data) {
            send_data = "&encodedData=" + data_encode;
        } else {
            send_data = "";
        }
        var userFriendly = "class=\"active\"";
        var first = false;
        clearTimeout(timoutSet);
        timoutSet = setTimeout(function () {
            //start
            $.get("<?=$rootF?>/data.php?cmd=data&page=" + data_type + send_data + "&paginator=" + fromP + ":" + toP, function (get_data) {
                //console.log(get_data);
                var CCo = 0;
                var tableData = get_data.data;
                var countP = get_data.count;
                var is_defect = "";
                var is_important = "";
                var out_text = '<?=(defined("OUT")) ? OUT : "OUT";?>';
                var check_text = '<?=(defined("CHECK")) ? CHECK : "CHECK";?>';
                fromP = buildPaginator(countP, fromP, toP,pNum);
                sum_overall.total = 0;
                sum_overall.spend = 0;
                sum_overall.percent = 0;
                sum_overall.left_over = 0;
                var htmlData = "";
                var showA = "";
                if (countP > 0) {
                    for (var i = 0; i < tableData.length; i++) {
                        var d = tableData[i];
						window.partner_icon = (partners_ids.indexOf(parseInt(d.sell_point)) > -1) ? '<img width="25" src="<?=$rootF?>/template/icons/partner_icon.png" title="<?=(defined("PARTNER")) ? PARTNER : "PARTNER";?>"/>':'';
                        window.costage = '';
                        <?php
                        if(!empty(array_intersect(array(99), $get_lvl))){
                        ?>
                        d.price = number_format(d.price, '0', ',', '');
                        if (d.price > 0) {
                            //buy.am 16 15%
                            //memu.am 37 25%
                            var $price_differ = 0;
                            var $total_price = order_currency.convert(d.currency, d.price);

                            if (d.sell_point == 16) {
                                var $price_differ = ($total_price * 15 ) / 100;
                                $total_price = $total_price - $price_differ;
                            } else if (d.sell_point == 15) {
                                var $price_differ = ($total_price * 25 ) / 100;
                                $total_price = $total_price - $price_differ;
                            }
                            var $left_over_price = $total_price - d.pNetcost;
                            var $percent = order_currency.pfp(d.pNetcost, $left_over_price);
                            sum_overall.total += parseInt($total_price);
                            sum_overall.spend += parseInt(d.pNetcost);
                            sum_overall.percent += parseInt($percent);
                            sum_overall.left_over += parseInt($left_over_price);
                            //console.log($percent,$total_price,$left_over_price);
                            if ($percent <= 20) {
                                $percent = '<span class="label label-danger" title="Loss ratio">' + number_format($percent, '0', ',', '.') + '%</span>';
                            } else if ($percent > 20 && $percent <= 30) {
                                $percent = '<span class="label label-warning" title="Neutral ratio">' + number_format($percent, '0', ',', '.') + '%</span>';
                            } else if ($percent > 30 && $percent <= 40) {
                                $percent = '<span class="label label-default" style="background-color: yellow;color:darkslategray;" title="Low ratio">' + number_format($percent, '0', ',', '.') + '%</span>';
                            } else if ($percent > 40 && $percent <= 60) {
                                $percent = '<span class="label label-default" style="background-color: green;" title="Middle ratio">' + number_format($percent, '0', ',', '.') + '%</span>';
                            } else if ($percent > 60 && $percent <= 100) {
                                $percent = '<span class="label label-success" title="Higher ratio">' + number_format($percent, '0', ',', '.') + '%</span>';
                            } else if ($percent > 0 && $percent > 100) {
                                $percent = '<span class="label label-default" style="background-color: magenta;" title="Over ratio">' + number_format($percent, '0', ',', '.') + '%</span>';
                            }
                            costage = "<br/>" + number_format($total_price, '0', ',', '.') + " / " + number_format(d.pNetcost, '0', ',', '.') + " / " + $percent + " ";
                        }
                        <?php
                        }else{
                        ?>
                        d.price = number_format(d.price, '0', ',', '');
                        var $total_price = order_currency.convert(d.currency, d.price);
                        //console.log(d.sell_point);
                        if (d.price > 0 && (d.sell_point == 16 || d.sell_point == 15 || d.sell_point == 13 || d.sell_point == 18) && $total_price <= 30000) {

                            //buy.am 16 15%
                            //memu.am 15 25%
                            var $price_differ = 0;

                            if (d.sell_point == 16) {
                                var $price_differ = ($total_price * 15 ) / 100;
                                $total_price = $total_price - $price_differ;
                            } else if (d.sell_point == 15) {
                                var $price_differ = ($total_price * 25 ) / 100;
                                $total_price = $total_price - $price_differ;
                            }
                            var $left_over_price = $total_price - d.pNetcost;

                            costage = "<br/>" + number_format($total_price, '0', ',', '.') + " AMD";
                        }
                        <?php
                        }
                        ?>
						
                        <?php
                        if(!empty(array_intersect(array(89), explode(",", $get_lvl[0]))))
                        {
                        ?>
                        if (true) {//d.delivery_type == "2" || d.delivery_type == "4"
                            <?php
                            }
                            ?>
                            <?php

                            if(max(page::filterLevel(3, $levelArray)) < 33) {
                            ?>
							
                            if(<?=(isset($userData[0]["username"]) && strtolower($userData[0]["username"]) == "ani") ? 'd.delivery_type == "10" || d.delivery_type == "7" ':'false'?>){
								continue;
							}
							if (d.delivery_status == "13" || d.delivery_status == "12" || d.delivery_status == "11" || d.delivery_status == "7" || d.delivery_status == "6" || d.delivery_status == "1" || d.delivery_status == "3") {
                                <?php
                                }
                                ?>
                                if (first) {
                                    showA = userFriendly;
                                    first = false;
                                } else {
                                    showA = "";
                                    first = true;
                                }

                                var co = 0;
                                var monthNames = new Array("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");

                                var myDate = d.delivery_date.split("-");
                                if ((myDate[0] + myDate[1] + myDate[2]) == 0) {
                                    var newDate = myDate[2] + "-" + myDate[1] + "-" + myDate[0];
                                } else {
                                    var newDate = myDate[2] + "-" + monthNames[myDate[1] - 1] + "-" + myDate[0];
                                }
                                is_defect = (d.out_defect == 1) ? '<img width="25" src="products/template/images/animated/animated-flower-image-0001.gif"/>' : '';
                                is_defect_description = (d.	order_defect == 1) ? '<img width="25" src="<?=$rootF?>/template/images/red_r.png"/>' : '';
                                is_important = (d.important == 1) ? '<img width="25" src="<?=$rootF?>/template/icons/important/important.gif"/>' : '';
                                htmlData += "<tr " + showA + ">";
                                var check_defected = "<br><button onclick=\"CheckAccounting(" + d.id + ")\">"+check_text+"</button>";
                                <?php

                                if(max(page::filterLevel(3, $levelArray)) >= 33)
                                {
                                ?>
                                //#1
                                htmlData += "<td class=\"hide-1 hidden-print\" style=\"min-width:50px;\" nowrap><a href=\"order.php?orderId=" + d.id + "\" target=\"_blank\">N-" + d.id + "</a><br/><a class=\"hidden-print\" href=\"print.php?orderId=" + d.id + "\" target=\"_blank\"><img src=\"<?=$rootF?>/template/icons/print.png\"></a>&nbsp;<?=((empty(array_intersect(array(99), explode(",", $get_lvl[0]))))) ? '<a class=\"hidden-print\" target=\"_blank\" href=\"products/?cmd=out&orderId="+d.id+"&manual=true\">'.((defined("OUT")) ? OUT : "OUT").'</a>' : ''?><br><button class=\"hidden-print\" onclick=\"CheckAccounting(" + d.id + ")\">"+check_text+"</button><br><input class=\"hidden-print\" id=\"mailToSend\" type=\"checkbox\" value=\"" + d.id + "\" disabled></td>";
                                <?php
                                }else{
                                ?>
                                //#1

                                htmlData += "<td class=\"hide-1 hidden-print\" style=\"min-width:50px;\" nowrap>N-" + d.id + "<br/><a class=\"hidden-print\" href=\"print.php?orderId=" + d.id + "\" ><img src=\"<?=$rootF?>/template/icons/print.png\"></a>&nbsp;<a class=\"hidden-print\" href=\"products/?cmd=out&orderId=" + d.id + "&manual=true\">"+out_text+"</a>" + check_defected + "</td>";
                                <?php
                                }
                                if(max(page::filterLevel(3, $levelArray)) >= 33)
                                {
                                ?>
                                //#2
                                htmlData += "<td class=\"hide-2 hidden-print\"><img src=\"<?=$rootF?>/template/icons/bonus/" + d.bonus_type + ".png\"><br/><img src=\"<?=$rootF?>/template/icons/region/" + d.delivery_region + ".png\"></td>";
                                //#3
                                if (!timeType[d.delivery_time]) {
                                    timeType[d.delivery_time] = "";
                                }

                                var driverN = (driver_name[d.deliverer]) ? driver_name[d.deliverer] : '';
                                var delvrr = (d.deliverer > 0) ? "<img id=\"drvnameimg_" + d.id + "\" " 
                                        + "width=\"25px\" style=\"position:absolute;left:0;top:0;z-index:1;\" src=\"<?=$rootF?>/template/icons/drivers/" 
                                        + d.deliverer + ".png\" title=\"" + driverN + "\">" : '';
                                var carN = (driver_car[d.delivery_type]) ? " title=\"" + driver_car[d.delivery_type] + "\"" : '';
                               
				htmlData += "<td  class=\"hide-3\"><strong>" 
                                        + newDate + "</strong><br/>" 
                                        + timeType[d.delivery_time] 
                                        + "(" + d.delivery_time_manual 
                                        + ")<br/><div class=\"hidden-print\" style=\"position:relative;height:50px;\">" 
                                        + delvrr + "<img id=\"carImage_" + d.id + "\" width=\"70px\" style=\"position:absolute;top:0;\" src=\"<?=$rootF?>/template/icons/deliver/" 
                                        + d.deliverer + ".png\" " + carN 
                                        + "><img width=\"50px\" style=\"position:absolute;top:0;right:0;\" src=\"<?=$rootF?>/template/icons/ontime/" 
                                        + d.ontime + ".png\"></div></td>";
                                //#4
                                htmlData += "<td class=\"hide-4 hidden-print\"><img src=\"<?=$rootF?>/template/icons/status/" + d.delivery_status + ".png\" title=\"" + statusTitle[d.delivery_status] + "\">" + is_defect + is_defect_description + "</td>";
                                 
                                
                                htmlData += "<td>";
                                  
                                <?php
                                
                                $levelsOfUser =$userData[0]["user_level"];
                                
                                if (strpos($levelsOfUser, '89') !== false) {
                                
                                ?>  
                                  
                                  
                                var showSelect = "SELECT";  
                                if (d.deliverer > 0 ) {
                                  showSelect =  driverN;
                                } 
                                
                                htmlData += '<select style="width:100px" id="driver_'+d.id+ '" ><option value="' + d.deliverer + '">'+ showSelect +'</option> <?php echo $selectionOption ?></select><br>';
                               
                                htmlData += '<hr>';
                                htmlData += '<select id="stage_'+d.id+ '"  style="width:100px">';
                                
                                if (d.stage > 0) {
                                    htmlData += '<option value="'+ d.stage + '">' +  d.stage + '</option>'
                                } 
                             
                                
                                htmlData    += '<option value="0"></option>'
                                            + '<option value="1">1</option>'
                                            + '<option value="2">2</option>'
                                            + '<option value="3">3</option>'
                                            + '<option value="4">4</option>'
                                            + '<option value="5">5</option>'
                                            + '<option value="6">6</option>'
                                            + '<option value="7">7</option>'
                                            + '<option value="8">8</option>'
                                            + '<option value="9">9</option>'
                                            + '<option value="10">10</option>'
                                            + '</select>';
                                htmlData += '<hr>';
                                if (d.step > 0) {
                                    htmlData += 'S <input  id="step_'+d.id+  '" style="width:45px" type="text" placeholder="step" value="' + d.step + '">';
                                }else {
                                    htmlData += 'S <input  id="step_'+d.id+  '" style="width:45px" type="text" placeholder="step" >';
                                }
                                
                                <?php 
                                }
                                
                                if (strpos($levelsOfUser, '89') !== false || strpos($levelsOfUser, '30') !== false || strpos($levelsOfUser, '31') !== false) {
                                ?>
                                
                                
                                if (d.quantity  > 0 ) {
                                  htmlData += '<input id="quantity_'+d.id+  '" style="width:35px" type="text" placeholder="quantity" value="' +  d.quantity + '">';
                                } else {
                                  htmlData += '<input id="quantity_'+d.id+  '" style="width:35px" type="text" placeholder="quantity" >';
                                }
                                htmlData += '<hr>';
                                htmlData += '<button style="width:100px" id="clickbutton_' + d.id + '" onclick="onclickSaveButton('+d.id+')" >SAVE</button>';
                                
                                
                                <?php
                                 }
                                
                                ?>
                                
                                /// >>>>>>>>>>>>>>>>>>>>..
                                htmlData += "</td>";
                               
                             
                                htmlData += "<td class=\"hide-10\">" + subregionType[d.receiver_subregion] + " <?=(defined('STATE')) ? STATE : 'STATE';?> / <hr>" + streetType[d.receiver_street] + " " + d.receiver_address + "</td>";
                               
                                var mycDate = d.created_date.split("-");
                                var newcDate = mycDate[2] + "-" + monthNames[mycDate[1] - 1] + "-" + mycDate[0];
                                htmlData += "<td class=\"hide-5 hidden-print\" nowrap>" + newcDate + "<br/>" + firstToUpperCase(d.operator) + "</td>";
                                //#6
                                var sType = "";
                                if (d.order_source != "0") {
                                    sType = sourceType[d.order_source];
                                } else {
                                    sType = "";
                                }
                                htmlData += "<td class=\"hide-6 hidden-print\" style=\"min-width:140px;word-break: break-all;\">" + sType + "<hr/>" + d.order_source_optional + "</td>";

                                //#7
                                var pType = "";
                                if (d.payment_type != "0") {
                                    pType = payType[d.payment_type];
                                } else {
                                    pType = "";
                                }
                                <?php
                                if(empty(array_intersect(array(89), explode(",", $get_lvl[0])))){
                                ?>

                                htmlData += "<td class=\"hide-7 hidden-print\"><div class=\"prices_list\"><img src=\"<?=$rootF?>/template/icons/currency/" + d.currency + ".png\" width=\"20px\">" + number_format(d.price, '2', ',', '.') + costage + "<hr style=\"height:5px;\" title=\""+number_format(sum_overall.total, '0', ',', '.')+"/"+number_format(sum_overall.spend, '0', ',', '.')+"/"+number_format(sum_overall.percent, '0', ',', '.')+"\"/>" + pType + "<hr/>" + d.payment_optional + "</div></td>";
                                <?php
                                }
                                
                                
                                } else{
                                    
                                    
                                    
                                    
                                ?>
                                if (!timeType[d.delivery_time]) {
                                    timeType[d.delivery_time] = "";
                                }

                                var driverN = (driver_name[d.deliverer]) ? driver_name[d.deliverer] : '';
                                var delvrr = (d.deliverer > 0) ? "<img width=\"25px\" style=\"position:absolute;left:0;top:0;z-index:1;\" src=\"<?=$rootF?>/template/icons/drivers/" + d.deliverer + ".png\" title=\"" + driverN + "\">" : '';
                                var carN = (driver_car[d.delivery_type]) ? " title=\"" + driver_car[d.delivery_type] + "\"" : '';
                                htmlData += "<td class=\"hide-2 hidden-print\" nowrap>" + newDate + "<br/>" + timeType[d.delivery_time] + "(" + d.delivery_time_manual + ")<div style=\"position:relative;\"><img src=\"<?=$rootF?>/template/icons/deliver/" + d.delivery_type + ".png\" " + carN + ">" + delvrr + "</div></td>";
                                htmlData += "<td class=\"hide-3 hidden-print\"><img src=\"<?=$rootF?>/template/icons/status/" + d.delivery_status + ".png\" title=\"" + statusTitle[d.delivery_status] + "\">" + is_defect + is_defect_description + is_important + is_important +costage +"</td>";
                                                               
                                                                  
                                htmlData += "<td>";
                                  
                                <?php
                                
                                $levelsOfUser =$userData[0]["user_level"];
                                
                                if (strpos($levelsOfUser, '89') !== false) {
                                
                                ?>  
                                  
                                  
                                var showSelect = "SELECT";  
                                if (d.deliverer > 0 ) {
                                  showSelect =  driverN;
                                } 
                                
                                htmlData += '<select style="width:100px" id="driver_'+d.id+ '" ><option value="' + d.deliverer + '">'+ showSelect +'</option> <?php echo $selectionOption ?></select><br>';
                               
                                htmlData += '<hr>';
                                htmlData += '<select id="stage_'+d.id+ '"  style="width:100px">';
                                
                                if (d.stage > 0) {
                                    htmlData += '<option value="'+ d.stage + '">' +  d.stage + '</option>'
                                } 
                             
                                
                                htmlData    += '<option value="0"></option>'
                                            + '<option value="1">1</option>'
                                            + '<option value="2">2</option>'
                                            + '<option value="3">3</option>'
                                            + '<option value="4">4</option>'
                                            + '<option value="5">5</option>'
                                            + '<option value="6">6</option>'
                                            + '<option value="7">7</option>'
                                            + '<option value="8">8</option>'
                                            + '<option value="9">9</option>'
                                            + '<option value="10">10</option>'
                                            + '</select>';
                                htmlData += '<hr>';
                                if (d.step > 0) {
                                    htmlData += 'S <input  id="step_'+d.id+  '" style="width:45px" type="number" placeholder="step" value="' + d.step + '">';
                                }else {
                                    htmlData += 'S <input  id="step_'+d.id+  '" style="width:45px" type="number" placeholder="step" >';
                                }
                                
                                <?php 
                                }
                                
                                if (strpos($levelsOfUser, '89') !== false || strpos($levelsOfUser, '30') !== false || strpos($levelsOfUser, '31') !== false) {
                                ?>
                                
                                
                                if (d.quantity  > 0 ) {
                                  htmlData += '<input id="quantity_'+d.id+  '" style="width:35px" type="number" placeholder="quantity" value="' +  d.quantity + '">';
                                } else {
                                  htmlData += '<input id="quantity_'+d.id+  '" style="width:35px" type="number" placeholder="quantity" >';
                                }
                                htmlData += '<hr>';
                                htmlData += '<button style="width:100px" id="clickbutton_' + d.id + '" onclick="onclickSaveButton('+d.id+')" >SAVE</button>';
                                
                                
                                <?php
                                }
                                
                                ?>
                                
                                /// >>>>>>>>>>>>>>>>>>>>..
                                htmlData += "</td>";                               
                                                                
                                                                
                                <?php
                                }
                                ?>
                                actiondata = "";
                                <?php
                                if(max(page::filterLevel(3, $levelArray)) <= 33)
                                {
                                ?>
                                if (d.delivery_status != "3" && d.delivery_status != "6") {
                                    //console.log(d.delivery_status);
                                    actiondata += "<p style=\"margin-top:5px;\"><span style=\"display: inline-block;vertical-align: text-bottom;\"><button onclick=\"onroad(" + d.id + ")\"><img width=\"75px\" src=\"ico/onroad.png\"/></button></span>";
                                }
                                if (d.delivery_status != "12" && d.delivery_status != "3" && d.delivery_status != "6") {
                                    actiondata += "<span style=\"display: inline-block;vertical-align: text-bottom;\"><button onclick=\"product_ready(" + d.id + ")\"><img width=\"32px\" src=\"<?=$rootF?>/template/icons/status/12.png\"/></button></span></p>";
                                }
                                <?php
                                }
                                ?>

                                var zoomimage = (d.image_exist > 0) ? "<br><button style=\"background: none;border: 0;\" onclick=\"zoom_img(" + d.id + ")\" class=\"hidden-print\"><img src=\"<?=$rootF?>/template/icons/zoom.png\"/></button>" : '';

                                htmlData += "<td class=\"hide-8\" style=\"min-width:200px;\">" + d.product + actiondata + zoomimage + partner_icon+"</td>";
                                htmlData += "<td class=\"hide-9\">" + d.receiver_name + "</td>";
                                <?php
                                if(max(page::filterLevel(3, $levelArray)) < 33)
                                {
                                ?>

                                co = d.notes_for_florist;
                                htmlData += "<td class=\"hide-22 hidden-print\"><div style=\"max-width:135px;word-wrap: break-word;\"><strong style=\"color:#ff0000;\">" + d.notes_for_florist + "</strong></div></td>";

                                <?php
                                }
                                ?>

                                if (!subregionType[d.receiver_subregion]) {
                                    subregionType[d.receiver_subregion] = d.receiver_subregion;
                                }
                                if (!streetType[d.receiver_street] && streetType[d.receiver_street] != "") {
                                    streetType[d.receiver_street] = d.receiver_street;
                                }
                                htmlData += "<td class=\"hide-11 hidden-print\">" + d.receiver_phone + "</td>";
                                <?php
                                if(max(page::filterLevel(3, $levelArray)) < 33)
                                {
                                ?>
                                co = d.greetings_card;
                                htmlData += "<td class=\"hide-12 hidden-print\"><div class=\"article\" ><button class=\"read-more\">VIEW " + co.length + "</button><div class=\"text short\">" + d.greetings_card + "</div></div></td>";
                                <?php
                                }else{
                                ?>
                                co = d.notes;
                                var whoreceived_show = (whoreceived[d.who_received]) ? "<br>#" + whoreceived[d.who_received] + "#" : '';

                                htmlData += "<td class=\"hide-12 hidden-print\" width=\"110\"><div class=\"article\"><button class=\"read-more\">VIEW " + co.length + "</button><div class=\"text short\">" + d.notes + whoreceived_show + "</div></div></td>";
                                <?php
                                }
                                ?>
                                <?php if(max(page::filterLevel(3, $levelArray)) >= 33){
                                }else{?>htmlData += "<td class=\"hide-13 hidden-print\"><img src=\"<?=$rootF?>/template/icons/ontime/" + d.ontime + ".png\"></td>";<?php } ?>

                                //#12
                                //co = d.sender_name+d.sender_region+d.sender_address+d.sender_phone+d.sender_email;
                                var pr_lng = (recLang[d.delivery_language_primary]) ? recLang[d.delivery_language_primary] : 'N/A';
                                var sc_lng = (recLang[d.delivery_language_secondary]) ? recLang[d.delivery_language_secondary] : 'N/A';
                                var drsn = (order_reason[d.delivery_reason]) ? '<br/>(' + order_reason[d.delivery_reason] + ')' : '';
                                htmlData += "<td class=\"hide-14 hidden-print\">" + d.sender_name + "<br>(" + pr_lng + "," + sc_lng + ")" + drsn + "</td>";
                                htmlData += "<td class=\"hide-15 hidden-print\">" + d.sender_phone + "<?php if(max(page::filterLevel(3, $levelArray)) >= 33){?><br/>" + d.sender_email + "<?php }?></td>";
                                //htmlData += "<td></td>";
                                <?php
                                if(max(page::filterLevel(3, $levelArray)) >= 33)
                                {
                                ?>
                                htmlData += "<td class=\"hide-16\">" + d.sender_address + "<br/>" + d.sender_region + "</td>";
                                //htmlData += "<td></td>";
                                <?php
                                }else{
                                ?>
                                htmlData += "<td class=\"hide-16\"> " + d.sender_region + "</td>";
                                <?php
                                }
                                ?>
                                <?php
                                if(max(page::filterLevel(3, $levelArray)) >= 33)
                                {
                                ?>
                                //#13

                                co = d.greetings_card;
                                htmlData += "<td class=\"hide-17 hidden-print\"><div class=\"article\" ><button class=\"read-more\">VIEW " + co.length + "</button><div class=\"text short\">" + d.greetings_card + "</div></div></td>";

                                //#14
                                co = d.notes_for_florist;
                                htmlData += "<td class=\"hide-18\"><div class=\"article\"><button class=\"read-more\">VIEW " + co.length + "</button><div class=\"text short\">" + d.notes_for_florist + "</div></div></td>";

                                if (!sellPoint[d.sell_point]) {
                                    sellPoint[d.sell_point] = "";
                                }
                                //#15
                                htmlData += "<td class=\"hide-19 hidden-print\">" + sellPoint[d.sell_point] + "<br/>" + d.keyword + "</td>";
                                //#16
                                //htmlData += "<td></td>";
                                //#17
                                co = d.log;
                                htmlData += "<td class=\"hide-20 hidden-print\" nowrap><div class=\"article\"><button class=\"read-more\">VIEW " + co.length + "</button><div class=\"text short\">" + d.log + "</div></div></td>";

                                <?php
                                }else{
                                ?>
                                htmlData += "<td class=\"hide-17 hidden-print\" > By " + firstToUpperCase(d.operator) + "</td>";
                                <?php
                                }
                                ?>
                                htmlData += "</tr>";
                                <?php

                                if(!empty(array_intersect(array(89), explode(",", $get_lvl[0])))){
                                ?>
                                CCo++;
                                countP = CCo;
                                $("#shopCT").html(countP);
                            } else {
                                $("#shopCT").html(countP);
                            }
                            <?php
                            }
                            ?>
                            <?php

                            if(max(page::filterLevel(3, $levelArray)) < 33)
                            {
                            ?>
                            CCo++;
                            countP = CCo;
                            $("#shopCT").html(countP);
                        } else {
                            $("#shopCT").html(countP);
                        }
                        <?php
                        }
                        ?>

                    }
                    $("#onC").html("(" + countP + ")");
                }

                //htmlData = htmlData.replace(new RegExp(global_filter_text, 'g'),'<span style="background-color: yellow;color:black;">'+global_filter_text+'</span>');
                $('#dataTable').html(htmlData);
				
                if (global_filter_text != false) {
					highlight(global_filter_text, $('#dataTable').html());
                }
                $("#loading").css("display", "none");
            });
            //end
        }, 1000);
        return false;
    }
    filter(null);
    $('#menuDrop .dropdown-menu').on({
        "click": function (e) {
            e.stopPropagation();
        }
    });
    function showCount(el) {
        toP = el.value;
        filter(null, true);
    }
    $(document).on('click', "button.read-more", function () {

        var elem = $(this).parent().find(".text");
        if (elem.hasClass("short")) {
            elem.removeClass("short").addClass("full");

        }
        else {
            elem.removeClass("full").addClass("short");

        }
    });
    $(document).on('click', "button.show-ALL", function () {

        var elem = $("div").find(".text");
        if (elem.hasClass("short")) {
            elem.removeClass("short").addClass("full");

        }
        else {
            elem.removeClass("full").addClass("short");

        }
    });
    function zoom_img(id) {
        $.get("<?=$rootF?>/data.php?cmd=order_images&itemId=" + id, function (get_data) {
            if (get_data.data.images) {
                if (!$('a[data-imagelightbox="' + id + '"]').length) {
                    var imd = get_data.data.images;
                    for (var u = 0; u < imd.length; u++) {
                        $('body').append('<a href="product_images/' + imd[u].image_source + '" data-imagelightbox="' + id + '" style="display:none"><img src="product_images/' + imd[u].image_source + '" alt="' + imd[u].image_note + '"></a>');

                    }
                }

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
            } else {
                alert('<?=(defined('NKAR_CHKA')) ? NKAR_CHKA : 'NKAR_CHKA';?>');
            }
        });
    }
    var
        // ACTIVITY INDICATOR
        activityIndicatorOn = function () {
            $('<div id="imagelightbox-loading"><div></div></div>').appendTo('body');
        },
        activityIndicatorOff = function () {
            $('#imagelightbox-loading').remove();
        },

        // OVERLAY
        overlayOn = function () {
            $('<div id="imagelightbox-overlay"></div>').appendTo('body');
        },
        overlayOff = function () {
            $('#imagelightbox-overlay').remove();
        },

        // CLOSE BUTTON
        closeButtonOn = function (instance) {
            $('<button type="button" id="imagelightbox-close" title="Close"></button>').appendTo('body').on('click touchend', function () {
                $(this).remove();
                instance.quitImageLightbox();
                return false;
            });
        },
        closeButtonOff = function () {
            $('#imagelightbox-close').remove();
        },

        // CAPTION
        captionOn = function () {
            var description = $('a[href="' + $('#imagelightbox').attr('src') + '"] img').attr('alt');
            if (description.length > 0)
                $('<div id="imagelightbox-caption">' + description + '</div>').appendTo('body');
        },
        captionOff = function () {
            $('#imagelightbox-caption').remove();
        },

        // NAVIGATION
        navigationOn = function (instance, selector) {
            var images = $(selector);
            if (images.length) {
                var nav = $('<div id="imagelightbox-nav"></div>');
                for (var i = 0; i < images.length; i++)
                    nav.append('<button type="button"></button>');

                nav.appendTo('body');
                nav.on('click touchend', function () {
                    return false;
                });

                var navItems = nav.find('button');
                navItems.on('click touchend', function () {
                    var $this = $(this);
                    if (images.eq($this.index()).attr('href') != $('#imagelightbox').attr('src'))
                        instance.switchImageLightbox($this.index());

                    navItems.removeClass('active');
                    navItems.eq($this.index()).addClass('active');

                    return false;
                })
                    .on('touchend', function () {
                        return false;
                    });
            }
        },
        navigationUpdate = function (selector) {
            var items = $('#imagelightbox-nav button');
            items.removeClass('active');
            items.eq($(selector).filter('[href="' + $('#imagelightbox').attr('src') + '"]').index(selector)).addClass('active');
        },
        navigationOff = function () {
            $('#imagelightbox-nav').remove();
        },

        // ARROWS
        arrowsOn = function (instance, selector) {
            var $arrows = $('<button type="button" class="imagelightbox-arrow imagelightbox-arrow-left"></button><button type="button" class="imagelightbox-arrow imagelightbox-arrow-right"></button>');

            $arrows.appendTo('body');

            $arrows.on('click touchend', function (e) {
                e.preventDefault();

                var $this = $(this),
                    $target = $(selector + '[href="' + $('#imagelightbox').attr('src') + '"]'),
                    index = $target.index(selector);

                if ($this.hasClass('imagelightbox-arrow-left')) {
                    index = index - 1;
                    if (!$(selector).eq(index).length)
                        index = $(selector).length;
                }
                else {
                    index = index + 1;
                    if (!$(selector).eq(index).length)
                        index = 0;
                }

                instance.switchImageLightbox(index);
                return false;
            });
        },
        arrowsOff = function () {
            $('.imagelightbox-arrow').remove();
        };
    function buildPaginator(tCount, pfrom, pto,pnum) {
        if(!pnum){
            pnum = 0;
        }
        pnum = parseInt(pnum);
        tCount= parseInt(tCount);
        pfrom = parseInt(pfrom);
        var htmlP = "";
        var pagesC = Math.ceil(tCount / pto);
        var max_page_value = (pagesC-1)*pto;
        var max_view = 15;
        var view_count = 0;
        if(pnum > 0){
            pnum--;
        }
        if((pagesC-pNum) < max_view && pNum < pagesC){
            var delta_value = pNum-(max_view - (pagesC-pNum));
            var delta_count = max_view;
            if((delta_value+1) != pNum){
                fromPageCount = (delta_value*pto);
                pnum = delta_value;
            }
        }
        var vNum = parseInt(fromPageCount);

        if (pagesC > 1) {
            if(pNum >= pagesC){
                htmlP = "<li class=\"active\"><a href=\"#\" onclick=\"return false;\">" + (pnum+1) + "</a></li>"+htmlP;
                for (var i = pnum; i < pagesC; i--) {
                    var cpNum = i;

                    vNum -= parseInt(pto);
                    view_count++;
                    if (cpNum == pNum) {

                    } else {
                        //cpNum = i-1;
                        htmlP = "<li ><a href=\"#\" onclick=\"loadData(" + vNum + "," + pto + ","+cpNum+");return false;\">" + cpNum + "</a></li>"+htmlP;
                    }
                    if(view_count >= max_view || view_count == pagesC){
                        break;
                    }
                }
                if(pNum > 1){
                    htmlP = "<li ><a href=\"#\" onclick=\"loadData(0,"+pto+",1);return false;\"><<<</a></li>"+htmlP;
                }
            }else{

                if(pNum > 1){
                    htmlP += "<li ><a href=\"#\" onclick=\"loadData(0,"+pto+",1);return false;\"><<<</a></li>";
                    var previus_data = ((pNum-2) > 0) ? (pNum-2)*pto : 0;
                    htmlP += "<li ><a href=\"#\" onclick=\"loadData("+previus_data+","+pto+","+(pNum-1)+");return false;\"><<</a></li>";
                }
                var cpNum = 0;
                for (var i = pnum; i < pagesC; i++) {
                    cpNum = i + 1;

                    view_count++;
                    if ((cpNum == pNum) || (pNum == 0 && cpNum == 1)) {
                        htmlP += "<li class=\"active\"><a href=\"#\" onclick=\"return false;\">" + cpNum + "</a></li>";
                    } else {
                        htmlP += "<li ><a href=\"#\" onclick=\"loadData(" + vNum + "," + parseInt(pto) + ","+cpNum+");return false;\">" + cpNum + "</a></li>";
                    }
                    vNum += parseInt(pto);
                    if(view_count >= max_view){
                        break;
                    }
                }
                if(pNum < pagesC){
                    var nex_data = parseInt(vNum+((cpNum+1)*parseInt(pto)));
                    htmlP += "<li ><a href=\"#\" onclick=\"loadData(" + nex_data + ","+parseInt(pto)+","+(cpNum+1)+");return false;\">>></a></li>";
                    htmlP += "<li ><a href=\"#\" onclick=\"loadData(" + max_page_value + ","+pto+","+pagesC+");return false;\">>>></a></li>";
                }
            }
        }
        $("#buildPages").html(htmlP);
        return vNum;
    }
    function loadData(v1, v2,pn) {
        fromP = v1;
        pNum = pn;
        fromPageCount = v1;
        filter(null);
    }
    if ($('[addon="rangedate"]')) {
        $('[addon="rangedate"]').each(function () {
            $(this).dateRangePicker({
                shortcuts: {
                    'prev-days': [3, 5, 7],
                    'prev': ['week', 'month', 'year'],
                    'next-days': null,
                    'next': null
                }
            }).bind('datepicker-apply', function () {
                filter(this, true);
            });
        });
    }
    if ($('[addon="date"]')) {
        $('[addon="date"]').datepicker({format: 'yyyy-mm-dd'}).on('changeDate', function () {
            filter(this, true);
        });
    }
    function totalResset() {
        $("input[type=text]").each(function () {
            $(this).val('');
        });
        $("select").each(function () {
            $(this).val('');
        });
		
        $("#showCount").val("150");
        data = {};

        toP = 150;//listi erkarutyun #2
		
        filter(null, this);
    }
    function sendMail() {
        var getMails = "";
        $("input:checkbox[id^='mailToSend']").each(function () {

            if ($(this).is(":checked")) {
                getMails += $(this).val() + ",";
            }
            if (!getMails) {
                $(this).prop("disabled", false);
            }
        });
        if (getMails) {
            window.open("mail/?mails=" + getMails, "", "toolbar=yes, scrollbars=yes, resizable=yes,width=800, height=400");
        }
    }
    function CheckAccounting(orderId) {
        window.open("products/?cmd=check&orderId=" + orderId, "", "toolbar=yes, scrollbars=yes, resizable=yes,width=970, height=600");
    }
    function onroad(id) {
        request_call('&id=' + id + '&delivery_status=6');
    }
    function product_ready(id) {
        request_call('&id=' + id + '&delivery_status=12');
    }
    function request_call(call_data) {
        $.get("ajax.php?update_order=true" + call_data, function (get_data) {
            if (get_data.status && get_data.status == "ok") {
                alert('ok');
                filter(null);
            }
        });
    }
    function selectAll(type) {
        $("input:checkbox[id^='mailToSend']").each(function () {

            if (type) {
                $(this).prop('checked', true);
            } else {
                $(this).prop('checked', false);
            }
        });
    }
    function checkAll(data) {
        if (data.checked) {
            selectAll(true);
        } else {
            selectAll();
        }
    }
    jQuery("[name=allfpf]").attr("disabled", "disabled");
    jQuery("[name=allfpf]").html("<option>---</option>");
    function hfilter(type) {
        var allFlPartners = <?=json_encode(getwayConnect::getwayData("SELECT `data_partners`.`sell_point_id` AS `value`,`delivery_sellpoint`.`name` FROM `data_partners` RIGHT JOIN `delivery_sellpoint` ON  `data_partners`.`sell_point_id` = `delivery_sellpoint`.`id` WHERE `data_partners`.`active` = 1 AND `data_partners`.`depend_on` = 'flower' ORDER BY `data_partners`.`ordering`", PDO::FETCH_ASSOC))?>;
        var allRTPartners = <?=json_encode(getwayConnect::getwayData("SELECT `data_partners`.`sell_point_id` AS `value`,`delivery_sellpoint`.`name` FROM `data_partners` RIGHT JOIN `delivery_sellpoint` ON  `data_partners`.`sell_point_id` = `delivery_sellpoint`.`id` WHERE `data_partners`.`active` = 1 AND `data_partners`.`depend_on` = 'travel' ORDER BY `data_partners`.`ordering`", PDO::FETCH_ASSOC))?>;
        var allOws = <?=json_encode(getwayConnect::getwayData("SELECT `data_partners`.`sell_point_id` AS `value`,`delivery_sellpoint`.`name` FROM `data_partners` RIGHT JOIN `delivery_sellpoint` ON  `data_partners`.`sell_point_id` = `delivery_sellpoint`.`id` WHERE `data_partners`.`active` = 1 AND `data_partners`.`depend_on` = 'ows' ORDER BY `data_partners`.`ordering`", PDO::FETCH_ASSOC))?>;
        var phtml = '<option value="">SELECT ONE</option>';
        jQuery("[name=allfpf]").removeAttr("disabled");
        if (type == "FLOWERS_PARTNERS") {
            for (var i = 0; i < allFlPartners.length; i++) {
                phtml += "<option value=\"" + allFlPartners[i].value + "\" >" + allFlPartners[i].name + "</option>";
            }
        } else if (type == "TRAVEL_PARTNERS") {
            for (var i = 0; i < allRTPartners.length; i++) {
                phtml += "<option value=\"" + allRTPartners[i].value + "\" >" + allRTPartners[i].name + "</option>";
            }
        } else if (type == "OTHER_WEBSITES") {
            for (var i = 0; i < allOws.length; i++) {
                phtml += "<option value=\"" + allOws[i].value + "\" >" + allOws[i].name + "</option>";
            }
        } else {
            jQuery("[name=allfpf]").attr("disabled", "disabled");
        }
        jQuery("[name=allfpf]").html(phtml);
        phtml = "";
    }
    function viewHidePrice() {
        $(".prices_list").toggle();
    }
    
    
    function highlight(text, object) {
        /*var term = text;
        term = term.replace(/(\s+)/, "(<[^>]+>)*$1(<[^>]+>)*");
        var pattern = new RegExp("(" + term + ")", "gi");

        object = object.replace(pattern, "<span style=\"background-color: yellow;color:black;font-size: 12px;\">$1</span>");
        object = object.replace(/(<span>[^<>]*)((<[^>]+>)+)([^<>]*<\/span>)/, "$1</span>$2<span style=\"background-color: yellow;color:black;font-size: 12px;\">$4");
        return object;*/

        // remove any old highlighted terms
        $('body').removeHighlight();

        // disable highlighting if empty
        if ( text ) {
            // highlight the new term
            $('body').highlight( text );
        }
    }
    jQuery.fn.highlight = function(pat) {
        function innerHighlight(node, pat) {
            var skip = 0;
            if (node.nodeType == 3) {
                var pos = node.data.toUpperCase().indexOf(pat);
                if (pos >= 0) {
                    var spannode = document.createElement('span');
                    spannode.className = 'highlight';
                    var middlebit = node.splitText(pos);
                    var endbit = middlebit.splitText(pat.length);
                    var middleclone = middlebit.cloneNode(true);
                    spannode.appendChild(middleclone);
                    middlebit.parentNode.replaceChild(spannode, middlebit);
                    skip = 1;
                }
            }
            else if (node.nodeType == 1 && node.childNodes && !/(script|style)/i.test(node.tagName)) {
                for (var i = 0; i < node.childNodes.length; ++i) {
                    i += innerHighlight(node.childNodes[i], pat);
                }
            }
            return skip;
        }
        return this.each(function() {
            innerHighlight(this, pat.toUpperCase());
        });
    };

    jQuery.fn.removeHighlight = function() {
        function newNormalize(node) {
            for (var i = 0, children = node.childNodes, nodeCount = children.length; i < nodeCount; i++) {
                var child = children[i];
                if (child.nodeType == 1) {
                    newNormalize(child);
                    continue;
                }
                if (child.nodeType != 3) { continue; }
                var next = child.nextSibling;
                if (next == null || next.nodeType != 3) { continue; }
                var combined_text = child.nodeValue + next.nodeValue;
                new_node = node.ownerDocument.createTextNode(combined_text);
                node.insertBefore(new_node, child);
                node.removeChild(child);
                node.removeChild(next);
                i--;
                nodeCount--;
            }
        }

        return this.find("span.highlight").each(function() {
            var thisParent = this.parentNode;
            thisParent.replaceChild(this.firstChild, this);
            newNormalize(thisParent);
        }).end();
    };
	$(document).on('click', '#printyfy .dropdown-menu', function (e) {
	  e.stopPropagation();
	});
	function hide_on_print(obj,object_class){
		if(obj.is(':checked')){
			$("."+object_class).addClass('hidden-print');
		}else{
			$("."+object_class).removeClass('hidden-print');
		}
	}
        
        //// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
        function onclickSaveButton(idToSave){
		 
                var driverId = $( "#driver_" + idToSave ).val();
                
                
                
                
                var step = 0;
                if ($( "#step_" + idToSave ).val() > 0) {
                   step  = $( "#step_" + idToSave ).val();
                }
              
                var stage = 0;
                if ( $( "#stage_" + idToSave ).val() > 0) {
                    stage = $( "#stage_" + idToSave ).val();
                }
                
                var quantity = 0;
                if ( $( "#quantity_" + idToSave).val() > 0) {
                    quantity =  $( "#quantity_" + idToSave).val();
                }
                
                var userid =<?=$userData[0]["id"]?>;
    
                var url = "<?=$rootF?>/data.php?cmd=updatedrive&id=" + idToSave 
                           + "&driverId=" + driverId 
                           + "&step="     + step 
                           + "&stage="    + stage 
                           + "&quantity=" + quantity 
                           + "&userid="   + userid;
    
               
                  $("#clickbutton_" + idToSave ).load( url, function(data) {
                      
                      
                      if (data == "ok") {
                          $("#drvnameimg_" + idToSave).attr("src", " ../../template/icons/drivers/" + driverId + ".png")
                          $("#carImage_" + idToSave).attr("src", "../../template/icons/deliver/" + driverId + ".png")
                          $("#clickbutton_" + idToSave ).text(data);
                      }
                    
                    
                  });
                 
                          
	}  
        
        
        
        
        
</script>

</body>
</html>