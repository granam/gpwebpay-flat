<?php
declare(strict_types=1); // on PHP 7+ are standard PHP methods strict to types of given parameters

namespace Granam\GpWebPay\Flat;

use Granam\Strict\Object\StrictObject;

class ReportedPayment extends StrictObject
{
    private $numberOfCashRegister;
    private $numberOfSummary;
    private $transactionDate;
    private $referenceNumber;
    private $transactionId;
    private $authorizationCode;
    private $priceInMerchantCurrency;
    private $feesInMerchantCurrency;
    private $priceToPay;
    private $cardType;
    private $orderRef1;
    private $orderRef2;

    public function __construct(array $values, ReportedPaymentKeysMapper $reportedPaymentKeysMapper)
    {
        $this->numberOfCashRegister = $reportedPaymentKeysMapper->getNumberOfCashRegister($values);
        $this->numberOfSummary = $reportedPaymentKeysMapper->getNumberOfSummary($values);
        $this->transactionDate = $reportedPaymentKeysMapper->getTransactionDate($values);
        $this->referenceNumber = $reportedPaymentKeysMapper->getReferenceNumber($values);
        $this->transactionId = $reportedPaymentKeysMapper->getTransactionId($values);
        $this->authorizationCode = $reportedPaymentKeysMapper->getAuthorizationCode($values);
        $this->priceInMerchantCurrency = $reportedPaymentKeysMapper->getPriceInMerchantCurrency($values);
        $this->feesInMerchantCurrency = $reportedPaymentKeysMapper->getFeesInMerchantCurrency($values);
        $this->priceToPay = $reportedPaymentKeysMapper->getPriceToPay($values);
        $this->cardType = $reportedPaymentKeysMapper->getCardType($values);
        $this->orderRef1 = $reportedPaymentKeysMapper->getOrderRef1($values);
        $this->orderRef2 = $reportedPaymentKeysMapper->getOrderRef2($values);
    }

    /**
     * @return string
     */
    public function getNumberOfCashRegister(): string
    {
        return $this->numberOfCashRegister;
    }

    /**
     * @return string
     */
    public function getNumberOfSummary(): string
    {
        return $this->numberOfSummary;
    }

    /**
     * @return \DateTime
     */
    public function getTransactionDate(): \DateTime
    {
        return $this->transactionDate;
    }

    /**
     * @return string
     */
    public function getReferenceNumber(): string
    {
        return $this->referenceNumber;
    }

    /**
     * @return string
     */
    public function getTransactionId(): string
    {
        return $this->transactionId;
    }

    /**
     * @return string
     */
    public function getAuthorizationCode(): string
    {
        return $this->authorizationCode;
    }

    /**
     * @return float
     */
    public function getPriceInMerchantCurrency(): float
    {
        return $this->priceInMerchantCurrency;
    }

    /**
     * @return float
     */
    public function getFeesInMerchantCurrency(): float
    {
        return $this->feesInMerchantCurrency;
    }

    /**
     * @return float
     */
    public function getPriceToPay(): float
    {
        return $this->priceToPay;
    }

    /**
     * @return string
     */
    public function getCardType(): string
    {
        return $this->cardType;
    }

    /**
     * @return string
     */
    public function getOrderRef1(): string
    {
        return $this->orderRef1;
    }

    /**
     * @return string
     */
    public function getOrderRef2(): string
    {
        return $this->orderRef2;
    }
}