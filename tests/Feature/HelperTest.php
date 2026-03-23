<?php

namespace Omnipay\Denizbank\Tests\Feature;

use Omnipay\Denizbank\Helpers\Helper;
use Omnipay\Denizbank\Tests\TestCase;

class HelperTest extends TestCase
{
	public function test_parse_response_success(): void
	{
		$raw = 'ProcReturnCode=00;;TransId=DnzTxn22302001;;OrderId=ORDER-12345;;ErrorMessage=;;';

		$result = Helper::parseResponse($raw);

		self::assertSame('00', $result['ProcReturnCode']);
		self::assertSame('DnzTxn22302001', $result['TransId']);
		self::assertSame('ORDER-12345', $result['OrderId']);
		self::assertSame('', $result['ErrorMessage']);
	}

	public function test_parse_response_error(): void
	{
		$raw = 'ProcReturnCode=05;;TransId=;;OrderId=ORDER-12345;;ErrorMessage=Genel red - Kart numarasi hatali;;';

		$result = Helper::parseResponse($raw);

		self::assertSame('05', $result['ProcReturnCode']);
		self::assertSame('', $result['TransId']);
		self::assertSame('ORDER-12345', $result['OrderId']);
		self::assertSame('Genel red - Kart numarasi hatali', $result['ErrorMessage']);
	}

	public function test_parse_response_empty(): void
	{
		$result = Helper::parseResponse('');

		self::assertSame([], $result);
	}

	public function test_hash_3d(): void
	{
		$hash = Helper::hash3D(
			'DenizShop001',
			'ORDER-12345',
			'100.00',
			'https://example.com/success',
			'https://example.com/fail',
			'Auth',
			'0',
			'test-rnd-value',
			'DenizStoreKey456',
		);

		// Verify it's a base64 string
		self::assertNotEmpty($hash);
		self::assertSame(base64_encode(base64_decode($hash, true)), $hash);

		// Verify deterministic
		$hash2 = Helper::hash3D(
			'DenizShop001',
			'ORDER-12345',
			'100.00',
			'https://example.com/success',
			'https://example.com/fail',
			'Auth',
			'0',
			'test-rnd-value',
			'DenizStoreKey456',
		);

		self::assertSame($hash, $hash2);
	}

	public function test_hash_3d_matches_expected(): void
	{
		// Manually compute the expected hash
		$hashString = 'DenizShop001' . 'ORDER-12345' . '100.00'
			. 'https://example.com/success' . 'https://example.com/fail'
			. 'Auth' . '0' . 'test-rnd-value' . 'DenizStoreKey456';

		$expected = base64_encode(sha1($hashString, true));

		$actual = Helper::hash3D(
			'DenizShop001',
			'ORDER-12345',
			'100.00',
			'https://example.com/success',
			'https://example.com/fail',
			'Auth',
			'0',
			'test-rnd-value',
			'DenizStoreKey456',
		);

		self::assertSame($expected, $actual);
	}
}
