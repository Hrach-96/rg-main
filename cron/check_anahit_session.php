<?php
	@include('database_com.php');
	$database = new Database();
	$dbCon = new mysqli('82.202.170.110', 'admin_anahit_am', 'SuBHLzVT6e');
    if($dbCon->connect_errno){
        $to      = 'notification@regard-group.com';
        $subject = date('d-M-Y H:i:s') . " Mysql Server Is Down";
        $message = date('d-M-Y H:i:s') . " The jos_session has been auto-empty for fixing the issue. Please check the http://anahit.am/ website from Ruben PC and call to 077-701117 mobile number for informing about this issue ASAP for double checking.";
        $headers = 'From: autocontrol@regard-group.com' . "\r\n" .
            'Reply-To: autocontrol@regard-group.com' . "\r\n" .
            'Bcc: hrach.avvagyan@gmail.com' . "\r\n" .
            'Content-Type: text/html; charset=UTF-8' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();
            die;
        mail($to, $subject, $message, $headers);
        $database->removeSessionTableContent();
    }
    $row_table = $database->geetSessionTable();
    if($row_table){
        var_dump(count($row_table));die;
    }
    else{
        $database->removeSessionTableContent();
        //$to      = 'notification@regard-group.com';
        //$to      = 'hrach.avagyan96@gmail.com';
		$to      = 'delivery@flowers-armenia.am';
        $subject = date('d-M-Y H:i:s') . " The anahit.am was under Hacker attack and may NOT working now";
        $message = date('d-M-Y H:i:s') . " The Auto fixing program just fixed the server owerloaded issue. If you will recive a few emails in one hour, then please check https://www.anahit.am website and call to Ruben to inform about this issue.";
        $headers = 'From: autocontrol@regard-group.com' . "\r\n" .
            'Reply-To: autocontrol@regard-group.com' . "\r\n" .
            'Content-Type: text/html; charset=UTF-8' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();
        $headers .= "";
        var_dump(99);die;
        var_dump(mail($to, $subject, $message, $headers));die;
    }
?>