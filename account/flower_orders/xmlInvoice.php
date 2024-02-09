<?php
    
    $servername = "62.109.14.103";
    $username = "admin_rgsystem";
    $password = "uniflora_rg_sysRG123$";
    $dbname = "admin_rgsystem";
    // $servername = "localhost";
    // $username = "root";
    // $password = "";
    // $dbname = "admin_rg_system";
    $conn = new mysqli($servername, $username, $password, $dbname);
    mysqli_set_charset($conn,"utf8");

    date_default_timezone_set('Asia/Yerevan');
    // log downloading
    $sql = "INSERT INTO order_xml_download (order_id,downloaded_datetime) VALUES ('" . $_GET['order_id'] . "','" . date("Y-m-d H:i:s") . "')";
    $conn->query($sql);

    // Get Order Info - rg_orders
    $sql = "SELECT * FROM rg_orders where id = '" . $_GET['order_id'] . "'";
    $result_row = $conn->query($sql);
    $rg_order = [];
    if ($result_row->num_rows > 0) {
        while($row = $result_row->fetch_assoc()) {
            $rg_order[] = $row;
        }
    }
    $rg_order = $rg_order[0];

    // Get Driver delivery Subregion Id - delivery_subregion
    $cityYerevanIds = ['1','2','3','4','5','6','7','8','9','10','11','12'];
    $sql = "SELECT * FROM delivery_subregion where code = '" . $rg_order['receiver_subregion'] . "'";
    $result_row = $conn->query($sql);
    $delivery_subregion = [];
    if ($result_row->num_rows > 0) {
        while($row = $result_row->fetch_assoc()) {
            $delivery_subregion[] = $row;
        }
    }

    $erevanExist = '';
    if(in_array($delivery_subregion[0]['id'], $cityYerevanIds)){
        $erevanExist = 'Երևան,';
    }
    $floorExist = ' ';
    if($rg_order['receiver_floor'] != ''){
        $floorExist = ' բն ' . $rg_order['receiver_floor'];
    }

    // Get Driver Car Number - delivery_drivers
    $sql = "SELECT * FROM delivery_drivers where id = '" . $rg_order['delivery_type'] . "'";
    $result_row = $conn->query($sql);
    $car_number = [];
    if ($result_row->num_rows > 0) {
        while($row = $result_row->fetch_assoc()) {
            $car_number[] = $row;
        }
    }

    // Get Country Info - countries
    $sql = "SELECT * FROM countries  WHERE id = '" . $rg_order['sender_country'] . "'";
    $result_row = $conn->query($sql);
    $result_country = [];
    if ($result_row->num_rows > 0) {
        while($row = $result_row->fetch_assoc()) {
            $result_country[] = $row;
        }
    }    
    // Get Delivery Subregion - delivery_subregion
    $sql = "SELECT * FROM delivery_subregion WHERE code='" . $rg_order['receiver_subregion'] . "'";
    $result_row = $conn->query($sql);
    $result_delivery_subregion = [];
    if ($result_row->num_rows > 0) {
        while($row = $result_row->fetch_assoc()) {
            $result_delivery_subregion[] = $row;
        }
    }
    $delyvery_subregion_by = ' ';
    if($result_delivery_subregion[0]['name'] != ''){
        $delyvery_subregion_by = $result_delivery_subregion[0]['name'] . ',';
    }
    $receiver_address_by = ' ';
    if($rg_order['receiver_address'] != ''){
        $receiver_address_by = $rg_order['receiver_address'] . ',';
    }
    // Get Delivery Addres - delivery_street
    $sql = "SELECT * FROM delivery_street WHERE code = '" . $rg_order['receiver_street'] . "'";

    $result_row = $conn->query($sql);
    $result_delivery_address = [];
    if ($result_row->num_rows > 0) {
        while($row = $result_row->fetch_assoc()) {
            $result_delivery_address[] = $row;
        }
    }

    $desc_tax_accounts = [];
    if ($file = fopen("../../desc_tax_account_2020.txt", "r")) {
        while(!feof($file)) {
            $line = fgets($file);
            $info = explode('|', $line);
            if(count($info) > 1){
                $desc_tax_accounts[] = $info[0];
            }
        }
        fclose($file);
    }
    $delivery_prices_2020 = [];
    if ($file = fopen("../../delivery_prices_2023.txt", "r")) {
        while(!feof($file)) {
            $line = fgets($file);
            $info = explode('|', $line);
            if(count($info) > 1){
                $delivery_prices_2020[] = $info[0];
            }
        }
        fclose($file);
    }


    // Get added products
    $sql = "SELECT * FROM order_tax_info where rg_order_id = '" . $_GET['order_id'] . "'";
    $result_row = $conn->query($sql);
    $textTagGood = '';
    $total_price = 0;
    if ($result_row->num_rows > 0) {
        while($row = $result_row->fetch_assoc()) {
            $total_price+=$row['price_amd'];
            $quantity_add = $row['quantity'];
            if($quantity_add == 0){
                $quantity_add = 1;
            }
            $textTagGood.='<Good>';
            $textTagGood.='<Description>' . $desc_tax_accounts[$row['tax_account_id']-1]  . '</Description>';
            $textTagGood.='<Unit>հատ</Unit>';
            $textTagGood.='<Amount>' . $quantity_add . '</Amount>';
            if($row['price_amd'] == 0){
                $textTagGood.='<PricePerUnit>0</PricePerUnit>';
            }
            else{
                $number = number_format((float)$row['price_amd'] / $quantity_add, 2, '.', '');
                $textTagGood.='<PricePerUnit>' . $number . '</PricePerUnit>';
            }
            $textTagGood.='<Price>' . $row['price_amd'] .  '</Price>';
            $textTagGood.='<TotalPrice>' . $row['price_amd'] .  '</TotalPrice>';
            $textTagGood.='</Good>';
        }
    }
    // Get Upload products
    $sql = "SELECT * FROM delivery_images where rg_order_id = '" . $_GET['order_id'] . "'";
    $result_row = $conn->query($sql);
    $textTagGoodUpload = '';
    if ($result_row->num_rows > 0) {
        while($row = $result_row->fetch_assoc()) {
            $quantity_upload = $row['tax_quantity'];
            if($quantity_upload == 0){
                $quantity_upload = 1;
            }
            $total_price+=$row['tax_price_amd'];
            $textTagGoodUpload.='<Good>';
            $textTagGoodUpload.='<Description>' . $desc_tax_accounts[$row['tax_account_id']-1]  . '</Description>';
            $textTagGoodUpload.='<Unit>հատ</Unit>';
            $textTagGoodUpload.='<Amount>' . $quantity_upload . '</Amount>';
            if($row['tax_price_amd'] == 0){
                $textTagGoodUpload.='<PricePerUnit>0</PricePerUnit>';
            }
            else{
                $number = number_format((float)$row['tax_price_amd'] / $quantity_upload, 2, '.', '');
                $textTagGoodUpload.='<PricePerUnit>' . $number . '</PricePerUnit>';
            }
            $textTagGoodUpload.='<Price>' . $row['tax_price_amd'] .  '</Price>';
            $textTagGoodUpload.='<TotalPrice>' . $row['tax_price_amd'] .  '</TotalPrice>';
            $textTagGoodUpload.='</Good>';
        }
    }


    // Get Postcard and delivery price Info - tax_numbers_of_check
    $sql = "SELECT * FROM tax_numbers_of_check where order_id = '" . $_GET['order_id'] . "'";
    $result_row = $conn->query($sql);
    $tax_numbers_of_check = [];
    if ($result_row->num_rows > 0) {
        while($row = $result_row->fetch_assoc()) {
            $tax_numbers_of_check[] = $row;
        }
    }
    $tax_numbers_of_check = $tax_numbers_of_check[0];
    $hvhh_tax_sql = '';
    if($tax_numbers_of_check['hvhh_tax'] != ''){
        $hvhh_tax_sql.='<BankAccount><BankName>Ամերիաբանկ</BankName><BankAccountNumber>1570020585900100</BankAccountNumber></BankAccount>';
    }
    $postcard_Good = '';
    if($tax_numbers_of_check['postcard_amd_price'] != ''){
        $total_price+=$tax_numbers_of_check['postcard_amd_price'];
        $postcard_Good = "<Good><Description>Բացիկ</Description><Unit>հատ</Unit><Amount>1</Amount><PricePerUnit>" . $tax_numbers_of_check['postcard_amd_price'] . "</PricePerUnit><Price>" . $tax_numbers_of_check['postcard_amd_price'] . "</Price><TotalPrice>" . $tax_numbers_of_check['postcard_amd_price'] . "</TotalPrice></Good>";
    }
    $delivery_other_price_Good = '';
    if($tax_numbers_of_check['delivery_other_price'] != ''){
        $total_price+=$tax_numbers_of_check['delivery_other_price'];
        $delivery_other_price_Good = "<Good><Description>Առաքում</Description><Unit>հատ</Unit><Amount>1</Amount><PricePerUnit>" . $tax_numbers_of_check['delivery_other_price'] . "</PricePerUnit><Price>" . $tax_numbers_of_check['delivery_other_price'] . "</Price><TotalPrice>" . $tax_numbers_of_check['delivery_other_price'] . "</TotalPrice></Good>";
    }
    $delivery_price_Good = '';
    if($tax_numbers_of_check['delivery_static_price'] != 0){
        $delivery_static_price = explode('դր -',$delivery_prices_2020[$tax_numbers_of_check['delivery_static_price'] -1]);
        $delivery_static_price = preg_replace("/[^0-9]/", '', $delivery_static_price[0]);
        $total_price+= $delivery_static_price;
        $delivery_price_Good = "<Good><Description>Առաքում</Description><Unit>հատ</Unit><Amount>1</Amount><PricePerUnit>" . $delivery_static_price . "</PricePerUnit><Price>" . $delivery_static_price . "</Price><TotalPrice>" . $delivery_static_price . "</TotalPrice></Good>";
    }
    $delivery_address = '';
    if($rg_order['receiver_subregion'] == 'chchstvac_hasce'){
        $delivery_address = '';
    }
    else{
        $delivery_address = $erevanExist  . " " . $delyvery_subregion_by  . ' ' . $result_delivery_address[0]['name'] . " " . $receiver_address_by . $floorExist;
    }
    if(substr($delivery_address, -2) == ', '){
        $delivery_address = substr($delivery_address, 0, -2);
    }
    $date = new DateTime($rg_order['delivery_date']);
    $now = new DateTime(date('Y-m-d'));
    $delivery_date = $rg_order['delivery_date'];
    if($date < $now) {
        $delivery_date = date('Y-m-d');
    }
    $sasBuyerInfo = '';
     // for sas
    if($rg_order['sell_point'] == '45'){
        $sasBuyerInfo = '<BuyerInfo>
            <VATNumber>02538542/1</VATNumber>
            <Taxpayer>
                <TIN>02538542</TIN>
                <Name>«ՍԱՍ-ԳՐՈՒՊ» Սահմանափակ պատասխանատվությամբ ընկերություն (ՍՊԸ)</Name>
                <Address>ԵՐԵՎԱՆ ԿԵՆՏՐՈՆ ԿԵՆՏՐՈՆ ԹԱՂԱՄԱՍ ՄԱՇՏՈՑԻ Պ. 18</Address>
                <TinNotRequired>false</TinNotRequired>
            </Taxpayer>
            <DeliveryMethod>Ինքնաառաքում</DeliveryMethod>
            <DeliveryLocation>' . $delivery_address .  '</DeliveryLocation>
        </BuyerInfo>';
    }
    else if($rg_order['sell_point'] == '48'){
        // for parma
        $sasBuyerInfo = '<BuyerInfo>
            <VATNumber>00029448/1</VATNumber>
            <Taxpayer>
                <TIN>00029448</TIN>
                <Name>«ԱՆԴԱԿՈ» Սահմանափակ պատասխանատվությամբ ընկերություն (ՍՊԸ)</Name>
                <Address>ԵՐԵՎԱՆ ԱՐԱԲԿԻՐ ԱՐԱԲԿԻՐ ԹԱՂԱՄԱՍ ԿԻԵՎՅԱՆ 22 43</Address>
                <TinNotRequired>false</TinNotRequired>
            </Taxpayer>
            <DeliveryMethod>Ինքնաառաքում</DeliveryMethod>
            <DeliveryLocation>' . $delivery_address .  '</DeliveryLocation>
        </BuyerInfo>';
    }
    else{
        $sasBuyerInfo = '<BuyerInfo>
            <Taxpayer>
                <Name>' . $rg_order['sender_name'] . '</Name>
                <Address>' . $result_country[0]['name_am'] . '</Address>
                <TinNotRequired>true</TinNotRequired>
            </Taxpayer>
            <DeliveryMethod>' . $car_number[0]['car_number'] . '</DeliveryMethod>
            <DeliveryLocation>' . $delivery_address .  '</DeliveryLocation>
        </BuyerInfo>';
    }
    $conn->query($sql);
    $conn->close();

    header('Content-type: text/xml');
    header('Content-Disposition: attachment; filename="' . $_GET['order_id'] . ' - ' . $total_price . ' dram".xml');

    echo '<ExportedAccDocData xmlns="http://www.taxservice.am/tp3/invoice/definitions">
    <AccountingDocument Version="1.0">
        <Type>1</Type>
        <GeneralInfo>
            <EcrReceipt></EcrReceipt>
            <AdjustmentDiffFlag>-1</AdjustmentDiffFlag>
            <DeliveryDate>' . $delivery_date . '+04:00</DeliveryDate>
            <Procedure>1</Procedure>
            <DealInfo/>
            <AdditionalData></AdditionalData>
        </GeneralInfo>
        <SupplierInfo>
            <VATNumber></VATNumber>
            <Taxpayer>
                <TIN>02252505</TIN>
                <Name>«ՌԵԳԱՐԴ ԳՐՈՒՊ» Սահմանափակ պատասխանատվությամբ ընկերություն (ՍՊԸ)</Name>
                <Address>ԱՐԱԳԱԾՈՏՆ ԱՇՏԱՐԱԿ ԱՇՏԱՐԱԿ 1փ 4</Address>
                ' . $hvhh_tax_sql . '
            </Taxpayer>
            <SupplyLocation>Երվանդ Քոչար 23/2</SupplyLocation>
        </SupplierInfo>
        ' . $sasBuyerInfo . '
        <GoodsInfo>
            ' . $textTagGood . '
            ' . $textTagGoodUpload . '
            ' . $postcard_Good . '
            ' . $delivery_price_Good . '
            ' . $delivery_other_price_Good . '
            <Total>
                <TotalPrice>' . $total_price . '</TotalPrice>
            </Total>
        </GoodsInfo>
    </AccountingDocument>
</ExportedAccDocData>';
    exit();
?>
