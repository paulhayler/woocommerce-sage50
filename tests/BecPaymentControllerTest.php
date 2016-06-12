<?php
require_once('BECPaymentController.php');
/**
 * Created by PhpStorm.
 * User: FreshRecipe
 * Date: 08/06/2016
 * Time: 11:47
 */
class BecPaymentControllerTest extends PHPUnit_Framework_TestCase
{
    public $id;
    public function __construct(){
        // Check id loaded
        $this->id = new BecPaymentController();
        $this->assertEquals('6571770ca951fa0e',$this->id->key );
    }
    public function testEncryptedSet(){
        $paymentString = "Amount=99&VendorTxCode=".date('mdY')."-123457&Currency=GBP&Description=123457&CustomerName=Tom Dick&BillingFirstnames=Tom&BillingSurname=Dick&CustomerEMail=test@here.com&BillingAddress1=Street&BillingCity=Town&BillingPostcode=Postcode&BillingCountry=GB&DeliveryCountry=GB&DeliveryFirstnames=First Name&DeliverySurname=Surname&DeliveryAddress1=Street&DeliveryCity=City&DeliveryPostcode=Postcode&SuccessURL=http://www.brightonexecutivecars.co.uk/payments/success.php&FailureURL=http://www.brightonexecutivecars.co.uk/payments/failure.php";
        $encPaymentString = \SagepayUtil::encryptAes($paymentString, $this->id->key);
        $decPaymentString = \SagepayUtil::decryptAes($encPaymentString, $this->id->key);
        $this->assertEquals($paymentString,$decPaymentString );
    }
}
