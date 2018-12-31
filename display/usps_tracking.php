<?php
date_default_timezone_set("America/Chicago");


set_time_limit(1000);
include ('connection.php');
$updateTime = date("Y/m/d h:i:s");
$startDate = date("Y-m-d", strtotime("-3 days"));

$array = array();


//$result = mysqli_query($link, "SELECT TrackingNumber FROM orders WHERE CarrierName='USPS' and TrackingStatus<>'Delivered' and TrackingStatus='' and TrackingNumber<>'' ");
$result = mysqli_query($link, "SELECT TrackingNumber FROM orders WHERE CarrierName='USPS' and TrackingStatus<>'Delivered'  and TrackingNumber<>'' and ShipDate>='$startDate' ");
$xml = "";

$count = 0;
$count2 = 0;
$rowcount = mysqli_num_rows($result);



$root = new SimpleXMLElement("<root></root>");
$newsXML = $root->addChild('TrackRequest');
$newsXML->addAttribute('USERID', '053LIBRA0030');

while ($row = mysqli_fetch_object($result))
	{
	$count++;
	$count2++;


	$TrackingNumber = $row->TrackingNumber;
	$newsIntro = $newsXML->addChild('TrackID');
	$newsIntro->addAttribute('ID', $TrackingNumber);
		if ($count == 10)
		{
		Header('Content-type: text/xml');
		//$xml_file = $newsXML->asXML("$count2.xml");
		request($newsXML->asXML(),$updateTime, $link);
		//array_push($array, $newsXML->asXML());
		
		$newsXML = $root->addChild('TrackRequest');
		$newsXML->addAttribute('USERID', '053LIBRA0030');
		$count = 0;
		
		}
	  else
	if ($count2 == $rowcount)
		{
		Header('Content-type: text/xml');
		//$xml_file = $newsXML->asXML("$count2.xml");
		
		request($newsXML->asXML(),$updateTime, $link);
		
		//array_push($array, $newsXML->asXML());
		$newsXML = $root->addChild('TrackRequest');
		$newsXML->addAttribute('USERID', '053LIBRA0030');
		$count = 0;
		}
		sleep(2);
	}



// $test= request($array[0]);
// echo $test;

function request($para,$updateTime, $link)
	{
	$url = "http://production.shippingapis.com/ShippingAPI.dll?API=TrackV2&";

	// setting the curl parameters.

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);

	// Following line is compulsary to add as it is:

	curl_setopt($ch, CURLOPT_POSTFIELDS, "XML=" . $para);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
	$data = curl_exec($ch);
	curl_close($ch);

	// convert the XML result into array
	// $array_data = json_decode(json_encode(simplexml_load_string($data)), true);

	$xml = simplexml_load_string($data);
	$json = json_encode($xml);
	$array = json_decode($json, TRUE);

	// print ($data);

	
	$TrackInfo = $array["TrackInfo"];

	 //var_dump($TrackInfo);
print ('<div>');	
	for ($x = 0; $x < count($TrackInfo); $x++)
		{
		
		echo $TrackInfo[$x]['@attributes']["ID"];
		
		$TrackingNumber = $TrackInfo[$x]['@attributes']["ID"];
		
		//$TrackDetailArr = $array["TrackInfo"][$x]['TrackDetail'];
		
		if (array_key_exists("TrackSummary",$TrackInfo[$x]))
			  {
			  echo $TrackInfo[$x]['TrackSummary'];
			  $TrackingStatus = $TrackInfo[$x]['TrackSummary'];
			  $TrackingSummary=$TrackingStatus;
				if(strpos($TrackingStatus, 'Your item was delivered') !== false)
							{
								
								$TrackingStatus='Delivered';
							}
							else if (strpos($TrackingStatus, 'A shipping label has been prepared') !== false) {
									$TrackingStatus='Wating';
								}
								else 
								{
									$TrackingStatus='In-Transit';
									
								}
			  }
			else
			  {
			  echo "Key does not exist!";
			  $TrackingSummary='not in system';
			  $TrackingStatus = 'Error';
			  }
			  echo '<a>'.$TrackingNumber.'</a>';
		echo '<a>'.$TrackingStatus.'</a>';
		updateStatus($TrackingStatus, $TrackingSummary ,$TrackingNumber,$updateTime, $link);
		
		echo "###################";
		
		}

	print ('</div>');

	// return $data;

	}

function updateStatus($TrackingStatus,$TrackingSummary,$TrackingNumber,$updateTime, $link)
	{
	$resInsert = mysqli_query($link, "update orders set TrackingStatus='$TrackingStatus', TrackingSummary='$TrackingSummary', UpdateTime='$updateTime'  WHERE TrackingNumber ='$TrackingNumber' "); // insert one row into new table
	if ($resInsert)
		{
			print ("update orders set TrackingStatus='$TrackingStatus', TrackingSummary='$TrackingSummary',UpdateTime='$$updateTime'  WHERE TrackingNumber ='$TrackingNumber'" . "<br />");
		print "TrackingStatus--TrackingStatus :Success<br />";
		}
	  else
		{
		print ("update orders set TrackingStatus='$TrackingStatus',TrackingSummary='$TrackingSummary', UpdateTime='$$updateTime'  WHERE TrackingNumber ='$TrackingNumber'" . "<br />");
		}
	}


?>