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
    /** @var array|string[][][] */
    private $originalValuesPerDay = [];

    /**
     * @param array|string[] $values
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

    /**
     * @param array|string[] $values
     * @throws \Granam\GpWebPay\Flat\Exceptions\CorruptedFlatStructure
     */
    public function addValues(array $values)
    {
        parent::addValues($values);
        $allMappedValues = $this->getValues();
        $this->addTransaction(end($allMappedValues), $this->eCommerceTransactionHeaderMapper, $values);
    }

    private function addTransaction(
        array $mappedValues,
        ECommerceTransactionHeaderMapper $eCommerceTransactionHeaderMapper,
        array $originalValues
    )
    {
        $transaction = new ECommerceTransaction($mappedValues, $eCommerceTransactionHeaderMapper);
        $this->transactions[] = $transaction;
        $this->originalValuesPerDay[$transaction->getTransactionDate()->format('Y-m-d')][] = $originalValues;
    }

    /**
     * @param \DateTime $onlyTransactionsOfDay
     * @return ECommerceTransactions|null
     */
    public function filterByDay(\DateTime $onlyTransactionsOfDay)
    {
        if ($this->count() === 0 // nothing to filter at all
            // or all transactions are from required day
            || (count($this->originalValuesPerDay) === 1 && array_key_exists($onlyTransactionsOfDay->format('Y-m-d'), $this->originalValuesPerDay))
        ) {
            return $this; // no change needed
        }
        $originalValues = $this->originalValuesPerDay[$onlyTransactionsOfDay->format('Y-m-d')] ?? false;
        if (!$originalValues) {
            return null; // no transactions for that day
        }
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $eCommerceTransactions = new static(
            array_pop($originalValues), // also removes one item / transaction
            $this->getHeaderOfSection(),
            $this->eCommerceTransactionHeaderMapper
        );
        foreach ($originalValues /* already one transaction less */ as $originalValuesForTransaction) {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $eCommerceTransactions->addValues($originalValuesForTransaction);
        }

        return $eCommerceTransactions;
    }

    /**
     * @param \DateTime $onlyTransactionsOfDay = null
     * @return array|ECommerceTransaction[]
     */
    public function getTransactions(\DateTime $onlyTransactionsOfDay = null): array
    {
        if ($onlyTransactionsOfDay === null) {
            return $this->transactions;
        }

        return array_filter(
            $this->getTransactions(),
            function (ECommerceTransaction $transaction) use ($onlyTransactionsOfDay) {
                return $onlyTransactionsOfDay->format('Ymd') === $transaction->getTransactionDate()->format('Ymd');
            }
        );
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayObject($this->getTransactions());
    }

    public function count(): int
    {
        return count($this->getTransactions());
    }

    /**
     * @param \DateTime|null $onlyTransactionsOfDay
     * @return float
     */
    public function getSummary(\DateTime $onlyTransactionsOfDay = null): float
    {
        return $this->getPaidAmountInMerchantCurrencySummary($onlyTransactionsOfDay);
    }

    /**
     * @param \DateTime|null $onlyTransactionsOfDay
     * @return float
     */
    public function getPaidAmountInMerchantCurrencySummary(\DateTime $onlyTransactionsOfDay = null): float
    {
        return array_sum(
            array_map(
                function (ECommerceTransaction $transaction) {
                    return $transaction->getPaidAmountInMerchantCurrency();
                },
                $this->getTransactions($onlyTransactionsOfDay)
            )
        );
    }

    /**
     * @param \DateTime|null $onlyTransactionsOfDay
     * @return float
     */
    public function getFeesInMerchantCurrencySummary(\DateTime $onlyTransactionsOfDay = null): float
    {
        return array_sum(
            array_map(
                function (ECommerceTransaction $transaction) {
                    return $transaction->getFeesInMerchantCurrency();
                },
                $this->getTransactions($onlyTransactionsOfDay)
            )
        );
    }

    /**
     * @param \DateTime|null $onlyTransactionsOfDay
     * @return float
     */
    public function getPaidAmountWithoutFeesSummary(\DateTime $onlyTransactionsOfDay = null): float
    {
        return array_sum(
            array_map(
                function (ECommerceTransaction $transaction) {
                    return $transaction->getPaidAmountWithoutFees();
                },
                $this->getTransactions($onlyTransactionsOfDay)
            )
        );
    }

}