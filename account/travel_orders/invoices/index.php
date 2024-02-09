<?php
session_start();
$pageName = "travel";
$rootF = "../../..";
include($rootF."/apay/pay.api.php");
include($rootF."/apay/travel.api.php");
include($rootF."/configuration.php");
$access = auth::checkUserAccess($secureKey);
$allData = array();
$buildClient = "";
$uid = "";
$level = "";
$userData = "";
$cc = "en";
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
if(is_file("local/{$cc}.php"))
{
	include("local/{$cc}.php");	
}else{
	include("local/en.php");
}

$getConstants = get_defined_constants();
$travel_data = (isset($_REQUEST['travel_id'])) ? travelData::getBaseDataById($_REQUEST['travel_id']) : false;

if($travel_data){
	$travel_data = $travel_data[0];
}
$total_sum_hotels = array();
$hotel_booking_data = (isset($_REQUEST['travel_id'])) ? getwayConnect::getwayData("SELECT DHB.*, DH.`name` AS `hotel_name`, DH.`address` AS `hotel_address` 
                                                                     FROM
                                                                    `travel_hotel_relation`  
                                                                    AS THR LEFT JOIN `data_hotel_booking` AS DHB  
                                                                    ON DHB.`id` = THR.`hotel_booking_id` 
                                                                    LEFT JOIN `data_hotels` AS DH  
                                                                    ON DH.`id` = DHB.`hotel_id` 
                                                                    WHERE THR.`travel_id` = '{$_REQUEST['travel_id']}' 
                                                                    ORDER BY DHB.`check_in` ASC",PDO::FETCH_ASSOC) : '';

$data_currency =  (isset($travel_data["travel_currency"])) ? getwayConnect::getwayData("SELECT * FROM `currency` WHERE `id` = '{$travel_data["travel_currency"]}'",PDO::FETCH_ASSOC) : '';
//$data_hotels =  (isset($travel_data["hotel_id"]) && $travel_data["hotel_id"] > 0) ? getwayConnect::getwayData("SELECT * FROM data_hotels WHERE `id` = '{$travel_data["hotel_id"]}'",PDO::FETCH_ASSOC) : array('');
$data_payments =  (isset($travel_data["hotel_id"])) ? getwayConnect::getwayData("SELECT * FROM data_payment WHERE `id` = '{$travel_data["travel_payment"]}'",PDO::FETCH_ASSOC) : '';


//rooms must be by booking id
$data_rooms = (isset($travel_data["hotel_booking_id"]) && $travel_data["hotel_booking_id"] > 0) ? getwayConnect::getwayData("SELECT * FROM `hotel_room_relation` WHERE `hotel_booking_id` = '{$travel_data["hotel_booking_id"]}'",PDO::FETCH_ASSOC) : false;

$data_rooms_types = getwayConnect::getwayData("SELECT * FROM `data_hotel_room_type`",PDO::FETCH_ASSOC);


$data_extra_types = getwayConnect::getwayData("SELECT * FROM `hotel_order_extra` ORDER BY `ordering`",PDO::FETCH_ASSOC);
//extras must be by booking id
$data_extra_f = (isset($travel_data["hotel_booking_id"]) && $travel_data["hotel_booking_id"] > 0) ? getwayConnect::getwayData("SELECT * FROM `hotel_extra_relation` WHERE `hotel_booking_id` = '{$travel_data["hotel_booking_id"]}'",PDO::FETCH_ASSOC) : false;
$hotel_rooms = '';
$hotel_extras = '';
$hotel_room_price_total = 0.000;
$hotel_extra_prices = 0.000;
$genInv = '{travel_document_id}';
$paper_type = '';
$s_message = 'Error sending';
if(isset($_REQUEST['printType']) && $_REQUEST['printType'] == 1){
	$paper_type = (isset($getConstants['invoice'])) ? invoice : "{invoice}";
}else{
	$paper_type = (isset($getConstants['voucher'])) ? voucher : "{voucher}";
}
if(isset($travel_data['id'])){
	$alpha = range("A","Z");
	$tid = str_split($travel_data['id']);
	$genInv = count($tid);
	foreach($tid as $value){
		$genInv .= $value.$alpha[$value];
	}
	$truniq = str_split(md5($travel_data['travel_uniq']));
	$genInv .= strtoupper($truniq[0]);
}
if(isset($_REQUEST['actionType']) && intval($_REQUEST['actionType']) == 2){

	$email = (isset($travel_data['travel_customerEmail'])) ? trim($travel_data['travel_customerEmail']) : false;
	$to = $email;
	//$to = "info@flowers-armenia.com";
	$subject = "Regard Travel  {$paper_type} {$genInv}";

	$headers = "From: info@regardtravel.com\r\n";
	$headers .= "Reply-To: info@regardtravel.com\r\n";
	//$headers .= "CC: susan@example.com\r\n";
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
	$data_link = urldecode("http://regard-group.ru/account/travel_orders/invoices/?printType={$_REQUEST["printType"]}&country_code={$_REQUEST['country_code']}&actionType=1&i=1&travel_id={$travel_data["id"]}");
	$agent= 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_VERBOSE, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, $agent);
	curl_setopt($ch, CURLOPT_URL,$data_link);
	$result=curl_exec($ch);
	$message = $result;
	if($message){
		if(mail($to, $subject, $message, $headers)){
			$s_message = "Mail has been Send!";
			getwayConnect::getwaySend("UPDATE `data_travel` SET `send_mail_count` = `send_mail_count`+1 WHERE id = '{$travel_data['id']}'");
		}
	}
	exit("<script>alert(\"{$s_message}\");window.location =\"../\"</script>");
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		
		<style>body{margin:0;padding:25px;}p{margin:0;} </style>
	</head>
	<body>
	<?php
	if(isset($_REQUEST['invid']) && isset($_REQUEST['i']) && $_REQUEST['i'] == "start"){
		$alpha = range("A","Z");
		$tid = str_split($_REQUEST['invid']);
		$genInv = count($tid);
		foreach($tid as $value){
			shuffle($alpha);
			$genInv .= $value.$alpha[0];
		}
		$genInv .=rand(0,9);
		$_SESSION['RESID'] = $genInv;
	?>
	<hr />
	<p style="text-align: center;">PRINT NUMBER:&nbsp;<strong><?=$genInv?></strong></p>
	<form action="" method="GET" name="print" id="print">
		<ol>
			<li>
				<label for="printType1">INVOICE:<input name="printType" id="printType1" type="radio" value="1" checked></label>
				<label for="printType2">VOUCHER:<input name="printType" id="printType2" type="radio" value="2"></label>
			</li>
			<li>
				<select name="country_code" id="country_code">
					<option value="en">EN</option>
					<option value="am">AM</option>
					<option value="ru">RU</option>
					<option value="ir">IR</option>
				</select>
			</li>
			<li>
				<label for="actionType1">PRINT:<input name="actionType" id="actionType1" type="radio" value="1" checked></label>
				<label for="actionType2">SENDM:<input name="actionType" id="actionType2" type="radio" value="2">
			</li>
			<li>
				<input type="hidden" name="i" value="1">
				<input type="hidden" name="travel_id" value="<?=$_REQUEST['invid'];?>">
				<button>CONFIRM</button>
			</li>
		</ol>
	</form>
	<hr />
	<?php
	}
	if((isset($_REQUEST['i']) && $_REQUEST['i'] == 1) || !isset($_REQUEST['i'])){
	?>
		<p align="left" style="margin:0;display: inline-block;">
			<img align="bottom" border="0" height="100" src="htmlimage.jpg"/>
		</p>
		<p align="right" style="margin:0;display: inline-block;float:right;">
			<font face="Tahoma, serif">
				<font size="3" style="font-size:12pt;">
					<span lang="en-US" xml:lang="en-US">
						<i>
							<b>
								<span style="">
									<br />
								</span>
							</b>
						</i>
					</span>
				</font>
			</font>
			<font color="#4f6228">
				<font face="Tahoma, serif">
					<span lang="en-US" xml:lang="en-US">
						<i>
						</i>
					</span>
				</font>
			</font>
			<font color="#4f6228">
				<font face="Cambria, serif">
					<span lang="en-US" xml:lang="en-US">
						<i>
							<b><?=(isset($getConstants['comapny'])) ? compnay : "Regard Group LLC";?><br /> <?=(isset($getConstants['address'])) ? address : "Address:";?> <?=(isset($getConstants['address_text'])) ? address_text : "{address_text}";?><br />
							</b>
						</i>
					</span>
				</font>
			</font><a>
				<font color="#4f6228">
					<font face="Cambria, serif">
						<span lang="en-US" xml:lang="en-US">
							<i>
								<u>
									<b><?=(isset($getConstants['telephone'])) ? telephone : "{telephone}";?></b>
								</u>
							</i>
						</span>
					</font>
				</font>
			</a>
			<font color="#4f6228">
				<font face="Cambria, serif">
					<span lang="en-US" xml:lang="en-US">
						<i>
							<b><?=(isset($getConstants['telephone_text'])) ? telephone_text : "{telephone_text}";?>
							<!--,<?=(isset($getConstants['mobile'])) ? mobile : "{mobile}";?><?=(isset($getConstants['mobile_text'])) ? mobile_text : "{mobile_text}";?>--></b>
						</i>
					</span>
				</font>
			</font>
			<font color="#4f6228">
				<font face="Tahoma, serif">
					<span lang="en-US" xml:lang="en-US">
						<i>
							<b>
								<br />
							</b>
						</i>
					</span>
				</font>
			</font>
			<font color="#4f6228">
				<font face="Cambria, serif">
					<span lang="en-US" xml:lang="en-US">
						<i>
							<b><?=(isset($getConstants['email'])) ? email : "{email}";?> <?=(isset($getConstants['email_text'])) ? email_text : "{email_text}";?><br />
							</b>
						</i>
					</span>
				</font>
			</font>
			<font color="#4f6228">
				<font face="Cambria, serif">
					<span lang="en-US" xml:lang="en-US">
						<i>
							<b>
		 <?=(isset($getConstants['url'])) ? url : "{url}";?>
		 <a href="www.RegardTravel.com" target="_blank"><?=(isset($getConstants['url_text'])) ? url_text : "{url_text}";?></a>
							</b>
						</i>
					</span>
				</font>
			</font>
		</p>
		<p style="clear: both;"></p>
		<p><br></p><p><br></p>
		<p align="center" style="margin-bottom:0in;">
			<font color="#4f6228">
				<font face="Cambria, serif">
					<font size="4" style="font-size:14pt;">
						<span lang="en-US" xml:lang="en-US">
							<i>
								<b><?=(isset($getConstants['title'])) ? title : "{title}";?></b>
							</i>
						</span>
					</font>
				</font>
			</font>
			<font color="#a40000">
				<font face="Cambria, serif">
					<font size="4" style="font-size:14pt;">
						<span lang="en-US" xml:lang="en-US">
							<i>
								<b>
									<br />
								</b>
							</i>
						</span>
					</font>
				</font>
			</font>
			<br />
		<p align="justify" style="margin-bottom:0in;">
			<font color="#0a4a19">
				<font face="Cambria, serif">
					<font size="3" style="font-size:12pt;">
						<span lang="en-US" xml:lang="en-US">
							<i>
								<b><?=(isset($getConstants['telephone_short'])) ? telephone_short : "{telephone_short}";?></b>
							</i>
						</span>
					</font>
				</font>
			</font>
			<font face="Cambria, serif">
				<font size="3" style="font-size:12pt;">
					<span lang="en-US" xml:lang="en-US">
						<i>
							<b><?=(isset($getConstants['telephone_short_text'])) ? telephone_short_text : "{telephone_short_text}";?></b>
						</i>
					</span>
				</font>
			</font>
		</p>
		<p align="justify" style="margin-bottom:0in;">
			<font color="#4f6228">
				<font face="Cambria, serif">
					<font size="3" style="font-size:12pt;">
						<span lang="en-US" xml:lang="en-US">
							<i>
								<b><?=(isset($getConstants['from'])) ? from : "{from}";?>
								</b>
							</i>
						</span>
					</font>
				</font>
			</font>
			<font face="Cambria, serif">
				<font size="3" style="font-size:12pt;">
					<span lang="en-US" xml:lang="en-US">
						<i>
							<b><?=(isset($getConstants['from_text'])) ? from_text : "{from_text}";?></b>
						</i>
					</span>
				</font>
			</font>
		</p>
		<p align="justify" style="margin-bottom:0in;">
			<font color="#4f6228">
				<font face="Cambria, serif">
					<font size="3" style="font-size:12pt;">
						<span lang="en-US" xml:lang="en-US">
							<i>
								<b><?=(isset($getConstants['to'])) ? to : "{to}";?></b>
							</i>
						</span>
					</font>
				</font>
			</font>
			<font face="Cambria, serif">
				<font size="3" style="font-size:12pt;">
					<span lang="en-US" xml:lang="en-US">
						<i>
							<b>
							<?php
							if(is_array($hotel_booking_data)){
								$hotel_names = [];
								foreach ($hotel_booking_data as $value){
									$hotel_names[] = $value['hotel_name'];
								}
								echo implode(" , ",$hotel_names);
							}else{
								echo "N/A";
							}
							?>
							</b>
						</i>
					</span>
				</font>
			</font>
		</p>
		<p><br></p><p><br></p>
		<p align="center" style="margin-bottom:0in;">
			<a name="_GoBack" id="_GoBack"/>
			<font color="#4f6228">
				<font face="Cambria, serif">
					<font size="4" style="font-size:14pt;">
						<span lang="en-US" xml:lang="en-US">
							<i>
								<b>
								<?=$paper_type?> <?=$genInv?></b>
							</i>
						</span>
					</font>
				</font>
			</font>
		</p>
		<p><br></p>
		<p align="justify" style="margin-bottom:0in;">
			</br>
		</p>
		<p style="margin-bottom:0.14in;">
			<font color="#4f6228">
				<font face="Cambria, serif">
					<font size="3" style="font-size:12pt;">
						<span lang="en-US" xml:lang="en-US">
							<i>
								<b><?=(isset($getConstants['guests'])) ? guests : "{guests}";?></b>
							</i>
						</span>
					</font>
				</font>
			</font>
			<font face="Arial AMU, serif">
				<font size="4" style="font-size:14pt;">
					<span lang="en-US" xml:lang="en-US">
						<i>
							<b><br/><?=isset($travel_data["travel_guests"]) ? nl2br($travel_data["travel_guests"]) : "N/A";?><br />
							</b>
						</i>
					</span>
				</font>
			</font>
		</p>
		<!--start hotel loops-->
		<?php
		if(is_array($hotel_booking_data)){
			$show_hotel_name = false;
			if(count($hotel_booking_data) > 2){
				$show_hotel_name = true;
			}
			foreach ($hotel_booking_data as$b_value) {
				$this_rooms = travelData::getHotelRooms($b_value['id']);
				$this_extras = travelData::getRoomExtra($b_value['id']);
				?>
				<?= ($show_hotel_name) ? "<br/><hr/><strong>( {$b_value['hotel_name']} )</strong>" : ''; ?>
				<p align="justify" style="margin-bottom:0in;">
					<font color="#4f6228">
						<font face="Cambria, serif">
							<font size="3" style="font-size:12pt;">
								<span lang="en-US" xml:lang="en-US">
									<i>
										<b><?= (isset($getConstants['guest_count'])) ? guest_count : "{guest_count}"; ?></b>
									</i>
								</span>
							</font>
						</font>
					</font>
					<font color="#4f6228">
						<font face="Cambria, serif">
							<font size="3" style="font-size:12pt;">
								<span lang="en-US" xml:lang="en-US">
									<i>
									</i>
								</span>
							</font>
						</font>
					</font>
					<font color="#000000">
						<font face="Cambria, serif">
							<font size="3" style="font-size:12pt;">
								<span lang="en-US" xml:lang="en-US">
									<i>
										<b>
											<?= (isset($b_value["adult_count"]) && $b_value["adult_count"] > 0) ? $b_value["adult_count"] . " Adult(s)" : ""; ?>
											<?= (isset($b_value["child_count"]) && $b_value["child_count"] > 0) ? $b_value["child_count"] . " Child(s)" : ""; ?>
										</b>
									</i>
								</span>
							</font>
						</font>
					</font>
				</p>

				<p align="justify" style="margin-bottom:0in;">
					<font color="#4f6228">
						<font face="Cambria, serif">
							<font size="3" style="font-size:12pt;">
								<span lang="en-US" xml:lang="en-US">
									<i>
										<b><?= (isset($getConstants['room_types'])) ? room_types : "{room_types}"; ?></b>
									</i>
								</span>
							</font>
						</font>
					</font>
					<font face="Cambria, serif">
						<font size="3" style="font-size:12pt;">
							<span lang="en-US" xml:lang="en-US">
								<i>
									<b>
									<?php
									$data_rooms = $this_rooms;
									if ($data_rooms) {
										$room_text = "";
										foreach ($data_rooms as $key => $value) {
											$room_type_name = '';
											foreach ($data_rooms_types as $rkey => $rvalue) {
												if ($rvalue['id'] == $value['hotel_room_id']) {
													$room_type_name = $rvalue['name'];
													continue;
												}
											}
											$extra_bed = ($value['extra_count'] > 0) ? 'with ' . $value['extra_count'] . ' extra bed' : '';
											$room_text .= $value['room_count'] . " " . strtoupper($room_type_name) . " {$extra_bed},";
										}
										$room_text = trim($room_text, ",");
										echo $room_text;
									}
									?>
									</b>
								</i>
							</span>
						</font>
					</font>
				</p>
				<p align="justify" style="margin-bottom:0in;">
					<font color="#4f6228">
						<font face="Cambria, serif">
							<font size="3" style="font-size:12pt;">
								<span lang="en-US" xml:lang="en-US">
									<i>
										<b><?= (isset($getConstants['check_in'])) ? check_in : "{check_in}"; ?></b>
									</i>
								</span>
							</font>
						</font>
					</font>
					<?php
					$checkin_date = "N/A";
					$checkin_time = "N/A";
					if (isset($b_value["check_in"])) {
						$expl = explode(" ", $b_value["check_in"]);
						$checkin_date = $expl[0];
						$checkin_time = $expl[1];
					}
					?>
					<font face="Cambria, serif">
						<font size="3" style="font-size:12pt;">
							<span lang="en-US" xml:lang="en-US">
								<i>
									<b><?= $checkin_date ?></b>
								</i>
							</span>
						</font>
					</font>
					<font color="#ff0000">
						<font face="Cambria, serif">
							<font size="3" style="font-size:12pt;">
								<span lang="en-US" xml:lang="en-US">
									<i>
										<b><?= $checkin_time ?></b>
									</i>
								</span>
							</font>
						</font>
					</font>
				</p>

				<p align="justify" style="margin-bottom:0in;">
					<font color="#4f6228">
						<font face="Cambria, serif">
							<font size="3" style="font-size:12pt;">
								<span lang="en-US" xml:lang="en-US">
									<i>
										<b><?= (isset($getConstants['check_out'])) ? check_out : "{check_out}"; ?></b>
									</i>
								</span>
							</font>
						</font>
					</font>
					<?php

					$checkout_date = "N/A";
					$checkout_time = "N/A";
					if (isset($b_value["check_out"])) {
						$expl = explode(" ", $b_value["check_out"]);
						$checkout_date = $expl[0];
						$checkout_time = $expl[1];
					}
					?>
					<font face="Cambria, serif">
						<font size="3" style="font-size:12pt;">
							<span lang="en-US" xml:lang="en-US">
								<i>
									<b><?= $checkout_date ?></b>
								</i>
							</span>
						</font>
					</font>
					<font color="#ff0000">
						<font face="Cambria, serif">
							<font size="3" style="font-size:12pt;">
								<span lang="en-US" xml:lang="en-US">
									<i>
										<b><?= $checkout_time ?></b>
									</i>
								</span>
							</font>
						</font>
					</font>
				</p>
				<p><br></p>
				<p align="justify" style="margin-bottom:0in;">
					</br>
				</p>
				<p align="justify" style="margin-bottom:0in;">
					<font color="#4f6228">
						<font face="Cambria, serif">
							<font size="3" style="font-size:12pt;">
								<span lang="en-US" xml:lang="en-US">
									<i>
										<b><?= (isset($getConstants['extra_services'])) ? extra_services : "{extra_services}"; ?></b>
									</i>
								</span>
							</font>
						</font>
					</font>
				</p>

				<?php
				$checkout_value = 0;
				$checkin_value = 0;
				$days_left = 1;
				if (isset($b_value["check_out"]) && isset($b_value["check_in"])) {
					$checkout_value = strtotime($b_value["check_out"]);
					$checkin_value = strtotime($b_value["check_in"]);
					$days_left = $checkout_value - $checkin_value;
					$days_left = round($days_left / (60 * 60 * 24));
					$days_left = ($days_left <= 0) ? 1 : $days_left;
				}
				?>
				<p align="justify" style="margin-bottom:0in;">
					<font color="#000000">v <font face="Cambria, serif">
							<font size="3" style="font-size:12pt;">
								<span lang="en-US" xml:lang="en-US">
									<i>
										<b><?= ($days_left > 0) ? $days_left : "N/A" ?> <?= isset($getConstants["hotel_booking_info"]) ? $getConstants["hotel_booking_info"] : "night(s)  accommodation in"; ?>  <?= isset($b_value["hotel_name"]) ? $b_value["hotel_name"] : "N/A"; ?>
											(<?= isset($b_value["hotel_address"]) ? $b_value["hotel_address"] : "N/A"; ?>
											)
									</b>
								</i>
							</span>
							</font>
						</font>
				</p>

				<?php
				$data_extra_f = $this_extras;
				if ($data_extra_f) {

					foreach ($data_extra_f as $ekey => $evalue) {
						$extra_name = '';
						$additional_data = '';
						foreach ($data_extra_types as $xkey => $xvalue) {
							if ($xvalue['id'] == $evalue['order_extra_id']) {
								$extra_name = strtolower($xvalue['name']);
								continue;
							}
						}
						if ($extra_name == 'airport_transfer' && isset($_REQUEST['printType']) && $_REQUEST['printType'] == 2) {
							$additional_data = " (Lider tel number : Mrs. Shushan  +374 95 54 13 43)";
						}

						?>
						<p align="justify" style="margin-bottom:0in;">
							<font color="#000000">v
							</font>
							<font color="#000000">
								<font face="Cambria, serif">
									<font size="3" style="font-size:12pt;">
										<span lang="en-US" xml:lang="en-US">
											<i>
												<b>
													<span
														style=""><?= (isset($getConstants[$extra_name])) ? $getConstants[$extra_name] . " {$additional_data}" : "{extra}"; ?></span>
												</b>
											</i>
										</span>
									</font>
								</font>
							</font>
						</p>

						<?php
					}
				}

				?>

				<p align="justify" style="margin-bottom:0in;">
					</br>
				</p><p><br></p>
				<?php
				$all_room_voucher_text = '';
				$currency = (isset($data_currency[0]) && isset($data_currency[0]['name'])) ? $data_currency[0]['name'] : '';
				if (isset($_REQUEST['printType']) && $_REQUEST['printType'] == 1) {
					$total_room_price = 0.00;
					$total_extra_bed_price = 0.00;
					$room_type_name = '';
					$total_extra_bed_count = 0;
					if ($data_rooms) {
						foreach ($data_rooms as $pkey => $pvalue) {
							foreach ($data_rooms_types as $rkey => $rvalue) {
								if ($rvalue['id'] == $pvalue['hotel_room_id']) {
									$room_type_name = $rvalue['name'];
									continue;
								}
							}
							if ($pvalue['room_count'] > 0) {
								$total_room_price += ($pvalue['room_count'] * $pvalue['room_price']);
								if ($pvalue['extra_count']) {
									$total_extra_bed_price += $pvalue['extra_count'] * $pvalue['extra_price'];
									$total_extra_bed_count += $pvalue['extra_count'];
								}
							}
							$all_room_voucher_text .= $pvalue['room_count'] . " x {$room_type_name} " . ($pvalue['room_price']) . " {$currency} ";
							if ($pvalue['extra_count'] > 0) {
								$all_room_voucher_text .= " with " . $pvalue['extra_count'] . " extra bed " . ($pvalue['extra_count'] * $pvalue['extra_price']) . " {$currency},";
							} else {
								$all_room_voucher_text .= "<br>";
							}

						}
					}


					$all_room_voucher_text = trim($all_room_voucher_text, ",");
					?>
					<p style="margin-bottom:0in;">
						<font color="#000000">
							<font face="Arial AMU, serif">
								<font size="3" style="font-size:12pt;">
						<span lang="en-US" xml:lang="en-US">
							<i>
								<b>
									<span style=""><?= (isset($getConstants['room'])) ? room : "{room}"; ?>
										:<br><?= $all_room_voucher_text; ?>
										<br><br> ROOM TOTAL: <?= $total_room_price ?> <?= $currency ?>
										(<?= (isset($getConstants['per_night'])) ? per_night : "{per_night}"; ?>
										) * <?= $days_left; ?> <?= (isset($getConstants['night'])) ? night : "{night}"; ?>
										<?php
										if ($total_extra_bed_price > 0) {
											?>
											+ <?= $total_extra_bed_price ?> <?= $currency ?> <?= $total_extra_bed_count ?> Extra bed(s) (<?= (isset($getConstants['per_night'])) ? per_night : "{per_night}"; ?>) * <?= $days_left; ?> <?= (isset($getConstants['night'])) ? night : "{night}"; ?>
											<?php
										}
										?>
										= <?= (($total_room_price * $days_left) + $total_extra_bed_price * $days_left) ?> <?= $currency ?>
										<br>
									</span>
								</b>
							</i>
						</span>
								</font>
							</font>
						</font>
					</p>
					<p style="margin-bottom:0in;">
						<font color="#000000">
							<font face="Arial AMU, serif">
								<font size="3" style="font-size:12pt;">
						<span lang="en-US" xml:lang="en-US">
							<i>
								<b>
									<span style="">
										
									<br>
										<?php
										$all_extra_text = '';
										$total_extra_price = '';
										$data_extra_f = $this_extras;
										if ($data_extra_f) {
											foreach ($data_extra_f as $ekey => $evalue) {
												$extra_name = '';
												$additional_data = '';
												foreach ($data_extra_types as $xkey => $xvalue) {
													if ($xvalue['id'] == $evalue['order_extra_id']) {
														$extra_name = strtolower($xvalue['name']);
														continue;
													}
												}
												if ($evalue['order_extra_price'] > 0) {
													//$all_extra_text .= '+';
													$all_extra_text .= isset($getConstants[$extra_name]) ? $getConstants[$extra_name] : $extra_name;
													if ($extra_name == 'airport_transfer' || $extra_name = 'city_tour') {
														$total_extra_price += $evalue['order_extra_price'] * $b_value["adult_count"];
														$all_extra_text .= ": +" . $evalue['order_extra_price'] * $b_value["adult_count"] . " {$currency} ";
													} else {
														$total_extra_price += $evalue['order_extra_price'];
														$all_extra_text .= ": +" . $evalue['order_extra_price'] . " {$currency} ";
													}
													$all_extra_text .= ", ";
												}
											}
										}
										$all_extra_text = trim($all_extra_text, " ");
										$all_extra_text = trim($all_extra_text, ",");
										echo (strlen($all_extra_text) > 2) ? $all_extra_text . "<br><br>" : '';
										?>
									
									</span>
								</b>
							</i>
						</span>
								</font>
							</font>
						</font>
					</p>
					<?php
				}
				$calc_total = (isset($total_room_price)) ? ($total_room_price*$days_left)+$total_extra_price+$total_extra_bed_price*$days_left : 0;
				$total_sum_hotels[] = array("hotel_name"=>$b_value['hotel_name'],"total_hotel"=>$calc_total);
			}
			echo ($show_hotel_name) ? "<hr/>" : '';
			}else{
				echo "N/A";
			}
		if (isset($_REQUEST['printType']) && $_REQUEST['printType'] == 1) {
			?>
			<!--loop end-->
		<table cellspacing="0" width="100%">
	<colgroup>
		<col width="143">
			<col width="175">
				<col width="218">
				</colgroup>
				<tbody>
					<tr valign="top">
						<td style="border:1px solid #00000a;padding-top:0in;padding-bottom:0in;padding-left:0.08in;padding-right:0.08in;" width="143">
							<p align="center">
								<font color="#4f6228">
									<font face="Arial AMU, serif">
										<font size="3" style="font-size:12pt;">
											<span lang="en-US" xml:lang="en-US">
												<i>
													<b>
														<span style=""><?=(isset($getConstants['total_amount'])) ? total_amount : "{total_amount}";?></span>
													</b>
												</i>
											</span>
										</font>
									</font>
								</font>
							</p>
						</td>
						<!--<td style="border:1px solid #00000a;padding-top:0in;padding-bottom:0in;padding-left:0.08in;padding-right:0.08in;" width="175">
							<p align="center">
								<font color="#4f6228">
									<font face="Arial AMU, serif">
										<font size="3" style="font-size:12pt;">
											<span lang="en-US" xml:lang="en-US">
												<i>
													<b>
														<span style=""><?=(isset($getConstants['bank_account'])) ? bank_account : "{bank_account}";?></span>
													</b>
												</i>
											</span>
										</font>
									</font>
								</font>
							</p>
						</td>
						<td style="border:1px solid #00000a;padding-top:0in;padding-bottom:0in;padding-left:0.08in;padding-right:0.08in;" width="218">
							<p align="center" style="margin-bottom:0in;">
								<font color="#4f6228">
									<font face="Arial AMU, serif">
										<font size="3" style="font-size:12pt;">
											<span lang="en-US" xml:lang="en-US">
												<i>
													<b>
														<span style=""><?=(isset($getConstants['payment_status'])) ? payment_status : "{payment_status}";?></span>
													</b>
												</i>
											</span>
										</font>
									</font>
								</font>
							</p>
						</td>-->
						</tr>
						<tr valign="top">
							<td style="border:1px solid #00000a;padding-top:0in;padding-bottom:0in;padding-left:0.08in;padding-right:0.08in;" width="143">
								<?php
								//todo count persons and check transfer amount also currency
								//<?=(isset($travel_data["travel_currency"])) ? $travel_data["travel_currency"] : ''
								?>
								<p align="center" style="margin-bottom:0in;">
									<font face="Arial AMU, serif">
										<font size="3" style="font-size:12pt;">
											<span lang="en-US" xml:lang="en-US">
												<i>
													<b>
														<?php
														$count_sums = count($total_sum_hotels);
														if(is_array($total_sum_hotels) && $count_sums > 0){
															$summing = 0;
															$sum_text = '';
															foreach ($total_sum_hotels as $vs) {
																$sum_text .= $vs['hotel_name'] . "({$vs['total_hotel']} {$currency}) +";
																$summing += $vs['total_hotel'];
															}
															if($count_sums > 1) {

																$sum_text = trim($sum_text, "+");
																echo $sum_text . " = " . $summing . " {$currency}<br>";
															}else{
																echo $summing . " {$currency}<br>";
															}
															if(isset($travel_data['travel_partial_pay']) && $travel_data['travel_partial_pay']> 0){
																echo $travel_data['travel_partial_pay']." {$currency} Paid<br>";
																echo ($summing-$travel_data['travel_partial_pay'])." {$currency} Payable";
															}
														}
														?>
													</b>
												</i>
											</span>
										</font>
									</font>
								</p>

							</td>
							<?php /*<td style="border:1px solid #00000a;padding-top:0in;padding-bottom:0in;padding-left:0.08in;padding-right:0.08in;" width="175">
								<p align="center" style="margin-bottom:0in;">
									<font color="#000000">
										<font face="Arial AMU, serif">
											<font size="3" style="font-size:12pt;">
												<span lang="en-US" xml:lang="en-US">
													<i>
														<b>
															<span style=""><?=(isset($travel_data["travel_paymentNote"])) ? $travel_data["travel_paymentNote"] : ''?></span>
														</b>
													</i>
												</span>
											</font>
										</font>
									</font>
								</p>
								<p align="center" style="margin-bottom:0in;">
									<font color="#000000">
										<font face="Arial AMU, serif">
											<font size="3" style="font-size:12pt;">
												<span lang="en-US" xml:lang="en-US">
													<i>
														<b>
															<span style=""><!--{bank_aacount_holder_name}--></span>
														</b>
													</i>
												</span>
											</font>
										</font>
									</font>
								</p>
								<p align="center">
									<font color="#000000">
										<font face="Arial AMU, serif">
											<font size="3" style="font-size:12pt;">
												<span lang="en-US" xml:lang="en-US">
													<i>
														<b>
															<span style=""><?=(isset($travel_data["travel_payment"])) ? $data_payments[0]['name'] : ''?></span>
														</b>
													</i>
												</span>
											</font>
										</font>
									</font>
								</p>
							</td> */?>
							<!--<td style="border:1px solid #00000a;padding-top:0in;padding-bottom:0in;padding-left:0.08in;padding-right:0.08in;" width="218">
								<p align="center">
									<font color="#000000">
										<font face="Arial AMU, serif">
											<font size="3" style="font-size:12pt;">
												<span lang="en-US" xml:lang="en-US">
													<i>
														<b>
															<span style=""><?=(isset($getConstants['confirmed'])) ? confirmed : "CONFIRMED";?></span>
														</b>
													</i>
												</span>
											</font>
										</font>
									</font>
								</p>
								</td>!-->
							</tr>
						</tbody>
					</table>
				
		
		<?php
		}
	}
		?>
	</body>
</html>