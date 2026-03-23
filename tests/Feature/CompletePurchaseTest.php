<?php

namespace Omnipay\Denizbank\Tests\Feature;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Denizbank\Message\CompletePurchaseRequest;
use Omnipay\Denizbank\Message\CompletePurchaseResponse;
use Omnipay\Denizbank\Tests\TestCase;

class CompletePurchaseTest extends TestCase
{
	/**
	 * @throws InvalidRequestException
	 * @throws \JsonException
	 */
	public function test_complete_purchase_request(): void
	{
		$options = file_get_contents(__DIR__ . '/../Mock/CompletePurchaseRequest.json');

		$options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

		$this->getHttpRequest()->request->replace([
			'ProcReturnCode' => '00',
			'TransId' => 'DnzTxn22302001',
			'OrderId' => 'ORDER-12345',
			'ErrorMessage' => '',
		]);

		$request = new CompletePurchaseRequest($this->getHttpClient(), $this->getHttpRequest());

		$request->initialize($options);

		$data = $request->getData();

		self::assertSame('00', $data['ProcReturnCode']);
		self::assertSame('DnzTxn22302001', $data['TransId']);
		self::assertSame('ORDER-12345', $data['OrderId']);
		self::assertSame('', $data['ErrorMessage']);
	}

	public function test_complete_purchase_request_validation_error(): void
	{
		$options = file_get_contents(__DIR__ . '/../Mock/CompletePurchaseRequest-ValidationError.json');

		$options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

		$request = new CompletePurchaseRequest($this->getHttpClient(), $this->getHttpRequest());

		$request->initialize($options);

		$this->expectException(InvalidRequestException::class);

		$request->getData();
	}

	public function test_complete_purchase_response_success(): void
	{
		$data = [
			'ProcReturnCode' => '00',
			'TransId' => 'DnzTxn22302001',
			'OrderId' => 'ORDER-12345',
			'ErrorMessage' => '',
		];

		$response = new CompletePurchaseResponse($this->getMockRequest(), $data);

		self::assertTrue($response->isSuccessful());
		self::assertSame('DnzTxn22302001', $response->getTransactionReference());
		self::assertSame('00', $response->getCode());
	}

	public function test_complete_purchase_response_error(): void
	{
		$data = [
			'ProcReturnCode' => '99',
			'TransId' => '',
			'OrderId' => 'ORDER-12345',
			'ErrorMessage' => '3D Dogrulama Hatasi',
		];

		$response = new CompletePurchaseResponse($this->getMockRequest(), $data);

		self::assertFalse($response->isSuccessful());
		self::assertSame('99', $response->getCode());
		self::assertSame('3D Dogrulama Hatasi', $response->getMessage());
	}
}
