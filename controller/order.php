<?php
header('Access-Control-Allow-Origin: *');
require_once('./../models/Order.php');
require_once('./../models/Response.php');
require_once('./../config/Database.php');
try {
    //* kết nối tới cơ sở dữ liệu
    $database = new Database();
    $db = $database->getConnection();
} catch (PDOException $e) {
    //* kết nối thất bại
    $response = new Response();
    $response->setHttpStatusCode(500);
    $response->setSuccess(false);
    $response->addMessage("Kết nối thất bại");
    $response->send();
    exit();
}


if (array_key_exists('orderId', $_GET)) {
} elseif (empty($_GET)) {
    //* tạo order
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    } elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {

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
            
            $newOrder = new Order($jsonData->orderId, $jsonData->amount, $jsonData->orderInfo, $jsonData->status);
            
            $orderId = $newOrder->getOrderID();
            $amount = $newOrder->getAmount();
            $orderInfo = $newOrder->getOrderInfo();
            $status = $newOrder->getStatus();

            $querry = "INSERT INTO orders (orderId, time_order, ID_customer, ID_staff, ID_transport_unit, address, amount, orderInfo, status) 
                        VALUES(:orderId, 'a', 'a', 'a', 'a', 'a', :amount, :orderInfo, :status)";
            $stmt = $db->prepare($querry);
            $stmt->bindParam(':orderId', $orderId);
            // $stmt->bindParam(':time_order', "a");            
            // $stmt->bindParam(':ID_customer', "a");            
            // $stmt->bindParam(':ID_staff', "a");            
            // $stmt->bindParam(':ID_transport_unit', "a");            
            // $stmt->bindParam(':address', "");                
            $stmt->bindParam(':amount', $amount);
            $stmt->bindParam(':orderInfo', $orderInfo);
            $stmt->bindParam(':status', $status);

            $stmt->execute();
            $response = new Response();
            $response->setHttpStatusCode(201);
            $response->setSuccess(true);
            $response->addMessage("Thêm thành công");
            $response->send();
            exit;

        } catch (Exception $e) {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage($e->getMessage());
            $response->send();
        }
    }
}
