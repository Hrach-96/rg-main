<?php
session_start();
include("../apay/pay.api.php");
include("../configuration.php");
$access = auth::checkUserAccess($secureKey);
if($access){
	header("location:../account");
}
if(isset($_REQUEST["submit"]))
{
	if(isset($_REQUEST["username"]))
	{	
		$username = htmlentities($_REQUEST["username"]);
		$username = str_replace(" ","",$username);
		if($username != "")
		{
			if(isset($_REQUEST["password"]))
			{	
				$password = htmlentities($_REQUEST["password"]);
				$password = str_replace(" ","",$password);
				
				if($password != "")
				{
					
					$access = auth::getAccess($username,$password,$secureKey);
					
					if($access)
					{
						header("location:../account");
					}
				}
			}
		}
	}
}
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<!-- Meta, title, CSS, favicons, etc. -->
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="description" content="Apay gateway">
		<meta name="keywords" content="paypal, payment,visa ,mastercard,payment getway,payment gateway">
		<meta name="author" content="Davit Gabrielyan, Ruben Mnatsakanyan">
		<link rel="stylesheet" href="../template/login/main.css">
		<style type="text/css">
			video#bgvid {
				position: fixed; right: 0; bottom: 0;
				min-width: 100%; min-height: 100%;
				width: auto; height: auto; z-index: -100;
				background: url(poster.jpg) no-repeat;
				background-size: cover;
			}
			@media screen and (max-device-width: 800px) {
				body { background: url(poster.jpg) #000 no-repeat center center fixed; }
				#bgvid { display: none; }
			}
		</style>
		<!-- Bootstrap minified CSS -->
		<link rel="stylesheet" href="../template/bootstrap/css/bootstrap.min.css">
		<!-- Bootstrap optional theme -->
		<link rel="stylesheet" href="../template/bootstrap/css/bootstrap-theme.min.css">
		<!--[if lt IE 9]>
		<script>
		document.createElement('video');
		</script>
		<![endif]-->
		<title>Login</title>
	</head>
	<body>
		<div id="container">
			<!--<div id="menu" style="background: #f8f8f8;padding: 5px;">
				<!--<div id="logo"><img src="../template/apay.png"></div>
				<div id="logo"></div>
				<a id="menuItem" href="#">HOME</a>
				<a id="menuItem" href="#">ABOUT</a>
				<a id="menuItem" href="#">CONTUCT US</a>
			</div>-->
			<div id="authorization" align="center">
				
				<div id="aItem" style="width:100%;max-width:390px;border:0px;">
						<div id="sign-in" style="border:0;background: #f9f9f9;text-align:center;border-radius:5px;margin-top:25px;opacity: 0.8;">
							<form method="POST" enctype="application/x-www-form-urlencoded" name="fSign-in" id="fSign-in">
								<h1 style="width:100%;">RG-SYSTEM</h1>
								<input name="username" type="text" id="username" form="fSign-in" autocomplete="off" placeholder="Username" class="form-control" required>
								<br>
								<input name="password" type="password" id="password" form="fSign-in" autocomplete="off" placeholder="Password" class="form-control" required>
								<br>
								<input name="submit" type="submit" id="submit" form="fSign-in" autocomplete="off" value="Sign In" class="btn btn-default" style="width:100%;">
							</form>
						</div>
				</div>
			</div>
		</div>
		<video autoplay loop poster="poster.jpg" id="bgvid">
		<!--<source src="polina.webm" type="video/webm">-->
		<source src="background.mp4" type="video/mp4">
		</video>
	<!-- initialize library-->
		<!-- Latest jquery compiled and minified JavaScript -->
		<script src="https://code.jquery.com/jquery-latest.min.js"></script>
		<!-- Bootstrap minified JavaScript -->
		<script src="../template/bootstrap/js/bootstrap.min.js"></script>
		<!--end initialize library-->
	</body>
</html>