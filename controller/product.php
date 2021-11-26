<?php
require_once('../config/Database.php');
require_once('../models/Product.php');
require_once('../models/Response.php');
/** ------------------------------------------------------------------------------------------------------------ */
/** 
 * *các api có dạng 
 * *{
    "success": true,
    "statusCode": 200,
    "message": [],
    "data": {
        "rows_return": 1,
        "product": {
            "id": "1",
            "title": "Áo thun nam chuột Mickeys",
            "status": "New",
            "imgSrc": "test",
            "price": "200000",
            "color": "yellow,white,navy,orange,pink",
            "size": "S,M,L,XL",
            "description": "Áo với form dáng thoải mái, với chất liệu vải 100% cotton dễ chịu khi mặc. Là trang phục hàng ngày hoàn hảo, dễ dàng kết hợp với mọi thứ"
        }
    }
}
* * Bao gồm 4 phần:
* * - success: trạng thái báo thành công hay thất bại
* * - statusCode: mã http code trả về
* * - message: message từ server trả về, có thể rỗng
* * - data: phần dữ liệu của server lấy từ database trả về
*/


/** ------------------------------------------------------------------------------------------------------------ */
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

//* array_key_exists('id', $_GET): nếu có giá trị id trong request gửi lên => thao tác cho 1 sản phẩm
if (array_key_exists('id', $_GET)) {

    $productId = $_GET['id'];
    if ($productId == '' || !is_numeric($productId)) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("ID sản phẩm không được rỗng và phải là số");
        $response->send();
        exit();
    }

    /** 
     * * lấy sản phẩm theo id
     * * vd lấy thông tin sản phẩm có id = 1: http://localhost/backend_zfashion/products/1 
    */
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        try {
            $query = 'SELECT * FROM product WHERE product_id =:productId LIMIT 1';
            $stmt = $db->prepare($query);
            $stmt->bindParam(':productId', $productId, PDO::PARAM_INT);
            $stmt->execute();

            $rowCount = $stmt->rowCount();

            if ($rowCount === 0) {
                $response = new Response();
                $response->setHttpStatusCode(404);
                $response->setSuccess(false);
                $response->addMessage("Không tìm thấy id sản phẩm");
                $response->send();
                exit();
            }
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $product = new Product($row['product_id'], $row['title'], $row['state'], $row['imgSrc'], $row['price'], $row['color'], $row['size'], $row['description']);
                $prodArr = $product->returnProductArray();
            }

            $returnData = array();
            $returnData['rows_return'] = $rowCount;
            $returnData['product'] = $prodArr;

            $response = new Response();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->toCache(true);
            $response->setData($returnData);
            $response->send();
            exit;

        } catch (ProductException $e) {
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
    elseif ($_SERVER['REQUEST_METHOD'] == 'POST')
    {
        //TODO: tạo sản phẩm
        
        try {
            //check xem id da ton tai hay chua
            
            $query_check_id = 'SELECT product_id FROM product WHERE product_id = :productId';
            
            $stmt = $db->prepare($query_check_id);
            $stmt->bindParam(':productId', $productId, PDO::PARAM_INT);
            $stmt->execute();

            $rowCount = $stmt->rowCount();

            if($rowCount) {
                $response = new Response();
                $response->setHttpStatusCode(406);
                $response->setSuccess(false);
                $response->addMessage("ID này đã tồn tại");
                $response->send();
                exit();
            }

            if ($productId == '' || !is_numeric($productId)) {
                $response = new Response();
                $response->setHttpStatusCode(400);
                $response->setSuccess(false);
                $response->addMessage("ID sản phẩm không được rỗng và phải là số");
                $response->send();
                exit();
            }

            $ID = $productId;
            $title = $_POST['title'];
            $price = $_POST['price'];
            $state = $_POST['state'];
            $imgSrc = $_POST['imgSrc'];
            $color = $_POST['color'];
            $size = $_POST['size'];
            $des = $_POST['description'];
            $quantity = $_POST['quantity'];
            $rate = 0;

            if(empty($size) || empty($des)) {
                $response = new Response();
                $response->setHttpStatusCode(406);
                $response->setSuccess(false);
                $response->addMessage("Kích cỡ và mô tả không được bỏ trống");
                $response->send();
                exit;
            }

            $query_insert_product = "INSERT INTO product (product_id, state, title, rate, price, ID_orders, ID_producer, quantity, imgSrc, color, size, description)
                            VALUES ('$ID', '$state', '$title', '$rate', '$price', NULL, NULL, '$quantity', '$imgSrc', '$color', '$size', '$des')";

            $query_insert_size = "INSERT INTO size (ID_product, size) VALUE ('$ID', '$size')";
            $query_insert_color = "INSERT INTO color (ID_product, color) VALUE ('$ID', '$color')";

            $insert_product = $db->prepare($query_insert_product);
            $insert_size = $db->prepare($query_insert_size);
            $insert_color = $db->prepare($query_insert_color);

            $insert_product->execute();
            $insert_size->execute();
            $insert_color->execute();

            $product = new Product($ID, $title, $state, $imgSrc, $price, $color, $size, $des);
            $prodArr = $product->returnProductArray();

            $returnData = array();
            $returnData['rows_return'] = 1;
            $returnData['product'] = $prodArr;
            $response = new Response();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->toCache(true);
            $response->setData($returnData);
            $response->send();
            exit();

        
        } catch (ProductException $e) {
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
    elseif ($_SERVER['REQUEST_METHOD'] == 'DELETE') 
    {
        //TODO: xoá sản phẩm theo id
    } 
    else 
    {
        //* request method không hợp lệ
        $response = new Response();
        $response->setHttpStatusCode(405);
        $response->setSuccess(false);
        $response->addMessage("Request method not allowed");
        $response->send();
        exit;
    }
}
//* empty($_GET): thao tác trên cả bảng dữ liệu products
else if (empty($_GET)) {
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        /** 
        * * lấy tất cả sản phẩm 
        * * vd lấy thông tin tất cả sản phẩm: http://localhost/backend_zfashion/products
        */
        try 
        {
            $query = 'SELECT * FROM product ORDER BY product_id ASC';
            $stmt = $db->prepare($query);
            $stmt->execute();
            $rowCount = $stmt->rowCount();
            $prodArr = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $product = new Product($row['product_id'], $row['title'], $row['state'], $row['imgSrc'], $row['price'], $row['color'], $row['size'], $row['description']);
                $prodArr[] = $product->returnProductArray();
                
            }
           
            $returnData = array();
            $returnData['rows_return'] = $rowCount;
            $returnData['products'] = $prodArr;

            $response = new Response();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->setData($returnData);
            $response->toCache(true);
            $response->send();
            exit;

        } catch (ProductException $e) {
            //* lấy danh sách sản phẩm không thành công
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage($e->getMessage());
            $response->send();
            exit;
        }
    } 
    else if ($_SERVER['REQUEST_METHOD'] == 'POST') 
    {
        //TODO: có param id
        try {
            //lấy id lớn nhất trong db
            
            $query_check_id = 'SELECT MAX(product_id) AS max_id FROM product';
            
            $stmt = $db->prepare($query_check_id);
            $stmt->execute();  


            $obj = $stmt->fetchColumn();
            $max_id = $obj;
            $max_id++;
            $ID = $max_id;
            $title = $_POST['title'];
            $price = $_POST['price'];
            $state = $_POST['state'];
            $imgSrc = $_POST['imgSrc'];
            $color = $_POST['color'];
            $size = $_POST['size'];
            $des = $_POST['description'];
            $quantity = $_POST['quantity'];
            $rate = 0;
            
            if(empty($size) || empty($des)) {
                $response = new Response();
                $response->setHttpStatusCode(406);
                $response->setSuccess(false);
                $response->addMessage("Kích cỡ và mô tả không được bỏ trống");
                $response->send();
                exit;
            }

            $query_insert_product = "INSERT INTO product (product_id, state, title, rate, price, ID_orders, ID_producer, quantity, imgSrc, color, size, description)
                            VALUES ('$ID', '$state', '$title', '$rate', '$price', NULL, NULL, '$quantity', '$imgSrc', '$color', '$size', '$des')";
            
            //$query_insert_size = "INSERT INTO size VALUE ('$ID', '$size')";
            //$query_insert_color = "INSERT INTO color VALUE ('$ID', '$color')";

            $insert_product = $db->prepare($query_insert_product);
            //$insert_size = $db->prepare($query_insert_size);
            //$insert_color = $db->prepare($query_insert_color);

            $insert_product->execute();
            //$insert_size->execute();
            //$insert_color->execute();

            $product = new Product($ID, $title, $state, $imgSrc, $price, $color, $size, $des);
            $prodArr = $product->returnProductArray();

            $returnData = array();
            $returnData['rows_return'] = 1;
            $returnData['product'] = $prodArr;
            $response = new Response();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->toCache(true);
            $response->setData($returnData);
            $response->send();
            exit();


        } catch (ProductException $e) {
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
    else {
        $response = new Response();
        $response->setHttpStatusCode(405);
        $response->setSuccess(false);
        $response->addMessage("Request method not allowed");
        $response->send();
        exit;
    }
}
else
{
    //* khi người dùng nhập uri không đúng quy tắc vd /products/abc => trả về lỗi
    $response = new Response();
    $response->setHttpStatusCode(404);
    $response->setSuccess(false);
    $response->addMessage("Không tìm thấy endpoint");
    $response->send();
    exit;
}
