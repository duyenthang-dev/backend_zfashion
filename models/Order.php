<?php
class Order{
    private $orderId;
    private $amount;
    private $orderInfo;
    private $status;
    public function __construct($orderId, $amount, $orderInfo, $status){
        $this->orderId = $orderId;
        $this->amount = $amount;
        $this->orderInfo = $orderInfo;
        $this->status = $status;
    }

    function getAmount(){
        return $this->amount;
    }
    function getOrderID() {
        return $this->orderId;
    }
    function getOrderInfo() {
        return $this->orderInfo;
    }
    function getStatus() {
        return $this->status;
    }
    function returnOrderArray(){
        $order = array();
        $order['orderId'] = $this->orderId;
        $order['amount'] = $this->amount;
        $order['orderInfo'] = $this->orderInfo;
        $order['status'] = $this->status;
        return $order;
    }
}