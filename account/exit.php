<?php
session_start();
include("../apay/pay.api.php");
include("../configuration.php");

auth::destroySession();
setcookie("token", "", time() - 3600, '/');
setcookie("suid", "", time() - 3600, '/');
setcookie("sid", "", time() - 3600, '/');
header("location:../login");

?>