
<?php
set_time_limit(300);
include ('connection.php');
date_default_timezone_set("America/Chicago");
$startDate = date("Y/m/d", strtotime("-2 days"));
$endDate = date("Y/m/d");

echo $startDate.'<br>';
echo $endDate.'<br>';
sys_order($startDate,$endDate,$link);
function sys_order($startDate,$endDate,$link)
	{
	$service_url = "https://api.teapplix.com/api2/OrderNotification?ShipDateStart=$startDate&ShipDateEnd=$endDate";
	echo $service_url.'<br>';
	$curl = curl_init($service_url);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array(
		'APIToken: ' . '99333-cfbb3-9f3d0-beca9-f674e-90320-6081'
	));
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
	$curl_response = curl_exec($curl);
	if ($curl_response === false)
		{
		$info = curl_getinfo($curl);
		curl_close($curl);
		die('error occured during curl exec. Additioanl info: ' . var_export($info));
		}

	curl_close($curl);
	$decoded1 = json_decode($curl_response, true);
	if (isset($decoded1->response->status) && $decoded1->response->status == 'ERROR')
		{
		die('error occured: ' . $decoded1->response->errormessage);
		}

	$decoded2 = $decoded1['Orders'];
	foreach($decoded2 as $key => $value)
		{
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
		$result = mysqli_query($link, "SELECT * FROM orders WHERE TxnId ='$TxnId' ");
		if (mysqli_num_rows($result) > 0)
			{
			print "order already exist<br />";
			}
		  else
			{
			$resInsert = mysqli_query($link, "INSERT INTO orders (TxnId,StoreType,StoreKey,SellerID,PaymentStatus,ShipClass,PaymentDate,OrderItems,ToJson ) VALUES ('$TxnId','$StoreType','$StoreKey','$SellerID','$PaymentStatus','$ShipClass','$PaymentDate','$OrderItems','$ToJson')"); // insert one row into new table
			if ($resInsert)
				{
				print "Insert_orders--Insert :Success<br />";
				}
			  else
				{
				print ("INSERT INTO orders (TxnId,StoreType,StoreKey,SellerID,PaymentStatus,ShipClass,PaymentDate,OrderItems,ToJson ) VALUES ('$TxnId','$StoreType','$StoreKey','$SellerID','$PaymentStatus','$ShipClass','$PaymentDate','$OrderItems','$ToJson')") . "<br />";
				}
			}

		echo "-------------------------------------------------------<br />";
		}
	}

sys_trackingNumber($startDate,$endDate,$link);

function sys_trackingNumber($startDate,$endDate,$link)
	{
	$service_url = "https://api.teapplix.com/api2/Shipment?ShipDateStart=$startDate&ShipDateEnd=$endDate";
	$curl = curl_init($service_url);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array(
		'APIToken: ' . '99333-cfbb3-9f3d0-beca9-f674e-90320-6081'
	));
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
	$curl_response = curl_exec($curl);
	if ($curl_response === false)
		{
		$info = curl_getinfo($curl);
		curl_close($curl);
		die('error occured during curl exec. Additioanl info: ' . var_export($info));
		}

	curl_close($curl);
	$decoded1 = json_decode($curl_response, true);
	if (isset($decoded1->response->status) && $decoded1->response->status == 'ERROR')
		{
		die('error occured: ' . $decoded1->response->errormessage);
		}

	$decoded2 = $decoded1['Items'];
	foreach($decoded2 as $key => $value)
		{
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
		if (mysqli_num_rows($result) > 0)
			{
			$resInsert = mysqli_query($link, "update orders set TrackingNumber='$TrackingNumber', CarrierName='$CarrierName', ShipDate='$ShipDate', ShipMethod='$ShipMethod', Provider='$Provider' WHERE TxnId ='$TxnId' "); // insert one row into new table
			if ($resInsert)
				{
				print "update_trackingNumber--update_trackingNumber :Success<br />";
				}
			  else
				{
				print ("update orders set TrackingNumber='$TrackingNumber', CarrierName='$CarrierName', ShipDate='$ShipDate', ShipMethod='$ShipMethod', Provider='$Provider' WHERE TxnId ='$TxnId'". "<br />");
				}
			}
		  else
			{
				print "no this order<br />";
			}

		echo "-------------------------------------------------------<br />";
		}
	}
	
?>
