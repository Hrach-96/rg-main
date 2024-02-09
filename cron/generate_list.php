<?php
	@include('database.php');
	$database = new Database();
    $not_checked_orders_statuses = $database->notCheckedOrders();
    date_default_timezone_set("Asia/Yerevan");
    $subject = 'Orders List - ' . date('Y-M-d H:m:s');
    $not_checked_html = '';
    if(count($not_checked_orders_statuses) > 0){
        $not_checked_html = '<table border="1">';
            $not_checked_html.= '<tr>';
            	$not_checked_html.= '<td colspan="7">';
            		$not_checked_html.= '<h2>Չստուգվածներ</h2>';
            	$not_checked_html.= '</td>';
            $not_checked_html.= '</tr>';
            $not_checked_html.= '<tr>';
                $not_checked_html.= '<th style="width:60px">';
                    $not_checked_html.= 'Համարը';
                $not_checked_html.= '</th>';
                $not_checked_html.= '<th style="width:100px">';
                    $not_checked_html.= 'Առաքման օր';
                $not_checked_html.= '</th>';
                $not_checked_html.= '<th style="width:100px">';
                    $not_checked_html.= 'Երկիրը';
                $not_checked_html.= '</th>';
                $not_checked_html.= '<th style="width:80px">';
                    $not_checked_html.= 'Արժեքը';
                $not_checked_html.= '</th>';
                $not_checked_html.= '<th style="width:80px">';
                    $not_checked_html.= 'Ձևակերպողը';
                $not_checked_html.= '</th>';
    	        $not_checked_html.= '<th style="width:45px">';
    	            $not_checked_html.= 'Ստս․';
    	        $not_checked_html.= '</th>';
                $not_checked_html.= '<th>';
                    $not_checked_html.= 'Օպերատորի նշումներ';
                $not_checked_html.= '</th>';
            $not_checked_html.= '</tr>';
        foreach($not_checked_orders_statuses as $key=>$value){
            $ordersByStatus = $database->ordersByStatusNotConfirmed($value['delivery_status']);
            $statusInfo = $database->statusInfo($value['delivery_status'])[0];
            $not_checked_html.="<tr>";
                $not_checked_html.="<td colspan='7'>";
                    $not_checked_html.=$statusInfo['name_am'];
                $not_checked_html.="</td>";
            $not_checked_html.="</tr>";
            foreach($ordersByStatus as $order){
                $countryInfo = $database->countryInfo($order['sender_country'])[0];
                $operatorInfo = $database->operatorInfo($order['operator'])[0];
                $notesInfo = $database->notesInfo(2,$order['id']);
                $noteText = '';
                if(!empty($notesInfo)){
                    $noteText = $notesInfo[0]['value'];
                }
                $not_checked_html.= '<tr>';
                    $not_checked_html.= '<th>';
                        $not_checked_html.= "<a href='http://new.regard-group.ru/account/flower_orders/order.php?orderId=" . $order['id'] . "'>" . $order['id'] . "</a>";
                    $not_checked_html.= '</th>';
                    $not_checked_html.= '<th>';
                        $not_checked_html.= date('d-M-Y',strtotime($order['delivery_date']));
                    $not_checked_html.= '</th>';
                    $not_checked_html.= '<th>';
                        $not_checked_html.= $countryInfo['name_am'] ;
                    $not_checked_html.= '</th>';
                    $not_checked_html.= '<th>';
                        $not_checked_html.= '<img src="http://new.regard-group.ru/template/icons/currency/' . $order['currency'] . '.png" width="15px">' .  number_format($order['price'], '2');
                    $not_checked_html.= '</th>';
                    $not_checked_html.= '<th>';
                        $not_checked_html.= explode(' ',$operatorInfo['full_name_am'])[0];
                    $not_checked_html.= '</th>';
                    $not_checked_html.= '<th style="font-size:11px;font-weight:normal">';
                        $not_checked_html.= "<img style='width:20px' src='http://new.regard-group.ru/template/icons/status/" . $order['delivery_status'] . ".png'>";
                    $not_checked_html.= '</th>';
                    $not_checked_html.= '<th style="font-size:11px;font-weight:normal">';
                        $not_checked_html.= $noteText;
                    $not_checked_html.= '</th>';
                $not_checked_html.= '</tr>';
            }
        }
        $not_checked_html.='</table>';
    }
    sleep(4);
    $chhastatvac_order_status = $database->chhaastatvacOrderStatuses();
    $chhastatvac_html = '';
    if(count($chhastatvac_order_status) > 0){
        $chhastatvac_html = '<table border="1">';
        $chhastatvac_html.= '<tr>';
        	$chhastatvac_html.= '<td colspan="7">';
        		$chhastatvac_html.= '<h2>Չհաստատվածներ</h2>';
        	$chhastatvac_html.= '</td>';
        $chhastatvac_html.= '</tr>';
        $chhastatvac_html.= '<tr>';
            $chhastatvac_html.= '<th style="width:60px">';
                $chhastatvac_html.= 'Համարը';
            $chhastatvac_html.= '</th>';
            $chhastatvac_html.= '<th style="width:100px">';
                $chhastatvac_html.= 'Առաքման օր';
            $chhastatvac_html.= '</th>';
            $chhastatvac_html.= '<th style="width:100px">';
                $chhastatvac_html.= 'Երկիրը';
            $chhastatvac_html.= '</th>';
            $chhastatvac_html.= '<th style="width:80px">';
                $chhastatvac_html.= 'Արժեքը';
            $chhastatvac_html.= '</th>';
            $chhastatvac_html.= '<th style="width:80px">';
                $chhastatvac_html.= 'Ձևակերպողը';
            $chhastatvac_html.= '</th>';
            $chhastatvac_html.= '<th style="width:45px">';
                $chhastatvac_html.= 'Ստս․';
            $chhastatvac_html.= '</th>';
            $chhastatvac_html.= '<th>';
                $chhastatvac_html.= 'Օպերատորի նշումներ';
            $chhastatvac_html.= '</th>';
        $chhastatvac_html.= '</tr>';
        foreach($chhastatvac_order_status as $key=>$value){
        	$ordersByStatus = $database->ordersByStatus($value['delivery_status']);
            $statusInfo = $database->statusInfo($value['delivery_status'])[0];
            $chhastatvac_html.="<tr>";
                $chhastatvac_html.="<td colspan='7'>";
                    $chhastatvac_html.=$statusInfo['name_am'];
                $chhastatvac_html.="</td>";
            $chhastatvac_html.="</tr>";
            foreach($ordersByStatus as $order){
                $countryInfo = $database->countryInfo($order['sender_country'])[0];
                $operatorInfo = $database->operatorInfo($order['operator'])[0];
                $notesInfo = $database->notesInfo(2,$order['id']);
                $noteText = '';
                if(!empty($notesInfo)){
                    $noteText = $notesInfo[0]['value'];
                }
                $chhastatvac_html.= '<tr>';
                    $chhastatvac_html.= '<th>';
                        $chhastatvac_html.= "<a href='http://new.regard-group.ru/account/flower_orders/order.php?orderId=" . $order['id'] . "'>" . $order['id'] . "</a>";
                    $chhastatvac_html.= '</th>';
                    $chhastatvac_html.= '<th>';
                        $chhastatvac_html.= date('d-M-Y',strtotime($order['delivery_date']));
                    $chhastatvac_html.= '</th>';
                    $chhastatvac_html.= '<th>';
                        $chhastatvac_html.= $countryInfo['name_am'] ;
                    $chhastatvac_html.= '</th>';
                    $chhastatvac_html.= '<th>';
                        $chhastatvac_html.= '<img src="http://new.regard-group.ru/template/icons/currency/' . $order['currency'] . '.png" width="15px">' .  number_format($order['price'], '2');
                    $chhastatvac_html.= '</th>';
                    $chhastatvac_html.= '<th>';
                        $chhastatvac_html.= explode(' ',$operatorInfo['full_name_am'])[0];
                    $chhastatvac_html.= '</th>';
                    $chhastatvac_html.= '<th style="font-size:11px;font-weight:normal">';
                        $chhastatvac_html.= "<img style='width:20px' src='http://new.regard-group.ru/template/icons/status/" . $order['delivery_status'] . ".png'>";
                    $chhastatvac_html.= '</th>';
                    $chhastatvac_html.= '<th style="font-size:11px;font-weight:normal">';
                        $chhastatvac_html.= $noteText;
                    $chhastatvac_html.= '</th>';
                $chhastatvac_html.= '</tr>';
            }
        }
        $chhastatvac_html.='</table>';
    }
    sleep(4);
    $hastatvac_order_status = $database->haastatvacOrderStatuses();
    $hastatvac_html = '';
    if(count($hastatvac_order_status) > 0){
        $hastatvac_html = '<table border="1">';
        $hastatvac_html.= '<tr>';
        	$hastatvac_html.= '<td colspan="7">';
        		$hastatvac_html.= '<h2>Հաստատվածներ</h2>';
        	$hastatvac_html.= '</td>';
        $hastatvac_html.= '</tr>';
        $hastatvac_html.= '<tr>';
            $hastatvac_html.= '<th style="width:60px">';
                $hastatvac_html.= 'Համարը';
            $hastatvac_html.= '</th>';
            $hastatvac_html.= '<th style="width:100px">';
                $hastatvac_html.= 'Առաքման օր';
            $hastatvac_html.= '</th>';
            $hastatvac_html.= '<th style="width:100px">';
                $hastatvac_html.= 'Երկիրը';
            $hastatvac_html.= '</th>';
            $hastatvac_html.= '<th style="width:80px">';
                $hastatvac_html.= 'Արժեքը';
            $hastatvac_html.= '</th>';
            $hastatvac_html.= '<th style="width:80px">';
                $hastatvac_html.= 'Ձևակերպողը';
            $hastatvac_html.= '</th>';
            $hastatvac_html.= '<th style="width:45px">';
                $hastatvac_html.= 'Ստս․';
            $hastatvac_html.= '</th>';
            $hastatvac_html.= '<th>';
                $hastatvac_html.= 'Օպերատորի նշումներ';
            $hastatvac_html.= '</th>';
        $hastatvac_html.= '</tr>';
        foreach($hastatvac_order_status as $key=>$value){
        	$ordersByStatus = $database->ordersByStatus($value['delivery_status']);
            $statusInfo = $database->statusInfo($value['delivery_status'])[0];
            $hastatvac_html.="<tr>";
                $hastatvac_html.="<td colspan='7'>";
                    $hastatvac_html.=$statusInfo['name_am'];
                $hastatvac_html.="</td>";
            $hastatvac_html.="</tr>";
            foreach($ordersByStatus as $order){
                $countryInfo = $database->countryInfo($order['sender_country'])[0];
                $operatorInfo = $database->operatorInfo($order['operator'])[0];
                $notesInfo = $database->notesInfo(2,$order['id']);
                $noteText = '';
                if(!empty($notesInfo)){
                    $noteText = $notesInfo[0]['value'];
                }
                $hastatvac_html.= '<tr>';
                    $hastatvac_html.= '<th>';
                        $hastatvac_html.= "<a href='http://new.regard-group.ru/account/flower_orders/order.php?orderId=" . $order['id'] . "'>" . $order['id'] . "</a>";
                    $hastatvac_html.= '</th>';
                    $hastatvac_html.= '<th>';
                        $hastatvac_html.= date('d-M-Y',strtotime($order['delivery_date']));
                    $hastatvac_html.= '</th>';
                    $hastatvac_html.= '<th>';
                        $hastatvac_html.= $countryInfo['name_am'] ;
                    $hastatvac_html.= '</th>';
                    $hastatvac_html.= '<th>';
                        $hastatvac_html.= '<img src="http://new.regard-group.ru/template/icons/currency/' . $order['currency'] . '.png" width="15px">' .  number_format($order['price'], '2');
                    $hastatvac_html.= '</th>';
                    $hastatvac_html.= '<th>';
                        $hastatvac_html.= explode(' ',$operatorInfo['full_name_am'])[0];
                    $hastatvac_html.= '</th>';
                    $hastatvac_html.= '<th style="font-size:11px;font-weight:normal">';
                        $hastatvac_html.= "<img style='width:20px' src='http://new.regard-group.ru/template/icons/status/" . $order['delivery_status'] . ".png'>";
                    $hastatvac_html.= '</th>';
                    $hastatvac_html.= '<th style="font-size:11px;font-weight:normal">';
                        $hastatvac_html.= $noteText;
                    $hastatvac_html.= '</th>';
                $hastatvac_html.= '</tr>';
            }
        }
        $hastatvac_html.='</table>';
    }
    sleep(4);
    $delivery_time_18_24_statuses = $database->deliveryTime18_24();
    $delivery_time_18_24 = '';
    if(count($delivery_time_18_24_statuses) > 0){
        $delivery_time_18_24 = '<table border="1">';
        $delivery_time_18_24.= '<tr>';
        	$delivery_time_18_24.= '<td colspan="7">';
        		$delivery_time_18_24.= '<h2>Առաքման ժամ 18։00-24։00</h2>';
        	$delivery_time_18_24.= '</td>';
        $delivery_time_18_24.= '</tr>';
        $delivery_time_18_24.= '<tr>';
            $delivery_time_18_24.= '<th style="width:60px">';
                $delivery_time_18_24.= 'Համարը';
            $delivery_time_18_24.= '</th>';
            $delivery_time_18_24.= '<th style="width:100px">';
                $delivery_time_18_24.= 'Առաքման օր';
            $delivery_time_18_24.= '</th>';
            $delivery_time_18_24.= '<th style="width:100px">';
                $delivery_time_18_24.= 'Երկիրը';
            $delivery_time_18_24.= '</th>';
            $delivery_time_18_24.= '<th style="width:80px">';
                $delivery_time_18_24.= 'Արժեքը';
            $delivery_time_18_24.= '</th>';
            $delivery_time_18_24.= '<th style="width:80px">';
                $delivery_time_18_24.= 'Ձևակերպողը';
            $delivery_time_18_24.= '</th>';
            $delivery_time_18_24.= '<th style="width:45px">';
                $delivery_time_18_24.= 'Ստս․';
            $delivery_time_18_24.= '</th>';
            $delivery_time_18_24.= '<th>';
                $delivery_time_18_24.= 'Օպերատորի նշումներ';
            $delivery_time_18_24.= '</th>';
        $delivery_time_18_24.= '</tr>';
        foreach($delivery_time_18_24_statuses as $key=>$value){
        	$ordersByStatus = $database->ordersByStatusDeliveryDate($value['delivery_status'],'5,6');
            $statusInfo = $database->statusInfo($value['delivery_status'])[0];
            $delivery_time_18_24.="<tr>";
                $delivery_time_18_24.="<td colspan='7'>";
                    $delivery_time_18_24.=$statusInfo['name_am'];
                $delivery_time_18_24.="</td>";
            $delivery_time_18_24.="</tr>";
            foreach($ordersByStatus as $order){
                $countryInfo = $database->countryInfo($order['sender_country'])[0];
                $operatorInfo = $database->operatorInfo($order['operator'])[0];
                $notesInfo = $database->notesInfo(2,$order['id']);
                $noteText = '';
                if(!empty($notesInfo)){
                    $noteText = $notesInfo[0]['value'];
                }
                $delivery_time_18_24.= '<tr>';
                    $delivery_time_18_24.= '<th>';
                        $delivery_time_18_24.= "<a href='http://new.regard-group.ru/account/flower_orders/order.php?orderId=" . $order['id'] . "'>" . $order['id'] . "</a>";
                    $delivery_time_18_24.= '</th>';
                    $delivery_time_18_24.= '<th>';
                        $delivery_time_18_24.= date('d-M-Y',strtotime($order['delivery_date']));
                    $delivery_time_18_24.= '</th>';
                    $delivery_time_18_24.= '<th>';
                        $delivery_time_18_24.= $countryInfo['name_am'] ;
                    $delivery_time_18_24.= '</th>';
                    $delivery_time_18_24.= '<th>';
                        $delivery_time_18_24.= '<img src="http://new.regard-group.ru/template/icons/currency/' . $order['currency'] . '.png" width="15px">' .  number_format($order['price'], '2');
                    $delivery_time_18_24.= '</th>';
                    $delivery_time_18_24.= '<th>';
                        $delivery_time_18_24.= explode(' ',$operatorInfo['full_name_am'])[0];
                    $delivery_time_18_24.= '</th>';
                    $delivery_time_18_24.= '<th style="font-size:11px;font-weight:normal">';
                        $delivery_time_18_24.= "<img style='width:20px' src='http://new.regard-group.ru/template/icons/status/" . $order['delivery_status'] . ".png'>";
                    $delivery_time_18_24.= '</th>';
                    $delivery_time_18_24.= '<th style="font-size:11px;font-weight:normal">';
                        $delivery_time_18_24.= $noteText;
                    $delivery_time_18_24.= '</th>';
                $delivery_time_18_24.= '</tr>';
            }
        }
        $delivery_time_18_24.='</table>';
    }
    sleep(4);
    $delivery_time_00_09_statuses = $database->deliveryTime00_09();
    $delivery_time_00_09 = '';
    if(count($delivery_time_00_09_statuses) > 0){
        $delivery_time_00_09 = '<table border="1">';
        $delivery_time_00_09.= '<tr>';
        	$delivery_time_00_09.= '<td colspan="7">';
        		$delivery_time_00_09.= '<h2>Առաքման ժամ 00։00-09:00</h2>';
        	$delivery_time_00_09.= '</td>';
        $delivery_time_00_09.= '</tr>';
        $delivery_time_00_09.= '<tr>';
            $delivery_time_00_09.= '<th style="width:60px">';
                $delivery_time_00_09.= 'Համարը';
            $delivery_time_00_09.= '</th>';
            $delivery_time_00_09.= '<th style="width:100px">';
                $delivery_time_00_09.= 'Առաքման օր';
            $delivery_time_00_09.= '</th>';
            $delivery_time_00_09.= '<th style="width:100px">';
                $delivery_time_00_09.= 'Երկիրը';
            $delivery_time_00_09.= '</th>';
            $delivery_time_00_09.= '<th style="width:80px">';
                $delivery_time_00_09.= 'Արժեքը';
            $delivery_time_00_09.= '</th>';
            $delivery_time_00_09.= '<th style="width:80px">';
                $delivery_time_00_09.= 'Ձևակերպողը';
            $delivery_time_00_09.= '</th>';
            $delivery_time_00_09.= '<th style="width:45px">';
                $delivery_time_00_09.= 'Ստս․';
            $delivery_time_00_09.= '</th>';
            $delivery_time_00_09.= '<th>';
                $delivery_time_00_09.= 'Օպերատորի նշումներ';
            $delivery_time_00_09.= '</th>';
        $delivery_time_00_09.= '</tr>';
        foreach($delivery_time_00_09_statuses as $key=>$value){
        	$ordersByStatus = $database->ordersByStatusDeliveryDate($value['delivery_status'],'7,8');
            $statusInfo = $database->statusInfo($value['delivery_status'])[0];
            $delivery_time_00_09.="<tr>";
                $delivery_time_00_09.="<td colspan='7'>";
                    $delivery_time_00_09.=$statusInfo['name_am'];
                $delivery_time_00_09.="</td>";
            $delivery_time_00_09.="</tr>";
            foreach($ordersByStatus as $order){
                $countryInfo = $database->countryInfo($order['sender_country'])[0];
                $operatorInfo = $database->operatorInfo($order['operator'])[0];
                $notesInfo = $database->notesInfo(2,$order['id']);
                $noteText = '';
                if(!empty($notesInfo)){
                    $noteText = $notesInfo[0]['value'];
                }
                $delivery_time_00_09.= '<tr>';
                    $delivery_time_00_09.= '<th>';
                        $delivery_time_00_09.= "<a href='http://new.regard-group.ru/account/flower_orders/order.php?orderId=" . $order['id'] . "'>" . $order['id'] . "</a>";
                    $delivery_time_00_09.= '</th>';
                    $delivery_time_00_09.= '<th>';
                        $delivery_time_00_09.= date('d-M-Y',strtotime($order['delivery_date']));
                    $delivery_time_00_09.= '</th>';
                    $delivery_time_00_09.= '<th>';
                        $delivery_time_00_09.= $countryInfo['name_am'] ;
                    $delivery_time_00_09.= '</th>';
                    $delivery_time_00_09.= '<th>';
                        $delivery_time_00_09.= '<img src="http://new.regard-group.ru/template/icons/currency/' . $order['currency'] . '.png" width="15px">' .  number_format($order['price'], '2');
                    $delivery_time_00_09.= '</th>';
                    $delivery_time_00_09.= '<th>';
                        $delivery_time_00_09.= explode(' ',$operatorInfo['full_name_am'])[0];
                    $delivery_time_00_09.= '</th>';
                    $delivery_time_00_09.= '<th style="font-size:11px;font-weight:normal">';
                        $delivery_time_00_09.= "<img style='width:20px' src='http://new.regard-group.ru/template/icons/status/" . $order['delivery_status'] . ".png'>";
                    $delivery_time_00_09.= '</th>';
                    $delivery_time_00_09.= '<th style="font-size:11px;font-weight:normal">';
                        $delivery_time_00_09.= $noteText;
                    $delivery_time_00_09.= '</th>';
                $delivery_time_00_09.= '</tr>';
            }
        }
        $delivery_time_00_09.='</table>';
    }
    sleep(4);
    $delivery_time_manual_statuses = $database->deliveryTimeManual();
    $delivery_time_manualy = '';
    if(count($delivery_time_manual_statuses) > 0){
        $delivery_time_manualy = '<table border="1">';
        $delivery_time_manualy.= '<tr>';
            $delivery_time_manualy.= '<td colspan="7">';
                $delivery_time_manualy.= '<h2>Ֆիքսված ժամեր</h2>';
            $delivery_time_manualy.= '</td>';
        $delivery_time_manualy.= '</tr>';
        $delivery_time_manualy.= '<tr>';
            $delivery_time_manualy.= '<th style="width:60px">';
                $delivery_time_manualy.= 'Համարը';
            $delivery_time_manualy.= '</th>';
            $delivery_time_manualy.= '<th style="width:100px">';
                $delivery_time_manualy.= 'Առաքման օր';
            $delivery_time_manualy.= '</th>';
            $delivery_time_manualy.= '<th style="width:100px">';
                $delivery_time_manualy.= 'Երկիրը';
            $delivery_time_manualy.= '</th>';
            $delivery_time_manualy.= '<th style="width:80px">';
                $delivery_time_manualy.= 'Արժեքը';
            $delivery_time_manualy.= '</th>';
            $delivery_time_manualy.= '<th style="width:80px">';
                $delivery_time_manualy.= 'Ձևակերպողը';
            $delivery_time_manualy.= '</th>';
            $delivery_time_manualy.= '<th style="width:45px">';
                $delivery_time_manualy.= 'Ստս․';
            $delivery_time_manualy.= '</th>';
            $delivery_time_manualy.= '<th>';
                $delivery_time_manualy.= 'Օպերատորի նշումներ';
            $delivery_time_manualy.= '</th>';
        $delivery_time_manualy.= '</tr>';
        foreach($delivery_time_manual_statuses as $key=>$value){
            $ordersByStatus = $database->ordersByStatusTimeManually($value['delivery_status']);
            $statusInfo = $database->statusInfo($value['delivery_status'])[0];
            $delivery_time_manualy.="<tr>";
                $delivery_time_manualy.="<td colspan='7'>";
                    $delivery_time_manualy.=$statusInfo['name_am'];
                $delivery_time_manualy.="</td>";
            $delivery_time_manualy.="</tr>";
            foreach($ordersByStatus as $order){
                $countryInfo = $database->countryInfo($order['sender_country'])[0];
                $operatorInfo = $database->operatorInfo($order['operator'])[0];
                $notesInfo = $database->notesInfo(2,$order['id']);
                $noteText = '';
                if(!empty($notesInfo)){
                    $noteText = $notesInfo[0]['value'];
                }
                $delivery_time_manualy.= '<tr>';
                    $delivery_time_manualy.= '<th>';
                        $delivery_time_manualy.= "<a href='http://new.regard-group.ru/account/flower_orders/order.php?orderId=" . $order['id'] . "'>" . $order['id'] . "</a>";
                    $delivery_time_manualy.= '</th>';
                    $delivery_time_manualy.= '<th>';
                        $delivery_time_manualy.= date('d-M-Y',strtotime($order['delivery_date']));
                    $delivery_time_manualy.= '</th>';
                    $delivery_time_manualy.= '<th>';
                        $delivery_time_manualy.= $countryInfo['name_am'] ;
                    $delivery_time_manualy.= '</th>';
                    $delivery_time_manualy.= '<th>';
                        $delivery_time_manualy.= '<img src="http://new.regard-group.ru/template/icons/currency/' . $order['currency'] . '.png" width="15px">' .  number_format($order['price'], '2');
                    $delivery_time_manualy.= '</th>';
                    $delivery_time_manualy.= '<th>';
                        $delivery_time_manualy.= explode(' ',$operatorInfo['full_name_am'])[0];
                    $delivery_time_manualy.= '</th>';
                    $delivery_time_manualy.= '<th style="font-size:11px;font-weight:normal">';
                        $delivery_time_manualy.= "<img style='width:20px' src='http://new.regard-group.ru/template/icons/status/" . $order['delivery_status'] . ".png'>";
                    $delivery_time_manualy.= '</th>';
                    $delivery_time_manualy.= '<th style="font-size:11px;font-weight:normal">';
                        $delivery_time_manualy.= $noteText;
                    $delivery_time_manualy.= '</th>';
                $delivery_time_manualy.= '</tr>';
            }
        }
        $delivery_time_manualy.='</table>';
    }
	$message = '';
    // $to      = 'hrach.avagyan96@gmail.com';
    // $to      = 'leonid.dan@yahoo.com';
    $to      = 'notification@regard-group.com';
    $headers = 'From: autocontrol@regard-group.com' . "\r\n" .
                'Reply-To: autocontrol@regard-group.com' . "\r\n" .
                // 'Bcc: jigyar.jigyarov@gmail.com' . "\r\n" .
                'Content-Type: text/html; charset=UTF-8' . "\r\n" .
                'X-Mailer: PHP/' . phpversion();
    $message.=$not_checked_html ."<br><br>" . $chhastatvac_html . "<br><br>" . $hastatvac_html . "<br><br>" . $delivery_time_18_24 . "<br><br>" . $delivery_time_00_09 . "<br><br>" . $delivery_time_manualy;
    mail($to, $subject, $message, $headers);
    var_dump($message);die;
    return true;
?>