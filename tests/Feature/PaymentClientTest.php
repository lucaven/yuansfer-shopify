<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Yuansfer\Exception\YuansferException;
use Yuansfer\Yuansfer;

class PaymentClientTest extends TestCase
{


    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testExample()
    {
        echo "Hello World\n";
        $config = array(
            Yuansfer::MERCHANT_NO => '200043',
            Yuansfer::STORE_NO => '300014',
            Yuansfer::API_TOKEN => 'None',
            Yuansfer::TEST_API_TOKEN => '5cbfb079f15b150122261c8537086d77a',
        );

        $yuansfer = new Yuansfer($config);
        $yuansfer->setTestMode();
        echo "Creating secure payment...\n";
        $api = $yuansfer->createSecurePay();
        echo "Created!\n";

        $api->setAmount(9.9)
            ->setCurrency('USD')
            ->setVendor('alipay')
            ->setTerminal('ONLINE')
            ->setReference('9999')
            ->setIpnUrl(env("APP_URL")."/callback/ipn")
            ->setCallbackUrl(env("APP_URL")."/callback")

        ;


        try {
            $response = $api->send();
            var_dump($response);
        } catch (YuansferException $e) {
            echo "{$e->getMessage()}\n";
            echo "{$e->getTraceAsString()}\n";
        }
    }
}
