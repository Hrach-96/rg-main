<?php

if ( !(isset($userData["id"]) &&(int) $userData["id"] > 0) ) {
    header("location:../../login");
}

$userListArray      = Accounting::getUsersList(); 
$currencyListArray  = Accounting::getCurrenciesList(); 
$defaultDate =  date("Y-m-d");

$filterArray = array();
$filterprice = "";
$filtertarget = "";


$filterArray["users"] = $userData["id"];


if (isset($_POST["posting"]) && $_POST["posting"]=="filter" ) {
   
  if (isset($_POST["price"])) {
      $filterArray["price"] = $filterprice = $_POST["price"] ;
       
  }
  
  if (isset($_POST["target"])) {
      $filtertarget  = $filterArray["target"] = $_POST["target"] ;
  }
  
  if (isset($_POST["actiontype"])) {
      $filterArray["actiontype"] = $_POST["actiontype"] ;
  }
  
 
    
  if (isset($_POST["currencies"])) {
      $filterArray["currencies"] = $_POST["currencies"] ;
  }
  
  if (isset($_POST["date"])) {
      $defaultDate = $_POST["date"] ;
      $filterArray["date"] = $defaultDate;
  }
}

$userid = 0;
$actiontype = 0;

$arrayObjects = Accounting::getAllObjects($userid, $defaultDate , $actiontype , $filterArray);


?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="<?= $rootF ?>/template/account/sidebar.css">
    
    <link rel="stylesheet" href="<?= $rootF ?>/template/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= $rootF ?>/template/bootstrap/css/bootstrap-theme.min.css">
    <link rel="stylesheet" href="<?= $rootF ?>/template/datepicker/css/datepicker.css">
    <link rel="stylesheet" href="<?= $rootF ?>/template/rangedate/daterangepicker.css"/>
    
    <script src="https://code.jquery.com/jquery-latest.min.js"></script>
    
    <title>Accounting</title>
    <style type="text/css">
                .highlight{
                    background-color: yellow;color:black;font-size: 12px;
                }
		@media print {
			.hidden-print {
				display: none !important;
			}
			.article .text.short {
				height: 100%;
				overflow: auto;
			}
		}
    </style>
</head>
<body>
    <nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                 
            </button>
            <a class="navbar-brand" href="#"><?= strtoupper($userData[0]["username"]); ?></a>
        </div>
        <div id="navbar" class="navbar-collapse collapse" aria-expanded="false">
            <ul class="nav navbar-nav">
                <?= page::buildMenu($level[0]["user_level"]) ?>
                <li class="dropdown" id="menuDrop">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                       aria-expanded="false"><?= (defined('FILTER')) ? FILTER : 'FILTER'; ?> <span class="caret"></span></a>
                    <ul class="dropdown-menu" role="menu" style="text-align:center;">
                        <?php
                        $fData = page::buildFilter($level[0]["user_level"], $pageName);
                        for ($fi = 0; $fi < count($fData); $fi++) {
                            echo "<li>{$fData[$fi][1]}</li>";
                        }
                        ?>
                    </ul>
                    <?php if (max(page::filterLevel(3, $levelArray)) >= 33): ?>
                <li><a href="order.php"
                       target="_blank"><?= (defined('ADD_NEW_ORDER')) ? ADD_NEW_ORDER : 'ADD_NEW_ORDER'; ?></a></li>
            <?php endif; ?>
                </li>
            </ul>
        </div>
    </div>
</nav>
  
<hr style=" visibility: hidden">
<hr style=" visibility: hidden">
    
