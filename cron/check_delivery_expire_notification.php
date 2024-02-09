<?php
	@include('database.php');
	$database = new Database();
	$on_off = $database->getOnOff('check_delivery_expire_notification');
    if($on_off[0]['action'] == 1){
        $orders_info = $database->deliveryExpireOrders();
    	$result = [];
        date_default_timezone_set("Asia/Yerevan");
        $stream_opts = [
            "ssl" => [
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ]
        ];
        foreach($orders_info as $order){
            $add_order = false;
            $delivery_date = $order['delivery_date'];
            if($order['delivery_time_manual'] != '' || $order['travel_time_end'] != ''){
                $add_order = true;
                if($order['travel_time_end'] > 0){
                    $delivery_date.= " " . $order['travel_time_end'] . ":00";
                }
                else{
                    $delivery_date.= " " . $order['delivery_time_manual'] . ":00";
                }
            }
            elseif($order['delivery_time'] != ''){
                $add_order = true;
                $delivery_time_info = $database->selectDeliveryTimeById($order['delivery_time']);
                $delivery_time = explode('-',$delivery_time_info[0]['name'])[1];
                $delivery_date.= " " . $delivery_time.":00";
            }
            $time_left = strtotime($delivery_date) - strtotime(date("Y-m-d H:i:s"));
            if($add_order){
                //  && date("Y-m-d H:i:s") < $delivery_date
                if($time_left <= 3600 && $time_left >= -3600){
                    $order_array['order_id'] = $order['id'];
                    $order_array['time_left'] = ceil($time_left/60);
                    $order_array['lose'] = false;
                    $deliveredTime = '';
                    if($order['delivery_time'] > 0){
                        $delivery_time_info = $database->selectDeliveryTimeById($order['delivery_time']);
                        $deliveredTime.= $delivery_time_info[0]['name'];
                    }
                    if($order['delivery_time_manual'] > 0){
                        $deliveredTime.= " " . $order['delivery_time_manual'];
                    }
                    if($order['travel_time_end'] > 0){
                        $deliveredTime.= " " . $order['travel_time_end'];
                    }
                    $order_array['deliveredTime'] = $deliveredTime;
                    if(ceil($time_left/60) < 0){
                        $order_array['lose'] = true;
                    }
                    $result[] = $order_array;
                }
            }
        }
        if(count($result) > 0){
            foreach($result as $res){
                // $to      = 'hrach.avagyan96@gmail.com';
                //$to      = 'notification@regard-group.com';
				$to      = 'delivery@flowers-armenia.am';
                $res_time_left = $res['time_left'];
                if($res_time_left == 0){
                    $res_time_left = 1;
                }
                if($res_time_left < 0){
                    $subject = abs($res_time_left) . " րոպե է արդեն անցել " . $res['order_id'] . " պատվերի Առաքման Վերջնաժամկետից";
                    $message = abs($res_time_left) . " րոպե է արդեն անցել <a target='_blank' href='https://new.regard-group.ru/account/flower_orders/order.php?orderId=" . $res['order_id'] . "'>" . $res['order_id'] . "</a> պատվերի Առաքման Վերջնաժամկետից, (" . $deliveredTime . ") բայց դեռ առաքման ընթացք տրված չի:";
                }
                else{
                    $subject = $res_time_left .  " րոպե է մնացել " . $res['order_id'] . " պատվերի Առաքման Վերջնաժամկետին";
                    $message =  $res_time_left . " րոպե է մնացել <a target='_blank' href='https://new.regard-group.ru/account/flower_orders/order.php?orderId=" . $res['order_id'] . "'>" . $res['order_id'] . "</a> պատվերի Առաքման Վերջնաժամկետից, (" . $deliveredTime . ") բայց դեռ առաքման ընթացք տրված չի:";
                }
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