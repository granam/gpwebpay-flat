<?php
declare(strict_types=1); // on PHP 7+ are standard PHP methods strict to types of given parameters

namespace Granam\GpWebPay\Flat;

use Granam\Strict\Object\StrictObject;

abstract class ECommerceTransactionHeaderMapper extends StrictObject
{
    const NUMBER_OF_CASH_REGISTER = 'number_of_cash_register';
    const NUMBER_OF_SUMMARY = 'number_of_summary';
    const TRANSACTION_DATE = 'transaction_date';
    const REFERENCE_NUMBER = 'reference_number';
    const TRANSACTION_ID = 'transaction_id';
    const AUTHORIZATION_CODE = 'authorization_code';
    const PAID_AMOUNT_IN_MERCHANT_CURRENCY = 'paid_amount_in_merchant_currency';
    const FEES_IN_MERCHANT_CURRENCY = 'fees_in_merchant_currency';
    const PAID_AMOUNT_WITHOUT_FEES = 'paid_amount_without_fees';
    const CARD_TYPE = 'card_type';
    const ORDER_REF1 = 'order_ref1';
    const ORDER_REF2 = 'order_ref2';

    /** @var string */
    private $dateFormat;
    /** @var string */
    private $localizedNumberOfCashRegister;
    /** @var string */
    private $localizedNumberOfSummary;
    /** @var string */
    private $localizedTransactionDate;
    /** @var string */
    private $localizedReferenceNumber;
    /** @var string */
    private $localizedTransactionId;
    /** @var string */
    private $localizedAuthorizationCode;
    /** @var string */
    private $localizedPaidAmountInMerchantCurrency;
    /** @var string */
    private $localizedFeesInMerchantCurrency;
    /** @var string */
    private $localizedPaidAmountWithoutFees;
    /** @var string */
    private $localizedCardType;
    /** @var string */
    private $localizedOrderRef1;
    /** @var string */
    private $localizedOrderRef2;

    protected function __construct(
        string $dateFormat,
        string $localizedNumberOfCashRegister,
        string $localizedNumberOfSummary,
        string $localizedTransactionDate,
        string $localizedReferenceNumber,
        string $localizedTransactionId,
        string $localizedAuthorizationCode,
        string $localizedPaidAmountInMerchantCurrency,
        string $localizedFeesInMerchantCurrency,
        string $localizedPaidAmountWithoutFees,
        string $localizedCardType,
        string $localizedOrderRef1,
        string $localizedOrderRef2
    )
    {
        $this->dateFormat = $dateFormat;
        $this->localizedNumberOfCashRegister = $localizedNumberOfCashRegister;
        $this->localizedNumberOfSummary = $localizedNumberOfSummary;
        $this->localizedTransactionDate = $localizedTransactionDate;
        $this->localizedReferenceNumber = $localizedReferenceNumber;
        $this->localizedTransactionId = $localizedTransactionId;
        $this->localizedAuthorizationCode = $localizedAuthorizationCode;
        $this->localizedPaidAmountInMerchantCurrency = $localizedPaidAmountInMerchantCurrency;
        $this->localizedFeesInMerchantCurrency = $localizedFeesInMerchantCurrency;
        $this->localizedPaidAmountWithoutFees = $localizedPaidAmountWithoutFees;
        $this->localizedCardType = $localizedCardType;
        $this->localizedOrderRef1 = $localizedOrderRef1;
        $this->localizedOrderRef2 = $localizedOrderRef2;
    }

    /**
     * @param array $values
     * @return string Digits with possible leading zeroes
     * @throws \Granam\GpWebPay\Flat\Exceptions\MissingMappedValue
     */
    public function getNumberOfCashRegister(array $values): string
    {
        return $this->getValue($values, $this->localizedNumberOfCashRegister);
    }

    /**
     * @param array $values
     * @param string $localeName
     * @return string
     * @throws \Granam\GpWebPay\Flat\Exceptions\MissingMappedValue
     */
    private function getValue(array $values, string $localeName): string
    {
        $value = $values[$localeName] ?? false;
        if ($value === false) {
            throw new Exceptions\MissingMappedValue(
                "Can not find a value by name '{$localeName}' in values " . var_export($values, true)
            );
        }

        return (string)$value;
    }

    /**
     * @param array $values
     * @return string Digits with possible leading zeroes
     * @throws \Granam\GpWebPay\Flat\Exceptions\MissingMappedValue
     */
    public function getNumberOfSummary(array $values): string
    {
        return $this->getValue($values, $this->localizedNumberOfSummary);
    }

    /**
     * @param array $values
     * @return \DateTime
     * @throws \Granam\GpWebPay\Flat\Exceptions\MissingMappedValue
     */
    public function getTransactionDate(array $values): \DateTime
    {
        return \DateTime::createFromFormat($this->dateFormat, $this->getValue($values, $this->localizedTransactionDate));
    }

    /**
     * @param array $values
     * @return string Digits with possible leading zeroes
     * @throws \Granam\GpWebPay\Flat\Exceptions\MissingMappedValue
     */
    public function getReferenceNumber(array $values): string
    {
        return $this->getValue($values, $this->localizedReferenceNumber);
    }

    /**
     * @param array $values
     * @return string Digits with possible leading zeroes
     * @throws \Granam\GpWebPay\Flat\Exceptions\MissingMappedValue
     */
    public function getTransactionId(array $values): string
    {
        return $this->getValue($values, $this->localizedTransactionId);
    }

    /**
     * @param array $values
     * @return string
     * @throws \Granam\GpWebPay\Flat\Exceptions\MissingMappedValue
     */
    public function getAuthorizationCode(array $values): string
    {
        return ''; // this is sadly NOT sent by GpWebPay
    }

    /**
     * @param array $values
     * @return float
     * @throws \Granam\GpWebPay\Flat\Exceptions\MissingMappedValue
     */
    public function getPaidAmountInMerchantCurrency(array $values): float
    {
        return (float)$this->getValue($values, $this->localizedPaidAmountInMerchantCurrency);
    }

    /**
     * @param array $values
     * @return float
     * @throws \Granam\GpWebPay\Flat\Exceptions\MissingMappedValue
     */
    public function getFeesInMerchantCurrency(array $values): float
    {
        return (float)$this->getValue($values, $this->localizedFeesInMerchantCurrency);
    }

    /**
     * @param array $values
     * @return float
     * @throws \Granam\GpWebPay\Flat\Exceptions\MissingMappedValue
     */
    public function getPaidAmountWithoutFees(array $values): float
    {
        return (float)$this->getValue($values, $this->localizedPaidAmountWithoutFees);
    }

    /**
     * @param array $values
     * @return string
     * @throws \Granam\GpWebPay\Flat\Exceptions\MissingMappedValue
     */
    public function getCardType(array $values): string
    {
        return $this->getValue($values, $this->localizedCardType);
    }

    /**
     * @param array $values
     * @return string Digits with possible leading zeroes
     * @throws \Granam\GpWebPay\Flat\Exceptions\MissingMappedValue
     */
    public function getOrderRef1(array $values): string
    {
        return $this->getValue($values, $this->localizedOrderRef1);
    }

    /**
     * @param array $values
     * @return string Digits with possible leading zeroes
     * @throws \Granam\GpWebPay\Flat\Exceptions\MissingMappedValue
     */
    public function getOrderRef2(array $values): string
    {
        return $this->getValue($values, $this->localizedOrderRef2);
    }

    /**
     * @return string
     */
    public function getLocalizedAuthorizationCode(): string
    {
        return $this->localizedAuthorizationCode;
    }
}