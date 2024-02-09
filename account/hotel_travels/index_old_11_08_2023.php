<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
$pageName = "travel";
$rootF = "../..";
include($rootF . "/apay/pay.api.php");
include($rootF . "/configuration.php");
include_once $_SERVER['DOCUMENT_ROOT'] . '/controls/GetDatabaseContent.php';

date_default_timezone_set("Asia/Yerevan");
$access = auth::checkUserAccess($secureKey);
$allData = array();
$buildClient = "";
$uid = "";
$level = "";
$userData = "";
$cc = "am";
if (!$access) {
    header("location:../../login");
} else {
    $uid = $_COOKIE["suid"];
    $level = auth::getUserLevel($uid);
    page::accessByLevel($level[0]["user_level"], $pageName);
    $levelArray = explode(",", $level[0]["user_level"]);
    $userData = auth::checkUserExistById($uid);
    $cc = $userData[0]["lang"];
}
page::cmd();
$exchangeratescba = GataDatabaseContent::getDefaultCurrencyValues();


$sql = "SELECT * FROM `data_travel` AS DT  RIGHT JOIN `travel_hotel_relation` AS THR ON THR.`travel_id` = DT.`id` RIGHT JOIN `data_hotel_booking` AS DHB ON THR.`hotel_booking_id` = DHB.`id` WHERE";
if(!isset($_GET['end_date']) && !isset($_GET['start_date'])){
    // by default today
    $sql.=" DHB.check_in >= '" . date("Y-m-d") . "'";
    $sql.=" and DHB.check_out <= '" . date("Y-m-d") . " 23:59:00'";
}
if(isset($_GET['start_date'])){
    $sql.=" DHB.check_in >= '" . $_GET['start_date'] . "'";
}
if(isset($_GET['end_date'])){
    $sql.=" and DHB.check_in <= '" . $_GET['end_date'] . " 23:59:00'";
}
$sql = str_replace('WHERE and','WHERE',$sql) . " group by DT.id";
$n_travel_data = getwayConnect::getwayData($sql);
$all_hotels = getwayConnect::getwayData("SELECT * FROM data_hotels");
$all_hotel_array = Array();
foreach($all_hotels as $hotel){
    $all_hotel_array[$hotel['id']] = $hotel;
}
$all_partners = getwayConnect::getwayData("SELECT * FROM travel_partner");
$all_partner_array = Array();
foreach($all_partners as $partner){
    $all_partner_array[$partner['id']] = $partner;
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
    <meta name="author" content="Davit Gabrielyan, Ruben Mnatsakanyan">
    <link rel="stylesheet" href="<?= $rootF ?>/template/account/sidebar.css">
    <link rel="stylesheet" href="<?= $rootF ?>/template/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= $rootF ?>/template/bootstrap/css/bootstrap-theme.min.css">
    <link rel="stylesheet" href="<?= $rootF ?>/template/datepicker/css/datepicker.css">
    <link rel="stylesheet" href="<?= $rootF ?>/template/rangedate/daterangepicker.css"/>
    <link rel="stylesheet" href="<?= $rootF ?>/template/datetimepicker/css/bootstrap-datetimepicker.min.css"/>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.css" />
    <style>
        @media print {
            body {
                -webkit-print-color-adjust: exact;
            }
        }

        .article {
            word-wrap: break-word;
            max-width: 125px;
        }

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

        .date-picker-wrapper {
            z-index: 99999999;
        }

        .datepicker {
            z-index: 99999999;
        }

        hr {
            line-height: 0;
            margin-top: 2px;
            margin-bottom: 2px;
            border-color: #666;
        }

        #loading {
            position: fixed;
            z-index: 9999999999;
            top: -6px;
            left: -8px;
            display: block;
        }

        .remove_button {
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

        .add_button {
            display: inline-block;
            border: 1px solid #ababab;
            padding: 3px;
            background: #f7f7f7;
        }

        .remove_button:hover, .add_button:hover {
            cursor: pointer;
            border-color: #777;
        }

        .item_list ol {
            padding-left: 20px;
        }

        .item_list li {
            margin-right: 5px;
        }

        .item_list li:hover {
            border-bottom: #666 1px solid;
        }
        .removeArrowButton::-webkit-outer-spin-button,
		.removeArrowButton::-webkit-inner-spin-button {
		  -webkit-appearance: none;
		  margin: 0;
		}

		/* Firefox */
		.removeArrowButton[type=number] {
		  -moz-appearance: textfield;
		}
        .displayActionsPart td{
            padding:5px;
        }
    </style>

    <script>

        (function () {
            var beforePrint = function () {
                if ($('#cleanprint').is(':checked')) {
                    $(".hiddenforprint").hide();
                }
            };

            var afterPrint = function () {
                $(".hiddenforprint").show();
            };

            if (window.matchMedia) {
                var mediaQueryList = window.matchMedia('print');
                mediaQueryList.addListener(function (mql) {
                    if (mql.matches) {
                        beforePrint();
                    } else {
                        afterPrint();
                    }
                });
            }

            window.onbeforeprint = beforePrint;
            window.onafterprint = afterPrint;
        }());

        var first_click = true;

        function openPrint() {
            if (first_click) {
                first_click = false;
                setTimeout(function () {
                    window.print();
                    first_click = true;
                }, 500);

            }

        }


    </script>
    <title>Travel</title>
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
            <a class="navbar-brand" href="#">RG-SYSTEM</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse" aria-expanded="false">
            <ul class="nav navbar-nav">
                <?= page::buildMenu($level[0]["user_level"]) ?>
                <li class="dropdown" id="menuDrop">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Filters
                        <span class="caret"></span></a>
                    <ul class="dropdown-menu" role="menu" style="text-align:center;">

                        <?php

                        $fData = page::buildFilter($level[0]["user_level"], "travel");
                        for ($fi = 0; $fi < count($fData); $fi++) {
                            echo "<li>{$fData[$fi][1]}</li>";
                        }
                        ?>

                    </ul>
                </li>
                <li>
                    <a href="#" onclick="addEditData(4);return false;">ADD</a>
                </li>
                <li>
                    <a href="../travel_orders_report/">Report</a>
                </li>
            </ul>
        </div>
    </div>
</nav>


<div class="container" style="margin-top:81px;width: 100%">
    <div style='float:left' class="hiddenforprint">
        <?php
            $types = ['jpeg', 'png', 'JPEG', 'jpg'];
            foreach($types as $type){
                if(file_exists('../user_images/' . $userData[0]['uid']. ".". $type)){
                ?>
                    <div class="btn-group" role="group" aria-label="...">
                        <img src="<?= '../user_images/' . $userData[0]['uid']. '.'. $type ?>" alt="" width="50" height="50" class="pull-right">
                    </div>
                <?php
                }
            }
        ?>
    </div>
        <div class='row'>
            <label for='check_in_radio' style='margin-left:10px'>Check In</label>
            <input type='radio' name='type_of_search' id="check_in_radio">
            <label for='check_out_radio'>Check Out</label>
            <input type='radio' name='type_of_search' id="check_out_radio">
            <label for='reserved_radio'>Reserved</label>
            <input type='radio' name='type_of_search' id="reserved_radio">

            <label for='from_date' style='margin-left:10px'>From</label>
            <input type="date" class='btn btn-default' name="from_date" id="from_date"/>
            <label for='to_date' style="margin-left: 10px">To</label>
            <input type="date" class='btn btn-default' name="to_date" style="margin-right: 20px;" id="to_date"/>

            <a href="?start_date=<?= date("Y-m-d")?>&end_date=<?=date("Y-m-d")?>" class='btn btn-default'>Այսօր</a>
            <a href="?start_date=<?= date("Y-m-d", strtotime("+1 day"))?>&end_date=<?=date("Y-m-d", strtotime("+1 day"))?>" class='btn btn-default'>Վաղը</a>

            <a href="#" class='btn btn-default'>Not Payed</a>
            <a href="#" class='btn btn-default'>Not Canceled</a>

            <select class='btn btn-default'>
                <option>Sales Point</option>
            </select>

            <input type='text' class='btn btn-default' style='margin-left:10px' placeholder="Search">
            <a href="#" class='btn btn-default'>Search</a>
        </div>
    <div class="table" style='margin-top:10px'>
        <table id="mainTable" style="width:100%">
            <thead>
                <tr>
                    <th>#</th>
                    <th style='width:20%'>Hotels</th>
                    <th>Status</th>
                    <th>Check In</th>
                    <th>Check Out</th>
                    <th>Price</th>
                    <th>Contacts</th>
                    <th>Last Updated Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    if($n_travel_data){
                        foreach($n_travel_data as $data){
                            ?>
                                <tr>
                                    <td><a href='#' onclick="addEditData(2,<?=$data['travel_id']?>);return false;" > NUM- <?= $data['travel_id']?></a> - <?= $data['travel_partneID']?><br><?= $data['travel_customerName']?></td>
                                    <td style='width:20%'><?= $all_hotel_array[$data['hotel_id']]['name']?><br><?= $all_partner_array[$data['travel_partneID']]['name']?></td>
                                    <td><img src="../../template/images/status/<?=$data['travel_status']?>.png"></td>
                                    <td><?= "<span style='display:none'>".$data['check_in'] . "</span> " . date("d-M-Y H:i:s",strtotime($data['check_in']))?></td>
                                    <td><?= "<span style='display:none'>".$data['check_out'] . "</span> " . date("d-M-Y H:i:s",strtotime($data['check_out']))?></td>
                                    <td><?= $data['travel_price']?>/<span style='color:red'><?= $data['travel_price'] - $data['travel_partial_pay'] ?></span> </td>
                                    <td><?= $data['travel_customerPhone']?><br><?= $data['travel_customerEmail']?></td>
                                    <td><span style='display:none'>$data['date_last_update']</span><button type="button" data-id="<?= $data['travel_uniq']?>" class="open_edited_popup"><?= date("d-M-Y H:i:s",strtotime($data['date_last_update']))?></button></td>
                                </tr>
                            <?php
                        }
                    }
                ?>
            </tbody>
        </table>
    </div>

    <div class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
         aria-hidden="true" id="tlAdd">
        <div class="modal-dialog modal-lg" style="margin-top:150px;">
            <div class="modal-content" style="padding:5px;" id="trLogContainer">

                <input type="hidden" value="<?= $_COOKIE["suid"]; ?>" id="vuid">
                <input type="hidden" value="0" id="travel_data_id">
                <form id="vform">
                    <h6 style=" text-align:  center" id="id_mod_order_id">ID : </h6>
                    <div class="container-fluid">
                        <div class="input-group" style="min-width:790px;">
					<span class="input-group-addon" style="text-align: left;">
                        <select name="vServices" id="vServices" style="width:155px;">
						     <?= page::buildOptions("data_service") ?>
						</select>
                        <br/>
                        <input type="text" name="vprt_ordreid" id="vprt_order_id" placeholder="Reservation Number"
                               style=" width: 155px">
					</span>
                            <span class="input-group-addon" style="text-align: left;">
                        Price : <input type="number" step="0.01" style="max-width:80px;"
                                       placeholder="Price" class='removeArrowButton' name="vprice" id="vprice"
                                       onChange="getTotalProfit()">
                                                <select name="vcurrency" id="vcurrency" onChange="getTotalProfit()">
						   <?= page::buildOptions("currency") ?>
						</select>
                                                <select name="vstatus" id="vstatus" style="max-width:100px;">
                                                    <option value=''>Reservation Status</option>
						     <?= page::buildOptions("data_status", false, false, false, true, 'ORDER BY `ordering` ') ?>
						</select> 
                        
                        <br>
                        <span style=" padding-right: 15px">Net:</span>
                        <input type="number" step="0.01" style="max-width:80px;"
                               placeholder="Net Price" class='removeArrowButton' name="vrevenue" id="vrevenue"
                               onChange="getTotalProfit()">
                        <select name="vCurrencyOfPrice" id="vCurrencyOfPrice"
                                style=" color: red" onChange="getTotalProfit()">
					             <?= page::buildOptions("currency") ?>
						</select>

                        <input type="text" name="vtotal_income" id="vtotal_income"
                               style="max-width:140px; color: red" placeholder="Total Benefit"
                               readonly>
                        <input type="checkbox" name="vWePaid" id="vWePaid" style="color: red;display:none">
					</span>
                            <span class="input-group-addon" style="text-align: left;">
                    
						<select name="vpayment" id="vpayment" style="max-width:166px;">
                            <option value=''>Payment Status</option>
						<?= page::buildOptions("data_payment_status",false, false, false, true, 'ORDER BY `ordering` ') ?>
						</select>
						<input type="number" step="0.0001" style="max-width:85px;padding:0" class='removeArrowButton' placeholder="Paid amount"
                                        name="vpartial_pay" id="vpartial_pay">
                        <select name="vCurrencyPartialPaied" id="vCurrencyPartialPaied">
                           <?= page::buildOptions("currency") ?>
                        </select>
                    <br/>
                        <select name="vpaymenttype" id="vpaymenttype" style="max-width:100px;">
                            <option value=''>Payment Type</option>
                        <?= page::buildOptions("data_payment",false, false, false, true, 'ORDER BY `ordering` ') ?>
                        </select>
                         <input type="text" style="width: 87px" placeholder="more details" name="vpaymentNote"
                                id="vpaymentNote"><br/>
                        		</span>
                        </div>
                        <p></p>
                        <div class="input-group" style="min-width:790px;">
					<span class="input-group-addon">
                                             
						<!-- C:<input name="vcustomerType" disabled id="vcustomerType" type="radio" value="0"
                                 onclick="function_control_partners(false)">
						P:<input name="vcustomerType" id="vcustomerType" type="radio" value="1"
                                 onclick="function_control_partners(true)"> -->
					 <span>Sales Point</span>
					<select name="vpartneID" id="vpartneID">
						<option value="0">Registered by</option>
                        <?= page::buildOptions("travel_partner", false, false, false, true, 'ORDER BY `ordering`') ?>
					</select>
					</span>
                            <input type="text" class="form-control" placeholder="Guest name" name="vcustomerName"
                                   id="vcustomerName" style="min-height:36px;">
                            <span class="input-group-addon">
						<input type="text" placeholder="Phone/Whatsapp/Viber" name="vcustomerPhone" id="vcustomerPhone"
                               style="max-width: 132px;">
					</span>
                            <span class="input-group-addon">
						<input type="text" placeholder="E-mail" name="vcustomerEmail" id="vcustomerEmail"
                               style="max-width: 132px;">
					</span>

                        </div>
                        <p data-rel-hotel="true" style="display:none">
                        <div class="input-group" style="min-width:790px;display:none" data-rel-hotel="true">
                            <span class="input-group-addon">
						 <table>
                         <tr>
                          <td>HOTEL:</td>
                          <td>
                          <select name="hotel_id" id="hotel_id" style="width: 118px;">
                          <option value="">HOTEL</option>
                              <?= page::buildOptions("data_hotels", false, false, false, true, 'ORDER BY `name`'); ?>
                         </select>

                         </td>
                         </tr>
                         <tr>
                         <td>C IN :</td>
                         <td><input type="text" placeholder="checkin" name="check_in"
                                    id="check_in" style="max-width: 118px;"/> </td>
                         </tr>
                         <tr>
                         <td>C OUT :</td>
                         <td><input type="text" placeholder="checkout" name="check_out"
                                    id="check_out" style="max-width: 118px;"/></td>
                         </tr>
                         </table>
                         </span>
                            <span class="input-group-addon">
						      <textarea name="vguests" placeholder="Important Notes" id="vguests" style="width:240px;margin: 0px;height: 64px;"></textarea>
                            </span>
                            <span class="input-group-addon" style='text-align: left;display:none'>
                                <span>
                                    <input type='checkbox' class='checkInPerson'> Check In person: <?php echo $userData[0]['full_name_am']?>
                                    <br>
                                    <input type='checkbox' class='checkInSmV' id='vroomkey' name='vroomkey' disabled="true">Room Key
                                    <br>
                                    <input type='checkbox' class='checkInSmV' disabled="true" id='vwifiinfo' name='vwifiinfo'>WiFi info
                                    <br>
                                    <input type='checkbox' class='checkInSmV' disabled="true" id='vlocalareainfo' name='vlocalareainfo'>Local area info
                                    <br>
                                    <input type='checkbox' class='checkInSmV' disabled="true" id='vhotelpolicypapers' name='vhotelpolicypapers'>Hotel Policy papers
                                </span>
                            </span>
                            <span class="input-group-addon" style='text-align: left;display:none'>
                                <span>
                                    <input type='checkbox' class='checkOutPerson'>Out person: <?php echo $userData[0]['full_name_am']?>
                                    <br>
                                    <input type='checkbox' class='checkOutSmV' id='vcheckedpayment' name='vcheckedpayment' disabled="true">Checked Payment
                                    <br>
                                    <input type='checkbox' class='checkOutSmV' disabled="true" id='vroomkeytaken' name='vroomkeytaken'>Room Key Taken
                                    <br>
                                    <input type='checkbox' class='checkOutSmV' disabled="true" id='vinvoiceprovided' name='vinvoiceprovided'>Invoice Provided
                                </span>
                            </span>
					</span>

                           <!--  <span class="input-group-addon">
        						Adult:<select name="adult_count" id="adult_count">
        							<?php
                                    foreach (range(0, 56) as $number) {
                                        echo "<option value={$number}>{$number}</option>";
                                    }
                                    ?>
        							</select>
        						<input type="text" style="max-width:50px;" placeholder="Price" name="adult_price"
                                       id="adult_price">
                                                        <hr>
                                                        Child:<select name="child_count" id="child_count">
        							<?php
                                    foreach (range(0, 56) as $number) {
                                        echo "<option value={$number}>{$number}</option>";
                                    }
                                    ?>
        							</select>
        						<input type="text" style="max-width:50px;" placeholder="Price" name="child_price"
                                       id="child_price">
        					</span> -->
                            <span class="input-group-addon" style="text-align: left;">
                                                
                                             <!-- <table> 
                                                    <tr>
                                                        <td>Airline:</td>
                                                        <td>
                                                        <select name="vairline_id" id="vairline_id"
                                                                style=" color:  red ; width: 120px">
                                                             <option value="0">Select Airline</option>
                                                            <?= GataDatabaseContent::getAllAirlines() ?>
                                                         </select>
                                                        
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>Arrival :</td>
                                                        <td> <input id="varraival_date" name="varraival_date"
                                                                    type="text" placeholder="arrival"
                                                                    style=" width: 120px"> </td>
                                                    </tr>
                                                    <tr>
                                                        <td>Departure :</td>
                                                        <td><input id="vdeparture_date" name="vdeparture_date"
                                                                   type="text" placeholder="deoarture"
                                                                   style=" width: 120px"></td>
                                                    </tr>
                                                </table>  -->
                                                
					</span>
                        </div>
                        </p>
                        <p data-rel-hotel="true" style="display:none">

                        <div class="input-group" style="min-width:790px;display:none" data-rel-hotel="true">
					<span class="input-group-addon">
                    <select name="data_hotel_room_numbers" id="data_hotel_room_numbers" style="max-width:60px;">
                        <option value="">N:</option>
                        <?= page::buildOptions("data_hotel_room_numbers", false, false, false, true, 'ORDER BY `ordering`') ?>
                    </select>
					<select name="hotel_room_type" id="hotel_room_type" style='width:100px'>
							<option value="">Room Type</option>
                        <?= page::buildOptions("data_hotel_room_type", false, false, false, true, 'ORDER BY `ordering`') ?>
						</select>

						<!-- <select name="hotel_room_count" id="hotel_room_count">
							<?php
                            foreach (range(1, 4) as $number) {
                                echo "<option value=\"{$number}\">{$number}</option>";
                            }
                            ?>
						</select> -->
                        C IN: <input type="text" placeholder="checkin" name="check_in_sm"
                                    id="check_in_sm" style="max-width: 118px;"/> 
                                    C OUT: <input type="text" placeholder="checkout" name="check_out_sm"
                                    id="check_out_sm" style="max-width: 118px;"/>
                                            <input type="number" step="0.0001" placeholder="price"
                                                   name="hotel_room_price" class='removeArrowButton' id="hotel_room_price"
                                                   style="max-width:50px;"/>
						<!-- <select name="hotel_extra_bed_count" id="hotel_extra_bed_count">
							<?php
                            foreach (range(0, 2) as $number) {
                                if ($number == 0) {
                                    echo "<option value=\"0\">Extra Bed</option>";
                                } else {
                                    echo "<option value=\"{$number}\">{$number}</option>";
                                }
                            }
                            ?>
						</select> -->
                                                <!-- <input type="numbert" step="0.0001" placeholder="price"
                                                       name="hotel_extra_bed_price" id="hotel_extra_bed_price"
                                                       style="max-width:80px;"/> -->
						<div onclick="addRoom();return false" class="add_button">+</div>
						<style>
								.hotel_room_list_item {
                                    margin: 0;
                                    padding: 0;
                                    margin-bottom: 3px;
                                }
							</style>
							<div id="hotel_room_list" style="margin: 5px;border: 1px dashed;text-align:center;">
								<ol style="text-align: left;margin: 0;" class="item_list">
								</ol>
							</div>
					</span>
                            <span class="input-group-addon" style="padding: 0;">
					
						<select name="hotel_extra_type" id="hotel_extra_type" style="max-width:60px;">
							<option value="">Extra</option>
                            <?= page::buildOptions("hotel_order_extra", false, false, false, true, 'ORDER BY `ordering`') ?>
						</select>

						<select name="hotel_extra_count" id="hotel_extra_count">
							<?php
                            foreach (range(1, 20) as $number) {
                                echo "<option value=\"{$number}\">{$number}</option>";
                            }
                            ?>
						</select>
                                                <input type="number" step="0.0001" placeholder="price"
                                                       name="hotel_extra_price" class='removeArrowButton' id="hotel_extra_price"
                                                       style="max-width:50px;"/>
						<div onclick="addHotelExtraItem();return false" class="add_button">+</div>
						<style>
								.hotel_extra_list_item {
                                    margin: 0;
                                    padding: 0;
                                    margin-bottom: 3px;
                                }
							</style>
							<div id="hotel_extra_list" style="margin: 5px;border: 1px dashed;text-align:center;">
								<ol style="text-align: left;margin: 0;" class="item_list">

								</ol>
							</div>
					</span>
                            <span class="input-group-addon" style="padding: 0;">
                        <div class="btn btn-default " onclick="addToHotelList()">ADD</div>
                    </span>
                        </div>
                        </p>
                        <p data-rel-hotel="true" style="display:none">
                        <div class="input-group" style="min-width:790px;display:none" data-rel-hotel="true">
						<span class="input-group-addon">
							<style>
								.hotel_list_item {
                                    margin: 0;
                                    padding: 0;
                                    margin-bottom: 3px;
                                    border: #666 1px solid;
                                    position: relative;
                                    padding: 5px;
                                }

                                .hotel_list_item:hover {
                                    background: rgb(255, 255, 255);
                                }

                                .hotel_list_item > .btn {
                                    display: none;;
                                }

                                .hotel_list_item:hover > .btn {
                                    display: block;
                                }

                                .small_popdown {
                                    background: white;
                                    color: #3e3e3e;
                                    position: absolute;
                                    bottom: -32px;
                                    border: solid 1px #adadad;
                                    left: 0;
                                    padding: 5px;
                                }
							</style>
							<div id="hotel_list" style="margin: 5px;border: 1px dashed;text-align:center;">
								<ol style="text-align: left;margin: 0;padding-top:3px;padding-left:30px;"
                                    class="item_list">

								</ol>

							</div>
                            <div class='displayActionsPart'></div>
                            <div style='float: left;margin-left:10px;margin-top:10px'>
                                Action
                                <select id='status_action'>
                                    <option value="">Action Type</option>
                                    <option value="1">What Happened?</option>
                                    <option value="0">Required to do</option>
                                </select>
                            </div>
                            <div style='float: left;margin-top:10px'>
                                Add Action
                                 <select id="actionSel" style="max-width:200px;">
                                    <option value=''>Actions</option>
                                     <?= page::buildOptions("hotel_actions", false, false, false, true, 'ORDER BY `ordering` ') ?>
                                </select>
                            </div>
                            
                                <div style='float:left;margin-left:10px' class='actionMain'></div>
                            
						</span>
                        
                        </div>
                        </p>
                        <p data-rel-hotel="true" style="display:none">
                        <div class="input-group" style="min-width:790px;display:none" data-rel-hotel="true">
                            <div action="invoices/" method="GET" name="print" id="print">
						<span class="input-group-addon">
							<label for="printType1">INVOICE:
                                                            <input name="printType" id="printType1" type="radio"
                                                                   value="1" checked></label>
							<label for="printType2">VOUCHER:
                                                            <input name="printType" id="printType2" type="radio"
                                                                   value="2"></label>
						</span>
                                <span class="input-group-addon">
							<select name="country_code" id="country_code">
								<option value="en" selected>EN</option>
								<option value="am">AM</option>
								<option value="ru">RU</option>
								<option value="ir">IR</option>
							</select>
						</span>
                                <span class="input-group-addon">
							<label for="actionType1">PRINT:<input name="actionType" id="actionType1" type="radio"
                                                                  value="1" checked></label>
							<label for="actionType2">SENDM:<input name="actionType" id="actionType2" type="radio"
                                                                  value="2">
						</span>
                                <span class="input-group-addon">
							<input type="hidden" name="i" value="1">
							<input type="hidden" name="travel_id" id="travel_id">
							<button onclick="extra_action(1);return false;">CONFIRM</button>
							<script type="text/javascript">
								function extra_action(action) {
                                    if ($("input[name=actionType]:checked").val() == 2) {


                                        if (!confirm("Send Mail to " + $("#vcustomerEmail").val() + "?")) {
                                            return false;
                                        }
                                    }
                                    if (action == 1) {
                                        window.open("invoices/?printType=" + $("input[name=printType]:checked").val() + "&country_code=" + $("#country_code").val() + "&actionType=" + $("input[name=actionType]:checked").val() + "&i=1&travel_id=" + $("#travel_id").val());
                                    }
                                }
							</script>
						</span>
                            </div>
                        </div>
                        </p>
                        <p></p>
                        <div class="input-group" style="min-width:790px;">
					<span class="input-group-addon">
						<select name="vcountry" style='width:180px' class='form-control' id="vcountry" onchange="getSubRegion();">
                            <option value=''>Country of residence</option>
							<?= page::buildOptions("regions") ?>
						</select>
					</span>
                            <span class="input-group-addon">
                        <select name="vcity" class='form-control' style='width:140px' id="vcity" disabled="true">
                            <option value="">----</option>
                        </select>
                    </span>
                    <span class="input-group-addon">
                        <input type='text' class='form-control' style='width:200px' id='vcityex' placeholder="City">
                    </span>
                    <textarea class="form-control" style="height:48px;width:220px" name="vcustomerAddress"
                                      id="vcustomerAddress" placeholder="Address/Street"></textarea>
                    
                    
                            
                        </div>
                        <div class="input-group" style="min-width:790px;margin-top:10px">
                            <span class="input-group-addon">
                                <select name="vnationality" id='vnationality' style='width:112px' class='form-control'>
                                    <option>Nationality</option>
                                    <?= page::buildOptions("regions") ?>
                                </select>
                            </span>
                            <span class="input-group-addon">
                                <input type='date' id='vbirthday' name='vbirthday' class='form-control' style='width:140px'>
                            </span>
                    <span class="input-group-addon">
                        <input type='text' class='form-control' style='width:110px' name='vpassport' id='vpassport' placeholder="Passport Info">
                    </span>
                    <span class="input-group-addon">


                        <select name="vlanguage" id="vlanguage" class="form-control" style='width:160px' >
                                <option value="">Առաջնային:</option>
                                <option value="1" selected="">Հայերեն</option>
                                <option value="2">Ռուսերեն</option>
                                <option value="3">Անգլերեն</option>
                                <option value="4">Իսպաներեն</option>
                                <option value="5">Ֆրանսերեն</option>
                                <option value="6">Գերմաներեն</option>
                                <option value="7">Իտալերեն</option>
                                <option value="8">Պարսկերեն</option>
                        </select>
                    </span>
                    <span class="input-group-addon">
                        <input type='checkbox' id='varmenian' name='varmenian' >
                        <label for='varmenian' >Knows Armenian</label>
                    </span>
                            
                        </div>
                        <p></p>

                        <div class="input-group" style="min-width:790px;">
					<!-- <span class="input-group-addon" style="text-align:left;">	
						<select name="vsource" id="vsource" style="max-width: 65px;">
							<?= page::buildOptions("data_source") ?>
						</select>
						<input type="text" placeholder="more details" name="vsourceNote" id="vsourceNote">
						<select name="vsellpoint" id="vsellpoint" style="max-width: 65px;">
							<?= page::buildOptions("data_sellpoint") ?>
						</select>
                        <input type="text" placeholder="Notification Date" name="valert_date" id="valert_date">

					</span> -->
                            <!-- <input type="text" class="form-control" placeholder="Some Note" style="min-height:36px;"
                                   name="vsellNote" id="vsellNote"> -->
                        </div>
                    </div>
                    <br>
                    <div style="text-align:center;">


                        <div class="input-group" style="min-width:790px;">
			
                    <span class="input-group-addon" style="width: 40px;">
                        CBA /AMD =
                    </span>
                            <span class="input-group-addon">
                        <input name="vexchange_USA" id="vexchange_USA" type="number" onchange="getTotalProfit()"
                               step="0.0001" value="<?php echo $exchangeratescba["USD"]; ?>" style="width: 70px;"> USD
                    </span>
                            <span class="input-group-addon">
                        <input name="vexchange_EUR" id="vexchange_EUR" type="number" onchange="getTotalProfit()"
                               step="0.0001" value="<?php echo $exchangeratescba["EUR"]; ?>" style="width: 70px;"> EUR
                    </span>
                            <span class="input-group-addon">
                        <input name="vexchange_IRR" id="vexchange_IRR" type="number" onchange="getTotalProfit()"
                               step="0.0001" value="<?php echo $exchangeratescba["IRR"]; ?>" style="width: 70px;"> IRR
                    </span>
                            <span class="input-group-addon">
                        <input name="vexchange_GEL" id="vexchange_GEL" type="number" onchange="getTotalProfit()"
                               step="0.0001" value="<?php echo $exchangeratescba["GEL"]; ?>" style="width: 70px;"> GEL
                    </span>
                            <span class="input-group-addon">
                        <input name="vexchange_RUR" id="vexchange_RUR" type="number" onchange="getTotalProfit()"
                               step="0.0001" value="<?php echo $exchangeratescba["RUB"]; ?>" style="width: 70px;"> RUB
                    </span>
                            <span class="input-group-addon">
                        <input name="vexchange_GBP" id="vexchange_GBP" type="number" onchange="getTotalProfit()"
                               step="0.0001" value="<?php echo $exchangeratescba["GBP"]; ?>" style="width: 70px;"> GBP
                    </span>
                        </div>


                        <div class="input-group" style="min-width:790px;">
					
                    <span class="input-group-addon">
				            <input type="SUBMIT" class="btn btn-default" value="SAVE"
                                   style="width:100%;max-width:858px;" onclick="addEditData(1);return false;"
                                   id="vInput">
                    </span>

                            <span class="input-group-addon">
                        <div class="btn btn-default" style="width:100%;max-width:858px;"
                             onclick="$('#tlAdd').modal('hide')">CLOSE</div>
                    </span>

                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Modal -->
<div class="modal fade" id="transferModal" tabindex="-1" role="dialog" aria-labelledby="transferModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="transferModalLabel">Transfer</h4>
            </div>
            <div class="modal-body">
                <select id="transfer_type">
                    <option value="1">Dimavorum</option>
                    <option value="2">Chanaparhum</option>
                    <option value="3">TOUR</option>
                </select>
                <input type="hidden" value="" id="transfer_data_id"/>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="makeTransfer();">TRANSFER</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="change_log" tabindex="-1" role="dialog" aria-labelledby="log_data">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="log_data">Change Log</h4>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <td>Id</td>
                        <td>Log</td>
                        <td>Date</td>
                        <td>Usen Name</td>
                    </tr>
                    </thead>
                    <tbody class="log_table_body">
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- initialize library-->
<!-- Latest jquery compiled and minified JavaScript -->
<script src="https://code.jquery.com/jquery-latest.min.js"></script>
<!-- Bootstrap minified JavaScript -->
<script src="<?= $rootF ?>/template/bootstrap/js/bootstrap.min.js"></script>
<!--end initialize library-->
<!-- Menu Toggle Script -->
<!-- Bootstrap minified JavaScript -->
  
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.js"></script>
<script src="<?= $rootF ?>/template/js/accounting.min.js"></script>
<script src="<?= $rootF ?>/template/datepicker/js/bootstrap-datepicker.js"></script>
<script src="<?= $rootF ?>/template/js/phpjs.js"></script>
<script src="<?= $rootF ?>/template/rangedate/moment.min.js"></script>
<script src="<?= $rootF ?>/template/rangedate/jquery.daterangepicker.js"></script>
<script src="<?= $rootF ?>/template/datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<?php
$regionData = page::getRegionFromCC($cc);
?>
<script>
   new DataTable('#mainTable');

    var data = {};
    window.roomObject = {};
    window.hotelExtraObject = {};
    window.hotelListObject = {};
    window.sum_overall = {"total": 0, "spend": 0, "percent": 0, "left_over": 0};
    roomObject[0] = {};
    hotelExtraObject[0] = {};
    var send_data = "";
    var data_type = "travel";//$pageName
    var fromP = 0;
    var toP = 200;
    var spointType = <?=page::getJsonData("data_sellpoint");?>;
    window.currency_name = <?=page::getJsonData("currency");?>;
    var sourceType = <?=page::getJsonData("data_source");?>;
    var countryType = <?=page::getJsonData("regions");?>;
    var partner_names = <?=page::getJsonData("travel_partner");?>;

    window.order_currency = {
        "USD": <?php echo $exchangeratescba["USD"]; ?>,
        "1": <?php echo $exchangeratescba["USD"]; ?>,
        "EUR": <?php echo $exchangeratescba["EUR"]; ?>,
        "4": <?php echo $exchangeratescba["EUR"]; ?>,
        "RUB": <?php echo $exchangeratescba["RUB"]; ?>,
        "2": <?php echo $exchangeratescba["RUB"]; ?>,
        "IRR": <?php echo $exchangeratescba["IRR"]; ?>,
        "6": <?php echo $exchangeratescba["IRR"]; ?>,
        "GBP": <?php echo $exchangeratescba["GBP"]; ?>,
        "5": <?php echo $exchangeratescba["GBP"]; ?>,
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
    $('#menuDrop .dropdown-menu').on({
        "click": function (e) {
            e.stopPropagation();
        }
    });

    $(document).on('change', '#hotel_id', function () {
        var hotel_id = $(this).val();
        if(hotel_id == 494 || hotel_id == 4923){
            $("#vWePaid").prop('checked',true);
            $("#vWePaid").attr('value','1');
            $("#vWePaid").attr('disabled',true);
        }
        else{
            $("#vWePaid").prop('checked',false);
            $("#vWePaid").attr('value','0');
            $("#vWePaid").attr('disabled',false);
        }
    })
    $(document).on('click', '#printyfy .dropdown-menu', function (e) {
        e.stopPropagation();
    });

    $('#printyfy').on('hide.bs.dropdown', function () {
        var checked_input = $('#printyfy .dropdown-menu').find('input:checked');

        var field_arr = [
            '.p_id',
            '.p_deal_date',
            '.p_status',
            '.p_airline',
            // '.p_name',
            '.p_contacts',
            '.p_hotels',
            '.p_comments',
            '.p_sell_point',
            '.p_last_updated_date',
            // '.p_arrival_date',
            // '.p_departure_date',
            '.p_price',
            '.p_net',
            // '.p_we_paid',
            '.p_partial_paid',
            '.p_guests',
            '.p_partner_order_id',
            '.p_check_in_date',
            '.p_check_out_date',
        ];

        var p_price_block = false;
        var p_id_block = false;
        checked_input.each(function () {
            var sel_id = $(this)[0].id;
            var p_class = '.p_' + sel_id;

            $(p_class).removeClass('hiddenforprint');



            if (p_class === '.p_id' ||
                p_class === '.p_deal_date') {
                p_id_block = true;
            }

            if (p_class === '.p_price' ||
                p_class === '.p_net' ||
                p_class === '.p_partial_paid') {
                p_price_block = true;
            }

            var index = field_arr.indexOf(p_class);
            if (index !== -1) field_arr.splice(index, 1);

        });

        $.each(field_arr, function (key, val) {

            $(val).addClass('hiddenforprint');

        });

        if (p_price_block) {
            $('.p_price_block').removeClass('hiddenforprint');

        } else {
            $('.p_price_block').addClass('hiddenforprint');
        }

        if (p_id_block) {
            $('.p_id_block').removeClass('hiddenforprint');

        } else {
            $('.p_id_block').addClass('hiddenforprint');
        }
    });
    $(document).on('click', ".open_edited_popup", function () {

        var id = $(this).attr('data-id');

        data = {"itemId": id};


        // console.log(data);
        var data_encode = base64_encode(json_encode(data));
        send_data = "";

        if (data) {
            send_data = "&encodedData=" + data_encode;
        }

        var getUrl = "<?=$rootF?>/data.php?cmd=get_archive&page=" + data_type + send_data;

        $.get(getUrl, function (get_data) {


            $('#change_log').modal('show');
            var html = '';
            for (var i = 0; i < get_data['data'].length; i++) {
                var data = get_data['data'][i];

                html += '<tr>';
                html += '<td>' + (i + 1) + '</td>';
                html += '<td>' + data.travel_log + '</td>';
                html += '<td>' + data.update_date + '</td>';
                html += '<td>' + data.username + '</td>';
                html += '</tr>';

            }

            $(".log_table_body").html(html)

        });
        // ToDo: create backand part
    });

    $(document).on('click', "button.read-more", function () {

        var elem = $(this).parent().find(".text");
        if (elem.hasClass("short")) {
            elem.removeClass("short").addClass("full");

        }
        else {
            elem.removeClass("full").addClass("short");

        }
    });


    function loadData(v1, v2) {
        fromP = v1;
        filter(null);
    }

    if ($('[addon="rangedate"]')) {
        //$('[addon="rangedate"]').dateRangePicker().bind('datepicker-apply',function(){filter(this,true);});
    }
    if ($('[addon="date"]')) {
        $('[addon="date"]').datepicker({format: 'yyyy-mm-dd'}).on('changeDate', function () {
            filter(this, true);
        });
    }
    $('input[type=checkbox]').click(function () {
        if ($(this).is(':checked')) {
            $(this).val(1);
        } else {
            $(this).val(0);
        }
    });

    $('#valert_date').datetimepicker({"format": "YYYY-MM-DD HH:mm"});
    $('#varraival_date').datetimepicker({"format": "YYYY-MM-DD HH:mm"});
    $('#vdeparture_date').datetimepicker({"format": "YYYY-MM-DD HH:mm"});

    $('#check_in').datetimepicker({"format": "YYYY-MM-DD HH:mm"});
    $('#check_out').datetimepicker({"format": "YYYY-MM-DD HH:mm"}).attr("disabled", "disabled");

    $('#check_in_sm').datetimepicker({"format": "YYYY-MM-DD 13:59"});
    $('#check_out_sm').datetimepicker({"format": "YYYY-MM-DD 11:59"}).attr("disabled", "disabled");

    $("#check_in").on("dp.change", function (e) {
        $('#check_out').data("DateTimePicker").minDate(e.date);
        $('#check_out').removeAttr("disabled");
    });
    $("#check_in_sm").on("dp.change", function (e) {
        $('#check_out_sm').data("DateTimePicker").minDate(e.date);
        $('#check_out_sm').removeAttr("disabled");
    });
    
    $(document).on('change','#actionSel',function(){
        $(".actionMain").html('');
        $(".actionMain").append('<textarea id="actionInformation" style="margin-top:10px" placeholder="Information"></textarea>');
    })
    $("#vServices").on("change", function () {
        if ($(this).val() == 3 || $(this).val() == 4 || $(this).val() == 5 || $(this).val() == 6) {
            $("[data-rel-hotel='true']").css("display", "block");
        } else {
            $("[data-rel-hotel='true']").css("display", "none");
        }
    });


    function geStatusName(status_id) {

        var status_name = "NEW ORDER";

        if (status_id == 1) {
            status_name = "REFOUND";
        }

        if (status_id == 2) {
            status_name = "CANCLE";
        }

        if (status_id == 3) {
            status_name = "COMMUNICATION";
        }

        if (status_id == 4) {
            status_name = "PAID";
        }

        if (status_id == 5) {
            status_name = "PENDING";
        }

        if (status_id == 6) {
            status_name = "RESERVATION";
        }

        return status_name;
    }
    var mainItemId = '';
    function addEditData(add, itemId, closedisabled) {

        $("#vWePaid").prop('checked', false);
        $(".displayActionsPart").html('');

        $("#applyButton").addClass('disabled');
        mainItemId = itemId;
        $("#id_mod_order_id").text("NUM-" + itemId + " / " + geStatusName($("#ext_tr_id_" + itemId).attr("ext_status")));

        // $("#vInput").addClass('disabled');
        $('#check_out').attr("disabled", "disabled");
        var command = "";
        var d = new Array();
        d[0] = $("#vServices");
        d[1] = $("#vstatus");
        d[2] = $("#vprice");
        d[3] = $("#vcurrency");
        d[4] = $("#vpayment");
        d[5] = $("#vpaymentNote");
        d[6] = $("#vcustomerName");
        d[7] = $("#vcustomerPhone");
        d[8] = $("#vcustomerEmail");
        d[9] = $("input[name=vcustomerType]:checked");
        d[10] = $("#vcountry");
        d[11] = $("#vcity");
        d[12] = $("#vcustomerAddress");
        d[13] = $("#vsource");
        d[14] = $("#vsourceNote");
        d[15] = $("#vsellpoint");
        d[16] = $("#vsellNote");
        d[17] = $("#vuid");
        d[18] = $("#vguests");
        d[19] = $("#vpartial_pay");
        d[20] = $("#vpartial_pay_note");
        d[21] = $("#vrevenue");
        d[22] = $("#vpartneID");
        d[23] = $("#valert_date");
        d[24] = $("#vprt_order_id");
        d[25] = $("#vtotal_income");
        d[26] = $("#vWePaid");
        d[27] = $("#vCurrencyOfPrice");
        d[28] = $("#vCurrencyPartialPaied");
        d[29] = $("#vexchange_USA");
        d[30] = $("#vexchange_EUR");
        d[31] = $("#vexchange_IRR");
        d[32] = $("#vexchange_GEL");
        d[33] = $("#vexchange_RUR");
        d[34] = $("#vexchange_GBP");
        d[35] = $("#vairline_id");
        d[36] = $("#varraival_date");
        d[37] = $("#vdeparture_date");
        d[38] = $("#vnationality");
        d[39] = $("#vpassport");
        d[40] = $("#vbirthday");
        d[41] = $("#varmenian");
        d[42] = $("#vlanguage");
        d[43] = $("#vroomkey");
        d[44] = $("#vwifiinfo");
        d[45] = $("#vlocalareainfo");
        d[46] = $("#vhotelpolicypapers");
        d[47] = $("#vcheckedpayment");
        d[48] = $("#vroomkeytaken");
        d[49] = $("#vinvoiceprovided");
        d[50] = $("#vcityex");
        d[51] = $("#vpaymenttype");

        for (var i = 0; i < d.length; i++) {
            if (d[i] && d[i].val()) {
                command += d[i].attr("id") + "=" + d[i].val().replace(/\n/g, '\\n') + "&";
            } else if (!d[i].val()) {
                command += d[i].attr("id") + "=&";
            }
        }
        command = command.substring(0, command.length - 1);

        var actionArray = new Object;
        actionArray.action_id = $("#actionSel").val();
        actionArray.information = $("#actionInformation").val();
        actionArray.status = $("#status_action").val();
        actionArray.user_id = "<?php echo $userData[0]['id'] ?>";

        if (add == 1) {
            $("#actionStatus").css("display", "none");
            $("#vform").css("display", "block");
            if(saveButtonCheckInputs()){
                addToHotelList();
                setTimeout(function(){
                    location.reload();
                },1500)
                setTimeout(function(){
                    $.get("<?=$rootF;?>/data.php?cmd=addData&" + command + "&actionArray=" + JSON.stringify(actionArray) + "&hotel_data=" + base64_encode(JSON.stringify(hotelListObject)), function (data) {
                        console.log(data.data);
                        if (data.data.ok) {
                            $("#vform").css("display", "none");
                            // filter(null, true);
                            // $('#tlAdd').modal('hide');
                        }
                    });
                },1000)
            }
        } else if (add == 2) {
            $("#hotel_list > ol").html("");
            $("#travel_data_id").val(itemId);
            $.get("<?=$rootF;?>/data.php?cmd=viewData&itemId=" + itemId, function (data) {
                loadAndSetHotelData(itemId);
                $("#actionStatus").css("display", "none");
                $("#vform").css("display", "block");
                for (key in data["data"][0]) {
                    var divIdstandart = key;
                    var divId = "v" + key.replace("travel_", "");

                    if (divId == "vWePaid") {
                        var value = data.data[0][divIdstandart];
                        if (value == 1) {
                            $('#vWePaid').prop('checked', true);
                        }
                    }

                    if (divId == "vcountry") {
                        var value = data.data[0][divIdstandart];

                        if (value == 0) {
                            data.data[0][divIdstandart] = 364;
                        }
                    }
                    if (divId == "vnationality") {
                        var value = data.data[0][divIdstandart];

                        if (value == 0) {
                            data.data[0][divIdstandart] = 364;
                        }
                    }

                    if (divId == "vcity") {
                        var value = data.data[0][divIdstandart];
                        if (value == 0) {
                            data.data[0][divIdstandart] = 1397;
                        }
                    }

                    if ($('#' + divIdstandart).is(':checkbox')) {
                        var value = data.data[0][divIdstandart];
                        $("#" + divIdstandart).each(function () {
                            if ($(this).val() == value) {
                                $(this).attr("checked", "true");
                            }
                        });
                    }

                    if (divId == "vcustomerType") {
                        var value = data.data[0][key];
                        $("input[name=vcustomerType][value =" + value + "]").prop("checked", true);

                    } else if (divId == "vuid") {

                    } else if (divId == "vServices") {
                        var newD = urldecode(html_entity_decode(data.data[0][key]));
                        if (newD == 3 || newD == 4 || newD == 5 || newD == 6) {
                            $("[data-rel-hotel='true']").css("display", "block");
                        } else {
                            $("[data-rel-hotel='true']").css("display", "none");
                        }

                        $("#" + divId).val(newD);
                    } else {
                        var newD = urldecode(html_entity_decode(data.data[0][key]));
                        var newDst = urldecode(html_entity_decode(data.data[0][divIdstandart]));
                        $("#" + divId).val(newD);
                        $("#" + divIdstandart).val(newDst);
                        if (divId == "vcountry") {
                            getSubRegion();
                        }
                        if (divId == "vnationality") {
                            getSubRegion();
                        }
                        if (divId == "vcity") {
                            setCity(newD);
                        }
                    }

                }
                $("#editors").html(data.data[0].addedBy[0].travel_log + " By:" + data.data[0].addedBy[0].username + " and " + data.data[0].latestUpdate[0].travel_log + " By:" + data.data[0].latestUpdate[0].username);
                $("#vInput").attr("onclick", "addEditData(3," + itemId + ");return false;");
                $("#applyButton").attr("onclick", "addEditData(3," + itemId + ",true);return false;");
                $('#tlAdd').modal({backdrop: 'static', keyboard: false});
                $("#travel_id").val(itemId);
            });
        } else if (add == 3) {
            // saveButtonCheckInputs();
            getTotalProfit();
            var get_command = "<?=$rootF;?>/data.php?cmd=editData&actionArray=" + JSON.stringify(actionArray) + "&itemId=" + itemId + "&" + command + "&hotel_data=" + base64_encode(JSON.stringify(hotelListObject));


            var post_data = {};


            $(":checkbox").each(function () {
                if (this.hasAttribute("confirmhotel")) {
                    var attribute = this.getAttribute("id");

                    if ($(this).is(':checked')) {
                        post_data[attribute] = 1;
                    } else {
                        post_data[attribute] = 0;
                    }


                }
            });

            $.post(get_command, post_data, function (data) { // get(get_command, function (data){
                setTimeout(function(){
                 location.reload();
                },500)
                if (data.data.ok) {
                }
            });


        } else if (add == 4) {
            loadAndSetHotelData(0);
            $("#applyButton").addClass('disabled');
            $("[data-rel-hotel='true']").css("display", "none");
            $("#editors").html("");
            $("#hotel_list > ol").html("");
            $("#actionStatus").css("display", "none");
            $("#vform").css("display", "block");
            $("#vform")[0].reset();
            $("#vInput").attr("onclick", "addEditData(1);return false;");
            $("#applyButton").attr("onclick", "addEditData(1);return false;");
            $('#tlAdd').modal('show');

        }

        $("#vform :input").change(function () {
            if (add == 2) {
                $("#applyButton").removeClass('disabled');
            }
            $("#vInput").removeClass('disabled');
        });
    }
    function saveButtonCheckInputs() {
        var inputArrays = Array('check_in');
        var sendRequest = true;
        var hotel_id = $("#hotel_id").val();
        var registered_by = $("#vpartneID").val();
        if(hotel_id > 1){
        }
        else{
            sendRequest = false;
            alert("Please select hotel!")
        }
        if(registered_by < 1){
            sendRequest = false;
            alert("Please select Sale Point!");
        }
        for(var i = 0 ; i < inputArrays.length;i++){
            if($("#"+inputArrays[i]).val() == '' || $("#"+inputArrays[i]).val() == 0){
                sendRequest = false;
                alert("Please add " + $("#"+inputArrays[i]).attr('placeholder'));
                return false;
            }
        }
        if(sendRequest == false){
            return false;
        }
        console.log(sendRequest);
        return true;
    }
    function setCity(val) {
        if (val > 0) {
            setTimeout(function () {
                if ($("#vcity option[value=" + val + "]")) {
                    $("#vcity option[value=" + val + "]").attr("selected", "selected");
                } else {
                    setCity(val);
                }
            }, 100);
        }
    }

    function loadAndSetHotelData(id) {
        if (id > 0) {

            jQuery("#hotel_room_list > ol").html("");
            jQuery("#hotel_extra_list > ol").html("");
            roomObject = {};
            hotelExtraObject = {};
            hotelListObject = {};
            $.get("<?=$rootF;?>/data.php?cmd=getHotelAndExtra&itemId=" + id, function (data) {
                if (data.data) {
                    for (var key in data.data) {
                        for (var nkey in data.data[key]) {
                            if (nkey == 'rooms') {
                                for (var i = 0; i < data.data[key][nkey].length; i++) {
                                    addRoomList(id, data.data[key][nkey][i].hotel_room_id, data.data[key][nkey][i].room_count, data.data[key][nkey][i].room_price, data.data[key][nkey][i].extra_count, data.data[key][nkey][i].extra_price, true,data.data[key][nkey][i].room_check_in,data.data[key][nkey][i].room_check_out,data.data[key][nkey][i].room_number);
                                }
                            } else if (nkey == 'extra') {
                                for (var i = 0; i < data.data[key][nkey].length; i++) {
                                    addRoomExtraList(id, data.data[key][nkey][i].order_extra_id, data.data[key][nkey][i].order_extra_count, data.data[key][nkey][i].order_extra_price, true);
                                }
                            }

                        }
                        addToHotelList(key, data.data[key].global);
                        setTimeout(function(){
                            $('#check_in').val(data.data[key].global.check_in.slice(0,-3));
                            $('#check_out').val(data.data[key].global.check_out.slice(0,-3));
                        },500)
                        $.ajax({
                            type : "POST",
                            data : {cmd:'getactions',row_id:mainItemId},
                            url:"<?=$rootF;?>/data.php",
                            success:function(res){
                                if(res.length > 0){
                                    var display_html = "<table  border='1' style='width:100%'>";
                                    display_html+="<thead>";
                                        display_html+="<tr>";
                                            display_html+="<td>Action</td>";
                                            display_html+="<td>Date</td>";
                                            display_html+="<td>User</td>";
                                            display_html+="<td>Information</td>";
                                            display_html+="<td>Status</td>";
                                            display_html+="<td>Done</td>";
                                            display_html+="<td>Done By</td>";
                                            display_html+="<td>Done Date</td>";
                                        display_html+="</tr>";
                                    display_html+="</thead>";
                                        display_html+="<tbody>";
                                    for(var i = 0;i < res.length;i++){
                                        var colorText = 'black'
                                        var statusText = 'What Hapened?'
                                        if(res[i].status == 0){
                                            colorText = 'red';
                                            statusText ="Required to do";
                                        }
                                            display_html+= "<tr>";
                                                display_html+="<td>";
                                                    display_html+=res[i]['action_info'].name;
                                                display_html+="</td>";
                                                display_html+="<td>";
                                                    display_html+=replaceDatetimeFormat(res[i].data_inserted);
                                                display_html+="</td>";
                                                display_html+="<td>";
                                                    display_html+=res[i]['user_info'].full_name_am;
                                                display_html+="</td>";
                                                display_html+="<td style='color:" + colorText + "'>";
                                                    display_html+=res[i].information;
                                                display_html+="</td>";
                                                display_html+="<td style='color:" + colorText + "' >";
                                                    display_html+=statusText
                                                display_html+="</td>";
                                                display_html+="<td>";
                                                    if(res[i].status == 0){
                                                        if(res[i].done == 1){
                                                            display_html+="<input disabled checked type='checkbox'> Done!"
                                                        }
                                                        else{
                                                            display_html+="<input data-row-id='" + res[i].id + "' class='doneCheckbox' type='checkbox'> Done!"
                                                        }
                                                    }
                                                display_html+="</td>";
                                                display_html+="<td>";
                                                if(res[i]['done_user_id'] > 0){
                                                    display_html+=res[i]['done_user_info'].full_name_am;
                                                }
                                                display_html+="</td>";
                                                display_html+="<td>";
                                                if(res[i]['done_user_id'] > 0){
                                                    display_html+=replaceDatetimeFormat(res[i].done_datetime);
                                                }
                                                display_html+="</td>";
                                            display_html+="</tr>";
                                    }
                                    display_html+="</tbody>";
                                    display_html+="</table>";
                                    $(".displayActionsPart").html(display_html);
                                }
                            }
                        })
                        $("#vbirthday").val(data.data[key].travelInfo[0]['travel_birthday'])
                        if(data.data[key].travelInfo[0]['travel_armenian'] == 1){
                            $("#varmenian").attr('checked',true);
                        }
                        else{
                            $("#varmenian").attr('checked',false);
                        }
                        if(data.data[key].travelInfo[0]['travel_roomkey'] == 1){
                            $("#vroomkey").attr('checked',true);
                        }
                        else{
                            $("#vroomkey").attr('checked',false);
                        }
                        if(data.data[key].travelInfo[0]['travel_wifiinfo'] == 1){
                            $("#vwifiinfo").attr('checked',true);
                        }
                        else{
                            $("#vwifiinfo").attr('checked',false);
                        }
                        if(data.data[key].travelInfo[0]['travel_localareainfo'] == 1){
                            $("#vlocalareainfo").attr('checked',true);
                        }
                        else{
                            $("#vlocalareainfo").attr('checked',false);
                        }
                        if(data.data[key].travelInfo[0]['travel_hotelpolicypapers'] == 1){
                            $("#vhotelpolicypapers").attr('checked',true);
                        }
                        else{
                            $("#vhotelpolicypapers").attr('checked',false);
                        }
                        if(data.data[key].travelInfo[0]['travel_checkedpayment'] == 1){
                            $("#vcheckedpayment").attr('checked',true);
                        }
                        else{
                            $("#vcheckedpayment").attr('checked',false);
                        }
                        if(data.data[key].travelInfo[0]['travel_roomkeytaken'] == 1){
                            $("#vroomkeytaken").attr('checked',true);
                        }
                        else{
                            $("#vroomkeytaken").attr('checked',false);
                        }
                        if(data.data[key].travelInfo[0]['travel_invoiceprovided'] == 1){
                            $("#vinvoiceprovided").attr('checked',true);
                        }
                        else{
                            $("#vinvoiceprovided").attr('checked',false);
                        }
                        setTimeout(function(){
                            checkCheckInCheckboxes();
                        },1000)
                    }

                } else if (data.error) {
                    console.error(data.error);
                }
            });
        } else {
            $("#travel_data_id").val(0);
            roomObject = {};
            hotelExtraObject = {};
            hotelListObject = {};
            jQuery("#hotel_room_list > ol").html("");
            jQuery("#hotel_extra_list > ol").html("");
        }
    }
    $(document).on("change",".doneCheckbox",function(){
        $(this).attr('disabled',true);
        var row_id = $(this).attr('data-row-id');
        $.ajax({
            type : "POST",
            data : {cmd:'insertDone',row_id:row_id,done_user_id:<?php echo $userData[0]['id'] ?>},
            url:"<?=$rootF;?>/data.php",
            success:function(res){
                console.log(res)
            }
        })
        console.log(row_id);
    })
    $(document).on("change",".checkInPerson",function(){
        checkCheckInCheckboxes();
    })
    function checkCheckInCheckboxes() {
        if($(".checkInPerson").prop("checked") == true){
            $(".checkInSmV").attr('disabled',false);
        }
        else{
            $(".checkInSmV").attr('disabled',true);
        }
    }
    $(document).on("change",".checkOutPerson",function(){
        checkCheckOutCheckboxes();
    })
    function checkCheckOutCheckboxes() {
        if($(".checkOutPerson").prop("checked") == true){
            $(".checkOutSmV").attr('disabled',false);
        }
        else{
            $(".checkOutSmV").attr('disabled',true);
        }
    }
    function getSubRegion() {
        var itemId = $("#vcountry").val();
        if (itemId) {
            $.get("<?=$rootF;?>/data.php?cmd=getSubRegion&itemId=" + itemId, function (data) {
                $("#vcity").removeAttr("disabled");
                $("#vcity").html(data);
            });
        } else {
            $("#vcity").attr("disabled", "true");
            $("#vcity").html("<option value=\"\">----</option>");
        }

    }

    function addRoom() {

        var roomType = jQuery("#hotel_room_type :selected");
        var roomCount = jQuery("#hotel_room_count");
        var roomPrice = jQuery("#hotel_room_price");
        var roomExtraBed = jQuery("#hotel_extra_bed_count");
        var check_in_sm = jQuery("#check_in_sm").val();
        var check_out_sm = jQuery("#check_out_sm").val();
        var check_out_sm = jQuery("#check_out_sm").val();
        var data_hotel_room_numbers = jQuery("#data_hotel_room_numbers").val();
      // new
        var data_hotel_room_numbers = jQuery("#data_hotel_room_numbers").find(":selected").text();
        var check_in = jQuery("#check_in").val();
        var check_out = jQuery("#check_out").val();
        if ((check_in == "") || (check_out == "")) {
            alert("Please enter Check in and Check out dates");
            return false
        }
        var dt1 = new Date(check_in);
        var dt2 = new Date(check_out);
        var millisBetween = dt1.getTime() - dt2.getTime();  
        var days = millisBetween / (1000 * 3600 * 24);  
        var diffCheckDays = Math.round(Math.abs(days));
        if(diffCheckDays <= 1){
            diffCheckDays = "1" + " Day";
        }
        else{
            diffCheckDays+=" Days";
        }
        // new
        var roomExtraBedPrice = jQuery("#hotel_extra_bed_price");
        if (roomType.val() < 1) {
            alert("Please select one.");
            return false;
        }
        if (!roomObject[jQuery("#travel_data_id").val()]) {
            roomObject[jQuery("#travel_data_id").val()] = {};
        }
        if (roomObject[jQuery("#travel_data_id").val()][roomType.val()]) {
            alert("Please Remove existing Before Adding New");
            return false;
        }
        roomObject[jQuery("#travel_data_id").val()][roomType.val()] = [roomCount.val(), roomPrice.val(), roomExtraBed.val(), roomExtraBedPrice.val(),check_in_sm,check_out_sm,data_hotel_room_numbers];
        if (roomPrice.val() < 1) {
            roomPrice.val(0.000);
        }
        if (roomExtraBedPrice.val() < 1) {
            roomExtraBedPrice.val(0.000);
        }
        var html_set = '<li class="hotel_room_list_item" data-roomType="' + jQuery("#travel_data_id").val() + roomType.val() + '">';
        html_set += '<span class="hotel_room_numbers"><strong>N: ' + data_hotel_room_numbers + ';</strong> </span>';
        html_set += '<span class="hotel_room_name">' + roomType.text() + '&nbsp;</span>';
        // html_set += '<span class="hotel_room_count">(<strong>' + roomCount.val() + '</strong> room(s))&nbsp;</span>';
        html_set += '<span class="hotel_checkout_days">' + diffCheckDays + '; </span>';
        html_set += '<span class="hotel_room_price">Price:<strong style="color:green;">' + $("#hotel_room_price").val() + '</strong>&nbsp;</span>';
        // html_set += '<span class="hotel_room_extended">Extra Bed(<strong style="color:orange;">' + roomExtraBed.val() + '</strong>)&nbsp;</span>';
        // html_set += '<span class="hotel_room_extended_price">Price:<strong style="color:orange;">' + roomExtraBedPrice.val() + '</strong>&nbsp;</span>';
        html_set += "<span class=\"hotel_room_action\"><div onclick=\"removeThisRoom('" + roomType.val() + "');return false;\" class=\"remove_button\">x</div>&nbsp;</span>";
        html_set += '</li>';
        jQuery("#hotel_room_list > ol").append(html_set);
        html_set = "";
    }

    function addRoomList(itemId, roomType, roomCount, roomPrice, roomExtraBed, roomExtraBedPrice, objectInsert,room_check_in,room_check_out,room_number) {
        if (!roomObject[itemId]) {
            roomObject[itemId] = {};
        }
        if (!objectInsert) {
            objectInsert = false;
        }
        roomObject[itemId][roomType] = [roomCount, roomPrice, roomExtraBed, roomExtraBedPrice,room_check_in,room_check_out,room_number];
        if (!objectInsert) {
            var html_set = '<li class="hotel_room_list_item" data-roomType="' + itemId + roomType + '">';
            var rooms = (roomCount > 1) ? 'rooms' : 'room';
            html_set += '<span class="hotel_room_name">' + $("#hotel_room_type option[value='" + roomType + "']").text() + '&nbsp;</span>';
            html_set += '<span class="hotel_room_count">(<strong>' + roomCount + '</strong> ' + rooms + ')&nbsp;</span>';
            html_set += '<span class="hotel_room_price">Price:<strong style="color:green;">' + roomPrice + '</strong>&nbsp;</span>';
            html_set += '<span class="hotel_room_extended">Extra Bed(<strong style="color:orange;">' + roomExtraBed + '</strong>)&nbsp;</span>';
            html_set += '<span class="hotel_room_extended_price">Price:<strong style="color:orange;">' + roomExtraBedPrice + '</strong>&nbsp;</span>';
            html_set += "<span class=\"hotel_room_action\"><div onclick=\"removeThisRoom('" + roomType + "');return false;\" class=\"remove_button\">x</div>&nbsp;</span>";
            html_set += '</li>';
            jQuery("#hotel_room_list > ol").append(html_set);
            html_set = "";
        }
    }

    function removeThisRoom(idType, force) {
        if (force || confirm("Are you want to delete " + idType)) {
            delete roomObject[jQuery("#travel_data_id").val()][idType];
            if (!roomObject[jQuery("#travel_data_id").val()][idType]) {
                jQuery("[data-roomType='" + jQuery("#travel_data_id").val() + idType + "']").remove();
            }
        }
    }

    function addHotelExtraItem() {
        var extraType = jQuery("#hotel_extra_type :selected");
        var extraCount = jQuery("#hotel_extra_count");
        var extraPrice = jQuery("#hotel_extra_price");
        if (!hotelExtraObject[jQuery("#travel_data_id").val()]) {
            hotelExtraObject[jQuery("#travel_data_id").val()] = {};
        }
        if (extraType.val() < 1) {
            alert("Please select one.");
            return false;
        }
        if (hotelExtraObject[jQuery("#travel_data_id").val()][extraType.val()]) {
            alert("Please Remove existing Before Adding New");
            return false;
        }
        hotelExtraObject[jQuery("#travel_data_id").val()][extraType.val()] = [extraCount.val(), extraPrice.val()];
        if (extraPrice.val() < 1) {
            extraPrice.val(0.000);
        }
        var html_set = '<li class="hotel_extra_list_item" data-extraType="' + jQuery("#travel_data_id").val() + extraType.val() + '">';
        html_set += '<span class="hotel_extra_name">' + extraType.text() + '&nbsp;</span>';
        html_set += '<span class="hotel_extra_count">(<strong>' + extraCount.val() + '</strong>)&nbsp;</span>';
        html_set += '<span class="hotel_extra_price">Price:<strong style="color:green;">' + extraPrice.val() + '</strong>&nbsp;</span>';
        html_set += "<span class=\"hotel_extra_action\"><div onclick=\"removeThisExtra('" + extraType.val() + "');return false;\" class=\"remove_button\">x</div>&nbsp;</span>";
        html_set += '</li>';
        jQuery("#hotel_extra_list > ol").append(html_set);
        html_set = "";
    }
    var monthNames = new Array("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");
               
    function replaceDatetimeFormat(datetime){
        if(datetime != null){
            var arr = datetime.split(" ");
            var mycDate = arr[0].split("-");
            return mycDate[2] + "-" + monthNames[mycDate[1] - 1] + "-" + mycDate[0];
        }
        return datetime;
    }
    function addRoomExtraList(itemId, extraType, extraCount, extraPrice, objectInsert) {
        if (!hotelExtraObject[itemId]) {
            hotelExtraObject[itemId] = {};
        }
        if (!objectInsert) {
            objectInsert = false;
        }
        hotelExtraObject[itemId][extraType] = [extraCount, extraPrice];//hotel_extra_type
        if (!objectInsert) {
            var html_set = '<li class="hotel_extra_list_item" data-extraType="' + itemId + extraType + '">';
            html_set += '<span class="hotel_extra_name">' + $("#hotel_extra_type option[value='" + extraType + "']").text() + '&nbsp;</span>';
            html_set += '<span class="hotel_extra_count">(<strong>' + extraCount + '</strong>)&nbsp;</span>';
            html_set += '<span class="hotel_extra_price">Price:<strong style="color:green;">' + extraPrice + '</strong>&nbsp;</span>';
            html_set += "<span class=\"hotel_extra_action\"><div onclick=\"removeThisExtra('" + extraType + "');return false;\" class=\"remove_button\">x</div>&nbsp;</span>";
            html_set += '</li>';
            jQuery("#hotel_extra_list > ol").append(html_set);
            html_set = "";
        }

    }

    function removeThisExtra(idType, force) {
        if (!force) {
            force = false;
        }
        if (force || confirm("Are you want to delete " + idType)) {
            delete hotelExtraObject[jQuery("#travel_data_id").val()][idType];
            if (!hotelExtraObject[jQuery("#travel_data_id").val()][idType]) {
                jQuery("[data-extraType='" + jQuery("#travel_data_id").val() + idType + "']").remove();
            }
        }
    }

    function makeTransfer() {
        var transfer_type = jQuery("#transfer_type :selected").val();
        var transfer_id = jQuery("#transfer_data_id").val();
        $.get("<?=$rootF;?>/data.php?cmd=setTransfer&itemId=" + transfer_id + "&transfer_type=" + transfer_type, function (data) {
            if (data.data.transfer) {
                jQuery("#transfer_data_id").val("");
                window.location = "/account/flower_orders/order.php?orderId=" + data.data.transfer;
            }
        });
    }

    window.hotels_count = 0;

    function addToHotelList(raw_id, hotel_info) {
        if (!hotel_info) {
            hotel_info = false;
        }
        if (!raw_id) {
            raw_id = false;
        }
        var itemId = jQuery("#travel_data_id").val();
        if (($("#hotel_id :selected").val() || hotel_info)) {
            if (!hotelListObject.hasOwnProperty(itemId)) {
                hotelListObject[itemId] = {};

            }
            if (!hotelListObject[itemId].hasOwnProperty(hotels_count)) {
                hotelListObject[itemId][hotels_count] = {};
            }
            if (!hotelListObject[itemId][hotels_count].hasOwnProperty('hotel')) {
                hotelListObject[itemId][hotels_count]['hotel'] = {};
            }
            hotelListObject[itemId][hotels_count]['hotel']['id'] = (hotel_id && hotel_info['hotel_id']) ? hotel_info['hotel_id'] : $("#hotel_id :selected").val();
            hotelListObject[itemId][hotels_count]['hotel']['name'] = (hotel_id && hotel_info['hotel_id']) ? $("#hotel_id [value=" + hotel_info['hotel_id'] + "]").text() : $("#hotel_id :selected").text();
            hotelListObject[itemId][hotels_count]['hotel']['checkin'] = (hotel_id && hotel_info['check_in']) ? hotel_info['check_in'] : $("#check_in").val();
            hotelListObject[itemId][hotels_count]['hotel']['checkout'] = (hotel_id && hotel_info['check_out']) ? hotel_info['check_out'] : $("#check_out").val();
            hotelListObject[itemId][hotels_count]['hotel']['adult'] = (hotel_id && hotel_info['adult_count']) ? hotel_info['adult_count'] : $("#adult_count :selected").val();
            hotelListObject[itemId][hotels_count]['hotel']['adult_price'] = (hotel_id && hotel_info['adult_price']) ? hotel_info['adult_price'] : $("#adult_price").val();
            hotelListObject[itemId][hotels_count]['hotel']['child'] = (hotel_id && hotel_info['child_count']) ? hotel_info['child_count'] : $("#child_count :selected").val();
            hotelListObject[itemId][hotels_count]['hotel']['child_price'] = (hotel_id && hotel_info['child_price']) ? hotel_info['child_price'] : $("#child_price").val();
            hotelListObject[itemId][hotels_count]['hotel']['booking_id'] = raw_id;
            hotelListObject[itemId][hotels_count]['rooms'] = JSON.stringify(roomObject);
            hotelListObject[itemId][hotels_count]['extra'] = JSON.stringify(hotelExtraObject);
            //resset fields
            // $("#hotel_id").val("");
            // $("#check_in").val("");
            // $("#check_out").val("");
            $("#adult_count").val("0");
            $("#adult_price").val("0.000");
            $("#child_count").val("0");
            $("#child_price").val("0.000");
            $("#hotel_room_type").val("");
            $("#hotel_room_count").val("1");
            $("#hotel_room_price").val("");
            $("#hotel_extra_bed_count").val("0");
            $("#hotel_extra_bed_price").val("");
            $("#hotel_extra_type").val("");
            $("#hotel_extra_count").val("1");
            $("#hotel_extra_price").val("");
            addHotelList(itemId, hotels_count, hotelListObject[itemId][hotels_count]['hotel'], hotelListObject[itemId][hotels_count]['rooms'], hotelListObject[itemId][hotels_count]['extra'], hotel_info);
            hotels_count++;
        }
        else{
            // var vprice = $("#vprice").val();
            // var vrevenue = $("#vrevenue").val();
            var hotel_id = $("#hotel_id").val();
            // if(vprice < 1){
            //     alert('Price not enetered');
            // }
            // if(vrevenue < 1 ){
            //     alert('Net Price not enetered');
            // }
            if(hotel_id < 1 ){
                alert('Hotel not selected');
            }
        }
    }

    function addHotelList(travelId, itemId, hotel_object, getRoomObject, getExtraObject, hotel_info) {

        getRoomObject = JSON.parse(getRoomObject);
        getExtraObject = JSON.parse(getExtraObject);
        var html_set = "";
        html_set += '<table style="width:100%" tblid="' + travelId + itemId + '">';
        html_set += '<tr>';
        html_set += '<td valign="top">';
        html_set += '<li style="padding:10px" class="hotel_list_item" data-hotelList="' + travelId + itemId + '" style="position: relative;">';
        // class="p_check_in_date"
        // class="p_check_out_date"
        // console.log(hotel_object);
        // html_set += '<strong>/ CHECK IN:</strong> ' + hotel_object.checkin + "; ";
        // html_set += '<strong>/ CHECK OUT:</strong> ' + hotel_object.checkout + ";</br></br>";
        // if(hotel_object.adult > 0){
        //     html_set += '<strong>ADULT:</strong> ' + hotel_object.adult + "; ";
        // }
        // if(hotel_object.adult_price > 0){
        //     html_set += '<strong> / ADULT PRICE:</strong> ' + hotel_object.adult_price + ";";
        // }
        // if(hotel_object.child >0){
        //     html_set += '<strong> / CHILD:</strong> ' + hotel_object.child + "; ";
        // }
        // if(hotel_object.child_price >0){
        //     html_set += '<strong> / CHILD PRICE:</strong> ' + hotel_object.child_price + ";";
        // }
        if (getRoomObject[travelId]) {
            var main_hotel_id = 42;
            for (var key in getRoomObject[travelId]) {
                if (getRoomObject[travelId].hasOwnProperty(key)) {
                        main_hotel_id = hotel_info.hotel_id;
                        console.log($("#hotel_id").find(":selected").text());
                    var rooms = (getRoomObject[travelId][key][0] > 1) ? 'rooms' : 'room';
                    html_set += '<strong>Hotel: </strong>' + $("#hotel_id option[value='" + hotel_info.hotel_id + "']").text() + "<br><br> ";
                    // html_set += '<strong>Hotel: </strong>' + $("#hotel_id").find(":selected").text() + "<br><br> ";
                    html_set += '<strong>CHECK IN:</strong> ' + getRoomObject[travelId][key][4] + "; ";
                    html_set += '<strong>CHECK OUT:</strong> ' + getRoomObject[travelId][key][5] + "; <br>";
                    html_set += '<span class="hotel_room_name">' + $("#hotel_room_type option[value='" + key + "']").text() + '&nbsp;</span>';
                    // html_set += '<span class="hotel_room_count">(<strong>' + getRoomObject[travelId][key][0] + '</strong> ' + rooms + ')&nbsp;</span><br>';
                    html_set += '<strong class="hotel_room_price">Price:<strong style="color:green;">' + getRoomObject[travelId][key][1] + '</strong>&nbsp;</strong>';
                    html_set += '<strong>N:</strong> ' + getRoomObject[travelId][key][6] + "; <br>";
                    // html_set += '<span class="hotel_room_extended">Extra Bed(<strong style="color:orange;">' + getRoomObject[travelId][key][2] + '</strong>)&nbsp;</span>';
                    // html_set += '<span class="hotel_room_extended_price">Price:<strong style="color:orange;">' + getRoomObject[travelId][key][3] + '</strong>&nbsp;</span></br>';
                    removeThisRoom(key, true);
                }
            }
            $("#hotel_id").val(main_hotel_id);
            html_set = trim(html_set);
        }
        if (getExtraObject[travelId]) {
            html_set += '</br><strong>EXTRAS:</strong></br>';
            for (var key in getExtraObject[travelId]) {
                if (getExtraObject[travelId].hasOwnProperty(key)) {

                    html_set += '<span class="hotel_extra_name">' + $("#hotel_extra_type option[value='" + key + "']").text() + '&nbsp;</span>';
                    html_set += '<span class="hotel_extra_count">(<strong>' + getExtraObject[travelId][key][0] + '</strong>)&nbsp;</span>';
                    html_set += '<span class="hotel_extra_price">Price:<strong style="color:green;">' + getExtraObject[travelId][key][1] + '</strong>&nbsp;</span></br>';
                    removeThisExtra(key, true);
                }
            }
            html_set = trim(html_set);
        }

        html_set += "<div onclick=\"removeThisHotel(" + travelId + "," + itemId + ");return false;\" class=\"btn btn-xs\" style=\"position: absolute;top:0;right: 0;color:red;\">X</div>";
        html_set += '</li>';
        html_set += '</td>';
        html_set += '<td valign="top">';


        if (hotel_info["hotel_confirmed"] == 1) {
            html_set += "<span class=\"input-group-addon\" style=\" text-align:  left\">"
                + "<input type=\"checkbox\"  id=\"vConfirmHotel[" + hotel_object["booking_id"] + "]\" onchange='activateSaveOption()' confirmhotel  checked>Confirmed <br>"
                + "<button id=\"remind_hotel\">Remind Hotel</button></span>";
        } else {
            // html_set += "<span class=\"input-group-addon\" style=\" text-align:  left\">"
            //     + "<input  type=\"checkbox\" id=\"vConfirmHotel[" + hotel_object["booking_id"] + "]\" onchange='activateSaveOption()' confirmhotel>Confirmed <br>"
            //     + "<button id=\"remind_hotel\">Remind Hotel</button></span>";
        }

        html_set += '</td>';
        html_set += '</tr>';
        html_set += '</table>';


        jQuery("#hotel_list > ol").append(html_set);


        html_set = "";
    }

    function removeThisHotel(travel_id, itemId) {
        if (confirm("Are you want to remove this hotel? ")) {
            $("#applyButton").removeClass('disabled');
            $("#vInput").removeClass('disabled');
            // console.log(hotelListObject);

            delete hotelListObject[travel_id][itemId];
            if (!hotelListObject[travel_id][itemId]) {
                jQuery("[data-hotelList='" + travel_id + itemId + "']").remove();
                jQuery("[tblid='" + travel_id + itemId + "']").remove();
            }

        }
    }

    function transferPopup(id) {
        jQuery("#transferModal").modal('toggle');
        jQuery("#transfer_data_id").val(id);
    }

    jQuery("#vpartneID").on('change', function () {
        // $.get("<?=$rootF;?>/data.php?cmd=getTravelPartnerData&pid=" + $(this).val(), function (data) {
        //     if (data.data.pd) {
        //         $("#vcustomerPhone").val(data.data.pd.phone);
        //         $("#vcustomerEmail").val(data.data.pd.email);

        //     } else if (data.error) {
        //         console.error(data.error);
        //     }
        // });
    });
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

    function function_control_partners(is_enabled) {
        $('#vpartneID').prop('disabled', !is_enabled);
    }

    function getTotalProfit() {

        var gros_price = $("#vprice").val();
        var gros_currency = $("#vcurrency option:selected").val();
        var net_price = $("#vrevenue").val();
        var net_currency = $("#vCurrencyOfPrice option:selected").val();

        var netprice_amd = net_price * getCurrencyinAMD(net_currency);
        var grocerice_amd = gros_price * getCurrencyinAMD(gros_currency);

        var result = grocerice_amd - netprice_amd;

        var percent = (netprice_amd / grocerice_amd) * 100;

        result = Math.round(result);
        percent = 100 - percent;
        percent = percent.toFixed(2);


        $('#vtotal_income').val(result + " AMD " + percent + " %");

    }


    function getCurrencyinAMD(currenct_id) {

        var result = 0;

        if (currenct_id == 1) {
            result = $("#vexchange_USA").val();
        }

        if (currenct_id == 2) {
            result = $("#vexchange_RUR").val();
        }

        if (currenct_id == 3) {
            result = 1;
        }

        if (currenct_id == 4) {
            result = $("#vexchange_EUR").val();
        }

        if (currenct_id == 5) {
            result = $("#vexchange_GBP").val();
        }

        if (currenct_id == 6) {
            result = $("#vexchange_IRR").val();
        }


        if (result == 0) {
            //alert ("ERROR getCurrencyinAMD = 0");
        }

        return result;

    }


    function activateSaveOption() {
        $('#vInput').removeClass('disabled');
    }


    function getCurrencyText(currenct_id) {

        var currency_name = "NONE";

        if (currenct_id == 1) {
            currency_name = "USD";
        }

        if (currenct_id == 2) {
            currency_name = "RUB";
        }

        if (currenct_id == 3) {
            currency_name = "AMD";
        }

        if (currenct_id == 4) {
            currency_name = "EUR";
        }

        if (currenct_id == 5) {
            currency_name = "GBP";
        }

        if (currenct_id == 6) {
            currency_name = "IRR";
        }

        return currency_name;

    }

    function convertDateTime(dateTime) {
        if (dateTime === '0000-00-00 00:00') return dateTime
        var myDate = new Date(dateTime);
        var timeString = myDate.getFullYear() + '-' + myDate.toString().substr(4, 3) + '-'
            + (Number(myDate.getDate()) < 10 ? '0' + myDate.getDate() : myDate.getDate()) + ' ' + dateTime.split(" ")[1];
        return timeString;
    }

</script>
</body>
</html>