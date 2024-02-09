<?php
session_start();
$pageName = "control";
$rootF = "../..";
include($rootF."/apay/pay.api.php");

include($rootF."/configuration.php");
include("actions.class.php");
$access = auth::checkUserAccess($secureKey);
$allData = array();
$buildClient = "";
$uid = "";
$level = "";
$userData = "";
$cc = "am";
$notify = "";
if(!$access){
    header("location:../../login");
}else{
    $uid = $_COOKIE["suid"];
    $level = auth::getUserLevel($uid);
    page::accessByLevel($level[0]["user_level"],$pageName);
    $levelArray = explode(",",$level[0]["user_level"]);
    $userData = auth::checkUserExistById($uid);
    $cc = $userData[0]["lang"];
}
page::cmd();
if(isset($_REQUEST["submit"]) && $_REQUEST["submit"] == "edit")
{
    $data = array();
    if(isset($_REQUEST["username"]) && isset($_REQUEST["access_level"]) && isset($_REQUEST["uid"])){
        $data['username'] = $_REQUEST["username"];
        $data['user_level'] = $_REQUEST["access_level"];
        if(action::edit($_REQUEST["uid"],$data)){
            $notify = '<div class="alert alert-success" role="alert"><strong>Success!</strong> User info changed ;)!
            </div>';
        }else{
            $notify = '<div class="alert alert-danger" role="alert">
              <strong>Fail!</strong> User info not changed :( !
            </div>';
        }
    }else if(isset($_REQUEST["uid"]) && isset($_REQUEST["password"]) && isset($_REQUEST["username"])){
        $uExist = auth::checkUserExist($_REQUEST["username"]);
        $password = $_REQUEST["password"];
        if($uExist)
        {
            $data['password'] = auth::hash($password,$secureKey);
        }
        if(action::edit($_REQUEST["uid"],$data)) {
            $notify = '<div class="alert alert-success" role="alert">
              <strong>Success!</strong> Password changed ;)!
            </div>';
        }else{
            $notify = '<div class="alert alert-danger" role="alert">
              <strong>Fail!</strong> Password not changed :( !
            </div>';
        }
    }
}
if(isset($_REQUEST["action"]) && $_REQUEST["action"] == "activate")
{
    if(isset($_REQUEST["mode"]) && isset($_REQUEST["uid"])){
        action::activation($_REQUEST["uid"],$_REQUEST["mode"]);
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
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    <link rel="stylesheet" href="<?=$rootF?>/template/account/sidebar.css">
    <!-- Bootstrap minified CSS -->
    <link rel="stylesheet" href="<?=$rootF?>/template/bootstrap/css/bootstrap.min.css">
    <!-- Bootstrap optional theme -->
    <link rel="stylesheet" href="<?=$rootF?>/template/bootstrap/css/bootstrap-theme.min.css">
    <link rel="stylesheet" href="<?=$rootF?>/template/datepicker/css/datepicker.css">
    <link rel="stylesheet" href="<?=$rootF?>/template/rangedate/daterangepicker.css" />
    <style>
        .article {word-wrap: break-word; max-width:125px;}
        .article .text {
            font-size: 13px;
            line-height: 17px;
            font-family: arial;
        }
        .article .text.short {
            height: 0px;
            overflow: hidden;
        }
        .article .text.full {

        }
        .read-more {

        }
        .date-picker-wrapper{
            z-index:99999999;
        }
        .datepicker{
            z-index:99999999;
        }
        hr{
            line-height: 0;
            margin-top:2px;
            margin-bottom:2px;
            border-color:#666;
        }
        #loading{
            display: block;
        }
    </style>
    <title>Controller</title>
</head>
<body>
<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#">RG-SYSTEM</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse" aria-expanded="false">
            <ul class="nav navbar-nav">
                <?=page::buildMenu($level[0]["user_level"])?>
                <li class="dropdown" id="menuDrop">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Filters <span class="caret"></span></a>
                    <ul class="dropdown-menu" role="menu" style="text-align:center;">
                        <?php
                        $fData = page::buildFilter($level[0]["user_level"],"control");
                        for($fi = 0 ; $fi < count($fData);$fi++)
                        {
                            //echo "<li class=\"divider\"></li>";
                            //echo "<li class=\"dropdown-header\">{$fData[$fi][0]}</li>";
                            echo "<li>{$fData[$fi][1]}</li>";
                        }
                        ?>

                    </ul>
                </li>
            </ul>
        </div><!--/.nav-collapse -->
    </div>
</nav>
 
<div class="container" style="margin-top:81px;width: 100%">
    <div class="panel panel-default">
        <div class="panel-heading">
            <div class="btn-group" role="group" aria-label="...">
                <button class="btn btn-default" name="orderF" id="33" onclick="filter(this,true);" value="delivery_date DESC"><span class="glyphicon glyphicon-signal" aria-hidden="true"></span> <?=(defined('DELIVERY_DAY_ORDER')) ? DELIVERY_DAY_ORDER : 'DELIVERY_DAY_ORDER';?></button>
                <select class="btn btn-default" id="showCount" onchange="showCount(this);" style="height: 34px;">
                    <option value="70" selected="">VIEW 70</option>
                    <option value="100">VIEW 100</option>
                    <option value="1000">VIEW 1000</option>
                    <option value="10000">VIEW 10000</option>
                    <option value="false">VIEW ALL</option>
                </select>
				<select class="btn btn-default" id="showCount" onchange="chart_type(this);" style="height: 34px;">
                    <option value="line" selected="">Line</option>
                    <option value="radar">Radar</option>
                </select>
            </div>
        </div>
        <div class="panel-body">
			<div id="loading" style="width:100%;text-align:center;"><img src="../../template/icons/loader.gif"></div>
            <canvas id="overallChart" width="400" height="150"></canvas>
        </div>
        <div class="panel-body">
            <canvas id="malusBonusNothing" width="400" height="150"></canvas>
        </div>
    </div>


</div>
<!-- initialize library-->
<!-- Latest jquery compiled and minified JavaScript -->
<script src="https://code.jquery.com/jquery-latest.min.js"></script>
<!-- Bootstrap minified JavaScript -->
<script src="<?=$rootF?>/template/bootstrap/js/bootstrap.min.js"></script>

<!--end initialize library-->
<!-- Menu Toggle Script -->
<!-- Bootstrap minified JavaScript -->
<script src="<?=$rootF?>/template/js/accounting.min.js"></script>
<script src="<?=$rootF?>/template/datepicker/js/bootstrap-datepicker.js"></script>
<script src="<?=$rootF?>/template/js/phpjs.js"></script>
<script src="<?=$rootF?>/template/rangedate/moment.min.js"></script>
<script src="<?=$rootF?>/template/rangedate/jquery.daterangepicker.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.4.0/Chart.bundle.min.js"></script>
<script>
    var timoutSet = null;
    var data ={};
    var send_data = "";
    var data_type = "flower";
    var fromP = 0;
    var toP = 70;
    var whoreceived = <?=page::getJsonData("delivery_receiver");?>;
    var payType = <?=page::getJsonData("delivery_payment");?>;
    var sourceType = <?=page::getJsonData("delivery_source");?>;
    var timeType = <?=page::getJsonData("delivery_time");?>;
    var sellPoint = <?=page::getJsonData("delivery_sellpoint");?>;
    var subregionType = <?=page::getJsonData("delivery_subregion","code");?>;
    var streetType= <?=page::getJsonData("delivery_street","code");?>;
    var statusTitle = <?=page::getJsonData("delivery_status");?>;
    var recLang = <?=page::getJsonData("delivery_language");?>;
    var driver_name = <?=page::getJsonData("delivery_deliverer");?>;
    var driver_car = <?=page::getJsonData("delivery_drivers");?>;
    var order_reason = <?=page::getJsonData("delivery_reason");?>;
    var order_currency = {
        "USD":478.2,
        "1":478.2,
        "EUR":560.3,
        "4":560.3,
        "RUB":7.3,
        "2":7.3,
        "IRR":0.0177,
        "6":0.0177,
        "GBP":726.7,
        "5":726.7,
        "convert":function($ISO,$price){
            if(this[$ISO]){
                return this[$ISO]*$price;
            }else{
                return $price;
            }
        },
        "pfp":function($total,$actual){
            return ($total > 0) ? (100*$actual)/$total: 0;
        }
    };
    var ctx = document.getElementById("overallChart");
    var ctx_bonus = document.getElementById("malusBonusNothing");
    function firstToUpperCase( str ) {
        return str.substr(0, 1).toUpperCase() + str.substr(1);
    }
    function convertToArray(obj) {
        return Object.keys(obj).map(function(key) {
            return obj[key];
        });
    }
    function filter(el,onfilter) {
        if(el){
            var element = jQuery("#"+el.id+" option:selected");
            if(element.attr("data-prel")){
                hfilter(element.attr("data-prel"));
            }
        }
        $("#loading").css("display","block");
        if (el) {
            if (!el.value || el.value == null || el.value == "") {
                delete data[el.name];
            }else{
                //data.push([el.id] = el.value;
                data[el.name] = {"filter":el.id,"value":el.value};
            }
        }
        if (onfilter) {
            fromP = 0;
            if(data["orderF"]){
                if(data["orderF"].value.search(/ASC/g) > 0)
                {
                    $("[id="+data["orderF"].filter+"]").each(function(){
                        if($(this).val() == data["orderF"].value)
                        {
                            var TempValue = $(this).val();
                            TempValue = TempValue.replace(/ASC/g,"DESC");
                            $(this).val(TempValue);
                        }
                    });
                }
                if(data["orderF"].value.search(/DESC/g) > 0)
                {
                    $("[id="+data["orderF"].filter+"]").each(function(){
                        if($(this).val() == data["orderF"].value)
                        {
                            var TempValue = $(this).val();
                            TempValue = TempValue.replace(/DESC/g,"ASC")
                            $(this).val(TempValue);
                        }
                    });
                }

            }
        }
        var activeFilter = "";
        var mu;
        for(mu in data)
        {
            //<li class=\"active\">Data</li>
            if($("#"+data[mu].filter).attr("placeholder"))
            {
                activeFilter += "<li class=\"active\">"+$("#"+data[mu].filter).attr("placeholder")+":"+data[mu].value+"</li>";
            }else if($("#"+data[mu].filter).find(":selected").text()){
                activeFilter += "<li class=\"active\">"+$("#"+data[mu].filter).find(":selected").text()+"</li>";
            }else if($("#"+data[mu].filter).text()){
                //activeFilter += "<li class=\"active\">"+$("#"+data[mu].filter).text()+"</li>";
            }
        }
        $("#activeFilters").html(activeFilter);
        var data_encode = base64_encode(json_encode(data));
        //console.log(data_encode);
        //console.log(base64_decode(data_encode));
        if (data) {
            send_data = "&encodedData="+data_encode;
        }else{
            send_data = "";
        }
        var userFriendly = "class=\"active\"";
        var first = false;
        clearTimeout(timoutSet);
        timoutSet = setTimeout(function(){
            //start
            $.get("<?=$rootF?>/data.php?cmd=data&page="+data_type+send_data+"&paginator="+fromP+":"+toP, function (get_data){
                //console.log(get_data);
                var CCo = 0;
                var tableData = get_data.data;
                var countP = get_data.count;
                var is_defect ="";
                var is_important = "";
                fromP = buildPaginator(countP,fromP,toP);
                window.overall_date = {};
                window.overall_value = {};
                var htmlData = "";
                var showA = "";
                if (countP > 0) {

                    for (var i = 0; i < tableData.length; i++) {
                        var d = tableData[i];
                        if(d.delivery_date != "0000-00-00"){
                            //d.delivery_date = "Day-Zero";

                        if(!overall_date['all']){
                            overall_date['all'] = new Array();
                        }
                        if(!overall_value['all']){
                            overall_value['all'] = new Array();
                        }
                        if(!overall_date['delivered']){
                            overall_date['delivered'] = new Array();
                        }
                        if(!overall_value['delivered']){
                            overall_value['delivered'] = new Array();
                        }
                        if(!overall_value['bonus']){
                            overall_value['bonus'] = 0;
                        }
                        if(!overall_value['malus']){
                            overall_value['malus'] = 0;
                        }
                        if(!overall_value['nothing']){
                            overall_value['nothing'] = 0;
                        }
                        if(d.bonus_type == 1){
                            overall_value['bonus'] += 1;
                        }else if(d.bonus_type == 2){
                            overall_value['malus'] += 1;
                        }if(d.bonus_type == 3){
                                overall_value['nothing'] += 1;
                        }
                        if($.inArray(d.delivery_date,overall_date['delivered']) > -1){
                            if(d.delivery_status == 3){
                                overall_value['delivered'][d.delivery_date] += 1;
                            }

                        }else{
                            overall_date['delivered'].push(d.delivery_date);
                            if(!overall_value['delivered'][d.delivery_date] && d.delivery_status == 3){
                                overall_value['delivered'][d.delivery_date] = 1;
                            }else if(!overall_value['delivered'][d.delivery_date]){
                                overall_value['delivered'][d.delivery_date] = 0;
                            }
                        }

                        if($.inArray(d.delivery_date,overall_date['all']) > -1){
                            overall_value['all'][d.delivery_date] += 1;
                        }else{
                            overall_date['all'].push(d.delivery_date);
                            if(!overall_value['all'][d.delivery_date]){
                                overall_value['all'][d.delivery_date] = 1;
                            }
                        }
                        }
                    }
                    window.chart_data = {
                        labels: overall_date['all'],
                        datasets: [
                            {
                                label: "OVERALL",
                                fill: false,
                                lineTension: 0.1,
                                backgroundColor: "rgba(75,192,192,0.4)",
                                borderColor: "rgba(75,192,192,1)",
                                borderCapStyle: 'butt',
                                borderDash: [],
                                borderDashOffset: 0.0,
                                borderJoinStyle: 'miter',
                                pointBorderColor: "rgba(75,192,192,1)",
                                pointBackgroundColor: "#fff",
                                pointBorderWidth: 1,
                                pointHoverRadius: 5,
                                pointHoverBackgroundColor: "rgba(75,192,192,1)",
                                pointHoverBorderColor: "rgba(220,220,220,1)",
                                pointHoverBorderWidth: 2,
                                pointRadius: 1,
                                pointHitRadius: 10,
                                data: convertToArray(overall_value['all']),
                                spanGaps: false,
                            },
                            {
                                label: "Delivered",
                                fill: true,
                                lineTension: 0.1,
                                backgroundColor: "rgba(150, 239, 114,0.4)",
                                borderColor: "rgb(150, 239, 114,1);",
                                borderCapStyle: 'butt',
                                borderDash: [],
                                borderDashOffset: 0.0,
                                borderJoinStyle: 'miter',
                                pointBorderColor: "rgba(150, 239, 114,1)",
                                pointBackgroundColor: "#fff",
                                pointBorderWidth: 1,
                                pointHoverRadius: 5,
                                pointHoverBackgroundColor: "rgba(150, 239, 114,1)",
                                pointHoverBorderColor: "rgba(250, 237, 144,1)",
                                pointHoverBorderWidth: 2,
                                pointRadius: 5,
                                pointHitRadius: 10,
                                data: convertToArray(overall_value['delivered']),
                                spanGaps: false,
                            }
                        ]
                    };
                    window.bonus_chart = {
                        labels: ['BONUS TYPE'],
                        datasets: [
                            {
                                label: "MALUS",
                                backgroundColor: [
                                    'rgba(191, 63, 63, 0.2)'
                                ],
                                borderColor: [
                                    'rgba(191, 63, 63,1)'
                                ],
                                borderWidth: 1,
                                data:[overall_value['malus']]
                            },
                            {
                                label: "BONUS",
                                backgroundColor: [
                                    'rgba(49, 116, 174, 0.2)'
                                ],
                                borderColor: [
                                    'rgba(49, 116, 174,1)'
                                ],
                                borderWidth: 1,
                                data: [overall_value['bonus']]
                            },
                            {
                                label: "NOTHING",
                                backgroundColor: [
                                    'rgba(239, 168, 67, 0.2)'
                                ],
                                borderColor: [
                                    'rgba(239, 168, 67,1)'
                                ],
                                borderWidth: 1,
                                data: [overall_value['nothing']]
                            }
                        ]
                    };
                    try{
                        myChart.destroy();
                        MyBonusChart.destroy();
                    }catch(e){

                    }
                    window.myChart = new Chart(ctx, {
                        type:"line",

                       data:chart_data,
                        options: {

                            responsive: true
                        }
                    });
                    window.MyBonusChart = new Chart(ctx_bonus, {
                        type:"bar",

                        data:bonus_chart,
                        options: {

                            responsive: true
                        }
                    });
                }
                $("#loading").css("display","none");
            });
            //end
        },2000);
        return false;
    }
    filter(null);
    $('#menuDrop .dropdown-menu').on({
        "click":function(e){
            e.stopPropagation();
        }
    });
    function showCount(el)
    {
        toP = el.value;
        filter(null,true);
    }
    $(document).on('click', "button.read-more", function() {

        var elem = $(this).parent().find(".text");
        if(elem.hasClass("short"))
        {
            elem.removeClass("short").addClass("full");

        }
        else
        {
            elem.removeClass("full").addClass("short");

        }
    });
    $(document).on('click', "button.show-ALL", function() {

        var elem = $("div").find(".text");
        if(elem.hasClass("short"))
        {
            elem.removeClass("short").addClass("full");

        }
        else
        {
            elem.removeClass("full").addClass("short");

        }
    });

    function buildPaginator(tCount,pfrom,pto){
        var htmlP = "";
        var pagesC = Math.ceil(tCount/pto);
        var vNum = 0;
        if (pagesC > 1) {
            for(var i = 0; i < pagesC; i++)
            {
                var pNum = i+1;

                if (vNum == pfrom) {
                    htmlP += "<li class=\"active\"><a href=\"#\" onclick=\"return false;\">"+pNum+"</a></li>";
                }else{
                    htmlP += "<li ><a href=\"#\" onclick=\"loadData("+vNum+","+pto+");return false;\">"+pNum+"</a></li>";
                }
                vNum = pto+vNum;
            }
        }
        $("#buildPages").html(htmlP);
        return vNum;
    }
    function loadData(v1,v2)
    {
        fromP = v1;
        filter(null);
    }
    if ($('[addon="rangedate"]')) {
        $('[addon="rangedate"]').dateRangePicker({shortcuts :
        {
            'prev-days': [3,5,7],
            'prev': ['week','month','year'],
            'this': ['week','month','year'],
            'next-days':null,
            'next':null
        }}).bind('datepicker-apply',function(){filter(this,true);});
    }
    if ($('[addon="date"]')) {
        $('[addon="date"]').datepicker({format: 'yyyy-mm-dd'}).on('changeDate',function(){filter(this,true);});
    }
    function totalResset()
    {
        $("input[type=text]").each(function(){$(this).val('');});
        $("select").each(function(){$(this).val('');});
        $("#showCount").val("70");
        data ={};

        toP = 70;
        filter(null,this);
    }
    function sendMail()
    {
        var getMails = "";
        $("input:checkbox[id^='mailToSend']").each(function(){

            if($(this).is(":checked"))
            {
                getMails += $(this).val()+",";
            }
            if(!getMails)
            {
                $(this).prop( "disabled", false );
            }
        });
        if(getMails)
        {
            window.open("mail/?mails="+getMails, "", "toolbar=yes, scrollbars=yes, resizable=yes,width=800, height=400");
        }
    }
    function CheckAccounting(orderId)
    {
        window.open("products/?cmd=check&orderId="+orderId, "", "toolbar=yes, scrollbars=yes, resizable=yes,width=970, height=600");
    }
    function onroad(id){
        request_call('&id='+id+'&delivery_status=6');
    }
    function product_ready(id){
        request_call('&id='+id+'&delivery_status=12');
    }
    function request_call(call_data){
        $.get("ajax.php?update_order=true"+call_data, function (get_data){
            if(get_data.status && get_data.status == "ok"){
                alert('ok');
                filter(null);
            }
        });
    }
    function selectAll(type)
    {
        $("input:checkbox[id^='mailToSend']").each(function(){

            if(type)
            {
                $(this).prop('checked', true);
            }else{
                $(this).prop('checked', false);
            }
        });
    }
    function checkAll(data)
    {
        if(data.checked)
        {
            selectAll(true);
        }else{
            selectAll();
        }
    }
    jQuery("[name=allfpf]").attr("disabled","disabled");
    jQuery("[name=allfpf]").html("<option>---</option>");
	function chart_type(obj){
		if(obj.value == 'line'){
			try{
				myChart.destroy()
			}catch(e){

			}
			window.myChart = new Chart(ctx, {
				type:"line",

			   data:chart_data,
				options: {

					responsive: true
				}
			});
		}else if(obj.value == 'radar'){
			try{
				myChart.destroy()
			}catch(e){

			}
			window.myChart = new Chart(ctx, {
				type:"radar",

			   data:chart_data,
				options: {

					responsive: true
				}
			});
		}
		
	}
    function hfilter(type){
        var allFlPartners = <?=json_encode(getwayConnect::getwayData("SELECT `data_partners`.`sell_point_id` as `value`,`delivery_sellpoint`.`name` FROM `data_partners` RIGHT JOIN `delivery_sellpoint` ON  `data_partners`.`sell_point_id` = `delivery_sellpoint`.`id` WHERE `data_partners`.`active` = 1 AND `data_partners`.`depend_on` = 'flower' ORDER BY `data_partners`.`ordering`",PDO::FETCH_ASSOC))?>;
        var allRTPartners = <?=json_encode(getwayConnect::getwayData("SELECT `data_partners`.`sell_point_id` as `value`,`delivery_sellpoint`.`name` FROM `data_partners` RIGHT JOIN `delivery_sellpoint` ON  `data_partners`.`sell_point_id` = `delivery_sellpoint`.`id` WHERE `data_partners`.`active` = 1 AND `data_partners`.`depend_on` = 'travel' ORDER BY `data_partners`.`ordering`",PDO::FETCH_ASSOC))?>;
        var allOws = <?=json_encode(getwayConnect::getwayData("SELECT `data_partners`.`sell_point_id` as `value`,`delivery_sellpoint`.`name` FROM `data_partners` RIGHT JOIN `delivery_sellpoint` ON  `data_partners`.`sell_point_id` = `delivery_sellpoint`.`id` WHERE `data_partners`.`active` = 1 AND `data_partners`.`depend_on` = 'ows' ORDER BY `data_partners`.`ordering`",PDO::FETCH_ASSOC))?>;
        var phtml = '<option value="">SELECT ONE</option>';
        jQuery("[name=allfpf]").removeAttr("disabled");
        if(type == "FLOWERS_PARTNERS"){
            for(var i=0;i < allFlPartners.length;i++){
                phtml += "<option value=\""+allFlPartners[i].value+"\" >"+allFlPartners[i].name+"</option>";
            }
        }else if(type == "TRAVEL_PARTNERS"){
            for(var i=0;i < allRTPartners.length;i++){
                phtml += "<option value=\""+allRTPartners[i].value+"\" >"+allRTPartners[i].name+"</option>";
            }
        }else if(type == "OTHER_WEBSITES"){
            for(var i=0;i < allOws.length;i++){
                phtml += "<option value=\""+allOws[i].value+"\" >"+allOws[i].name+"</option>";
            }
        }else{
            jQuery("[name=allfpf]").attr("disabled","disabled");
        }
        jQuery("[name=allfpf]").html(phtml);
        phtml = "";
    }
</script>
</body>
</html>