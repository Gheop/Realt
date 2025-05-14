<?php

$SubGraphApiKey = '[API_KEY]';
$SubGraphRealtTokenUrl = 'https://gateway-arbitrum.network.thegraph.com/api/'.$SubGraphApiKey.'/subgraphs/id/FPPoFB7S2dcCNrRyjM5QbaMwKqRZPdbTg8ysBrwXd4SP';

function getUserID($address) {
	global $SubGraphRealtTokenUrl;

	if ($address !== null) {
		if (isValidEthereumAddress($address)) {
			$address = strtolower($address);
		} else {
			return false;
		}
	}
	else return false;
	

	$sGraph = '{"operationName":"RealTokenQuery","variables":{"address":"'.$address.'"},"query":"  query GetUserIdQuery($address: String!) {\n    account(id: $address) {\n      userIds(orderBy: timestamp, orderDirection: desc, first: 1) {\nuserId\n}\n}\n}"}';
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_HEADER, 		 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_POST,           1 );
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; fr; rv:1.9.2.13) Gecko/20101203 Firefox/3.6.13');
	curl_setopt($ch, CURLOPT_POSTFIELDS,  	 $sGraph);
	curl_setopt($ch, CURLOPT_URL, 		     $SubGraphRealtTokenUrl);
	$ret=json_decode(curl_exec($ch));
	curl_close($ch);

	if(empty($ret)) return;

	return $ret->data->account->userIds[0]->userId;
}

function isValidEthereumAddress($address) {
    return preg_match('/^0x[a-fA-F0-9]{40}$/', $address);
}

$address = null;
if (!empty($_GET['address'])) {
    $address = $_GET['address'];
} elseif (!empty($_POST['address'])) {
    $address = $_POST['address'];
}

echo getUserID($address);
