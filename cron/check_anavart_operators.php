<?php
	@include('database.php');
	$database = new Database();
    $on_off = $database->getOnOff('ajax_check_anavart_operators_mail');
    if($on_off[0]['action'] == 1){
        $logged_user_id = 30;
        $userInfo = $database->userInfoById($logged_user_id);
        date_default_timezone_set("Asia/Yerevan");
        print "<pre>";
        $getAnavartOrders = $database->getAnavartOrdersOperatorName($userInfo['username']);
       
        if(count($getAnavartOrders) > 0){
            $orderVariables = "";
            foreach($getAnavartOrders as $key=>$value){
                $todayAnavartNshum = $database->getAnavartRecordsToday($value['id'],$logged_user_id);
                if(!$todayAnavartNshum){
                    $orderVariables.="<a target='_blank' href='https://new.regard-group.ru/account/flower_orders/order.php?orderId=" . $value['id'] . "'>" . $value['id'] . "</a>,";
                }
            }
            $orderVariables = substr($orderVariables, 0, -1);
            // $to      = 'notification@regard-group.com';
            //$to      = 'jigyarov.jigyar@gmail.com';
			$to      = 'delivery@flowers-armenia.am';
            $subject =  "Հարգելի " . explode(' ',$userInfo['full_name_am'])[0];
            $message = " Հարգելի " . explode(' ',$userInfo['full_name_am'])[0] . " դուք ունեք կցված " . $orderVariables . " Անավարտ պատվերներ որոնցով հարկավոր է այսօր զբաղվել   ";
            $message.=  "<br><br> RG System checking notification time: <span style='color:grey'> " . date('d-M-Y H:i:s') . "</span>";
            $headers = 'From: autocontrol@regard-group.com' . "\r\n" .
                'Reply-To: autocontrol@regard-group.com' . "\r\n" .
                'Content-Type: text/html; charset=UTF-8' . "\r\n" .
                'X-Mailer: PHP/' . phpversion();
            $headers .= "";
            var_dump(mail($to, $subject, $message, $headers),$orderVariables);die;

        }
        var_dump($totalOrderds);die;
      
        
    }
  
    return true;
?>