<?php
require_once('TrialSage.php');
echo "<?xml version=\"1.0\" standalone=\"yes\" ?>\n";
echo "<DsOrders xmlns=\"http://www.tempuri.org/DsOrderInfo.xsd\">\n";
$xmlData = new TrialSage();

// Customer loop by location ie GBWebCustomers
$customers = $xmlData->readCustomers();
foreach ($customers as $customer):
  echo "<Customers>\n\r";
      echo "<CustomerID>".$customer['country']."WebOrders</CustomerID>\n\r";
      echo "<ContactName>Mooncup UK</ContactName>\n\r";
      echo "<Address1>Web Sales</Address1>\n\r";
      echo "<Town>Web Site</Town>\n\r";
      echo "<CountryCode>".$customer['country']."</CountryCode>\n\r";
  echo "</Customers>\n\r";
  $country = $customer['country'];

  $orders = $xmlData->readOrders($country);
  foreach ($orders as $order):
      echo "<Orders>";
      echo "<CustomerID>Web".$order['country']."</CustomerID>\n\r";
      echo "<OrderID>".$order['currency']."</OrderID>\n\r";
      echo "<OrderDate>".date('M-Y',strtotime($order['date']))."</OrderDate>\n\r";
      echo "</Orders>\n\r";
      $orderid = $order['currency'];
			
			$orderitems = $xmlData->readOrders($country);
      foreach ($orderitems as $orderitem):
				echo "<OrderItems>";
				echo "<OrderID>".$orderitem['currency']."</OrderID>\n\r";
				echo "<Description>".$orderitem['description']." - ".$orderitem['user_guide']." - ".$orderitem['sku']."</Description>\n\r";
				echo "<Price>".$orderitem['line_total']."</Price>\n\r";
				echo "<ProductCode>".$orderitem['variation_id']."</ProductCode>\n\r";
				echo "<Quantity>".$orderitem['qty']."</Quantity>\n\r";
				echo "</OrderItems>\n\r";
      endforeach;
  endforeach;
endforeach;
echo "</DsOrders>";
 ?>
