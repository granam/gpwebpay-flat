<?php declare(strict_types=1);

namespace Granam\GpWebPay\Flat;

use Granam\GpWebPay\Flat\Sections\Currency;
use Granam\GpWebPay\Flat\Sections\TransactionsSummaryPerCardTypeForTerminal;
use Granam\GpWebPay\Flat\Sections\DescriptionOfDischargedDebts;
use Granam\GpWebPay\Flat\Sections\DescriptionOfNewHolds;
use Granam\GpWebPay\Flat\Sections\DescriptionOfUndischargedDebts;
use Granam\GpWebPay\Flat\Sections\Detail;
use Granam\GpWebPay\Flat\Sections\DetailForMerchantWithCashback;
use Granam\GpWebPay\Flat\Sections\DetailWithOptionalTransactionId;
use Granam\GpWebPay\Flat\Sections\DetailWithOptionalTransactionIdForMerchantWithCashback;
use Granam\GpWebPay\Flat\Sections\DischargedDebt;
use Granam\GpWebPay\Flat\Sections\ECommerceTransactions;
use Granam\GpWebPay\Flat\Sections\End;
use Granam\GpWebPay\Flat\Sections\HeaderOfSection;
use Granam\GpWebPay\Flat\Sections\TransactionsSummaryPerCardType;
use Granam\GpWebPay\Flat\Sections\PidAddressSection;
use Granam\GpWebPay\Flat\Sections\PidOfMerchantWithCashback;
use Granam\GpWebPay\Flat\Sections\Pid;
use Granam\GpWebPay\Flat\Sections\TransactionsSummaryPerCardTypeWithCashback;
use Granam\GpWebPay\Flat\Sections\Start;
use Granam\GpWebPay\Flat\Sections\SummaryOfChargedAmount;
use Granam\GpWebPay\Flat\Sections\SummaryOfDetail;
use Granam\GpWebPay\Flat\Sections\SummaryOfDetailedSectionForMerchantWithCashback;
use Granam\GpWebPay\Flat\Sections\SummaryOfDetailPerCardType;
use Granam\GpWebPay\Flat\Sections\SummaryOfDetailedSectionPerCardForMerchantWithCashback;
use Granam\GpWebPay\Flat\Sections\SummaryOfDischargedDebt;
use Granam\GpWebPay\Flat\Sections\SummaryOfNewMovements;
use Granam\GpWebPay\Flat\Sections\SummaryOfNewMovementsForMerchantWithCashback;
use Granam\GpWebPay\Flat\Sections\TerminalOfMerchantWithCashbackSection;
use Granam\GpWebPay\Flat\Sections\UnchargedTransactionsOfMerchantWithCashback;
use Granam\GpWebPay\Flat\Sections\UnchargedTransactions;
use Granam\GpWebPay\Flat\Sections\UndischargedDebt;
use Granam\Strict\Object\StrictObject;

