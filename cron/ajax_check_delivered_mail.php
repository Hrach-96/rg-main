<?php
	@include('database.php');
	$database = new Database();
    $on_off = $database->getOnOff('ajax_check_delivered_mail');
    if($on_off[0]['action'] == 1){
        $orders_info = $database->deliveryExpireOrdersToday();
    	$result = [];
        date_default_timezone_set("Asia/Yerevan");
        $stream_opts = [
            "ssl" => [
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ]
        ];
        foreach($orders_info as $order){
            $order_id = $order['id'];
            $sellPointsInfo = $database->deliverySellpointById(1);
            $orderInfo = $database->getOrdersFewDays($order_id);
            $result['order_id'] = $order_id;
            $result['delivered_at'] = $orderInfo[0]['delivered_at'];
            $result['response'] = true;
            if($order_id > 80000){
                if($orderInfo[0]['delivery_status'] == 3){
                    $sellpointsArray = Array();
                    foreach($sellPointsInfo as $value){
                        $sellpointsArray[] = $value['id'];
                    }
                    if(in_array($orderInfo[0]['sell_point'], $sellpointsArray)){
                        $mail_log = $database->getFromMailLog($order_id);
                        if($mail_log){
                            if($mail_log[0]['count'] == 0){
                                $result['response'] = false;
                            }
                        }
                        else{
                            $result['response'] = false;
                        }
                    }
                }
            }
            if($result['response'] == false){
                $datetime2 = new DateTime(date('Y-m-d H:i:s'));
                $datetime1 = new DateTime($result['delivered_at']);
            
                $interval = $datetime1->diff($datetime2);
                $timeLast = '';
                $sendMalus = '';
                if($interval->format('%h') > 0){
                    $sendMalus = " (1 MALUS)";
                    $timeLast.= $interval->format('%h') . ' ժամ ';
                }
                if($interval->format('%i') > 0){
                    if($interval->format('%i') > 59){
                        $sendMalus = " (1 MALUS)";
                    }
                    $timeLast.= $interval->format('%i') . ' րոպե ';
                }
                // $to      = 'hrach.avagyan96@gmail.com';
                //$to      = 'notification@regard-group.com';
				$to      = 'delivery@flowers-armenia.am';
                $subject = "Ավելի քան " . $timeLast . " " . $result['order_id'] . " պատվերի Առաքման Ծանուցումը չի ուղարկվել" . $sendMalus;
                $message = "Ավելի քան " . $timeLast . " <a target='_blank' href='https://new.regard-group.ru/account/flower_orders/order.php?orderId=" . $result['order_id'] . "'>" . $result['order_id'] . "</a> պատվերի Առաքման Ծանուցումը չի ուղարկվել:";
                $message.=  "<br><br> RG System checking notification time: <span style='color:grey'> " . date('d-M-Y H:i:s') . "</span>";
                $headers = 'From: autocontrol@regard-group.com' . "\r\n" .
                    'Reply-To: autocontrol@regard-group.com' . "\r\n" .
                    'Content-Type: text/html; charset=UTF-8' . "\r\n" .
                    'X-Mailer: PHP/' . phpversion();
                $headers .= "";
                mail($to, $subject, $message, $headers);
                $telegram_message = urlencode($message);
                // for RG SYSTEM Telegram message
                $bot_id_florist = '366104506:AAGTt0Kp0igoxxMYi4x2MmcPnOaqZla1lw0';
                $chat_id_florist = '-1001114000413';
                $resp = file_get_contents("https://www.flowers-armenia.am/telegram.php?bot=".$bot_id_florist."&chat_id=" . $chat_id_florist . "&telegram_message=".$telegram_message,false, stream_context_create($stream_opts));
            }
        }
    }
  
    return true;
?>