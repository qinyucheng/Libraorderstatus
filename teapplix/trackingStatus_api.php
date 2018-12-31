<?php
set_time_limit(1000);
include ('connection.php');

sys_trackingStatus($link);
function sys_trackingStatus($link)
	{
		//$result = mysqli_query($link, "SELECT * FROM orders WHERE TrackingStatus <>'Delivered' and CarrierName='USPS' ");
		$result = mysqli_query($link, "SELECT * FROM orders WHERE TrackingStatus ='' and CarrierName='USPS' ");
		while($row=mysqli_fetch_object($result))
		{
			$CarrierName=$row->CarrierName;
			$TrackingNumber=$row->TrackingNumber;
			
			$TxnId=$row->TxnId;
			echo $TxnId.'<br>';
		
			curl($CarrierName,$TrackingNumber,$TxnId,$link);
			sleep(0.25);
		}
		
	}
	

function curl($CarrierName,$TrackingNumber,$TxnId,$link)
{
		$service_url = "https://api.teapplix.com/api2/Track?Carrier=".$CarrierName."&TrackingNumber=".$TrackingNumber;

		$curl = curl_init($service_url);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			'APIToken: ' . '99333-cfbb3-9f3d0-beca9-f674e-90320-6081'
		));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);

		$curl_response = curl_exec($curl);
		if (!curl_errno($curl)) {
			switch ($http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE)) {
				case 200: # OK
					getStatus1($curl_response,$TxnId,$link);
					break;
				case 400: # OK
					getStatus2($http_code);
					break;
				default:
					getStatus3($http_code);
					echo 'Unexpected HTTP code: ', $http_code, "\n";
			}
		}

		curl_close($curl);
	
	
}

function getStatus1($curl_response,$TxnId,$link)
{
    $decoded1 = json_decode($curl_response, true);
	$IsDelivered = $decoded1['IsDelivered'];
	if($IsDelivered=='')
	{
		$IsDelivered=2;
		
	}
	if (array_key_exists("EstimatedDeliveryDate",$decoded1))
	  {
		$EstimatedDeliveryDate = $decoded1['EstimatedDeliveryDate'];
	  }
	else
	  {
		$IsDelivered=0;
	  }
	
	
	
	if($IsDelivered==2)
	{
		$TrackingStatus='In-Transit';
		echo 'In-Transit';
	}
	
	else if($IsDelivered==0)
	{
		$TrackingStatus='Not shipped yet';
		echo 'Not shipped yet';
	}
	else if($IsDelivered==1)
	{
		$TrackingStatus='Delivered';
		echo 'Delivered';
	}
	updateStatus($TrackingStatus,$TxnId,$link);
    
}
function getStatus2($curl_response)
{
    
    print $curl_response;
}
function getStatus3($curl_response)
{
    
    print $curl_response;
}

function updateStatus($TrackingStatus,$TxnId,$link)
{
	
	$resInsert = mysqli_query($link, "update orders set TrackingStatus='$TrackingStatus'  WHERE TxnId ='$TxnId' "); // insert one row into new table
	if ($resInsert)
		{
		print "TrackingStatus--TrackingStatus :Success<br />";
		}
	  else
		{
		print ("update orders set TrackingStatus='$TrackingStatus',  WHERE TxnId ='$TxnId'". "<br />");
		}		
}



?>