<?php
require_once('models/Sage_Model.php');
class Sage_Controller{
  private $customers;
  private $orders;
  private $hostname;
  public function __construct($hostname){
    $this->hostname = $hostname;
    $this->conn = new Sage_Model($this->hostname);
  }
  public function getCustomers()
  {
    return $this->customers;
  }
  public function setCustomers()
  {
    $this->customers = $this->conn->setCustomers();
    return $this->customers;
  }
  public function getOrders()
  {
    return $this->orders;
  }
  public function setOrders($country)
  {
    $this->orders = $this->conn->setOrders($country);
    return $this->orders;
  }
  public function getProducts()
  {
    return $this->orders;
  }
	public function setProducts($orderid)
  {

    $this->orders = $this->conn->setProducts($orderid);
    return $this->orders;
  }
  public function getItems()
  {
    return $this->orders;
  }
  public function setItems($orderid)
  {
    $this->orders = $this->conn->setItems($orderid);
    return $this->orders;
  }
}

