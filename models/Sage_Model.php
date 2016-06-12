<?php

class Sage_Model{
    private $password = 'Edw4rd6s';
    private $username = 'mooncup';
    private $database = 'mooncup';
    private $hostname = '188.166.159.243';
    private $country;
    function __construct($hostname)
    {
        $this->hostname = $hostname;
        $this->password = 'Edw4rd6s';
        $this->username = 'mooncup';
        $this->database = 'mooncup';

        try {
            $this->conn = new PDO("mysql:host=$this->hostname;dbname=$this->database", $this->username, $this->password);
        }catch(PDOException $e)
        {
            echo $e->getMessage();
        }
        return $this->conn;
    }
    public function getOrders(){
        return $this->country;
    }

    public function setCustomers()
    {
        $this->sql =$this->conn->prepare("SELECT  `country`, `currency` FROM _order_locations");
        $this->sql->execute();
        $this->customers = $this->sql->fetchAll();
        return $this->customers;
    }
    public function setOrders($country)
    {
        $this->country = $country;
        $this->sql =$this->conn->prepare("SELECT `country`,`currency`,`date` FROM `_order_currency` WHERE `country` = :param");
        $this->sql->bindParam(':param', $this->country);
        $this->sql->execute();
        $this->orders = $this->sql->fetchAll();
        return $this->orders;
    }
    public function setProducts($orderid)
    {
        $this->sql =$this->conn->prepare("SELECT `currency`,`product_id`,`line_total`, `qty`,`description`,`sku`,`payment_method` FROM `_order_products` WHERE `currency` = :param");
        $this->sql->bindParam(':param', $orderid);
        $this->sql->execute();
        $this->orders = $this->sql->fetchAll();
        return $this->orders;
    }
    public function setItems($orderid)
    {
        $this->sql =$this->conn->prepare("SELECT `currency`,`line_total`,`variation_id`,sum(`qty`) AS `qty`,`description`,`sku`,`user_guide` FROM `_order_items` WHERE `currency` = :param group by `variation_id`");
        $this->sql->bindParam(':param', $orderid);
        $this->sql->execute();
        $this->orders = $this->sql->fetchAll();
        return $this->orders;
    }
}