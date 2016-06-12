<?php
require_once('vendor/autoload.php');
use GuzzleHttp\Client;
/**
 * Created by PhpStorm.
 * User: Paul Hayler
 * Date: 20/11/2015
 * Time: 10:14
 */
class SageCurl
{
    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'http://localhost:5493/sdata/accounts50/gcrm/-/',
            'auth' => ['manager', ''],
            'headers' => [
                'Accept' => 'application/atom+xml',
                'Content-Type' => 'application/atom+xml',
                'Host' => 'host'
            ]
        ]);

    }
    public function sageReadAccounts($type)
    {
        $response = $this->client->request('GET',$type);
        $xml = simplexml_load_string($response->getBody());
        $xml->registerXPathNamespace('sdata', 'http://schemas.sage.com/sdata/sync/2008/1');
        foreach($xml->xpath("//sdata:payload") as $entry) {
            $accounts = $entry->xpath("./crm:tradingAccount");
            foreach($accounts as $a) {
                $nodes = $a->children('crm', true);
                echo "<li>".$nodes->reference . " | " . $nodes->name . " | " . $nodes->openedDate . "</li>";
            }
        }
    }
    public function sageReadOrders($type)
    {
        $response = $this->client->request('GET',$type);
        $xml = simplexml_load_string($response->getBody());
        $xml->registerXPathNamespace('sdata', 'http://schemas.sage.com/sdata/sync/2008/1');
        foreach($xml->xpath("//sdata:payload") as $entry) {
            $content = $entry->xpath("./crm:salesOrder");
            foreach($content as $c) {
                // Make set of children with prefix crm
                $nodes = $c->children('crm', true);
                echo "<li>".$nodes->CustomerId . " | " .$nodes->grossTotal . " | " . $nodes->date . " </li>";
            }
        }
    }
    public function sageReadOrderLines($type)
    {
        $response = $this->client->request('GET',$type);
        $xml = simplexml_load_string($response->getBody());
        $xml->registerXPathNamespace('sdata', 'http://schemas.sage.com/sdata/sync/2008/1');
        foreach($xml->xpath("//sdata:payload") as $entry) {
            $content = $entry->xpath("./crm:salesOrderLine");
            foreach($content as $c) {
                $nodes = $c->children('crm', true);
                echo "<li>".$nodes->text . " | " .$nodes->netTotal . " | " . $nodes->grossTotal . " </li>";
            }
        }
    }


}
$data = new SageCurl();
echo "<p>Customers:</p>";
echo "<ul>";
$data->sageReadAccounts('TradingAccounts');
echo "</ul>";
echo "<p>Customer Orders:</p>";
echo "<ul>";
$data->sageReadOrders('SalesOrders');
echo "</ul>";
echo "<p>Products Ordered:</p>";
echo "<ul>";
$data->sageReadOrderLines('SalesOrderLines');
echo "</ul>";