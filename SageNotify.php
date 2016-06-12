<?php
$hostname = '188.166.159.243';
$username = 'mooncup';
$password = 'Edw4rd6s';
$database = 'mooncup';
try {
    $dbh = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
    if($_POST["OrderIDString"] != "")
    {
        $order_numbers = (array) $_POST["OrderIDString"];
        foreach($order_numbers as $order_number):
            $sql = "UPDATE `wp_posts` SET `post_content` = 1 WHERE `ID` = :value";
            $update = $dbh->prepare($sql);
            $update->bindParam(':value',$order_number);
            $update->execute();
        endforeach;
    }
    }
    catch(PDOException $e)
    {
        echo $e->getMessage();
    }
?>

