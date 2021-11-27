<?php
require_once('../config/Database.php');
require_once('../models/Bill.php');
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

//* array_key_exists('id', $_GET): nếu có giá trị id trong request gửi lên => thao tác cho 1 hóa đơn
if (array_key_exists('id', $_GET)) {

    $billId = $_GET['id'];
    if ($billId == '' || !is_numeric($billId)) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("ID hóa đơn không được rỗng và phải là số");
        $response->send();
        exit();
    }

    /** 
     * * lấy hóa đơn theo id
     * * vd lấy thông tin hóa đơn có id = 1: http://localhost/backend_zfashion/bills/1 
    */
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        try {
            $query = 'SELECT * FROM bill WHERE ID =:ID LIMIT 1';
            $stmt = $db->prepare($query);
            $stmt->bindParam(':ID', $billId, PDO::PARAM_INT);
            $stmt->execute();

            $rowCount = $stmt->rowCount();

            if ($rowCount === 0) {
                $response = new Response();
                $response->setHttpStatusCode(404);
                $response->setSuccess(false);
                $response->addMessage("Không tìm thấy id hóa đơn");
                $response->send();
                exit();
            }
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $bill = new Bill($row['ID'], $row['time_create'], $row['method']);
                $billArr = $bill->returnBillArray();
            }

            $returnData = array();
            $returnData['rows_return'] = $rowCount;
            $returnData['bill'] = $billArr;

            $response = new Response();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->toCache(true);
            $response->setData($returnData);
            $response->send();
            exit;

        } catch (BillException $e) {
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
        //TODO: tạo hóa đơn
        try {
            //check xem id da ton tai hay chua
            
            $query_check_id = 'SELECT ID FROM bill WHERE ID = :ID';
            
            $stmt = $db->prepare($query_check_id);
            $stmt->bindParam(':ID', $billId, PDO::PARAM_INT);
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

            if ($billId == '' || !is_numeric($billId)) {
                $response = new Response();
                $response->setHttpStatusCode(400);
                $response->setSuccess(false);
                $response->addMessage("ID hóa đơn không được rỗng và phải là số");
                $response->send();
                exit();
            }

            $ID = $billId;
            $time_create = $_POST['time_create'];
            $method = $_POST['method'];

            if(empty($time_create) || empty($method)) {
                $response = new Response();
                $response->setHttpStatusCode(406);
                $response->setSuccess(false);
                $response->addMessage("Thời gian và phương thức không được bỏ trống");
                $response->send();
                exit;
            }

            $query_insert_product = "INSERT INTO bill (ID, time_create, method)
                            VALUES ('$ID', '$time_create', '$method')";


            $insert_bill = $db->prepare($query_insert_product);

            $insert_bill->execute();

            $bill = new Bill($ID, $time_create, $method);
            $billArr = $bill->returnBillArray();

            $returnData = array();
            $returnData['rows_return'] = 1;
            $returnData['bill'] = $billArr;
            $response = new Response();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->toCache(true);
            $response->setData($returnData);
            $response->send();
            exit();

        
        } catch (BillException $e) {
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
        //TODO: xoá hóa đơn theo id
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
//* empty($_GET): thao tác trên cả bảng dữ liệu bills
else if (empty($_GET)) {
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        /** 
        * * lấy tất cả hóa đơn 
        * * vd lấy thông tin tất cả hóa đơn: http://localhost/backend_zfashion/bills
        */
        try 
        {
            $query = 'SELECT * FROM bill ORDER BY ID ASC';
            $stmt = $db->prepare($query);
            $stmt->execute();
            $rowCount = $stmt->rowCount();
            $billArr = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $bill = new Bill($row['ID'], $row['time_create'], $row['method']);
                $billArr[] = $bill->returnBillArray();
                
            }
           
            $returnData = array();
            $returnData['rows_return'] = $rowCount;
            $returnData['bill'] = $billArr;

            $response = new Response();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->setData($returnData);
            $response->toCache(true);
            $response->send();
            exit;

        } catch (BillException $e) {
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
            
            $query_check_id = 'SELECT MAX(ID) AS max_id FROM bill';
            
            $stmt = $db->prepare($query_check_id);
            $stmt->execute();  


            $obj = $stmt->fetchColumn();
            $max_id = $obj;
            $max_id++;
            $ID = $max_id;
            $time_create = $_POST['time_create'];
            $method = $_POST['method'];
            
            if(empty($time_create) || empty($method)) {
                $response = new Response();
                $response->setHttpStatusCode(406);
                $response->setSuccess(false);
                $response->addMessage("Thời gian và phương thức không được bỏ trống");
                $response->send();
                exit;
            }
            
            $query_insert_bill = "INSERT INTO bill (ID, time_create, method)
                            VALUES ('$ID', '$time_create', '$method')";
            
            

            $insert_bill = $db->prepare($query_insert_bill);
            
            $insert_bill->execute();
            
            $bill = new Bill($ID, $time_create, $method);
            $billArr = $bill->returnBillArray();

            $returnData = array();
            $returnData['rows_return'] = 1;
            $returnData['bill'] = $billArr;
            $response = new Response();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->toCache(true);
            $response->setData($returnData);
            $response->send();
            exit();


        } catch (BillException $e) {
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
    //* khi người dùng nhập uri không đúng quy tắc vd /bills/abc => trả về lỗi
    $response = new Response();
    $response->setHttpStatusCode(404);
    $response->setSuccess(false);
    $response->addMessage("Không tìm thấy endpoint");
    $response->send();
    exit;
}
