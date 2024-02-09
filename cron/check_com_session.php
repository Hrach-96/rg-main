<?php
	@include('database_com.php');
	$database = new Database();
	$dbCon = new mysqli('82.202.170.110', 'admin_fa', 'RGfacom21$');
    if($dbCon->connect_errno){
        $to      = 'notification@regard-group.com';
        $subject = date('d-M-Y H:i:s') . "flowers-armenia.com Mysql Server Is Down";
        $message = date('d-M-Y H:i:s') . "The Auto fixing program just fixed the server owerloaded issue. If you will recive a few emails in one hour, then please check the https://www.flowers-armenia.com website. In case of meeting any issue, pleae call to Ruben to inform about this problem.";
        $headers = 'From: autocontrol@regard-group.com' . "\r\n" .
            'Reply-To: autocontrol@regard-group.com' . "\r\n" .
            'Bcc: hrach.avvagyan@gmail.com' . "\r\n" .
            'Content-Type: text/html; charset=UTF-8' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();
        mail($to, $subject, $message, $headers);
        $database->removeSessionTableContent();
    }
    $row_table = $database->geetSessionTable();
    if(count($row_table) > 2500){
		
		$database->removeSessionTableContent();
        var_dump(count($row_table));die;

		$to      = 'delivery@flowers-armenia.am';
        $subject = date('d-M-Y H:i:s') . " The flowers-armenia.com was Owerloaded, the auto fixing program just cleanuped the DB";
        $message = date('d-M-Y H:i:s') . " The Auto fixing program just cleanuped the server owerloaded issue. If you will recive a few emails in one hour, then please check https://www.flowers-armenia.com website. In case of meeting any issue, pleae call to Ruben to inform about this problem.";
        $headers = 'From: autocontrol@regard-group.com' . "\r\n" .
            'Reply-To: autocontrol@regard-group.com' . "\r\n" .
            'Content-Type: text/html; charset=UTF-8' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();
        $headers .= "";
        var_dump(mail($to, $subject, $message, $headers));die;
		
    }
	elseif($row_table == -1){ // whet Tabel in Use // by Hacker attack
		$to      = 'delivery@flowers-armenia.am';
        $subject = date('d-M-Y H:i:s') . " The flowers-armenia.com MySQL DB has been Repaired, Truncated by the auto fixing program";
        $message = date('d-M-Y H:i:s') . " The Auto fixing program just Repaired, Truncated MySQL DB. If you will recive a few emails in one hour, then please check https://www.flowers-armenia.com website. In case of meeting any issue, pleae call to Ruben to inform about this problem.";
        $headers = 'From: autocontrol@regard-group.com' . "\r\n" .
            'Reply-To: autocontrol@regard-group.com' . "\r\n" .
            'Content-Type: text/html; charset=UTF-8' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();
        $headers .= "";
        var_dump(mail($to, $subject, $message, $headers));die;
	}
    else{
		//Success

    }
?>