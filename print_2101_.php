<?php
session_start();

include("apay/pay.api.php");
include("apay/travel.api.php");
include("configuration.php");

include_once $_SERVER['DOCUMENT_ROOT'].'/controls/FlowersForms.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/account/flower_orders/lang/language_am.php';

date_default_timezone_set("Asia/Yerevan");


    $datePost = "";
        if (isset($_POST["date"]) && strlen($_POST["date"]) > 0) {
            $datePost = $_POST["date"];
        } else {
            $datePost = date('Y-m-d');
        }
            
        
        // $editdatte = "readonly";
        $editdatte = "";
        if (strpos($datePost, date("Y-m-d")) !== false) {
            $editdatte = "";
        }
        
        
$now    = strtotime(getway::utc());
$access = auth::checkUserAccess($secureKey);

$arrayStages   = array();
$arrayStages[] = 1 ;
$arrayStages[] = 2 ;
$arrayStages[] = 3 ;
$arrayStages[] = 4 ;
$arrayStages[] = 5 ;
$arrayStages[] = 6 ;
$arrayStages[] = 7 ;
$arrayStages[] = 8 ;
$arrayStages[] = 9 ;
$arrayStages[] = 10 ;

function getConstant($value){
    if (defined($value)) { 
        return constant($value);
    } else {
        return $value;
    }
}

