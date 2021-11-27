<?php
class BillException extends Exception
{
}
class Bill
{

    private $id;
    private $time_create;
    private $method;
    public function __construct($id, $time_create, $method)
    {
        $this->id = $id;
        $this->time_create = $time_create;
        $this->method = $method;
    }
    //* getter
    public function getId()
    {
        return $this->id;
    }
    public function getTimeCreate()
    {
        return $this->time_create;
    }
    public function getMethod()
    {
        return $this->method;
    }
    public function returnBillArray()
    {
        $bill = array();
        $bill['id'] = $this->id;
        $bill['time_create'] = $this->time_create;
        $bill['method'] = $this->method;
        return $bill;
    }

}
