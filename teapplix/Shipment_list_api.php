<?php
set_time_limit(180);
include ('connection.php');
$service_url = 'https://api.teapplix.com/api2/Shipment?ShipDateStart=2018/12/09&&ShipDateEnd=2018/12/11';
$curl = curl_init($service_url);
curl_setopt($curl, CURLOPT_HTTPHEADER, array(
'APIToken: ' . '99333-cfbb3-9f3d0-beca9-f674e-90320-6081'
));
curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
$curl_response = curl_exec($curl);
if ($curl_response === false) {
    $info = curl_getinfo($curl);
    curl_close($curl);
    die('error occured during curl exec. Additioanl info: ' . var_export($info));
}

curl_close($curl);
$decoded1 = json_decode($curl_response,true);
if (isset($decoded1->response->status) && $decoded1->response->status == 'ERROR') {
    die('error occured: ' . $decoded1->response->errormessage);
}

$decoded2=$decoded1['Items'];
 foreach($decoded2 as $key => $value) {
	 $TxnId=$decoded2[$key]['TxnId'];
	 $ShipDate=$decoded2[$key]['ShipDate'];
	 $ShipMethod=$decoded2[$key]['ShipMethod'];
	 $Provider=$decoded2[$key]['Provider'];
	 $Amount=$decoded2[$key]['PostageAmount']['Amount'];
	 $TrackingNumber=$decoded2[$key]['TrackingInfo']['TrackingNumber'];
	 $CarrierName=$decoded2[$key]['TrackingInfo']['CarrierName'];
	
	 
    echo $TxnId."<br>";
    echo $ShipDate ."<br>";
    echo $ShipMethod."<br>";
    echo $Provider ."<br>";
    echo $Amount ."<br>";
    echo $TrackingNumber ."<br>";
    echo $CarrierName ."<br>";
  
    echo "-------------------------------------------------------<br>";
	
	
	 $result = mysqli_query($link, "SELECT * FROM shipmentlist WHERE TxnId ='$TxnId' ");
            if (mysqli_num_rows($result) > 0) {
               
                    print "order already exist<br>";
               
            } else {
                $resInsert = mysqli_query($link, "INSERT INTO shipmentlist (TxnId,ShipDate,ShipMethod,Provider,Amount,TrackingNumber,CarrierName) VALUES ('$TxnId','$ShipDate','$ShipMethod','$Provider','$Amount','$TrackingNumber','$CarrierName')"); // insert one row into new table
                if ($resInsert) {
                    print "Insert_orders--Insert :Success<br>";
                } else {
                    print ("INSERT INTO shipmentlist (TxnId,ShipDate,ShipMethod,Provider,Amount,TrackingNumber,CarrierName) VALUES ('$TxnId','$ShipDate','$ShipMethod','$Provider','$Amount','$TrackingNumber','$CarrierName')")."<br>";
                }
            }
	echo "-------------------------------------------------------<br>";
	
	
  }


?>