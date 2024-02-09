<?php
        // $to      = 'jigyar.jigyarov@gmail.com';
	$to      = 'notification@regard-group.com';
    $subject = " Email send time " . date('d-M-Y H:i:s');
    $message =  " Email send time " . date('d-M-Y H:i:s');
    $headers = 'From: autocontrol@regard-group.com' . "\r\n" .
        'Reply-To: autocontrol@regard-group.com' . "\r\n" .
       	'Bcc: jigyar.jigyarov@gmail.com' . "\r\n" .
        'Content-Type: text/html; charset=UTF-8' . "\r\n" .
        'X-Mailer: PHP/' . phpversion();
    $headers .= "";
    die;
    var_dump(mail($to, $subject, $message, $headers));die;
?>