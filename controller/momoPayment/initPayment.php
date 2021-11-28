<?php
include_once('../../common/helper.php');
include_once('../../models/Response.php');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
$config = file_get_contents('../../config/payment.json');

$m4bInfo = json_decode($config, true);

$endpoint = "https://test-payment.momo.vn/gw_payment/transactionProcessor";
$partnerCode = $m4bInfo['partnerCode'];
$accessKey = $m4bInfo['accessKey'];
$secretKey = $m4bInfo['secretKey'];
$returnUrl = $m4bInfo['returnUrl'];
$notifyurl = $m4bInfo['notifyurl'];
$extraData = "merchantName=Z Fashion";
$requestId = $m4bInfo['requestId'];

if (empty($_GET)) {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        /**
         * * data form 
         * * {
         * *    "orderId" : "aaaa",
         * *    "amount" : 100,
         * *    "orderInfo" : "aaaa",
         * *    "status": "status",
         * *}
         */
        try {
            if ($_SERVER['CONTENT_TYPE'] != 'application/json') {
                $response = new Response();
                $response->setHttpStatusCode(400);
                $response->setSuccess(false);
                $response->addMessage("Dữ liệu phải ở định dạng Json");
                $response->send();
                exit;
            }
            $rawData = file_get_contents('php://input');
           
            $jsonData = json_decode($rawData);
          
            $orderId = $jsonData->orderId;
            $amount = $jsonData->amount;
            $orderInfo = $jsonData->orderInfo;
            $status = $jsonData->status;
            $requestType = "captureMoMoWallet";

            //* create HMAC SHA256 for signature
            $rawHash = "partnerCode=" . $partnerCode . "&accessKey=" . $accessKey . "&requestId=" . $requestId . "&amount=" . $amount . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo . "&returnUrl=" . $returnUrl . "&notifyUrl=" . $notifyurl . "&extraData=" . $extraData;
            $signature = hash_hmac('sha256', $rawHash, $secretKey);
            $data = array(
                'partnerCode' => $partnerCode,
                'accessKey' => $accessKey,
                'requestId' => $requestId,
                'amount' => $amount,
                'orderId' => $orderId,
                'orderInfo' => $orderInfo,
                'returnUrl' => $returnUrl,
                'notifyUrl' => $notifyurl,
                'extraData' => $extraData,
                'requestType' => $requestType,
                'signature' => $signature
            );

            $res = executePostRequest($endpoint, json_encode($data));
            $json_res = json_decode($res);
            echo json_encode($json_res);
        } 
        catch (Exception $e) {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage($e->getMessage());
            $response->send();
        }
    }
}