class FlatContent extends StrictObject
{
    /** @var Start */
    private $start;
    /** @var Currency */
    private $currency;
    /** @var End */
    private $end;
    /** @var UnchargedTransactions|null */
    private $unchargedTransactions;
    /** @var UnchargedTransactionsOfMerchantWithCashback|null */
    private $unchargedTransactionsOfMerchantWithCashback;
    /** @var DescriptionOfDischargedDebts|null */
    private $descriptionOfDischargedDebts;
    /** @var DescriptionOfUndischargedDebts|null */
    private $descriptionOfUndischargedDebts;
    /** @var DescriptionOfNewHolds|null */
    private $descriptionOfNewHolds;
    /** @var Detail|null */
    private $detail;
    /** @var UndischargedDebt|null */
    private $undischargedDebt;
    /** @var DischargedDebt|null */
    private $dischargedDebt;
    /** @var ECommerceTransactions|null */
    private $eCommerceTransactions;
    /** @var DetailWithOptionalTransactionId|null */
    private $detailSectionWithOptionalTransactionId;
    /** @var DetailForMerchantWithCashback|null */
    private $detailSectionForMerchantWithCashback;
    /** @var DetailWithOptionalTransactionIdForMerchantWithCashback|null */
    private $detailSectionWithOptionalTransactionIdForMerchantWithCashback;
    /** @var SummaryOfDischargedDebt|null */
    private $summaryOfDischargedDebt;
    /** @var SummaryOfNewMovements|null */
    private $summaryOfNewMovements;
    /** @var SummaryOfChargedAmount|null */
    private $summaryOfChargedAmount;
    /** @var SummaryOfDetail|null */
    private $summaryOfDetail;
    /** @var SummaryOfDetailPerCardType|null */
    private $summaryOfDetailPerCardType;
    /** @var SummaryOfNewMovementsForMerchantWithCashback|null */
    private $summaryOfNewMovementsForMerchantWithCashback;
    /** @var SummaryOfDetailedSectionForMerchantWithCashback|null */
    private $summaryOfDetailedSectionForMerchantWithCashback;
    /** @var SummaryOfDetailedSectionPerCardForMerchantWithCashback|null */
    private $summaryOfDetailedSectionPerCardForMerchantWithCashback;
    /** @var Pid|null */
    private $pid;
    /** @var PidAddressSection|null */
    private $pidAddressSection;
    /** @var PidOfMerchantWithCashback|null */
    private $pidOfMerchantWithCashback;
    /** @var TransactionsSummaryPerCardType|null */
    private $transactionsSummaryPerCardType;
    /** @var TransactionsSummaryPerCardTypeWithCashback|null */
    private $transactionsSummaryPerCardTypeWithCashback;
    /** @var TerminalOfMerchantWithCashbackSection|null */
    private $terminalOfMerchantWithCashbackSection;
    /** @var TransactionsSummaryPerCardTypeForTerminal|null */
    private $transactionsSummaryPerCardTypeForTerminal;

