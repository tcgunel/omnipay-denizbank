<?php

namespace Omnipay\Denizbank\Message;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\AbstractRequest;
use Omnipay\Denizbank\Traits\PurchaseGettersSetters;

abstract class RemoteAbstractRequest extends AbstractRequest
{
	use PurchaseGettersSetters;

	/** @var string */
	protected $endpoint = '';

	/**
	 * @throws InvalidRequestException
	 */
	protected function validateSettings(): void
	{
		$this->validate('merchantId', 'merchantUser', 'merchantPassword');
	}

	/**
	 * Get the API endpoint URL based on test mode.
	 *
	 * @return string
	 */
	public function getApiUrl(): string
	{
		if ($this->getTestMode()) {
			return 'https://test.inter-vpos.com.tr/mpi/Default.aspx';
		}

		return 'https://inter-vpos.com.tr/mpi/Default.aspx';
	}

	/**
	 * Post form-encoded data to the Interbank VPos API.
	 *
	 * @param array<string, mixed> $data
	 * @return string
	 */
	protected function postForm(array $data): string
	{
		$httpResponse = $this->httpClient->request(
			'POST',
			$this->getApiUrl(),
			[
				'Content-Type' => 'application/x-www-form-urlencoded',
			],
			http_build_query($data)
		);

		return (string) $httpResponse->getBody();
	}

	/**
	 * Get card attribute safely.
	 *
	 * @param string $key
	 * @return mixed
	 */
	protected function getCardAttribute(string $key)
	{
		return $this->getCard() ? $this->getCard()->$key() : null;
	}

	abstract protected function createResponse($data);
}
