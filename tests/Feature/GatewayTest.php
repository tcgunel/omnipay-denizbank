<?php

namespace Omnipay\Denizbank\Tests\Feature;

use Omnipay\Denizbank\Message\CompletePurchaseRequest;
use Omnipay\Denizbank\Message\PurchaseRequest;
use Omnipay\Denizbank\Message\RefundRequest;
use Omnipay\Denizbank\Message\VoidRequest;
use Omnipay\Denizbank\Tests\TestCase;

class GatewayTest extends TestCase
{
    public function test_gateway_name(): void
    {
        self::assertSame('Denizbank', $this->gateway->getName());
    }

    public function test_gateway_default_parameters(): void
    {
        $defaults = $this->gateway->getDefaultParameters();

        self::assertArrayHasKey('merchantId', $defaults);
        self::assertArrayHasKey('merchantUser', $defaults);
        self::assertArrayHasKey('merchantPassword', $defaults);
        self::assertArrayHasKey('merchantStorekey', $defaults);
        self::assertArrayHasKey('installment', $defaults);
        self::assertArrayHasKey('secure', $defaults);
    }

    public function test_gateway_purchase(): void
    {
        $request = $this->gateway->purchase([
            'amount' => '100.00',
        ]);

        self::assertInstanceOf(PurchaseRequest::class, $request);
    }

    public function test_gateway_complete_purchase(): void
    {
        $request = $this->gateway->completePurchase([
            'amount' => '100.00',
        ]);

        self::assertInstanceOf(CompletePurchaseRequest::class, $request);
    }

    public function test_gateway_void(): void
    {
        $request = $this->gateway->void([
            'orderNumber' => 'ORDER-12345',
        ]);

        self::assertInstanceOf(VoidRequest::class, $request);
    }

    public function test_gateway_refund(): void
    {
        $request = $this->gateway->refund([
            'orderNumber' => 'ORDER-12345',
            'amount' => '50.00',
        ]);

        self::assertInstanceOf(RefundRequest::class, $request);
    }
}
