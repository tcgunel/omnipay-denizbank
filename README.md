# Omnipay: Denizbank (Interbank VPos)

**Denizbank Interbank VPos gateway for the Omnipay PHP payment processing library**

[Omnipay](https://github.com/thephpleague/omnipay) is a framework agnostic, multi-gateway payment
processing library for PHP. This package implements Denizbank Interbank VPos support for Omnipay.

## Installation

```bash
composer require tcgunel/omnipay-denizbank
```

## Available Methods

| Method | Description |
|--------|-------------|
| `purchase()` | Direct (non-3D) sale or 3D Secure redirect |
| `completePurchase()` | Complete 3D Secure payment after bank callback |
| `void()` | Cancel/void a transaction |
| `refund()` | Refund a transaction (full or partial) |

## Supported Features

| Feature | Supported |
|---------|-----------|
| 3D Secure | Yes |
| Non-3D (direct) | Yes |
| Cancel (void) | Yes |
| Refund | Yes |
| BIN lookup | No |
| Installment query | No |
| Sale query | No |

## Usage

### Gateway Initialization

```php
use Omnipay\Omnipay;

$gateway = Omnipay::create('Denizbank');

$gateway->setMerchantId('your_shop_code');
$gateway->setMerchantUser('your_user_code');
$gateway->setMerchantPassword('your_user_password');
$gateway->setMerchantStorekey('your_store_key'); // Required for 3D Secure
$gateway->setTestMode(true); // Use test endpoint
```

### Non-3D Purchase (Direct Sale)

```php
$response = $gateway->purchase([
    'amount'      => '100.00',
    'currency'    => 'TRY',
    'transactionId' => 'ORDER-12345',
    'secure'      => false,
    'card'        => [
        'number'      => '4508034508034509',
        'expiryMonth' => '12',
        'expiryYear'  => '2030',
        'cvv'         => '000',
    ],
])->send();

if ($response->isSuccessful()) {
    echo 'Transaction ID: ' . $response->getTransactionReference();
} else {
    echo 'Error: ' . $response->getMessage();
}
```

### 3D Secure Purchase

```php
$response = $gateway->purchase([
    'amount'      => '100.00',
    'currency'    => 'TRY',
    'transactionId' => 'ORDER-12345',
    'secure'      => true,
    'returnUrl'   => 'https://yoursite.com/payment/success',
    'cancelUrl'   => 'https://yoursite.com/payment/fail',
    'card'        => [
        'number'      => '4508034508034509',
        'expiryMonth' => '12',
        'expiryYear'  => '2030',
        'cvv'         => '000',
    ],
])->send();

if ($response->isRedirect()) {
    $response->redirect(); // POSTs card data to the bank 3D Secure page
}
```

### Complete 3D Secure Purchase (Callback Handler)

After the bank posts back to your `returnUrl`:

```php
$response = $gateway->completePurchase([])->send();

if ($response->isSuccessful()) {
    echo 'Payment confirmed! Transaction: ' . $response->getTransactionReference();
} else {
    echo 'Payment failed: ' . $response->getMessage();
}
```

The callback POST fields (`ProcReturnCode`, `TransId`, `OrderId`, `ErrorMessage`) are read
directly from `$_POST` by the request internally — no manual mapping is required.

### Void (Cancel)

```php
$response = $gateway->void([
    'orderNumber' => 'ORDER-12345', // original order ID used at purchase time
])->send();

if ($response->isSuccessful()) {
    echo 'Transaction voided.';
} else {
    echo 'Error: ' . $response->getMessage();
}
```

### Refund

```php
$response = $gateway->refund([
    'orderNumber' => 'ORDER-12345', // original order ID used at purchase time
    'amount'      => '50.00',
    'currency'    => 'TRY',
])->send();

if ($response->isSuccessful()) {
    echo 'Refund processed.';
} else {
    echo 'Error: ' . $response->getMessage();
}
```

## Test Credentials

| Setting | Value |
|---------|-------|
| Test URL | `https://test.inter-vpos.com.tr/mpi/Default.aspx` |
| Production URL | `https://inter-vpos.com.tr/mpi/Default.aspx` |

Test credentials (shop code, user code, user password, and store key) must be obtained directly
from Denizbank by signing a merchant agreement. There is no shared public test account.

## Not Available

The following features are **not** provided by the Interbank VPos API and are therefore not
implemented in this package:

- BIN lookup (card issuer query)
- Installment options query
- Sale / order status query

## Running Tests

```bash
composer test
```

## Static Analysis

```bash
composer analyse
```

## Code Style

```bash
composer lint
```

## License

MIT License. See [LICENSE](LICENSE) for details.
