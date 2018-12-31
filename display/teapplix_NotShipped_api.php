
<?php
set_time_limit(180);
include ('connection.php');

$service_url = 'https://api.teapplix.com/api2/OrderNotification?NotShipped=1';
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
$truncate_result = mysqli_query($link, "TRUNCATE `notshipped_orders` ");

if ($truncate_result)
	{
	updateOnshipedOrder($decoded2,$link);
	}
  else
	{
	//echo 'system_error';
	}

function updateOnshipedOrder($decoded2,$link)
	{
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
		//echo $decoded2[$key]['TxnId'] . "<br />";
		//echo $decoded2[$key]['StoreType'] . "<br />";
		//echo $decoded2[$key]['StoreKey'] . "<br />";
		//echo $decoded2[$key]['SellerID'] . "<br />";
		//echo $decoded2[$key]['PaymentStatus'] . "<br />";
		//echo $decoded2[$key]['OrderDetails']['ShipClass'] . "<br />";
		//echo $decoded2[$key]['OrderDetails']['PaymentDate'] . "<br />";
		//echo json_encode($decoded2[$key]['OrderItems']) . "<br />";

		// //echo addslashes(json_encode($ToJson))."<br />";

		//echo json_encode($decoded2[$key]['To']) . "<br />";

		// //echo addslashes(json_encode($OrderItems))."<br />";

		//echo "-------------------------------------------------------<br />";
		$result = mysqli_query($link, "SELECT * FROM notshipped_orders WHERE TxnId ='$TxnId' ");
		if (mysqli_num_rows($result) > 0)
			{
			//print "order already exist<br />";
			}
		  else
			{
			$resInsert = mysqli_query($link, "INSERT INTO notshipped_orders (TxnId,StoreType,StoreKey,SellerID,PaymentStatus,ShipClass,PaymentDate,OrderItems,ToJson ) VALUES ('$TxnId','$StoreType','$StoreKey','$SellerID','$PaymentStatus','$ShipClass','$PaymentDate','$OrderItems','$ToJson')"); // insert one row into new table
			if ($resInsert)
				{
				//print "Insert_orders--Insert :Success<br />";
				}
			  else
				{
				//print ("INSERT INTO notshipped_orders (TxnId,StoreType,StoreKey,SellerID,PaymentStatus,ShipClass,PaymentDate,OrderItems,ToJson ) VALUES ('$TxnId','$StoreType','$StoreKey','$SellerID','$PaymentStatus','$ShipClass','$PaymentDate','$OrderItems','$ToJson')") . "<br />";
				}
			}

		//echo "-------------------------------------------------------<br />";
		}
	}
	
	
	 $arr['status'] = 'noshiped_done';
	echo json_encode($arr)

?>
