<?php
declare(strict_types=1); // on PHP 7+ are standard PHP methods strict to types of given parameters

namespace Granam\GpWebPay\Flat\Sections;

class TransactionsSummaryPerCardType extends MultiLineFlatSection
{
    const TRANSACTIONS_SUMMARY_PER_CARD_TYPE = '02'; // in czech "věta avíza, úroveň účtování Obchodní místo"
}