<div style="text-align: center">
    <form  method="POST" action="" >
        <input type="hidden" name="posting" value="filter">
            <table style="width: 100%" class="table table-bordered table-hover">
                   <tr>
                       <th>
                           <input type="text" name="target" placeholder="Նպատակ" class="form-control" value="<?=$filtertarget?>">
                       </th>
                       <th>
                           <input type="text" name="price" placeholder="Գին" value="<?=$filterprice?>" class="form-control" class="input">
                       </th>
                       <th>
                           <select name="actiontype" class="form-control" class="form-control">
                           
                               <?php 
                                if (isset($_POST["actiontype"]) &&  $_POST["actiontype"]  == 1 ) {
                                  ?>
                                   <option value="0">Ստատուս</option>
                                   <option value="1" selected>Մուտք</option>
                                   <option value="2">Ելք</option>
                               
                                  <?php
                                     } else  if (isset($_POST["actiontype"]) &&  $_POST["actiontype"]  == 2 ) {
                                  ?>
                                   <option value="0">Ստատուս</option>
                                   <option value="1">Մուտք</option>
                                   <option value="2" selected>Ելք</option>
                               
                                  <?php
                                } else  {
                                  ?>
                                        <option value="0">Ստատուս</option>
                                        <option value="1">Մուտք</option>
                                        <option value="2">Ելք</option>
                                  <?php
                                }
                               ?>
                               
                               
                               
                                
                           </select>   
                           
                        </th>
 
                       <th>
                           <select name="currencies" class="form-control">
                                <option value="0"  selected>ALL</option>
                                <?php
                                    
                                    foreach ($currencyListArray as $key => $value) {
                                        if (isset($_POST["currencies"]) && $_POST["currencies"] == $value) {
                                             ?>  
                                                <option value="<?=$value?>"  selected><?=$value?></option>
                                             <?php 
                                            
                                        } else {
                                            ?>  
                                                <option value="<?=$value?>"><?=$value?></option>
                                             <?php 
                                        }
                                    }
                                ?>
                           </select>  
                       </th>
                       <th>
                           <input  name="date" type="date" value="<?=$defaultDate?>" class="form-control">
                       </th>
                         <th>
                            <input type="submit" class="form-control"  value="Որոնել" >
                       </th>
                       
                      <th style="width:200px" >
                                <span id="radiobut1" class="form-control" style="width:150px;height: 50px; display: inline; background-color: greenyellow" onclick="setAction (1)"><input type="radio"   id="radio1" name="acvtiontype" checked onclick="setAction (1)">  Մուտք</span>
                                <span id="radiobut2" class="form-control" style="width: 150px;height: 50px; display: inline" onclick="setAction (2)"><input type="radio"  id="radio2"  name="acvtiontype" onclick="setAction (2)"> Ելք</span>
                       </th>
                       
                        <th style="width: 200px" id="usernameid">
                            <?php print_r($userData["username"]);?>
                        </th>
                   </tr>

             </table>
             
    </form>
    </div>
    
    <table id="data_tabel" style="width: 100%" class="table table-bordered table-hover">
        
        <tr>
            <th>
               #
            </th>
            <th>
                Նպատակ
            </th>
            <th>
                Քանակ
            </th>
             <th>
                Գումար
            </th>
            <th>
                Արտաժույթ
            </th>
            <th>
                Գործողություն 
            </th>
            <th>
                Ամսաթիվ
            </th>
        </tr>
        
        <?php 
        
            foreach ($arrayObjects  as $key => $value) {
        ?>
        
        <tr>
            <td attr="calckeys">
               <?=$key?>
            </td>
            <td>
                <?=$value["purpose"]?>
            </td>
            <td>
                <?=$value["quantity"]?>
            </td>
            <td attr="price_<?=$key?>">
                <?=$value["price"]?>
            </td>
            <td attr="currency_<?=$key?>">
                <?=$value["currency"]?>
            </td>
            <td>
                <?php
                    if ($value["actiontype"] == 1) {
                       echo "Մուտք";
                    } else if ($value["actiontype"] == 2) {
                       echo "Ելք";
                    }
                
                ?>
                
                
            </td>
            <td>
                <?=Accounting::getCorrectedDate($value["cdate"])?>
            </td>
        </tr>
        
        
        <?php
             }
        ?>
        
        
    </table>
        
