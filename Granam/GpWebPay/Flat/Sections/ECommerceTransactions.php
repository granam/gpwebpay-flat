<?php
declare(strict_types=1); // on PHP 7+ are standard PHP methods strict to types of given parameters

namespace Granam\GpWebPay\Flat\Sections;

use Granam\GpWebPay\Flat\ECommerceTransaction;
use Granam\GpWebPay\Flat\ECommerceTransactionHeaderMapper;
use Granam\GpWebPay\Flat\Summarizable;

class ECommerceTransactions extends MultiLineFlatSection implements \IteratorAggregate, \Countable, Summarizable
{
    const E_COMMERCE_TRANSACTION = '24'; // in czech "věta detailního oddílu e-commerce"

    /** @var array|ECommerceTransaction[] */
    private $transactions = [];
    /** @var ECommerceTransactionHeaderMapper */
    private $eCommerceTransactionHeaderMapper;

    /**
     * @param array $values
     * @param HeaderOfSection $headerOfSection
     * @param ECommerceTransactionHeaderMapper $eCommerceTransactionHeaderMapper
     * @throws \Granam\GpWebPay\Flat\Exceptions\CorruptedFlatStructure
     */
    public function __construct(
        array $values,
        HeaderOfSection $headerOfSection,
        ECommerceTransactionHeaderMapper $eCommerceTransactionHeaderMapper
    )
    {
        $this->eCommerceTransactionHeaderMapper = $eCommerceTransactionHeaderMapper;
        parent::__construct($values, $headerOfSection);
    }

    public function addValues(array $values)
    {
        parent::addValues($values);
        $allMappedValues = $this->getValues();
        $this->addTransaction(end($allMappedValues), $this->eCommerceTransactionHeaderMapper);
    }

    private function addTransaction(array $mappedValues, ECommerceTransactionHeaderMapper $eCommerceTransactionHeaderMapper)
    {
        $this->transactions[] = new ECommerceTransaction($mappedValues, $eCommerceTransactionHeaderMapper);
    }

    /**
     * @return array|ECommerceTransaction[]
     */
    public function getTransactions(): array
    {
        return $this->transactions;
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayObject($this->getTransactions());
    }

    public function count(): int
    {
        return count($this->getTransactions());
    }

    public function getSummary(): float
    {
        return $this->getPaidAmountInMerchantCurrencySummary();
    }

    public function getPaidAmountInMerchantCurrencySummary(): float
    {
        return array_sum(
            array_map(
                function (ECommerceTransaction $transaction) {
                    return $transaction->getPaidAmountInMerchantCurrency();
                },
                $this->getTransactions()
            )
        );
    }

    public function getFeesInMerchantCurrencySummary(): float
    {
        return array_sum(
            array_map(
                function (ECommerceTransaction $transaction) {
                    return $transaction->getFeesInMerchantCurrency();
                },
                $this->getTransactions()
            )
        );
    }

    public function getPaidAmountWithoutFeesSummary(): float
    {
        return array_sum(
            array_map(
                function (ECommerceTransaction $transaction) {
                    return $transaction->getPaidAmountWithoutFees();
                },
                $this->getTransactions()
            )
        );
    }

}