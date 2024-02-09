<?php
	@include('database.php');
	$database = new Database();
	$on_off = $database->getOnOff('check_delivery_expire_notification');
    if($on_off[0]['action'] == 1){
        $orders_info = $database->getTodayOrdersRobots();
    	$result = [];
        date_default_timezone_set("Asia/Yerevan");
        $stream_opts = [
            "ssl" => [
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ]
        ];
        foreach($orders_info as $key=>$value){
            $datetime2 = new DateTime(date('Y-m-d H:i:s'));
            $datetime1 = new DateTime($value['created_date'] . " " . $value['created_time']);
        
            $interval = $datetime2->diff($datetime1);
            $timeLast = '';
            $sendMalus = '';
            if($interval->format('%h') > 0){
                $sendMalus = " (1 MALUS)";
                $timeLast.= $interval->format('%h') . ' ժամ';
            }
            if($interval->format('%i') > 0){
                if($interval->format('%i') > 59){
                    $sendMalus = " (1 MALUS)";
                }
                $timeLast.= $interval->format('%i') . ' րոպե է';
            }
            else{
                $timeLast.= ' է ';
            }
            if($interval->format('%i') > 10){
                //$to      = 'notification@regard-group.com';
                // $to      = 'hrach.avagyan96@gmail.com';
    			$to      = 'delivery@flowers-armenia.am';
                $subject = "Արդեն " . $timeLast ." , որ " . $value['id'] . " պատվերի Ռոբոտը խմբագրված չի" . $sendMalus;
                $message = $value['created_time'] . " " . $value['created_date'] . " ստացած <a target='_blank' href='https://new.regard-group.ru/account/flower_orders/order.php?orderId=" . $value['id'] . "'>" . $value['id'] . "</a> պատվերի տվյալները խմբագրված և համապատասխան ընթացք տրված չի:";
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