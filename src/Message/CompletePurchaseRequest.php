<?php

namespace Omnipay\Denizbank\Message;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\ResponseInterface;

class CompletePurchaseRequest extends RemoteAbstractRequest
{
	/**
	 * Return the callback data from the 3D redirect as-is.
	 *
	 * The callback POST contains ProcReturnCode, TransId, OrderId, etc.
	 *
	 * @throws InvalidRequestException
	 * @return array<string, mixed>
	 */
	public function getData(): array
	{
		$this->validateAll();

		return [
			'ProcReturnCode' => $this->getTransactionReference() !== null
				? $this->httpRequest->request->get('ProcReturnCode')
				: $this->httpRequest->request->get('ProcReturnCode'),
			'TransId' => $this->httpRequest->request->get('TransId'),
			'OrderId' => $this->httpRequest->request->get('OrderId'),
			'ErrorMessage' => $this->httpRequest->request->get('ErrorMessage', ''),
		];
	}

	/**
	 * @throws InvalidRequestException
	 */
	protected function validateAll(): void
	{
		$this->validateSettings();
	}

	/**
	 * @param array<string, mixed> $data
	 * @return ResponseInterface|CompletePurchaseResponse
	 */
	public function sendData($data)
	{
		return $this->createResponse($data);
	}

	/**
	 * @param array<string, mixed> $data
	 * @return CompletePurchaseResponse
	 */
	protected function createResponse($data): CompletePurchaseResponse
	{
		return $this->response = new CompletePurchaseResponse($this, $data);
	}
}