    /**
     * @param array|string[][][] $indexedRows [0 => [code => '00', values => [...]]]
     * @param ECommerceTransactionHeaderMapper $eCommerceTransactionHeaderMapper
     * @throws \Granam\GpWebPay\Flat\Exceptions\CorruptedFlatStructure
     * @throws \Granam\GpWebPay\Flat\Exceptions\UnexpectedCode
     */
    public function __construct(array $indexedRows, ECommerceTransactionHeaderMapper $eCommerceTransactionHeaderMapper)
    {
        $header = null;
        $previousCode = false;
        foreach ($indexedRows as $row) {
            /** @var string $code */
            $code = $row['code'];
            /** @var array|string[] $values */
            $values = $row['values'];
            if ($previousCode !== false && $previousCode !== HeaderOfSection::HEADER_OF_SECTION && $previousCode !== $code) {
                $header = null; // header is valid only for a block of following same-code section
            }
            switch ($code) {
                case HeaderOfSection::HEADER_OF_SECTION :
                    $header = new HeaderOfSection($values, $eCommerceTransactionHeaderMapper);
                    break;
                case Start::START :
                    if ($this->start !== null) {
                        throw new Exceptions\CorruptedFlatStructure('Start section should be unique');
                    }
                    $this->start = new Start($values);
                    break;
                case Currency::CURRENCY_OF_TRANSACTIONS :
                    if ($this->currency !== null) {
                        throw new Exceptions\CorruptedFlatStructure('Currency section should be unique');
                    }
                    $this->currency = new Currency($values);
                    break;
                case DescriptionOfDischargedDebts::DESCRIPTION_OF_DISCHARGED_DEBTS :
                    if ($this->descriptionOfDischargedDebts === null) {
                        $this->descriptionOfDischargedDebts = new DescriptionOfDischargedDebts($values, $header);
                    } else {
                        $this->descriptionOfDischargedDebts->addValues($values);
                    }
                    break;
                case DescriptionOfUndischargedDebts::DESCRIPTION_OF_UNDISCHARGED_DEBTS:
                    if ($this->descriptionOfUndischargedDebts === null) {
                        $this->descriptionOfUndischargedDebts = new DescriptionOfUndischargedDebts($values, $header);
                    } else {
                        $this->descriptionOfUndischargedDebts->addValues($values);
                    }
                    break;
                case DescriptionOfNewHolds::DESCRIPTION_OF_NEW_HOLDS :
                    if ($this->descriptionOfNewHolds === null) {
                        $this->descriptionOfNewHolds = new DescriptionOfNewHolds($values, $header);
                    } else {
                        $this->descriptionOfNewHolds->addValues($values);
                    }
                    break;
                case Detail::DETAIL_SECTION :
                    if ($this->detail === null) {
                        $this->detail = new Detail($values, $header);
                    } else {
                        $this->detail->addValues($values);
                    }
                    break;
                case UndischargedDebt::UNDISCHARGED_DEBT :
                    if ($this->undischargedDebt === null) {
                        $this->undischargedDebt = new UndischargedDebt($values, $header);
                    } else {
                        $this->undischargedDebt->addValues($values);
                    }
                    break;
                case DischargedDebt::DISCHARGED_DEBT :
                    if ($this->dischargedDebt === null) {
                        $this->dischargedDebt = new DischargedDebt($values, $header);
                    } else {
                        $this->dischargedDebt->addValues($values);
                    }
                    break;
                case ECommerceTransactions::E_COMMERCE_TRANSACTION :
                    if ($this->eCommerceTransactions === null) {
                        if ($header === null) {
                            throw new Exceptions\CorruptedFlatStructure(
                                "Missing preceding header '" . HeaderOfSection::HEADER_OF_SECTION
                                . "' for '" . ECommerceTransactions::E_COMMERCE_TRANSACTION . "'"
                                . ' (' . ECommerceTransactions::class . ')'
                            );
                        }
                        $this->eCommerceTransactions = new ECommerceTransactions($values, $header, $eCommerceTransactionHeaderMapper);
                    } else {
                        $this->eCommerceTransactions->addValues($values);
                    }
                    break;
                case DetailWithOptionalTransactionId::DETAIL_WITH_OPTIONAL_TRANSACTION_ID :
                    if ($this->detailSectionWithOptionalTransactionId === null) {
                        $this->detailSectionWithOptionalTransactionId = new DetailWithOptionalTransactionId($values, $header);
                    } else {
                        $this->detailSectionWithOptionalTransactionId->addValues($values);
                    }
                    break;
                case DetailForMerchantWithCashback::DETAIL_FOR_MERCHANT_WITH_CASHBACK :
                    if ($this->detailSectionForMerchantWithCashback === null) {
                        $this->detailSectionForMerchantWithCashback = new DetailForMerchantWithCashback($values, $header);
                    } else {
                        $this->detailSectionForMerchantWithCashback->addValues($values);
                    }
                    break;
                case DetailWithOptionalTransactionIdForMerchantWithCashback::DETAIL_WITH_OPTIONAL_TRANSACTION_ID_FOR_MERCHANT_WITH_CASHBACK :
                    if ($this->detailSectionWithOptionalTransactionIdForMerchantWithCashback === null) {
                        $this->detailSectionWithOptionalTransactionIdForMerchantWithCashback = new DetailWithOptionalTransactionIdForMerchantWithCashback($values, $header);
                    } else {
                        $this->detailSectionWithOptionalTransactionIdForMerchantWithCashback->addValues($values);
                    }
                    break;
                case UnchargedTransactions::UNCHARGED_TRANSACTIONS :
                    if ($this->unchargedTransactions === null) {
                        $this->unchargedTransactions = new UnchargedTransactions($values, $header);
                    } else {
                        $this->unchargedTransactions->addValues($values);
                    }
                    break;
                case UnchargedTransactionsOfMerchantWithCashback::UNCHARGED_TRANSACTIONS_OF_MERCHANT_WITH_CASHBACK :
                    if ($this->unchargedTransactionsOfMerchantWithCashback === null) {
                        $this->unchargedTransactionsOfMerchantWithCashback = new UnchargedTransactionsOfMerchantWithCashback($values, $header);
                    } else {
                        $this->unchargedTransactionsOfMerchantWithCashback->addValues($values);
                    }
                    break;
                case SummaryOfDischargedDebt::SUMMARY_OF_DISCHARGED_DEBT :
                    if ($this->summaryOfDischargedDebt !== null) {
                        throw new Exceptions\CorruptedFlatStructure('Summary of discharged debt section should be unique');
                    }
                    $this->summaryOfDischargedDebt = new SummaryOfDischargedDebt($values);
                    break;
                case SummaryOfNewMovements::SUMMARY_OF_NEW_MOVEMENTS :
                    if ($this->summaryOfNewMovements !== null) {
                        throw new Exceptions\CorruptedFlatStructure('Summary of new movements section should be unique');
                    }
                    $this->summaryOfNewMovements = new SummaryOfNewMovements($values);
                    break;
                case SummaryOfChargedAmount::SUMMARY_OF_CHARGED_AMOUNT :
                    if ($this->summaryOfChargedAmount !== null) {
                        throw new Exceptions\CorruptedFlatStructure('Summary of charged amount section should be unique');
                    }
                    $this->summaryOfChargedAmount = new SummaryOfChargedAmount($values);
                    break;
                case SummaryOfDetail::SUMMARY_OF_DETAIL :
                    if ($this->summaryOfDetail !== null) {
                        throw new Exceptions\CorruptedFlatStructure('Summary of detail section should be unique');
                    }
                    $this->summaryOfDetail = new SummaryOfDetail($values);
                    break;
                case SummaryOfDetailPerCardType::SUMMARY_OF_DETAIL_PER_CARD_TYPE :
                    if ($this->summaryOfDetailPerCardType === null) {
                        $this->summaryOfDetailPerCardType = new SummaryOfDetailPerCardType($values, $header);
                    } else {
                        $this->summaryOfDetailPerCardType->addValues($values);
                    }
                    break;
                case SummaryOfNewMovementsForMerchantWithCashback::SUMMARY_OF_NEW_MOVEMENTS_FOR_MERCHANT_WITH_CASHBACK :
                    if ($this->summaryOfNewMovementsForMerchantWithCashback === null) {
                        $this->summaryOfNewMovementsForMerchantWithCashback = new SummaryOfNewMovementsForMerchantWithCashback($values, $header);
                    } else {
                        $this->summaryOfNewMovementsForMerchantWithCashback->addValues($values);
                    }
                    break;
                case SummaryOfDetailedSectionForMerchantWithCashback::SUMMARY_OF_DETAILED_SECTION_FOR_MERCHANT_WITH_CASHBACK :
                    if ($this->summaryOfDetailedSectionForMerchantWithCashback === null) {
                        $this->summaryOfDetailedSectionForMerchantWithCashback = new SummaryOfDetailedSectionForMerchantWithCashback($values, $header);
                    } else {
                        $this->summaryOfDetailedSectionForMerchantWithCashback->addValues($values);
                    }
                    break;
                case SummaryOfDetailedSectionPerCardForMerchantWithCashback::SUMMARY_OF_DETAILED_SECTION_PER_CARD_FOR_MERCHANT_WITH_CASHBACK :
                    if ($this->summaryOfDetailedSectionPerCardForMerchantWithCashback === null) {
                        $this->summaryOfDetailedSectionPerCardForMerchantWithCashback = new SummaryOfDetailedSectionPerCardForMerchantWithCashback($values, $header);
                    } else {
                        $this->summaryOfDetailedSectionPerCardForMerchantWithCashback->addValues($values);
                    }
                    break;
                case Pid::PID :
                    if ($this->pid !== null) {
                        throw new Exceptions\CorruptedFlatStructure('PID section should be unique');
                    }
                    $this->pid = new Pid($values);
                    break;
                case PidAddressSection::PID_ADDRESS :
                    if ($this->pidAddressSection !== null) {
                        throw new Exceptions\CorruptedFlatStructure('PID address section should be unique');
                    }
                    $this->pidAddressSection = new PidAddressSection($values);
                    break;
                case PidOfMerchantWithCashback::PID_OF_MERCHANT_WITH_CASHBACK :
                    if ($this->pidOfMerchantWithCashback !== null) {
                        throw new Exceptions\CorruptedFlatStructure('PID of merchant with cashback section should be unique');
                    }
                    $this->pidOfMerchantWithCashback = new PidOfMerchantWithCashback($values);
                    break;
                case TransactionsSummaryPerCardType::TRANSACTIONS_SUMMARY_PER_CARD_TYPE :
                    if ($this->transactionsSummaryPerCardType === null) {
                        $this->transactionsSummaryPerCardType = new TransactionsSummaryPerCardType($values, $header);
                    } else {
                        $this->transactionsSummaryPerCardType->addValues($values);
                    }
                    break;
                case TransactionsSummaryPerCardTypeWithCashback::TRANSACTIONS_SUMMARY_PER_CARD_TYPE_WITH_CASHBACK :
                    if ($this->transactionsSummaryPerCardTypeWithCashback === null) {
                        $this->transactionsSummaryPerCardTypeWithCashback = new TransactionsSummaryPerCardTypeWithCashback($values, $header);
                    } else {
                        $this->transactionsSummaryPerCardTypeWithCashback->addValues($values);
                    }
                    break;
                case TerminalOfMerchantWithCashbackSection::TERMINAL_OF_MERCHANT_WITH_CASHBACK :
                    if ($this->terminalOfMerchantWithCashbackSection === null) {
                        $this->terminalOfMerchantWithCashbackSection = new TerminalOfMerchantWithCashbackSection($values, $header);
                    } else {
                        $this->terminalOfMerchantWithCashbackSection->addValues($values);
                    }
                    break;
                case TransactionsSummaryPerCardTypeForTerminal::TRANSACTIONS_SUMMARY_PER_CARD_TYPE_FOR_TERMINAL :
                    if ($this->transactionsSummaryPerCardTypeForTerminal === null) {
                        $this->transactionsSummaryPerCardTypeForTerminal = new TransactionsSummaryPerCardTypeForTerminal($values, $header);
                    } else {
                        $this->transactionsSummaryPerCardTypeForTerminal->addValues($values);
                    }
                    break;
                case End::END :
                    if ($this->end !== null) {
                        throw new Exceptions\CorruptedFlatStructure('End section should be unique');
                    }
                    $this->end = new End($values);
                    break;

                default :
                    throw new Exceptions\UnexpectedCode("Unknown code {$row['code']}");
            }
            $previousCode = $code;
        }
        if ($this->start === null) {
            throw new Exceptions\CorruptedFlatStructure("Missing required '" . Start::START . "' section (" . Start::class . ')');
        }
        if ($this->currency === null) {
            throw new Exceptions\CorruptedFlatStructure("Missing required '" . Currency::CURRENCY_OF_TRANSACTIONS . "' section (" . Currency::class . ')');
        }
        if ($this->end === null) {
            throw new Exceptions\CorruptedFlatStructure("Missing required '" . End::END . "' section (" . End::class . ')');
        }
    }

