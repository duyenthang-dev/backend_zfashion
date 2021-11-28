<?php
require_once('../config/Database.php');
require_once('../models/User.php');
require_once('../models/Response.php');
// header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');


//*-----------------------------------DATABASE CONNECTION--------------------------
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
    exit;
} 

if(array_key_exists('sessionId', $_GET)){


}
elseif(empty($_GET))
{
    //* tạo login session
   
    if($_SERVER['REQUEST_METHOD'] !== 'POST')
    {
        $response = new Response();
        $response->setHttpStatusCode(405);
        $response->setSuccess(false);
        $response->addMessage("Request không được hỗ trợ");
        $response->send();
        exit;
    }
   
    // if($_SERVER['CONTENT_TYPE'] !== 'application/json')
    // {
    //     $response = new Response();
    //     $response->setHttpStatusCode(400);
    //     $response->setSuccess(false);
    //     $response->addMessage("Content type header phải là json");
    //     $response->addMessage($_SERVER['CONTENT_TYPE']);
    //     $response->send();
    //     exit;
    // }

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

    if(!isset($jsonData->username) || !isset($jsonData->password))
    {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        (!isset($jsonData->username)) ? $response->addMessage("Chưa có username field"): false;
        (!isset($jsonData->password)) ? $response->addMessage("Chưa có password field"): false;  
        $response->send();
        exit;
    }
    if (strlen($jsonData->username) < 1 || strlen($jsonData->username) > 100 || strlen($jsonData->password) < 1 || strlen($jsonData->password) > 50)
    {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        (strlen($jsonData->username) < 1) ? $response->addMessage("username không được để trống"): false;
        (strlen($jsonData->username) > 50) ? $response->addMessage("username không được quá 50 kí tự"): false;  
        (strlen($jsonData->password) < 1) ? $response->addMessage("password không được để trống"): false;
        (strlen($jsonData->password) > 50) ? $response->addMessage("password không được quá 50 kí tự"): false; 
        $response->send();
        exit;
    }
    try{
        $username = $jsonData->username;
        $password = $jsonData->password;

        $query = $db->prepare('SELECT ID, fullname, username, password, email FROM accs WHERE username = :username');
        $query->bindParam(':username', $username, PDO::PARAM_STR);
        $query->execute();

        if($query->rowCount() === 0)
        {
            $response = new Response();
            $response->setHttpStatusCode(401);
            $response->setSuccess(false);
            $response->addMessage("Tài khoản hoặc mật khẩu sai");
            $response->send();
            exit;
        }

        $row = $query->fetch(PDO::FETCH_ASSOC);
        $return_id = $row['ID'];
        $return_fullname = $row['fullname'];
        $return_username = $row['username'];
        $return_password = $row['password'];
        $return_email = $row['email'];

        if(!password_verify($password, $return_password))
        {
            $response = new Response();
            $response->setHttpStatusCode(401);
            $response->setSuccess(false);
            $response->addMessage("Tài khoản hoặc mật khẩu sai");
            $response->send();
            exit;
        }

        $accessToken = base64_encode(bin2hex(openssl_random_pseudo_bytes(24).time()));        
        $refeshAcessToken = base64_encode(bin2hex(openssl_random_pseudo_bytes(24).time()));
        
        $accessToken_expires = 1209600;
        $refeshAcessToken_expires = $accessToken_expires;

        $query = $db->prepare('INSERT INTO sessions (user_id, access_token, access_token_expire, refresh_token, refresh_token_expire)
                        VALUES (:userId, :accessToken, date_add(NOW(), INTERVAL :accessTokenExpire SECOND), :refreshToken, date_add(NOW(), INTERVAL :refreshTokenExpire SECOND))');
        $query->bindParam(':userId', $return_id);
        $query->bindParam(':accessToken', $accessToken);
        $query->bindParam(':accessTokenExpire', $accessToken_expires);
        $query->bindParam(':refreshToken', $refeshAcessToken);
        $query->bindParam(':refreshTokenExpire', $refeshAcessToken_expires);
        $query->execute();

        $lastSessionId = $db->lastInsertId();
        $returnDataa = array();
        $returnDataa['username'] = $username;
        $returnDataa['session_id'] = intval($lastSessionId);
        $returnDataa['access_token'] = $accessToken;
        $returnDataa['access_token_expire'] = $accessToken_expires;
        $returnDataa['refresh_token'] = $refeshAcessToken;
        $returnDataa['refresh_token_expire'] = $refeshAcessToken_expires;

        $response = new Response();
        $response->setHttpStatusCode(201);
        $response->setSuccess(true);
        $response->setData($returnDataa);
        $response->send();
      
        exit;

    }
    catch(PDOException $e)
    {
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage($e->getMessage());
        $response->send();
        exit;
    }
    
}
else
{
     //* khi người dùng nhập uri không đúng quy tắc vd /Users/abc => trả về lỗi
    $response = new Response();
    $response->setHttpStatusCode(404);
    $response->setSuccess(false);
    $response->addMessage("Không tìm thấy endpoint");
    $response->send();
    exit;
}