<?php

	@include('database.php');
	$database = new Database();
    $on_off = $database->getOnOff('ajax_check_hastatum_mail');
    if($on_off[0]['action'] == 1){
        $orders_info = $database->deliveryHastatumOrdersToday();
        date_default_timezone_set("Asia/Yerevan");
        $stream_opts = [
            "ssl" => [
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ]
        ];
        $sellPointsInfo = $database->deliverySellpointById(1);
        $sellpointsArray = Array();
        foreach($sellPointsInfo as $value){
            $sellpointsArray[] = $value['id'];
        }
        foreach($orders_info as $order){
            $order_id = $order['id'];
            $orderMailLog = $database->getOrderMailLog($order_id);
            if(count($orderMailLog) < 1 && in_array($order['sell_point'], $sellpointsArray)){
            	$operatorInfo = $database->operatorInfo($order['operator'])[0];
                //$to      = 'notification@regard-group.com';
                // $to      = 'hrach.avagyan96@gmail.com';
				$to      = 'delivery@flowers-armenia.am';
                $subject =  $order_id . " պատվերի հաստատման ծանուցւմը չի ուղարկվել " . explode(' ',$operatorInfo['full_name_am'])[0] . "-ի կողմից";
                $message = " Հարգելի " . explode(' ',$operatorInfo['full_name_am'])[0] . " <a target='_blank' href='https://new.regard-group.ru/account/flower_orders/order.php?orderId=" . $order_id . "'>" . $order_id . "</a> պատվերի հաստատման ծանուցումը դեռ չի ուղարկվել պատվիրատուին․";
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