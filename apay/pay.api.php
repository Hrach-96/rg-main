<?php

function dd($var)
{
    echo '<pre>';
    print_r($var);
    echo '</pre>';
}

function ddd($var)
{
    echo '<pre>';
    var_dump($var);
    echo '</pre>';
}

function dde($var)
{
    echo '<pre>';
    print_r($var);
    echo '</pre>';
    exit('exit');
}

function numberFormat($number)
{
    return number_format($number, 0);
}

include_once $_SERVER['DOCUMENT_ROOT'] . '/database/DatabaseConnection.php';

function paymentTypes()
{
    return array("ameriabank");
}

function ExceptionHandler($e)
{
    die($e);
}

function ErrorHandler($errno, $errstr, $errfile, $errline)
{
    die("RG SYSTEM TEMPORARILY UNAVAILABLE.<br/>Please try again after a few minutes!<!--" . $errstr . ":" . $errfile . ":" . $errline . ":-->");
}

function inRange($num, $min, $max)
{
    if ($num >= $min && $num <= $max) {
        return true;
    } else {
        return false;
    }
}

function FatalErrorHandler()
{
    $last_error = error_get_last();
    if ($last_error['type'] === E_ERROR) {
        if (stristr($last_error['message'], "SOAP-ERROR")) {
            die(json_encode(xe::localXE(2)));
        }
    } else {
        die(print_r($last_error, true));
    }
}


class security
{

    public static function hashCheck($data = 0, $hash = 0, $type = 0)
    {
        $check = false;
        $data = ($type == 0) ? md5($data) : sha1($data);
        $check = ($data == $hash) ? true : false;
        return $check;
    }

    public static function filter($data, $type = 0)
    {
        $check = false;
        if ($type == 0) {
            $check = (is_numeric($data)) ? true : false;
        }
        return $check;
    }

    public static function checkClient($clientId)
    {
        $check = false;

        $data = clientData::getDataByClientId($clientId);
        if ($data != false) {
            $data = $data[0];
            if (isset($data["client_site"])) {

                $url = $data["client_site"];
                $ref = (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : "unknown";
                $check = (preg_match("/" . $url . "*/", $ref)) ? true : false;
                return $check;
            }
        }
        return $check;
    }
}


class auth
{
    public static function genToken($username, $uid, $salt)
    {
        $agent = getway::agent();
        $genId = rand(1, 99) . uniqid() . rand(1, 99);
        $hashId = md5($genId . $salt);
        setcookie("sid", $hashId, 0, '/');//time() + (10 * 365 * 24 * 60 * 60)
        $token = md5($username . $uid . $agent . $salt . $genId);
        self::addAuth($uid, $agent, $token, $genId);
        return $token;
    }