    /**
     * @return Start
     */
    public function getStart(): Start
    {
        return $this->start;
    }

    /**
     * @return Currency
     */
    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    /**
     * @return End
     */
    public function getEnd(): End
    {
        return $this->end;
    }

    /**
     * @return UnchargedTransactions|null
     */
    public function getUnchargedTransactions():? UnchargedTransactions
    {
        return $this->unchargedTransactions;
    }

    /**
     * @return UnchargedTransactionsOfMerchantWithCashback|null
     */
    public function getUnchargedTransactionsOfMerchantWithCashback():? UnchargedTransactionsOfMerchantWithCashback
    {
        return $this->unchargedTransactionsOfMerchantWithCashback;
    }

    /**
     * @return DescriptionOfDischargedDebts|null
     */
    public function getDescriptionOfDischargedDebts():? DescriptionOfDischargedDebts
    {
        return $this->descriptionOfDischargedDebts;
    }

    /**
     * @return DescriptionOfUndischargedDebts|null
     */
    public function getDescriptionOfUndischargedDebts():? DescriptionOfUndischargedDebts
    {
        return $this->descriptionOfUndischargedDebts;
    }

    /**
     * @return DescriptionOfNewHolds|null
     */
    public function getDescriptionOfNewHolds():? DescriptionOfNewHolds
    {
        return $this->descriptionOfNewHolds;
    }

