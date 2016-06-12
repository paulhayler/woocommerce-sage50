<?php
require_once('controllers/Sage_Controller.php');
echo "<?xml version=\"1.0\" standalone=\"yes\" ?>\n";
echo "<DsOrders xmlns=\"http://www.tempuri.org/DsOrderInfo.xsd\">\n";
$xmlData = new Sage_Controller('188.166.159.243');

// Customer loop by location  
$customers = $xmlData->setCustomers();
// <Customers>
foreach ($customers as $customer):
    $country = $customer['country'];
  echo "<Customers>\n\r";
      echo "<CustomerID>Web ".$customer['country']."</CustomerID>\n\r";
      echo "<ContactName>Mooncup ".$customer['country']."</ContactName>\n\r";
      echo "<Address1>Web Sales</Address1>\n\r";
      echo "<Town>Web Site</Town>\n\r";
      echo "<CountryCode>".$customer['country']."</CountryCode>\n\r";
  echo "</Customers>\n\r";
    //</Customers>

  $orders = $xmlData->setOrders($country);
    //<Orders>
  foreach ($orders as $order):
      $orderid = $order['currency'];
      echo "<Orders>";
      echo "<CustomerID>Web ".$order['country']."</CustomerID>\n\r";
      echo "<OrderID>".$order['country'].' - '.$order['currency']."</OrderID>\n\r";
      echo "<OrderDate>".date('M-Y',strtotime($order['date']))."</OrderDate>\n\r";
      echo "</Orders>\n\r";
      // </Orders>
      $orderproducts = $xmlData->setProducts($orderid);
      // <OrderItems>
      foreach ($orderproducts as $orderproduct):
            echo "<OrderItems>";
            echo "<OrderID>".$order['country'].' - '.$orderproduct['currency']."</OrderID>\n\r";
            echo "<Description>".$orderproduct['description']." - ".$orderproduct['sku']."</Description>\n\r";
              if($orderproduct['currency'] === 'GBP' && $order['country'] == 'GB'):
                echo "<Price>".($orderproduct['line_total'])."</Price>\n\r";
                echo "<TaxCode>5</TaxCode>";
               elseif($orderproduct['currency'] === 'GBP' && $order['country'] != 'GB'):
                echo "<Price>".($orderproduct['line_total'])."</Price>\n\r";
                echo "<TaxCode>0</TaxCode>";
              elseif($orderproduct['currency'] === 'EUR'):
                echo "<Price>".($orderproduct['line_total'])."</Price>\n\r";
                echo "<TaxCode>4</TaxCode>";
              else:
                echo "<Price>".($orderproduct['line_total'])."</Price>\n\r";
                echo "<TaxCode>0</TaxCode>";
              endif;
            echo "<ProductCode>".$orderproduct['sku']."</ProductCode>\n\r";
            echo "<Quantity>".$orderproduct['qty']."</Quantity>\n\r";
          $orderitems = $xmlData->setItems($orderid);
          foreach ($orderitems as $orderitem):
              echo "<OrderItems>";
              echo "<OrderID>".$order['country'].' - '.$orderproduct['currency']."</OrderID>\n\r";
              echo "<Description>".$orderitem['user_guide']." - ".substr($orderitem['sku'],0,-1)."</Description>\n\r";
              echo "<Price>0</Price>\n\r";
              echo "<ProductCode>".substr($orderitem['sku'],0,-1)."</ProductCode>\n\r";
              echo "<Quantity>".$orderitem['qty']."</Quantity>\n\r";
              echo "</OrderItems>\n\r";
          endforeach;
            echo "</OrderItems>\n\r";


      endforeach;
  endforeach;
endforeach;
echo "</DsOrders>";