if ($access) {
    
    $arrayUsers = FlowersForm::getUserList();
    $driverList = FlowersForm::getDriversList();
    
    $selectStatus = '<select name="status">
        <option value="0">Ստատուս</option><option value="1,3,6,7,11,12,13,14" data-prel="ALL_PAID">Բոլոր վճարվածները</option>
        <option value="2,4,5,8,9" data-prel="ALL_UNPAID">Բոլոր Չվճարվածները</option>
        <option value="1">Հաստատված</option><option value="2">Անավարտ</option>
        <option value="3">Առաքված</option><option value="4">Չեղյալ</option>
        <option value="5">Բաց թողնված</option>
        <option value="6">Ճանապարհին</option>
        <option value="7">Վերադարձրած</option>
        <option value="8">Կոմունիկացիա</option>
        <option value="9">Դուբլիկատ</option>
        <option value="10">Ավտոմատ</option>
        <option value="11">Հրաժարվել է առաքել</option>
        <option value="12">Պատրաստ</option>
        <option value="13">Հաստատված առաքիչի կողմից</option>
     </select>';
    
    
    if (isset($_POST["status"]) && $_POST["status"] != "0") {
         $selectStatus = str_replace('value="'.$_POST["status"].'"' , 'value="'.$_POST["status"].'" selected ' ,  $selectStatus);
    }
    
$deliverer_names = [];
$deliverer_informations = [];
if ($file = fopen("couriers_list.txt", "r")) {
    while(!feof($file)) {
        $line = fgets($file);
        $info = explode('|', $line);
        if(count($info) > 1){
            $deliverer_names[] = $info[0];
            $deliverer_informations[] = $info[1]; 
        }
    }
    fclose($file);
}
?> 

<?php 
    if(isset($_POST['order_id']) && $_POST['order_id'] != "0" && isset($_POST['old_status']) && $_POST['old_status'] != "0"){
        $order_id = $_POST['order_id'];
        $status = $_POST['old_status'];
        if($status != "3"){
            if($status == "1"){
                $status = 6;
            } else if($status == "6"){
                $status = 3;
            }
            getwayConnect::getwaySend("UPDATE rg_orders set delivery_status='{$status}' where id='{$order_id}'");
        }
        echo json_encode(array('new_status' => $status));
        exit;
    }

?>
<html>

 
    <header>
        <meta charset="utf-8">
        <style type="text/css">
        @media print { body { -webkit-print-color-adjust: exact; } }
        </style>
        
            <script src="https://code.jquery.com/jquery-latest.min.js"></script>
        
            <script>
    
                (function() {
                    var beforePrint = function() {
                         $("#hide_print").hide();
                    };
                    var afterPrint = function() {
                        $("#hide_print").show();
                    };

                if (window.matchMedia) {
                    var mediaQueryList = window.matchMedia('print');
                    mediaQueryList.addListener(function(mql) {
                        if (mql.matches) {
                            beforePrint();
                        } else {
                            afterPrint();
                        }
                    });
                }

                window.onbeforeprint = beforePrint;
                window.onafterprint = afterPrint;
                }());

                

                function saveName (driveid ,stage ) {
                   
                   var text = $("#drivename_" + driveid + "_" + stage).val().trim();
                   if (text.length > 1) {
                        $.post( "/ajax/post_print.php", {driveid: driveid, stage : stage , name : text }).done(function( data ) {
                             
                        });
                   }
                }
    
    
    </script>
        
    </header>
    
    <body>


<div  id="hide_print" >
<form action="" method="POST">
    
    <?=$selectStatus?>
    <select name="selectedDriver">
      <?php
        echo "<option value=\"0\">All Drivers</option>";
        foreach ($driverList as $value) {
            
            
            $value["name"] = getConstant($value["name"]);
            
            
               
            
            if (isset($_POST["selectedDriver"]) && $_POST["selectedDriver"] > 0 && $_POST["selectedDriver"] == $value["id"] ) {
                 echo "<option value=\"" . $value["id"] . "\" selected>" .$value["name"] . "</option>";
            } else {
                 echo "<option value=\"" . $value["id"] . "\">" .$value["name"] . "</option>";
            }
        }
      ?>
        
    </select>
    <select name="selectStage" id="selectStage">
        <option value='0'>All</option>
        <?php 
            for($i = 1; $i < 11; $i++){
                echo "<option value='".$i."'";
                if(isset($_POST['selectStage']) && $_POST['selectStage'] > 0 && $_POST['selectStage'] == $i){
                    echo "selected='selected'";
                }
                echo ">";
                echo $i;
                echo "</option>";
            }
        ?>
    </select>
    <select name="selectedUser">
        <?php
            echo "<option value=\"0\">All Users</option>";
            foreach ($arrayUsers as $key => $value) {
                if (isset($_POST["selectedUser"]) && $_POST["selectedUser"] == $key) {
                     echo "<option value=\"" . $key. "\" selected>$value</option>";
                } else {
                     echo "<option value=\"" . $key. "\">$value</option>";
                }
            }
        
        ?>
    </select>
    <input type="date" name="date" value="<?=$datePost?>"> 
    <input type="submit" value="Filter">
    
</form>
</div>
 
     
    <?php
        
        $result = array();
        $query_drivers  = "SELECT * FROM `delivery_deliverer` ";
        $query_result = getwayConnect::$db->query($query_drivers);
        foreach ($query_result as $row) {
             $result[$row["id"]] = $row;
        }
        
        
        if (isset($_POST["selectedDriver"]) && $_POST["selectedDriver"] > 0) {
                $tmpar = $result[$_POST["selectedDriver"]];
                $result = array();
                $result[] = $tmpar;
        }
        $selectStage = 0;
        if(isset($_POST['selectStage']) && $_POST['selectStage'] > 0){
            $selectStage = $_POST['selectStage'];
        }
        $number = 0;
        foreach ($result as $key => $valueDrivers) {
            $id   = $valueDrivers["id"];
            $name = $valueDrivers["name"];
            $name = getConstant($name);
                    
            $stageData = "";
            $showData = false;
            $stageWorkingUsers = "";
                    
                    
            $backgroundTr = 'style="background-color: #e8e8e8"';
            
                    foreach ($arrayStages as $valueStages) {
                        
                        if($selectStage > 0 && $selectStage != $valueStages){
                            continue;
                        }
                        $number = 0;
                        $conditoion  = " stage > 0";
                        $conditoion .= " AND stage = $valueStages  ";
                        $conditoion .= " AND deliverer = $id ";
                        $conditoion .= " AND delivery_date='" .$datePost."'";
                        
                        
                        if (isset($_POST["status"]) && strlen($_POST["status"]) > 0 && $_POST["status"] != "0") {
                           $conditoion .= " AND delivery_status IN (" . $_POST["status"].")";
                        }
                        
                        if (isset($_POST["selectedUser"] ) && (int)$_POST["selectedUser"] > 0) {
                           $conditoion .= " AND userid = " . (int)$_POST["selectedUser"] ."";
                        }
                        
                        $stageData = '<b style="color: blue;">' . $valueStages.' պատոկ: </b>';
                        $queryDataTime = "SELECT RGO.*, DSR.name AS subname , DST.name AS strname, DTM.name AS dtmname  FROM rg_orders AS RGO " 
                                . " LEFT JOIN delivery_op_subregion AS DSR ON  DSR.id =  RGO.receiver_subregion"
                                . " LEFT JOIN delivery_street AS DST ON DST.code = RGO.receiver_street"
                                . " LEFT JOIN delivery_time AS DTM ON DTM.ID = RGO.delivery_time "
                                . " WHERE step = 0 AND $conditoion  GROUP BY RGO.id ORDER BY delivery_time ASC  ";
                        
                         $queryDataStep = "SELECT RGO.*, DSR.name AS subname , DST.name AS strname, DTM.name AS dtmname  FROM rg_orders AS RGO " 
                                . " LEFT JOIN delivery_op_subregion AS DSR ON  DSR.id =  RGO.receiver_subregion"
                                . " LEFT JOIN delivery_street AS DST ON DST.code = RGO.receiver_street"
                                . " LEFT JOIN delivery_time AS DTM ON DTM.ID = RGO.delivery_time "
                                . " WHERE  step > 0  AND  $conditoion GROUP BY RGO.id ORDER BY step ASC  ";
                        
                        //name
                        $stageUsersWorking = "";
                        
                        $query_data_result_time = getwayConnect::$db->query($queryDataTime);
                        $query_data_result_step = getwayConnect::$db->query($queryDataStep);
                         
                         
                        $stagetableFirst = '<table style="width: 100%;" border="1">            
                                    <tr>
                                       <td style="width: 150px; color : blue; font-size:20px;"> '.$name.'<br>'.$valueStages .' ուղերթ <hr>';
                        if(count($deliverer_names)){
                            $stagetableFirst .= '<select class="delivererInfo">';
                            $stagetableFirst .= '<option></option>';
                            foreach($deliverer_names as $deliverer_key => $deliverer_name){
                                $stagetableFirst .= '<option value="'.$deliverer_name.'"';
                                if(getDriverName ($valueStages , $id, $datePost) == $deliverer_name){
                                    $stagetableFirst .= 'selected="selected"';    
                                }
                                $stagetableFirst .= '>'.$deliverer_name.'</option>';
                            }
                            $stagetableFirst .= '</select>';
                        }
                        $stagetableFirst .= ' <input type="text" id="drivename_'.$id.'_'. $valueStages.'"  value="' .  getDriverName ($valueStages , $id, $datePost)  . '"  '. $editdatte . '> '
                                . ' <button onclick="saveName('. $id  . ', ' .$valueStages .  ')">SAVE</button> ';
                        $stagetable = '</td><td> <table style="width: 100%;border: 1px solid black;">            
                                            <tr style="border: 1px solid black;">
                                                 <th align="left" style="width: 50px; " >Հ/Հ</th>
                                                 <th align="left" style="width: 100px;">Պատվեր</th>
                                                 <th align="left" style="width: 120px;">Ժամ</th>
                                                 <th align="left">Հասցե</th>
                                                 <th align="left" style="width: 100px;">Կտոր</th>
                                            </tr>';
                        
                        $showStage = FALSE;
                        
                        foreach ($query_data_result_step as $rowData) {
                              $number++;
                              $statusImage = '<img src="/template/icons/status/' . $rowData["delivery_status"] . '.png" class="statusChange" data-order="'.$rowData["id"].'" data-status="'.$rowData["delivery_status"].'" style="width : 20px" />   &nbsp;';
                              
                              if ($rowData["userid"] > 0) {
                                    $workingUser = $arrayUsers[$rowData["userid"]];
                                    if (strpos($stageWorkingUsers, $workingUser) !== false) {

                                    } else {
                                        $stageWorkingUsers .=  $workingUser . " <br> " ;
                                    }
                              }
                              
                              
                              $qty = "";
                              if ($rowData["quantity"] > 0) {
                                   $qty = $rowData["quantity"];
                              }
                              
                              
                              $manualTime = "";
                              
                              if ( isset($rowData["delivery_time_manual"] ) && strlen($rowData["delivery_time_manual"] ) > 1) {
                                 $manualTime =   " (" . $rowData["delivery_time_manual"] . ") "  ;
                              }
                              
                              
                              $stagetable .= '<tr ' .  $backgroundTr . '>
                                    <td style="border-bottom: 1px solid black; font-size:18px;">'.$number.'</td>
                                    <td style="border-bottom: 1px solid black; font-size:18px;">'.$rowData["id"] .'</td>
                                    <td style="border-bottom: 1px solid black; font-size:18px;">'.$statusImage . $rowData["dtmname"] . $manualTime .   '</td>
                                    <td style="border-bottom: 1px solid black; font-size:18px;">'.$rowData["strname"]. " ".  $rowData["receiver_address"]. " / ";
                                    if(isset($rowData['receiver_floor']) && $rowData['receiver_floor'] != ''){
                                        $stagetable .= ' բն․ '.$rowData["receiver_floor"];
                                    }
                                    if(isset($rowData['receiver_entrance']) && $rowData['receiver_entrance'] != ''){
                                        $stagetable .= ' մուտք '.$rowData["receiver_entrance"];
                                    }
                                    if(isset($rowData['receiver_door_code']) && $rowData['receiver_door_code'] != ''){
                                        $stagetable .= ' կոդ '.$rowData["receiver_door_code"];
                                    }
                                    $stagetable .= '  '. $rowData["subname"].'</td>
                                    <td style="border-bottom: 1px solid black; font-size:18px;">'.$qty  .'</td>
                                </tr>';
                              
                              
                              if ($backgroundTr == 'style="background-color: #e8e8e8"') {
                                  $backgroundTr = "";
                              } else {
                                  $backgroundTr = 'style="background-color: #e8e8e8"';
                              }
                              
                              
                              $showStage = TRUE;
                        }
                        
                        foreach ($query_data_result_time as $rowData) {
                              $number++;
                              $statusImage = '<img src="/template/icons/status/' . $rowData["delivery_status"] . '.png" class="statusChange" data-order="'.$rowData["id"].'" data-status="'.$rowData["delivery_status"].'" style="width : 20px" />   &nbsp;';
                              
                              if ($rowData["userid"] > 0) {
                                    $workingUser = $arrayUsers[$rowData["userid"]];
                                    if (strpos($stageWorkingUsers, $workingUser) !== false) {

                                    } else {
                                        $stageWorkingUsers .=  $workingUser . " <br> " ;
                                    }
                              }
                              
                              
                              $qty = "";
                              if ($rowData["quantity"] > 0) {
                                   $qty = $rowData["quantity"];
                              }
                              
                              
                              $manualTime = "";
                              
                              if ( isset($rowData["delivery_time_manual"] ) && strlen($rowData["delivery_time_manual"] ) > 1) {
                                 $manualTime =   " (" . $rowData["delivery_time_manual"] . ") "  ;
                              }
                              
                              
                              $stagetable .= '<tr ' .  $backgroundTr . '>
                                    <td style="border-bottom: 1px solid black; font-size:18px;">'.$number.'</td>
                                    <td style="border-bottom: 1px solid black; font-size:18px;">'.$rowData["id"] .'</td>
                                    <td style="border-bottom: 1px solid black; font-size:18px;">'.$statusImage . $rowData["dtmname"] . $manualTime .   '</td>
                                    <td style="border-bottom: 1px solid black; font-size:18px;">'.$rowData["strname"]. " ".  $rowData["receiver_address"]. " / ";
                                    if(isset($rowData['receiver_floor']) && $rowData['receiver_floor'] != ''){
                                        $stagetable .= ' բն․ '.$rowData["receiver_floor"];
                                    }
                                    if(isset($rowData['receiver_entrance']) && $rowData['receiver_entrance'] != ''){
                                        $stagetable .= ' մուտք '.$rowData["receiver_entrance"];
                                    }
                                    if(isset($rowData['receiver_door_code']) && $rowData['receiver_door_code'] != ''){
                                        $stagetable .= ' կոդ '.$rowData["receiver_door_code"];
                                    }
                                    $stagetable .= '  '. $rowData["subname"].'</td>
                                    <td style="border-bottom: 1px solid black; font-size:18px;">'.$qty  .'</td>
                                </tr>';
                              
                              
                              if ($backgroundTr == 'style="background-color: #e8e8e8"') {
                                  $backgroundTr = "";
                              } else {
                                  $backgroundTr = 'style="background-color: #e8e8e8"';
                              }
                              
                              
                              $showStage = TRUE;
                        }
                        $stagetable .= '</table>';
                        if(getDriverName ($valueStages , $id, $datePost) &&  getDriverName ($valueStages , $id, $datePost) != ''){
                            if(array_search(getDriverName ($valueStages , $id, $datePost), $deliverer_names) !== false){
                                $stagetable .= '<p>'.$deliverer_informations[array_search(getDriverName ($valueStages , $id, $datePost), $deliverer_names)].'</p>';    
                            }
                        }
                        
                        
                        $stagetable .= '</td></tr></table>';
                        
                        
                        if ($showStage) {
                            echo  $stagetableFirst. '<hr><span style="color: red ; font-size:10px;">' . $stageWorkingUsers. '</span>' . $stagetable;
                            
                        }
                        
                       
                    }
                    
            ?>
                
            <?php
        }
    ?>
   
<?php
    
}


function  getDriverName ($stageid , $driveid, $date = null) {
     $result_data = "";
     if(!isset($date)){
        $date = date('Y-m-d');
     }
     $query_check = "SELECT * FROM page_print WHERE DRIVERID = $driveid AND STAGE = $stageid AND '{$date}' = DATE(CDATE) ";
     $result =  getwayConnect::$db->query($query_check);

     foreach ($result as $row) {
       $result_data =  $row["NAME"];
     }
     
     return $result_data;
}



?>
    <script>
        $('body').on('change', '.delivererInfo', function(e){
            $(this).siblings('input').val($(this).val());
        });
        $('body').on('click', '.statusChange', function(e){
            e.preventDefault();
            let order_id = $(this).attr('data-order');
            let status = $(this).attr('data-status');
            let $self = $(this);
            $.ajax({
                type: 'post',
                url: location.href,
                data: {
                    "order_id": order_id,
                    "old_status": status
                },
                success: function(data){
                    var new_data = JSON.parse(data);
                    $self.attr('data-status', new_data['new_status']);
                    $self.attr('src', '/template/icons/status/'+new_data['new_status']+'.png');
                }
            })
        });
    </script>
    </body>
</html>
