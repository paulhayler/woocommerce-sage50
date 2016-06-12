<?php
require_once('Ia/Sdata/Conn.php');

require_once('Ia/Sdata/Schema.php');
require_once('Ia/Sdata/Query/Type/Collection.php');

require_once('Ia/Sdata/Query/Type/Delete.php');

require_once('Ia/Sdata/Query/Type/Update.php');
use \Ia\Sdata\Conn;

use \Ia\Sdata\Schema;
use \Ia\Sdata\Query\Type\Collection;

use \Ia\Sdata\Query\Type\Delete;

use \Ia\Sdata\Query\Type\Update;

/**
 * Created by PhpStorm.
 * User: Paul Hayler
 * Date: 20/11/2015
 * Time: 08:34
 */
class SageLink
{
    public function __construct()
    {
        $this->sdata = new Conn(array(

            'hostname'=>'http://freshrecipe:5493',
            'username'=>'manager',
            'password'=>'',
            'company'=>'-'
        ));
    }

    public function sageRetrieveCollection($currkind)
    {
        try{
            $query = new Collection($this->sdata);
            $query->setResourceKind($currkind);
            $query->setQueryParam('count',1);
            $query->setQueryParam('startIndex',1);
            $this->entries = $query->getEntries();
            return $this->entries;
        } catch (Exception $e) {
            echo 'Exception: '.$e->getMessage();
            exit;
        }

    }
    public function sageCreateSale()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_URL, "http://localhost:5493/sdata/accounts50/GCRM/-/SalesInvoice");
        curl_setopt($ch, CURLOPT_POST, 1);
        $xmlcontent = file_get_contents('orders.xml');
        curl_setopt($ch, CURLOPT_POSTFIELDS, "XML=".$xmlcontent."&username=manager&password=");
        $content=curl_exec($ch);
    }
}
$data = new SageLink();
var_dump($data->sageRetrieveCollection('TradingAccountSummaries'));
$data->sageCreateSale();