    public static function addAuth($uid, $browser, $token, $genId)
    {
        $date = getway::utc();
        $ip = getway::ip();
        getwayConnect::getwaySend("INSERT INTO user_login(user_id,user_browser,user_token,user_genid,user_date,user_ip,user_access)
            VALUES('{$uid}','{$browser}','{$token}','{$genId}','{$date}','{$ip}',1)");
    }

    public static function addUserLog($uid, $text = "NaN", $action = "login")
    {
        $date = getway::utc();
        $ip = getway::ip();
        getwayConnect::getwaySend("INSERT INTO user_log(user_id,user_action,user_date,user_text,user_ip)
            VALUES('{$uid}','{$action}','{$date}','{$text}','{$ip}')");
    }

    public static function hash($password, $salt)
    {
        return md5($password . $salt);
    }

    public static function getUserData($username, $password)
    {
        return getwayConnect::getwayData("SELECT * FROM user WHERE username = '{$username}' AND password = '{$password}'");
    }

    public static function getUserList()
    {
        return getwayConnect::getwayData("SELECT * FROM user ORDER BY id");
    }
    public static function getUserListByUserActive()
    {
        return getwayConnect::getwayData("SELECT * FROM user ORDER BY user_active desc,id");
    }

    public static function userAdminReg($username, $password, $level, $salt)
    {
        $uExist = self::checkUserExist($username);
        if (!$uExist) {
            $uid = uniqid();
            $hpassword = self::hash($password, $salt);
            $target = "../user_images/";
            $targetFile = $target . $uid. ".". pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            if(move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)){
                return getwayConnect::getwaySend("INSERT INTO user SET uid = '{$uid}', username = '{$username}', password = '{$hpassword}', user_level = '{$level}',user_active = '1',lang = 'am', country_short='0'");
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public static function getAccess($username, $password, $salt)
    {
        $uExist = self::checkUserExist($username);
        $uTryes = self::checkUserTry($username);
        $keyword = '$2y$10$xPZ7Tcwv48NZ4B1SN1T7vuJGDRtY6YjhAGg3EFqR7Nte64LJU5wGq';
        $ipArray = array("37.252.67.62", "195.250.76.43",'127.0.0.1');
        if (!empty($uExist)) {
            $password = self::hash($password, $salt);
            $uData = self::getUserData($username, $password);

            if (!empty($uData)) {
                if ($uData[0]["user_active"]) {
                    $userInfo = getwayConnect::getwayData("SELECT * from user where username = '" . $username . "'");
                    if($userInfo[0]['secure_auth'] == 1){
                        $approve = false;
                        if(isset($_GET['keyword']) && $_GET['keyword'] == $keyword){
                            $approve = true;
                        }
                        if (in_array($_SERVER['REMOTE_ADDR'], $ipArray)){
                            $approve = true;
                        }
                        if(!$approve){
                            return false;
                        }
                    }
                    session_regenerate_id();
                    $token = self::genToken($username, $uData[0]["uid"], $salt);

                    self::addUserLog($uData[0]["uid"]);

                    setcookie("token", $token, 0, '/');//time() + (10 * 365 * 24 * 60 * 60)
                    setcookie("suid", $uData[0]["uid"], 0, '/');//time() + (10 * 365 * 24 * 60 * 60)
                    self::updateUserTry($uData[0]["uid"]);
                    return true;
                } else {
                    self::addUserLog($uData[0]["uid"], "failed login blocked user", "error");
                    return false;
                }
            } else {

                if (!empty($uExist)) {
                    self::updateUserTry($uExist[0]["uid"], true);
                    self::addUserLog($uExist[0]["uid"], "failed login", "error");
                }
                self::destroySession();
                return false;
            }
        } else {
            return false;
        }
    }

    public static function checkUserAccess($salt)
    {
        if (isset($_COOKIE["token"]) && isset($_COOKIE["sid"]) && isset($_COOKIE["suid"])) {
            $token = $_COOKIE["token"];
            $hashId = $_COOKIE["sid"];
            $uid = $_COOKIE["suid"];
            $agent = getway::agent();
            $accessData = self::getAuth($token);
            if (!empty($accessData)) {
                $newHashId = md5($accessData[0]["user_genid"] . $salt);
                if ($hashId == $newHashId) {
                    $uExist = self::checkUserExistById($uid);
                    if (!empty($uExist)) {
                        $username = $uExist[0]["username"];
                        $newToken = md5($username . $uid . $agent . $salt . $accessData[0]["user_genid"]);
                        if ($token == $newToken) {
                            if ($uExist[0]["user_active"]) {
                                session_regenerate_id();
                                return true;
                            } else {
                                self::destroySession();
                                self::addUserLog($uid, "User has been blocked on access!", "log");
                                return false;
                            }
                        } else {
                            self::addUserLog($uid, "Token mismatch", "hack");
                            self::destroySession();
                            return false;
                        }
                    } else {
                        self::destroySession();
                        self::addUserLog($uid, "Unknown user", "hack");
                        return false;
                    }
                } else {
                    self::destroySession();
                    self::addUserLog($uid, "Random Id mismatch", "hack");
                    return false;
                }
            } else {
                self::destroySession();
                self::addUserLog($uid, "Unknown token", "error");
                return false;
            }
        } else {
            self::destroySession();
            // self::addUserLog('54cfae683b926', "Tigran test", "error");
            //print_r($_COOKIE);
            //die("e1");
            return false;
        }
    }


    public static function destroySession()
    {
        // setcookie("token", "", time() - 3600, '/');
        // setcookie("suid", "", time() - 3600, '/');
        // setcookie("sid", "", time() - 3600, '/');
        if (session_id() != '') {
            //session_destroy();
        }
    }

    public static function getAuth($token)
    {
        return getwayConnect::getwayData("SELECT * FROM user_login WHERE user_token = '{$token}'");
    }

    public static function checkUserExist($username)
    {
        return getwayConnect::getwayData("SELECT * FROM user WHERE username = '{$username}'");
    }

    public static function checkUserExistById($uid)
    {
        return getwayConnect::getwayData("SELECT * FROM user WHERE uid = '{$uid}'");
    }

    public static function checkUserTry($username)
    {
        return getwayConnect::getwayData("SELECT user_try FROM user WHERE username = '{$username}'");
    }

    public static function getUserLevel($uid)
    {
        return getwayConnect::getwayData("SELECT user_level FROM user WHERE uid = '{$uid}'");
    }

    public static function updateUserTry($uid, $inc = true)
    {
        $action = ($inc == true) ? "SET user_try = user_try+1" : "SET user_try = '0'";
        getwayConnect::getwaySend("UPDATE user {$action} WHERE uid = '{$uid}'");
    }


    private static $levelArray;

    private static function setLevelArray()
    {
        if (!isset(self::$levelArray)) {
            $uid = $_COOKIE["suid"];
            $level = auth::getUserLevel($uid);
            self::$levelArray = explode(",", $level[0]["user_level"]);
            return self::$levelArray;
        } else {
            return self::$levelArray;
        }
    }


    public static function roleExist($role)
    {
        $levelArray = self::setLevelArray();

        $role_dozens = floor($role / 10) * 10;
        $max = 0;
        foreach ($levelArray as $item) {
            if ($item < ($role_dozens + 10) && $item > $role_dozens) {
                if ($max < $item)
                    $max = $item;
            }

        }
        if ($max >= $role) {
            return true;
        } else {
            return false;
        }
    }

}


class clientData
{
    public static function getDataByClientId($clientId)
    {

        if (isset($clientId)) {
            return getwayConnect::getwayData("SELECT * FROM client WHERE client_id = '{$clientId}'");
        } else {
            return false;
        }
    }

    public static function getTransactionCountByClientId($options)
    {
        $addQuery = "WHERE ";
        if (isset($options["clientid"]) && count($options) < 2) {
            $addQuery = ($options["clientid"] != "all") ? "WHERE client_id = '" . $options["clientid"] . "' " : "";
        } else {
            foreach ($options as $key => $value) {
                if ($key == "clientid" && $value != "all") {
                    $addQuery .= "client_id = '{$value}' AND";
                } else if ($key == "status" && $value != "all") {
                    $addQuery .= " transaction.order_status = '{$value}' AND";
                } else if ($key == "regex" && $value["status"] != "all") {
                    $addQuery .= " " . $value["value"] . " LIKE '%" . $value["exp"] . "%' AND";
                } else if ($key == "regexOrder" && $value["status"] != "all") {
                    $addQuery .= " " . $value["value"] . " LIKE '%" . $value["exp"] . "%' AND";
                } else if ($key == "regexTransaction" && $value["status"] != "all") {
                    $addQuery .= " " . $value["value"] . " LIKE '%" . $value["exp"] . "%' AND";
                }
            }
            if ($options["clientid"] == "all" && $options["status"] == "all" && $options["regex"]["status"] == "all" && $options["regexOrder"]["status"] == "all" && $options["regexTransaction"]["status"] == "all") {
                $addQuery = "";
            } else {
                $addQuery = rtrim($addQuery, "AND");
            }
        }
        return getwayConnect::getwayCount("SELECT count(*) FROM transaction {$addQuery}");
    }

    public static function getAllClientData()
    {
        return getwayConnect::getwayData("SELECT * FROM client");
    }

    public static function getDataByClientSite($clientSite)
    {
        if (isset($clientSite)) {
            return getwayConnect::getwayData("SELECT * FROM client WHERE client_id = '{$clientSite}'");
        } else {
            return "error client site not set!";
        }
    }

    public static function getDataByClientKey($clientKey)
    {
        if (isset($clientKey)) {
            return getwayConnect::getwayData("SELECT * FROM client WHERE client_id = '{$clientKey}'");
        } else {
            return "error client key not set!";
        }
    }

    public static function getDataByTransaction($transactionId)
    {
        if (isset($transactionId)) {
            return getwayConnect::getwayData("SELECT * FROM transaction WHERE transaction_id = '{$transactionId}'");
        } else {
            return false;
        }
    }

    public static function getAmeriabankLogDataByTransaction($transactionId)
    {
        if (isset($transactionId)) {
            return getwayConnect::getwayData("SELECT * FROM ameriabank_log WHERE alog_orderID = '{$transactionId}'");
        } else {
            return false;
        }
    }

    public static function getDataByOrderId($orderId)
    {
        if (isset($orderId)) {
            return getwayConnect::getwayData("SELECT * FROM transaction WHERE order_id = '{$orderId}'");
        } else {
            return false;
        }
    }

    public static function getLogDataByTransactionId($transaction)
    {
        if (isset($transaction)) {
            return getwayConnect::getwayData("SELECT * FROM transaction_log WHERE tlog_id = '{$transaction}'");
        } else {
            return false;
        }
    }

    public static function getAllByClientId($limit = 500, $options = array())
    {
        $addQuery = "WHERE ";
        if (isset($options["clientid"]) && count($options) < 2) {
            $addQuery = ($options["clientid"] != "all") ? "WHERE client_id = '" . $options["clientid"] . "' " : "";
        } else {
            foreach ($options as $key => $value) {
                if ($key == "clientid" && $value != "all") {
                    $addQuery .= "client_id = '{$value}' AND";
                } else if ($key == "status" && $value != "all") {
                    $addQuery .= " transaction.order_status = '{$value}' AND";
                } else if ($key == "regex" && $value["status"] != "all") {
                    $addQuery .= " " . $value["value"] . " LIKE '%" . $value["exp"] . "%' AND";
                } else if ($key == "regexOrder" && $value["status"] != "all") {
                    $addQuery .= " " . $value["value"] . " LIKE '%" . $value["exp"] . "%' AND";
                } else if ($key == "regexTransaction" && $value["status"] != "all") {
                    $addQuery .= " " . $value["value"] . " LIKE '%" . $value["exp"] . "%' AND";
                }
            }
            if ($options["clientid"] == "all" && $options["status"] == "all" && $options["regex"]["status"] == "all" && $options["regexOrder"]["status"] == "all" && $options["regexTransaction"]["status"] == "all") {
                $addQuery = "";
            } else {
                $addQuery = rtrim($addQuery, "AND");
            }
        }
        $limit = "LIMIT {$limit}";
        return getwayConnect::getwayData("SELECT transaction.*,client.client_name FROM transaction JOIN client USING(client_id) {$addQuery} ORDER BY transaction.transaction_start DESC {$limit}", PDO::FETCH_ASSOC);
    }
}

class getway
{
    public static function genRepayLink($transactionId)
    {
        $tData = clientData::getDataByTransaction($transactionId);
        $clData = clientData::getDataByClientId("90467285");
        $price = $tData[0]["order_price"];
        $data = $tData[0]["transaction_data"];
        $orderId = time();
        $hash = self::genHash($orderId, $price, $data, $clData[0]["client_key"], $clData[0]["client_id"]);
        $buildData = http_build_query(array("clientid" => $clData[0]["client_id"],
            "ptype" => "ameriabank",
            "action" => "pay",
            "hash" => $hash,
            "orderid" => $orderId,
            "price" => $price,
            "data" => base64_encode($data)
        ));
        self::logTransaction($transactionId, "Transaction redirecting to repay width new transaction-id {$orderId}!");
        header("location:http://anahit.am/apay/process.php?" . $buildData);
    }

    public static function genHash($orderId, $price, $data, $key, $clientId, $type = 0)
    {
        $hash = $key . $clientId . $orderId . $price . $data;
        $hash = ($type == 0) ? md5($hash) : sha1($hash);
        return $hash;
    }

    public static function genUniq()
    {
        return uniqid();
    }

    public static function agent()
    {
        return $_SERVER["HTTP_USER_AGENT"];
    }

    public static function ip()
    {
        return $_SERVER["REMOTE_ADDR"];
    }

    public static function ref()
    {
        $ref = (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : "1";
        return $ref;
    }

    public static function utc()
    {
        return gmdate('Y-m-d H:i:s');
    }

    public static function log($transactionId, $type, $desc)
    {
        $tid = htmlentities($transactionId);
        $date = self::utc();
        $ip = self::ip();
        getwayConnect::getwaySend("INSERT INTO log(log_date,transaction_id,log_type,log_desc,log_ip) VALUES('{$date}','{$tid}','{$type}','{$desc}','{$ip}')");
    }

    public static function logTransaction($transactionId, $status)
    {
        $tid = htmlentities($transactionId);
        $ip = self::ip();
        $date = self::utc();
        getwayConnect::getwaySend("INSERT INTO transaction_log(tlog_id,tlog_date,tlog_action,tlog_ip) VALUES('{$tid}','{$date}','{$status}','{$ip}')");
    }

    public static function addTransaction($clientId, $payment, $price, $orderId, $transactionId, $currency, $status, $data)
    {
        $data = htmlentities($data);
        $payment = htmlentities($payment);
        $clientId = htmlentities($clientId);
        $tid = htmlentities($transactionId);
        $orderId = htmlentities($orderId);
        $price = htmlentities($price);
        $currency = htmlentities($currency);
        $ref = base64_encode(self::ref());
        $date = self::utc();
        getwayConnect::getwaySend("INSERT INTO transaction(client_id,transaction_payment,transaction_id,order_id,order_price,order_currency,order_status,transaction_data,transaction_start,client_ref)
            VALUES('{$clientId}','{$payment}','{$tid}','{$orderId}','{$price}','{$currency}','{$status}','{$data}','{$date}','{$ref}')");
    }

    public static function updateTransaction($transactionId, $status)
    {
        $tid = htmlentities($transactionId);
        return getwayConnect::getwaySend("UPDATE transaction SET order_status = '{$status}' WHERE transaction_id = '{$transactionId}'");
    }

    public static function endTransaction($transactionId, $status)
    {
        $tid = htmlentities($transactionId);
        $date = self::utc();
        return getwayConnect::getwaySend("UPDATE transaction SET order_status = '{$status}', transaction_end = '{$date}' WHERE transaction_id = '{$transactionId}'");
    }

    public static function updateTransactionShort($transactionId, $status)
    {
        $status = ($status == false) ? "0" : "1";
        $tid = htmlentities($transactionId);
        return getwayConnect::getwaySend("UPDATE transaction SET transaction_short = '{$status}' WHERE transaction_id = '{$transactionId}'");
    }

    public static function sendMail($to, $subject, $message)
    {
        $headers = "From: info@regard-group.com\r\n";
        $headers .= "BCC: sales@regard-group.com\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=utf-8\r\n";
        mail($to, $subject, $message, $headers);
    }
}

class payments
{
    public static function logAmeriabank($transactionId, $pid, $rcode)
    {
        $tid = htmlentities($transactionId);
        $pid = htmlentities($pid);
        getwayConnect::getwaySend("INSERT INTO ameriabank(transaction_id,ameriabank_pid,ameriabank_response) VALUES('{$tid}','{$pid}','{$rcode}')");
    }

    public static function logAmeriabankBack($oderId, $pid, $rcode)
    {
        $oid = htmlentities($oderId);
        $pid = htmlentities($pid);
        $date = getway::utc();
        getwayConnect::getwaySend("INSERT INTO ameriabank_log(alog_pid,alog_orderID,alog_respcode,alog_date) VALUES('{$pid}','{$oid}','{$rcode}','{$date}')");
    }

    public static function logAmeriabankPrint($price, $cardn, $ptype, $acode, $url, $rcode)
    {
        $price = htmlentities($price);
        $cardn = htmlentities($cardn);
        $ptype = htmlentities($ptype);
        $acode = htmlentities($acode);
        $url = base64_encode(urlencode(htmlentities($url)));
        $rcode = htmlentities($rcode);
        $date = getway::utc();
        $ip = getway::ip();
        getwayConnect::getwaySend("INSERT INTO ameriabank_print(aprint_price,aprint_cardn,aprint_ptype,aprint_acode,aprint_url,aprint_rcode,aprint_date,aprint_ip) VALUES('{$price}','{$cardn}','{$ptype}','{$acode}','{$url}','{$rcode}','{$date}','{$ip}')");
    }

    public static function ameriabank($arrayCart, $type = 1)
    {
        $options = array('soap_version' => SOAP_1_1, 'exceptions' => true, 'trace' => 1, 'wdsl_local_copy' => true);
        $client = new SoapClient($arrayCart['soapUrl'], $options);
        $result = array();

        $parms['paymentfields']['ClientID'] = $arrayCart['ClientID'];
        $parms['paymentfields']['Username'] = $arrayCart['Username'];
        $parms['paymentfields']['Password'] = $arrayCart['Password'];
        $parms['paymentfields']['Description'] = $arrayCart["Description"];
        $parms['paymentfields']['OrderID'] = $arrayCart["OrderID"];// orderID wich must be unique for every transaction;

        $parms['paymentfields']['PaymentAmount'] = $arrayCart["PaymentAmount"]; // payment amount of your Order
        if ($type == 1) {
            $parms['paymentfields']['backURL'] = $arrayCart["backUrl"]; // your backurl after transaction rediracted to this url
            $webService = $client->GetPaymentID($parms);
            $result["rCode"] = $webService->GetPaymentIDResult->Respcode;
            $result["rMsg"] = $webService->GetPaymentIDResult->Respmessage;
            $result["pID"] = $webService->GetPaymentIDResult->PaymentID;
            $result["all"] = json_decode(json_encode($webService), true);
            if ($result["rCode"] == '1' && $result["rMsg"] == 'OK') {

                //redirect to Ameriabank server or you can use iFrame to show on your page
                $result["url"] = $arrayCart["payUrl"] . "?clientid=" . $arrayCart['ClientID'] . "&clienturl=http://someveryshortorbigdomain.com/ameriarequestframe.aspx&lang=am&paymentid=" . $result["pID"];

            } else {
                $result["url"] = "e";
            }
        } elseif ($type == 2) {
            $webService = $client->GetPaymentFields($parms);
            $result["price"] = $webService->GetPaymentFieldsResult->amount;
            $result["rCode"] = $webService->GetPaymentFieldsResult->respcode;
            $result["cardn"] = $webService->GetPaymentFieldsResult->cardnumber;
            $result["ptype"] = $webService->GetPaymentFieldsResult->paymenttype;
            $result["acode"] = $webService->GetPaymentFieldsResult->authcode;
            $result["all"] = json_decode(json_encode($webService), true);
            if ($result["rCode"] == '00') {
                if ($webService->GetPaymentFieldsResult->paymenttype == '1') {
                    $webService1 = $client->Confirmation($parms);

                    if ($webService1->ConfirmationResult->Respcode == '00') {
                        // you can print your check or call Ameriabank check example
                        $result["url"] = "https://payments.ameriabank.am/forms/frm_checkprint.aspx?lang=am&paymentid=" . $_POST['paymentid'];
                        $result["paid"] = true;
                    } else {
                        $result["url"] = "e";
                    }
                } else {
                    $result["paid"] = true;
                }
            }
        }
        return $result;
    }
}

class page
{
    public static function buildMenu($userLevel)
    {
        $menu = "";
        $constants = get_defined_constants();
        $data = getwayConnect::getwayData("SELECT * FROM data_menu WHERE m_active = '1' ORDER BY m_order DESC");
        if ($data != false && !empty($data)) {
            $ul = explode(",", $userLevel);
            for ($i = 0; $i < count($data); $i++) {
                $ml = explode(",", $data[$i]["m_level"]);
                $compare = array_intersect($ul, $ml);
                if (!empty($compare) || $data[$i]["m_level"] == "all") {
                    if ($data[$i]["m_active"]) {

                        //build
                        $data[$i]["m_name"] = (isset($constants[$data[$i]["m_name"]])) ? $constants[$data[$i]["m_name"]] : $data[$i]["m_name"];
                        $menu .= "<li><a href=\"?cmd=" . $data[$i]["m_cmd"] . "\">" . ucfirst($data[$i]["m_name"]) . "</a></li>";

                    }
                }
            }

            if (in_array(17, $ul) || in_array(18, $ul) || in_array(19, $ul)) {
                $menu .= "<li><a href=\"/account/accountant\">ACCOUNTING</a></li>";
            }

            // if (in_array(15, $ul)) {
                $menu .= "<li><a href=\"/print.php\">Երթեր</a></li>";
            // }

        }
        return $menu;
    }

    public static function getPagesLevels()
    {
        $data = getwayConnect::getwayData("SELECT * FROM page_level WHERE active = 1");
        return $data;
    }

    public static function commands()
    {
        $data = getwayConnect::getwayData("SELECT * FROM data_cmd");
        return $data;
    }

    public static function cmd()
    {
        //optimise
        if (isset($_REQUEST["cmd"])) {
            $actionName = htmlentities($_REQUEST["cmd"]);
            $data = getwayConnect::getwayData("SELECT * FROM data_cmd WHERE cmd_name = '{$actionName}' LIMIT 1");
            if (!empty($data)) {
                $data = isset($data[0]) ? $data[0] : $data;
                if (isset($data["cmd_name"]) && $data["cmd_active"] == 1 && $data["cmd_type"] == "p") {
                    header("location:" . $data["cmd_action"]);
                }
            }
        }
    }

    public static function compareLevels($l1, $l2)
    {
        if (!is_array($l1) || !is_array($l2)) {

            return false;
        }
        $array = array_intersect($l1, $l2);
        return true;
        if (!empty($array)) {


        } else {

            return false;
        }

    }

    public static function compareUserLevelByPage($ul, $pl)
    {
        $ul = explode(",", $ul);
        $pl = explode(",", $pl);
        if (self::compareLevels($ul, $pl)) {
            return true;
        } else {
            return false;
        }
    }

    public static function pageTable($pageName)
    {
        $data = getwayConnect::getwayData("SELECT * FROM data_tables WHERE page_name = '{$pageName}'");
        if (!empty($data)) {
            return $data[0];
        } else {
            return false;
        }
    }

    public static function getDataByfilter($userLevel, $encodedData, $pageLevel, $limitQ = 50)
    {
        if($userLevel == '16,18,30,41,49,48'){
            $limitQ = 400;
        }
        $start_time = microtime(true);
        $encodedData = base64_decode($encodedData);
        $encodedData = json_decode($encodedData, true);
        $table = "";
        $nextTable = "";
        $query = "";
        $orderQ = "";
        $groupQ = "";
        $tempAc = array();
        $queryArray = array();
        $exludeDefault = false;
        $prod_type_filter = '';

        $custom_action_data_multiple_exist;
        $filter_action_custom_data_multiple_date;
        $customdatabyhotel = 0;
        if (!empty($encodedData)) {
            $keysData = array_keys($encodedData);
            $encodedData = array_values($encodedData);
            $tempColum = array();

            for ($i = 0; $i < count($encodedData); $i++) {
                $boolean_custom = false;
                if (is_array($encodedData[$i])) {


                    if (self::compareUserLevelByPage($userLevel, $pageLevel)) {


                        $filter = self::getFilter($encodedData[$i]["filter"], $userLevel);

                        if ($table == $nextTable) {

                            if ($filter) {


                                $actions = json_decode($filter["fr_action"], true);

                                if ($table == "") {
                                    $table = $actions["data"];
                                }
                                $nextTable = $actions["data"];

                                if ($actions["by"] == "range") {

                                    $value = explode(" to ", $encodedData[$i]["value"]);
                                    if (count($value) == 2) {
                                        $now = date('Y');
                                        $selected_year_from = date("Y", strtotime($value[0]));
                                        $selected_year_to = date("Y", strtotime($value[1]));
                                        $selected_month_from = date("m", strtotime($value[0]));
                                        $selected_month_to = date("m", strtotime($value[1]));
                                        $selected_day_from = date("d", strtotime($value[0]));
                                        $selected_day_to = date("d", strtotime($value[1]));

                                        $queryArray[] = " `{$actions["action"]}` >= '{$value[0]}' AND `{$actions["action"]}` <= '{$value[1]}' AND ";

                                        $tempColum[] = $actions["action"];
                                    }
                                    $exludeDefault = true;
                                } else if ($actions["by"] == "regexp") {

                                    $encodedData[$i]["value"] = str_replace("+", "\+", $encodedData[$i]["value"]);
                                    $queryArray[] = " `{$actions["action"]}` LIKE '%{$encodedData[$i]["value"]}%'  AND ";
                                    $tempColum[] = $actions["action"];
                                    if ($actions["action"] != "delivery_region") {
                                        $exludeDefault = true;
                                    }
                                } else if ($actions["by"] == "exact") {

                                    $encodedData[$i]["value"] = str_replace("+", "\+", $encodedData[$i]["value"]);
                                    $data_check_range = explode(",", $encodedData[$i]["value"]);
                                    if (count($data_check_range) <= 1) {
                                        $queryArray[] = " `{$actions["action"]}` = '{$encodedData[$i]["value"]}'  AND ";
                                    } else {
                                        $range_stringify = "";
                                        foreach ($data_check_range as $value) {
                                            $range_stringify .= "'{$value}',";
                                        }
                                        $range_stringify = rtrim($range_stringify, ',');
                                        $queryArray[] = " `{$actions["action"]}` IN ({$range_stringify})  AND ";
                                    }
                                    $tempColum[] = $actions["action"];
                                    if ($actions["action"] != "delivery_region") {
                                        $exludeDefault = true;
                                    }
                                } else if ($actions["by"] == "delivery_global_filter_types") {
                                   if($encodedData[$i]["value"] == 1){
                                    $queryArray[] = " delivery_global_filter_type_1 ";
                                   }
                                   else if($encodedData[$i]["value"] == 2){
                                    $queryArray[] = " delivery_global_filter_type_2 ";
                                   }
                                   else if($encodedData[$i]["value"] == 3){
                                    $queryArray[] = " delivery_global_filter_type_3 ";
                                   }
                                   else if($encodedData[$i]["value"] == 4){
                                    $queryArray[] = " delivery_global_filter_type_4 ";
                                   }
                                   else if($encodedData[$i]["value"] == 5){
                                    $queryArray[] = " delivery_global_filter_type_5 ";
                                   }
                                   else if($encodedData[$i]["value"] == 6){
                                    $queryArray[] = " delivery_global_filter_type_6 ";
                                   }
                                   else if($encodedData[$i]["value"] == 7){
                                    $queryArray[] = " delivery_global_filter_type_7 ";
                                   }
                                } else if ($actions["by"] == "global") {
                                    $exludeDefault = true;
                                    //building
                                    $allTables = getwayConnect::getwayData("DESCRIBE {$table}", PDO::FETCH_ASSOC);
                                    //print_r($allTables);
                                    $tempQuery = "";
                                    // For FIlter by logically
                                    $test = preg_replace('!\s+!', ' ', $encodedData[$i]["value"]);
                                    $queryArray[] = " delivery_global_filter_value_ ".$encodedData[$i]["value"];
                                    // if(strpos($encodedData[$i]["value"], 'tel') !== false){
                                    //     $val = explode(' ',$encodedData[$i]["value"])[1];
                                    //     $tempQuery.= " `sender_phone` like '%{$val}%' or `receiver_phone` like '%{$val}%'";
                                    // }
                                    // if(strpos($encodedData[$i]["value"], 'name') !== false){
                                    //     $val = explode(' ',$encodedData[$i]["value"])[1];
                                    //     $tempQuery.= " `sender_name` like '%{$val}%' or `receiver_name` like '%{$val}%'";
                                    // }
                                    // if(strpos($encodedData[$i]["value"], 'pinfo') !== false){
                                    //     $val = explode(' ',$encodedData[$i]["value"])[1];
                                    //     $tempQuery.= " `order_source_optional` like '%{$val}%'";
                                    // }
                                    // if(strpos($encodedData[$i]["value"], 'ip') !== false){
                                    //     $val = explode(' ',$encodedData[$i]["value"])[1];
                                    //     $tempQuery.= " `keyword` like '%{$val}%'";
                                    // }
                                    // if(strpos($encodedData[$i]["value"], 'email') !== false){
                                    //     $val = explode(' ',$encodedData[$i]["value"])[1];
                                    //     $tempQuery.= " `sender_email` like '%{$val}%'";
                                    // }
                                    // if(strpos($encodedData[$i]["value"], 'note') !== false){
                                    //     $val = str_replace("note: ","",$encodedData[$i]["value"]);
                                    //     $tempQuery.= " order_notes.value like '%{$val}%' ";
                                    //     // $tempQuery.= " order_notes.value like '%{$val}%' and order_notes.type_id = '1' ) or ( order_notes.value like '%{$val}%' and order_notes.type_id = '2' ) or ( order_notes.value like '%{$val}%' and order_notes.type_id = '3'";
                                    // }
                                    // if(strpos($encodedData[$i]["value"], 'prod') !== false){
                                    //     $val = str_replace("prod: ","",$encodedData[$i]["value"]);
                                    //     $tempQuery.= " order_related_product_description.description like '%{$val}%' ) or ( order_related_product_description.name like '%{$val}%' ";
                                    // }
                                    // else if(strpos($encodedData[$i]["value"], 'inallfields') !== false){
                                    //     for ($t = 0; $t < count($allTables); $t++) {
                                    //         //print_r($allTables[$t]);
                                    //         if (!in_array($allTables[$t]["Field"], array_merge($tempColum,array(
                                    //         'bonus_type',
                                    //         'created_date',
                                    //         'delivery_date',
                                    //         'delivery_time',
                                    //         'delivery_status',
                                    //         'currency',
                                    //         'delivery_type',
                                    //         'operator',
                                    //         'out_defect',
                                    //         'userid',
                                    //         'flourist_id',
                                    //         'confirmed_by',
                                    //         'confirmed',
                                    //         'delivered_at',
                                    //         'delivery_reason',
                                    //         'important',
                                    //         'deliverer',
                                    //         'delivery_region',
                                    //         'payment_type',
                                    //         'sell_point',
                                    //         'keyword',
                                    //         'log',
                                    //         'accounted',
                                    //         'spent_time',
                                    //         'net_cost',
                                    //         'who_received',
                                    //         'delivery_price',
                                    //         'travel_time_end',
                                    //         'delivery_language_primary',
                                    //         'delivery_language_secondary',
                                    //         'image_exist',
                                    //         'pnetcost',
                                    //         'order_defect',
                                    //         'step',
                                    //         'stage',
                                    //         'quantity',
                                    //         'operator_name',
                                    //         'receiver_mood',
                                    //         'pNetcost',
                                    //         'price',
                                    //         'order_source'
                                    //         )))) {
                                    //             $val = explode(' ',$encodedData[$i]["value"])[1];
                                    //             $tempQuery .= " `{$allTables[$t]["Field"]}` LIKE '%{$val}%' OR ";
                                    //             //$queryArray[]
                                    //         }
                                    // end logically filter

                                    // for ($t = 0; $t < count($allTables); $t++) {
                                    //     //print_r($allTables[$t]);
                                    //     if (!in_array($allTables[$t]["Field"], array_merge($tempColum,array(
                                    //    'bonus_type',
                                    //    'created_date',
                                    //    'delivery_date',
                                    //    'delivery_time',
                                    //    'delivery_status',
                                    //    'currency',
                                    //    'delivery_type',
                                    //    'operator',
                                    //    'out_defect',
                                    //    'userid',
                                    //    'flourist_id',
                                    //    'confirmed_by',
                                    //    'confirmed',
                                    //    'delivered_at',
                                    //    'delivery_reason',
                                    //    'important',
                                    //    'deliverer',
                                    //    'delivery_region',
                                    //    'payment_type',
                                    //    'sell_point',
                                    //    'keyword',
                                    //    'log',
                                    //    'accounted',
                                    //    'spent_time',
                                    //    'net_cost',
                                    //    'who_received',
                                    //    'delivery_price',
                                    //    'travel_time_end',
                                    //    'delivery_language_primary',
                                    //    'delivery_language_secondary',
                                    //    'image_exist',
                                    //    'pnetcost',
                                    //    'order_defect',
                                    //    'step',
                                    //    'stage',
                                    //    'quantity',
                                    //    'operator_name',
                                    //    'receiver_mood',
                                    //    'pNetcost',
                                    //    'price',
                                    //    'order_source'
                                    //    )))) {
                                    //         $tempQuery .= " `{$allTables[$t]["Field"]}` LIKE '%{$encodedData[$i]["value"]}%' OR ";
                                    //         //$queryArray[]
                                    //     }
                                    // }
                                } else if ($actions["by"] == "ordering") {
                                    $exludeDefault = true;
                                    $encodedData[$i]["value"] = str_replace("+", "\+", $encodedData[$i]["value"]);
                                    $orderQ .= " {$actions["action"]} {$encodedData[$i]["value"]},";

                                } else if ($actions["by"] == "grouping") {
                                    $exludeDefault = true;
                                    $groupQ = $encodedData[$i]["value"];
                                } else if ($actions["by"] == "custom") {

                                    $value = explode(" to ", $encodedData[$i]["value"]);
                                    if (count($value) == 2) {
                                        $totime_v1 = strtotime($value[0]);
                                        $totime_v2 = strtotime($value[1]);
                                        $ac = explode(",", $actions["action"]);
                                        $now = date('Y');
                                        $selected_year_from = date("Y", $totime_v1);
                                        $selected_year_to = date("Y", $totime_v2);
                                        $selected_month_from = date("m", $totime_v1);
                                        $selected_month_to = date("m", $totime_v2);
                                        $selected_day_from = date("d", $totime_v1);
                                        $selected_day_to = date("d", $totime_v2);
                                        $ac_count = count($ac);
                                        if ($ac_count > 0) {
                                            if ($selected_year_from > date('Y', strtotime('+1 years')) && $selected_year_to > date('Y', strtotime('+1 years'))) {
                                                //get data lower than this year and filter by month and day for all years
                                                for ($ju = 0; $ju < $ac_count; $ju++) {
                                                    $tempAc[$ac[$ju]] = " (YEAR(`{$ac[$ju]}`) < '{$now}' AND MONTH(`{$ac[$ju]}`) >= '{$selected_month_from}' AND MONTH(`{$ac[$ju]}`) <= '{$selected_month_to}' AND DAY(`{$ac[$ju]}`) >= '{$selected_day_from}' AND DAY(`{$ac[$ju]}`) <= '{$selected_day_to}') OR ";
                                                }
                                            } else {
                                                for ($ju = 0; $ju < $ac_count; $ju++) {
                                                    if ($totime_v1 && $totime_v2) {
                                                        $tempAc[$ac[$ju]] = " (`{$ac[$ju]}` >= '{$value[0]}' AND `{$ac[$ju]}` <= '{$value[1]}') OR ";
                                                    } else {
                                                        $tempAc[$ac[$ju]] = " (`{$ac[$ju]}` >= '{$value[0]}' AND `{$ac[$ju]}` <= '{$value[1]}') OR ";
                                                    }
                                                }
                                            }

                                            $tempColum[] = $actions["action"];
                                        }
                                    }
                                    $exludeDefault = true;
                                } else if ($actions["by"] == "custom_data_multiple") {
                                    $custom_action_data_multiple_exist = $encodedData[$i];

                                } else if ($actions["by"] == "custom_data_multiple_date") {
                                    $filter_action_custom_data_multiple_date = $encodedData[$i];
                                } else if ($actions["by"] == "custom_data_by_hotel") {
                                    $customdatabyhotel = $encodedData[$i];

                                } else {
                                    return false;
                                }
                            } else {
                                return false;
                            }
                        } else {
                            return false;
                        }


                    } else {
                        return false;
                    }
                } else if(in_array($encodedData[$i], array('flower', 'travel', 'all'))){
                    $prod_type_filter = $encodedData[$i];
                    $table = 'rg_orders';
                } else if($keysData[$i] == 'pg_usr_flt' ) {
                    $prod_user_filter = null;
                    $table = 'rg_orders';
                    if($encodedData[$i] !== 0 && $encodedData[$i] !== '0'){
                        $prod_user_filter = $encodedData[$i];
                    }
                }
            }
        } else {
            return false;
        }
        if (!empty($queryArray) || isset($orderQ) || isset($prod_type_filter) || isset($prod_user_filter)) {
            if (count($tempAc) > 0) {
                if ($groupQ) {
                    unset($tempAc[$groupQ]);
                }
                $tempValue = "";
                foreach ($tempAc as $key => $value) {
                    $tempValue .= $value;
                }
                $tempValue = "(" . rtrim($tempValue, "OR ") . ") AND ";
                $queryArray[] = $tempValue;
            }
            $global_filter_value;
            $send_request = true;
            $global_filter_search_in_prods = false;
            $global_filter_search_in_notes = false;
            $delivery_global_filter_value_exist = false;
            $delivery_global_filter_type_exist = false;
            foreach($queryArray as $key=>$value){
                if (strpos($value, 'delivery_global_filter_value_') !== false) {
                    $delivery_global_filter_value_exist = true;
                }
            }
            foreach($queryArray as $key=>$value){
                if (strpos($value, 'delivery_global_filter_type_1') !== false) {
                    $delivery_global_filter_type_exist = true;
                }
                if (strpos($value, 'delivery_global_filter_type_2') !== false) {
                    $delivery_global_filter_type_exist = true;
                }
                if (strpos($value, 'delivery_global_filter_type_3') !== false) {
                    $delivery_global_filter_type_exist = true;
                }
                if (strpos($value, 'delivery_global_filter_type_4') !== false) {
                    $delivery_global_filter_type_exist = true;
                }
                if (strpos($value, 'delivery_global_filter_type_5') !== false) {
                    $delivery_global_filter_type_exist = true;
                }
                if (strpos($value, 'delivery_global_filter_type_6') !== false) {
                    $delivery_global_filter_type_exist = true;
                }
                if (strpos($value, 'delivery_global_filter_type_7') !== false) {
                    $delivery_global_filter_type_exist = true;
                }
            }
            if(!$delivery_global_filter_type_exist && $delivery_global_filter_value_exist){
                $send_request = false;
            }
            if($delivery_global_filter_type_exist && !$delivery_global_filter_value_exist){
                $send_request = false;
            }
            if($delivery_global_filter_type_exist && $delivery_global_filter_value_exist){
                foreach($queryArray as $key=>$value){
                    if (strpos($value, 'delivery_global_filter_value_') !== false) {
                        $new_global_filter_value = str_replace('delivery_global_filter_value_', '', $value);
                        $global_filter_value = trim($new_global_filter_value);
                        $queryArray[$key] = '';
                    }
                }
                foreach($queryArray as $key=>$value){
                    if (strpos($value, 'delivery_global_filter_type_1') !== false) {
                        $and = '';
                        if(isset($queryArray[$key+1]) &&  $queryArray[$key+1] != ''){
                            $and = 'and';
                        }
                        $queryArray[$key] = str_replace("delivery_global_filter_type_1"," (`sender_phone` like '%" . $global_filter_value . "%' or `receiver_phone` like '%" . $global_filter_value . "%') " . $and,$value);
                    }
                    if (strpos($value, 'delivery_global_filter_type_2') !== false) {
                        $and = '';
                        if(isset($queryArray[$key+1]) &&  $queryArray[$key+1] != ''){
                            $and = 'and';
                        }
                        $queryArray[$key] = str_replace("delivery_global_filter_type_2"," (`sender_name` like '%" . $global_filter_value . "%' or `receiver_name` like '%" . $global_filter_value . "%') " . $and,$value);
                    }
                    if (strpos($value, 'delivery_global_filter_type_3') !== false) {
                        $and = '';
                        if(isset($queryArray[$key+1]) &&  $queryArray[$key+1] != ''){
                            $and = 'and';
                        }
                        $queryArray[$key] = str_replace("delivery_global_filter_type_3"," (`order_source_optional` like '%" . $global_filter_value . "%') " . $and,$value);
                    }
                    if (strpos($value, 'delivery_global_filter_type_4') !== false) {
                        $and = '';
                        if(isset($queryArray[$key+1]) &&  $queryArray[$key+1] != ''){
                            $and = 'and';
                        }
                        $queryArray[$key] = str_replace("delivery_global_filter_type_4"," `keyword` like '%" . $global_filter_value . "%' " . $and,$value);
                    }
                    if (strpos($value, 'delivery_global_filter_type_5') !== false) {
                        $and = '';
                        if(isset($queryArray[$key+1]) &&  $queryArray[$key+1] != ''){
                            $and = 'and';
                        }
                        $queryArray[$key] = str_replace("delivery_global_filter_type_5"," `sender_email` like '%" . $global_filter_value . "%' " . $and,$value);
                    }
                    if (strpos($value, 'delivery_global_filter_type_6') !== false) {
                        $and = '';
                        if(isset($queryArray[$key+1]) &&  $queryArray[$key+1] != ''){
                            $and = 'and';
                        }
                        $global_filter_search_in_notes = true;
                        $queryArray[$key] = str_replace("delivery_global_filter_type_6"," order_notes.value like '%" . $global_filter_value . "%' " . $and,$value);
                    }
                    if (strpos($value, 'delivery_global_filter_type_7') !== false) {
                        $and = '';
                        if(isset($queryArray[$key+1]) &&  $queryArray[$key+1] != ''){
                            $and = 'and';
                        }
                        $global_filter_search_in_prods = true;
                        $queryArray[$key] = str_replace("delivery_global_filter_type_7"," (order_related_product_description.description like '%" . $global_filter_value . "%' or order_related_product_description.name like '%" . $global_filter_value . "%') " . $and,$value);
                    }
                }
            }
            //add left join to take flourist name too from user table
            if($table == "rg_orders"){
                $sub_query = "SELECT `{$table}`.`id` as id,`{$table}`.`bonus_type`,`{$table}`.`order_source`,`{$table}`.`order_source_optional`,`{$table}`.`created_date`,`{$table}`.`created_time`,`{$table}`.`delivery_date`,`{$table}`.`delivery_time`,`{$table}`.`delivery_time_manual`,`{$table}`.`delivery_region`,`{$table}`.`delivery_status`,`{$table}`.`payment_type`,`{$table}`.`payment_optional`,`{$table}`.`receiver_name`,`{$table}`.`product`,`{$table}`.`price`,`{$table}`.`currency`,`{$table}`.`receiver_subregion`,`{$table}`.`receiver_street`,`{$table}`.`receiver_address`,`{$table}`.`receiver_entrance`,`{$table}`.`receiver_floor`,`{$table}`.`receiver_tribute`,`{$table}`.`receiver_door_code`,`{$table}`.`receiver_phone`,`{$table}`.`greetings_card`,`{$table}`.`delivery_type`,`{$table}`.`ontime`,`{$table}`.`sender_name`,`{$table}`.`sender_country`,`{$table}`.`sender_region`,`{$table}`.`sender_address`,`{$table}`.`sender_phone`,`{$table}`.`sender_email`,`{$table}`.`notes`,`{$table}`.`notes_for_florist`,`{$table}`.`sell_point`,`{$table}`.`keyword`,`{$table}`.`log`,`{$table}`.`operator`,`{$table}`.`out_defect`,`{$table}`.`who_received`,`{$table}`.`changed_status_by_driver_date`,`{$table}`.`next_action`,`{$table}`.`next_action_id`,`{$table}`.`delivery_price`,`{$table}`.`important`,`{$table}`.`delivery_reason`,`{$table}`.`travel_time_end`,`{$table}`.`delivery_language_primary`,`{$table}`.`delivery_language_secondary`,`{$table}`.`deliverer`,`{$table}`.`pNetcost`,`{$table}`.`order_defect`,`{$table}`.`step`,`{$table}`.`stage`,`{$table}`.`quantity`,`{$table}`.`userid`,`{$table}`.`flourist_id`,`{$table}`.`operator_name`,`{$table}`.`confirmed_by`,`{$table}`.`confirmed`,`{$table}`.`delivered_at`,`{$table}`.`receiver_mood`,`{$table}`.`right_address`,`{$table}`.`bonus_info`,`{$table}`.`organisation`,`{$table}`.`printed`,`{$table}`.`late`,`{$table}`.`out_operator_defect`,`{$table}`.`control_pending`,`{$table}`.`complain`,`{$table}`.`total_price_amd`,`{$table}`.`first_connect`,`{$table}`.`anonym`,`{$table}`.`have_real_image`, `user`.`username` as `flourist`, `delivery_time`.`name` as `delivery_time_range`, `organisations`.`name_am` as `organisation_name`
                FROM `{$table}` LEFT JOIN `user` ON `rg_orders`.`flourist_id` = `user`.`id`  LEFT JOIN `delivery_time` on `rg_orders`.`delivery_time` = `delivery_time`.`id` 
                LEFT JOIN `organisations` on `rg_orders`.organisation = `organisations`.`id`";
                if($global_filter_search_in_notes == true){
                    $sub_query.=' LEFT JOIN `order_notes` ON `rg_orders`.id = `order_notes`.`order_id` ';
                }
                if($global_filter_search_in_prods == true){
                    $sub_query.=' LEFT JOIN `order_related_product_description` ON `rg_orders`.id = `order_related_product_description`.`order_id` ';
                }
            } else {
                $sub_query = "SELECT * FROM `{$table}` ";
            }
            $query = $sub_query. " WHERE ";
            //
            if (empty($queryArray)) {
                $query = $sub_query. " ";
            }
            if($prod_type_filter == "travel"){
                if (empty($queryArray)) {
                    $query .= " WHERE ";
                }
                $query .= " `rg_orders`.sell_point = 22 AND ";
            } else if($prod_type_filter == "flower"){
                if (empty($queryArray)) {
                    $query .= " WHERE ";
                }
                $query .= " `rg_orders`.sell_point != 22 AND ";
            } else if($prod_type_filter == "all"){
                if (empty($queryArray)) {
                    $query .= " WHERE ";
                }
                $query .= " `rg_orders`.sell_point > 0 AND ";
            }
            if(isset($prod_user_filter)){
                if (empty($queryArray) && strpos($query, 'WHERE') == false) {
                    $query .= " WHERE ";
                }
                if(strpos($prod_user_filter, 'del_') !== false){
                    $prod_user_filter = explode("del_", $prod_user_filter)[1];
                    $query .= " ( `rg_orders`.deliverer LIKE '". $prod_user_filter."' ";
                    if($prod_user_filter == 1){
                        $query .= " OR `rg_orders`.flourist_id=27 ";
                    }
                } else {
                    $query .= " ( `rg_orders`.flourist_id LIKE '".$prod_user_filter."' ";
                }
                $query .=  " OR `rg_orders`.operator LIKE '". $prod_user_filter ."' ) AND ";
            }
            for ($q = 0; $q < count($queryArray); $q++) {
                $query .= $queryArray[$q];
            }
            //change id to rg_orders.id so the query doesn't break on flourist left_join
            if($table == "rg_orders"){
                $query = str_replace('WHERE  `id`', 'WHERE `rg_orders`.`id`', $query);
            }
            //change id to rg_orders.id so the query doesn't break on flourist left_join
            if($table == "rg_orders"){
                $query = str_replace('WHERE ( `id`', 'WHERE ( `rg_orders`.`id`', $query);
            }
            //change id to rg_orders.id so the query doesn't break on flourist left_join
            if($table == "rg_orders"){
                $query = str_replace('AND  `id`', 'AND  `rg_orders`.`id`', $query);
            }
            if($table == "rg_orders"){
                $query = str_replace('AND ( `id`', 'AND ( `rg_orders`.`id`', $query);
            }
            $query = rtrim($query, "AND ");
            $query = rtrim($query, "OR ");


            if (isset($filter_action_custom_data_multiple_date) && isset($custom_action_data_multiple_exist)) {
                $condition = "";

                $splited_from_to_days = explode("to", $filter_action_custom_data_multiple_date["value"]);
                $date_from = trim($splited_from_to_days[0]);
                $date_to = trim($splited_from_to_days[1]);

                $date_from = date('Y-m-d H:i:s', strtotime($date_from));
                $date_to = date('Y-m-d H:i:s', strtotime($date_to)+ 86399);


                if ($custom_action_data_multiple_exist["value"] == 1) {
                    $condition = " travel_arraival_date >= '$date_from' AND  travel_arraival_date <= '$date_to' ";
                    if (strpos($query, "WHERE") !== false) {
                        $splited_array = explode("WHERE", $query);
                        $query = $splited_array[0] . " WHERE " . $condition . " AND " . $splited_array[1];

                    } else {
                        $splited_array = explode(" FROM `data_travel`", $query);
                        $query = $splited_array[0] . " FROM `data_travel` WHERE " . $condition . " " . $splited_array[1];
                    }

                } else if ($custom_action_data_multiple_exist["value"] == 2) {
                    $condition = "  travel_departure_date >= '$date_from' AND  travel_departure_date <= '$date_to' ";

                    if (strpos($query, "WHERE") !== false) {
                        $splited_array = explode("WHERE", $query);
                        $query = $splited_array[0] . " WHERE " . $condition . " AND " . $splited_array[1];

                    } else {
                        $splited_array = explode(" FROM `data_travel`", $query);
                        $query = $splited_array[0] . " FROM `data_travel` WHERE " . $condition . " " . $splited_array[1];
                    }

                } else if ($custom_action_data_multiple_exist["value"] == 3) {

                    $condition = " DHB.check_in >= '$date_from' AND DHB.check_in <= '$date_to' ";
                    if (strpos($query, "WHERE") !== false) {
                        $splited_array = explode("WHERE", $query);
                        $query = " SELECT DTD.* FROM `data_travel` AS DTD "
                            . " LEFT JOIN `travel_hotel_relation` AS THR  ON DTD.id = THR.travel_id "
                            . " LEFT JOIN `data_hotel_booking` AS DHB  ON  THR.hotel_booking_id = DHB.id"
                            . " WHERE " . $condition . " AND " . $splited_array[1];
                    } else {
                        $splited_array = explode(" FROM `data_travel`", $query);
                        $query = " SELECT DTD.* FROM `data_travel` AS DTD "
                            . " LEFT JOIN `travel_hotel_relation` AS THR  ON DTD.id = THR.travel_id "
                            . " LEFT JOIN `data_hotel_booking` AS DHB  ON  THR.hotel_booking_id = DHB.id"
                            . " WHERE " . $condition . $splited_array[1];
                    }


                } else if ($custom_action_data_multiple_exist["value"] == 4) {

                    $condition = " DHB.check_out >= '$date_from' AND DHB.check_out <= '$date_to' ";
                    if (strpos($query, "WHERE") !== false) {
//                        dde(1);
                        $splited_array = explode("WHERE", $query);
                        $query = " SELECT DTD.* FROM `data_travel` AS DTD "
                            . " LEFT JOIN `travel_hotel_relation` AS THR  ON DTD.id = THR.travel_id "
                            . " LEFT JOIN `data_hotel_booking` AS DHB  ON  THR.hotel_booking_id = DHB.id"
                            . " WHERE " . $condition . " AND " . $splited_array[1];
                    } else {
//                        dde(2);
                        $splited_array = explode(" FROM `data_travel`", $query);
                        $query = " SELECT DTD.* FROM `data_travel` AS DTD "
                            . " LEFT JOIN `travel_hotel_relation` AS THR  ON DTD.id = THR.travel_id "
                            . " LEFT JOIN `data_hotel_booking` AS DHB  ON  THR.hotel_booking_id = DHB.id"
                            . " WHERE " . $condition . $splited_array[1];
                    }

                } else if ($custom_action_data_multiple_exist["value"] == 5) {

                    $condition = " (( travel_arraival_date >= '$date_from' AND  travel_arraival_date <= '$date_to')  ";
                    $condition .= " OR ( travel_departure_date >= '$date_from' AND  travel_departure_date <= '$date_to')) ";

                    if (strpos($query, "WHERE") !== false) {
                        $splited_array = explode("WHERE", $query);
                        $query = $splited_array[0] . " WHERE " . $condition . " AND " . $splited_array[1];

                    } else {
                        $splited_array = explode(" FROM `data_travel`", $query);
                        $query = $splited_array[0] . " FROM `data_travel` WHERE " . $condition . " " . $splited_array[1];
                    }

                } else if ($custom_action_data_multiple_exist["value"] == 6) {


                    $condition = " ((DHB.check_out >= '$date_from'   AND DHB.check_out <= '$date_to') ";
                    $condition .= " OR (DHB.check_in >= '$date_from' AND DHB.check_in <= '$date_to')) ";


                    if (strpos($query, "WHERE") !== false) {
                        $splited_array = explode("WHERE", $query);
                        $query = " SELECT DTD.* FROM `data_travel` AS DTD "
                            . " LEFT JOIN `travel_hotel_relation` AS THR  ON DTD.id = THR.travel_id "
                            . " LEFT JOIN `data_hotel_booking` AS DHB  ON  THR.hotel_booking_id = DHB.id"
                            . " WHERE " . $condition . " AND " . $splited_array[1];
                    } else {
                        $splited_array = explode(" FROM `data_travel`", $query);
                        $query = " SELECT DTD.* FROM `data_travel` AS DTD "
                            . " LEFT JOIN `travel_hotel_relation` AS THR  ON DTD.id = THR.travel_id "
                            . " LEFT JOIN `data_hotel_booking` AS DHB  ON  THR.hotel_booking_id = DHB.id"
                            . " WHERE " . $condition . $splited_array[1];
                    }
                } else if ($custom_action_data_multiple_exist["value"] == 7) {


                    $condition = " (DTD.date_last_update >= '$date_from'   AND DTD.date_last_update <= '$date_to') ";
//                    $condition .= " OR (DHB.check_in >= '$date_from' AND DHB.check_in <= '$date_to')) ";


                    if (strpos($query, "WHERE") !== false) {
                        $splited_array = explode("WHERE", $query);
                        $query = " SELECT DTD.* FROM `data_travel` AS DTD "
                            . " LEFT JOIN `travel_hotel_relation` AS THR  ON DTD.id = THR.travel_id "
                            . " LEFT JOIN `data_hotel_booking` AS DHB  ON  THR.hotel_booking_id = DHB.id"
                            . " WHERE " . $condition . " AND " . $splited_array[1];
                    } else {
                        $splited_array = explode(" FROM `data_travel`", $query);
                        $query = " SELECT DTD.* FROM `data_travel` AS DTD "
                            . " LEFT JOIN `travel_hotel_relation` AS THR  ON DTD.id = THR.travel_id "
                            . " LEFT JOIN `data_hotel_booking` AS DHB  ON  THR.hotel_booking_id = DHB.id"
                            . " WHERE " . $condition . $splited_array[1];

                    }
                }

            }


            if (isset($customdatabyhotel) && $customdatabyhotel > 0) {

                if (strpos($query, "WHERE") !== false) {
                    $splited_array = explode("WHERE", $query);
                    $query = " SELECT DTD.* FROM `data_travel` AS DTD "
                        . " LEFT JOIN `travel_hotel_relation` AS THR  ON DTD.id = THR.travel_id "
                        . " LEFT JOIN `data_hotel_booking` AS DHB  ON  THR.hotel_booking_id = DHB.id"
                        . " WHERE DHB.hotel_id =" . $customdatabyhotel["value"] . " AND " . $splited_array[1];
                } else {

                    $splited_array = explode(" FROM `data_travel`", $query);
                    $query = " SELECT DTD.* FROM `data_travel` AS DTD "
                        . " LEFT JOIN `travel_hotel_relation` AS THR  ON DTD.id = THR.travel_id "
                        . " LEFT JOIN `data_hotel_booking` AS DHB  ON  THR.hotel_booking_id = DHB.id"
                        . " WHERE DHB.hotel_id =  " . $customdatabyhotel["value"] . " AND " . $splited_array[1];
                }
            }


            $query = trim($query);

            if (substr($query, strlen($query) - 3) === "AND") {
                $query = substr($query, 0, strlen($query) - 3);
            }


            $count_query = "";
            if (strpos($query, 'SELECT DTD.*') !== false) {
                $count_query = str_replace("SELECT DTD.*", "SELECT count(*)", $query);
            }elseif(strpos($query, 'SELECT `rg_orders`.*') !== false){
                $count_query = "SELECT count(*) FROM `rg_orders`" . strstr($query, "WHERE");
            } else {
                $count_query = str_replace("SELECT *", "SELECT count(*)", $query);
            }


            $query .= " GROUP BY rg_orders.id ";
            
            $orderBY = self::getOrderByTable($table);
            // $qCount = getwayConnect::getwayCount($query);

            if ($exludeDefault) {
                if ($orderQ) {
                    $orderBY["ordering_by"] = rtrim($orderQ, ",");
                }
            }

            if ($limitQ != "false") {
                $query .= " {$orderBY["ordering_by"]} LIMIT {$limitQ} ";
            } else {
                $query .= " {$orderBY["ordering_by"]} ";
            }
            // if($send_request){
                ///  $custom_action_data_multiple_exist  = $actions;
                ///  $filter_action_custom_data_multiple_date  = $actions;
                $query = str_replace("delivery_global_filter_type_1","",$query);
                $query = str_replace("AND  delivery_global_filter_type_4","",$query);
                $backData = getwayConnect::getwayData($query, PDO::FETCH_ASSOC);//
                $qCount = count($backData);
                $time_end = microtime(true) - $start_time;
                    $log_query = @file_get_contents('/home/admin/web/new.regard-group.ru/public_html/log_query');
                    $log_array_data = json_decode($log_query);
                    $log_array_data[] = array('Date'=>gmdate("Y-m-d H:i:s"),'query_duration'=>$time_end,'query'=>$query);
                    @file_put_contents('/home/admin/web/new.regard-group.ru/public_html/log_query',json_encode($log_array_data));
                if (!empty($backData)) {
                    return array($qCount, $backData, $query);
                } else {
                    return false;
                }
            // }
            // else{
            //     return false;
            // }
        } else {
            return false;
        }


    }


    public static function getOrderByTable($table)
    {
        $data = getwayConnect::getwayData("SELECT * FROM data_tables WHERE table_name = '{$table}'", PDO::FETCH_ASSOC);
        if (isset($data[0]))
        return $data[0];
    }

    public static function getDataByPage($userLevel, $pageLevel, $pageName, $limitQ = 50)
    {
        
        if (self::compareUserLevelByPage($userLevel, $pageLevel)) {
            $table = self::pageTable($pageName);
            $pageName = $table["table_name"];
            if ($table) {
                $query = "SELECT * FROM `{$pageName}`";

                $qCount = getwayConnect::getwayCount($query);

                if ($limitQ != "false") {
                    $query .= "  {$table["ordering_by"]}  LIMIT {$limitQ}";
                } else {
                    $query .= " {$table["ordering_by"]} ";
                }
                
//                dd($query);
                
                $data = getwayConnect::getwayData($query, PDO::FETCH_ASSOC);
                if (!empty($data)) {
                    return array($qCount, $data);
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }


    public static function getArrayData($table_name, $code = false, $reverse = false)
    {
        $code = ($code) ? "," . $code : "";
        $pt = getwayConnect::getwayData("SELECT id,name{$code} FROM {$table_name}", PDO::FETCH_ASSOC);
        $data = array();
        $constants = get_defined_constants();
        for ($i = 0; $i < count($pt); $i++) {
            $pt[$i]["name"] = (isset($constants[$pt[$i]["name"]])) ? $constants[$pt[$i]["name"]] : $pt[$i]["name"];
            if ($code) {
                $data[$pt[$i]["code"]] = $pt[$i]["name"];
            } else if ($reverse === true) {
                $data[strtolower($pt[$i]["name"])] = $pt[$i]["id"];
            } else {
                $data[$pt[$i]["id"]] = $pt[$i]["name"];
            }
        }
        return $data;
    }

    public static function getJsonData($table_name, $code = false, $reverse = false)
    {
        $data = self::getArrayData($table_name,$code,$reverse);
        return json_encode($data);
    }

    public static function getFilter($filterId, $ul)
    {
        $data = getwayConnect::getwayData("SELECT * FROM data_filter WHERE id = '{$filterId}'");
        if (!empty($data)) {
            $fl = $data[0]["fr_level"];
            $ul = explode(",", $ul);
            $fl = explode(",", $fl);
            if (self::compareLevels($ul, $fl)) {
                return $data[0];
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public static function buildRelatedOptions($table_name, $rel_table, $active = false, $join = 'RIGHT JOIN ', $on = "", $where = "", $orderby = "")
    {
        $constants = get_defined_constants();
        $data = getwayConnect::getwayData("SELECT * FROM {$table_name} {$join} {$rel_table} {$on} {$where} {$orderby}", PDO::FETCH_ASSOC);

        $options = false;
        $relatedTo = false;
        if (!empty($data)) {
            foreach ($data as $value) {
                $value["name"] = (isset($constants[$value["name"]])) ? $constants[$value["name"]] : $value["name"];
                $current = ($value["sell_point_id"] == $active) ? "selected" : false;
                $options .= "<option value=\"{$value["sell_point_id"]}\" {$current}>{$value["name"]}</option>\n";
                $relatedTo = $value['depend_on'];
            }
        }
        return array($options, $relatedTo);
    }

    public static function buildOptions($table_name, $active = false, $subcode = false, $limit = false, $is_active = false, $last_query = '')
    {
        if ($subcode != false) {
            $subcode = "WHERE sub_code = '{$subcode}'";
        } else {
            $subcode = "";
        }
        if ($is_active && $subcode != false) {
            $is_active = "AND `active` = 1";
        } elseif ($is_active && $subcode == false) {
            $is_active = "WHERE `active` = 1";
        } else {
            $is_active = "";
        }
        $constants = get_defined_constants();

        if($table_name == 'delivery_reason'){
            $data = getwayConnect::getwayData("SELECT * FROM {$table_name} where active = 1 order by ordering", PDO::FETCH_ASSOC);
        }
        else{
            $data = getwayConnect::getwayData("SELECT * FROM {$table_name} {$subcode} {$is_active} {$last_query}", PDO::FETCH_ASSOC);
        }

        $options = "";
        $current = "";
        $value = "";
        if (!empty($data)) {
            for ($i = 0; $i < count($data); $i++) {
                if ($data[$i]["id"] == $active) {
                    $current = "selected";
                } else if (isset($data[$i]["code"]) && $data[$i]["code"] == $active) {
                    $current = "selected";
                } else {
                    $current = "";
                }
                if ($subcode != false) {
                    $value = $data[$i]["code"];
                } else {
                    $value = $data[$i]["id"];
                }
                $data[$i]["name"] = (isset($constants[$data[$i]["name"]])) ? $constants[$data[$i]["name"]] : $data[$i]["name"];
                if ($limit) {
                    if (!empty(array_intersect($limit, explode(",", $data[$i]["level"])))) {
                        if($table_name == 'delivery_street'){
                            $options .= "<option value=\"{$value}\" data-zone=\"{$data[$i]["zone"]}\" data-code=\"{$data[$i]["code"]}\" {$current}>{$data[$i]["name"]}";
                            $old_name = trim($data[$i]['old_name']); 
                            if($old_name != '' && !empty($old_name) && count($old_name) > 0){
                                $options .= ' ('.$old_name . ') ';
                            }
                            $options .= "</option>\n";
                        } else {
                            $options .= "<option value=\"{$value}\" {$current}>{$data[$i]["name"]}</option>\n";
                        }
                    }
                } elseif (!$limit) {
                    if($table_name == 'delivery_street'){
                        $options .= "<option value=\"{$value}\" data-zone=\"{$data[$i]["zone"]}\" data-duplicate=\"{$data[$i]["duplicate"]}\" data-duplicate-count=\"{$data[$i]["duplicate_count"]}\" data-code=\"{$data[$i]["code"]}\" {$current}>{$data[$i]["name"]}";
                            $old_name = trim($data[$i]['old_name']); 
                            if($old_name != '' && !empty($old_name) && count($old_name) > 0){
                                $options .= ' ('.$old_name . ') ';
                            }
                        $options .= "</option>\n";
                    } else {
                        $options .= "<option value=\"{$value}\" {$current}>{$data[$i]["name"]}</option>\n";
                    }
                }
            }
        }
        return $options;
    }
    public static function buildOptionsByOrdering($table_name, $active = false, $subcode = false, $limit = false, $is_active = false, $last_query = '')
    {
        if ($subcode != false) {
            $subcode = "WHERE sub_code = '{$subcode}'";
        } else {
            $subcode = "";
        }
        if ($is_active && $subcode != false) {
            $is_active = "AND `active` = 1";
        } elseif ($is_active && $subcode == false) {
            $is_active = "WHERE `active` = 1";
        } else {
            $is_active = "";
        }
        $constants = get_defined_constants();


        $data = getwayConnect::getwayData("SELECT * FROM {$table_name} {$subcode} {$is_active} {$last_query} order by ordering", PDO::FETCH_ASSOC);
        $options = "";
        $current = "";
        $value = "";
        if (!empty($data)) {
            for ($i = 0; $i < count($data); $i++) {
                if ($data[$i]["id"] == $active) {
                    $current = "selected";
                } else if (isset($data[$i]["code"]) && $data[$i]["code"] == $active) {
                    $current = "selected";
                } else {
                    $current = "";
                }
                if ($subcode != false) {
                    $value = $data[$i]["code"];
                } else {
                    $value = $data[$i]["id"];
                }
                $data[$i]["name"] = (isset($constants[$data[$i]["name"]])) ? $constants[$data[$i]["name"]] : $data[$i]["name"];
                if ($limit) {
                    if (!empty(array_intersect($limit, explode(",", $data[$i]["level"])))) {
                        if($table_name == 'delivery_street'){
                            $options .= "<option value=\"{$value}\" data-zone=\"{$data[$i]["zone"]}\" data-code=\"{$data[$i]["code"]}\" {$current}>{$data[$i]["name"]}";
                            $old_name = trim($data[$i]['old_name']); 
                            if($old_name != '' && !empty($old_name) && count($old_name) > 0){
                                $options .= ' ('.$old_name . ') ';
                            }
                            $options .= "</option>\n";
                        } else {
                            $options .= "<option value=\"{$value}\" {$current}>{$data[$i]["name"]}</option>\n";
                        }
                    }
                } elseif (!$limit) {
                    if($table_name == 'delivery_street'){
                        $options .= "<option value=\"{$value}\" data-zone=\"{$data[$i]["zone"]}\" data-code=\"{$data[$i]["code"]}\" {$current}>{$data[$i]["name"]}";
                            $old_name = trim($data[$i]['old_name']); 
                            if($old_name != '' && !empty($old_name) && count($old_name) > 0){
                                $options .= ' ('.$old_name . ') ';
                            }
                        $options .= "</option>\n";
                    } else {
                        $options .= "<option value=\"{$value}\" {$current}>{$data[$i]["name"]}</option>\n";
                    }
                }
            }
        }
        return $options;
    }

    public static function getStreetCodeByName($name)
    {
        $region = getwayConnect::getwayData("SELECT code FROM `delivery_street` WHERE name = '{$name}'", PDO::FETCH_ASSOC);
        if (!isset($region[0]["code"])) {
            $region[0]["code"] = "E-1";
        }
        return $region[0]["code"];
    }

    public static function isStreetCode($name)
    {
        $region = getwayConnect::getwayData("SELECT code FROM `delivery_street` WHERE code = '{$name}'", PDO::FETCH_ASSOC);
        if (!$region) {
            return false;
        } else {
            return true;
        }
    }

    public static function buildAllRegions()
    {
        $region = getwayConnect::getwayData("SELECT * FROM `delivery_region` WHERE active = '1'", PDO::FETCH_ASSOC);

        $json = array();
        if (!empty($region)) {
            for ($i = 0; $i < count($region); $i++) {

                $sub_region = getwayConnect::getwayData("SELECT * FROM `delivery_subregion` WHERE `sub_code` = '{$region[$i]["code"]}' AND active = '1'", PDO::FETCH_ASSOC);
                if (!empty($sub_region)) {
                    for ($u = 0; $u < count($sub_region); $u++) {
                        $subregion_street = getwayConnect::getwayData("SELECT * FROM `delivery_street` WHERE `sub_code` = '{$sub_region[$u]["code"]}' AND active = '1'", PDO::FETCH_ASSOC);
                        if (!empty($subregion_street)) {
                            for ($j = 0; $j < count($subregion_street); $j++) {
                                $json[] = array(
                                    "region" => array(
                                        "code" => $region[$i]["code"],
                                        "name" => $region[$i]["name"],
                                        "sub_region" => array(
                                            "code" => $sub_region[$u]["code"],
                                            "name" => $sub_region[$u]["name"],
                                            "street" => array(
                                                "code" => $subregion_street[$j]["code"],
                                                "name" => $subregion_street[$j]["name"],
                                                "old_name" => $subregion_street[$j]["old_name"],
                                                "zone" => $subregion_street[$j]["zone"],
                                                "duplicate" => $subregion_street[$j]["duplicate"],
                                                "duplicate_count" => $subregion_street[$j]["duplicate_count"]
                                            )
                                        )
                                    )
                                );
                            }
                        } else {
                            $json[] = array(
                                "region" => array(
                                    "code" => $region[$i]["code"],
                                    "name" => $region[$i]["name"],
                                    "sub_region" => array(
                                        "code" => $sub_region[$u]["code"],
                                        "name" => $sub_region[$u]["name"],
                                        "street" => array(
                                            "code" => "E-1",
                                            "name" => "none"
                                        )
                                    )
                                )
                            );
                        }
                    }
                } else {
                    $json[] = array(
                        "region" => array(
                            "code" => $region[$i]["code"],
                            "name" => $region[$i]["name"],
                            "sub_region" => array(
                                "code" => "E-1",
                                "name" => "none",
                                "street" => array(
                                    "code" => "E-1",
                                    "name" => "none"
                                )
                            )
                        )
                    );
                }

            }
        }
        return json_encode($json);
    }


    /////
    public static function buildFilter($userLevel, $pageName)
    {
        $filters = "";
        $fArray = array();
        $data = getwayConnect::getwayData("SELECT * FROM data_filter WHERE fr_source = '{$pageName}' ORDER BY `ordering` ASC");
        $ul = explode(",", $userLevel);
        $constants = get_defined_constants();


        if (!empty($data)) {
            for ($i = 0; $i < count($data); $i++) {
                $fl = explode(",", $data[$i]["fr_level"]);
                $compare = array_intersect($ul, $fl);
                if (!empty($compare) && $data[$i]["fr_active"] == "1") {
                    $filterData = json_decode($data[$i]["fr_action"], true);

                    if ($filterData["form"] == "input") {
                        $filterData["placeholder"] = (isset($constants[$filterData["placeholder"]])) ? $constants[$filterData["placeholder"]] : $filterData["placeholder"];
                        $filters .= "<{$filterData["form"]} autocomplete='off' type=\"{$filterData["type"]}\" name=\"{$filterData["id"]}\" id=\"{$data[$i]["id"]}\" oninput=\"{$filterData["onaction"]}\" placeholder=\"{$filterData["placeholder"]}\" addon=\"{$filterData["addon"]}\" style=\"margin:2px;\" >";
                    } else if ($filterData["form"] == "select") {
                        $filters .= "<{$filterData["form"]} name=\"{$filterData["id"]}\" id=\"{$data[$i]["id"]}\" onchange=\"{$filterData["onaction"]}\" style=\"margin:2px;width:98%;\">";

                        $sfd = getwayConnect::getwayData("SELECT * FROM " . $filterData["table"] . " WHERE active = '1'");

                        $global_filters = getwayConnect::getwayData("SELECT * FROM global_filters WHERE fr_source = '{$pageName}' AND `table_name` = '{$filterData["table"]}' AND `active` = 1", PDO::FETCH_ASSOC);

                        $data[$i]["fr_name"] = (isset($constants[$data[$i]["fr_name"]])) ? $constants[$data[$i]["fr_name"]] : $data[$i]["fr_name"];
                        $filters .= "<option value=\"\">{$data[$i]["fr_name"]}</option>";

                        if (!empty($global_filters)) {
                            $count_gf = count($global_filters);


                            for ($q = 0; $q < $count_gf; $q++) {
                                $dl = explode(",", $global_filters[$q]["level"]);
                                $compareAccess = array_intersect($ul, $dl);
                                if (!empty($compareAccess)) {
                                    $ident_rel = $global_filters[$q]["name"];
                                    $global_filters[$q]["name"] = (isset($constants[$global_filters[$q]["name"]])) ? $constants[$global_filters[$q]["name"]] : $global_filters[$q]["name"];
                                    $filters .= "<option value=\"{$global_filters[$q]["filter_value"]}\" data-prel=\"{$ident_rel}\">{$global_filters[$q]["name"]}</option>";
                                }
                            }
                        }/**/
                        if (!empty($sfd)) {
                            for ($u = 0; $u < count($sfd); $u++) {
                                $dl = explode(",", $sfd[$u]["level"]);
                                $compareAccess = array_intersect($ul, $dl);


                                if (!empty($compareAccess)) {
                                    if($filterData["table"] == 'delivery_deliverer'){
                                        $sfd[$u]["name"] = (isset($constants[$sfd[$u]["full_name"]])) ? $constants[$sfd[$u]["full_name"]] : $sfd[$u]["full_name"];
                                        $filters .= "<option value=\"{$sfd[$u]["id"]}\">{$sfd[$u]["full_name"]}</option>";
                                    }
                                    else{
                                        $sfd[$u]["name"] = (isset($constants[$sfd[$u]["name"]])) ? $constants[$sfd[$u]["name"]] : $sfd[$u]["name"];
                                        $filters .= "<option value=\"{$sfd[$u]["id"]}\">{$sfd[$u]["name"]}</option>";
                                    }
                                }


                            }
                        }

                        $filters .= "</select>";
                    }
                }
                $data[$i]["fr_name"] = (isset($constants[$data[$i]["fr_name"]])) ? $constants[$data[$i]["fr_name"]] : $data[$i]["fr_name"];
                $fArray[] = array($data[$i]["fr_name"], $filters);
                $filters = "";
            }
        }
        return $fArray;
    }


    public static function getOperator($uid)
    {
        $data = getwayConnect::getwayData("SELECT username FROM user WHERE uid = '{$uid}'");
        return ($uid == "-1" || !isset($data[0])) ? "Robot" : $data[0]["username"];
    }

    public static function filterLevel($num, $array)
    {
        //$data = array_filter($array,function($num,);
        $filter = array(0);
        for ($i = 0; $i < count($array); $i++) {
            if (preg_match("/^$num/", trim($array[$i]))) {
                $filter[] = $array[$i];
            }
        }
        if (count($filter) > 1) {
            unset($filter[0]);
        }
        return $filter;
    }

    public static function getRegionFromCC($cc)
    {
        $data = getwayConnect::getwayData("SELECT * FROM delivery_region WHERE cc = '{$cc}'");
        return (isset($data[0])) ? $data[0] : $cc;
    }


    public static function accessByLevel($userLevel, $pageName)
    {
        $checked = false;
        $ul = explode(",", $userLevel);
        $data = getwayConnect::getwayData("SELECT * FROM page_level WHERE pg_name = '{$pageName}'  AND active = 1");
        if (!empty($data)) {
            $range = explode(",", $data[0]["pg_level"]);
            for ($i = 0; $i < count($ul); $i++) {
                if (inRange($ul[$i], $range[0], $range[1])) {
                    $checked = true;
                }
            }

        }
        if (!$checked) {
            $data = self::getPagesLevels();

            if (!empty($data)) {
                for ($k = 0; $k < count($data); $k++) {
                    $range = explode(",", $data[$k]["pg_level"]);

                    for ($i = 0; $i < count($ul); $i++) {

                        if (inRange($ul[$i], $range[0], $range[1])) {

                            $url = include($_SERVER['DOCUMENT_ROOT'] . '/config.php');

                            header("location:" . $url['url'] . "/account/" . $data[$k]["pg_location"]);
//                            header("location:http://beta.regard-group.ru:8080/account/" . $data[$k]["pg_location"]);
//                            header("location:http://rg.arm-gift.com/account/" . $data[$k]["pg_location"]);
//                            header("location:http://localhost:84/account/" . $data[$k]["pg_location"]);
                            echo "ok";
                        }
                    }
                }
            }
        }
        return $checked;
    }
}

class  extra_data_hotel_booking
{

    public static function update_hotel_confirmed($id, $val)
    {
        $sql = "UPDATE data_hotel_booking set hotel_confirmed = $val WHERE id = $id";
        getwayConnect::$db->query($sql);
    }

    public static function update_filters_confirmed_hotels($id, $vals_json)
    {

        $sql = "UPDATE data_travel set travel_hotel_confirmations = '$vals_json' WHERE id = $id";
        getwayConnect::$db->query($sql);
    }


    public static function update_filters_travel_hotel_confirmations_json($id, $vals_json)
    {

        $sql = "UPDATE data_travel set travel_hotel_confirmations_json = '$vals_json' WHERE id = $id";
        getwayConnect::$db->query($sql);
    }


}

?>