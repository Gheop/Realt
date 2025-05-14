<?php

$SubGraphApiKey = '[API-KEY]';
$SubGraphRealtTokenUrl = 'https://gateway-arbitrum.network.thegraph.com/api/'.$SubGraphApiKey.'/subgraphs/id/FPPoFB7S2dcCNrRyjM5QbaMwKqRZPdbTg8ysBrwXd4SP';

function getAllAddresses($userId) {
    global $SubGraphRealtTokenUrl;
    if (empty($userId)) return false;
    //trusted address need before.
    $userId = '0x296033cb983747b68911244ec1a3f01d7708851b-'.$userId;

    $sGraph = '{"operationName":null,"variables":{},"query":"{ accounts(where: { userIds: [\"'.$userId.'\"] }) { address } }"}';
//    $sGraph = '{"operationName":null,"variables":{},"query":"{ accounts(where: { userIds: [\"'.$userId.'\"] }) { address } whitelist: userId(id: \"'.$userId.'\") { attributeKeys attributeValues } }"}';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; fr; rv:1.9.2.13) Gecko/20101203 Firefox/3.6.13');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $sGraph);
    curl_setopt($ch, CURLOPT_URL, $SubGraphRealtTokenUrl);
    $ret = curl_exec($ch);
    curl_close($ch);
    $json = json_decode($ret, true);
    if (isset($json['data']['accounts'])) {
        return $json['data']['accounts'];
    } else {
        return;
    }
}

$userId = null;
if (!empty($_GET['userId'])) {
    $userId = $_GET['userId'];
} elseif (!empty($_POST['userId'])) {
    $userId = $_POST['userId'];
}

if($userId == null) {
    $address = null;
    if (!empty($_GET['address'])) {
        $address = $_GET['address'];
    } elseif (!empty($_POST['address'])) {
        $address = $_POST['address'];
    }
    $userId = file_get_contents('https://killythe.bid/realt/getUserId.php?address='.$address);
}
if ($userId !== null) {
    header('Content-Type: application/json');
    $accounts = getAllAddresses($userId);
    if(!empty($accounts)) {
        $addresses = array_map(function($item) { return $item['address']; }, $accounts);
        echo json_encode($addresses);
    }
    else {
        echo '{}';
    }
} else {
    echo '{}';
} 