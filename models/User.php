<?php
class UserException extends Exception
{
}
class User
{

    private $id;
    private $account_name;
    private $password;
    private $age;
    private $gender;
    private $email;
    private $phone_number;
    public function __construct($id, $account_name, $age, $gender, $email, $password, $phone_number)
    {
        $this->id = $id;
        $this->account_name = $account_name;
        $this->age = $age;
        $this->gender = $gender;
        $this->password = $password;
        $this->email = $email;
        $this->phone_number = $phone_number;
    }
    //* getter
    public function getId()
    {
        return $this->id;
    }
    public function getAccountName()
    {
        return $this->account_name;
    }
    public function getPassword()
    {
        return $this->password;
    }
    public function getAge()
    {
        return $this->age;
    }
    public function getGender()
    {
        return $this->gender;
    }
    public function getEmail()
    {
        return $this->email;
    }
    public function getPhoneNumber()
    {
        return $this->phone_number;
    }
    public function returnUserArray()
    {
        $user = array();
        $user['id'] = $this->id;
        $user['account_name'] = $this->account_name;
        $user['password'] = $this->password;
        $user['age'] = $this->age;
        $user['email'] = $this->email;
        $user['gender'] = $this->gender;
        $user['phone_number'] = $this->phone_number;
        return $user;
    }

}
