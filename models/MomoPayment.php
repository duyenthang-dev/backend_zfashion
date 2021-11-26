<?php
class MomoPayment
{
    private $accessKey;
    private $requestID;
    private $amount;
    private $orderId;
    private $orderInfo;
    private $returnUrl;
    private $notifyUrl;
    private $extraData;
    private $signature;

    function __construct($accessKey, $requestID, $amount, $orderId, $orderInfo, $returnUrl, $notifyUrl, $extraData, $signature)
    {
        $this->accessKey = $accessKey;
        $this->requestID = $requestID;
        $this->amount = $amount;
        $this->orderId = $orderId;
        $this->orderInfo = $orderInfo;
        $this->returnUrl = $returnUrl;
        $this->notifyUrl = $notifyUrl;
        $this->extraData = $extraData;
        $this->signature = $signature;
    }

    //*getter
    function getAccessKey()
    {
        return $this->accessKey;
    }
    function getRequestID()
    {
        return $this->requestID;
    }
    function getAmount()
    {
        return $this->amount;
    }
    function getOrderID()
    {
        return $this->orderID;
    }
    function getOrderInfo()
    {
        return $this->orderInfo;
    }
    function getReturnUrl()
    {
        return $this->returnUrl;
    }
    function getNotifyUrl()
    {
        return $this->notifyUrl;
    }
    function getExtraData()
    {
        return $this->extraData;
    }

    function returnPaymentArray()
    {
        $payment = array();
        $payment['accessKey'] = $this->accessKey;
        $payment['requestId'] = $this->requestId;
        $payment['amount'] = $this->amount;
        $payment['orderId'] = $this->orderId;
        $payment['orderInfo'] = $this->orderInfo;
        $payment['returnUrl'] = $this->returnUrl;
        $payment['notifyUrl'] = $this->notifyUrl;
        $payment['extraData'] = $this->extraData;
        return $payment;
    }
    
}
