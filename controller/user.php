<?php
require_once('../config/Database.php');
require_once('../models/User.php');
require_once('../models/Response.php');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
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

    $userId = $_GET['id'];
    if ($userId == '' || !is_numeric($userId)) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("ID người dùng không được rỗng và phải là số");
        $response->send();
        exit();
    }

    /** 
     * * lấy hóa đơn theo id
     * * vd lấy thông tin hóa đơn có id = 1: http://localhost/backend_zfashion/accs/1 
     */
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        try {
            $query = 'SELECT * FROM acc WHERE ID =:ID LIMIT 1';
            $stmt = $db->prepare($query);
            $stmt->bindParam(':ID', $userId, PDO::PARAM_INT);
            $stmt->execute();

            $rowCount = $stmt->rowCount();

            if ($rowCount === 0) {
                $response = new Response();
                $response->setHttpStatusCode(404);
                $response->setSuccess(false);
                $response->addMessage("Không tìm thấy id người dùng");
                $response->send();
                exit();
            }
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $user = new User($row['ID'], $row['account_name'], $row['age'], $row['gender'], $row['email'], $row['account_password'], $row['phone_number']);
                $userArr = $user->returnUserArray();
            }

            $returnData = array();
            $returnData['rows_return'] = $rowCount;
            $returnData['user'] = $userArr;

            $response = new Response();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->toCache(true);
            $response->setData($returnData);
            $response->send();
            exit;
        } catch (UserException $e) {
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage($e->getMessage());
            $response->send();
            exit;
        } catch (PDOException $e) {
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage($e->getMessage());
            $response->send();
            exit;
        }
    } elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
        //TODO: tạo hóa đơn
        try {
            //check xem id da ton tai hay chua

            $query_check_id = 'SELECT ID FROM acc WHERE ID = :ID';

            $stmt = $db->prepare($query_check_id);
            $stmt->bindParam(':ID', $userId, PDO::PARAM_INT);
            $stmt->execute();

            $rowCount = $stmt->rowCount();

            if ($rowCount) {
                $response = new Response();
                $response->setHttpStatusCode(406);
                $response->setSuccess(false);
                $response->addMessage("ID này đã tồn tại");
                $response->send();
                exit();
            }

            if ($userId == '' || !is_numeric($userId)) {
                $response = new Response();
                $response->setHttpStatusCode(400);
                $response->setSuccess(false);
                $response->addMessage("ID hóa đơn không được rỗng và phải là số");
                $response->send();
                exit();
            }

            $ID = $userId;
            $account_name = $_POST['account_name'];
            $age = $_POST['age'];
            $gender = $_POST['gender'];
            $phone_number = $_POST['phone_number'];
            $email = $_POST['email'];
            $account_password = $_POST['account_password'];


            if (empty($account_name) || empty($account_password)) {
                $response = new Response();
                $response->setHttpStatusCode(406);
                $response->setSuccess(false);
                $response->addMessage("Tên đăng nhập và mật khẩu không được bỏ trống");
                $response->send();
                exit;
            }

            $query_insert_user = "INSERT INTO acc (ID, account_name, age, gender, phone_number, email, account_password)
                            VALUES ('$ID', '$account_name', '$age', '$gender', '$phone_number', '$email', '$account_password')";



            $insert_user = $db->prepare($query_insert_user);

            $insert_user->execute();

            $user = new User($ID, $account_name, $age, $gender, $phone_number, $email, $account_password);
            $userArr = $user->returnUserArray();

            $returnData = array();
            $returnData['rows_return'] = 1;
            $returnData['user'] = $userArr;
            $response = new Response();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->toCache(true);
            $response->setData($returnData);
            $response->send();
            exit();
        } catch (UserException $e) {
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage($e->getMessage());
            $response->send();
            exit;
        } catch (PDOException $e) {
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage($e->getMessage());
            $response->send();
            exit;
        }
    } elseif ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
        //TODO: xoá hóa đơn theo id
    } else {
        //* request method không hợp lệ
        $response = new Response();
        $response->setHttpStatusCode(405);
        $response->setSuccess(false);
        $response->addMessage("Request method not allowed");
        $response->send();
        exit;
    }
}
//* empty($_GET): thao tác trên cả bảng dữ liệu Users
else if (empty($_GET)) {
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        /** 
         * * lấy tất cả hóa đơn 
         * * vd lấy thông tin tất cả hóa đơn: http://localhost/backend_zfashion/Users
         */
        try {
            $query = 'SELECT * FROM acc ORDER BY ID ASC';
            $stmt = $db->prepare($query);
            $stmt->execute();
            $rowCount = $stmt->rowCount();
            $userArr = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $user = new User($row['ID'], $row['account_name'], $row['age'], $row['gender'], $row['email'], $row['account_password'], $row['phone_number']);
                $userArr[] = $user->returnUserArray();
            }

            $returnData = array();
            $returnData['rows_return'] = $rowCount;
            $returnData['user'] = $userArr;

            $response = new Response();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->setData($returnData);
            $response->toCache(true);
            $response->send();
            exit;
        } catch (UserException $e) {
            //* lấy danh sách sản phẩm không thành công
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage($e->getMessage());
            $response->send();
            exit;
        }
    } else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        //TODO: tạo mới user
        try {
            //lấy id lớn nhất trong db
            $rawData = file_get_contents('php://input');
            $jsonData = json_decode($rawData);

            if (!$jsonData) {
                $response = new Response();
                $response->setHttpStatusCode(400);
                $response->setSuccess(false);
                $response->addMessage("Request gửi lên phải là định dạng json");
                $response->send();
                exit;
            }

            //* kiểm tra thông tin 
            if (!isset($jsonData->username) || !isset($jsonData->email) || !isset($jsonData->password) || !isset($jsonData->fullname)) {
                $response = new Response();
                $response->setHttpStatusCode(400);
                $response->setSuccess(false);
                (!isset($jsonData->fullname) ? $response->addMessage("Chưa nhập họ tên!") : false);
                (!isset($jsonData->username) ? $response->addMessage("Chưa nhập tên tài khoản!") : false);
                (!isset($jsonData->password) ? $response->addMessage("Chưa nhập mật khẩu!") : false);
                (!isset($jsonData->email) ? $response->addMessage("Chưa nhập email!") : false);
                $response->send();
                exit;
            }

            $username = $jsonData->username;
            $fullName = $jsonData->fullname;
            $password = $jsonData->password;
            $email = $jsonData->email;

            if (strlen($fullName) < 1 || strlen($fullName) > 100 || strlen($username) < 1 || strlen($username) > 50 || strlen($email) < 1 || strlen($email) > 50 || strlen($password) < 1 || strlen($password) > 30) 
            {
                $response = new Response();
                $response->setHttpStatusCode(400);
                $response->setSuccess(false);
                (strlen($fullName) < 1) ? $response->addMessage("Họ tên không được để trống") : false;
                (strlen($fullName) > 100) ? $response->addMessage("Họ tên có độ dài tối đa 100 kí tự") : false;
                (strlen($username) < 1) ? $response->addMessage("Username không được để trống") : false;
                (strlen($username) > 50) ? $response->addMessage("Username có độ dài tối đa 50 kí tự") : false;
                (strlen($email) < 1) ? $response->addMessage("Email không được để trống") : false;
                (strlen($email) > 50) ? $response->addMessage("Email có độ dài tối đa 50 kí tự") : false;
                (strlen($password) < 1) ? $response->addMessage("Mật khẩu không được để trống") : false;
                (strlen($password) > 30) ? $response->addMessage("Mật khẩu có độ dài tối đa 30 kí tự") : false;
                $response->send();

            }
            try{
                $query = $db->prepare('SELECT id FROM accs WHERE username = :username');
                $query->bindParam(':username', $username);
                $query->execute();
                if($query->rowCount() !== 0)
                {
                    $response = new Response();
                    $response->setHttpStatusCode(409);
                    $response->setSuccess(false);
                    $response->addMessage('Tài khoản đã tồn tại');
                    $response->send();
                    exit;
                }
                //* mã hoá password 
                $hash_password = password_hash($password, PASSWORD_DEFAULT);
                $query = $db->prepare('INSERT INTO accs (fullname, username, age, phone_number,gender, email, password) 
                                    VALUES (:fullname, :username, 0, 0, "" , :email, :password)');
                
                $query->bindParam(':fullname', $fullName);
                $query->bindParam(':username', $username);
                $query->bindParam(':email', $email);
                $query->bindParam(':password', $hash_password);
                $query->execute();

                $lastUserID = $db->lastInsertId();
                $returnData = array();
                $returnData['user_id'] = $lastUserID;
                $returnData['fullname'] = $fullName;
                $returnData['username'] = $username;
                
                $response = new Response();
                $response->setHttpStatusCode(201);
                $response->setSuccess(true);
                $response->addMessage("Tạo tài khoản thành công");
                $response->setData($returnData);
                $response->send();
                exit();
            }
            catch(PDOException $e){
                error_log("Database querry error ".$e, 0);
                $response = new Response();
                $response->setHttpStatusCode(400);
                $response->setSuccess(false);
                $response->addMessage($e->getMessage());
                $response->send();
                exit;
            }
            
        } catch (UserException $e) {
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage($e->getMessage());
            $response->send();
            exit;
        } catch (PDOException $e) {
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage($e->getMessage());
            $response->send();
            exit;
        }
    } else {
        $response = new Response();
        $response->setHttpStatusCode(405);
        $response->setSuccess(false);
        $response->addMessage("Request method not allowed");
        $response->send();
        exit;
    }
} else {
    //* khi người dùng nhập uri không đúng quy tắc vd /Users/abc => trả về lỗi
    $response = new Response();
    $response->setHttpStatusCode(404);
    $response->setSuccess(false);
    $response->addMessage("Không tìm thấy endpoint");
    $response->send();
    exit;
}
