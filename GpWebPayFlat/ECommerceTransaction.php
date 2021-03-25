<?php declare(strict_types=1);

namespace Granam\GpWebPay\Flat;

use Granam\Strict\Object\StrictObject;

class ECommerceTransaction extends StrictObject
{

    private $numberOfCashRegister;
    private $numberOfSummary;
    private $transactionDate;
    private $referenceNumber;
    private $transactionId;
    private $authorizationCode;
    private $paidAmountInMerchantCurrency;
    private $feesInMerchantCurrency;
    private $paidAmountWithoutFees;
    private $cardType;
    private $orderRef1;
    private $orderRef2;

    public function __construct(array $values, ECommerceTransactionHeaderMapper $eCommerceTransactionHeaderMapper)
    {
        $this->numberOfCashRegister = $eCommerceTransactionHeaderMapper->getNumberOfCashRegister($values);
        $this->numberOfSummary = $eCommerceTransactionHeaderMapper->getNumberOfSummary($values);
        $this->transactionDate = $eCommerceTransactionHeaderMapper->getTransactionDate($values);
        $this->referenceNumber = $eCommerceTransactionHeaderMapper->getReferenceNumber($values);
        $this->transactionId = $eCommerceTransactionHeaderMapper->getTransactionId($values);
        $this->authorizationCode = $eCommerceTransactionHeaderMapper->getAuthorizationCode($values);
        $this->paidAmountInMerchantCurrency = $eCommerceTransactionHeaderMapper->getPaidAmountInMerchantCurrency($values);
        $this->feesInMerchantCurrency = $eCommerceTransactionHeaderMapper->getFeesInMerchantCurrency($values);
        $this->paidAmountWithoutFees = $eCommerceTransactionHeaderMapper->getPaidAmountWithoutFees($values);
        $this->cardType = $eCommerceTransactionHeaderMapper->getCardType($values);
        $this->orderRef1 = $eCommerceTransactionHeaderMapper->getOrderRef1($values);
        $this->orderRef2 = $eCommerceTransactionHeaderMapper->getOrderRef2($values);
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
    public function getPaidAmountInMerchantCurrency(): float
    {
        return $this->paidAmountInMerchantCurrency;
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
    public function getPaidAmountWithoutFees(): float
    {
        return $this->paidAmountWithoutFees;
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
