<?php
$secureKey = "+1Az%9Za&";

$ameriaBankParms['soapUrl'] = "https://payments.ameriabank.am/webservice/PaymentService.svc?wsdl";
$ameriaBankParms['payUrl'] = "https://payments.ameriabank.am/forms/frm_paymentstype.aspx";
$ameriaBankParms['backUrl'] = "http://regard-group.com/pay/?ptype=ameriabank&action=check";
$ameriaBankParms['ClientID'] = "6c459996-f842-49b2-bf2b-8abb1fa8bdea";
$ameriaBankParms['Username']= "3d19531518";
$ameriaBankParms['Password']= "Z*iY34*Z1879XWgD";
$ameriaBankParms['Description'] = "Test Payment";
$ameriaBankParms['OrderID']= "";// orderID wich must be unique for every transaction and must be int32;
$ameriaBankParms['PaymentAmount']= "10"; // payment amount of your Order <<price>>