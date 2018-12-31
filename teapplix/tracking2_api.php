<?php

//$service_url = 'https://api.teapplix.com/api2/Track?Carrier=USPS&TrackingNumber=9405509205328534109941';
$service_url = 'https://api.teapplix.com/api2/Track?Carrier=FEDEX&TrackingNumber=784334185397';

$curl = curl_init($service_url);
curl_setopt($curl, CURLOPT_HTTPHEADER, array(
'APIToken: ' . '99333-cfbb3-9f3d0-beca9-f674e-90320-6081'
));
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
//echo 'response ok!';
//var_export($decoded1->response);
?>