    /**
     * @return Detail|null
     */
    public function getDetail():? Detail
    {
        return $this->detail;
    }

    /**
     * @return UndischargedDebt|null
     */
    public function getUndischargedDebt():? UndischargedDebt
    {
        return $this->undischargedDebt;
    }

    /**
     * @return DischargedDebt|null
     */
    public function getDischargedDebt():? DischargedDebt
    {
        return $this->dischargedDebt;
    }

    /**
     * @param \DateTime $onlyTransactionsOfDay = null
     * @return ECommerceTransactions|ECommerceTransaction[]|null
     */
    public function getECommerceTransactions(\DateTime $onlyTransactionsOfDay = null):? ECommerceTransactions
    {
        return $onlyTransactionsOfDay === null
            ? $this->eCommerceTransactions
            : $this->eCommerceTransactions->filterByDay($onlyTransactionsOfDay);
    }

    /**
     * @return DetailWithOptionalTransactionId|null
     */
    public function getDetailSectionWithOptionalTransactionId():? DetailWithOptionalTransactionId
    {
        return $this->detailSectionWithOptionalTransactionId;
    }

    /**
     * @return DetailForMerchantWithCashback|null
     */
    public function getDetailSectionForMerchantWithCashback():? DetailForMerchantWithCashback
    {
        return $this->detailSectionForMerchantWithCashback;
    }

