<?php

namespace Omnipay\Denizbank\Tests\Feature;

use Omnipay\Common\Exception\InvalidCreditCardException;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Denizbank\Constants\SecureType;
use Omnipay\Denizbank\Constants\TxnType;
use Omnipay\Denizbank\Message\PurchaseRequest;
use Omnipay\Denizbank\Message\PurchaseResponse;
use Omnipay\Denizbank\Tests\TestCase;

class PurchaseTest extends TestCase
{
    /**
     * @throws InvalidRequestException
     * @throws InvalidCreditCardException
     * @throws \JsonException
     */
    public function test_purchase_request(): void
    {
        $options = file_get_contents(__DIR__ . '/../Mock/PurchaseRequest.json');

        $options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

        $request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());

        $request->initialize($options);

        $data = $request->getData();

        self::assertSame('DenizShop001', $data['ShopCode']);
        self::assertSame('DenizUser', $data['UserCode']);
        self::assertSame('DenizPass123', $data['UserPass']);
        self::assertSame('100.00', $data['PurchAmount']);
        self::assertSame('949', $data['Currency']);
        self::assertSame('ORDER-12345', $data['OrderId']);
        self::assertSame(TxnType::AUTH, $data['TxnType']);
        self::assertSame('0', $data['InstallmentCount']);
        self::assertSame(SecureType::NON_SECURE, $data['SecureType']);
        self::assertSame('4355084355084358', $data['Pan']);
        self::assertSame('000', $data['Cvv2']);
        self::assertSame('1230', $data['Expiry']);
        self::assertSame('TR', $data['Lang']);
    }

    public function test_purchase_request_with_installment(): void
    {
        $options = file_get_contents(__DIR__ . '/../Mock/PurchaseRequest.json');

        $options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

        $options['installment'] = '3';

        $request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());

        $request->initialize($options);

        $data = $request->getData();

        self::assertSame('3', $data['InstallmentCount']);
    }

    public function test_purchase_request_validation_error(): void
    {
        $options = file_get_contents(__DIR__ . '/../Mock/PurchaseRequest-ValidationError.json');

        $options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

        $request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());

        $request->initialize($options);

        $this->expectException(InvalidRequestException::class);

        $request->getData();
    }

    public function test_purchase_3d_request(): void
    {
        $options = file_get_contents(__DIR__ . '/../Mock/PurchaseRequest3D.json');

        $options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

        $request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());

        $request->initialize($options);

        $data = $request->getData();

        self::assertSame('DenizShop001', $data['ShopCode']);
        self::assertSame('100.00', $data['PurchAmount']);
        self::assertSame('949', $data['Currency']);
        self::assertSame('ORDER-12345', $data['OrderId']);
        self::assertSame(TxnType::AUTH, $data['TxnType']);
        self::assertSame('0', $data['InstallmentCount']);
        self::assertSame(SecureType::THREE_D_PAY, $data['SecureType']);
        self::assertSame('4355084355084358', $data['Pan']);
        self::assertSame('000', $data['Cvv2']);
        self::assertSame('1230', $data['Expiry']);
        self::assertSame('TR', $data['Lang']);
        self::assertSame('https://example.com/payment/success', $data['OkUrl']);
        self::assertSame('https://example.com/payment/fail', $data['FailUrl']);
        self::assertNotEmpty($data['Rnd']);
        self::assertNotEmpty($data['Hash']);
    }

    public function test_purchase_3d_response_is_redirect(): void
    {
        $options = file_get_contents(__DIR__ . '/../Mock/PurchaseRequest3D.json');

        $options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

        $request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());

        /** @var PurchaseResponse $response */
        $response = $request->initialize($options)->send();

        self::assertFalse($response->isSuccessful());
        self::assertTrue($response->isRedirect());
        self::assertSame('POST', $response->getRedirectMethod());
        self::assertNotEmpty($response->getRedirectUrl());
        self::assertNotEmpty($response->getRedirectData());
    }

    public function test_purchase_response_success(): void
    {
        $httpResponse = $this->getMockHttpResponse('PurchaseResponseSuccess.txt');

        $response = new PurchaseResponse($this->getMockRequest(), (string) $httpResponse->getBody());

        self::assertTrue($response->isSuccessful());
        self::assertFalse($response->isRedirect());
        self::assertSame('DnzTxn22302001', $response->getTransactionReference());
        self::assertSame('00', $response->getCode());
    }

    public function test_purchase_response_error(): void
    {
        $httpResponse = $this->getMockHttpResponse('PurchaseResponseError.txt');

        $response = new PurchaseResponse($this->getMockRequest(), (string) $httpResponse->getBody());

        self::assertFalse($response->isSuccessful());
        self::assertSame('05', $response->getCode());
        self::assertSame('Genel red - Kart numarasi hatali', $response->getMessage());
    }
}
