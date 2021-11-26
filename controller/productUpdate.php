<?php
header('Access-Control-Allow-Origin: *');
require_once('../config/Database.php');
require_once('../models/Product.php');
require_once('../models/Response.php');


try {
    $database = new Database();
    $db = $database->getConnection();
} catch (PDOException $e) {
    $response = new Response();
    $response->setHttpStatusCode(500);
    $response->setSuccess(false);
    $response->addMessage("Kết nối thất bại");
    $response->send();
    exit();
}


if(array_key_exists('id', $_GET))
{
    $productid = $_GET['id'];
    if(($productid == '')||( !is_numeric($productid)))
    {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("ID sản phẩm không được rỗng và phải là số");
        $response->send();
     
        exit();
    }

    if($_SERVER['REQUEST_METHOD'] == 'PUT')
    {
        try{
             
        $exist = false;
        $inspect = "SELECT * FROM product";
        $result = $db->prepare($inspect);
        $result->execute();
        while($row = $result->fetch(PDO::FETCH_ASSOC))
        {
            if($row['product_id'] == $productid) $exist = true;
        }
        if($exist == false)
        {   
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage("ID sản phẩm không tồn tại");
            $response->send();
        }else{
        $producttitle = $_GET['title'];
        $productstatus = $_GET['status'];
        $productimgSrc = $_GET['imgSrc'];
        $productprice = $_GET['price'];
        $productcolor = $_GET['color'];
        $productsize = $_GET['size'];
        $productrate = $_GET['rate'];
        $productdescription = $_GET['description'];
        
        $query = "UPDATE product SET status = '$productstatus', title = '$producttitle', rate = '$productrate', price = '$productprice', imgSrc = '$productimgSrc', color = '$productcolor', size = '$productsize', description = '$productdescription' WHERE product_id = '$productid'";
        $update = $db->prepare($query);
        $update->execute();
           


        $response = new Response();
        $response->setHttpStatusCode(200);
        $response->setSuccess(true);
        $response->toCache(true);
        $response->send();
        exit;
        }
        }catch (ProductException $e) {
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage($e->getMessage());
            $response->send();
            exit;
        }
        catch(PDOException $e) {
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage($e->getMessage());
            $response->send();
            exit;
        }
    
    }

}    

?>