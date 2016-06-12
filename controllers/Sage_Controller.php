<?php
class Sage_Controller{
  function __construct()
  {
    $_password = 'Edw4rd6s';
    $_username = 'mooncup';
    $_hostname = '188.166.159.243';
    $_database = 'mooncup';

    try {
        $this->conn = new PDO("mysql:host=$_hostname;dbname=$_database", $_username, $_password);
      }catch(PDOException $e)
      {
          echo $e->getMessage();
      }
  }
  public function readCustomers()
  {
    $this->sql =$this->conn->prepare("SELECT  `country`, `currency` FROM _order_locations");
    $this->sql->execute();
    $this->customers = $this->sql->fetchAll();
    return $this->customers;
  }
  public function readOrders($country)
  {
    $this->sql =$this->conn->prepare("SELECT `country`,`currency`,`date` FROM `_order_currency` WHERE `country` = :param");
    $this->sql->bindParam(':param', $country);
    $this->sql->execute();
    $this->orders = $this->sql->fetchAll();
    return $this->orders;
  }
	public function readProducts($orderid)
  {
    $this->sql =$this->conn->prepare("SELECT `currency`,`product_id`,`line_total`, `qty`,`description`,`sku`,`payment_method` FROM `_order_products` WHERE `currency` = :param");
    $this->sql->bindParam(':param', $orderid);
    $this->sql->execute();
    $this->orders = $this->sql->fetchAll();
    return $this->orders;
  }
  public function readItems($orderid)
  {
    $this->sql =$this->conn->prepare("SELECT `currency`,`line_total`,`variation_id`,sum(`qty`) AS `qty`,`description`,`sku`,`user_guide` FROM `_order_items` WHERE `currency` = :param group by `variation_id`");
    $this->sql->bindParam(':param', $orderid);
    $this->sql->execute();
    $this->orders = $this->sql->fetchAll();
    return $this->orders;
  }
}
 ?>