<hr style=" visibility: hidden">
<hr style=" visibility: hidden">
<hr style=" visibility: hidden">
<hr style=" visibility: hidden">
    
        <div style="bottom:0; width: 100%">
            
                  <table style="width: 100%" class="table table-bordered table-hover">

                       <tr>
                           <td id="GBPTOTAL">
                               
                           </td>
                           <td id="EURTOTAL">
                              
                           </td >
                         
                           <td id="RUBTOTAL">
                              
                           </td>
                           <td id="AMDTOTAL">
                              
                           </td>
                           <td id="USDTOTAL">
                              
                           </td>
                           <td id="IRRTOTAL" >
                              
                           </td>
                       </tr>
                   </table>  
            
                   <table style="width: 100%" class="table table-bordered table-hover">

                       <tr>
                           <th style="width: 100px">
                               <input type="text" id="price" name="price" style=" height: 50px" class="form-control" placeholder="Գումար">
                           </th>
                           <th>
                               <input type="text" id="target" name="target" style=" height: 50px" class="form-control" placeholder="Նպատակ">
                           </th>
                           <th  style="width: 100px">
                              
                               <input type="text" id="quantity" name="quantity" style=" height: 50px" class="form-control" placeholder="Քանակ">
                           </th>

                           <th style="width: 100px">
                                
                                <select id="selectedcurrency" name="selectedcurrency" class="form-control" style=" height: 50px">
                                <?php
                                    foreach ($currencyListArray   as $key => $value) {
                                        ?>        
                                         <option value="<?=$value?>"><?=$value?></option>
                                        <?php
                                        
                                    }
                                ?>
                           </select>  
                           </th>
                           <th style="width: 100px">
                               <input type="submit" id="save" class="btn btn-lg btn-primary" style=" height: 50px" value="Պահպանել" onclick="post_new_value ()">
                           </th>
                       </tr>
                </table>              
        </div>    
 

    <script>
        
     function post_new_value () {
         
        var selectedActionType = 0; 
        
        if ($('#radio1:checked').val() == "on") {
                selectedActionType = 1;
        } 
        
        if ($('#radio2:checked').val() == "on") {
                selectedActionType = 2;
        } 
         
        var selectedcurrency =  $("#selectedcurrency").val();
        var target =  $("#target").val();
        var quantity =  $("#quantity").val();
        var price =  $("#price").val();
        
        var doPost = true;
        if (!jQuery.isNumeric(quantity )) {
            doPost  = false;
            $("#quantity").val("");
            $("#quantity").css({"background-color": 'red'});
        }  else {
             $("#quantity").css({"background-color": 'white'});
        }
        
        if (!jQuery.isNumeric(price )) {
            doPost  = false;
            $("#price").val("");
            $("#price").css({"background-color": 'red'});
        } else {
             $("#price").css({"background-color": 'white'});
            
        }
        
        if (target.length < 4) {
            doPost  = false;
            $("#target").val("");
            $("#target").css({"background-color": 'red'});
        } else {
            $("#target").css({"background-color": 'white'});
        } 
        
  
        if (doPost) {
             var post_object = {
                  posting: "insert", 
                  actiontype:  selectedActionType, 
                  selectedcurrency : selectedcurrency, 
                  target  :  target ,
                  quantity : quantity, 
                  price : price
              };


              $.post( "/account/accountant/data.php", post_object).done(function( data ) {
                  
                  try {
                      
                      if (parseInt(data) > 0) {
                         
                         
                              var row = '<tr>';
                              row += '<td>' + data + '</td>';
                           
                              row += '<td>' +  target + '</td>';
                              row += '<td>' + quantity + '</td>';
                              row += '<td attr="price">' + price + '</td>';
                              row += '<td attr="price">' + selectedcurrency + '</td>';
                                 
                              if (selectedActionType == 1) {
                                  row += '<td>Մուտք</td>';
                              } 
                              if (selectedActionType == 2) {
                                  row += '<td>Ելք</td>';
                              } 
                              
                              row += '<td>' + getCurrentDateAndTime ()  + '</td>';
                              row += '</tr>';
                              
                              $("#data_tabel").append(row);
                              $("#price").val("");
                              $("#price").css({"background-color": 'white'});
                              $("#target").val("");
                              $("#target").css({"background-color": 'white'});
                              $("#quantity").val("");
                              $("#quantity").css({"background-color": 'white'});
                              
                              calculatePrice ( parseFloat(price), selectedcurrency)
                               
                      } else {
                          alert ("DATA ARE NOT INSERTED");
                      }
                      
                  }catch(inserte) {
                       alert ("DATA ARE NOT INSERTED" + inserte);
                  }
                 
              });
        }
       
     }
    
    function getCurrentDateAndTime () {
        var monthNames = new Array("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");
        
        var currentdate = new Date();
        var result = currentdate.getDate() + "-"
                +  monthNames[currentdate.getMonth()+1] + "-" 
                + currentdate.getFullYear() 
                + " " + currentdate.getHours()+":" +  currentdate.getMinutes(); 
        return result;
    }
    
    
    function setAction (action1or2) {
            if (action1or2 == 1) {
                 $("#radiobut1").css({"background-color": 'greenyellow'});
                 $("#radio1").prop("checked", true);
                 
                 $("#radiobut2").css({"background-color": 'white'});
                 $("#radio2").prop("checked", false);
            }
             
            if (action1or2 == 2) {
                 $("#radiobut2").css({"background-color": 'greenyellow'});
                 $("#radio2").prop("checked", true);
                 
                 $("#radiobut1").css({"background-color": 'white'});
                 $("#radio1").prop("checked", false);
                 
            }
    }
    
    
     
   
    
    function calculatePrice (selprice, selcurrenct) {
        
        if (selprice > 0) {
           
            var priceinfo = $("#" + selcurrenct + "TOTAL").text();
            if (priceinfo.trim().length > 3) {
                  var res = priceinfo.split(" "); 
                  var pricefinal  =selprice + parseFloat(res[0].trim());
                  $("#" + selcurrenct + "TOTAL").text(pricefinal + " " +selcurrenct );
            } else {
                  $("#" + selcurrenct + "TOTAL").text(selprice + " " + selcurrenct );
            }
            
        } else {
            
             $("[attr='calckeys']" ).each(function( index ) {
                    var key =  $( this ).text();
                    var pricekey = "price_" + key.trim() ;
                    var currencykey = "currency_" + key.trim() ;

                    var price = parseFloat($("[attr='" + pricekey + "']").text().trim());
                    var currency = $("[attr='" + currencykey + "']").text().trim();

                    var priceinfo = $("#" + currency + "TOTAL").text();

                    if (priceinfo.trim().length > 3) {
                         var res = priceinfo.split(" "); 
                         var pricefinal  =price + parseFloat(res[0].trim());
                         $("#" + currency + "TOTAL").text(pricefinal + " " + currency );
                    } else {
                         $("#" + currency + "TOTAL").text(price + " " + currency );
                    }
            });
            
        }
        
       
        
    }
    
    
    $( document ).ready(function() {
            calculatePrice (0, null);
     });
    
    </script>
    
    
    
    
</body>

</html>

