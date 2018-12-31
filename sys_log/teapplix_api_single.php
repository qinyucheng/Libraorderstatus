<?php
set_time_limit(500);
include ('connection.php');
date_default_timezone_set("America/Chicago");
$startDate = date("Y/m/d", strtotime("-4 days"));
$endDate = date("Y/m/d");

$T_UpdateTime = date("Y/m/d h:i:s");
echo $startDate . '<br>';
echo $endDate . '<br>';
$sys_log = $T_UpdateTime . "\r\n";
sys_order($startDate, $endDate, $T_UpdateTime, $link, $sys_log);
function sys_order($startDate, $endDate, $T_UpdateTime, $link, &$sys_log) {
    $service_url = "https://api.teapplix.com/api2/OrderNotification?TxnId=112-6336239-7570643";
    echo $service_url . '<br>';
    $curl = curl_init($service_url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('APIToken: ' . '99333-cfbb3-9f3d0-beca9-f674e-90320-6081'));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    $curl_response = curl_exec($curl);
    if ($curl_response === false) {
        $info = curl_getinfo($curl);
        curl_close($curl);
        die('error occured during curl exec. Additioanl info: ' . var_export($info));
    }
    curl_close($curl);
    $decoded1 = json_decode($curl_response, true);
    if (isset($decoded1->response->status) && $decoded1->response->status == 'ERROR') {
        die('error occured: ' . $decoded1->response->errormessage);
    }
    $decoded2 = $decoded1['Orders'];
	print_r($decoded2);
    foreach ($decoded2 as $key => $value) {
        $TxnId = $decoded2[$key]['TxnId'];
        $StoreType = $decoded2[$key]['StoreType'];
        $StoreKey = $decoded2[$key]['StoreKey'];
		//$isHasRMACode=property_exists($decoded2[$key], 'RMACode');
		if(isset( $decoded2[$key]['RMACode']))
		{
			
			$RMACode = $decoded2[$key]['RMACode'];
		}
		else
		{
			$RMACode ='';
			
		}
		
        $SellerID = $decoded2[$key]['SellerID'];
        $PaymentStatus = $decoded2[$key]['PaymentStatus'];
        $ShipClass = $decoded2[$key]['OrderDetails']['ShipClass'];
        $PaymentDate = $decoded2[$key]['OrderDetails']['PaymentDate'];
        $OrderDetails_Memo = $decoded2[$key]['OrderDetails']['Memo'];
        $OrderItems = addslashes(json_encode($decoded2[$key]['OrderItems']));
        $ToJson = addslashes(json_encode($decoded2[$key]['To']));
        echo $decoded2[$key]['TxnId'] . "<br />";
        echo $decoded2[$key]['StoreType'] . "<br />";
        echo $RMACode."<br />";
        echo $decoded2[$key]['StoreKey'] . "<br />";
        echo $decoded2[$key]['SellerID'] . "<br />";
        echo $decoded2[$key]['PaymentStatus'] . "<br />";
        echo $decoded2[$key]['OrderDetails']['ShipClass'] . "<br />";
        echo $decoded2[$key]['OrderDetails']['PaymentDate'] . "<br />";
        echo $decoded2[$key]['OrderDetails']['Memo'] . "MEMO<br />";
        echo json_encode($decoded2[$key]['OrderItems']) . "<br />";
        // echo addslashes(json_encode($ToJson))."<br />";
        echo json_encode($decoded2[$key]['To']) . "<br />";
        // echo addslashes(json_encode($OrderItems))."<br />";
        echo "-------------------------------------------------------<br />";
		
        $result = mysqli_query($link, "SELECT * FROM orders WHERE TxnId ='$TxnId' ");
        if (mysqli_num_rows($result) > 0) {
            $resUpdate = mysqli_query($link, "update orders set RMACode ='$RMACode', Memo='$OrderDetails_Memo' WHERE TxnId ='$TxnId' "); // insert one row into new table
            if ($resUpdate) {
                //print "resUpdate--Insert :Success<br />";
                
            } else {
                //print ("INSERT INTO orders (TxnId,StoreType,StoreKey,SellerID,PaymentStatus,ShipClass,PaymentDate,OrderItems,ToJson ) VALUES ('$TxnId','$StoreType','$StoreKey','$SellerID','$PaymentStatus','$ShipClass','$PaymentDate','$OrderItems','$ToJson')") . "<br />";
                
            }
            
        } else {
            $resInsert = mysqli_query($link, "INSERT INTO orders (TxnId,StoreType,StoreKey,SellerID,PaymentStatus,ShipClass,PaymentDate,OrderItems,ToJson,T_UpdateTime,OrderStatus,RMACode,Memo ) VALUES ('$TxnId','$StoreType','$StoreKey','$SellerID','$PaymentStatus','$ShipClass','$PaymentDate','$OrderItems','$ToJson','$T_UpdateTime',0,'$RMACode','$OrderDetails_Memo')"); // insert one row into new table
            if ($resInsert) {
                //print "Insert_orders--Insert :Success<br />";
                
            } else {
                //print ("INSERT INTO orders (TxnId,StoreType,StoreKey,SellerID,PaymentStatus,ShipClass,PaymentDate,OrderItems,ToJson ) VALUES ('$TxnId','$StoreType','$StoreKey','$SellerID','$PaymentStatus','$ShipClass','$PaymentDate','$OrderItems','$ToJson')") . "<br />";
                
            }
        }
        echo "-------------------------------------------------------<br />";
    }
    $sys_log.= 'sys_order_Done';
}
//sys_trackingNumber($startDate, $endDate, $T_UpdateTime,$sys_log ,$link);
function sys_trackingNumber($startDate, $endDate, $T_UpdateTime, &$sys_log, $link) {
    $service_url = "https://api.teapplix.com/api2/Shipment?ShipDateStart=$startDate&ShipDateEnd=$endDate";
    $curl = curl_init($service_url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('APIToken: ' . '99333-cfbb3-9f3d0-beca9-f674e-90320-6081'));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    $curl_response = curl_exec($curl);
    if ($curl_response === false) {
        $info = curl_getinfo($curl);
        curl_close($curl);
        die('error occured during curl exec. Additioanl info: ' . var_export($info));
    }
    curl_close($curl);
    $decoded1 = json_decode($curl_response, true);
    if (isset($decoded1->response->status) && $decoded1->response->status == 'ERROR') {
        die('error occured: ' . $decoded1->response->errormessage);
    }
    $decoded2 = $decoded1['Items'];
    foreach ($decoded2 as $key => $value) {
        $TxnId = $decoded2[$key]['TxnId'];
        $ShipDate = $decoded2[$key]['ShipDate'];
        $ShipMethod = $decoded2[$key]['ShipMethod'];
        $Provider = $decoded2[$key]['Provider'];
        $Amount = $decoded2[$key]['PostageAmount']['Amount'];
        $TrackingNumber = $decoded2[$key]['TrackingInfo']['TrackingNumber'];
        $CarrierName = $decoded2[$key]['TrackingInfo']['CarrierName'];
        echo $TxnId . "<br />";
        echo $ShipDate . "<br />";
        echo $ShipMethod . "<br />";
        echo $Provider . "<br />";
        echo $Amount . "<br />";
        echo $TrackingNumber . "<br />";
        echo $CarrierName . "<br />";
        echo "-------------------------------------------------------<br />";
        $result = mysqli_query($link, "SELECT * FROM orders WHERE TxnId ='$TxnId' ");
        if (mysqli_num_rows($result) > 0) {
            $resInsert = mysqli_query($link, "update orders set TrackingNumber='$TrackingNumber', CarrierName='$CarrierName', ShipDate='$ShipDate', ShipMethod='$ShipMethod', Provider='$Provider',T_UpdateTime='$T_UpdateTime' WHERE TxnId ='$TxnId' "); // insert one row into new table
            if ($resInsert) {
                //print "update_trackingNumber--update_trackingNumber :Success<br />";
                
            } else {
                //print ("update orders set TrackingNumber='$TrackingNumber', CarrierName='$CarrierName', ShipDate='$ShipDate', ShipMethod='$ShipMethod', Provider='$Provider' WHERE TxnId ='$TxnId'". "<br />");
                
            }
        } else {
            //print "no this order<br />";
            
        }
        echo "-------------------------------------------------------<br />";
    }
    $sys_log.= 'sys_trackingNumber_Done';
}
// $arr['status'] = 'shiped_done';
//echo json_encode($arr)
//sys_updateNoshipedOrder($T_UpdateTime, $sys_log, $link);
function sys_updateNoshipedOrder($T_UpdateTime, &$sys_log, $link) {
    $service_url = 'https://api.teapplix.com/api2/OrderNotification?NotShipped=1';
    $curl = curl_init($service_url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('APIToken: ' . '99333-cfbb3-9f3d0-beca9-f674e-90320-6081'));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    $curl_response = curl_exec($curl);
    if ($curl_response === false) {
        $info = curl_getinfo($curl);
        curl_close($curl);
        die('error occured during curl exec. Additioanl info: ' . var_export($info));
    }
    curl_close($curl);
    $decoded1 = json_decode($curl_response, true);
    if (isset($decoded1->response->status) && $decoded1->response->status == 'ERROR') {
        die('error occured: ' . $decoded1->response->errormessage);
    }
    $decoded2 = $decoded1['Orders'];
    $truncate_result = mysqli_query($link, "TRUNCATE `notshipped_orders` ");
    if ($truncate_result) {
        updateNoshipedOrder($decoded2,$T_UpdateTime, $sys_log, $link);
    } else {
        echo 'system_error';
    }
}
function updateNoshipedOrder($decoded2,$T_UpdateTime, &$sys_log, $link) {
    foreach ($decoded2 as $key => $value) {
        $TxnId = $decoded2[$key]['TxnId'];
        $StoreType = $decoded2[$key]['StoreType'];
        $StoreKey = $decoded2[$key]['StoreKey'];
        $SellerID = $decoded2[$key]['SellerID'];
        $PaymentStatus = $decoded2[$key]['PaymentStatus'];
        $ShipClass = $decoded2[$key]['OrderDetails']['ShipClass'];
        $PaymentDate = $decoded2[$key]['OrderDetails']['PaymentDate'];
        $OrderItems = addslashes(json_encode($decoded2[$key]['OrderItems']));
        $ToJson = addslashes(json_encode($decoded2[$key]['To']));
        echo $decoded2[$key]['TxnId'] . "<br />";
        echo $decoded2[$key]['StoreType'] . "<br />";
        echo $decoded2[$key]['StoreKey'] . "<br />";
        echo $decoded2[$key]['SellerID'] . "<br />";
        echo $decoded2[$key]['PaymentStatus'] . "<br />";
        echo $decoded2[$key]['OrderDetails']['ShipClass'] . "<br />";
        echo $decoded2[$key]['OrderDetails']['PaymentDate'] . "<br />";
        echo json_encode($decoded2[$key]['OrderItems']) . "<br />";
        // echo addslashes(json_encode($ToJson))."<br />";
        echo json_encode($decoded2[$key]['To']) . "<br />";
        // echo addslashes(json_encode($OrderItems))."<br />";
        echo "-------------------------------------------------------<br />";
        $result = mysqli_query($link, "SELECT * FROM notshipped_orders WHERE TxnId ='$TxnId' ");
        if (mysqli_num_rows($result) > 0) {
            //print "order already exist<br />";
            
        } else {
            $resInsert = mysqli_query($link, "INSERT INTO notshipped_orders (TxnId,StoreType,StoreKey,SellerID,PaymentStatus,ShipClass,PaymentDate,OrderItems,ToJson,T_UpdateTime ) VALUES ('$TxnId','$StoreType','$StoreKey','$SellerID','$PaymentStatus','$ShipClass','$PaymentDate','$OrderItems','$ToJson','$T_UpdateTime')"); // insert one row into new table
            if ($resInsert) {
                //print "Insert_orders--Insert :Success<br />";
                
            } else {
                //print ("INSERT INTO notshipped_orders (TxnId,StoreType,StoreKey,SellerID,PaymentStatus,ShipClass,PaymentDate,OrderItems,ToJson ) VALUES ('$TxnId','$StoreType','$StoreKey','$SellerID','$PaymentStatus','$ShipClass','$PaymentDate','$OrderItems','$ToJson')") . "<br />";
                
            }
        }
        echo "-------------------------------------------------------<br />";
    }
    $sys_log.= 'NoshipedOrder_done';
}
mysqli_close($link);
$t = time();
$filePath = "C:\\xampp\\htdocs\\Libraorderstatus\sys_log\\logs\\$t._teapplix.log";
file_put_contents($filePath, $sys_log);
?>
