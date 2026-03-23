<?php

namespace Omnipay\Denizbank\Tests\Feature;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Denizbank\Constants\SecureType;
use Omnipay\Denizbank\Constants\TxnType;
use Omnipay\Denizbank\Message\VoidRequest;
use Omnipay\Denizbank\Message\VoidResponse;
use Omnipay\Denizbank\Tests\TestCase;

class VoidTest extends TestCase
{
    /**
     * @throws InvalidRequestException
     * @throws \JsonException
     */
    public function test_void_request(): void
    {
        $options = file_get_contents(__DIR__ . '/../Mock/VoidRequest.json');

        $options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

        $request = new VoidRequest($this->getHttpClient(), $this->getHttpRequest());

        $request->initialize($options);

        $data = $request->getData();

        self::assertSame('DenizShop001', $data['ShopCode']);
        self::assertSame('DenizUser', $data['UserCode']);
        self::assertSame('DenizPass123', $data['UserPass']);
        self::assertSame(TxnType::VOID, $data['TxnType']);
        self::assertSame('ORDER-12345', $data['orgOrderId']);
        self::assertSame(SecureType::NON_SECURE, $data['SecureType']);
        self::assertSame('TR', $data['Lang']);
    }

    public function test_void_request_validation_error(): void
    {
        $options = file_get_contents(__DIR__ . '/../Mock/VoidRequest-ValidationError.json');

        $options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

        $request = new VoidRequest($this->getHttpClient(), $this->getHttpRequest());

        $request->initialize($options);

        $this->expectException(InvalidRequestException::class);

        $request->getData();
    }

    public function test_void_response_success(): void
    {
        $httpResponse = $this->getMockHttpResponse('VoidResponseSuccess.txt');

        $response = new VoidResponse($this->getMockRequest(), (string) $httpResponse->getBody());

        self::assertTrue($response->isSuccessful());
        self::assertSame('DnzVoid22302001', $response->getTransactionReference());
        self::assertSame('00', $response->getCode());
    }

    public function test_void_response_error(): void
    {
        $httpResponse = $this->getMockHttpResponse('VoidResponseError.txt');

        $response = new VoidResponse($this->getMockRequest(), (string) $httpResponse->getBody());

        self::assertFalse($response->isSuccessful());
        self::assertSame('05', $response->getCode());
        self::assertSame('Iptal edilecek islem bulunamadi', $response->getMessage());
    }
}
