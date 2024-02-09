<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=Edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>check</title>
</head>

<body style="background:#F7F7F7 url('http://anahit.am/apay/template/images/background.png') repeat;">
<div id="cBase" style="border-radius:3px;max-width:600px;background:#fff;margin-left:auto;margin-right:auto;margin-top:5%;padding:5px;text-align:center;">
	<div id="cHeader" style="background:url('http://anahit.am/apay/template/images/header.png') repeat;border:1px solid #838080;border-radius:3px;text-align:left;font-size:16px;font-weight:bold;padding:5px;color:#E0D8D8;">
        <ul style="list-style-type: none;margin: 0;padding: 0;">
		  <li style="display: inline;margin-right:5px;">TRANSACTION</li>
		  <li style="display: inline;">START:</li>
		  <li style="display: inline;background:url('http://anahit.am/apay/template/images/paper.png') repeat;color:#3F424F;padding-left:3px;padding-right:3px;border-radius:3px;">{transaction_start}</li>
		  <li style="display: inline;">END:</li>
		  <li style="display: inline;background:url('http://anahit.am/apay/template/images/paper.png') repeat;color:#3F424F;padding-left:3px;padding-right:3px;border-radius:3px;">{transaction_end}</li>
		  <li style="display: inline;">UTC</li>
		</ul>   
    </div>
	<div id="cContent" style="border: dashed 1px #999;border-radius: 5px;padding: 5px;margin-top: 5px;">
        <div style="padding:5px;border-bottom:solid 2px #838080;text-align:left;">
		  <span style="text-align:left;color:#666363;margin-right:5px;float:left;min-width:150px;">TRANSACTION ID:</span>
		  <span style="text-align:left;color:#A90A0A;font-weight:bold;">{transaction_id}</span>
		</div> 
		<div style="padding:5px;border-bottom:solid 2px #838080;text-align:left;">
		  <span style="text-align:left;color:#666363;margin-right:5px;float:left;min-width:150px;">RECEIPT ID:</span>
		  <span style="text-align:left;color:#A90A0A;font-weight:bold;">{order_id}</span>
		</div>
		<div style="padding:5px;border-bottom:solid 2px #838080;text-align:left;">
		  <span style="text-align:left;color:#666363;margin-right:5px;float:left;min-width:150px;">DESCRIPTION:</span>
		  <span style="text-align:left;color:#A90A0A;font-weight:bold;">{desc}</span>
		</div>
		<div style="padding:5px;border-bottom:solid 2px #838080;text-align:left;">
		  <span style="text-align:left;color:#666363;margin-right:5px;float:left;min-width:150px;">STATUS:</span>
		  <span style="text-align:left;color:#A90A0A;font-weight:bold;">Paid with Ameriabank </span>
		</div>
		<div style="padding:5px;border-bottom:solid 2px #838080;text-align:left;">
		  <span style="text-align:left;color:#666363;margin-right:5px;float:left;min-width:150px;">AMOUNT IN AMD:</span>
		  <span style="text-align:left;color:#A90A0A;font-weight:bold;">{order_price}</span>
		</div>
		<div style="padding:5px;border-bottom:solid 2px #838080;text-align:left;">
		{client_adress}
		</div>
    </div>
	<div style="margin-top: 5px;text-align:right;font-size:10px;color:666;">{ip}<div>
</div>
</body>
</html>
