<?php

namespace Omnipay\Denizbank\Tests\Feature;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Denizbank\Constants\SecureType;
use Omnipay\Denizbank\Constants\TxnType;
use Omnipay\Denizbank\Message\RefundRequest;
use Omnipay\Denizbank\Message\RefundResponse;
use Omnipay\Denizbank\Tests\TestCase;

class RefundTest extends TestCase
{
	/**
	 * @throws InvalidRequestException
	 * @throws \JsonException
	 */
	public function test_refund_request(): void
	{
		$options = file_get_contents(__DIR__ . '/../Mock/RefundRequest.json');

		$options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

		$request = new RefundRequest($this->getHttpClient(), $this->getHttpRequest());

		$request->initialize($options);

		$data = $request->getData();

		self::assertSame('DenizShop001', $data['ShopCode']);
		self::assertSame('DenizUser', $data['UserCode']);
		self::assertSame('DenizPass123', $data['UserPass']);
		self::assertSame(TxnType::REFUND, $data['TxnType']);
		self::assertSame('ORDER-12345', $data['orgOrderId']);
		self::assertSame('50.00', $data['PurchAmount']);
		self::assertSame('949', $data['Currency']);
		self::assertSame(SecureType::NON_SECURE, $data['SecureType']);
		self::assertSame('TR', $data['Lang']);
	}

	public function test_refund_request_validation_error(): void
	{
		$options = file_get_contents(__DIR__ . '/../Mock/RefundRequest-ValidationError.json');

		$options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

		$request = new RefundRequest($this->getHttpClient(), $this->getHttpRequest());

		$request->initialize($options);

		$this->expectException(InvalidRequestException::class);

		$request->getData();
	}

	public function test_refund_response_success(): void
	{
		$httpResponse = $this->getMockHttpResponse('RefundResponseSuccess.txt');

		$response = new RefundResponse($this->getMockRequest(), (string) $httpResponse->getBody());

		self::assertTrue($response->isSuccessful());
		self::assertSame('DnzRef22302001', $response->getTransactionReference());
		self::assertSame('00', $response->getCode());
	}

	public function test_refund_response_error(): void
	{
		$httpResponse = $this->getMockHttpResponse('RefundResponseError.txt');

		$response = new RefundResponse($this->getMockRequest(), (string) $httpResponse->getBody());

		self::assertFalse($response->isSuccessful());
		self::assertSame('05', $response->getCode());
		self::assertSame('Iade edilecek islem bulunamadi', $response->getMessage());
	}
}
