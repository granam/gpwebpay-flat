## Parser of a report from GpWebPay in FLAT format

# Security first
NEVER download this library from an unknown source. Use ONLY [packagist.org](https://packagist.org) (therefore [composer](https://composer.org)), or [github.com/jaroslavtyc/granam-gpwebpay-flat](github.com/jaroslavtyc/granam-gpwebpay-flat).

You are going to work with financial data, which are ALWAYS sensitive. Think twice if a public library to process them is OK for your company inner politic.

This library is released under [MIT licence](./LICENCE), which means any harm caused to you by using it is your fight. But I will do my best to protect you from any data leakage or corruption.

# Step-by-step

- send a request to [helpdesk@globalpayments.cz](helpdesk@globalpayments.cz) to get daily reports of transactions in **FLAT** format to an email of your choice
- add this library to your project ```composer require granam/gpwebpay-flat```
- let it to parse FLAT report
```php
<?php
namespace Coolest\Fan;

use Granam\GpWebPay\Flat\CzechECommerceTransactionHeaderMapper;
use Granam\GpWebPay\Flat\FlatReportParser;

$flatReportParser = new FlatReportParser();
$flatContentFromCzechFile = $flatReportParser->createFlatContentFromCzechFile(
    __DIR__ . '/Granam/Documentations/cs/Vzor FLAT.txt',
    new CzechECommerceTransactionHeaderMapper()
);
$eCommerceTransactions = $flatContentFromCzechFile->getECommerceTransactions();
echo 'We got '.$eCommerceTransactions->count().' of new purchases via GpWebPay gateway!';

```
- verify that you have not missed a payment from a customer (what a shame!)
```php
<?php
// ...
/** @var \Granam\GpWebPay\Flat\Sections\ECommerceTransactions $eCommerceTransactions */
$expectedIncome = require __DIR__ . '/expected_income.php';
if ($expectedIncome !== $eCommerceTransactions->getPaidAmountWithoutFeesSummary()) {
    throw new \RuntimeException(
        "We have missing (or redundant) GpWebPay payments! Expected {$expectedIncome}, got ". $eCommerceTransactions->getPaidAmountWithoutFeesSummary()
    );
}

echo 'Our customers spent '.$eCommerceTransactions->getPaidAmountWithoutFeesSummary().'.-, we paid '
.$eCommerceTransactions->getFeesInMerchantCurrencySummary().' to GpWebPay as fee'
.' and we got '.$eCommerceTransactions->getPaidAmountWithoutFeesSummary();
```
- be happy! (or at least less sad)
