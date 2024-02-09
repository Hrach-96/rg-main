<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
$pageName = "travel";
$rootF = "../..";
include($rootF . "/apay/pay.api.php");
include($rootF . "/configuration.php");
include_once $_SERVER['DOCUMENT_ROOT'] . '/controls/GetDatabaseContent.php';


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
    <div class="hiddenforprint">
        <span><input type="checkbox" id="cleanprint" checked> Clean Print </span>
        <img src="/images/printicon.png" style="width: 20px;" onclick="openPrint()">
        <img src="/images/excel.png" style="width: 20px;" att="excel" onclick="filter(this,true)">

        <div class="btn-group" role="group" id="printyfy">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true"
                    aria-expanded="false">
                Ցուցադրել Տպելիս
                <span class="caret"></span>
            </button>
            <ul class="dropdown-menu">
                <li>
                    <label for="id">ID <input id="id" type="checkbox"></label>
                </li>
                <li>
                    <label for="partner_order_id">Partner Order ID <input id="partner_order_id" type="checkbox"></label>
                </li>
                <li>
                    <label for="status">Status <input id="status" type="checkbox" checked></label>
                </li>
                <li>
                    <label for="price">Price <input id="price" type="checkbox"></label>
                </li>
                <li>
                    <label for="partial_paid">Partial Paid <input id="partial_paid" type="checkbox"></label>
                </li>
                <li>
                    <label for="net">Net <input id="net" type="checkbox"></label>
                </li>
                <!--                <li>-->
                <!--                    <label for="we_paid">We Paid <input id="we_paid" type="checkbox"></label>-->
                <!--                </li>-->
                <li>
                    <label for="airline">Airline <input id="airline" type="checkbox"></label>
                </li>
                <!--                <li>-->
                <!--                    <label for="arrival_date">Arrival date <input id="arrival_date" type="checkbox"></label>-->
                <!--                </li>-->
                <!--                <li>-->
                <!--                    <label for="departure_date">Departure date <input id="departure_date" type="checkbox"></label>-->
                <!--                </li>-->
                <li>
                    <label for="hotels">Hotels <input id="hotels" type="checkbox" checked></label>
                </li>
                <li>
                    <label for="check_in_date">Check IN date <input id="check_in_date" type="checkbox" checked></label>
                </li>
                <li>
                    <label for="check_out_date">Check OUT date <input id="check_out_date" type="checkbox"
                                                                      checked></label>
                </li>
                <li>
                    <label for="guests">Guests <input id="guests" type="checkbox" checked></label>
                </li>
                <li>
                    <label for="deal_date">Deal Date <input id="deal_date" type="checkbox"></label>
                </li>
                <li>
                    <label for="last_updated_date">Last Updated Date <input id="last_updated_date"
                                                                            type="checkbox"></label>
                </li>
                <li>
                    <label for="comments">Comments <input id="comments" type="checkbox"></label>
                </li>
            </ul>
        </div>
    </div>

    <div class="table">
        <table class="table table-bordered">
            <thead>
            <tr class="success">
                <th class="hiddenforprint p_id_block">
                    <div id="loading"><img src="<?= $rootF; ?>/template/icons/loader.gif"></div>
                    #<strong id="fCount"></strong>
                </th>
                <th class="p_status">Status</th>
                <th style=" width: 160px" class="hiddenforprint p_airline">Airlines</th>
                <th class="p_name">Name</th>
                <th class="hiddenforprint p_price_block" style=" width: 170px">Price</th>
                <th class="hiddenforprint p_contacts">Contacts</th>
                <th class="p_hotels">Hotels</th>
                <th class="hiddenforprint p_comments">Comments</th>
                <th class="hiddenforprint p_sell_point">Sell point</th>
                <th class="hiddenforprint p_last_updated_date">Last Updated Date</th>
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
                        <input type="text" name="vprt_ordreid" id="vprt_order_id" placeholder="Prt order-ID"
                               style=" width: 155px">
					</span>
                            <span class="input-group-addon" style="text-align: left;">
                        Price : <input type="number" step="0.01" style="max-width:80px;"
                                       placeholder="Price" name="vprice" id="vprice"
                                       onChange="getTotalProfit()">
                                                <select name="vcurrency" id="vcurrency" onChange="getTotalProfit()">
						   <?= page::buildOptions("currency") ?>
						</select>
                                                <select name="vstatus" id="vstatus" style="max-width:100px;">
						     <?= page::buildOptions("data_status") ?>
						</select> 
                        <br>
                        <span style=" padding-right: 15px">Net:</span>
                        <input type="number" step="0.01" style="max-width:80px;"
                               placeholder="Net Price" name="vrevenue" id="vrevenue"
                               onChange="getTotalProfit()">
                        <select name="vCurrencyOfPrice" id="vCurrencyOfPrice"
                                style=" color: red" onChange="getTotalProfit()">
					             <?= page::buildOptions("currency") ?>
						</select>

                        <input type="text" name="vtotal_income" id="vtotal_income"
                               style="max-width:140px; color: red" placeholder="Total Benefit"
                               readonly>
                        <input type="checkbox" name="vWePaid" id="vWePaid" style="color: red">We Paid
					</span>
                            <span class="input-group-addon" style="text-align: left;">
                    PARTIAL PAID:<input type="number" step="0.0001" style="max-width:80px;" placeholder="Partial paid"
                                        name="vpartial_pay" id="vpartial_pay">
                    <select name="vCurrencyPartialPaied" id="vCurrencyPartialPaied" style=" color: red">
						   <?= page::buildOptions("currency") ?>
						</select><br/>
						<select name="vpayment" id="vpayment" style="max-width:100px;">
						<?= page::buildOptions("data_payment") ?>
						</select>
                         <input type="text" style="width: 60%" placeholder="more details" name="vpaymentNote"
                                id="vpaymentNote"><br/>
                        		</span>
                        </div>
                        <p></p>
                        <div class="input-group" style="min-width:790px;">
					<span class="input-group-addon">
                                             
						C:<input name="vcustomerType" id="vcustomerType" type="radio" value="0"
                                 onclick="function_control_partners(false)">
						P:<input name="vcustomerType" id="vcustomerType" type="radio" value="1"
                                 onclick="function_control_partners(true)">
					 
					<select name="vpartneID" id="vpartneID">
						<option value="0">select partner</option>
                        <?= page::buildOptions("travel_partner") ?>
					</select>
					</span>
                            <input type="text" class="form-control" placeholder="Customer name" name="vcustomerName"
                                   id="vcustomerName" style="min-height:36px;">
                            <span class="input-group-addon">
						<input type="text" placeholder="Phone" name="vcustomerPhone" id="vcustomerPhone"
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
						<textarea name="vguests" id="vguests" style="width:200px;margin: 0px;height: 64px;"></textarea>
					</span>

                            <span class="input-group-addon">
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
					</span>
                            <span class="input-group-addon" style="text-align: left;">
                                                
                                             <table> 
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
                                                </table> 
                                                
					</span>
                        </div>
                        </p>
                        <p data-rel-hotel="true" style="display:none">

                        <div class="input-group" style="min-width:790px;display:none" data-rel-hotel="true">
					<span class="input-group-addon">
					<select name="hotel_room_type" id="hotel_room_type">
							<option value="">Room Type</option>
                        <?= page::buildOptions("data_hotel_room_type") ?>
						</select>

						<select name="hotel_room_count" id="hotel_room_count">
							<?php
                            foreach (range(1, 20) as $number) {
                                echo "<option value=\"{$number}\">{$number}</option>";
                            }
                            ?>
						</select>
                                            <input type="number" step="0.0001" placeholder="price"
                                                   name="hotel_room_price" id="hotel_room_price"
                                                   style="max-width:80px;"/>
						<select name="hotel_extra_bed_count" id="hotel_extra_bed_count">
							<?php
                            foreach (range(0, 20) as $number) {
                                if ($number == 0) {
                                    echo "<option value=\"0\">Extra Bed</option>";
                                } else {
                                    echo "<option value=\"{$number}\">{$number}</option>";
                                }
                            }
                            ?>
						</select>
                                                <input type="numbert" step="0.0001" placeholder="price"
                                                       name="hotel_extra_bed_price" id="hotel_extra_bed_price"
                                                       style="max-width:80px;"/>
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
                            <?= page::buildOptions("hotel_order_extra") ?>
						</select>

						<select name="hotel_extra_count" id="hotel_extra_count">
							<?php
                            foreach (range(1, 20) as $number) {
                                echo "<option value=\"{$number}\">{$number}</option>";
                            }
                            ?>
						</select>
                                                <input type="number" step="0.0001" placeholder="price"
                                                       name="hotel_extra_price" id="hotel_extra_price"
                                                       style="max-width:80px;"/>
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
						<select name="vcountry" id="vcountry" onchange="getSubRegion();">
							<?= page::buildOptions("regions") ?>
						</select>
					</span>
                            <span class="input-group-addon">
						<select name="vcity" id="vcity" disabled="true">
							<option value="">----</option>
						</select>
					</span>
                            <textarea class="form-control" style="height:36px;" name="vcustomerAddress"
                                      id="vcustomerAddress" placeholder="Address/Street"></textarea>
                        </div>
                        <p></p>

                        <div class="input-group" style="min-width:790px;">
					<span class="input-group-addon" style="text-align:left;">	
						<select name="vsource" id="vsource" style="max-width: 65px;">
							<?= page::buildOptions("data_source") ?>
						</select>
						<input type="text" placeholder="more details" name="vsourceNote" id="vsourceNote">
						<select name="vsellpoint" id="vsellpoint" style="max-width: 65px;">
							<?= page::buildOptions("data_sellpoint") ?>
						</select>
                        <input type="text" placeholder="Notification Date" name="valert_date" id="valert_date">
					</span>
                            <input type="text" class="form-control" placeholder="Some Note" style="min-height:36px;"
                                   name="vsellNote" id="vsellNote">
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

    function filter(el, onfilter) {
        $("#loading").css("display", "block");

        if (onfilter) {
            fromP = 0;
        }

        if (el) {
            if (!el.value || el.value == null || el.value == "") {
                delete data[el.name];
            } else {
                //data.push([el.id] = el.value;
                data[el.name] = {"filter": el.id, "value": el.value};
            }
        }

        console.log(data);
        var data_encode = base64_encode(json_encode(data));
        if (data) {
            send_data = "&encodedData=" + data_encode;
        } else {
            send_data = "";
        }
        var userFriendly = "class=\"active\"";
        var first = false;


        var getUrl = "<?=$rootF?>/data.php?cmd=data&page=" + data_type + send_data + "&paginator=" + fromP + ":" + toP;

        var update_page = true;

        try {
            if (el.getAttribute("att") != null && el.getAttribute("att") == "excel") {



                var checked_input = $('#printyfy .dropdown-menu').find('input:checked');
                var excel_col_arr = [];

                checked_input.each(function () {
                    var sel_id = $(this)[0].id;

                    excel_col_arr.push(sel_id);
                });


                console.log(excel_col_arr);


                var excel_col_encode = base64_encode(json_encode(excel_col_arr));
                var excel_col_send_data;
                if (excel_col_arr) {
                    excel_col_send_data = "&excelColEncodedData=" + excel_col_encode;
                } else {
                    excel_col_send_data = "";
                }



                getUrl += "&excel=1&"+excel_col_send_data;
                update_page = false;
            }
        } catch (ex) {

        }

        if (update_page) {
            $.get(getUrl, function (get_data) {


                var tableData = get_data.data;
                var countP = get_data.count;
                var other_data = get_data.other;
                fromP = buildPaginator(countP, fromP, toP);
                sum_overall.total = 0;
                sum_overall.spend = 0;
                sum_overall.percent = 0;
                sum_overall.left_over = 0;
                window.costage = 0;
                var htmlData = "";
                var showA = "";
                if (countP > 0) {


                    var total_price_USD = 0;
                    var total_price_EUR = 0;
                    var total_price_RUB = 0;
                    var total_price_IRR = 0;
                    var total_price_GBP = 0;
                    var total_price_AMD = 0;

                    var total_price_NET_USD = 0;
                    var total_price_NET_EUR = 0;
                    var total_price_NET_RUB = 0;
                    var total_price_NET_IRR = 0;
                    var total_price_NET_GBP = 0;
                    var total_price_NET_AMD = 0;


                    var total_price_REV_AMD = 0;


                    for (var i = 0; i < tableData.length; i++) {
                        if (first) {
                            showA = userFriendly;
                            first = false;
                        } else {
                            showA = "";
                            first = true;
                        }
                        var d = tableData[i];
                        var co = 0;
                        var transfer_button = '';
                        if (d.travel_Services == "3" || d.travel_Services == "4" || d.travel_Services == "6" || d.travel_Services == "8") {
                            if (!d.travel_transfered || d.travel_transfered == 0) {
                                transfer_button = "<br/><a href=\"JavaScript:void(0);\" style=\"color:orange;\"  onclick=\"transferPopup(" + d.id + ")\">>DIMAVORUM</a>";
                            } else if (d.travel_transfered == 2) {
                                transfer_button = "<br/><a href=\"JavaScript:void(0);\" style=\"color:red;\" onclick=\"transferPopup(" + d.id + ")\">CHANAPARHUM?</a>";
                            } else if (d.travel_transfered == 1) {
                                transfer_button = "<br/><a href=\"JavaScript:void(0);\" style=\"color:green;\" onclick=\"transferPopup(" + d.id + ")\">TOUR / CHANAPARHUM</a>";
                            } else {
                                transfer_button = "<br/><a href=\"JavaScript:void(0);\" style=\"color:gray;\">Gnacela</a>";
                            }
                        }
                        if (typeof partner_names[d.travel_partneID] != 'undefined') {
                            d.travel_partneID = partner_names[d.travel_partneID];
                        } else {
                            d.travel_partneID = '';
                        }
                        var show_reminder = '';
                        if (other_data['alert']) {
                            if (other_data['alert'][d.id]) {
                                show_reminder = "<br><img height=\"40px\" src=\"<?=$rootF;?>/template/icons/important/important.gif\" alt=\"PENDING\" title=\"PENDING\">";
                            }
                        }
                        d.travel_price = number_format(d.travel_price, '0', ',', '');
                        if (d.travel_price > 0) {

                            var $price_differ = 0;
                            var $total_price = order_currency.convert(d.travel_currency, d.travel_price);

                            if (d.sell_point == 16) {
                                var $price_differ = ($total_price * 15) / 100;
                                $total_price = $total_price - $price_differ;
                            } else if (d.sell_point == 15) {
                                var $price_differ = ($total_price * 25) / 100;
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

                            costage = "<br/>" + number_format($total_price, '0', ',', '.');// + " / " + number_format(d.pNetcost, '0', ',', '.') + " / " + $percent + " ";
                        }
                        costage = "";//+costage+ "<hr title=\""+number_format(sum_overall.total, '0', ',', '.')+"/"+number_format(sum_overall.spend, '0', ',', '.')+"/"+number_format(sum_overall.percent, '0', ',', '.')+"\"/>"
                        htmlData += "<tr " + showA + "   id= \"ext_tr_id_" + d.id + "\"  ext_status = \"" + d.travel_status + "\" >";

                        var traveldate_cm = "";

                        try {
                            traveldate_cm = d.travel_date.slice(0, -3);
                        } catch (ex) {
                            traveldate_cm = d.travel_date;
                        }

                        htmlData += "<td style=\"width:140px;\" class=\"hiddenforprint p_id_block\"><a class=\"hiddenforprint p_id\" href=\"#\" onclick=\"addEditData(2," + d.id + ");return false;\">NUM-" + d.id + "</a><span class=\"hiddenforprint\">" + transfer_button + show_reminder + "</span><span class=\"hiddenforprint p_deal_date\"><br> " + convertDateTime(traveldate_cm) + "</span></td>";
                        htmlData += "<td style=\"min-width:50px;\"><img src=\"<?=$rootF;?>/template/images/status/" + d.travel_status + ".png\"/></td>";

                        htmlData += "<td class=\"hiddenforprint p_airline\">";

                        var arraivleDate = "";
                        var departureDate = "";


                        try {
                            arraivleDate = convertDateTime(d.travel_arraival_date.slice(0, -3));
                        } catch (ex) {

                        }

                        try {
                            departureDate = convertDateTime(d.travel_departure_date.slice(0, -3));
                        } catch (ex) {

                        }
                        try {
                            htmlData += "<b>" + d.travel_airline_name + "</b>"
                                + "<br>"
                                + "<span class='p_arrival_date'>A: " + arraivleDate + "</span>"
                                + "<br> "
                                + "<span class='p_departure_date'>D:  " + departureDate + "</span>"
                            ;
                        } catch (ex) {

                        }
                        htmlData += "</td>";


                        htmlData += "<td >" + d.travel_partneID
                            + " " + urldecode(html_entity_decode(d.travel_customerName))
                            + "<span  class=\"hiddenforprint  p_partner_order_id\" >"
                            + (d.travel_prt_order_id ? "(" + d.travel_prt_order_id + ")" : '')
                            + "</span>"
                            + "<span  class=\"p_guests\" >"
                            + " <br> Country : " + countryType[d.travel_country]
                            // + "</span>"

                            // + "<span  class=\" p_guests\" >"
                            + "<br> " + (d.travel_guests != null ? d.travel_guests : '')
                            + "</span>"
                            + "</td>";


                        //// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>

                        if (d.travel_total_income != null && d.travel_total_income.indexOf("AMD") !== -1) {

                            var inc_ctm = d.travel_total_income.split("AMD");
                            var inc_ctm_str = inc_ctm[0];
                            inc_ctm_str = inc_ctm_str.trim();
                            if (parseFloat(inc_ctm_str) > 0) {
                                total_price_REV_AMD = parseFloat(total_price_REV_AMD) + parseFloat(inc_ctm_str);
                            }


                        }


                        if (d.travel_CurrencyOfPrice == 1) {
                            total_price_NET_USD = parseFloat(total_price_NET_USD) + parseFloat((parseFloat(Math.round(d.travel_revenue * 100) / 100).toFixed(2)))
                        }
                        if (d.travel_currency == 1) {
                            total_price_USD = parseFloat(total_price_USD) + parseFloat((parseFloat(Math.round(d.travel_price * 100) / 100).toFixed(2)));
                        }


                        if (d.travel_currency == 2) {
                            total_price_RUB = parseFloat(total_price_RUB) + parseFloat((parseFloat(Math.round(d.travel_price * 100) / 100).toFixed(2)));
                        }
                        if (d.travel_CurrencyOfPrice == 2) {
                            total_price_NET_RUB = parseFloat(total_price_NET_RUB) + parseFloat((parseFloat(Math.round(d.travel_revenue * 100) / 100).toFixed(2)))
                        }

                        if (d.travel_currency == 3) {
                            total_price_AMD = parseFloat(total_price_AMD) + parseFloat((parseFloat(Math.round(d.travel_price * 100) / 100).toFixed(2)));
                        }
                        if (d.travel_CurrencyOfPrice == 3) {
                            total_price_NET_AMD = parseFloat(total_price_NET_AMD) + parseFloat((parseFloat(Math.round(d.travel_revenue * 100) / 100).toFixed(2)))
                        }


                        if (d.travel_currency == 4) {
                            total_price_EUR = parseFloat(total_price_EUR) + parseFloat((parseFloat(Math.round(d.travel_price * 100) / 100).toFixed(2)));
                        }
                        if (d.travel_CurrencyOfPrice == 4) {
                            total_price_NET_EUR = parseFloat(total_price_NET_EUR) + parseFloat((parseFloat(Math.round(d.travel_revenue * 100) / 100).toFixed(2)))
                        }


                        if (d.travel_currency == 5) {
                            total_price_GBP = parseFloat(total_price_GBP) + parseFloat((parseFloat(Math.round(d.travel_price * 100) / 100).toFixed(2)));
                        }
                        if (d.travel_CurrencyOfPrice == 5) {
                            total_price_NET_GBP = parseFloat(total_price_NET_GBP) + parseFloat((parseFloat(Math.round(d.travel_revenue * 100) / 100).toFixed(2)))
                        }

                        if (d.travel_currency == 6) {
                            total_price_IRR = parseFloat(total_price_IRR) + parseFloat((parseFloat(Math.round(d.travel_price * 100) / 100).toFixed(2)));
                        }
                        if (d.travel_CurrencyOfPrice == 6) {
                            total_price_NET_IRR = parseFloat(total_price_NET_IRR) + parseFloat((parseFloat(Math.round(d.travel_revenue * 100) / 100).toFixed(2)))
                        }

console.log(d)
                        htmlData += "<td class=\"hiddenforprint p_price_block\"><span  class=\"hiddenforprint p_price\" > P : "
                            + (parseFloat(Math.round(d.travel_price * 100) / 100).toFixed(2)) + " " + getCurrencyText(d.travel_currency);


                        if (d.travel_status == 4) {
                            htmlData += "<img src=\"../../template/images/status/4.png\" style=\"margin-left: 10px ; width : 15px \">";
                        } else if (d.travel_partial_pay > 0) {
                            htmlData += "<img src=\"../../template/images/status/5.png\" style=\"margin-left: 10px ; width : 15px \">";
                        } else {
                            htmlData += "<img src=\"../../template/images/status/2.png\" style=\"margin-left: 10px ; width : 15px \">";
                        }
                        htmlData += "</span>";


                        htmlData += "<br><span  class=\"hiddenforprint p_net\" > N : "
                            + (parseFloat(Math.round(d.travel_revenue * 100) / 100).toFixed(2)) + " " + getCurrencyText(d.travel_CurrencyOfPrice);

                        // htmlData += "</span>"

                        // htmlData += "<span  class=\"hiddenforprint p_we_paid\" >"

                        if (d.travel_WePaid == 1) {
                            htmlData += "<img src=\"../../template/images/status/4.png\" style=\"margin-left: 10px ; width : 15px \">";
                        } else {
                            htmlData += "<img src=\"../../template/images/status/2.png\" style=\"margin-left: 10px ; width : 15px \">";
                        }

                        htmlData += "</span>"


                        htmlData += "<br><span  class=\"hiddenforprint p_partial_paid \" > PP : " + (parseFloat(Math.round(d.travel_partial_pay * 100) / 100).toFixed(2)) + " " + getCurrencyText(d.travel_CurrencyPartialPaied);
                        htmlData += "</span>";
                        +"</td>";


                        htmlData += "<br><span  class=\"hiddenforprint\" > R : " + d.travel_total_income
                        htmlData += "</span>";
                        +"</td>";

                        /// >>>>>>>>>>>>>>>>>>
                        // <img src="../../template/images/status/4.png">


                        htmlData += "<td class=\"hiddenforprint\">" + urldecode(html_entity_decode(d.travel_customerPhone))
                            + "<br>"
                            + urldecode(html_entity_decode(d.travel_customerEmail))
                            + "</td>";


                        htmlData += "<td class=\"p_hotel_block\" style=\"width : 220px\">";
                        var update_button_date = '';

                        try {

                            var hotl_list_array = d.travel_hotel_name.split("<hr>");
                            var hotl_list_confirm = d.travel_hotel_confirmations.split(" ");


                            if (d.date_last_update != null) {
                                update_button_date += '<span class="hiddenforprint"><button type="button" data-id="' + d.travel_uniq + '" class="open_edited_popup">Edited</button><br></span>';
                                update_button_date += convertDateTime(d.date_last_update);
                            }

                            var arrayLength = hotl_list_confirm.length;
                            for (var iii = 0; iii < arrayLength - 1; iii++) {
                                htmlData += "<table>";
                                htmlData += "<tr>";
                                htmlData += "<td valign=\"top\">";


                                var spl_hotel_list = hotl_list_array[iii].split('<br>');


                                var hotel_cin_date = spl_hotel_list[1].split('CIN -');
                                var hotel_cout_date = spl_hotel_list[2].split('COUT -');

                                htmlData += '<span class="p_hotels">' + spl_hotel_list[0] + '</span>';

                                htmlData += '<span class="p_check_in_date"><br>CIN - ' + convertDateTime(hotel_cin_date[1].trim()) + '</span>';

                                htmlData += '<span class="p_check_out_date"><br>COUT - ' + convertDateTime(hotel_cout_date[1].trim()) + '</span>';

// console.log(
//     spl_hotel_list[0]+'<span class="p_check_in_date"><br>'+convertDateTime(hotel_cin_date[1].trim())+'</sapn>'+'<span class="p_check_out_date"><br>'+convertDateTime(hotel_cout_date[1].trim())+'</sapn><br>'
// )

                                // htmlData += hotel_html;
                                htmlData += "</td>";


                                htmlData += "<td class=\"p_hotels\"  valign=\"top\">";
                                if (hotl_list_confirm[iii] == 1) {
                                    htmlData += "<img src=\"../../template/images/status/4.png\" style=\"margin-left: 10px ; width : 15px \">";
                                } else {
                                    htmlData += "<img src=\"../../template/images/status/2.png\" style=\"margin-left: 10px ; width : 15px \">";
                                }
                                htmlData += "</td>";


                                htmlData += "</tr>";
                                htmlData += "</table>";
                            }


                        } catch (ex) {


                        }
                        htmlData += "</td>";
                        htmlData += "<td class=\"hiddenforprint p_comments\">" + urldecode(html_entity_decode(d.travel_sellNote)) + "</td>";
                        htmlData += "<td class=\"hiddenforprint\">" + spointType[d.travel_sellpoint] + " <br> " + sourceType[d.travel_source] + "</td>";
                        htmlData += "<td class=\"hiddenforprint p_last_updated_date\">" + update_button_date + "</td>";
                        htmlData += "</tr>";
                    }
                    console.log()
                    htmlData += "<tr>";
                    htmlData += "<td class=\"hiddenforprint\" ></td>";
                    htmlData += "<td class=\"hiddenforprint\"></td>";
                    htmlData += "<td></td>";

                    htmlData += "<td></td>";

                    htmlData += "<td>Payment <br>";

                    if (total_price_USD > 0) {
                        htmlData += total_price_USD + " USD <br>";
                    }
                    if (total_price_RUB > 0) {
                        htmlData += total_price_RUB + " RUB <br>";
                    }

                    if (total_price_AMD > 0) {
                        htmlData += total_price_AMD + " AMD <br>";
                    }

                    if (total_price_EUR > 0) {
                        htmlData += total_price_EUR + " EUR <br>";
                    }

                    if (total_price_GBP > 0) {
                        htmlData += total_price_GBP + " GBP <br>";
                    }

                    if (total_price_IRR > 0) {
                        htmlData += total_price_IRR + " IRR <br>";
                    }

                    htmlData += "<span  class=\"hiddenforprint\" >";

                    htmlData += "<hr>";

                    htmlData += " Net<br>";


                    if (total_price_NET_USD > 0) {
                        htmlData += total_price_NET_USD + " USD <br>";
                    }

                    if (total_price_NET_RUB > 0) {
                        htmlData += total_price_NET_RUB + " RUB  <br>";
                    }
                    if (total_price_NET_AMD > 0) {
                        htmlData += total_price_NET_AMD + " AMD  <br>";
                    }

                    if (total_price_NET_EUR > 0) {
                        htmlData += total_price_NET_EUR + " EUR <br>";
                    }

                    if (total_price_NET_GBP > 0) {
                        htmlData += total_price_NET_GBP + " GBP <br>";
                    }

                    if (total_price_NET_IRR > 0) {
                        htmlData += total_price_NET_IRR + " GBP <br>";
                    }

                    htmlData += "<hr> INCOME <br>";
                    if (total_price_REV_AMD > 0) {
                        htmlData += total_price_REV_AMD + " AMD ";
                    }

                    htmlData += " </td>";

                    htmlData += "</span>";

                    htmlData += "<td class=\"hiddenforprint\"></td>";
                    htmlData += "<td ></td>";
                    htmlData += "<td class=\"hiddenforprint\"></td>";
                    htmlData += "<td class=\"hiddenforprint\"></td>";
                    htmlData += "</tr>";

                }
                $('#dataTable').html(htmlData);
                $("#loading").css("display", "none");


            });
        }
        if (!update_page) {
            $("#loading").css("display", "none");
            window.open(getUrl);
        }


        return false;
    }

    filter(null);
    $('#menuDrop .dropdown-menu').on({
        "click": function (e) {
            e.stopPropagation();
        }
    });

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

            console.log(field_arr.includes(p_class), sel_id, p_class, $(p_class))


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
        console.log(field_arr);
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
            console.log(get_data);


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

    function buildPaginator(tCount, pfrom, pto) {
        var htmlP = "";
        var pagesC = Math.ceil(tCount / pto);
        var vNum = 0;
        if (pagesC > 1) {
            for (var i = 0; i < pagesC; i++) {
                var pNum = i + 1;

                if (vNum == pfrom) {
                    htmlP += "<li class=\"active\"><a href=\"#\" onclick=\"return false;\">" + pNum + "</a></li>";
                } else {
                    htmlP += "<li ><a href=\"#\" onclick=\"loadData(" + vNum + "," + pto + ");return false;\">" + pNum + "</a></li>";
                }
                vNum = pto + vNum;
            }
        }
        $("#buildPages").html(htmlP);
        return vNum;
    }

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

    $('#check_in').datetimepicker({"format": "YYYY-MM-DD 14:00"});
    $('#check_out').datetimepicker({"format": "YYYY-MM-DD 12:00"}).attr("disabled", "disabled");

    $("#check_in").on("dp.change", function (e) {
        $('#check_out').data("DateTimePicker").minDate(e.date);
        $('#check_out').removeAttr("disabled");
    });

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

    function addEditData(add, itemId, closedisabled) {

        $("#vWePaid").prop('checked', false);

        $("#applyButton").addClass('disabled');

        $("#id_mod_order_id").text("NUM-" + itemId + " / " + geStatusName($("#ext_tr_id_" + itemId).attr("ext_status")));

        $("#vInput").addClass('disabled');
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

        for (var i = 0; i < d.length; i++) {
            if (d[i] && d[i].val()) {
                command += d[i].attr("id") + "=" + d[i].val().replace(/\n/g, '\\n') + "&";
            } else if (!d[i].val()) {
                command += d[i].attr("id") + "=&";
            }
        }
        command = command.substring(0, command.length - 1);

        if (add == 1) {
            $("#actionStatus").css("display", "none");
            $("#vform").css("display", "block");

            $.get("<?=$rootF;?>/data.php?cmd=addData&" + command + "&hotel_data=" + base64_encode(JSON.stringify(hotelListObject)), function (data) {
                if (data.data.ok) {
                    $("#vform").css("display", "none");
                    filter(null, true);
                    $('#tlAdd').modal('hide');
                }
            });
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
            getTotalProfit();

            var get_command = "<?=$rootF;?>/data.php?cmd=editData&itemId=" + itemId + "&" + command + "&hotel_data=" + base64_encode(JSON.stringify(hotelListObject));


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
                if (data.data.ok) {
                    filter(null, true);
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
                                    addRoomList(id, data.data[key][nkey][i].hotel_room_id, data.data[key][nkey][i].room_count, data.data[key][nkey][i].room_price, data.data[key][nkey][i].extra_count, data.data[key][nkey][i].extra_price, true);
                                }
                            } else if (nkey == 'extra') {
                                for (var i = 0; i < data.data[key][nkey].length; i++) {
                                    addRoomExtraList(id, data.data[key][nkey][i].order_extra_id, data.data[key][nkey][i].order_extra_count, data.data[key][nkey][i].order_extra_price, true);
                                }
                            }

                        }
                        addToHotelList(key, data.data[key].global);
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
        roomObject[jQuery("#travel_data_id").val()][roomType.val()] = [roomCount.val(), roomPrice.val(), roomExtraBed.val(), roomExtraBedPrice.val()];
        if (roomPrice.val() < 1) {
            roomPrice.val(0.000);
        }
        if (roomExtraBedPrice.val() < 1) {
            roomExtraBedPrice.val(0.000);
        }
        var html_set = '<li class="hotel_room_list_item" data-roomType="' + jQuery("#travel_data_id").val() + roomType.val() + '">';
        html_set += '<span class="hotel_room_name">' + roomType.text() + '&nbsp;</span>';
        html_set += '<span class="hotel_room_count">(<strong>' + roomCount.val() + '</strong> room(s))&nbsp;</span>';
        html_set += '<span class="hotel_room_price">Price:<strong style="color:green;">' + roomPrice.val() + '</strong>&nbsp;</span>';
        html_set += '<span class="hotel_room_extended">Extra Bed(<strong style="color:orange;">' + roomExtraBed.val() + '</strong>)&nbsp;</span>';
        html_set += '<span class="hotel_room_extended_price">Price:<strong style="color:orange;">' + roomExtraBedPrice.val() + '</strong>&nbsp;</span>';
        html_set += "<span class=\"hotel_room_action\"><div onclick=\"removeThisRoom('" + roomType.val() + "');return false;\" class=\"remove_button\">x</div>&nbsp;</span>";
        html_set += '</li>';
        jQuery("#hotel_room_list > ol").append(html_set);
        html_set = "";
    }

    function addRoomList(itemId, roomType, roomCount, roomPrice, roomExtraBed, roomExtraBedPrice, objectInsert) {
        if (!roomObject[itemId]) {
            roomObject[itemId] = {};
        }
        if (!objectInsert) {
            objectInsert = false;
        }
        roomObject[itemId][roomType] = [roomCount, roomPrice, roomExtraBed, roomExtraBedPrice];
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
        if (($("#hotel_id :selected").val() || hotel_info) && !jQuery.isEmptyObject(roomObject)) {
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
            //console.log(hotelListObject,2);
            //resset fields
            $("#hotel_id").val("");
            $("#check_in").val("");
            $("#check_out").val("");
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
            //console.log(JSON.stringify(hotelListObject));
            hotels_count++;
        }
    }

    function addHotelList(travelId, itemId, hotel_object, getRoomObject, getExtraObject, hotel_info) {

        getRoomObject = JSON.parse(getRoomObject);
        getExtraObject = JSON.parse(getExtraObject);
        var html_set = "";

        html_set += '<table style="width:100%" tblid="' + travelId + itemId + '">';
        html_set += '<tr>';
        html_set += '<td valign="top">';
        html_set += '<li class="hotel_list_item" data-hotelList="' + travelId + itemId + '" style="position: relative;">';
        html_set += '<strong>' + hotel_object.name + '</strong>  ';


        // class="p_check_in_date"
        // class="p_check_out_date"


        html_set += '<strong>/ CHECK IN:</strong> ' + hotel_object.checkin + "; ";
        html_set += '<strong>/ CHECK OUT:</strong> ' + hotel_object.checkout + ";</br></br>";

        html_set += '<strong>ADULT:</strong> ' + hotel_object.adult + "; ";
        html_set += '<strong> / ADULT PRICE:</strong> ' + hotel_object.adult_price + ";";
        html_set += '<strong> / CHILD:</strong> ' + hotel_object.child + "; ";
        html_set += '<strong> / CHILD PRICE:</strong> ' + hotel_object.child_price + ";";

        if (getRoomObject[travelId]) {
            html_set += '</br></br><strong>ROOMS:</strong></br>';
            for (var key in getRoomObject[travelId]) {
                if (getRoomObject[travelId].hasOwnProperty(key)) {
                    var rooms = (getRoomObject[travelId][key][0] > 1) ? 'rooms' : 'room';
                    html_set += '<span class="hotel_room_name">' + $("#hotel_room_type option[value='" + key + "']").text() + '&nbsp;</span>';
                    html_set += '<span class="hotel_room_count">(<strong>' + getRoomObject[travelId][key][0] + '</strong> ' + rooms + ')&nbsp;</span>';
                    html_set += '<span class="hotel_room_price">Price:<strong style="color:green;">' + getRoomObject[travelId][key][1] + '</strong>&nbsp;</span>';
                    html_set += '<span class="hotel_room_extended">Extra Bed(<strong style="color:orange;">' + getRoomObject[travelId][key][2] + '</strong>)&nbsp;</span>';
                    html_set += '<span class="hotel_room_extended_price">Price:<strong style="color:orange;">' + getRoomObject[travelId][key][3] + '</strong>&nbsp;</span></br>';
                    removeThisRoom(key, true);
                }
            }
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
            html_set += "<span class=\"input-group-addon\" style=\" text-align:  left\">"
                + "<input  type=\"checkbox\" id=\"vConfirmHotel[" + hotel_object["booking_id"] + "]\" onchange='activateSaveOption()' confirmhotel>Confirmed <br>"
                + "<button id=\"remind_hotel\">Remind Hotel</button></span>";
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
        $.get("<?=$rootF;?>/data.php?cmd=getTravelPartnerData&pid=" + $(this).val(), function (data) {
            if (data.data.pd) {
                $("#vcustomerPhone").val(data.data.pd.phone);
                $("#vcustomerEmail").val(data.data.pd.email);

            } else if (data.error) {
                console.error(data.error);
            }
        });
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