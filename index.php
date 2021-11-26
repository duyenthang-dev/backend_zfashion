<?php
$partnerCode = 'MOMOSHIH20211115';
$accessKey = 'kC6ERc2789qHbU1A';
$requestId = '0a1a0809-c264-4ee0-9210-7aed08636538';
$amount = 100000;
$orderId = 'MM1540456475601';
$orderInfo = "Buy t shirt";
$returnUrl = 'http://localhost:3000/';
$notifyUrl = 'http://localhost:3000/';
$serectkey = 'EiWwQN9kh6cRrRZ3bYDRJPtYVkbTmeCg';
$extraData = "";
$rawHash = "partnerCode=" . $partnerCode . "&accessKey=" . $accessKey . "&requestId=" . $requestId . "&amount=" . $amount . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo . "&returnUrl=" . $returnUrl . "&notifyUrl=" . $notifyUrl . "&extraData=" . $extraData;
$rawData = "partnerCode=MOMOSHIH20211115&accessKey=kC6ERc2789qHbU1A&requestId=0a1a0809-c264-4ee0-9210-7aed08636538&amount=100000&orderId=abcdef&orderInfo=buy&returnUrl=http://localhost:3000&notifyUrl=http://localhost:3000&extraData=email=abc@gmail.com";
$serectkey = "EiWwQN9kh6cRrRZ3bYDRJPtYVkbTmeCg";
$signature = hash_hmac("sha256", $rawData, $serectkey);
echo json_encode($signature);
