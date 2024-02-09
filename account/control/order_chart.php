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
	$operators = getwayConnect::getwayData("SELECT * FROM user where user_level like '%36%' order by user_active desc,username desc");
	if(isset($_REQUEST['getorders']) && $_REQUEST['getorders']){
		$type = $_REQUEST['type'];
		if($type == 'status'){
		    $from_date = $_REQUEST['from_date'];
		    $to_date = $_REQUEST['to_date'];
		    $operator_filter = $_REQUEST['operator_filter'];
		    $sql = "SELECT * from rg_orders";
		    if(!empty($from_date)){
		    	$sql.= " where created_date >= '" . $from_date . "'";
		    }
		    if(!empty($to_date)){
		    	$sql.= " AND created_date <= '" . $to_date . "'";
		    }
		    if($operator_filter != 'all' ){
				$for_operator_sql = " AND operator = '" . $operator_filter ."'";
		    	$sql.= " AND operator = '" . $operator_filter ."'";
		    }
			$orders = getwayConnect::getwayData($sql);
			$for_checking = [];
			$for_show_dates = [];
			foreach( $orders as $key => $value ){
				if (!in_array($value['created_date'], $for_checking)) {
					$for_checking[]= $value['created_date'];
					$for_show_dates[] = [$value['created_date']];
				}
			}
			$for_hastatvac_array = [];
			$for_anavart_array = [];
			$for_araqvac_array = [];
			$for_chexyal_array = [];
			$for_janaparhin_array = [];
			$for_dublikat_array = [];
			$for_veradararcac_array = [];
			$for_hrajarvel_e_araqel_array = [];
			$for_patrast_array = [];
			$for_avtomat_array = [];
			$for_hastatvac_araqichi_koxmic_array = [];
			$for_bac_toxac_array = [];
			$for_komunikacia_array = [];
			$for_all_orders = [];
			foreach( $for_checking as $key => $value ){
				if($operator_filter != 'all'){
					$all_orders = getwayConnect::getwayData("SELECT COUNT(*) FROM rg_orders where created_date = '" . $value . "'");
					$hastatvac = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_status = 1 and created_date = '" . $value . "' and operator= '" .$operator_filter."'");
					$anavart = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_status = 2 and created_date = '" . $value . "' and operator= '" . $operator_filter."'");
					$araqvac = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_status = 3 and created_date = '" . $value . "' and operator = '" . $operator_filter ."'");
					$chexyal = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_status = 4 and created_date = '" . $value . "' and operator = '" . $operator_filter ."'");
					$bac_toxac = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_status = 5 and created_date = '" . $value . "' and operator = '" . $operator_filter ."'");
					$janaparhin = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_status = 6 and created_date = '" . $value . "' and operator = '" . $operator_filter ."'");
					$veradarcrac = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_status = 7 and created_date = '" . $value . "' and operator = '" . $operator_filter ."'");
					$komunikacia = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_status = 8 and created_date = '" . $value . "' and operator = '" . $operator_filter ."'");
					$dublikat = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_status = 9 and created_date = '" . $value . "' and operator = '" . $operator_filter ."'");
					$avtomat = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_status = 10 and created_date = '" . $value . "' and operator = '" . $operator_filter ."'");
					$hrajarvel_e_aragel = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_status = 11 and created_date = '" . $value . "'  and operator = '" . $operator_filter ."'");
					$patrast = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_status = 12 and created_date = '" . $value . "' and operator = '" . $operator_filter ."'");
					$hastatvac_araqichi_koxmic = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_status = 13 and created_date = '" . $value . "' and operator = '" . $operator_filter ."'");
				}
				else{
					$all_orders = getwayConnect::getwayData("SELECT COUNT(*) FROM rg_orders where created_date = '" . $value . "'");
					$hastatvac = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_status = 1 and created_date = '" . $value . "'");
					$anavart = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_status = 2 and created_date = '" . $value . "'");
					$araqvac = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_status = 3 and created_date = '" . $value . "'");
					$chexyal = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_status = 4 and created_date = '" . $value . "'");
					$bac_toxac = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_status = 5 and created_date = '" . $value . "'");
					$janaparhin = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_status = 6 and created_date = '" . $value . "'");
					$veradarcrac = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_status = 7 and created_date = '" . $value . "'");
					$komunikacia = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_status = 8 and created_date = '" . $value . "'");
					$dublikat = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_status = 9 and created_date = '" . $value . "'");
					$avtomat = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_status = 10 and created_date = '" . $value . "'");
					$hrajarvel_e_aragel = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_status = 11 and created_date = '" . $value . "'");
					$patrast = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_status = 12 and created_date = '" . $value . "'");
					$hastatvac_araqichi_koxmic = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_status = 13 and created_date = '" . $value . "'");
				}

				if(!empty($hastatvac)){
					$for_hastatvac_array[] = count($hastatvac);
				}
				else{
					$for_hastatvac_array[] = 0;
				}
				if(!empty($all_orders)){
					$for_all_orders[] = $all_orders[0][0];
				}
				else{
					$for_all_orders[] = 0;
				}
				if(!empty($anavart)){
					$for_anavart_array[] = count($anavart);
				}
				else{
					$for_anavart_array[] = 0;
				}
				if(!empty($araqvac)){
					$for_araqvac_array[] = count($araqvac);
				}
				else{
					$for_araqvac_array[] = 0;
				}
				if(!empty($chexyal)){
					$for_chexyal_array[] = count($chexyal);
				}
				else{
					$for_chexyal_array[] = 0;
				}
				if(!empty($bac_toxac)){
					$for_bac_toxac_array[] = count($bac_toxac);
				}
				else{
					$for_bac_toxac_array[] = 0;
				}
				if(!empty($janaparhin)){
					$for_janaparhin_array[] = count($janaparhin);
				}
				else{
					$for_janaparhin_array[] = 0;
				}
				if(!empty($veradarcrac)){
					$for_veradararcac_array[] = count($veradarcrac);
				}
				else{
					$for_veradararcac_array[] = 0;
				}
				if(!empty($komunikacia)){
					$for_komunikacia_array[] = count($komunikacia);
				}
				else{
					$for_komunikacia_array[] = 0;
				}
				if(!empty($dublikat)){
					$for_dublikat_array[] = count($dublikat);
				}
				else{
					$for_dublikat_array[] = 0;
				}
				if(!empty($avtomat)){
					$for_avtomat_array[] = count($avtomat);
				}
				else{
					$for_avtomat_array[] = 0;
				}
				if(!empty($hrajarvel_e_aragel)){
					$for_hrajarvel_e_araqel_array[] = count($hrajarvel_e_aragel);
				}
				else{
					$for_hrajarvel_e_araqel_array[] = 0;
				}
				if(!empty($patrast)){
					$for_patrast_array[] = count($patrast);
				}
				else{
					$for_patrast_array[] = 0;
				}
				if(!empty($hastatvac_araqichi_koxmic)){
					$for_hastatvac_araqichi_koxmic_array[] = count($hastatvac_araqichi_koxmic);
				}
				else{
					$for_hastatvac_araqichi_koxmic_array[] = 0;
				}
			}
			$result = [];
			$result['for_hastatvac_array'] = $for_hastatvac_array;
			$result['for_patrast_array'] = $for_patrast_array;
			$result['for_hastatvac_araqichi_koxmic_array'] = $for_hastatvac_araqichi_koxmic_array;
			$result['for_anavart_array'] = $for_anavart_array;
			$result['for_janaparhin_array'] = $for_janaparhin_array;
			$result['for_chexyal_array'] = $for_chexyal_array;
			$result['for_bac_toxac_array'] = $for_bac_toxac_array;
			$result['for_komunikacia_array'] = $for_komunikacia_array;
			$result['for_dublikat_array'] = $for_dublikat_array;
			$result['for_hrajarvel_e_araqel_array'] = $for_hrajarvel_e_araqel_array;
			$result['for_avtomat_array'] = $for_avtomat_array;
			$result['for_veradararcac_array'] = $for_veradararcac_array;
			$result['for_araqvac_array'] = $for_araqvac_array;
			$result['for_show_dates'] = $for_show_dates;
			$result['for_type_of_chart'] = 'status';
			$result['for_all_orders'] = $for_all_orders;
			print json_encode($result);die;
		}
		else if($type == 'payemnt_type'){
			$from_date = $_REQUEST['from_date'];
			$current_payment_status = $_REQUEST['current_payment_status'];
		    $to_date = $_REQUEST['to_date'];
		    $operator_filter = $_REQUEST['operator_filter'];
		    $sql = "SELECT * from rg_orders";
		    if(!empty($from_date)){
		    	$sql.= " where created_date >= '" . $from_date . "'";
		    }
		    if(!empty($to_date)){
		    	$sql.= " AND created_date <= '" . $to_date . "'";
		    }
		    if($operator_filter != 'all' ){
				$for_operator_sql = " AND operator = '" . $operator_filter ."'";
		    	$sql.= " AND operator = '" . $operator_filter ."'";
		    }
		    $current_payment_status_sql = '';
		    if($current_payment_status == 'paid'){
		    	$current_payment_status_sql = " and ( delivery_status = 1 or delivery_status = 3 or delivery_status = 6 or delivery_status = 7 or delivery_status = 11 or delivery_status = 12 or delivery_status = 13 or delivery_status = 14 ) ";
		    }
		    else if($current_payment_status == 'unpaid'){
		    	$current_payment_status_sql = " and ( delivery_status = 2 or delivery_status = 4 or delivery_status = 5 or delivery_status = 8 or delivery_status = 9 or delivery_status = 10) ";
		    }
			$orders = getwayConnect::getwayData($sql);
			$for_checking = [];
			$for_show_dates = [];
			foreach( $orders as $key => $value ){
				if (!in_array($value['created_date'], $for_checking)) {
					$for_checking[]= $value['created_date'];
					$for_show_dates[] = [$value['created_date']];
				}
			}
			$for_unistream_array = [];
			$for_moneygram_array = [];
			$for_ria_array = [];
			$for_zolotaya_korona_array = [];
			$for_bank_transfer_array = [];
			$for_webmoney_array = [];
			$for_yandex_array = [];
			$for_qiwi_array = [];
			$for_paypal_array = [];
			$for_cash_array = [];
			$for_walletone_array = [];
			$for_idram_array = [];
			$for_ameriabank_array = [];
			$for_telcell_array = [];
			$for_cberbank_array = [];
			$for_nisya_array = [];
			$for_bank_invoice_array = [];
			$for_la_caxia_barcelona_array = [];
			$for_credit_card_stripe_array = [];
			$for_paypal_spain_array = [];
			$for_acba_online_array = [];
			$for_all_orders = [];
			$for_harkayin_hashvov_orders = [];
			$for_hdm_ktronov_orders = [];
			foreach($for_checking as $key=>$value){
				if($operator_filter != 'all'){
					$all_orders = getwayConnect::getwayData("SELECT COUNT(*) FROM rg_orders where created_date = '" . $value . "'" . $current_payment_status_sql);
					$harkayin_hashvov = getwayConnect::getwayData("SELECT COUNT(*) FROM rg_orders where created_date = '" . $value . "' and (payment_type = 15 or payment_type = 23 or payment_type = 13 or payment_type = 16 or payment_type = 5 or payment_type = 19 ) " . $current_payment_status_sql);
					$hdm_ktronov = getwayConnect::getwayData("SELECT COUNT(*) FROM rg_orders where created_date = '" . $value . "' and (payment_type = 11 or payment_type = 12 ) " . $current_payment_status_sql);
					$unistream = getwayConnect::getwayData("SELECT * FROM rg_orders where payment_type = 1 and created_date = '" . $value . "' and operator= '" .$operator_filter."' " . $current_payment_status_sql);
					$moneygram = getwayConnect::getwayData("SELECT * FROM rg_orders where payment_type = 2 and created_date = '" . $value . "' and operator= '" . $operator_filter."' " . $current_payment_status_sql);
					$ria = getwayConnect::getwayData("SELECT * FROM rg_orders where payment_type = 3 and created_date = '" . $value . "' and operator = '" . $operator_filter ."'" . $current_payment_status_sql);
					$zolotaya_korona = getwayConnect::getwayData("SELECT * FROM rg_orders where payment_type = 4 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql);
					$bank_transfer = getwayConnect::getwayData("SELECT * FROM rg_orders where payment_type = 5 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql);
					$webmoney = getwayConnect::getwayData("SELECT * FROM rg_orders where payment_type = 6 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$yandex = getwayConnect::getwayData("SELECT * FROM rg_orders where payment_type = 7 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql);
					$qiwi = getwayConnect::getwayData("SELECT * FROM rg_orders where payment_type = 9 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql);
					$paypal = getwayConnect::getwayData("SELECT * FROM rg_orders where payment_type = 10 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " .$current_payment_status_sql);
					$cash = getwayConnect::getwayData("SELECT * FROM rg_orders where payment_type = 11 and created_date = '" . $value . "'  and operator = '" . $operator_filter ."' " . $current_payment_status_sql);
					$walletone = getwayConnect::getwayData("SELECT * FROM rg_orders where payment_type = 12 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql);
					$idram = getwayConnect::getwayData("SELECT * FROM rg_orders where payment_type = 13 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql);
					$ameriabank = getwayConnect::getwayData("SELECT * FROM rg_orders where payment_type = 15 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql);
					$telcell = getwayConnect::getwayData("SELECT * FROM rg_orders where payment_type = 16 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql);
					$cberbank = getwayConnect::getwayData("SELECT * FROM rg_orders where payment_type = 17 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql);
					$nisya = getwayConnect::getwayData("SELECT * FROM rg_orders where payment_type = 18 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql);
					$bank_invoice = getwayConnect::getwayData("SELECT * FROM rg_orders where payment_type = 19 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql);
					$la_caxia_barcelona = getwayConnect::getwayData("SELECT * FROM rg_orders where payment_type = 20 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql);
					$credit_card_stripe = getwayConnect::getwayData("SELECT * FROM rg_orders where payment_type = 21 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql);
					$paypal_spain = getwayConnect::getwayData("SELECT * FROM rg_orders where payment_type = 22 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql);
					$acba_online = getwayConnect::getwayData("SELECT * FROM rg_orders where payment_type = 23 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql);
				}
				else{
					$all_orders = getwayConnect::getwayData("SELECT COUNT(*) FROM rg_orders where created_date = '" . $value . "' " . $current_payment_status_sql);
					$harkayin_hashvov = getwayConnect::getwayData("SELECT COUNT(*) FROM rg_orders where created_date = '" . $value . "' and (payment_type = 15 or payment_type = 23 or payment_type = 13 or payment_type = 16 or payment_type = 5 or payment_type = 19 ) " . $current_payment_status_sql);
					$hdm_ktronov = getwayConnect::getwayData("SELECT COUNT(*) FROM rg_orders where created_date = '" . $value . "' and (payment_type = 11 or payment_type = 12 ) " . $current_payment_status_sql);
					$unistream = getwayConnect::getwayData("SELECT * FROM rg_orders where payment_type = 1 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$moneygram = getwayConnect::getwayData("SELECT * FROM rg_orders where payment_type = 2 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$ria = getwayConnect::getwayData("SELECT * FROM rg_orders where payment_type = 3 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$zolotaya_korona = getwayConnect::getwayData("SELECT * FROM rg_orders where payment_type = 4 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$bank_transfer = getwayConnect::getwayData("SELECT * FROM rg_orders where payment_type = 5 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$webmoney = getwayConnect::getwayData("SELECT * FROM rg_orders where payment_type = 6 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$yandex = getwayConnect::getwayData("SELECT * FROM rg_orders where payment_type = 7 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$credit_card = getwayConnect::getwayData("SELECT * FROM rg_orders where payment_type = 8 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$qiwi = getwayConnect::getwayData("SELECT * FROM rg_orders where payment_type = 9 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$paypal = getwayConnect::getwayData("SELECT * FROM rg_orders where payment_type = 10 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$cash = getwayConnect::getwayData("SELECT * FROM rg_orders where payment_type = 11 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$walletone = getwayConnect::getwayData("SELECT * FROM rg_orders where payment_type = 12 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$idram = getwayConnect::getwayData("SELECT * FROM rg_orders where payment_type = 13 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$ameriabank = getwayConnect::getwayData("SELECT * FROM rg_orders where payment_type = 15 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$telcell = getwayConnect::getwayData("SELECT * FROM rg_orders where payment_type = 16 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$cberbank = getwayConnect::getwayData("SELECT * FROM rg_orders where payment_type = 17 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$nisya = getwayConnect::getwayData("SELECT * FROM rg_orders where payment_type = 18 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$bank_invoice = getwayConnect::getwayData("SELECT * FROM rg_orders where payment_type = 19 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$la_caxia_barcelona = getwayConnect::getwayData("SELECT * FROM rg_orders where payment_type = 20 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$credit_card_stripe = getwayConnect::getwayData("SELECT * FROM rg_orders where payment_type = 21 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$paypal_spain = getwayConnect::getwayData("SELECT * FROM rg_orders where payment_type = 22 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$acba_online = getwayConnect::getwayData("SELECT * FROM rg_orders where payment_type = 23 and created_date = '" . $value . "' " . $current_payment_status_sql);
				}
				if(!empty($unistream)){
					$for_unistream_array[] = count($unistream);
				}
				else{
					$for_unistream_array[] = 0;
				}
				if(!empty($all_orders)){
					$for_all_orders[] = $all_orders[0][0];
				}
				else{
					$for_all_orders[] = 0;
				}
				if(!empty($harkayin_hashvov)){
					$for_harkayin_hashvov_orders[] = $harkayin_hashvov[0][0];
				}
				else{
					$for_harkayin_hashvov_orders[] = 0;
				}
				if(!empty($hdm_ktronov)){
					$for_hdm_ktronov_orders[] = $hdm_ktronov[0][0];
				}
				else{
					$for_hdm_ktronov_orders[] = 0;
				}
				if(!empty($moneygram)){
					$for_moneygram_array[] = count($moneygram);
				}
				else{
					$for_moneygram_array[] = 0;
				}
				if(!empty($ria)){
					$for_ria_array[] = count($ria);
				}
				else{
					$for_ria_array[] = 0;
				}
				if(!empty($zolotaya_korona)){
					$for_zolotaya_korona_array[] = count($zolotaya_korona);
				}
				else{
					$for_zolotaya_korona_array[] = 0;
				}
				if(!empty($bank_transfer)){
					$for_bank_transfer_array[] = count($bank_transfer);
				}
				else{
					$for_bank_transfer_array[] = 0;
				}
				if(!empty($webmoney)){
					$for_webmoney_array[] = count($webmoney);
				}
				else{
					$for_webmoney_array[] = 0;
				}
				if(!empty($yandex)){
					$for_yandex_array[] = count($yandex);
				}
				else{
					$for_yandex_array[] = 0;
				}
				if(!empty($qiwi)){
					$for_qiwi_array[] = count($qiwi);
				}
				else{
					$for_qiwi_array[] = 0;
				}
				if(!empty($paypal)){
					$for_paypal_array[] = count($paypal);
				}
				else{
					$for_paypal_array[] = 0;
				}
				if(!empty($cash)){
					$for_cash_array[] = count($cash);
				}
				else{
					$for_cash_array[] = 0;
				}
				if(!empty($walletone)){
					$for_walletone_array[] = count($walletone);
				}
				else{
					$for_walletone_array[] = 0;
				}
				if(!empty($idram)){
					$for_idram_array[] = count($idram);
				}
				else{
					$for_idram_array[] = 0;
				}
				if(!empty($ameriabank)){
					$for_ameriabank_array[] = count($ameriabank);
				}
				else{
					$for_ameriabank_array[] = 0;
				}
				if(!empty($telcell)){
					$for_telcell_array[] = count($telcell);
				}
				else{
					$for_telcell_array[] = 0;
				}
				if(!empty($cberbank)){
					$for_cberbank_array[] = count($cberbank);
				}
				else{
					$for_cberbank_array[] = 0;
				}
				if(!empty($nisya)){
					$for_nisya_array[] = count($nisya);
				}
				else{
					$for_nisya_array[] = 0;
				}
				if(!empty($bank_invoice)){
					$for_bank_invoice_array[] = count($bank_invoice);
				}
				else{
					$for_bank_invoice_array[] = 0;
				}
				if(!empty($la_caxia_barcelona)){
					$for_la_caxia_barcelona_array[] = count($la_caxia_barcelona);
				}
				else{
					$for_la_caxia_barcelona_array[] = 0;
				}
				if(!empty($credit_card_stripe)){
					$for_credit_card_stripe_array[] = count($credit_card_stripe);
				}
				else{
					$for_credit_card_stripe_array[] = 0;
				}
				if(!empty($paypal_spain)){
					$for_paypal_spain_array[] = count($paypal_spain);
				}
				else{
					$for_paypal_spain_array[] = 0;
				}
				if(!empty($acba_online)){
					$for_acba_online_array[] = count($acba_online);
				}
				else{
					$for_acba_online_array[] = 0;
				}
			}
			$result = [];
			$result['for_unistream_array'] = $for_unistream_array;
			$result['for_moneygram_array'] = $for_moneygram_array;
			$result['for_ria_array'] = $for_ria_array;
			$result['for_zolotaya_korona_array'] = $for_zolotaya_korona_array;
			$result['for_bank_transfer_array'] = $for_bank_transfer_array;
			$result['for_webmoney_array'] = $for_webmoney_array;
			$result['for_yandex_array'] = $for_yandex_array;
			$result['for_qiwi_array'] = $for_qiwi_array;
			$result['for_paypal_array'] = $for_paypal_array;
			$result['for_cash_array'] = $for_cash_array;
			$result['for_walletone_array'] = $for_walletone_array;
			$result['for_idram_array'] = $for_idram_array;
			$result['for_ameriabank_array'] = $for_ameriabank_array;
			$result['for_telcell_array'] = $for_telcell_array;
			$result['for_cberbank_array'] = $for_cberbank_array;
			$result['for_nisya_array'] = $for_nisya_array;
			$result['for_bank_invoice_array'] = $for_bank_invoice_array;
			$result['for_la_caxia_barcelona_array'] = $for_la_caxia_barcelona_array;
			$result['for_credit_card_stripe_array'] = $for_credit_card_stripe_array;
			$result['for_paypal_spain_array'] = $for_paypal_spain_array;
			$result['for_acba_online_array'] = $for_acba_online_array;
			$result['for_show_dates'] = $for_show_dates;
			$result['for_type_of_chart'] = 'payment_type';
			$result['for_all_orders'] = $for_all_orders;
			$result['for_harkayin_hashvov_orders'] = $for_harkayin_hashvov_orders;
			$result['for_hdm_ktronov_orders'] = $for_hdm_ktronov_orders;
			print json_encode($result);die;
		}
		else if($type == 'communication'){
			$from_date = $_REQUEST['from_date'];
			$current_payment_status = $_REQUEST['current_payment_status'];
		    $to_date = $_REQUEST['to_date'];
		    $operator_filter = $_REQUEST['operator_filter'];
		    $sql = "SELECT * from rg_orders";
		    if(!empty($from_date)){
		    	$sql.= " where created_date >= '" . $from_date . "'";
		    }
		    if(!empty($to_date)){
		    	$sql.= " AND created_date <= '" . $to_date . "'";
		    }
		    if($operator_filter != 'all' ){
				$for_operator_sql = " AND operator = '" . $operator_filter ."'";
		    	$sql.= " AND operator = '" . $operator_filter ."'";
		    }
		    $current_payment_status_sql = '';
		    if($current_payment_status == 'paid'){
		    	$current_payment_status_sql = " and ( delivery_status = 1 or delivery_status = 3 or delivery_status = 6 or delivery_status = 7 or delivery_status = 11 or delivery_status = 12 or delivery_status = 13 or delivery_status = 14 ) ";
		    }
		    else if($current_payment_status == 'unpaid'){
		    	$current_payment_status_sql = " and ( delivery_status = 2 or delivery_status = 4 or delivery_status = 5 or delivery_status = 8 or delivery_status = 9 or delivery_status = 10) ";
		    }
			$orders = getwayConnect::getwayData($sql);
			$for_checking = [];
			$for_show_dates = [];
			foreach( $orders as $key => $value ){
				if (!in_array($value['created_date'], $for_checking)) {
					$for_checking[]= $value['created_date'];
					$for_show_dates[] = [$value['created_date']];
				}
			}
			$for_online_order_array = [];
			$for_live_chat_array = [];
			$for_skype_array = [];
			$for_all_int_055_500_956_array = [];
			$for_an_am_055_242_242_array = [];
			$for_f_a_am_091_356_937_array = [];
			$for_rus_758_401_8345_array = [];
			$for_european_array = [];
			$for_us_1815705800_array = [];
			$for_email_array = [];
			$for_town_array = [];
			$for_by_partner_array = [];
			$for_viber_array = [];
			$for_whatsapp_array = [];
			$for_facebook_array = [];
			$for_instagram_array = [];
			$for_vkontakte_array = [];
			$for_telegram_array = [];
			$for_3_rd_step_array = [];
			$for_familiar_friend_array = [];
			$for_all_orders = [];
			foreach($for_checking as $key=>$value){
				if($operator_filter != 'all'){
					$all_orders = getwayConnect::getwayData("SELECT COUNT(*) FROM rg_orders where created_date = '" . $value . "'" . $current_payment_status_sql);
					$online_order = getwayConnect::getwayData("SELECT * FROM rg_orders where order_source = 1 and created_date = '" . $value . "' and operator= '" .$operator_filter."' " . $current_payment_status_sql);
					$live_chat = getwayConnect::getwayData("SELECT * FROM rg_orders where order_source = 2 and created_date = '" . $value . "' and operator= '" . $operator_filter."' " . $current_payment_status_sql);
					$skype = getwayConnect::getwayData("SELECT * FROM rg_orders where order_source = 3 and created_date = '" . $value . "' and operator = '" . $operator_filter ."'" . $current_payment_status_sql);
					$all_int_055_500_956 = getwayConnect::getwayData("SELECT * FROM rg_orders where order_source = 4 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql);
					$an_am_055_242_242 = getwayConnect::getwayData("SELECT * FROM rg_orders where order_source = 5 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql);
					$f_a_am_091_356_937 = getwayConnect::getwayData("SELECT * FROM rg_orders where order_source = 6 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$rus_758_401_8345 = getwayConnect::getwayData("SELECT * FROM rg_orders where order_source = 7 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql);
					$european = getwayConnect::getwayData("SELECT * FROM rg_orders where order_source = 8 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql);
					$us_1815705800 = getwayConnect::getwayData("SELECT * FROM rg_orders where order_source = 9 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " .$current_payment_status_sql);
					$email = getwayConnect::getwayData("SELECT * FROM rg_orders where order_source = 10 and created_date = '" . $value . "'  and operator = '" . $operator_filter ."' " . $current_payment_status_sql);
					$town = getwayConnect::getwayData("SELECT * FROM rg_orders where order_source = 11 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql);
					$by_partner = getwayConnect::getwayData("SELECT * FROM rg_orders where order_source = 12 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql);
					$viber = getwayConnect::getwayData("SELECT * FROM rg_orders where order_source = 13 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql);
					$whatsapp = getwayConnect::getwayData("SELECT * FROM rg_orders where order_source = 14 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql);
					$facebook = getwayConnect::getwayData("SELECT * FROM rg_orders where order_source = 15 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql);
					$instagram = getwayConnect::getwayData("SELECT * FROM rg_orders where order_source = 16 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql);
					$vkontakte = getwayConnect::getwayData("SELECT * FROM rg_orders where order_source = 17 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql);
					$telegram = getwayConnect::getwayData("SELECT * FROM rg_orders where order_source = 18 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql);
					$step_3_rd = getwayConnect::getwayData("SELECT * FROM rg_orders where order_source = 19 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql);
					$familiar_friend = getwayConnect::getwayData("SELECT * FROM rg_orders where order_source = 20 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql);
				}
				else{
					$all_orders = getwayConnect::getwayData("SELECT COUNT(*) FROM rg_orders where created_date = '" . $value . "' " . $current_payment_status_sql);
					$online_order = getwayConnect::getwayData("SELECT * FROM rg_orders where order_source = 1 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$live_chat = getwayConnect::getwayData("SELECT * FROM rg_orders where order_source = 2 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$skype = getwayConnect::getwayData("SELECT * FROM rg_orders where order_source = 3 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$all_int_055_500_956 = getwayConnect::getwayData("SELECT * FROM rg_orders where order_source = 4 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$an_am_055_242_242 = getwayConnect::getwayData("SELECT * FROM rg_orders where order_source = 5 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$f_a_am_091_356_937 = getwayConnect::getwayData("SELECT * FROM rg_orders where order_source = 6 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$rus_758_401_8345 = getwayConnect::getwayData("SELECT * FROM rg_orders where order_source = 7 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$european = getwayConnect::getwayData("SELECT * FROM rg_orders where order_source = 8 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$us_1815705800 = getwayConnect::getwayData("SELECT * FROM rg_orders where order_source = 9 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$email = getwayConnect::getwayData("SELECT * FROM rg_orders where order_source = 10 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$town = getwayConnect::getwayData("SELECT * FROM rg_orders where order_source = 11 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$by_partner = getwayConnect::getwayData("SELECT * FROM rg_orders where order_source = 12 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$viber = getwayConnect::getwayData("SELECT * FROM rg_orders where order_source = 13 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$whatsapp = getwayConnect::getwayData("SELECT * FROM rg_orders where order_source = 14 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$facebook = getwayConnect::getwayData("SELECT * FROM rg_orders where order_source = 15 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$instagram = getwayConnect::getwayData("SELECT * FROM rg_orders where order_source = 16 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$vkontakte = getwayConnect::getwayData("SELECT * FROM rg_orders where order_source = 17 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$telegram = getwayConnect::getwayData("SELECT * FROM rg_orders where order_source = 18 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$step_3_rd = getwayConnect::getwayData("SELECT * FROM rg_orders where order_source = 19 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$familiar_friend = getwayConnect::getwayData("SELECT * FROM rg_orders where order_source = 20 and created_date = '" . $value . "' " . $current_payment_status_sql);
				}
				if(!empty($online_order)){
					$for_online_order_array[] = count($online_order);
				}
				else{
					$for_online_order_array[] = 0;
				}
				if(!empty($all_orders)){
					$for_all_orders[] = $all_orders[0][0];
				}
				else{
					$for_all_orders[] = 0;
				}
				if(!empty($live_chat)){
					$for_live_chat_array[] = count($live_chat);
				}
				else{
					$for_live_chat_array[] = 0;
				}
				if(!empty($skype)){
					$for_skype_array[] = count($skype);
				}
				else{
					$for_skype_array[] = 0;
				}
				if(!empty($all_int_055_500_956)){
					$for_all_int_055_500_956_array[] = count($all_int_055_500_956);
				}
				else{
					$for_all_int_055_500_956_array[] = 0;
				}
				if(!empty($f_a_am_091_356_937)){
					$for_f_a_am_091_356_937_array[] = count($f_a_am_091_356_937);
				}
				else{
					$for_f_a_am_091_356_937_array[] = 0;
				}
				if(!empty($rus_758_401_8345)){
					$for_rus_758_401_8345_array[] = count($rus_758_401_8345);
				}
				else{
					$for_rus_758_401_8345_array[] = 0;
				}
				if(!empty($european)){
					$for_european_array[] = count($european);
				}
				else{
					$for_european_array[] = 0;
				}
				if(!empty($us_1815705800)){
					$for_us_1815705800_array[] = count($us_1815705800);
				}
				else{
					$for_us_1815705800_array[] = 0;
				}
				if(!empty($email)){
					$for_email_array[] = count($email);
				}
				else{
					$for_email_array[] = 0;
				}
				if(!empty($town)){
					$for_town_array[] = count($town);
				}
				else{
					$for_town_array[] = 0;
				}
				if(!empty($by_partner)){
					$for_by_partner_array[] = count($by_partner);
				}
				else{
					$for_by_partner_array[] = 0;
				}
				if(!empty($viber)){
					$for_viber_array[] = count($viber);
				}
				else{
					$for_viber_array[] = 0;
				}
				if(!empty($whatsapp)){
					$for_whatsapp_array[] = count($whatsapp);
				}
				else{
					$for_whatsapp_array[] = 0;
				}
				if(!empty($facebook)){
					$for_facebook_array[] = count($facebook);
				}
				else{
					$for_facebook_array[] = 0;
				}
				if(!empty($instagram)){
					$for_instagram_array[] = count($instagram);
				}
				else{
					$for_instagram_array[] = 0;
				}
				if(!empty($vkontakte)){
					$for_vkontakte_array[] = count($vkontakte);
				}
				else{
					$for_vkontakte_array[] = 0;
				}
				if(!empty($telegram)){
					$for_telegram_array[] = count($telegram);
				}
				else{
					$for_telegram_array[] = 0;
				}
				if(!empty($step_3_rd)){
					$for_3_rd_step_array[] = count($step_3_rd);
				}
				else{
					$for_3_rd_step_array[] = 0;
				}
				if(!empty($familiar_friend)){
					$for_familiar_friend_array[] = count($familiar_friend);
				}
				else{
					$for_familiar_friend_array[] = 0;
				}
				if(!empty($an_am_055_242_242)){
					$for_an_am_055_242_242_array[] = count($an_am_055_242_242);
				}
				else{
					$for_an_am_055_242_242_array[] = 0;
				}
			}
			$result = [];
			$result['for_online_order_array'] = $for_online_order_array;
			$result['for_live_chat_array'] = $for_live_chat_array;
			$result['for_skype_array'] = $for_skype_array;
			$result['for_all_int_055_500_956_array'] = $for_all_int_055_500_956_array;
			$result['for_an_am_055_242_242_array'] = $for_an_am_055_242_242_array;
			$result['for_f_a_am_091_356_937_array'] = $for_f_a_am_091_356_937_array;
			$result['for_rus_758_401_8345_array'] = $for_rus_758_401_8345_array;
			$result['for_european_array'] = $for_european_array;
			$result['for_us_1815705800_array'] = $for_us_1815705800_array;
			$result['for_email_array'] = $for_email_array;
			$result['for_town_array'] = $for_town_array;
			$result['for_by_partner_array'] = $for_by_partner_array;
			$result['for_viber_array'] = $for_viber_array;
			$result['for_whatsapp_array'] = $for_whatsapp_array;
			$result['for_facebook_array'] = $for_facebook_array;
			$result['for_instagram_array'] = $for_instagram_array;
			$result['for_vkontakte_array'] = $for_vkontakte_array;
			$result['for_telegram_array'] = $for_telegram_array;
			$result['for_3_rd_step_array'] = $for_3_rd_step_array;
			$result['for_familiar_friend_array'] = $for_familiar_friend_array;
			$result['for_show_dates'] = $for_show_dates;
			$result['for_type_of_chart'] = 'communication';
			$result['for_all_orders'] = $for_all_orders;
			print json_encode($result);die;
		}
		else if($type == 'currency'){
			$from_date = $_REQUEST['from_date'];
			$current_payment_status = $_REQUEST['current_payment_status'];
		    $to_date = $_REQUEST['to_date'];
		    $operator_filter = $_REQUEST['operator_filter'];
		    $sql = "SELECT * from rg_orders";
		    if(!empty($from_date)){
		    	$sql.= " where created_date >= '" . $from_date . "'";
		    }
		    if(!empty($to_date)){
		    	$sql.= " AND created_date <= '" . $to_date . "'";
		    }
		    if($operator_filter != 'all' ){
				$for_operator_sql = " AND operator = '" . $operator_filter ."'";
		    	$sql.= " AND operator = '" . $operator_filter ."'";
		    }
		    $current_payment_status_sql = '';
		    if($current_payment_status == 'paid'){
		    	$current_payment_status_sql = " and ( delivery_status = 1 or delivery_status = 3 or delivery_status = 6 or delivery_status = 7 or delivery_status = 11 or delivery_status = 12 or delivery_status = 13 or delivery_status = 14 ) ";
		    }
		    else if($current_payment_status == 'unpaid'){
		    	$current_payment_status_sql = " and ( delivery_status = 2 or delivery_status = 4 or delivery_status = 5 or delivery_status = 8 or delivery_status = 9 or delivery_status = 10) ";
		    }
			$orders = getwayConnect::getwayData($sql);
			$for_checking = [];
			$for_show_dates = [];
			foreach( $orders as $key => $value ){
				if (!in_array($value['created_date'], $for_checking)) {
					$for_checking[]= $value['created_date'];
					$for_show_dates[] = [$value['created_date']];
				}
			}
			$for_usd_array = [];
			$for_amd_array = [];
			$for_eur_array = [];
			$for_gbp_array = [];
			$for_rub_array = [];
			$for_irr_array = [];
			$for_all_orders = [];
			foreach($for_checking as $key=>$value){
				if($operator_filter != 'all'){
					$all_orders = getwayConnect::getwayData("SELECT COUNT(*) FROM rg_orders where created_date = '" . $value . "'" . $current_payment_status_sql);
					$usd_order = getwayConnect::getwayData("SELECT * FROM rg_orders where currency = 1 and created_date = '" . $value . "' and operator= '" .$operator_filter."' " . $current_payment_status_sql);
					$rub_order = getwayConnect::getwayData("SELECT * FROM rg_orders where currency = 2 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql);
					$amd_order = getwayConnect::getwayData("SELECT * FROM rg_orders where currency = 3 and created_date = '" . $value . "' and operator= '" . $operator_filter."' " . $current_payment_status_sql);
					$eur_order = getwayConnect::getwayData("SELECT * FROM rg_orders where currency = 4 and created_date = '" . $value . "' and operator = '" . $operator_filter ."'" . $current_payment_status_sql);
					$gbp_order = getwayConnect::getwayData("SELECT * FROM rg_orders where currency = 5 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql);
					$irr_order = getwayConnect::getwayData("SELECT * FROM rg_orders where currency = 6 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
				}
				else{
					$all_orders = getwayConnect::getwayData("SELECT COUNT(*) FROM rg_orders where created_date = '" . $value . "' " . $current_payment_status_sql);
					$usd_order = getwayConnect::getwayData("SELECT * FROM rg_orders where currency = 1 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$rub_order = getwayConnect::getwayData("SELECT * FROM rg_orders where currency = 2 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$amd_order = getwayConnect::getwayData("SELECT * FROM rg_orders where currency = 3 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$eur_order = getwayConnect::getwayData("SELECT * FROM rg_orders where currency = 4 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$gbp_order = getwayConnect::getwayData("SELECT * FROM rg_orders where currency = 5 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$irr_order = getwayConnect::getwayData("SELECT * FROM rg_orders where currency = 6 and created_date = '" . $value . "' " . $current_payment_status_sql);
				}
				if(!empty($usd_order)){
					$for_usd_array[] = count($usd_order);
				}
				else{
					$for_usd_array[] = 0;
				}
				if(!empty($all_orders)){
					$for_all_orders[] = $all_orders[0][0];
				}
				else{
					$for_all_orders[] = 0;
				}
				if(!empty($rub_order)){
					$for_rub_array[] = count($rub_order);
				}
				else{
					$for_rub_array[] = 0;
				}
				if(!empty($amd_order)){
					$for_amd_array[] = count($amd_order);
				}
				else{
					$for_amd_array[] = 0;
				}
				if(!empty($eur_order)){
					$for_eur_array[] = count($eur_order);
				}
				else{
					$for_eur_array[] = 0;
				}
				if(!empty($gbp_order)){
					$for_gbp_array[] = count($gbp_order);
				}
				else{
					$for_gbp_array[] = 0;
				}
				if(!empty($irr_order)){
					$for_irr_array[] = count($irr_order);
				}
				else{
					$for_irr_array[] = 0;
				}
			}
			$result = [];
			$result['for_usd_array'] = $for_usd_array;
			$result['for_rub_array'] = $for_rub_array;
			$result['for_amd_array'] = $for_amd_array;
			$result['for_eur_array'] = $for_eur_array;
			$result['for_gbp_array'] = $for_gbp_array;
			$result['for_irr_array'] = $for_irr_array;
			$result['for_all_orders'] = $for_all_orders;
			$result['for_show_dates'] = $for_show_dates;
			$result['for_type_of_chart'] = 'currency';
			print json_encode($result);die;
		}
		else if($type == 'delivery_country'){
			$from_date = $_REQUEST['from_date'];
			$current_payment_status = $_REQUEST['current_payment_status'];
		    $to_date = $_REQUEST['to_date'];
		    $operator_filter = $_REQUEST['operator_filter'];
		    $sql = "SELECT * from rg_orders";
		    if(!empty($from_date)){
		    	$sql.= " where created_date >= '" . $from_date . "'";
		    }
		    if(!empty($to_date)){
		    	$sql.= " AND created_date <= '" . $to_date . "'";
		    }
		    if($operator_filter != 'all' ){
				$for_operator_sql = " AND operator = '" . $operator_filter ."'";
		    	$sql.= " AND operator = '" . $operator_filter ."'";
		    }
		    $current_payment_status_sql = '';
		    if($current_payment_status == 'paid'){
		    	$current_payment_status_sql = " and ( delivery_status = 1 or delivery_status = 3 or delivery_status = 6 or delivery_status = 7 or delivery_status = 11 or delivery_status = 12 or delivery_status = 13 or delivery_status = 14 ) ";
		    }
		    else if($current_payment_status == 'unpaid'){
		    	$current_payment_status_sql = " and ( delivery_status = 2 or delivery_status = 4 or delivery_status = 5 or delivery_status = 8 or delivery_status = 9 or delivery_status = 10) ";
		    }
			$orders = getwayConnect::getwayData($sql);
			$for_checking = [];
			$for_show_dates = [];
			foreach( $orders as $key => $value ){
				if (!in_array($value['created_date'], $for_checking)) {
					$for_checking[]= $value['created_date'];
					$for_show_dates[] = [$value['created_date']];
				}
			}
			$for_Armenia_array = [];
			$for_France_array = [];
			$for_Moscow_array = [];
			$for_Spain_array = [];
			$for_abroad_array = [];
			$for_Tehran_array = [];
			$for_all_orders = [];
			foreach($for_checking as $key=>$value){
				if($operator_filter != 'all'){
					$all_orders = getwayConnect::getwayData("SELECT COUNT(*) FROM rg_orders where created_date = '" . $value . "'" . $current_payment_status_sql);
					$Armenian_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_region = 1 and created_date = '" . $value . "' and operator= '" .$operator_filter."' " . $current_payment_status_sql);
					$France_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_region = 2 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql);
					$Moscow_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_region = 3 and created_date = '" . $value . "' and operator= '" . $operator_filter."' " . $current_payment_status_sql);
					$Spain_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_region = 4 and created_date = '" . $value . "' and operator = '" . $operator_filter ."'" . $current_payment_status_sql);
					$abroad_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_region = 5 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql);
					$Tehran_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_region = 6 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
				}
				else{
					$all_orders = getwayConnect::getwayData("SELECT COUNT(*) FROM rg_orders where created_date = '" . $value . "' " . $current_payment_status_sql);
					$Armenian_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_region = 1 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$France_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_region = 2 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Moscow_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_region = 3 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Spain_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_region = 4 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$abroad_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_region = 5 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Tehran_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_region = 6 and created_date = '" . $value . "' " . $current_payment_status_sql);
				}
				if(!empty($Armenian_order)){
					$for_Armenia_array[] = count($Armenian_order);
				}
				else{
					$for_Armenia_array[] = 0;
				}
				if(!empty($all_orders)){
					$for_all_orders[] = $all_orders[0][0];
				}
				else{
					$for_all_orders[] = 0;
				}
				if(!empty($France_order)){
					$for_France_orders[] = count($France_order);
				}
				else{
					$for_France_orders[] = 0;
				}
				if(!empty($Moscow_order)){
					$for_Moscow_array[] = count($Moscow_order);
				}
				else{
					$for_Moscow_array[] = 0;
				}
				if(!empty($Spain_order)){
					$for_Spain_array[] = count($Spain_order);
				}
				else{
					$for_Spain_array[] = 0;
				}
				if(!empty($abroad_order)){
					$for_abroad_array[] = count($abroad_order);
				}
				else{
					$for_abroad_array[] = 0;
				}
				if(!empty($Tehran_order)){
					$for_Tehran_array[] = count($Tehran_order);
				}
				else{
					$for_Tehran_array[] = 0;
				}
			}
			$result = [];
			$result['for_Armenia_array'] = $for_Armenia_array;
			$result['for_France_orders'] = $for_France_orders;
			$result['for_Moscow_array'] = $for_Moscow_array;
			$result['for_Spain_array'] = $for_Spain_array;
			$result['for_abroad_array'] = $for_abroad_array;
			$result['for_Tehran_array'] = $for_Tehran_array;
			$result['for_all_orders'] = $for_all_orders;
			$result['for_show_dates'] = $for_show_dates;
			$result['for_type_of_chart'] = 'delivery_country';
			print json_encode($result);die;
		}
		else if($type == 'receiver_subregion'){
			$from_date = $_REQUEST['from_date'];
			$current_payment_status = $_REQUEST['current_payment_status'];
		    $to_date = $_REQUEST['to_date'];
		    $operator_filter = $_REQUEST['operator_filter'];
		    $sql = "SELECT * from rg_orders";
		    if(!empty($from_date)){
		    	$sql.= " where created_date >= '" . $from_date . "'";
		    }
		    if(!empty($to_date)){
		    	$sql.= " AND created_date <= '" . $to_date . "'";
		    }
		    if($operator_filter != 'all' ){
				$for_operator_sql = " AND operator = '" . $operator_filter ."'";
		    	$sql.= " AND operator = '" . $operator_filter ."'";
		    }
		    $current_payment_status_sql = '';
		    if($current_payment_status == 'paid'){
		    	$current_payment_status_sql = " and ( delivery_status = 1 or delivery_status = 3 or delivery_status = 6 or delivery_status = 7 or delivery_status = 11 or delivery_status = 12 or delivery_status = 13 or delivery_status = 14 ) ";
		    }
		    else if($current_payment_status == 'unpaid'){
		    	$current_payment_status_sql = " and ( delivery_status = 2 or delivery_status = 4 or delivery_status = 5 or delivery_status = 8 or delivery_status = 9 or delivery_status = 10) ";
		    }
			$orders = getwayConnect::getwayData($sql);
			$for_checking = [];
			$for_show_dates = [];
			foreach( $orders as $key => $value ){
				if (!in_array($value['created_date'], $for_checking)) {
					$for_checking[]= $value['created_date'];
					$for_show_dates[] = [$value['created_date']];
				}
			}
			$for_ajapnyak_array = [];
			$for_avan_array = [];
			$for_arabkir_array = [];
			$for_davtashen_array = [];
			$for_erebuni_array = [];
			$for_kentron_array = [];
			$for_malatia_sebastia_array = [];
			$for_nor_norq_array = [];
			$for_norq_marash_array = [];
			$for_nubarashen_array = [];
			$for_shengavit_array = [];
			$for_qanaqer_zeytun_array = [];
			$for_erevan_plus_5_array = [];
			$for_ayl_marzer_array = [];
			$for_chchstvac_hasce_array = [];
			$for_kotayq_array = [];
			$for_lori_array = [];
			$for_tavush_array = [];
			$for_syunik_array = [];
			$for_vayoc_dzor_array = [];
			$for_armavir_array = [];
			$for_shirak_array = [];
			$for_ararat_array = [];
			$for_aragatsotn_array = [];
			$for_gexarquniq_array = [];
			$for_all_regions_array = [];
			$for_all_orders = [];
			foreach($for_checking as $key=>$value){
				if($operator_filter != 'all'){
					$all_orders = getwayConnect::getwayData("SELECT COUNT(*) FROM rg_orders where created_date = '" . $value . "'" . $current_payment_status_sql);
					$ajapnyak_order = getwayConnect::getwayData("SELECT * FROM rg_orders where receiver_subregion = 'ajapnyak' and created_date = '" . $value . "' and operator= '" .$operator_filter."' " . $current_payment_status_sql);
					$avan_order = getwayConnect::getwayData("SELECT * FROM rg_orders where receiver_subregion = 'avan' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql);
					$arabkir_order = getwayConnect::getwayData("SELECT * FROM rg_orders where receiver_subregion = 'arabkir' and created_date = '" . $value . "' and operator= '" . $operator_filter."' " . $current_payment_status_sql);
					$davtashen_order = getwayConnect::getwayData("SELECT * FROM rg_orders where receiver_subregion = 'davtashen' and created_date = '" . $value . "' and operator = '" . $operator_filter ."'" . $current_payment_status_sql);
					$erebuni_order = getwayConnect::getwayData("SELECT * FROM rg_orders where receiver_subregion = 'erebuni' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql);
					$kentron_order = getwayConnect::getwayData("SELECT * FROM rg_orders where receiver_subregion = 'kentron' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$malatia_sebastia_order = getwayConnect::getwayData("SELECT * FROM rg_orders where receiver_subregion = 'malatia-sebastia' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$nor_norq_order = getwayConnect::getwayData("SELECT * FROM rg_orders where receiver_subregion = 'nor-norq' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$norq_marash_order = getwayConnect::getwayData("SELECT * FROM rg_orders where receiver_subregion = 'norq-marash' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$nubarashen_order = getwayConnect::getwayData("SELECT * FROM rg_orders where receiver_subregion = 'nubarashen' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$shengavit_order = getwayConnect::getwayData("SELECT * FROM rg_orders where receiver_subregion = 'shengavit' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$qanaqer_zeytun_order = getwayConnect::getwayData("SELECT * FROM rg_orders where receiver_subregion = 'qanaqer-zeytun' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$erevan_plus_5_order = getwayConnect::getwayData("SELECT * FROM rg_orders where receiver_subregion = 'erevan_plus_5' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$ayl_marzer_order = getwayConnect::getwayData("SELECT * FROM rg_orders where receiver_subregion = 'ayl-marzer' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$chchstvac_hasce_order = getwayConnect::getwayData("SELECT * FROM rg_orders where receiver_subregion = 'chchstvac_hasce' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$kotayq_order = getwayConnect::getwayData("SELECT * FROM rg_orders where receiver_subregion = 'kotayq' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$lori_order = getwayConnect::getwayData("SELECT * FROM rg_orders where receiver_subregion = 'lori' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$tavush_order = getwayConnect::getwayData("SELECT * FROM rg_orders where receiver_subregion = 'tavush' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$syunik_order = getwayConnect::getwayData("SELECT * FROM rg_orders where receiver_subregion = 'syunik' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$vayoc_dzor_order = getwayConnect::getwayData("SELECT * FROM rg_orders where receiver_subregion = 'vayoc_dzor' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$armavir_order = getwayConnect::getwayData("SELECT * FROM rg_orders where receiver_subregion = 'armavir' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$shirak_order = getwayConnect::getwayData("SELECT * FROM rg_orders where receiver_subregion = 'shirak' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$ararat_order = getwayConnect::getwayData("SELECT * FROM rg_orders where receiver_subregion = 'ararat' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$aragatsotn_order = getwayConnect::getwayData("SELECT * FROM rg_orders where receiver_subregion = 'aragatsotn' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$gexarquniq_order = getwayConnect::getwayData("SELECT * FROM rg_orders where receiver_subregion = 'gexarquniq' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$all_regions_order = getwayConnect::getwayData("SELECT * FROM rg_orders where receiver_subregion = 'all_regions' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
				}
				else{
					$all_orders = getwayConnect::getwayData("SELECT COUNT(*) FROM rg_orders where created_date = '" . $value . "' " . $current_payment_status_sql);
					$ajapnyak_order = getwayConnect::getwayData("SELECT * FROM rg_orders where receiver_subregion = 'ajapnyak' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$avan_order = getwayConnect::getwayData("SELECT * FROM rg_orders where receiver_subregion = 'avan' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$arabkir_order = getwayConnect::getwayData("SELECT * FROM rg_orders where receiver_subregion = 'arabkir' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$davtashen_order = getwayConnect::getwayData("SELECT * FROM rg_orders where receiver_subregion = 'davtashen' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$erebuni_order = getwayConnect::getwayData("SELECT * FROM rg_orders where receiver_subregion = 'erebuni' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$kentron_order = getwayConnect::getwayData("SELECT * FROM rg_orders where receiver_subregion = 'kentron' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$malatia_sebastia_order = getwayConnect::getwayData("SELECT * FROM rg_orders where receiver_subregion = 'malatia-sebastia' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$nor_norq_order = getwayConnect::getwayData("SELECT * FROM rg_orders where receiver_subregion = 'nor-norq' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$norq_marash_order = getwayConnect::getwayData("SELECT * FROM rg_orders where receiver_subregion = 'norq-marash' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$nubarashen_order = getwayConnect::getwayData("SELECT * FROM rg_orders where receiver_subregion = 'nubarashen' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$shengavit_order = getwayConnect::getwayData("SELECT * FROM rg_orders where receiver_subregion = 'shengavit' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$qanaqer_zeytun_order = getwayConnect::getwayData("SELECT * FROM rg_orders where receiver_subregion = 'qanaqer-zeytun' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$erevan_plus_5_order = getwayConnect::getwayData("SELECT * FROM rg_orders where receiver_subregion = 'erevan_plus_5' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$ayl_marzer_order = getwayConnect::getwayData("SELECT * FROM rg_orders where receiver_subregion = 'ayl-marzer' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$chchstvac_hasce_order = getwayConnect::getwayData("SELECT * FROM rg_orders where receiver_subregion = 'chchstvac_hasce' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$kotayq_order = getwayConnect::getwayData("SELECT * FROM rg_orders where receiver_subregion = 'kotayq' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$lori_order = getwayConnect::getwayData("SELECT * FROM rg_orders where receiver_subregion = 'lori' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$tavush_order = getwayConnect::getwayData("SELECT * FROM rg_orders where receiver_subregion = 'tavush' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$syunik_order = getwayConnect::getwayData("SELECT * FROM rg_orders where receiver_subregion = 'syunik' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$vayoc_dzor_order = getwayConnect::getwayData("SELECT * FROM rg_orders where receiver_subregion = 'vayoc_dzor' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$armavir_order = getwayConnect::getwayData("SELECT * FROM rg_orders where receiver_subregion = 'armavir' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$shirak_order = getwayConnect::getwayData("SELECT * FROM rg_orders where receiver_subregion = 'shirak' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$ararat_order = getwayConnect::getwayData("SELECT * FROM rg_orders where receiver_subregion = 'ararat' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$aragatsotn_order = getwayConnect::getwayData("SELECT * FROM rg_orders where receiver_subregion = 'aragatsotn' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$gexarquniq_order = getwayConnect::getwayData("SELECT * FROM rg_orders where receiver_subregion = 'gexarquniq' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$all_regions_order = getwayConnect::getwayData("SELECT * FROM rg_orders where receiver_subregion = 'all_regions' and created_date = '" . $value . "' " . $current_payment_status_sql);
				}
				if(!empty($ajapnyak_order)){
					$for_ajapnyak_array[] = count($ajapnyak_order);
				}
				else{
					$for_ajapnyak_array[] = 0;
				}
				if(!empty($all_orders)){
					$for_all_orders[] = $all_orders[0][0];
				}
				else{
					$for_all_orders[] = 0;
				}
				if(!empty($avan_order)){
					$for_avan_array[] = count($avan_order);
				}
				else{
					$for_avan_array[] = 0;
				}
				if(!empty($arabkir_order)){
					$for_arabkir_array[] = count($arabkir_order);
				}
				else{
					$for_arabkir_array[] = 0;
				}
				if(!empty($davtashen_order)){
					$for_davtashen_array[] = count($davtashen_order);
				}
				else{
					$for_davtashen_array[] = 0;
				}
				if(!empty($erebuni_order)){
					$for_erebuni_array[] = count($erebuni_order);
				}
				else{
					$for_erebuni_array[] = 0;
				}
				if(!empty($kentron_order)){
					$for_kentron_array[] = count($kentron_order);
				}
				else{
					$for_kentron_array[] = 0;
				}
				if(!empty($malatia_sebastia_order)){
					$for_malatia_sebastia_array[] = count($malatia_sebastia_order);
				}
				else{
					$for_malatia_sebastia_array[] = 0;
				}
				if(!empty($nor_norq_order)){
					$for_nor_norq_array[] = count($nor_norq_order);
				}
				else{
					$for_nor_norq_array[] = 0;
				}
				if(!empty($norq_marash_order)){
					$for_norq_marash_array[] = count($norq_marash_order);
				}
				else{
					$for_norq_marash_array[] = 0;
				}
				if(!empty($nubarashen_order)){
					$for_nubarashen_array[] = count($nubarashen_order);
				}
				else{
					$for_nubarashen_array[] = 0;
				}
				if(!empty($shengavit_order)){
					$for_shengavit_array[] = count($shengavit_order);
				}
				else{
					$for_shengavit_array[] = 0;
				}
				if(!empty($qanaqer_zeytun_order)){
					$for_qanaqer_zeytun_array[] = count($qanaqer_zeytun_order);
				}
				else{
					$for_qanaqer_zeytun_array[] = 0;
				}
				if(!empty($erevan_plus_5_order)){
					$for_erevan_plus_5_array[] = count($erevan_plus_5_order);
				}
				else{
					$for_erevan_plus_5_array[] = 0;
				}
				if(!empty($ayl_marzer_order)){
					$for_ayl_marzer_array[] = count($ayl_marzer_order);
				}
				else{
					$for_ayl_marzer_array[] = 0;
				}
				if(!empty($chchstvac_hasce_order)){
					$for_chchstvac_hasce_array[] = count($chchstvac_hasce_order);
				}
				else{
					$for_chchstvac_hasce_array[] = 0;
				}
				if(!empty($kotayq_order)){
					$for_kotayq_array[] = count($kotayq_order);
				}
				else{
					$for_kotayq_array[] = 0;
				}
				if(!empty($lori_order)){
					$for_lori_array[] = count($lori_order);
				}
				else{
					$for_lori_array[] = 0;
				}
				if(!empty($tavush_order)){
					$for_tavush_array[] = count($tavush_order);
				}
				else{
					$for_tavush_array[] = 0;
				}
				if(!empty($syunik_order)){
					$for_syunik_array[] = count($syunik_order);
				}
				else{
					$for_syunik_array[] = 0;
				}
				if(!empty($vayoc_dzor_order)){
					$for_vayoc_dzor_array[] = count($vayoc_dzor_order);
				}
				else{
					$for_vayoc_dzor_array[] = 0;
				}
				if(!empty($armavir_order)){
					$for_armavir_array[] = count($armavir_order);
				}
				else{
					$for_armavir_array[] = 0;
				}
				if(!empty($shirak_order)){
					$for_shirak_array[] = count($shirak_order);
				}
				else{
					$for_shirak_array[] = 0;
				}
				if(!empty($ararat_order)){
					$for_ararat_array[] = count($ararat_order);
				}
				else{
					$for_ararat_array[] = 0;
				}
				if(!empty($aragatsotn_order)){
					$for_aragatsotn_array[] = count($aragatsotn_order);
				}
				else{
					$for_aragatsotn_array[] = 0;
				}
				if(!empty($gexarquniq_order)){
					$for_gexarquniq_array[] = count($gexarquniq_order);
				}
				else{
					$for_gexarquniq_array[] = 0;
				}
				if(!empty($all_regions_order)){
					$for_all_regions_array[] = count($all_regions_order);
				}
				else{
					$for_all_regions_array[] = 0;
				}
			}
			$result = [];
			$result['for_ajapnyak_array'] = $for_ajapnyak_array;
			$result['for_avan_array'] = $for_avan_array;
			$result['for_arabkir_array'] = $for_arabkir_array;
			$result['for_davtashen_array'] = $for_davtashen_array;
			$result['for_erebuni_array'] = $for_erebuni_array;
			$result['for_kentron_array'] = $for_kentron_array;
			$result['for_malatia_sebastia_array'] = $for_malatia_sebastia_array;
			$result['for_nor_norq_array'] = $for_nor_norq_array;
			$result['for_nubarashen_array'] = $for_nubarashen_array;
			$result['for_shengavit_array'] = $for_shengavit_array;
			$result['for_qanaqer_zeytun_array'] = $for_qanaqer_zeytun_array;
			$result['for_erevan_plus_5_array'] = $for_erevan_plus_5_array;
			$result['for_ayl_marzer_array'] = $for_ayl_marzer_array;
			$result['for_chchstvac_hasce_array'] = $for_chchstvac_hasce_array;
			$result['for_kotayq_array'] = $for_kotayq_array;
			$result['for_lori_array'] = $for_lori_array;
			$result['for_tavush_array'] = $for_tavush_array;
			$result['for_syunik_array'] = $for_syunik_array;
			$result['for_vayoc_dzor_array'] = $for_vayoc_dzor_array;
			$result['for_armavir_array'] = $for_armavir_array;
			$result['for_shirak_array'] = $for_shirak_array;
			$result['for_ararat_array'] = $for_ararat_array;
			$result['for_aragatsotn_array'] = $for_aragatsotn_array;
			$result['for_gexarquniq_array'] = $for_gexarquniq_array;
			$result['for_all_regions_array'] = $for_all_regions_array;
			$result['for_all_orders'] = $for_all_orders;
			$result['for_show_dates'] = $for_show_dates;
			$result['for_type_of_chart'] = 'receiver_subregion';
			print json_encode($result);die;
		}
		else if($type == 'orders_count'){
			$from_date = $_REQUEST['from_date'];
			$current_payment_status = $_REQUEST['current_payment_status'];
		    $to_date = $_REQUEST['to_date'];
		    $operator_filter = $_REQUEST['operator_filter'];
		    $sql = "SELECT * from rg_orders";
		    if(!empty($from_date)){
		    	$sql.= " where created_date >= '" . $from_date . "'";
		    }
		    if(!empty($to_date)){
		    	$sql.= " AND created_date <= '" . $to_date . "'";
		    }
		    if($operator_filter != 'all' ){
				$for_operator_sql = " AND operator = '" . $operator_filter ."'";
		    	$sql.= " AND operator = '" . $operator_filter ."'";
		    }
		    $current_payment_status_sql = '';
		    if($current_payment_status == 'paid'){
		    	$current_payment_status_sql = " and delivery_status IN (1,3,6,7,11,12,13,14) ";
		    }
		    else if($current_payment_status == 'unpaid'){
		    	$current_payment_status_sql = " and delivery_status IN (2,4,5,8,9,10) ";
		    }
			$orders = getwayConnect::getwayData($sql);
			$for_checking = [];
			$for_show_dates = [];
			foreach( $orders as $key => $value ){
				if (!in_array($value['created_date'], $for_checking)) {
					$for_checking[]= $value['created_date'];
					$for_show_dates[] = [$value['created_date']];
				}
			}
			$for_paid = [];
			$for_unpaid = [];
			$for_all_orders = [];
			foreach($for_checking as $key=>$value){
				$for_ment_all_paid = [];
				$for_ment_all_unpaid = [];
				if($operator_filter != 'all'){
		    		if($current_payment_status == 'paid'){
						$for_ment_all_paid = getwayConnect::getwayData("SELECT * FROM rg_orders where created_date = '" . $value . "' and operator= '" .$operator_filter."' " . $current_payment_status_sql);
		    		}
		    		else if($current_payment_status == 'unpaid'){
						$for_ment_all_unpaid = getwayConnect::getwayData("SELECT * FROM rg_orders where created_date = '" . $value . "' and operator= '" .$operator_filter."' " . $current_payment_status_sql);
		    		}
		    		else{
						$for_ment_all_paid = getwayConnect::getwayData("SELECT * FROM rg_orders where created_date = '" . $value . "' and operator= '" .$operator_filter."' " . " and delivery_status IN (1,3,6,7,11,12,13,14) ");
						$for_ment_all_unpaid = getwayConnect::getwayData("SELECT * FROM rg_orders where created_date = '" . $value . "' and operator= '" .$operator_filter."' " . "and delivery_status IN (2,4,5,8,9,10)");
		    		}
				}
				else{
					if($current_payment_status == 'paid'){
						$for_ment_all_paid = getwayConnect::getwayData("SELECT * FROM rg_orders where created_date = '" . $value . "' " . $current_payment_status_sql);
		    		}
		    		else if($current_payment_status == 'unpaid'){
						$for_ment_all_unpaid = getwayConnect::getwayData("SELECT * FROM rg_orders where created_date = '" . $value . "' " . $current_payment_status_sql);
		    		}
		    		else{
						$for_ment_all_paid = getwayConnect::getwayData("SELECT * FROM rg_orders where created_date = '" . $value . "' " . " and delivery_status IN (1,3,6,7,11,12,13,14) ");
						$for_ment_all_unpaid = getwayConnect::getwayData("SELECT * FROM rg_orders where created_date = '" . $value . "' " . "and delivery_status IN (2,4,5,8,9,10)");
		    		}
				}
				if(!empty($for_ment_all_paid)){
					$for_paid[] = count($for_ment_all_paid);
				}
				else{
					$for_paid[] = 0;
				}
				if(!empty($for_ment_all_unpaid)){
					$for_unpaid[] = count($for_ment_all_unpaid);
				}
				else{
					$for_unpaid[] = 0;
				}
			}
			$result = [];
			$result['paid'] = $for_paid;
			$result['unpaid'] = $for_unpaid;
			$result['for_show_dates'] = $for_show_dates;
			$result['for_type_of_chart'] = 'orders_count';
			print json_encode($result);die;
		}
		else if($type == 'delivery_time'){
			$from_date = $_REQUEST['from_date'];
			$current_payment_status = $_REQUEST['current_payment_status'];
		    $to_date = $_REQUEST['to_date'];
		    $operator_filter = $_REQUEST['operator_filter'];
		    $sql = "SELECT * from rg_orders";
		    if(!empty($from_date)){
		    	$sql.= " where created_date >= '" . $from_date . "'";
		    }
		    if(!empty($to_date)){
		    	$sql.= " AND created_date <= '" . $to_date . "'";
		    }
		    if($operator_filter != 'all' ){
				$for_operator_sql = " AND operator = '" . $operator_filter ."'";
		    	$sql.= " AND operator = '" . $operator_filter ."'";
		    }
		    $current_payment_status_sql = '';
		    if($current_payment_status == 'paid'){
		    	$current_payment_status_sql = " and ( delivery_status = 1 or delivery_status = 3 or delivery_status = 6 or delivery_status = 7 or delivery_status = 11 or delivery_status = 12 or delivery_status = 13 or delivery_status = 14 ) ";
		    }
		    else if($current_payment_status == 'unpaid'){
		    	$current_payment_status_sql = " and ( delivery_status = 2 or delivery_status = 4 or delivery_status = 5 or delivery_status = 8 or delivery_status = 9 or delivery_status = 10) ";
		    }
			$orders = getwayConnect::getwayData($sql);
			$for_checking = [];
			$for_show_dates = [];
			foreach( $orders as $key => $value ){
				if (!in_array($value['created_date'], $for_checking)) {
					$for_checking[]= $value['created_date'];
					$for_show_dates[] = [$value['created_date']];
				}
			}
			$for_8_10_array = [];
			$for_9_12_array = [];
			$for_12_15_array = [];
			$for_15_18_array = [];
			$for_18_21_array = [];
			$for_21_24_array = [];
			$for_00_00_array = [];
			$for_00_9_array = [];
			$for_8_15_array = [];
			$for_15_19_array = [];
			$for_19_00_array = [];
			$for_all_orders = [];
			foreach($for_checking as $key=>$value){
				if($operator_filter != 'all'){
					$all_orders = getwayConnect::getwayData("SELECT COUNT(*) FROM rg_orders where created_date = '" . $value . "'" . $current_payment_status_sql);
					$order_8_10 = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_time = '1' and created_date = '" . $value . "' and operator= '" .$operator_filter."' " . $current_payment_status_sql);
					$order_9_12 = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_time = '2' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql);
					$order_12_15 = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_time = '3' and created_date = '" . $value . "' and operator= '" . $operator_filter."' " . $current_payment_status_sql);
					$order_15_18 = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_time = '4' and created_date = '" . $value . "' and operator = '" . $operator_filter ."'" . $current_payment_status_sql);
					$order_18_21 = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_time = '5' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql);
					$order_21_24 = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_time = '6' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$order_00_00 = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_time = '7' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$order_00_9 = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_time = '8' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$order_8_15 = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_time = '9' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$order_15_19 = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_time = '10' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$order_19_00 = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_time = '11' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
				}
				else{
					$all_orders = getwayConnect::getwayData("SELECT COUNT(*) FROM rg_orders where created_date = '" . $value . "' " . $current_payment_status_sql);
					$order_8_10 = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_time = '1' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$order_9_12 = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_time = '2' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$order_12_15 = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_time = '3' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$order_15_18 = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_time = '4' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$order_18_21 = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_time = '5' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$order_21_24 = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_time = '6' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$order_00_00 = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_time = '7' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$order_00_9 = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_time = '8' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$order_8_15 = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_time = '9' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$order_15_19 = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_time = '10' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$order_19_00 = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_time = '11' and created_date = '" . $value . "' " . $current_payment_status_sql);
				}
				if(!empty($order_8_10)){
					$for_8_10_array[] = count($order_8_10);
				}
				else{
					$for_8_10_array[] = 0;
				}
				if(!empty($all_orders)){
					$for_all_orders[] = $all_orders[0][0];
				}
				else{
					$for_all_orders[] = 0;
				}
				if(!empty($order_9_12)){
					$for_9_12_array[] = count($order_9_12);
				}
				else{
					$for_9_12_array[] = 0;
				}
				if(!empty($order_12_15)){
					$for_12_15_array[] = count($order_12_15);
				}
				else{
					$for_12_15_array[] = 0;
				}
				if(!empty($order_15_18)){
					$for_15_18_array[] = count($order_15_18);
				}
				else{
					$for_15_18_array[] = 0;
				}
				if(!empty($order_18_21)){
					$for_18_21_array[] = count($order_18_21);
				}
				else{
					$for_18_21_array[] = 0;
				}
				if(!empty($order_21_24)){
					$for_21_24_array[] = count($order_21_24);
				}
				else{
					$for_21_24_array[] = 0;
				}
				if(!empty($order_00_00)){
					$for_00_00_array[] = count($order_00_00);
				}
				else{
					$for_00_00_array[] = 0;
				}
				if(!empty($order_00_9)){
					$for_00_9_array[] = count($order_00_9);
				}
				else{
					$for_00_9_array[] = 0;
				}
				if(!empty($order_8_15)){
					$for_8_15_array[] = count($order_8_15);
				}
				else{
					$for_8_15_array[] = 0;
				}
				if(!empty($order_15_19)){
					$for_15_19_array[] = count($order_15_19);
				}
				else{
					$for_15_19_array[] = 0;
				}
				if(!empty($order_19_00)){
					$for_19_00_array[] = count($order_19_00);
				}
				else{
					$for_19_00_array[] = 0;
				}
			}
			$result = [];
			$result['for_8_10_array'] = $for_8_10_array;
			$result['for_9_12_array'] = $for_9_12_array;
			$result['for_12_15_array'] = $for_12_15_array;
			$result['for_15_18_array'] = $for_15_18_array;
			$result['for_18_21_array'] = $for_18_21_array;
			$result['for_21_24_array'] = $for_21_24_array;
			$result['for_00_00_array'] = $for_00_00_array;
			$result['for_00_9_array'] = $for_00_9_array;
			$result['for_8_15_array'] = $for_8_15_array;
			$result['for_15_19_array'] = $for_15_19_array;
			$result['for_19_00_array'] = $for_19_00_array;
			$result['for_all_orders'] = $for_all_orders;
			$result['for_show_dates'] = $for_show_dates;
			$result['for_type_of_chart'] = 'delivery_time';
			print json_encode($result);die;
		}
		else if($type == 'sale_point'){
			$from_date = $_REQUEST['from_date'];
			$current_payment_status = $_REQUEST['current_payment_status'];
		    $to_date = $_REQUEST['to_date'];
		    $operator_filter = $_REQUEST['operator_filter'];
		    $sql = "SELECT * from rg_orders";
		    if(!empty($from_date)){
		    	$sql.= " where created_date >= '" . $from_date . "'";
		    }
		    if(!empty($to_date)){
		    	$sql.= " AND created_date <= '" . $to_date . "'";
		    }
		    if($operator_filter != 'all' ){
				$for_operator_sql = " AND operator = '" . $operator_filter ."'";
		    	$sql.= " AND operator = '" . $operator_filter ."'";
		    }
		    $current_payment_status_sql = '';
		    if($current_payment_status == 'paid'){
		    	$current_payment_status_sql = " and ( delivery_status = 1 or delivery_status = 3 or delivery_status = 6 or delivery_status = 7 or delivery_status = 11 or delivery_status = 12 or delivery_status = 13 or delivery_status = 14 ) ";
		    }
		    else if($current_payment_status == 'unpaid'){
		    	$current_payment_status_sql = " and ( delivery_status = 2 or delivery_status = 4 or delivery_status = 5 or delivery_status = 8 or delivery_status = 9 or delivery_status = 10) ";
		    }
			$orders = getwayConnect::getwayData($sql);
			$for_checking = [];
			$for_show_dates = [];
			foreach( $orders as $key => $value ){
				if (!in_array($value['created_date'], $for_checking)) {
					$for_checking[]= $value['created_date'];
					$for_show_dates[] = [$value['created_date']];
				}
			}
			$sql_flowers_armenia_internation_group = 'and ( sell_point = "2" or sell_point = "14" or sell_point = "6" or sell_point = "3" or sell_point = "5" or sell_point = "7" or sell_point = "8") ';
			$sql_flowers_local_market_group = 'and ( sell_point = "13" or sell_point = "18" or sell_point = "20" or sell_point = "18") ';
			$sql_landing_pages_by_generator = 'and sell_point = "17" ';

			$for_flowers_armenia_com_array = [];
			$for_giftsArmenia_info_array = [];
			$for_anahit_am_array = [];
			$for_flowers_armenia_am_array = [];
			$for_regard_Travel_net_array = [];
			$for_flowers_barcelona_com_array = [];
			$for_buy_am_array = [];
			$for_sas_am_array = [];
			$for_menu_am_array = [];
			$for_rus_buket_ru_array = [];
			$for_yes_ua_array = [];
			$for_charlotte_ru_array = [];
			$for_nevabuket_ru_array = [];
			$for_teleflora_com_mt_array = [];
			$for_megaflowers_ru_array = [];
			$for_edelweiss_service_ru_array = [];
			$for_flora2000_ru_array = [];
			$for_flowwow_com_array = [];
			$for_europeanflora_com_array = [];
			$for_flowersussr_com_array = [];
			$for_myflowers_gr_array = [];
			$for_cyber_florist_ru_array = [];
			$for_crossroad_com_array = [];
			$for_flowers_tehran_com_array = [];
			$for_flowers_sib_ru_array = [];
			$for_myglobalflowers_com_array = [];
			$for_grandflora_ru_array = [];
			$for_heart_in_flowers_ru_array = [];
			$for_gemoji_array = [];
			$for_armenia_flowers_com_array = [];
			$for_ayl_tarberak_array = [];
			$for_flowers_to_armenia_info_array = [];
			$for_gifts_to_armenia_info_array = [];
			$for_flowers_armenia_info_array = [];
			$for_gifts_armenia_info_array = [];
			$for_flowers_Palace_ru_array = [];
			$for_flowers_paris_ru_array = [];
			$for_flowers_paris_fr_array = [];
			$for_mamaflora_ru_array = [];
			$for_flowersarmenia_info_array = [];
			$for_flowers_armenia_us_array = [];
			$for_promo_flower_com_array = [];
			$for_anahit_flowers_com_array = [];
			$for_regard_flowers_com_array = [];
			$for_flowers_armenia_internation_orders = [];
			$for_flowers_local_market_group_orders = [];
			$for_landing_pages_by_generator_orders = [];
			$for_all_orders = [];
			foreach($for_checking as $key=>$value){
				if($operator_filter != 'all'){
					$all_orders = getwayConnect::getwayData("SELECT COUNT(*) FROM rg_orders where created_date = '" . $value . "'" . $current_payment_status_sql);
					$flowers_armenia_internation_orders = getwayConnect::getwayData("SELECT COUNT(*) FROM rg_orders where created_date = '" . $value . "' " . $sql_flowers_armenia_internation_group . " " . $current_payment_status_sql);
					$flowers_local_market_group_orders = getwayConnect::getwayData("SELECT COUNT(*) FROM rg_orders where created_date = '" . $value . "' " . $sql_flowers_local_market_group . " " . $current_payment_status_sql);
					$landing_pages_by_generator_orders = getwayConnect::getwayData("SELECT COUNT(*) FROM rg_orders where created_date = '" . $value . "' " . $sql_landing_pages_by_generator . " " . $current_payment_status_sql);
					$order_flowers_armenia_com = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '2' and created_date = '" . $value . "' and operator= '" .$operator_filter."' " . $current_payment_status_sql);
					$order_giftsArmenia_info = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '3' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql);
					$order_anahit_am = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '13' and created_date = '" . $value . "' and operator= '" . $operator_filter."' " . $current_payment_status_sql);
					$order_flowers_armenia_am = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '18' and created_date = '" . $value . "' and operator = '" . $operator_filter ."'" . $current_payment_status_sql);
					$order_regard_Travel_net = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '22' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql);
					$order_flowers_barcelona_com = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '23' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$order_buy_am = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '16' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$order_sas_am = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '45' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$order_menu_am = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '15' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$order_rus_buket_ru = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '24' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$order_yes_ua = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '25' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$order_charlotte_ru = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '26' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$order_nevabuket_ru = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '27' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$order_teleflora_mt = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '28' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$order_megaflowers_ru = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '29' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$order_edelweiss_service_ru = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '30' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$order_flora2000_ru = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '31' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$order_flowwow_com = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '32' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$order_europeanflora_com = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '33' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$order_flowersussr_com = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '34' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$order_myflowers_gr = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '35' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$order_cyber_florist_ru = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '36' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$order_crossroad_com = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '37' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$order_flowers_tehran_com = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '38' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$order_flowers_sib_ru = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '39' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$order_myglobalflowers_com = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '40' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$order_grandflora_ru = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '41' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$order_heart_in_flowers_ru = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '42' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$order_gemoji = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '44' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$order_armenia_flowers_com = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '21' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$order_ayl_tarberak = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '1' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$order_flowers_to_armenia_info = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '4' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$order_gifts_to_armenia_info = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '5' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$order_flowers_armenia_info = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '6' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$order_gifts_armenia_info = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '7' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$order_flowers_Palace_ru = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '8' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$order_flowers_paris_ru = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '9' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$order_flowers_paris_fr = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '10' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$order_mamaflora_ru = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '11' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$order_flowersarmenia_info = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '12' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$order_flowers_armenia_us = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '14' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$order_promo_flower_com = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '17' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$order_anahit_flowers_com = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '19' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$order_regard_flowers_com = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '20' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
				}
				else{
					$all_orders = getwayConnect::getwayData("SELECT COUNT(*) FROM rg_orders where created_date = '" . $value . "' " . $current_payment_status_sql);
					$flowers_armenia_internation_orders = getwayConnect::getwayData("SELECT COUNT(*) FROM rg_orders where created_date = '" . $value . "' " . $sql_flowers_armenia_internation_group . " " . $current_payment_status_sql);
					$flowers_local_market_group_orders = getwayConnect::getwayData("SELECT COUNT(*) FROM rg_orders where created_date = '" . $value . "' " . $sql_flowers_local_market_group . " " . $current_payment_status_sql);
					$landing_pages_by_generator_orders = getwayConnect::getwayData("SELECT COUNT(*) FROM rg_orders where created_date = '" . $value . "' " . $sql_landing_pages_by_generator . " " . $current_payment_status_sql);
					$order_flowers_armenia_com = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '2' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$order_giftsArmenia_info = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '3' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$order_anahit_am = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '13' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$order_flowers_armenia_am = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '18' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$order_regard_Travel_net = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '22' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$order_flowers_barcelona_com = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '23' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$order_buy_am = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '16' and created_date = '" . $value . "' " . $current_payment_status_sql );
					$order_sas_am = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '45' and created_date = '" . $value . "' " . $current_payment_status_sql );
					$order_menu_am = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '15' and created_date = '" . $value . "' " . $current_payment_status_sql );
					$order_rus_buket_ru = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '24' and created_date = '" . $value . "' " . $current_payment_status_sql );
					$order_yes_ua = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '25' and created_date = '" . $value . "' " . $current_payment_status_sql );
					$order_charlotte_ru = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '26' and created_date = '" . $value . "' " . $current_payment_status_sql );
					$order_nevabuket_ru = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '27' and created_date = '" . $value . "' " . $current_payment_status_sql );
					$order_teleflora_mt = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '28' and created_date = '" . $value . "' " . $current_payment_status_sql );
					$order_megaflowers_ru = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '29' and created_date = '" . $value . "' " . $current_payment_status_sql );
					$order_edelweiss_service_ru = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '30' and created_date = '" . $value . "' " . $current_payment_status_sql );
					$order_flora2000_ru = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '31' and created_date = '" . $value . "' " . $current_payment_status_sql );
					$order_flowwow_com = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '32' and created_date = '" . $value . "' " . $current_payment_status_sql );
					$order_europeanflora_com = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '33' and created_date = '" . $value . "' " . $current_payment_status_sql );
					$order_flowersussr_com = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '34' and created_date = '" . $value . "' " . $current_payment_status_sql );
					$order_myflowers_gr = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '35' and created_date = '" . $value . "' " . $current_payment_status_sql );
					$order_cyber_florist_ru = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '36' and created_date = '" . $value . "' " . $current_payment_status_sql );
					$order_crossroad_com = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '37' and created_date = '" . $value . "' " . $current_payment_status_sql );
					$order_flowers_tehran_com = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '38' and created_date = '" . $value . "' " . $current_payment_status_sql );
					$order_flowers_sib_ru = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '39' and created_date = '" . $value . "' " . $current_payment_status_sql );
					$order_myglobalflowers_com = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '40' and created_date = '" . $value . "' " . $current_payment_status_sql );
					$order_grandflora_ru = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '41' and created_date = '" . $value . "' " . $current_payment_status_sql );
					$order_heart_in_flowers_ru = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '42' and created_date = '" . $value . "' " . $current_payment_status_sql );
					$order_gemoji = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '44' and created_date = '" . $value . "' " . $current_payment_status_sql );
					$order_armenia_flowers_com = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '21' and created_date = '" . $value . "' " . $current_payment_status_sql );
					$order_ayl_tarberak = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '1' and created_date = '" . $value . "' " . $current_payment_status_sql );
					$order_flowers_to_armenia_info = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '4' and created_date = '" . $value . "' " . $current_payment_status_sql );
					$order_gifts_to_armenia_info = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '5' and created_date = '" . $value . "' " . $current_payment_status_sql );
					$order_flowers_armenia_info = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '6' and created_date = '" . $value . "' " . $current_payment_status_sql );
					$order_gifts_armenia_info = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '7' and created_date = '" . $value . "' " . $current_payment_status_sql );
					$order_flowers_Palace_ru = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '8' and created_date = '" . $value . "' " . $current_payment_status_sql );
					$order_flowers_paris_ru = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '9' and created_date = '" . $value . "' " . $current_payment_status_sql );
					$order_flowers_paris_fr = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '10' and created_date = '" . $value . "' " . $current_payment_status_sql );
					$order_mamaflora_ru = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '11' and created_date = '" . $value . "' " . $current_payment_status_sql );
					$order_flowersarmenia_info = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '12' and created_date = '" . $value . "' " . $current_payment_status_sql );
					$order_flowers_armenia_us = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '14' and created_date = '" . $value . "' " . $current_payment_status_sql );
					$order_promo_flower_com = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '17' and created_date = '" . $value . "' " . $current_payment_status_sql );
					$order_anahit_flowers_com = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '19' and created_date = '" . $value . "' " . $current_payment_status_sql );
					$order_regard_flowers_com = getwayConnect::getwayData("SELECT * FROM rg_orders where sell_point = '20' and created_date = '" . $value . "' " . $current_payment_status_sql );
				}
				if(!empty($order_flowers_armenia_com)){
					$for_flowers_armenia_com_array[] = count($order_flowers_armenia_com);
				}
				else{
					$for_flowers_armenia_com_array[] = 0;
				}
				if(!empty($all_orders)){
					$for_all_orders[] = $all_orders[0][0];
				}
				else{
					$for_all_orders[] = 0;
				}
				if(!empty($flowers_armenia_internation_orders)){
					$for_flowers_armenia_internation_orders[] = $flowers_armenia_internation_orders[0][0];
				}
				else{
					$for_flowers_armenia_internation_orders[] = 0;
				}
				if(!empty($flowers_local_market_group_orders)){
					$for_flowers_local_market_group_orders[] = $flowers_local_market_group_orders[0][0];
				}
				else{
					$for_flowers_local_market_group_orders[] = 0;
				}
				if(!empty($landing_pages_by_generator_orders)){
					$for_landing_pages_by_generator_orders[] = $landing_pages_by_generator_orders[0][0];
				}
				else{
					$for_landing_pages_by_generator_orders[] = 0;
				}
				if(!empty($order_giftsArmenia_info)){
					$for_giftsArmenia_info_array[] = count($order_giftsArmenia_info);
				}
				else{
					$for_giftsArmenia_info_array[] = 0;
				}
				if(!empty($order_anahit_am)){
					$for_anahit_am_array[] = count($order_anahit_am);
				}
				else{
					$for_anahit_am_array[] = 0;
				}
				if(!empty($order_flowers_armenia_am)){
					$for_flowers_armenia_am_array[] = count($order_flowers_armenia_am);
				}
				else{
					$for_flowers_armenia_am_array[] = 0;
				}
				if(!empty($order_regard_Travel_net)){
					$for_regard_Travel_net_array[] = count($order_regard_Travel_net);
				}
				else{
					$for_regard_Travel_net_array[] = 0;
				}
				if(!empty($order_flowers_barcelona_com)){
					$for_flowers_barcelona_com_array[] = count($order_flowers_barcelona_com);
				}
				else{
					$for_flowers_barcelona_com_array[] = 0;
				}
				if(!empty($order_buy_am)){
					$for_buy_am_array[] = count($order_buy_am);
				}
				else{
					$for_buy_am_array[] = 0;
				}
				if(!empty($order_sas_am)){
					$for_sas_am_array[] = count($order_sas_am);
				}
				else{
					$for_sas_am_array[] = 0;
				}
				if(!empty($order_menu_am)){
					$for_menu_am_array[] = count($order_menu_am);
				}
				else{
					$for_menu_am_array[] = 0;
				}
				if(!empty($order_rus_buket_ru)){
					$for_rus_buket_ru_array[] = count($order_rus_buket_ru);
				}
				else{
					$for_rus_buket_ru_array[] = 0;
				}
				if(!empty($order_yes_ua)){
					$for_yes_ua_array[] = count($order_yes_ua);
				}
				else{
					$for_yes_ua_array[] = 0;
				}
				if(!empty($order_charlotte_ru)){
					$for_charlotte_ru_array[] = count($order_charlotte_ru);
				}
				else{
					$for_charlotte_ru_array[] = 0;
				}
				if(!empty($order_nevabuket_ru)){
					$for_nevabuket_ru_array[] = count($order_nevabuket_ru);
				}
				else{
					$for_nevabuket_ru_array[] = 0;
				}
				if(!empty($order_teleflora_mt)){
					$for_teleflora_com_mt_array[] = count($order_teleflora_mt);
				}
				else{
					$for_teleflora_com_mt_array[] = 0;
				}
				if(!empty($order_megaflowers_ru)){
					$for_megaflowers_ru_array[] = count($order_megaflowers_ru);
				}
				else{
					$for_megaflowers_ru_array[] = 0;
				}
				if(!empty($order_edelweiss_service_ru)){
					$for_edelweiss_service_ru_array[] = count($order_edelweiss_service_ru);
				}
				else{
					$for_edelweiss_service_ru_array[] = 0;
				}
				if(!empty($order_flora2000_ru)){
					$for_flora2000_ru_array[] = count($order_flora2000_ru);
				}
				else{
					$for_flora2000_ru_array[] = 0;
				}
				if(!empty($order_flowwow_com)){
					$for_flowwow_com_array[] = count($order_flowwow_com);
				}
				else{
					$for_flowwow_com_array[] = 0;
				}
				if(!empty($order_europeanflora_com)){
					$for_europeanflora_com_array[] = count($order_europeanflora_com);
				}
				else{
					$for_europeanflora_com_array[] = 0;
				}
				if(!empty($order_flowersussr_com)){
					$for_flowersussr_com_array[] = count($order_flowersussr_com);
				}
				else{
					$for_flowersussr_com_array[] = 0;
				}
				if(!empty($order_myflowers_gr)){
					$for_myflowers_gr_array[] = count($order_myflowers_gr);
				}
				else{
					$for_myflowers_gr_array[] = 0;
				}
				if(!empty($order_cyber_florist_ru)){
					$for_cyber_florist_ru_array[] = count($order_cyber_florist_ru);
				}
				else{
					$for_cyber_florist_ru_array[] = 0;
				}
				if(!empty($order_crossroad_com)){
					$for_crossroad_com_array[] = count($order_crossroad_com);
				}
				else{
					$for_crossroad_com_array[] = 0;
				}
				if(!empty($order_flowers_tehran_com)){
					$for_flowers_tehran_com_array[] = count($order_flowers_tehran_com);
				}
				else{
					$for_flowers_tehran_com_array[] = 0;
				}
				if(!empty($order_flowers_sib_ru)){
					$for_flowers_sib_ru_array[] = count($order_flowers_sib_ru);
				}
				else{
					$for_flowers_sib_ru_array[] = 0;
				}
				if(!empty($order_myglobalflowers_com)){
					$for_myglobalflowers_com_array[] = count($order_myglobalflowers_com);
				}
				else{
					$for_myglobalflowers_com_array[] = 0;
				}
				if(!empty($order_grandflora_ru)){
					$for_grandflora_ru_array[] = count($order_grandflora_ru);
				}
				else{
					$for_grandflora_ru_array[] = 0;
				}
				if(!empty($order_heart_in_flowers_ru)){
					$for_heart_in_flowers_ru_array[] = count($order_heart_in_flowers_ru);
				}
				else{
					$for_heart_in_flowers_ru_array[] = 0;
				}
				if(!empty($order_gemoji)){
					$for_gemoji_array[] = count($order_gemoji);
				}
				else{
					$for_gemoji_array[] = 0;
				}
				if(!empty($order_armenia_flowers_com)){
					$for_armenia_flowers_com_array[] = count($order_armenia_flowers_com);
				}
				else{
					$for_armenia_flowers_com_array[] = 0;
				}
				if(!empty($order_ayl_tarberak)){
					$for_ayl_tarberak_array[] = count($order_ayl_tarberak);
				}
				else{
					$for_ayl_tarberak_array[] = 0;
				}
				if(!empty($order_flowers_to_armenia_info)){
					$for_flowers_to_armenia_info_array[] = count($order_flowers_to_armenia_info);
				}
				else{
					$for_flowers_to_armenia_info_array[] = 0;
				}
				if(!empty($order_gifts_to_armenia_info)){
					$for_gifts_to_armenia_info_array[] = count($order_gifts_to_armenia_info);
				}
				else{
					$for_gifts_to_armenia_info_array[] = 0;
				}
				if(!empty($order_flowers_armenia_info)){
					$for_flowers_armenia_info_array[] = count($order_flowers_armenia_info);
				}
				else{
					$for_flowers_armenia_info_array[] = 0;
				}
				if(!empty($order_gifts_armenia_info)){
					$for_gifts_armenia_info_array[] = count($order_gifts_armenia_info);
				}
				else{
					$for_gifts_armenia_info_array[] = 0;
				}
				if(!empty($order_flowers_Palace_ru)){
					$for_flowers_Palace_ru_array[] = count($order_flowers_Palace_ru);
				}
				else{
					$for_flowers_Palace_ru_array[] = 0;
				}
				if(!empty($order_flowers_paris_ru)){
					$for_flowers_paris_ru_array[] = count($order_flowers_paris_ru);
				}
				else{
					$for_flowers_paris_ru_array[] = 0;
				}
				if(!empty($order_flowers_paris_fr)){
					$for_flowers_paris_fr_array[] = count($order_flowers_paris_fr);
				}
				else{
					$for_flowers_paris_fr_array[] = 0;
				}
				if(!empty($order_mamaflora_ru)){
					$for_mamaflora_ru_array[] = count($order_mamaflora_ru);
				}
				else{
					$for_mamaflora_ru_array[] = 0;
				}
				if(!empty($order_flowersarmenia_info)){
					$for_flowersarmenia_info_array[] = count($order_flowersarmenia_info);
				}
				else{
					$for_flowersarmenia_info_array[] = 0;
				}
				if(!empty($order_flowers_armenia_us)){
					$for_flowers_armenia_us_array[] = count($order_flowers_armenia_us);
				}
				else{
					$for_flowers_armenia_us_array[] = 0;
				}
				if(!empty($order_promo_flower_com)){
					$for_promo_flower_com_array[] = count($order_promo_flower_com);
				}
				else{
					$for_promo_flower_com_array[] = 0;
				}
				if(!empty($order_anahit_flowers_com)){
					$for_anahit_flowers_com_array[] = count($order_anahit_flowers_com);
				}
				else{
					$for_anahit_flowers_com_array[] = 0;
				}
				if(!empty($order_regard_flowers_com)){
					$for_regard_flowers_com_array[] = count($order_regard_flowers_com);
				}
				else{
					$for_regard_flowers_com_array[] = 0;
				}
			}
			$result = [];
			$result['for_flowers_armenia_com_array'] = $for_flowers_armenia_com_array;
			$result['for_giftsArmenia_info_array'] = $for_giftsArmenia_info_array;
			$result['for_anahit_am_array'] = $for_anahit_am_array;
			$result['for_flowers_armenia_am_array'] = $for_flowers_armenia_am_array;
			$result['for_regard_Travel_net_array'] = $for_regard_Travel_net_array;
			$result['for_flowers_barcelona_com_array'] = $for_flowers_barcelona_com_array;
			$result['for_buy_am_array'] = $for_buy_am_array;
			$result['for_sas_am_array'] = $for_sas_am_array;
			$result['for_menu_am_array'] = $for_menu_am_array;
			$result['for_rus_buket_ru_array'] = $for_rus_buket_ru_array;
			$result['for_yes_ua_array'] = $for_yes_ua_array;
			$result['for_charlotte_ru_array'] = $for_charlotte_ru_array;
			$result['for_nevabuket_ru_array'] = $for_nevabuket_ru_array;
			$result['for_teleflora_com_mt_array'] = $for_teleflora_com_mt_array;
			$result['for_megaflowers_ru_array'] = $for_megaflowers_ru_array;
			$result['for_edelweiss_service_ru_array'] = $for_edelweiss_service_ru_array;
			$result['for_flora2000_ru_array'] = $for_flora2000_ru_array;
			$result['for_flowwow_com_array'] = $for_flowwow_com_array;
			$result['for_europeanflora_com_array'] = $for_europeanflora_com_array;
			$result['for_flowersussr_com_array'] = $for_flowersussr_com_array;
			$result['for_myflowers_gr_array'] = $for_myflowers_gr_array;
			$result['for_cyber_florist_ru_array'] = $for_cyber_florist_ru_array;
			$result['for_crossroad_com_array'] = $for_crossroad_com_array;
			$result['for_flowers_tehran_com_array'] = $for_flowers_tehran_com_array;
			$result['for_flowers_sib_ru_array'] = $for_flowers_sib_ru_array;
			$result['for_myglobalflowers_com_array'] = $for_myglobalflowers_com_array;
			$result['for_grandflora_ru_array'] = $for_grandflora_ru_array;
			$result['for_heart_in_flowers_ru_array'] = $for_heart_in_flowers_ru_array;
			$result['for_gemoji_array'] = $for_gemoji_array;
			$result['for_armenia_flowers_com_array'] = $for_armenia_flowers_com_array;
			$result['for_ayl_tarberak_array'] = $for_ayl_tarberak_array;
			$result['for_flowers_to_armenia_info_array'] = $for_flowers_to_armenia_info_array;
			$result['for_gifts_to_armenia_info_array'] = $for_gifts_to_armenia_info_array;
			$result['for_flowers_armenia_info_array'] = $for_flowers_armenia_info_array;
			$result['for_gifts_armenia_info_array'] = $for_gifts_armenia_info_array;
			$result['for_flowers_Palace_ru_array'] = $for_flowers_Palace_ru_array;
			$result['for_flowers_paris_ru_array'] = $for_flowers_paris_ru_array;
			$result['for_flowers_paris_fr_array'] = $for_flowers_paris_fr_array;
			$result['for_mamaflora_ru_array'] = $for_mamaflora_ru_array;
			$result['for_flowersarmenia_info_array'] = $for_flowersarmenia_info_array;
			$result['for_flowers_armenia_us_array'] = $for_flowers_armenia_us_array;
			$result['for_promo_flower_com_array'] = $for_promo_flower_com_array;
			$result['for_anahit_flowers_com_array'] = $for_anahit_flowers_com_array;
			$result['for_regard_flowers_com_array'] = $for_regard_flowers_com_array;
			$result['for_all_orders'] = $for_all_orders;
			$result['for_flowers_armenia_internation_orders'] = $for_flowers_armenia_internation_orders;
			$result['for_flowers_local_market_group_orders'] = $for_flowers_local_market_group_orders;
			$result['for_landing_pages_by_generator_orders'] = $for_landing_pages_by_generator_orders;
			$result['for_show_dates'] = $for_show_dates;
			$result['for_type_of_chart'] = 'sale_point';
			print json_encode($result);die;
		}
		else if($type == 'operators'){
			$from_date = $_REQUEST['from_date'];
			$current_payment_status = $_REQUEST['current_payment_status'];
		    $to_date = $_REQUEST['to_date'];
		    $sql = "SELECT * from rg_orders";
		    if(!empty($from_date)){
		    	$sql.= " where created_date >= '" . $from_date . "'";
		    }
		    if(!empty($to_date)){
		    	$sql.= " AND created_date <= '" . $to_date . "'";
		    }
		    $current_payment_status_sql = '';
		    if($current_payment_status == 'paid'){
		    	$current_payment_status_sql = " and ( delivery_status = 1 or delivery_status = 3 or delivery_status = 6 or delivery_status = 7 or delivery_status = 11 or delivery_status = 12 or delivery_status = 13 or delivery_status = 14 ) ";
		    }
		    else if($current_payment_status == 'unpaid'){
		    	$current_payment_status_sql = " and ( delivery_status = 2 or delivery_status = 4 or delivery_status = 5 or delivery_status = 8 or delivery_status = 9 or delivery_status = 10) ";
		    }
			$orders = getwayConnect::getwayData($sql);
			$for_checking = [];
			$for_show_dates = [];
			foreach( $orders as $key => $value ){
				if (!in_array($value['created_date'], $for_checking)) {
					$for_checking[]= $value['created_date'];
					$for_show_dates[] = [$value['created_date']];
				}
			}
			$for_gayane_array = [];
			$for_sona_array = [];
			$for_nara_array = [];
			$for_Anye_array = [];
			$for_Anush_array = [];
			$for_dev_operator_array = [];
			$for_Lyuda_array = [];
			$for_gayaneh_array = [];
			$for_RegAnna_array = [];
			$for_RegShushan_array = [];
			$for_lilit_array = [];
			$for_ruzan_array = [];
			$for_tatev_array = [];
			$for_liana_array = [];
			$for_mariam_array = [];
			$for_lia_array = [];
			$for_anna_array = [];
			$for_Arusik_array = [];
			$for_Anette_array = [];
			$for_Elen_array = [];
			$for_emma_array = [];
			$for_Heghine_array = [];
			$for_Sarah_array = [];
			$for_Ruzanna_array = [];
			$for_Alvina_array = [];
			$for_Kristina_array = [];
			$for_annab_array = [];
			$for_bulkuser_array = [];
			$for_arshan_array = [];
			$for_dev_travel_array = [];
			$for_Margarita_array = [];
			$for_Mari_array = [];
			$for_Elmira_array = [];
			$for_Satine_array = [];
			$for_Mery_array = [];
			$for_lusine_array = [];
			$for_elya_array = [];
			$for_Shushanik_array = [];
			$for_knarik_array = [];
			$for_siranush_array = [];
			$for_yeva_array = [];
			$for_lily_array = [];
			$for_christina_array = [];
			$for_sofi_array = [];
			$for_zara_array = [];
			$for_hasmik_array = [];
			$for_hranush_array = [];
			$for_all_orders = [];
			foreach($for_checking as $key=>$value){
				$all_orders = getwayConnect::getwayData("SELECT COUNT(*) FROM rg_orders where created_date = '" . $value . "'" . $current_payment_status_sql);
				$gayane_order = getwayConnect::getwayData("SELECT * FROM rg_orders where created_date = '" . $value . "' and operator= 'gayane' " . $current_payment_status_sql);
				$sona_order = getwayConnect::getwayData("SELECT * FROM rg_orders where created_date = '" . $value . "' and operator = 'sona' " . $current_payment_status_sql);
				$nara_order = getwayConnect::getwayData("SELECT * FROM rg_orders where created_date = '" . $value . "' and operator= 'nara' " . $current_payment_status_sql);
				$Anye_order = getwayConnect::getwayData("SELECT * FROM rg_orders where created_date = '" . $value . "' and operator = 'Anye' " . $current_payment_status_sql);
				$Anush_order = getwayConnect::getwayData("SELECT * FROM rg_orders where created_date = '" . $value . "' and operator = 'Anush' " . $current_payment_status_sql);
				$gayaneh_order = getwayConnect::getwayData("SELECT * FROM rg_orders where created_date = '" . $value . "' and operator = 'gayaneh' " . $current_payment_status_sql);
				$dev_operator_order = getwayConnect::getwayData("SELECT * FROM rg_orders where created_date = '" . $value . "' and operator = 'dev_operator' " . $current_payment_status_sql );
				$Lyuda_order = getwayConnect::getwayData("SELECT * FROM rg_orders where created_date = '" . $value . "' and operator = 'Lyuda' " . $current_payment_status_sql );
				$RegAnna_order = getwayConnect::getwayData("SELECT * FROM rg_orders where created_date = '" . $value . "' and operator = 'RegAnna' " . $current_payment_status_sql );
				$RegShushan_order = getwayConnect::getwayData("SELECT * FROM rg_orders where created_date = '" . $value . "' and operator = 'RegShushan' " . $current_payment_status_sql );
				$lilit_order = getwayConnect::getwayData("SELECT * FROM rg_orders where created_date = '" . $value . "' and operator = 'lilit' " . $current_payment_status_sql );
				$ruzan_order = getwayConnect::getwayData("SELECT * FROM rg_orders where created_date = '" . $value . "' and operator = 'ruzan' " . $current_payment_status_sql );
				$tatev_order = getwayConnect::getwayData("SELECT * FROM rg_orders where created_date = '" . $value . "' and operator = 'tatev' " . $current_payment_status_sql );
				$liana_order = getwayConnect::getwayData("SELECT * FROM rg_orders where created_date = '" . $value . "' and operator = 'liana' " . $current_payment_status_sql );
				$mariam_order = getwayConnect::getwayData("SELECT * FROM rg_orders where created_date = '" . $value . "' and operator = 'mariam' " . $current_payment_status_sql );
				$lia_order = getwayConnect::getwayData("SELECT * FROM rg_orders where created_date = '" . $value . "' and operator = 'lia' " . $current_payment_status_sql );
				$anna_order = getwayConnect::getwayData("SELECT * FROM rg_orders where created_date = '" . $value . "' and operator = 'anna' " . $current_payment_status_sql );
				$Arusik_order = getwayConnect::getwayData("SELECT * FROM rg_orders where created_date = '" . $value . "' and operator = 'Arusik' " . $current_payment_status_sql );
				$Anette_order = getwayConnect::getwayData("SELECT * FROM rg_orders where created_date = '" . $value . "' and operator = 'Anette' " . $current_payment_status_sql );
				$Elen_order = getwayConnect::getwayData("SELECT * FROM rg_orders where created_date = '" . $value . "' and operator = 'Elen' " . $current_payment_status_sql );
				$emma_order = getwayConnect::getwayData("SELECT * FROM rg_orders where created_date = '" . $value . "' and operator = 'emma' " . $current_payment_status_sql );
				$Heghine_order = getwayConnect::getwayData("SELECT * FROM rg_orders where created_date = '" . $value . "' and operator = 'Heghine' " . $current_payment_status_sql );
				$Sarah_order = getwayConnect::getwayData("SELECT * FROM rg_orders where created_date = '" . $value . "' and operator = 'Sarah' " . $current_payment_status_sql );
				$Ruzanna_order = getwayConnect::getwayData("SELECT * FROM rg_orders where created_date = '" . $value . "' and operator = 'Ruzanna' " . $current_payment_status_sql );
				$Alvina_order = getwayConnect::getwayData("SELECT * FROM rg_orders where created_date = '" . $value . "' and operator = 'Alvina' " . $current_payment_status_sql );
				$Kristina_order = getwayConnect::getwayData("SELECT * FROM rg_orders where created_date = '" . $value . "' and operator = 'Kristina' " . $current_payment_status_sql );
				$annab_order = getwayConnect::getwayData("SELECT * FROM rg_orders where created_date = '" . $value . "' and operator = 'annab' " . $current_payment_status_sql );
				$bulkuser_order = getwayConnect::getwayData("SELECT * FROM rg_orders where created_date = '" . $value . "' and operator = 'bulkuser' " . $current_payment_status_sql );
				$arshan_order = getwayConnect::getwayData("SELECT * FROM rg_orders where created_date = '" . $value . "' and operator = 'arshan' " . $current_payment_status_sql );
				$dev_travel_order = getwayConnect::getwayData("SELECT * FROM rg_orders where created_date = '" . $value . "' and operator = 'dev_travel' " . $current_payment_status_sql );
				$Margarita_order = getwayConnect::getwayData("SELECT * FROM rg_orders where created_date = '" . $value . "' and operator = 'Margarita' " . $current_payment_status_sql );
				$Mari_order = getwayConnect::getwayData("SELECT * FROM rg_orders where created_date = '" . $value . "' and operator = 'Mari' " . $current_payment_status_sql );
				$Elmira_order = getwayConnect::getwayData("SELECT * FROM rg_orders where created_date = '" . $value . "' and operator = 'Elmira' " . $current_payment_status_sql );
				$Satine_order = getwayConnect::getwayData("SELECT * FROM rg_orders where created_date = '" . $value . "' and operator = 'Satine' " . $current_payment_status_sql );
				$Mery_order = getwayConnect::getwayData("SELECT * FROM rg_orders where created_date = '" . $value . "' and operator = 'Mery' " . $current_payment_status_sql );
				$lusine_order = getwayConnect::getwayData("SELECT * FROM rg_orders where created_date = '" . $value . "' and operator = 'lusine' " . $current_payment_status_sql );
				$elya_order = getwayConnect::getwayData("SELECT * FROM rg_orders where created_date = '" . $value . "' and operator = 'elya' " . $current_payment_status_sql );
				$Shushanik_order = getwayConnect::getwayData("SELECT * FROM rg_orders where created_date = '" . $value . "' and operator = 'Shushanik' " . $current_payment_status_sql );
				$knarik_order = getwayConnect::getwayData("SELECT * FROM rg_orders where created_date = '" . $value . "' and operator = 'knarik' " . $current_payment_status_sql );
				$siranush_order = getwayConnect::getwayData("SELECT * FROM rg_orders where created_date = '" . $value . "' and operator = 'siranush' " . $current_payment_status_sql );
				$yeva_order = getwayConnect::getwayData("SELECT * FROM rg_orders where created_date = '" . $value . "' and operator = 'yeva' " . $current_payment_status_sql );
				$lily_order = getwayConnect::getwayData("SELECT * FROM rg_orders where created_date = '" . $value . "' and operator = 'lily' " . $current_payment_status_sql );
				$christina_order = getwayConnect::getwayData("SELECT * FROM rg_orders where created_date = '" . $value . "' and operator = 'christina' " . $current_payment_status_sql );
				$sofi_order = getwayConnect::getwayData("SELECT * FROM rg_orders where created_date = '" . $value . "' and operator = 'sofi' " . $current_payment_status_sql );
				$zara_order = getwayConnect::getwayData("SELECT * FROM rg_orders where created_date = '" . $value . "' and operator = 'zara' " . $current_payment_status_sql );
				$hasmik_order = getwayConnect::getwayData("SELECT * FROM rg_orders where created_date = '" . $value . "' and operator = 'hasmik' " . $current_payment_status_sql );
				$hranush_order = getwayConnect::getwayData("SELECT * FROM rg_orders where created_date = '" . $value . "' and operator = 'hranush' " . $current_payment_status_sql );
			if(!empty($gayane_order)){
				$for_gayane_array[] = count($gayane_order);
			}
			else{
				$for_gayane_array[] = 0;
			}
			if(!empty($all_orders)){
				$for_all_orders[] = $all_orders[0][0];
			}
			else{
				$for_all_orders[] = 0;
			}
			if(!empty($sona_order)){
				$for_sona_array[] = count($sona_order);
			}
			else{
				$for_sona_array[] = 0;
			}
			if(!empty($nara_order)){
				$for_nara_array[] = count($nara_order);
			}
			else{
				$for_nara_array[] = 0;
			}
			if(!empty($Anye_order)){
				$for_Anye_array[] = count($Anye_order);
			}
			else{
				$for_Anye_array[] = 0;
			}
			if(!empty($Anush_order)){
				$for_Anush_array[] = count($Anush_order);
			}
			else{
				$for_Anush_array[] = 0;
			}
			if(!empty($dev_operator_order)){
				$for_dev_operator_array[] = count($dev_operator_order);
			}
			else{
				$for_dev_operator_array[] = 0;
			}
			if(!empty($Lyuda_order)){
				$for_Lyuda_array[] = count($Lyuda_order);
			}
			else{
				$for_Lyuda_array[] = 0;
			}
			if(!empty($gayaneh_order)){
				$for_gayaneh_array[] = count($gayaneh_order);
			}
			else{
				$for_gayaneh_array[] = 0;
			}
			if(!empty($RegAnna_order)){
				$for_RegAnna_array[] = count($RegAnna_order);
			}
			else{
				$for_RegAnna_array[] = 0;
			}
			if(!empty($RegShushan_order)){
				$for_RegShushan_array[] = count($RegShushan_order);
			}
			else{
				$for_RegShushan_array[] = 0;
			}
			if(!empty($lilit_order)){
				$for_lilit_array[] = count($lilit_order);
			}
			else{
				$for_lilit_array[] = 0;
			}
			if(!empty($ruzan_order)){
				$for_ruzan_array[] = count($ruzan_order);
			}
			else{
				$for_ruzan_array[] = 0;
			}
			if(!empty($tatev_order)){
				$for_tatev_array[] = count($tatev_order);
			}
			else{
				$for_tatev_array[] = 0;
			}
			if(!empty($lia_order)){
				$for_lia_array[] = count($lia_order);
			}
			else{
				$for_lia_array[] = 0;
			}
			if(!empty($anna_order)){
				$for_anna_array[] = count($anna_order);
			}
			else{
				$for_anna_array[] = 0;
			}
			if(!empty($liana_order)){
				$for_liana_array[] = count($liana_order);
			}
			else{
				$for_liana_array[] = 0;
			}
			if(!empty($Arusik_order)){
				$for_Arusik_array[] = count($Arusik_order);
			}
			else{
				$for_Arusik_array[] = 0;
			}
			if(!empty($Anette_order)){
				$for_Anette_array[] = count($Anette_order);
			}
			else{
				$for_Anette_array[] = 0;
			}
			if(!empty($Elen_order)){
				$for_Elen_array[] = count($Elen_order);
			}
			else{
				$for_Elen_array[] = 0;
			}
			if(!empty($emma_order)){
				$for_emma_array[] = count($emma_order);
			}
			else{
				$for_emma_array[] = 0;
			}
			if(!empty($Heghine_order)){
				$for_Heghine_array[] = count($Heghine_order);
			}
			else{
				$for_Heghine_array[] = 0;
			}
			if(!empty($Sarah_order)){
				$for_Sarah_array[] = count($Sarah_order);
			}
			else{
				$for_Sarah_array[] = 0;
			}
			if(!empty($Ruzanna_order)){
				$for_Ruzanna_array[] = count($Ruzanna_order);
			}
			else{
				$for_Ruzanna_array[] = 0;
			}
			if(!empty($Alvina_order)){
				$for_Alvina_array[] = count($Alvina_order);
			}
			else{
				$for_Alvina_array[] = 0;
			}
			if(!empty($Kristina_order)){
				$for_Kristina_array[] = count($Kristina_order);
			}
			else{
				$for_Kristina_array[] = 0;
			}
			if(!empty($annab_order)){
				$for_annab_array[] = count($annab_order);
			}
			else{
				$for_annab_array[] = 0;
			}
			if(!empty($bulkuser_order)){
				$for_bulkuser_array[] = count($bulkuser_order);
			}
			else{
				$for_bulkuser_array[] = 0;
			}
			if(!empty($arshan_order)){
				$for_arshan_array[] = count($arshan_order);
			}
			else{
				$for_arshan_array[] = 0;
			}
			if(!empty($dev_travel_order)){
				$for_dev_travel_array[] = count($dev_travel_order);
			}
			else{
				$for_dev_travel_array[] = 0;
			}
			if(!empty($Margarita_order)){
				$for_Margarita_array[] = count($Margarita_order);
			}
			else{
				$for_Margarita_array[] = 0;
			}
			if(!empty($Mari_order)){
				$for_Mari_array[] = count($Mari_order);
			}
			else{
				$for_Mari_array[] = 0;
			}
			if(!empty($Elmira_order)){
				$for_Elmira_array[] = count($Elmira_order);
			}
			else{
				$for_Elmira_array[] = 0;
			}
			if(!empty($Satine_order)){
				$for_Satine_array[] = count($Satine_order);
			}
			else{
				$for_Satine_array[] = 0;
			}
			if(!empty($Mery_order)){
				$for_Mery_array[] = count($Mery_order);
			}
			else{
				$for_Mery_array[] = 0;
			}
			if(!empty($lusine_order)){
				$for_lusine_array[] = count($lusine_order);
			}
			else{
				$for_lusine_array[] = 0;
			}
			if(!empty($elya_order)){
				$for_elya_array[] = count($elya_order);
			}
			else{
				$for_elya_array[] = 0;
			}
			if(!empty($Shushanik_order)){
				$for_Shushanik_array[] = count($Shushanik_order);
			}
			else{
				$for_Shushanik_array[] = 0;
			}
			if(!empty($knarik_order)){
				$for_knarik_array[] = count($knarik_order);
			}
			else{
				$for_knarik_array[] = 0;
			}
			if(!empty($siranush_order)){
				$for_siranush_array[] = count($siranush_order);
			}
			else{
				$for_siranush_array[] = 0;
			}
			if(!empty($yeva_order)){
				$for_yeva_array[] = count($yeva_order);
			}
			else{
				$for_yeva_array[] = 0;
			}
			if(!empty($lily_order)){
				$for_lily_array[] = count($lily_order);
			}
			else{
				$for_lily_array[] = 0;
			}
			if(!empty($christina_order)){
				$for_christina_array[] = count($christina_order);
			}
			else{
				$for_christina_array[] = 0;
			}
			if(!empty($sofi_order)){
				$for_sofi_array[] = count($sofi_order);
			}
			else{
				$for_sofi_array[] = 0;
			}
			if(!empty($zara_order)){
				$for_zara_array[] = count($zara_order);
			}
			else{
				$for_zara_array[] = 0;
			}
			if(!empty($hasmik_order)){
				$for_hasmik_array[] = count($hasmik_order);
			}
			else{
				$for_hasmik_array[] = 0;
			}
			if(!empty($hranush_order)){
				$for_hranush_array[] = count($hranush_order);
			}
			else{
				$for_hranush_array[] = 0;
			}
			}
		$result = [];
		$result['for_gayane_array'] = $for_gayane_array;
		$result['for_sona_array'] = $for_sona_array;
		$result['for_nara_array'] = $for_nara_array;
		$result['for_Anye_array'] = $for_Anye_array;
		$result['for_Anush_array'] = $for_Anush_array;
		$result['for_dev_operator_array'] = $for_dev_operator_array;
		$result['for_Lyuda_array'] = $for_Lyuda_array;
		$result['for_gayaneh_array'] = $for_gayaneh_array;
		$result['for_RegAnna_array'] = $for_RegAnna_array;
		$result['for_RegShushan_array'] = $for_RegShushan_array;
		$result['for_lilit_array'] = $for_lilit_array;
		$result['for_ruzan_array'] = $for_ruzan_array;
		$result['for_tatev_array'] = $for_tatev_array;
		$result['for_liana_array'] = $for_liana_array;
		$result['for_mariam_array'] = $for_mariam_array;
		$result['for_lia_array'] = $for_lia_array;
		$result['for_anna_array'] = $for_anna_array;
		$result['for_Arusik_array'] = $for_Arusik_array;
		$result['for_Anette_array'] = $for_Anette_array;
		$result['for_Elen_array'] = $for_Elen_array;
		$result['for_emma_array'] = $for_emma_array;
		$result['for_Heghine_array'] = $for_Heghine_array;
		$result['for_Sarah_array'] = $for_Sarah_array;
		$result['for_Ruzanna_array'] = $for_Ruzanna_array;
		$result['for_Alvina_array'] = $for_Alvina_array;
		$result['for_Kristina_array'] = $for_Kristina_array;
		$result['for_annab_array'] = $for_annab_array;
		$result['for_bulkuser_array'] = $for_bulkuser_array;
		$result['for_arshan_array'] = $for_arshan_array;
		$result['for_dev_travel_array'] = $for_dev_travel_array;
		$result['for_Margarita_array'] = $for_Margarita_array;
		$result['for_Mari_array'] = $for_Mari_array;
		$result['for_Elmira_array'] = $for_Elmira_array;
		$result['for_Satine_array'] = $for_Satine_array;
		$result['for_Mery_array'] = $for_Mery_array;
		$result['for_lusine_array'] = $for_lusine_array;
		$result['for_elya_array'] = $for_elya_array;
		$result['for_Shushanik_array'] = $for_Shushanik_array;
		$result['for_knarik_array'] = $for_knarik_array;
		$result['for_siranush_array'] = $for_siranush_array;
		$result['for_yeva_array'] = $for_yeva_array;
		$result['for_lily_array'] = $for_lily_array;
		$result['for_christina_array'] = $for_christina_array;
		$result['for_sofi_array'] = $for_sofi_array;
		$result['for_zara_array'] = $for_zara_array;
		$result['for_hasmik_array'] = $for_hasmik_array;
		$result['for_hranush_array'] = $for_hranush_array;
		$result['for_all_orders'] = $for_all_orders;
		$result['for_show_dates'] = $for_show_dates;
		$result['for_type_of_chart'] = 'operators';
		print json_encode($result);die;
		}
		else if($type == 'delivery_reason'){
			$from_date = $_REQUEST['from_date'];
			$current_payment_status = $_REQUEST['current_payment_status'];
		    $to_date = $_REQUEST['to_date'];
		    $operator_filter = $_REQUEST['operator_filter'];
		    $sql = "SELECT * from rg_orders";
		    if(!empty($from_date)){
		    	$sql.= " where created_date >= '" . $from_date . "'";
		    }
		    if(!empty($to_date)){
		    	$sql.= " AND created_date <= '" . $to_date . "'";
		    }
		    if($operator_filter != 'all' ){
				$for_operator_sql = " AND operator = '" . $operator_filter ."'";
		    	$sql.= " AND operator = '" . $operator_filter ."'";
		    }
		    $current_payment_status_sql = '';
		    if($current_payment_status == 'paid'){
		    	$current_payment_status_sql = " and ( delivery_status = 1 or delivery_status = 3 or delivery_status = 6 or delivery_status = 7 or delivery_status = 11 or delivery_status = 12 or delivery_status = 13 or delivery_status = 14 ) ";
		    }
		    else if($current_payment_status == 'unpaid'){
		    	$current_payment_status_sql = " and ( delivery_status = 2 or delivery_status = 4 or delivery_status = 5 or delivery_status = 8 or delivery_status = 9 or delivery_status = 10) ";
		    }
			$orders = getwayConnect::getwayData($sql);
			$for_checking = [];
			$for_show_dates = [];
			foreach( $orders as $key => $value ){
				if (!in_array($value['created_date'], $for_checking)) {
					$for_checking[]= $value['created_date'];
					$for_show_dates[] = [$value['created_date']];
				}
			}
			$for_sireliin_array = [];
			$for_sirelii_cnndyan_array = [];
			$for_barekami_array = [];
			$for_barekami_cnndyan_array = [];
			$for_harsanekan_array = [];
			$for_henc_aynpes_array = [];
			$for_noracnin_array = [];
			$for_gorcnakan_array = [];
			$for_sgo_array = [];
			$for_erexayi_cnndyan_array = [];
			$for_gorcenkeroj_cnndyan_array = [];
			$for_tarelic_array = [];
			$for_anhaskanali_array = [];
			$for_anhaskanali_cnndyan_array = [];
			$for_nor_tari_array = [];
			$for_valentin_array = [];
			$for_marti_8_array = [];
			$for_aprili_7_array = [];
			$for_mayrutyan_or_array = [];
			$for_petrvari_23_array = [];
			$for_zatik_array = [];
			$for_september_1_array = [];
			$for_marti_1_array = [];
			$for_odanavakayan_dimavorum_array = [];
			$for_janaparhum_odanavakayan_array = [];
			$for_siti_tur_array = [];
			$for_gisherayin_siti_tur_array = [];
			$for_tur_sevan_caxkadzor_array = [];
			$for_tur_garni_gexard_array = [];
			$for_tur_ejmiacin_array = [];
			$for_kilikia_dimavorum_array = [];
			$for_janaprhum_kilikiayic_array = [];
			$for_mahan_dimavorum_array = [];
			$for_aseman_dimavorum_array = [];
			$for_kish_dimavorum_array = [];
			$for_gisherayin_dimavorum_array = [];
			$for_gisherayin_janaprhum_array = [];
			$for_dimavorum_dalmayic_array = [];
			$for_janaparhum_dalmayic_array = [];
			$for_pox_vercnel_array = [];
			$for_pox_bajanel_array = [];
			$for_undefined_tours_array = [];
			$for_all_orders = [];
			foreach($for_checking as $key=>$value){
				if($operator_filter != 'all'){
					$all_orders = getwayConnect::getwayData("SELECT COUNT(*) FROM rg_orders where created_date = '" . $value . "'" . $current_payment_status_sql);
					$sireliin_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '1' and created_date = '" . $value . "' and operator= '" .$operator_filter."' " . $current_payment_status_sql);
					$sirelii_cnndyan_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '2' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql);
					$barekami_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '3' and created_date = '" . $value . "' and operator= '" . $operator_filter."' " . $current_payment_status_sql);
					$barekami_cnndyan_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '4' and created_date = '" . $value . "' and operator = '" . $operator_filter ."'" . $current_payment_status_sql);
					$harsanekan_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '5' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql);
					$henc_aynpes_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '6' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$noracnin_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '7' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$gorcnakan_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '8' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$sgo_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '9' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$erexayi_cnndyan_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '10' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$nshandreq_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '11' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$gorcenkeroj_cnndyan_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '12' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$tarelic_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '13' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$anhaskanali_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '14' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$anhaskanali_cnndyan_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '15' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$nor_tari_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '16' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$valentin_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '17' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$marti_8_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '18' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$aprili_7_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '19' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$mayrutyan_or_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '20' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$petrvari_23_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '21' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$zatik_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '22' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$september_1_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '23' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$marti_1_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '24' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$odanavakayan_dimavorum_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '25' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$janaparhum_odanavakayan_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '26' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$siti_tur = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '27' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$gisherayin_siti_tur_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '28' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$tur_sevan_caxkadzor_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '29' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$tur_garni_gexard_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '30' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$tur_ejmiacin_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '31' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$kilikia_dimavorum_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '32' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$janaprhum_kilikiayic_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '33' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$mahan_dimavorum_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '34' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$aseman_dimavorum_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '35' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$kish_dimavorum_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '36' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$gisherayin_dimavorum_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '37' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$gisherayin_janaprhum_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '38' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$dimavorum_dalmayic_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '39' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$janaparhum_dalmayic_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '40' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$pox_vercnel_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '41' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$pox_bajanel_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '42' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$undefined_tours_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '43' and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
				}
				else{
					$all_orders = getwayConnect::getwayData("SELECT COUNT(*) FROM rg_orders where created_date = '" . $value . "' " . $current_payment_status_sql);
					$sireliin_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '1' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$sirelii_cnndyan_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '2' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$barekami_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '3' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$barekami_cnndyan_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '4' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$harsanekan_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '5' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$henc_aynpes_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '6' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$noracnin_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '7' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$gorcnakan_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '8' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$sgo_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '9' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$erexayi_cnndyan_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '10' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$nshandreq_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '11' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$gorcenkeroj_cnndyan_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '12' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$tarelic_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '13' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$anhaskanali_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '14' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$anhaskanali_cnndyan_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '15' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$nor_tari_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '16' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$valentin_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '17' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$marti_8_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '18' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$aprili_7_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '19' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$mayrutyan_or_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '20' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$petrvari_23_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '21' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$zatik_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '22' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$september_1_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '23' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$marti_1_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '24' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$odanavakayan_dimavorum_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '25' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$janaparhum_odanavakayan_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '26' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$siti_tur = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '27' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$gisherayin_siti_tur_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '28' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$tur_sevan_caxkadzor_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '29' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$tur_garni_gexard_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '30' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$tur_ejmiacin_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '31' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$kilikia_dimavorum_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '32' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$janaprhum_kilikiayic_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '33' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$mahan_dimavorum_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '34' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$aseman_dimavorum_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '35' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$kish_dimavorum_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '36' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$gisherayin_dimavorum_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '37' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$gisherayin_janaprhum_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '38' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$dimavorum_dalmayic_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '39' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$janaparhum_dalmayic_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '40' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$pox_vercnel_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '41' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$pox_bajanel_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '42' and created_date = '" . $value . "' " . $current_payment_status_sql);
					$undefined_tours_order = getwayConnect::getwayData("SELECT * FROM rg_orders where delivery_reason = '43' and created_date = '" . $value . "' " . $current_payment_status_sql);
				}
				if(!empty($sireliin_order)){
					$for_sireliin_array[] = count($sireliin_order);
				}
				else{
					$for_sireliin_array[] = 0;
				}
				if(!empty($all_orders)){
					$for_all_orders[] = $all_orders[0][0];
				}
				else{
					$for_all_orders[] = 0;
				}
				if(!empty($sirelii_cnndyan_order)){
					$for_sirelii_cnndyan_array[] = count($sirelii_cnndyan_order);
				}
				else{
					$for_sirelii_cnndyan_array[] = 0;
				}
				if(!empty($barekami_order)){
					$for_barekami_array[] = count($barekami_order);
				}
				else{
					$for_barekami_array[] = 0;
				}
				if(!empty($barekami_cnndyan_order)){
					$for_barekami_cnndyan_array[] = count($barekami_cnndyan_order);
				}
				else{
					$for_barekami_cnndyan_array[] = 0;
				}
				if(!empty($harsanekan_order)){
					$for_harsanekan_array[] = count($harsanekan_order);
				}
				else{
					$for_harsanekan_array[] = 0;
				}
				if(!empty($henc_aynpes_order)){
					$for_henc_aynpes_array[] = count($henc_aynpes_order);
				}
				else{
					$for_henc_aynpes_array[] = 0;
				}
				if(!empty($noracnin_order)){
					$for_noracnin_array[] = count($noracnin_order);
				}
				else{
					$for_noracnin_array[] = 0;
				}
				if(!empty($gorcnakan_order)){
					$for_gorcnakan_array[] = count($gorcnakan_order);
				}
				else{
					$for_gorcnakan_array[] = 0;
				}
				if(!empty($sgo_order)){
					$for_sgo_array[] = count($sgo_order);
				}
				else{
					$for_sgo_array[] = 0;
				}
				if(!empty($erexayi_cnndyan_order)){
					$for_erexayi_cnndyan_array[] = count($erexayi_cnndyan_order);
				}
				else{
					$for_erexayi_cnndyan_array[] = 0;
				}
				if(!empty($gorcenkeroj_cnndyan_order)){
					$for_gorcenkeroj_cnndyan_array[] = count($gorcenkeroj_cnndyan_order);
				}
				else{
					$for_gorcenkeroj_cnndyan_array[] = 0;
				}
				if(!empty($tarelic_order)){
					$for_tarelic_array[] = count($tarelic_order);
				}
				else{
					$for_tarelic_array[] = 0;
				}
				if(!empty($anhaskanali_order)){
					$for_anhaskanali_array[] = count($anhaskanali_order);
				}
				else{
					$for_anhaskanali_array[] = 0;
				}
				if(!empty($anhaskanali_cnndyan_order)){
					$for_anhaskanali_cnndyan_array[] = count($anhaskanali_cnndyan_order);
				}
				else{
					$for_anhaskanali_cnndyan_array[] = 0;
				}
				if(!empty($nor_tari_order)){
					$for_nor_tari_array[] = count($nor_tari_order);
				}
				else{
					$for_nor_tari_array[] = 0;
				}
				if(!empty($valentin_order)){
					$for_valentin_array[] = count($valentin_order);
				}
				else{
					$for_valentin_array[] = 0;
				}
				if(!empty($marti_8_order)){
					$for_marti_8_array[] = count($marti_8_order);
				}
				else{
					$for_marti_8_array[] = 0;
				}
				if(!empty($aprili_7_order)){
					$for_aprili_7_array[] = count($aprili_7_order);
				}
				else{
					$for_aprili_7_array[] = 0;
				}
				if(!empty($mayrutyan_or_order)){
					$for_mayrutyan_or_array[] = count($mayrutyan_or_order);
				}
				else{
					$for_mayrutyan_or_array[] = 0;
				}
				if(!empty($petrvari_23_order)){
					$for_petrvari_23_array[] = count($petrvari_23_order);
				}
				else{
					$for_petrvari_23_array[] = 0;
				}
				if(!empty($september_1_order)){
					$for_september_1_array[] = count($september_1_order);
				}
				else{
					$for_september_1_array[] = 0;
				}
				if(!empty($zatik_order)){
					$for_zatik_array[] = count($zatik_order);
				}
				else{
					$for_zatik_array[] = 0;
				}
				if(!empty($marti_1_order)){
					$for_marti_1_array[] = count($marti_1_order);
				}
				else{
					$for_marti_1_array[] = 0;
				}
				if(!empty($odanavakayan_dimavorum_order)){
					$for_odanavakayan_dimavorum_array[] = count($odanavakayan_dimavorum_order);
				}
				else{
					$for_odanavakayan_dimavorum_array[] = 0;
				}
				if(!empty($janaparhum_odanavakayan_order)){
					$for_janaparhum_odanavakayan_array[] = count($janaparhum_odanavakayan_order);
				}
				else{
					$for_janaparhum_odanavakayan_array[] = 0;
				}
				if(!empty($siti_tur_order)){
					$for_siti_tur_array[] = count($siti_tur_order);
				}
				else{
					$for_siti_tur_array[] = 0;
				}
				if(!empty($tur_sevan_caxkadzor_order)){
					$for_tur_sevan_caxkadzor_array[] = count($tur_sevan_caxkadzor_order);
				}
				else{
					$for_tur_sevan_caxkadzor_array[] = 0;
				}
				if(!empty($tur_garni_gexard_order)){
					$for_tur_garni_gexard_array[] = count($tur_garni_gexard_order);
				}
				else{
					$for_tur_garni_gexard_array[] = 0;
				}
				if(!empty($tur_ejmiacin_order)){
					$for_tur_ejmiacin_array[] = count($tur_ejmiacin_order);
				}
				else{
					$for_tur_ejmiacin_array[] = 0;
				}
				if(!empty($kilikia_dimavorum_order)){
					$for_kilikia_dimavorum_array[] = count($kilikia_dimavorum_order);
				}
				else{
					$for_kilikia_dimavorum_array[] = 0;
				}
				if(!empty($janaprhum_kilikiayic_order)){
					$for_janaprhum_kilikiayic_array[] = count($janaprhum_kilikiayic_order);
				}
				else{
					$for_janaprhum_kilikiayic_array[] = 0;
				}
				if(!empty($mahan_dimavorum_order)){
					$for_mahan_dimavorum_array[] = count($mahan_dimavorum_order);
				}
				else{
					$for_mahan_dimavorum_array[] = 0;
				}
				if(!empty($aseman_dimavorum_order)){
					$for_aseman_dimavorum_array[] = count($aseman_dimavorum_order);
				}
				else{
					$for_aseman_dimavorum_array[] = 0;
				}
				if(!empty($kish_dimavorum_order)){
					$for_kish_dimavorum_array[] = count($kish_dimavorum_order);
				}
				else{
					$for_kish_dimavorum_array[] = 0;
				}
				if(!empty($gisherayin_dimavorum_order)){
					$for_gisherayin_dimavorum_array[] = count($gisherayin_dimavorum_order);
				}
				else{
					$for_gisherayin_dimavorum_array[] = 0;
				}
				if(!empty($gisherayin_janaprhum_order)){
					$for_gisherayin_janaprhum_array[] = count($gisherayin_janaprhum_order);
				}
				else{
					$for_gisherayin_janaprhum_array[] = 0;
				}
				if(!empty($dimavorum_dalmayic_order)){
					$for_dimavorum_dalmayic_array[] = count($dimavorum_dalmayic_order);
				}
				else{
					$for_dimavorum_dalmayic_array[] = 0;
				}
				if(!empty($janaparhum_dalmayic_order)){
					$for_janaparhum_dalmayic_array[] = count($janaparhum_dalmayic_order);
				}
				else{
					$for_janaparhum_dalmayic_array[] = 0;
				}
				if(!empty($pox_vercnel_order)){
					$for_pox_vercnel_array[] = count($pox_vercnel_order);
				}
				else{
					$for_pox_vercnel_array[] = 0;
				}
				if(!empty($pox_bajanel_order)){
					$for_pox_bajanel_array[] = count($pox_bajanel_order);
				}
				else{
					$for_pox_bajanel_array[] = 0;
				}
				if(!empty($nshandreq_order)){
					$for_nshandreq_array[] = count($nshandreq_order);
				}
				else{
					$for_nshandreq_array[] = 0;
				}
				if(!empty($undefined_tours_order)){
					$for_undefined_tours_array[] = count($undefined_tours_order);
				}
				else{
					$for_undefined_tours_array[] = 0;
				}
			}
			$result = [];
			$result['for_sireliin_array'] = $for_sireliin_array;
			$result['for_sirelii_cnndyan_array'] = $for_sirelii_cnndyan_array;
			$result['for_barekami_array'] = $for_barekami_array;
			$result['for_barekami_cnndyan_array'] = $for_barekami_cnndyan_array;
			$result['for_harsanekan_array'] = $for_harsanekan_array;
			$result['for_henc_aynpes_array'] = $for_henc_aynpes_array;
			$result['for_noracnin_array'] = $for_noracnin_array;
			$result['for_gorcnakan_array'] = $for_gorcnakan_array;
			$result['for_sgo_array'] = $for_sgo_array;
			$result['for_erexayi_cnndyan_array'] = $for_erexayi_cnndyan_array;
			$result['for_nshandreq_array'] = $for_nshandreq_array;
			$result['for_gorcenkeroj_cnndyan_array'] = $for_gorcenkeroj_cnndyan_array;
			$result['for_tarelic_array'] = $for_tarelic_array;
			$result['for_anhaskanali_array'] = $for_anhaskanali_array;
			$result['for_anhaskanali_cnndyan_array'] = $for_anhaskanali_cnndyan_array;
			$result['for_nor_tari_array'] = $for_nor_tari_array;
			$result['for_valentin_array'] = $for_valentin_array;
			$result['for_marti_8_array'] = $for_marti_8_array;
			$result['for_aprili_7_array'] = $for_aprili_7_array;
			$result['for_mayrutyan_or_array'] = $for_mayrutyan_or_array;
			$result['for_petrvari_23_array'] = $for_petrvari_23_array;
			$result['for_zatik_array'] = $for_zatik_array;
			$result['for_september_1_array'] = $for_september_1_array;
			$result['for_marti_1_array'] = $for_marti_1_array;
			$result['for_odanavakayan_dimavorum_array'] = $for_odanavakayan_dimavorum_array;
			$result['for_janaparhum_odanavakayan_array'] = $for_janaparhum_odanavakayan_array;
			$result['for_siti_tur_array'] = $for_siti_tur_array;
			$result['for_gisherayin_siti_tur_array'] = $for_gisherayin_siti_tur_array;
			$result['for_tur_sevan_caxkadzor_array'] = $for_tur_sevan_caxkadzor_array;
			$result['for_tur_garni_gexard_array'] = $for_tur_garni_gexard_array;
			$result['for_tur_ejmiacin_array'] = $for_tur_ejmiacin_array;
			$result['for_kilikia_dimavorum_array'] = $for_kilikia_dimavorum_array;
			$result['for_janaprhum_kilikiayic_array'] = $for_janaprhum_kilikiayic_array;
			$result['for_mahan_dimavorum_array'] = $for_mahan_dimavorum_array;
			$result['for_aseman_dimavorum_array'] = $for_aseman_dimavorum_array;
			$result['for_kish_dimavorum_array'] = $for_kish_dimavorum_array;
			$result['for_gisherayin_dimavorum_array'] = $for_gisherayin_dimavorum_array;
			$result['for_gisherayin_janaprhum_array'] = $for_gisherayin_janaprhum_array;
			$result['for_dimavorum_dalmayic_array'] = $for_dimavorum_dalmayic_array;
			$result['for_janaparhum_dalmayic_array'] = $for_janaparhum_dalmayic_array;
			$result['for_pox_vercnel_array'] = $for_pox_vercnel_array;
			$result['for_pox_bajanel_array'] = $for_pox_bajanel_array;
			$result['for_undefined_tours_array'] = $for_undefined_tours_array;
			$result['for_all_orders'] = $for_all_orders;
			$result['for_show_dates'] = $for_show_dates;
			$result['for_type_of_chart'] = 'delivery_reason';
			print json_encode($result);die;
		}
		else if($type == 'sender_country'){
			$from_date = $_REQUEST['from_date'];
			$current_payment_status = $_REQUEST['current_payment_status'];
		    $to_date = $_REQUEST['to_date'];
		    $operator_filter = $_REQUEST['operator_filter'];
		    $sql = "SELECT * from rg_orders";
		    if(!empty($from_date)){
		    	$sql.= " where created_date >= '" . $from_date . "'";
		    }
		    if(!empty($to_date)){
		    	$sql.= " AND created_date <= '" . $to_date . "'";
		    }
		    if($operator_filter != 'all' ){
				$for_operator_sql = " AND operator = '" . $operator_filter ."'";
		    	$sql.= " AND operator = '" . $operator_filter ."'";
		    }
		    $current_payment_status_sql = '';
		    if($current_payment_status == 'paid'){
		    	$current_payment_status_sql = " and ( delivery_status = 1 or delivery_status = 3 or delivery_status = 6 or delivery_status = 7 or delivery_status = 11 or delivery_status = 12 or delivery_status = 13 or delivery_status = 14 ) ";
		    }
		    else if($current_payment_status == 'unpaid'){
		    	$current_payment_status_sql = " and ( delivery_status = 2 or delivery_status = 4 or delivery_status = 5 or delivery_status = 8 or delivery_status = 9 or delivery_status = 10) ";
		    }
			$orders = getwayConnect::getwayData($sql);
			$for_checking = [];
			$for_show_dates = [];
			foreach( $orders as $key => $value ){
				if (!in_array($value['created_date'], $for_checking)) {
					$for_checking[]= $value['created_date'];
					$for_show_dates[] = [$value['created_date']];
				}
			}
			$for_Armenia_array = [];
			$for_Russia_array = [];
			$for_USA_array = [];
			$for_France_array = [];
			$for_Germany_array = [];
			$for_Spain_array = [];
			$for_unknown_country_array = [];
			$for_Belarus_array = [];
			$for_Ukraine_array = [];
			$for_Australia_array = [];
			$for_Greece_array = [];
			$for_Canada_array = [];
			$for_United_Kingdom_array = [];
			$for_United_Arab_Emirates_array = [];
			$for_Georgia_array = [];
			$for_Austria_array = [];
			$for_Poland_array = [];
			$for_Sweden_array = [];
			$for_Bulgaria_array = [];
			$for_Netherlands_array = [];
			$for_Belgium_array = [];
			$for_Czech_Republic_array = [];
			$for_Kuwait_array = [];
			$for_Italy_array = [];
			$for_Uzbekistan_array = [];
			$for_Kazakhstan_array = [];
			$for_Argentina_array = [];
			$for_Artsakh_array = [];
			$for_Afghanistan_array = [];
			$for_Alaska_array = [];
			$for_Albania_array = [];
			$for_Algeria_array = [];
			$for_Cyprus_array = [];
			$for_Denmark_array = [];
			$for_Egypt_array = [];
			$for_Estonia_array = [];
			$for_Finland_array = [];
			$for_Hong_Kong_array = [];
			$for_Hungary_array = [];
			$for_Iceland_array = [];
			$for_India_array = [];
			$for_Iran_array = [];
			$for_Iraq_array = [];
			$for_Ireland_array = [];
			$for_Israel_array = [];
			$for_Japan_array = [];
			$for_Latvia_array = [];
			$for_Lebanon_array = [];
			$for_Macedonia_array = [];
			$for_Mexico_array = [];
			$for_Moldova_array = [];
			$for_Norway_array = [];
			$for_Portugal_array = [];
			$for_Qatar_array = [];
			$for_Romania_array = [];
			$for_Saudi_Arabia_array = [];
			$for_Serbia_array = [];
			$for_Singapore_array = [];
			$for_Slovakia_array = [];
			$for_Slovenia_array = [];
			$for_Switzerland_array = [];
			$for_Turkey_array = [];
			$for_Uruguay_array = [];
			$for_Vatican_array = [];
			$for_all_orders = [];
			foreach($for_checking as $key=>$value){
				if($operator_filter != 'all'){
					$all_orders = getwayConnect::getwayData("SELECT COUNT(*) FROM rg_orders where created_date = '" . $value . "'" . $current_payment_status_sql);
					$Armenian_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 1 and created_date = '" . $value . "' and operator= '" .$operator_filter."' " . $current_payment_status_sql);
					$Russia_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 184 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql);
					$USA_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 234 and created_date = '" . $value . "' and operator= '" . $operator_filter."' " . $current_payment_status_sql);
					$France_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 80 and created_date = '" . $value . "' and operator = '" . $operator_filter ."'" . $current_payment_status_sql);
					$Germany_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 87 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql);
					$Spain_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 206 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$unknown_country_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 25 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$Belarus_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 22 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$Ukraine_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 230 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$Australia_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 25 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$Greece_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 90 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$Canada_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 40 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$United_Kingdom_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 232 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$United_Arab_Emirates_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 231 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$Georgia_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 86 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$Austria_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 16 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$Poland_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 176 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$Sweden_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 212 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$Bulgaria_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 35 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$Netherlands_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 155 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$Belgium_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 23 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$Czech_Republic_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 60 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$Kuwait_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 118 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$Italy_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 111 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$Uzbekistan_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 235 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$Kazakhstan_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 115 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$Argentina_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 12 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$Artsakh_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 2 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$Afghanistan_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 3 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$Alaska_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 4 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$Albania_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 5 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$Algeria_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 6 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$Cyprus_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 59 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$Denmark_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 62 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$Egypt_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 68 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$Estonia_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 72 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$Finland_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 79 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$Hong_Kong_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 102 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$Hungary_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 103 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$Iceland_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 104 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$India_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 105 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$Iran_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 107 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$Iraq_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 108 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$Ireland_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 109 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$Israel_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 110 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$Japan_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 113 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$Latvia_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 121 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$Lebanon_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 122 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$Macedonia_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 130 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$Mexico_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 142 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$Moldova_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 144 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$Norway_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 165 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$Portugal_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 177 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$Qatar_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 179 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$Romania_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 183 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$Saudi_Arabia_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 194 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$Serbia_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 196 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$Singapore_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 199 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$Slovakia_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 200 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$Slovenia_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 201 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$Switzerland_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 213 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$Turkey_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 225 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$Uruguay_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 233 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
					$Vatican_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 6 and created_date = '" . $value . "' and operator = '" . $operator_filter ."' " . $current_payment_status_sql );
				}
				else{
					$all_orders = getwayConnect::getwayData("SELECT COUNT(*) FROM rg_orders where created_date = '" . $value . "' " . $current_payment_status_sql);
					$Armenian_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 1 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Russia_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 184 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$USA_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 234 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$France_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 80 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Germany_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 87 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Spain_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 206 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$unknown_country_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 25 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Belarus_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 22 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Ukraine_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 230 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Australia_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 15 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Greece_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 90 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Canada_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 40 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$United_Kingdom_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 232 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$United_Arab_Emirates_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 231 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Georgia_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 86 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Austria_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 16 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Poland_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 176 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Sweden_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 212 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Bulgaria_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 35 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Netherlands_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 155 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Belgium_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 23 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Czech_Republic_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 60 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Kuwait_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 118 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Italy_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 111 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Uzbekistan_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 235 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Kazakhstan_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 115 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Argentina_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 12 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Artsakh_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 2 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Afghanistan_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 3 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Alaska_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 4 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Albania_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 5 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Algeria_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 6 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Cyprus_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 59 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Denmark_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 62 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Egypt_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 68 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Estonia_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 72 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Finland_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 79 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Hong_Kong_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 102 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Hungary_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 103 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Iceland_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 104 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$India_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 105 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Iran_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 107 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Iraq_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 108 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Ireland_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 109 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Israel_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 110 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Japan_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 113 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Latvia_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 121 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Lebanon_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 122 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Macedonia_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 130 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Mexico_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 142 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Moldova_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 144 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Norway_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 165 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Portugal_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 177 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Qatar_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 179 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Romania_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 183 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Saudi_Arabia_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 194 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Serbia_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 196 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Singapore_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 199 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Slovakia_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 200 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Slovenia_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 201 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Switzerland_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 213 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Turkey_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 225 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Uruguay_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 233 and created_date = '" . $value . "' " . $current_payment_status_sql);
					$Vatican_order = getwayConnect::getwayData("SELECT * FROM rg_orders where sender_country = 237 and created_date = '" . $value . "' " . $current_payment_status_sql);
				}
				if(!empty($Armenian_order)){
					$for_Armenia_array[] = count($Armenian_order);
				}
				else{
					$for_Armenia_array[] = 0;
				}
				if(!empty($all_orders)){
					$for_all_orders[] = $all_orders[0][0];
				}
				else{
					$for_all_orders[] = 0;
				}
				if(!empty($Russia_order)){
					$for_Russia_array[] = count($Russia_order);
				}
				else{
					$for_Russia_array[] = 0;
				}
				if(!empty($USA_order)){
					$for_USA_array[] = count($USA_order);
				}
				else{
					$for_USA_array[] = 0;
				}
				if(!empty($German_order)){
					$for_Germany_array[] = count($German_order);
				}
				else{
					$for_Germany_array[] = 0;
				}
				if(!empty($Spain_order)){
					$for_Spain_array[] = count($Spain_order);
				}
				else{
					$for_Spain_array[] = 0;
				}
				if(!empty($unknown_country_order)){
					$for_unknown_country_array[] = count($unknown_country_order);
				}
				else{
					$for_unknown_country_array[] = 0;
				}
				if(!empty($Belarus_order)){
					$for_Belarus_array[] = count($Belarus_order);
				}
				else{
					$for_Belarus_array[] = 0;
				}
				if(!empty($Ukraine_order)){
					$for_Ukraine_array[] = count($Ukraine_order);
				}
				else{
					$for_Ukraine_array[] = 0;
				}
				if(!empty($Australia_order)){
					$for_Australia_array[] = count($Australia_order);
				}
				else{
					$for_Australia_array[] = 0;
				}
				if(!empty($Greece_order)){
					$for_Greece_array[] = count($Greece_order);
				}
				else{
					$for_Greece_array[] = 0;
				}
				if(!empty($Canada_order)){
					$for_Canada_array[] = count($Canada_order);
				}
				else{
					$for_Canada_array[] = 0;
				}
				if(!empty($United_Kingdom_order)){
					$for_United_Kingdom_array[] = count($United_Kingdom_order);
				}
				else{
					$for_United_Kingdom_array[] = 0;
				}
				if(!empty($United_Arab_Emirates_order)){
					$for_United_Arab_Emirates_array[] = count($United_Arab_Emirates_order);
				}
				else{
					$for_United_Arab_Emirates_array[] = 0;
				}
				if(!empty($Georgia_order)){
					$for_Georgia_array[] = count($Georgia_order);
				}
				else{
					$for_Georgia_array[] = 0;
				}
				if(!empty($Austria_order)){
					$for_Austria_array[] = count($Austria_order);
				}
				else{
					$for_Austria_array[] = 0;
				}
				if(!empty($Poland_order)){
					$for_Poland_array[] = count($Poland_order);
				}
				else{
					$for_Poland_array[] = 0;
				}
				if(!empty($Sweden_order)){
					$for_Sweden_array[] = count($Sweden_order);
				}
				else{
					$for_Sweden_array[] = 0;
				}
				if(!empty($Bulgaria_order)){
					$for_Bulgaria_array[] = count($Bulgaria_order);
				}
				else{
					$for_Bulgaria_array[] = 0;
				}
				if(!empty($Netherlands_order)){
					$for_Netherlands_array[] = count($Netherlands_order);
				}
				else{
					$for_Netherlands_array[] = 0;
				}
				if(!empty($Belgium_order)){
					$for_Belgium_array[] = count($Belgium_order);
				}
				else{
					$for_Belgium_array[] = 0;
				}
				if(!empty($Czech_Republic_order)){
					$for_Czech_Republic_array[] = count($Czech_Republic_order);
				}
				else{
					$for_Czech_Republic_array[] = 0;
				}
				if(!empty($Kuwait_order)){
					$for_Kuwait_array[] = count($Kuwait_order);
				}
				else{
					$for_Kuwait_array[] = 0;
				}
				if(!empty($Italy_order)){
					$for_Italy_array[] = count($Italy_order);
				}
				else{
					$for_Italy_array[] = 0;
				}
				if(!empty($Uzbekistan_order)){
					$for_Uzbekistan_array[] = count($Uzbekistan_order);
				}
				else{
					$for_Uzbekistan_array[] = 0;
				}
				if(!empty($Kazakhstan_order)){
					$for_Kazakhstan_array[] = count($Kazakhstan_order);
				}
				else{
					$for_Kazakhstan_array[] = 0;
				}
				if(!empty($Argentina_order)){
					$for_Argentina_array[] = count($Argentina_order);
				}
				else{
					$for_Argentina_array[] = 0;
				}
				if(!empty($Artsakh_order)){
					$for_Artsakh_array[] = count($Artsakh_order);
				}
				else{
					$for_Artsakh_array[] = 0;
				}
				if(!empty($Afghanistan_order)){
					$for_Afghanistan_array[] = count($Afghanistan_order);
				}
				else{
					$for_Afghanistan_array[] = 0;
				}
				if(!empty($Alaska_order)){
					$for_Alaska_array[] = count($Alaska_order);
				}
				else{
					$for_Alaska_array[] = 0;
				}
				if(!empty($Albania_order)){
					$for_Albania_array[] = count($Albania_order);
				}
				else{
					$for_Albania_array[] = 0;
				}
				if(!empty($Algeria_order)){
					$for_Algeria_array[] = count($Algeria_order);
				}
				else{
					$for_Algeria_array[] = 0;
				}
				if(!empty($Cyprus_order)){
					$for_Cyprus_array[] = count($Cyprus_order);
				}
				else{
					$for_Cyprus_array[] = 0;
				}
				if(!empty($Denmark_order)){
					$for_Denmark_array[] = count($Denmark_order);
				}
				else{
					$for_Denmark_array[] = 0;
				}
				if(!empty($Egypt_order)){
					$for_Egypt_array[] = count($Egypt_order);
				}
				else{
					$for_Egypt_array[] = 0;
				}
				if(!empty($Estonia_order)){
					$for_Estonia_array[] = count($Estonia_order);
				}
				else{
					$for_Estonia_array[] = 0;
				}
				if(!empty($Finland_order)){
					$for_Finland_array[] = count($Finland_order);
				}
				else{
					$for_Finland_array[] = 0;
				}
				if(!empty($Hong_Kong_order)){
					$for_Hong_Kong_array[] = count($Hong_Kong_order);
				}
				else{
					$for_Hong_Kong_array[] = 0;
				}
				if(!empty($Hungary_order)){
					$for_Hungary_array[] = count($Hungary_order);
				}
				else{
					$for_Hungary_array[] = 0;
				}
				if(!empty($Iceland_order)){
					$for_Iceland_array[] = count($Iceland_order);
				}
				else{
					$for_Iceland_array[] = 0;
				}
				if(!empty($India_order)){
					$for_India_array[] = count($India_order);
				}
				else{
					$for_India_array[] = 0;
				}
				if(!empty($Iran_order)){
					$for_Iran_array[] = count($Iran_order);
				}
				else{
					$for_Iran_array[] = 0;
				}
				if(!empty($Iraq_order)){
					$for_Iraq_array[] = count($Iraq_order);
				}
				else{
					$for_Iraq_array[] = 0;
				}
				if(!empty($Ireland_order)){
					$for_Ireland_array[] = count($Ireland_order);
				}
				else{
					$for_Ireland_array[] = 0;
				}
				if(!empty($Israel_order)){
					$for_Israel_array[] = count($Israel_order);
				}
				else{
					$for_Israel_array[] = 0;
				}
				if(!empty($Japan_order)){
					$for_Japan_array[] = count($Japan_order);
				}
				else{
					$for_Japan_array[] = 0;
				}
				if(!empty($Latvia_order)){
					$for_Latvia_array[] = count($Latvia_order);
				}
				else{
					$for_Latvia_array[] = 0;
				}
				if(!empty($Lebanon_order)){
					$for_Lebanon_array[] = count($Lebanon_order);
				}
				else{
					$for_Lebanon_array[] = 0;
				}
				if(!empty($Macedonia_order)){
					$for_Macedonia_array[] = count($Macedonia_order);
				}
				else{
					$for_Macedonia_array[] = 0;
				}
				if(!empty($Mexico_order)){
					$for_Mexico_array[] = count($Mexico_order);
				}
				else{
					$for_Mexico_array[] = 0;
				}
				if(!empty($Moldova_order)){
					$for_Moldova_array[] = count($Moldova_order);
				}
				else{
					$for_Moldova_array[] = 0;
				}
				if(!empty($Norway_order)){
					$for_Norway_array[] = count($Norway_order);
				}
				else{
					$for_Norway_array[] = 0;
				}
				if(!empty($Portugal_order)){
					$for_Portugal_array[] = count($Portugal_order);
				}
				else{
					$for_Portugal_array[] = 0;
				}
				if(!empty($Qatar_order)){
					$for_Qatar_array[] = count($Qatar_order);
				}
				else{
					$for_Qatar_array[] = 0;
				}
				if(!empty($Romania_order)){
					$for_Romania_array[] = count($Romania_order);
				}
				else{
					$for_Romania_array[] = 0;
				}
				if(!empty($Saudi_Arabia_order)){
					$for_Saudi_Arabia_array[] = count($Saudi_Arabia_order);
				}
				else{
					$for_Saudi_Arabia_array[] = 0;
				}
				if(!empty($Serbia_order)){
					$for_Serbia_array[] = count($Serbia_order);
				}
				else{
					$for_Serbia_array[] = 0;
				}
				if(!empty($Singapore_order)){
					$for_Singapore_array[] = count($Singapore_order);
				}
				else{
					$for_Singapore_array[] = 0;
				}
				if(!empty($Slovakia_order)){
					$for_Slovakia_array[] = count($Slovakia_order);
				}
				else{
					$for_Slovakia_array[] = 0;
				}
				if(!empty($Slovenia_order)){
					$for_Slovenia_array[] = count($Slovenia_order);
				}
				else{
					$for_Slovenia_array[] = 0;
				}
				if(!empty($Switzerland_order)){
					$for_Switzerland_array[] = count($Switzerland_order);
				}
				else{
					$for_Switzerland_array[] = 0;
				}
				if(!empty($Turkey_order)){
					$for_Turkey_array[] = count($Turkey_order);
				}
				else{
					$for_Turkey_array[] = 0;
				}
				if(!empty($Uruguay_order)){
					$for_Uruguay_array[] = count($Uruguay_order);
				}
				else{
					$for_Uruguay_array[] = 0;
				}
				if(!empty($Vatican_order)){
					$for_Vatican_array[] = count($Vatican_order);
				}
				else{
					$for_Vatican_array[] = 0;
				}
				if(!empty($france_order)){
					$for_France_array[] = count($france_order);
				}
				else{
					$for_France_array[] = 0;
				}
			}
			$result = [];
			$result['for_Armenia_array'] = $for_Armenia_array;
			$result['for_Russia_array'] = $for_Russia_array;
			$result['for_USA_array'] = $for_USA_array;
			$result['for_Germany_array'] = $for_Germany_array;
			$result['for_Spain_array'] = $for_Spain_array;
			$result['for_unknown_country_array'] = $for_unknown_country_array;
			$result['for_Belarus_array'] = $for_Belarus_array;
			$result['for_Ukraine_array'] = $for_Ukraine_array;
			$result['for_Australia_array'] = $for_Australia_array;
			$result['for_Greece_array'] = $for_Greece_array;
			$result['for_Canada_array'] = $for_Canada_array;
			$result['for_United_Kingdom_array'] = $for_United_Kingdom_array;
			$result['for_United_Arab_Emirates_array'] = $for_United_Arab_Emirates_array;
			$result['for_Georgia_array'] = $for_Georgia_array;
			$result['for_Austria_array'] = $for_Austria_array;
			$result['for_Poland_array'] = $for_Poland_array;
			$result['for_Sweden_array'] = $for_Sweden_array;
			$result['for_Bulgaria_array'] = $for_Bulgaria_array;
			$result['for_Netherlands_array'] = $for_Netherlands_array;
			$result['for_Belgium_array'] = $for_Belgium_array;
			$result['for_Czech_Republic_array'] = $for_Czech_Republic_array;
			$result['for_Kuwait_array'] = $for_Kuwait_array;
			$result['for_Italy_array'] = $for_Italy_array;
			$result['for_Uzbekistan_array'] = $for_Uzbekistan_array;
			$result['for_Kazakhstan_array'] = $for_Kazakhstan_array;
			$result['for_Argentina_array'] = $for_Argentina_array;
			$result['for_Artsakh_array'] = $for_Artsakh_array;
			$result['for_Afghanistan_array'] = $for_Afghanistan_array;
			$result['for_Alaska_array'] = $for_Alaska_array;
			$result['for_Albania_array'] = $for_Albania_array;
			$result['for_Algeria_array'] = $for_Algeria_array;
			$result['for_Cyprus_array'] = $for_Cyprus_array;
			$result['for_Denmark_array'] = $for_Denmark_array;
			$result['for_Egypt_array'] = $for_Egypt_array;
			$result['for_Estonia_array'] = $for_Estonia_array;
			$result['for_Finland_array'] = $for_Finland_array;
			$result['for_Hong_Kong_array'] = $for_Hong_Kong_array;
			$result['for_Hungary_array'] = $for_Hungary_array;
			$result['for_Iceland_array'] = $for_Iceland_array;
			$result['for_India_array'] = $for_India_array;
			$result['for_Iran_array'] = $for_Iran_array;
			$result['for_Iraq_array'] = $for_Iraq_array;
			$result['for_Ireland_array'] = $for_Ireland_array;
			$result['for_Israel_array'] = $for_Israel_array;
			$result['for_Japan_array'] = $for_Japan_array;
			$result['for_Latvia_array'] = $for_Latvia_array;
			$result['for_Lebanon_array'] = $for_Lebanon_array;
			$result['for_Macedonia_array'] = $for_Macedonia_array;
			$result['for_Mexico_array'] = $for_Mexico_array;
			$result['for_Moldova_array'] = $for_Moldova_array;
			$result['for_Norway_array'] = $for_Norway_array;
			$result['for_Portugal_array'] = $for_Portugal_array;
			$result['for_Qatar_array'] = $for_Qatar_array;
			$result['for_Romania_array'] = $for_Romania_array;
			$result['for_Saudi_Arabia_array'] = $for_Saudi_Arabia_array;
			$result['for_Serbia_array'] = $for_Serbia_array;
			$result['for_Singapore_array'] = $for_Singapore_array;
			$result['for_Slovakia_array'] = $for_Slovakia_array;
			$result['for_Slovenia_array'] = $for_Slovenia_array;
			$result['for_Switzerland_array'] = $for_Switzerland_array;
			$result['for_Turkey_array'] = $for_Turkey_array;
			$result['for_Uruguay_array'] = $for_Uruguay_array;
			$result['for_Vatican_array'] = $for_Vatican_array;
			$result['for_all_orders'] = $for_all_orders;
			$result['for_show_dates'] = $for_show_dates;
			$result['for_type_of_chart'] = 'sender_country';
			print json_encode($result);die;
		}
		else{
			var_dump(9999);die;
		}

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
    <title>Order Charts</title>
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
			<input type='date' class='from_date mt-1 form-control' value="<?= date("Y-m-d") ?>">
		</div>
		<div class='col-md-2' style='margin-top:60px'>
			<input type='date' class='to_date mb-1 form-control'>
		</div>
		<div class='col-md-2' style='margin-top:60px'>
			<select class='form-control operator_filter' >
				<option value='all'>All</option>
				<?php 
					foreach( $operators as $key => $value ) {
						?>
							<option value="<?=$value['username']?>"><?= ($value['full_name_am'] != '')? $value['full_name_am'] : $value['username']?></option>
						<?php
					}
				?>
			</select>
		</div>
		<div class='col-md-2' style='margin-top:60px'>
			<select class='form-control current_payment_status' >
				<option value='all'>Վճարվածներ և Չվճարվածները</option>
				<option value="paid">Վճարվածները</option>
				<option value="unpaid">Չվճարվածները</option>
			</select>
		</div>
		<!-- <div class='col-md-2' style='margin-top:60px'>
			<label for="all_types" style='margin-left:20px'>Բոլորը</label>
			<input type='checkbox' id='all_types'>
		</div> -->
		<div class='col-md-2' style='margin-top:60px'>
			<button class='btn btn-success btn_for_find_orders'>Check</button>
		</div>
		<div class='col-md-12' style='margin-top:10px'>
			<label for="by_status">Ստատուս</label>
			<input type='checkbox' value='status' id='by_status' name='create_chart_by_checkbox'>
			<label for="by_payment_type" style='margin-left:20px'>Վճարման ձևի</label>
			<input type='checkbox' value='payemnt_type' id='by_payment_type' name='create_chart_by_checkbox'>
			<label for="by_communication" style='margin-left:20px'>Կոմոնիկացիոն միջոց</label>
			<input type='checkbox' value='communication' id='by_communication' name='create_chart_by_checkbox'>
			<label for="by_currency" style='margin-left:20px'>Արժույթ</label>
			<input type='checkbox' value='currency' id='by_currency' name='create_chart_by_checkbox'>
			<label for="by_delivery_country" style='margin-left:20px'>Առաքման Երկիր</label>
			<input type='checkbox' value='delivery_country' id='by_delivery_country' name='create_chart_by_checkbox'>
			<label for="by_sender_country" style='margin-left:20px'>Ուղարկողի Երկիր</label>
			<input type='checkbox' value='sender_country' id='by_sender_country' name='create_chart_by_checkbox'>
			<label for="by_receiver_subregion" style='margin-left:20px'>Ստացողի Համայնք</label>
			<input type='checkbox' value='receiver_subregion' id='by_receiver_subregion' name='create_chart_by_checkbox'>
			<label for="by_delivery_reason" style='margin-left:20px'>Պատճառ</label>
			<input type='checkbox' value='delivery_reason' id='by_delivery_reason' name='create_chart_by_checkbox'>
			<label for="by_delivery_time" style='margin-left:20px'>Առաքման Ժամ</label>
			<input type='checkbox' value='delivery_time' id='by_delivery_time' name='create_chart_by_checkbox'>
			<label for="by_operators" style='margin-left:20px'>Օպերատոր</label>
			<input type='checkbox' value='operators' id='by_operators' name='create_chart_by_checkbox'>
			<label for="by_sale_point" style='margin-left:20px'>Վաճառքի կետ</label>
			<input type='checkbox' value='sale_point' id='by_sale_point' name='create_chart_by_checkbox'>
			<label for="by_count" style='margin-left:20px'>Ըստ քանակի</label>
			<input type='checkbox' value='orders_count' id='by_count' name='create_chart_by_checkbox'>
		</div>
	</div>
	<div class='col-md-12' id="containerStatus" style='margin-top:50px'></div>
	<div class='col-md-12' id="containerPaymentType" style='margin-top:50px'></div>
	<div class='col-md-12' id="containerCommunication" style='margin-top:50px'></div>
	<div class='col-md-12' id="containerDeliveryCountry" style='margin-top:50px'></div>
	<div class='col-md-12' id="containerSenderCountry" style='margin-top:50px'></div>
	<div class='col-md-12' id="containerReceiverSubregion" style='margin-top:50px'></div>
	<div class='col-md-12' id="containerByOrderCountPaid" style='margin-top:50px'></div>
	<div class='col-md-12' id="containerDeliveryReason" style='margin-top:50px'></div>
	<div class='col-md-12' id="containerDeliveryTime" style='margin-top:50px'></div>
	<div class='col-md-12' id="containerOperators" style='margin-top:50px'></div>
	<div class='col-md-12' id="containerSalePointOurWebsites" style='margin-top:50px'></div>
	<div class='col-md-12' id="containerSalePointPartners" style='margin-top:50px'></div>
	<script src="https://code.highcharts.com/highcharts.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
	<script type="text/javascript">
		$(document).ready(function(){
			$(document).on('click',".btn_for_find_orders",function(){
				$("#containerStatus").empty();
				$("#containerPaymentType").empty();
				$("#containerCommunication").empty();
				$("#containerDeliveryCountry").empty();
				$("#containerSenderCountry").empty();
				$("#containerReceiverSubregion").empty();
				$("#containerByOrderCountPaid").empty();
				$("#containerDeliveryReason").empty();
				$("#containerDeliveryTime").empty();
				$("#containerOperators").empty();
				$("#containerSalePointOurWebsites").empty();
				$("#containerSalePointPartners").empty();
				var from_date = $(".from_date").val();
				var to_date = $(".to_date").val();
				var operator_filter = $(".operator_filter").val();
				var current_payment_status = $(".current_payment_status").val();
				var create_chart_by_checkboxes_array = $("input[name='create_chart_by_checkbox']:checked");
				for(var i = 0 ; i < create_chart_by_checkboxes_array.length;i++){
					var type = $(create_chart_by_checkboxes_array[i]).val();
					console.log(type);
					if(type != ''){
						if(from_date){
							$.ajax({
					            url: location.href,
					            type: 'post',
					            data: {
					                getorders: true,
					                type:type,
					                from_date:from_date,
					                to_date:to_date,
					                current_payment_status:current_payment_status,
					                operator_filter:operator_filter
					            },
					            success: function(resp){
					            	if(resp.length > 5){
					            		resp =  JSON.parse(resp);
										var all_orders = $('#all_types').prop('checked');
										if(!all_orders){
											resp.for_all_orders = 0
										}
					            		if(resp.for_type_of_chart == 'status'){
						         			createHighChartGraphicByStatus(resp);
					            		}
					            		else if(resp.for_type_of_chart == 'payment_type'){
						         			createHighChartGraphicByPaymentType(resp);
					            		}
					            		else if(resp.for_type_of_chart == 'communication'){
						         			createHighChartGraphicByCommunication(resp);
					            		}
					            		else if(resp.for_type_of_chart == 'currency'){
						         			createHighChartGraphicByCurrency(resp);
					            		}
					            		else if(resp.for_type_of_chart == 'delivery_country'){
						         			createHighChartGraphicByDeliveryCountry(resp);
					            		}
					            		else if(resp.for_type_of_chart == 'sender_country'){
						         			createHighChartGraphicBySenderCountry(resp);
					            		}
					            		else if(resp.for_type_of_chart == 'receiver_subregion'){
						         			createHighChartGraphicByReceiverSubregion(resp);
					            		}
					            		else if(resp.for_type_of_chart == 'orders_count'){
						         			createHighChartGraphicByOrderCountPaidUnPaid(resp);
					            		}
					            		else if(resp.for_type_of_chart == 'delivery_reason'){
						         			createHighChartGraphicByDeliveryReason(resp);
					            		}
					            		else if(resp.for_type_of_chart == 'delivery_time'){
						         			createHighChartGraphicByDeliveryTime(resp);
					            		}
					            		else if(resp.for_type_of_chart == 'operators'){
						         			createHighChartGraphicByOperator(resp);
					            		}
					            		else if(resp.for_type_of_chart == 'sale_point'){
						         			createHighChartGraphicSalePoint(resp);
					            		}
					         		}
					            }
				        	})
						}
					}
				}
			})
			function createHighChartGraphicSalePoint(resp){
				var for_show_dates = [];
					resp.for_show_dates.forEach(function(item) {
					  for_show_dates.push(item[0]);
					});
					Highcharts.chart('containerSalePointOurWebsites', {
					chart: {
				        marginBottom: 80
				    },
				    xAxis: {
				        categories: for_show_dates,
				        labels: {
				            rotation: 60
				        },
				        opposite:true
				    },
				    title:{
						text:'By Sell Point ( Our Websites )'
					},

				    series: [{
						name: 'Flowers-Armenia.com',
				        data: resp.for_flowers_armenia_com_array
				    },{
						name: 'GiftsArmenia.info',
				        data: resp.for_giftsArmenia_info_array
				    },{
						name: 'Anahit.am',
				        data: resp.for_anahit_am_array
				    },{
						name: 'Flowers-Armenia.Am',
				        data: resp.for_flowers_armenia_am_array
				    },{
						name: 'www.Regard-Travel.net',
				        data: resp.for_regard_Travel_net_array
				    },{
						name: 'Flowers-Barcelona.com',
				        data: resp.for_flowers_barcelona_com_array
				    },{
						name: 'Flowers-Sib.ru ',
				        data: resp.for_flowers_sib_ru_array
				    },{
						name: 'www.Armenia-Flowers.com',
				        data: resp.for_armenia_flowers_com_array
				    },{
						name: 'Flowers-to-Armenia.info',
				        data: resp.for_flowers_to_armenia_info_array
				    },{
						name: 'Gifts-to-Armenia.info',
				        data: resp.for_gifts_to_armenia_info_array
				    },{
						name: 'Flowers-Armenia.info',
				        data: resp.for_flowers_armenia_info_array
				    },{
						name: 'Gifts-Armenia.info',
				        data: resp.for_gifts_armenia_info_array
				    },{
						name: 'Flowers-Palace.ru',
				        data: resp.for_flowers_Palace_ru_array
				    },{
						name: 'Flowers-Paris.ru',
				        data: resp.for_flowers_paris_ru_array
				    },{
						name: 'Flowers-Paris.fr',
				        data: resp.for_flowers_paris_fr_array
				    },{
						name: 'FlowersArmenia.info',
				        data: resp.for_flowersarmenia_info_array
				    },{
						name: 'Flowers Armenia International',
				        data: resp.for_flowers_armenia_internation_orders
				    },{
						name: 'Flowers-Armenia.us',
				        data: resp.for_flowers_armenia_us_array
				    },{
						name: 'www.promo-flower.com',
				        data: resp.for_promo_flower_com_array
				    },{
						name: 'Anahit-Flowers.com',
				        data: resp.for_anahit_flowers_com_array
				    },{
						name: 'www.Regard-Flowers.com',
				        data: resp.for_regard_flowers_com_array
				    },{
						name: 'Flowers Local Market',
				        data: resp.for_flowers_local_market_group_orders
				    },{
						name: 'Landing Pages by Generator (promo-flower)',
				        data: resp.for_landing_pages_by_generator_orders
				    },{
						name: 'Բոլոր Պատվերները',
				        data: resp.for_all_orders
				    }
				    ]

				});
					Highcharts.chart('containerSalePointPartners', {
					chart: {
				        marginBottom: 80
				    },
				    xAxis: {
				        categories: for_show_dates,
				        labels: {
				            rotation: 60
				        },
				        opposite:true
				    },
				    title:{
						text:'By Sell Point ( Partners )'
					},

				    series: [{
						name: 'Rus-buket.ru',
				        data: resp.for_rus_buket_ru_array
				    },{
						name: 'Yes.ua',
				        data: resp.for_yes_ua_array
				    },{
						name: 'Charlotte.ru',
				        data: resp.for_charlotte_ru_array
				    },{
						name: 'NevaBuket.ru',
				        data: resp.for_nevabuket_ru_array
				    },{
						name: 'Teleflora.com.mt',
				        data: resp.for_teleflora_com_mt_array
				    },{
						name: 'MegaFlowers.ru',
				        data: resp.for_megaflowers_ru_array
				    },{
						name: 'Edelweiss-Service.ru',
				        data: resp.for_edelweiss_service_ru_array
				    },{
						name: 'Flora2000.ru',
				        data: resp.for_flora2000_ru_array
				    },{
						name: 'Flowwow.com',
				        data: resp.for_flowwow_com_array
				    },{
						name: 'EuropeanFlora.com',
				        data: resp.for_europeanflora_com_array
				    },{
						name: 'FlowersUSSR.com',
				        data: resp.for_flowersussr_com_array
				    },{
						name: 'Buy.am',
				        data: resp.for_buy_am_array
				    },{
						name: 'Sas.am',
				        data: resp.for_sas_am_array
				    },{
						name: 'Menu.am',
				        data: resp.for_menu_am_array
				    },{
						name: 'MyFlowers.gr',
				        data: resp.for_myflowers_gr_array
				    },{
						name: 'Cyber-Florist.ru',
				        data: resp.for_cyber_florist_ru_array
				    },{
						name: 'CrossRoad.com',
				        data: resp.for_crossroad_com_array
				    },{
						name: 'Flowers-Tehran.com',
				        data: resp.for_flowers_tehran_com_array
				    },{
						name: 'Flowers-Sib.ru',
				        data: resp.for_flowers_sib_ru_array
				    },{
						name: 'MyGlobalFlowers.com',
				        data: resp.for_myglobalflowers_com_array
				    },{
						name: 'GrandFlora.ru(my-buket.ru)',
				        data: resp.for_grandflora_ru_array
				    },{
						name: 'Heart-in-Flowers.ru',
				        data: resp.for_heart_in_flowers_ru_array
				    },{
						name: 'Gemoji',
				        data: resp.for_gemoji_array
				    },{
						name: 'Այլ տարբերակ',
				        data: resp.for_ayl_tarberak_array
				    },{
						name: 'Mamaflora.ru',
				        data: resp.for_mamaflora_ru_array
				    },{
						name: 'Բոլոր Պատվերները',
				        data: resp.for_all_orders
				    }
				    ]

				});
			}
			function createHighChartGraphicByOperator(resp){
				var for_show_dates = [];
					resp.for_show_dates.forEach(function(item) {
					  for_show_dates.push(item[0]);
					});
					Highcharts.chart('containerOperators', {
					chart: {
				        marginBottom: 80
				    },
				    xAxis: {
				        categories: for_show_dates,
				        labels: {
				            rotation: 60
				        },
				        opposite:true
				    },
				    title:{
						text:'By Operators'
					},

				    series: [{
						name: 'Գայանե',
				        data: resp.for_gayane_array
				    },{
						name: 'Սոնա',
				        data: resp.for_sona_array
				    },{
						name: 'Նառա',
				        data: resp.for_nara_array
				    },{
						name: 'Անի',
				        data: resp.for_Anye_array
				    },{
						name: 'Անուշ',
				        data: resp.for_Anush_array
				    },{
						name: 'Dev-operator',
				        data: resp.for_dev_operator_array
				    },{
						name: 'Լյուդա',
				        data: resp.for_Lyuda_array
				    },{
						name: 'Գայանեհ',
				        data: resp.for_gayaneh_array
				    },{
						name: 'RegAnna',
				        data: resp.for_RegAnna_array
				    },{
						name: 'RegShushan',
				        data: resp.for_RegShushan_array
				    },{
						name: 'Լիլիթ',
				        data: resp.for_lilit_array
				    },{
						name: 'Ռուզան',
				        data: resp.for_ruzan_array
				    },{
						name: 'Տաթև',
				        data: resp.for_tatev_array
				    },{
						name: 'Լիանա',
				        data: resp.for_liana_array
				    },{
						name: 'Մարիամ',
				        data: resp.for_mariam_array
				    },{
						name: 'Լիա',
				        data: resp.for_lia_array
				    },{
						name: 'Աննա',
				        data: resp.for_anna_array
				    },{
						name: 'Արուսիկ',
				        data: resp.for_Arusik_array
				    },{
						name: 'Անետա',
				        data: resp.for_Anette_array
				    },{
						name: 'Էլեն',
				        data: resp.for_Elen_array
				    },{
						name: 'Էմմա',
				        data: resp.for_emma_array
				    },{
						name: 'Հեղինե',
				        data: resp.for_Heghine_array
				    },{
						name: 'Սառա',
				        data: resp.for_Sarah_array
				    },{
						name: 'Ռուզաննա',
				        data: resp.for_Ruzanna_array
				    },{
						name: 'Ալվինա',
				        data: resp.for_Alvina_array
				    },{
						name: 'Կրիստինա',
				        data: resp.for_Kristina_array
				    },{
						name: 'ԱննաԲ',
				        data: resp.for_annab_array
				    },{
						name: 'bulkuser',
				        data: resp.for_bulkuser_array
				    },{
						name: 'arshan',
				        data: resp.for_arshan_array
				    },{
						name: 'dev-travel',
				        data: resp.for_dev_travel_array
				    },{
						name: 'Մարգարիտա',
				        data: resp.for_Margarita_array
				    },{
						name: 'Մարի',
				        data: resp.for_Mari_array
				    },{
						name: 'Էլմիրա',
				        data: resp.for_Elmira_array
				    },{
						name: 'Սաթինե',
				        data: resp.for_Satine_array
				    },{
						name: 'Մերի',
				        data: resp.for_Mery_array
				    },{
						name: 'Լուսինե',
				        data: resp.for_lusine_array
				    },{
						name: 'Էլյա',
				        data: resp.for_elya_array
				    },{
						name: 'Շուշանիկ',
				        data: resp.for_Shushanik_array
				    },{
						name: 'Քնարիկ',
				        data: resp.for_knarik_array
				    },{
						name: 'Սիրանուշ',
				        data: resp.for_siranush_array
				    },{
						name: 'Եվա',
				        data: resp.for_yeva_array
				    },{
						name: 'Լիլի',
				        data: resp.for_lily_array
				    },{
						name: 'Քրիստինա',
				        data: resp.for_christina_array
				    },{
						name: 'Սոֆի',
				        data: resp.for_sofi_array
				    },{
						name: 'Զառա',
				        data: resp.for_zara_array
				    },{
						name: 'Հասմիկ',
				        data: resp.for_hasmik_array
				    },{
						name: 'Հրանուշ',
				        data: resp.for_hranush_array
				    },{
						name: 'Բոլոր Պատվերները',
				        data: resp.for_all_orders
				    }
				    ]

				});
			}
			function createHighChartGraphicByDeliveryTime(resp){
				var for_show_dates = [];
					resp.for_show_dates.forEach(function(item) {
					  for_show_dates.push(item[0]);
					});
					Highcharts.chart('containerDeliveryTime', {
					chart: {
				        marginBottom: 80
				    },
				    xAxis: {
				        categories: for_show_dates,
				        labels: {
				            rotation: 60
				        },
				        opposite:true
				    },
				    title:{
						text:'By Delivery Time'
					},

				    series: [{
						name: '8-10',
				        data: resp.for_8_10_array
				    },{
						name: '9-12',
				        data: resp.for_9_12_array
				    },{
						name: '12-15',
				        data: resp.for_12_15_array
				    },{
						name: '15-18',
				        data: resp.for_15_18_array
				    },{
						name: '18-21',
				        data: resp.for_18_21_array
				    },{
						name: '21-24',
				        data: resp.for_21_24_array
				    },{
						name: '00-00',
				        data: resp.for_00_00_array
				    },{
						name: '00-09',
				        data: resp.for_00_9_array
				    },{
						name: '8-15',
				        data: resp.for_8_15_array
				    },{
						name: '15-19',
				        data: resp.for_15_19_array
				    },{
						name: '19-00',
				        data: resp.for_19_00_array
				    },{
						name: 'Բոլոր Պատվերները',
				        data: resp.for_all_orders
				    }
				    ]

				});
			}
			function createHighChartGraphicByDeliveryReason(resp){
				var for_show_dates = [];
					resp.for_show_dates.forEach(function(item) {
					  for_show_dates.push(item[0]);
					});
					Highcharts.chart('containerDeliveryReason', {
					chart: {
				        marginBottom: 80
				    },
				    xAxis: {
				        categories: for_show_dates,
				        labels: {
				            rotation: 60
				        },
				        opposite:true
				    },
				    title:{
						text:'By Delivery Reason'
					},

				    series: [{
						name: 'Սիրելիին',
				        data: resp.for_sireliin_array
				    },{
						name: 'Սիրելիի Ծննդյան',
				        data: resp.for_sirelii_cnndyan_array
				    },{
						name: 'Բարեկամին',
				        data: resp.for_barekami_array
				    },{
						name: 'Բարեկամի Ծննդյան',
				        data: resp.for_barekami_cnndyan_array
				    },{
						name: 'Հարսանեկան',
				        data: resp.for_harsanekan_array
				    },{
						name: 'Հենց Այնպես',
				        data: resp.for_henc_aynpes_array
				    },{
						name: 'Նորածնին',
				        data: resp.for_noracnin_array
				    },{
						name: 'Գործնական',
				        data: resp.for_gorcnakan_array
				    },{
						name: 'Սգո',
				        data: resp.for_sgo_array
				    },{
						name: 'Երեխայի Ծննդյան',
				        data: resp.for_erexayi_cnndyan_array
				    },{
						name: 'Նշանդրեք',
				        data: resp.for_nshandreq_array
				    },{
						name: 'Գործընկերոջ Ծննդյան',
				        data: resp.for_gorcenkeroj_cnndyan_array
				    },{
						name: 'Տարելից',
				        data: resp.for_tarelic_array
				    },{
						name: 'Անհասկանալի',
				        data: resp.for_anhaskanali_array
				    },{
						name: 'Անհասկանալի Ծննդյան',
				        data: resp.for_anhaskanali_cnndyan_array
				    },{
						name: 'Նոր Տարի',
				        data: resp.for_nor_tari_array
				    },{
						name: 'Վալենտին',
				        data: resp.for_valentin_array
				    },{
						name: 'Մարտի 8',
				        data: resp.for_marti_8_array
				    },{
						name: 'Ապրիլի 7',
				        data: resp.for_aprili_7_array
				    },{
						name: 'Մայրության Օր',
				        data: resp.for_mayrutyan_or_array
				    },{
						name: 'Փետրվարի 23',
				        data: resp.for_petrvari_23_array
				    },{
						name: 'Զատիկ',
				        data: resp.for_zatik_array
				    },{
						name: 'Սեպտեմբերի 1',
				        data: resp.for_september_1_array
				    },{
						name: 'Մարտի 1',
				        data: resp.for_marti_1_array
				    },{
						name: 'Օդանավակայան Դիմաորում',
				        data: resp.for_odanavakayan_dimavorum_array
				    },{
						name: 'Ճանապարհում Օդանավակայան',
				        data: resp.for_janaparhum_odanavakayan_array
				    },{
						name: 'Սիթի Տուր',
				        data: resp.for_siti_tur_array
				    },{
						name: 'Գիշերային Սիթի Տուր',
				        data: resp.for_gisherayin_siti_tur_array
				    },{
						name: 'Տուր Սևան-Ծաղկաձոր',
				        data: resp.for_tur_sevan_caxkadzor_array
				    },{
						name: 'Տուր Գառնի-Գեղարդ',
				        data: resp.for_tur_garni_gexard_array
				    },{
						name: 'Տուր Էջմիածին',
				        data: resp.for_tur_ejmiacin_array
				    },{
						name: 'Կիլիկիա Դիմաորում',
				        data: resp.for_kilikia_dimavorum_array
				    },{
						name: 'Ճանապարհում Կիլիկիաից',
				        data: resp.for_janaprhum_kilikiayic_array
				    },{
						name: 'Mahan Դիմաորում',
				        data: resp.for_mahan_dimavorum_array
				    },{
						name: 'Aseman Դիմաորում',
				        data: resp.for_aseman_dimavorum_array
				    },{
						name: 'Kish Դիմաորում',
				        data: resp.for_kish_dimavorum_array
				    },{
						name: 'Գիշերային Դիմաորում',
				        data: resp.for_gisherayin_dimavorum_array
				    },{
						name: 'Գիշերային Ճանապարհում',
				        data: resp.for_gisherayin_janaprhum_array
				    },{
						name: 'Դիմաորում Դալմայից',
				        data: resp.for_dimavorum_dalmayic_array
				    },{
						name: 'Ճանապարհում Դալմայից',
				        data: resp.for_janaparhum_dalmayic_array
				    },{
						name: 'Pox_Vercnel',
				        data: resp.for_pox_vercnel_array
				    },{
						name: 'Pox_Bajanel',
				        data: resp.for_pox_bajanel_array
				    },{
						name: 'Undefined TOURs',
				        data: resp.for_undefined_tours_array
				    },{
						name: 'Բոլոր Պատվերները',
				        data: resp.for_all_orders
				    }
				    ]

				});

			}
			function createHighChartGraphicByOrderCountPaidUnPaid(resp){
				var for_show_dates = [];
					resp.for_show_dates.forEach(function(item) {
					  for_show_dates.push(item[0]);
					});
					console.log(resp.paid,resp.unpaid);
					Highcharts.chart('containerByOrderCountPaid', {
					chart: {
				        marginBottom: 80
				    },
				    xAxis: {
				        categories: for_show_dates,
				        labels: {
				            rotation: 60
				        },
				        opposite:true
				    },
				    title:{
						text:'Վճարվածներ & Չվճարվածները'
					},

				    series: [{
						name: 'Վճարվածներ',
				        data: resp.paid
				    },{
						name: 'Չվճարվածները',
				        data: resp.unpaid
				    },
				    ]

				});
			}
			function createHighChartGraphicByReceiverSubregion(resp){
				var for_show_dates = [];
					resp.for_show_dates.forEach(function(item) {
					  for_show_dates.push(item[0]);
					});
					Highcharts.chart('containerReceiverSubregion', {
					chart: {
				        marginBottom: 80
				    },
				    xAxis: {
				        categories: for_show_dates,
				        labels: {
				            rotation: 60
				        },
				        opposite:true
				    },
				    title:{
						text:'By Receiver Subregion'
					},

				    series: [{
						name: 'Աջափնյակ',
				        data: resp.for_ajapnyak_array
				    },{
						name: 'Ավան',
				        data: resp.for_avan_array
				    },{
						name: 'Արաբկիր',
				        data: resp.for_arabkir_array
				    },{
						name: 'Դավթաշեն',
				        data: resp.for_davtashen_array
				    },{
						name: 'Էրեբունի',
				        data: resp.for_erebuni_array
				    },{
						name: 'Կենտրոն',
				        data: resp.for_kentron_array
				    },{
						name: 'Մալաթիա-Սեբաստիա',
				        data: resp.for_malatia_sebastia_array
				    },{
						name: 'Նոր Նորք',
				        data: resp.for_nor_norq_array
				    },{
						name: 'Նորք Մարաշ',
				        data: resp.for_norq_marash_array
				    },{
						name: 'Նուբարաշեն',
				        data: resp.for_nubarashen_array
				    },{
						name: 'Շենգավիթ',
				        data: resp.for_shengavit_array
				    },{
						name: 'Քանաքեռ-Զեյթուն',
				        data: resp.for_qanaqer_zeytun_array
				    },{
						name: '- Երևան +5 կիլոմետր -',
				        data: resp.for_erevan_plus_5_array
				    },{
						name: '-- Այլ ՀՀ Մարզեր --',
				        data: resp.for_ayl_marzer_array
				    },{
						name: 'ՉՃՇՏՎԱԾ ՀԱՍՑԵ - Ճշտել Զանգով',
				        data: resp.for_chchstvac_hasce_array
				    },{
						name: 'Կոտայք',
				        data: resp.for_kotayq_array
				    },{
						name: 'Լոռի',
				        data: resp.for_lori_array
				    },{
						name: 'Տավուշ',
				        data: resp.for_tavush_array
				    },{
						name: 'Սյունիք',
				        data: resp.for_syunik_array
				    },{
						name: 'Վայոց ձոր',
				        data: resp.for_vayoc_dzor_array
				    },{
						name: 'Արմավիր',
				        data: resp.for_armavir_array
				    },{
						name: 'Շիրակ',
				        data: resp.for_shirak_array
				    },{
						name: 'Արարատ',
				        data: resp.for_ararat_array
				    },{
						name: 'Արագածոտն',
				        data: resp.for_aragatsotn_array
				    },{
						name: 'Գեղարքունիք',
				        data: resp.for_gexarquniq_array
				    },{
						name: 'ՀՀ, ԼՂՀ բոլոր գյուղերը',
				        data: resp.for_all_regions_array
				    },{
						name: 'Բոլոր Պատվերները',
				        data: resp.for_all_orders
				    }
				    ]

				});
			}
			function createHighChartGraphicBySenderCountry(resp){
				var for_show_dates = [];
					resp.for_show_dates.forEach(function(item) {
					  for_show_dates.push(item[0]);
					});
					Highcharts.chart('containerSenderCountry', {
					chart: {
				        margin: [0, 0, 150, 0]
				    },
				    xAxis: {
				        categories: for_show_dates,
				        labels: {
				            rotation: 60
				        },
				        opposite:true
				    },
				    title:{
						text:'By Sender Country'
					},

				    series: [{
						name: 'Հայաստան',
				        data: resp.for_Armenia_array
				    },{
						name: 'Ռուսաստան',
				        data: resp.for_Russia_array
				    },{
						name: 'ԱՄՆ',
				        data: resp.for_USA_array
				    },{
						name: 'Գերմանիա',
				        data: resp.for_Germany_array
				    },{
						name: 'Իսպանիա',
				        data: resp.for_Spain_array
				    },{
						name: '-- Անհնարին է իմանալ Երկիրը --',
				        data: resp.for_unknown_country_array
				    },{
						name: 'Ուկրաինա',
				        data: resp.for_Ukraine_array
				    },{
						name: 'Ավստրալիա',
				        data: resp.for_Australia_array
				    },{
						name: 'Բելառուս',
				        data: resp.for_Belarus_array
				    },{
						name: 'Հունաստան',
				        data: resp.for_Greece_array
				    },{
						name: 'Կանադա',
				        data: resp.for_Canada_array
				    },{
						name: 'Անգլիա',
				        data: resp.for_United_Kingdom_array
				    },{
						name: 'Դուբայ',
				        data: resp.for_United_Arab_Emirates_array
				    },{
						name: 'Վրաստան',
				        data: resp.for_Georgia_array
				    },{
						name: 'Ավստրիա',
				        data: resp.for_Austria_array
				    },{
						name: 'Շվեդիա',
				        data: resp.for_Sweden_array
				    },{
						name: 'Հոլանդիա',
				        data: resp.for_Netherlands_array
				    },{
						name: 'Բելգիա',
				        data: resp.for_Belgium_array
				    },{
						name: 'Չեխիա',
				        data: resp.for_Czech_Republic_array
				    },{
						name: 'Քուվեյթ',
				        data: resp.for_Kuwait_array
				    },{
						name: 'Իտալիա',
				        data: resp.for_Italy_array
				    },{
						name: 'Ուզբեկստան',
				        data: resp.for_Uzbekistan_array
				    },{
						name: 'Ղազախստան',
				        data: resp.for_Kazakhstan_array
				    },{
						name: 'Արգենտինա',
				        data: resp.for_Argentina_array
				    },{
						name: 'Արցախ',
				        data: resp.for_Artsakh_array
				    },{
						name: 'Կիպրոս',
				        data: resp.for_Cyprus_array
				    },{
						name: 'Հնդկաստան',
				        data: resp.for_India_array
				    },{
						name: 'Իսրաել',
				        data: resp.for_Israel_array
				    },{
						name: 'Սաուդիան Արաբիա',
				        data: resp.for_Saudi_Arabia_array
				    },{
						name: 'Բոլոր Պատվերները',
				        data: resp.for_all_orders
				    }
				    ]

				});
			}
			function createHighChartGraphicByDeliveryCountry(resp){
				var for_show_dates = [];
					resp.for_show_dates.forEach(function(item) {
					  for_show_dates.push(item[0]);
					});
					Highcharts.chart('containerDeliveryCountry', {
					chart: {
				        marginBottom: 80
				    },
				    xAxis: {
				        categories: for_show_dates,
				        labels: {
				            rotation: 60
				        },
				        opposite:true
				    },
				    title:{
						text:'By Delivery Country'
					},

				    series: [{
						name: 'Հայաստան',
				        data: resp.for_Armenia_array
				    },{
						name: 'Ֆրանսիա',
				        data: resp.for_France_array
				    },{
						name: 'Մոսկվա - Ռուսաստան',
				        data: resp.for_Moscow_array
				    },{
						name: 'Իսպանիա',
				        data: resp.for_Spain_array
				    },{
						name: 'Արտերկիր(հիմա չունենք)',
				        data: resp.for_abroad_array
				    },{
						name: 'Թեհրան',
				        data: resp.for_Tehran_array
				    },{
						name: 'Բոլոր Պատվերները',
				        data: resp.for_all_orders
				    }
				    ]

				});
			}
			function createHighChartGraphicByCurrency(resp){
				var for_show_dates = [];
					resp.for_show_dates.forEach(function(item) {
					  for_show_dates.push(item[0]);
					});
					Highcharts.chart('containerCommunication', {
					chart: {
				        marginBottom: 80
				    },
				    xAxis: {
				        categories: for_show_dates,
				        labels: {
				            rotation: 60
				        },
				        opposite:true
				    },
				    title:{
						text:'By Currency'
					},

				    series: [{
						name: 'USD',
				        data: resp.for_usd_array
				    },{
						name: 'RUB',
				        data: resp.for_rub_array
				    },{
						name: 'AMD',
				        data: resp.for_amd_array
				    },{
						name: 'EUR',
				        data: resp.for_eur_array
				    },{
						name: 'GBP',
				        data: resp.for_gbp_array
				    },{
						name: 'IRR',
				        data: resp.for_irr_array
				    },{
						name: 'Բոլոր Պատվերները',
				        data: resp.for_all_orders
				    }
				    ]

				});

			}
			function createHighChartGraphicByCommunication(resp){
					var for_show_dates = [];
					resp.for_show_dates.forEach(function(item) {
					  for_show_dates.push(item[0]);
					});
					Highcharts.chart('containerCommunication', {
					chart: {
				        marginBottom: 80
				    },
				    xAxis: {
				        categories: for_show_dates,
				        labels: {
				            rotation: 60
				        },
				        opposite:true
				    },
				    title:{
						text:'By Communication'
					},

				    series: [{
						name: 'ONLINE ORDER',
				        data: resp.for_online_order_array
				    },{
						name: 'Live Chat',
				        data: resp.for_live_chat_array
				    },{
						name: 'Skype',
				        data: resp.for_skype_array
				    },{
						name: 'All Int. 055.500.956',
				        data: resp.for_all_int_055_500_956_array
				    },{
						name: 'An.am 055.242.242',
				        data: resp.for_an_am_055_242_242_array
				    },{
						name: 'F-A.am 091.356.937',
				        data: resp.for_f_a_am_091_356_937_array
				    },{
						name: 'Rus +7.958.401.8345',
				        data: resp.for_rus_758_401_8345_array
				    },{
						name: 'European',
				        data: resp.for_european_array
				    },{
						name: 'US +1.818.570.5800',
				        data: resp.for_us_1815705800_array
				    },{
						name: 'Email',
				        data: resp.for_email_array
				    },{
						name: 'Քաղաքային',
				        data: resp.for_town_array
				    },{
						name: 'By Partner',
				        data: resp.for_by_partner_array
				    },{
						name: 'Viber',
				        data: resp.for_viber_array
				    },{
						name: 'Whatsapp',
				        data: resp.for_whatsapp_array
				    },{
						name: 'Facebook',
				        data: resp.for_facebook_array
				    },{
						name: 'Instagram',
				        data: resp.for_instagram_array
				    },{
						name: 'Vkontakte',
				        data: resp.for_vkontakte_array
				    },{
						name: 'Telegram',
				        data: resp.for_telegram_array
				    },{
						name: '3-rd Step',
				        data: resp.for_3_rd_step_array
				    },{
						name: 'Ծանոթ, Բարեկամ',
				        data: resp.for_familiar_friend_array
				    },{
						name: 'Բոլոր Պատվերները',
				        data: resp.for_all_orders
				    }
				    ]

				});
			}
			function createHighChartGraphicByPaymentType(resp){
					var for_show_dates = [];
					resp.for_show_dates.forEach(function(item) {
					  for_show_dates.push(item[0]);
					});
					Highcharts.chart('containerPaymentType', {
					chart: {
				        marginBottom: 80
				    },
				    xAxis: {
				        categories: for_show_dates,
				        labels: {
				            rotation: 60
				        },
				        opposite:true
				    },
				    title:{
						text:'By Payment Type'
					},

				    series: [{
						name: 'Unistream',
				        data: resp.for_unistream_array
				    },{
						name: 'Moneygram',
				        data: resp.for_moneygram_array
				    },{
						name: 'Ria',
				        data: resp.for_ria_array
				    },{
						name: 'Բանկային փոխանցում',
				        data: resp.for_bank_transfer_array
				    },{
						name: 'Webmoney',
				        data: resp.for_webmoney_array
				    },{
						name: 'Yandex',
				        data: resp.for_yandex_array
				    },{
						name: 'Qiwi',
				        data: resp.for_qiwi_array
				    },{
						name: 'Paypal',
				        data: resp.for_paypal_array
				    },{
						name: 'Կանխիկ Խանութում',
				        data: resp.for_cash_array
				    },{
						name: 'Կանխիկ վճարում Առաքիչին',
				        data: resp.for_walletone_array
				    },{
						name: 'Idram',
				        data: resp.for_idram_array
				    },{
						name: 'Ameriabank',
				        data: resp.for_ameriabank_array
				    },{
						name: 'Telcell',
				        data: resp.for_telcell_array
				    },{
						name: 'Cberbank',
				        data: resp.for_cberbank_array
				    },{
						name: 'Ապառիկ Տրված',
				        data: resp.for_nisya_array
				    },{
						name: 'Ըստ Հաշիվ Ապրանքագրի',
				        data: resp.for_bank_invoice_array
				    },{
						name: 'La Caxia Barcelona',
				        data: resp.for_la_caxia_barcelona_array
				    },{
						name: 'Credit Cards by Stripe',
				        data: resp.for_credit_card_stripe_array
				    },{
						name: 'PayPal Barcelona',
				        data: resp.for_paypal_spain_array
				    },{
						name: 'ACBA Օնլայն',
				        data: resp.for_acba_online_array
				    },{
						name: 'Հարկային Հաշվով',
				        data: resp.for_harkayin_hashvov_orders
				    },{
						name: 'ՀԴՄ Կտրոնով',
				        data: resp.for_hdm_ktronov_orders
				    },{
						name: 'Բոլոր Պատվերները',
				        data: resp.for_all_orders
				    },{
						name: 'Золотая Корона',
				        data: resp.for_zolotaya_korona_array
				    }
				    ]

				});
			}
			function createHighChartGraphicByStatus(resp){
					var for_show_dates = [];
					resp.for_show_dates.forEach(function(item) {
					  for_show_dates.push(item[0]);
					});
					Highcharts.chart('containerStatus', {
					chart: {
				        marginBottom: 80
				    },
				    xAxis: {
				        categories: for_show_dates,
				        labels: {
				            rotation: 60
				        },
				        opposite:true
				    },
				    title:{
						text:'By Status'
					},

				    series: [{
						name: 'Հաստատված',
				        data: resp.for_hastatvac_array
				    },{
						name: 'Անավարտ',
				        data: resp.for_anavart_array
				    },{
						name: 'Առաքված',
				        data: resp.for_araqvac_array
				    },{
						name: 'Չեղյալ',
				        data: resp.for_chexyal_array
				    },{
						name: 'Բաց Թողած',
				        data: resp.for_bac_toxac_array
				    },{
						name: 'Ճանապարհին',
				        data: resp.for_janaparhin_array
				    },{
						name: 'Վերադարձրած',
				        data: resp.for_veradararcac_array
				    },{
						name: 'Կոմունիկացիա',
				        data: resp.for_komunikacia_array
				    },{
						name: 'Դուբլիկատ',
				        data: resp.for_dublikat_array
				    },{
						name: 'Ավտոմատ',
				        data: resp.for_avtomat_array
				    },{
						name: 'Հրաժարվել է առաքել',
				        data: resp.for_hrajarvel_e_araqel_array
				    },{
						name: 'Պատրաստ',
				        data: resp.for_patrast_array
				    },{
						name: 'Հաստատված առաքիչի կողմից',
				        data: resp.for_hastatvac_araqichi_koxmic_array
				    },{
						name: 'Բոլոր Պատվերները',
				        data: resp.for_all_orders
				    }
				    ]

				});
			}
		})
	</script>
</body>
</html>