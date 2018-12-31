<?php
// Copyright 2009, FedEx Corporation. All rights reserved.
// Version 6.0.0
set_time_limit(1000);
require_once('fedex-common.php');
include ('connection.php');
date_default_timezone_set("America/Chicago");
$ShipDate = date("Y-m-d", strtotime("-3 days"));
$updateTime = date("Y/m/d h:i:s");

sys_trackingStatus($updateTime,$ShipDate,$link);

function sys_trackingStatus($updateTime,$ShipDate,$link)
	{
		$count=0;
		//$result = mysqli_query($link, "SELECT * FROM orders WHERE TrackingStatus <>'Delivered' and TrackingStatus ='' and CarrierName='fedex' and TrackingNumber<>'' ");
		$result = mysqli_query($link, "SELECT * FROM orders WHERE TrackingStatus <>'Delivered' and CarrierName='fedex' and TrackingNumber<>'' and ShipDate>='$ShipDate'  ");
		while($row=mysqli_fetch_object($result))
		{
			$CarrierName=$row->CarrierName;
			$TrackingNumber=$row->TrackingNumber;
			
			$TxnId=$row->TxnId;
			echo $TxnId.'<br>';
		
			curl($TrackingNumber,$TxnId,$updateTime,$link);
			sleep(0.5);
			echo $count++.'<br>';
		}
		
	}
	

function curl($TrackingNumber,$TxnId,$updateTime,$link)
{
		//The WSDL is not included with the sample code.
		//Please include and reference in $path_to_wsdl variable.
		$path_to_wsdl = "TrackService_v16.wsdl";

		ini_set("soap.wsdl_cache_enabled", "0");

		$client = new SoapClient($path_to_wsdl, array('trace' => 1)); // Refer to http://us3.php.net/manual/en/ref.soap.php for more information

		$request['WebAuthenticationDetail'] = array(
			'ParentCredential' => array(
				'Key' => getProperty('parentkey'), 
				'Password' => getProperty('parentpassword')
			),
			'UserCredential' => array(
				'Key' => getProperty('key'), 
				'Password' => getProperty('password')
			)
		);

		$request['ClientDetail'] = array(
			'AccountNumber' => getProperty('shipaccount'), 
			'MeterNumber' => getProperty('meter')
		);
		$request['TransactionDetail'] = array('CustomerTransactionId' => '*** Track Request using PHP ***');
		$request['Version'] = array(
			'ServiceId' => 'trck', 
			'Major' => '16', 
			'Intermediate' => '0', 
			'Minor' => '0'
		);
		$request['SelectionDetails'] = array(
			'PackageIdentifier' => array(
				'Type' => 'TRACKING_NUMBER_OR_DOORTAG',
				'Value' => '784300970181' // Replace 'XXX' with a valid tracking identifier
			)
		);
		$request['SelectionDetails'] = array(
			'PackageIdentifier' => array(
				'Type' => 'TRACKING_NUMBER_OR_DOORTAG',
				'Value' => $TrackingNumber // Replace 'XXX' with a valid tracking identifier
			)
		);

		//print_r($request);


		try {
			if(setEndpoint('changeEndpoint')){
				$newLocation = $client->__setLocation(setEndpoint('endpoint'));
			}
			
			$response = $client ->track($request);
		 
			if ($response -> HighestSeverity != 'FAILURE' && $response -> HighestSeverity != 'ERROR'){
				if($response->HighestSeverity != 'SUCCESS'){
					echo '<table border="1">';
					echo '<tr><th>Track Reply</th><th>&nbsp;</th></tr>';
					trackDetails($response->Notifications, '');
					echo '</table>';
				}else{
					if ($response->CompletedTrackDetails->HighestSeverity != 'SUCCESS'){
						echo '<table border="1">';
						echo '<tr><th>Shipment Level Tracking Details</th><th>&nbsp;</th></tr>';
						trackDetails($response->CompletedTrackDetails, '');
						echo '</table>';
					}else{
						if(property_exists($response, 'CompletedTrackDetails'))
						{
								echo  $response->CompletedTrackDetails->TrackDetails->Events->Timestamp.'<br>';
						
							echo  $response->CompletedTrackDetails->TrackDetails->Events->EventDescription;
						
											
							$TrackingStatus=$response->CompletedTrackDetails->TrackDetails->Events->EventDescription;
							$TrackingSummary=$TrackingStatus;
							if($TrackingStatus=='Delivered' || $TrackingStatus=='delivered')
							{
								
								$TrackingStatus='Delivered';
							}
							else if (strpos($TrackingStatus, 'Shipment information sent') !== false) {
									$TrackingStatus='Wating';
								}
								else 
								{
									$TrackingStatus='In-Transit';
									
								}
									updateStatus($TrackingStatus,$TrackingSummary,$TxnId,$updateTime,$link);
							echo  "<br>--------------------------------------------<br>";
							
						}
						else
						{
							$TrackingSummary='not in system';
							$TrackingStatus='Error';
							updateStatus($TrackingStatus,$TrackingSummary,$TxnId,$updateTime,$link);
							
						}
						
						
					}
				}
				//printSuccess($client, $response);
			}else{
				printError($client, $response);
			} 
		   
			writeToLog($client);    // Write to log file   
		} catch (SoapFault $exception) {

			printFault($exception, $client);

	}
	
	
}

function updateStatus($TrackingStatus,$TrackingSummary,$TxnId,$updateTime,$link)
{
	
	$resInsert = mysqli_query($link, "update orders set TrackingStatus='$TrackingStatus',TrackingSummary='$TrackingSummary', UpdateTime='$updateTime'  WHERE TxnId ='$TxnId' "); // insert one row into new table
	if ($resInsert)
		{
		print "TrackingStatus--TrackingStatus :Success<br />";
		print ("update orders set TrackingStatus='$TrackingStatus',TrackingSummary='$TrackingSummary',UpdateTime='$updateTime'  WHERE TxnId ='$TxnId'". "<br />");
		}
	  else
		{
		print ("update orders set TrackingStatus='$TrackingStatus',TrackingSummary='$TrackingSummary',UpdateTime='$updateTime'  WHERE TxnId ='$TxnId'". "<br />");
		}		
}

?>