    /**
     * @return DetailWithOptionalTransactionIdForMerchantWithCashback|null
     */
    public function getDetailSectionWithOptionalTransactionIdForMerchantWithCashback():? DetailWithOptionalTransactionIdForMerchantWithCashback
    {
        return $this->detailSectionWithOptionalTransactionIdForMerchantWithCashback;
    }

    /**
     * @return SummaryOfDischargedDebt|null
     */
    public function getSummaryOfDischargedDebt():? SummaryOfDischargedDebt
    {
        return $this->summaryOfDischargedDebt;
    }

    /**
     * @return SummaryOfNewMovements|null
     */
    public function getSummaryOfNewMovements():? SummaryOfNewMovements
    {
        return $this->summaryOfNewMovements;
    }

    /**
     * @return SummaryOfChargedAmount|null
     */
    public function getSummaryOfChargedAmount():? SummaryOfChargedAmount
    {
        return $this->summaryOfChargedAmount;
    }

    /**
     * @return SummaryOfDetail|null
     */
    public function getSummaryOfDetail():? SummaryOfDetail
    {
        return $this->summaryOfDetail;
    }

    /**
     * @return SummaryOfDetailPerCardType|null
     */
    public function getSummaryOfDetailPerCardType():? SummaryOfDetailPerCardType
    {
        return $this->summaryOfDetailPerCardType;
    }

    /**
     * @return SummaryOfNewMovementsForMerchantWithCashback|null
     */
    public function getSummaryOfNewMovementsForMerchantWithCashback():? SummaryOfNewMovementsForMerchantWithCashback
    {
        return $this->summaryOfNewMovementsForMerchantWithCashback;
    }

    /**
     * @return SummaryOfDetailedSectionForMerchantWithCashback|null
     */
    public function getSummaryOfDetailedSectionForMerchantWithCashback():? SummaryOfDetailedSectionForMerchantWithCashback
    {
        return $this->summaryOfDetailedSectionForMerchantWithCashback;
    }

    /**
     * @return SummaryOfDetailedSectionPerCardForMerchantWithCashback|null
     */
    public function getSummaryOfDetailedSectionPerCardForMerchantWithCashback():? SummaryOfDetailedSectionPerCardForMerchantWithCashback
    {
        return $this->summaryOfDetailedSectionPerCardForMerchantWithCashback;
    }

    /**
     * @return Pid|null
     */
    public function getPid():? Pid
    {
        return $this->pid;
    }

    /**
     * @return PidAddressSection|null
     */
    public function getPidAddressSection():? PidAddressSection
    {
        return $this->pidAddressSection;
    }

    /**
     * @return PidOfMerchantWithCashback|null
     */
    public function getPidOfMerchantWithCashback():? PidOfMerchantWithCashback
    {
        return $this->pidOfMerchantWithCashback;
    }

    /**
     * @return TransactionsSummaryPerCardType|null
     */
    public function getTransactionsSummaryPerCardType():? TransactionsSummaryPerCardType
    {
        return $this->transactionsSummaryPerCardType;
    }

    /**
     * @return TransactionsSummaryPerCardTypeWithCashback|null
     */
    public function getTransactionsSummaryPerCardTypeWithCashback():? TransactionsSummaryPerCardTypeWithCashback
    {
        return $this->transactionsSummaryPerCardTypeWithCashback;
    }

    /**
     * @return TerminalOfMerchantWithCashbackSection|null
     */
    public function getTerminalOfMerchantWithCashbackSection():? TerminalOfMerchantWithCashbackSection
    {
        return $this->terminalOfMerchantWithCashbackSection;
    }

    /**
     * @return TransactionsSummaryPerCardTypeForTerminal|null
     */
    public function getTransactionsSummaryPerCardTypeForTerminal():? TransactionsSummaryPerCardTypeForTerminal
    {
        return $this->transactionsSummaryPerCardTypeForTerminal;
    }
}
