<?php
declare(strict_types=1); // on PHP 7+ are standard PHP methods strict to types of given parameters

namespace Granam\GpWebPay\Flat\Sections;

class SummaryOfNewMovementsForMerchantWithCashback extends MultiLineFlatSection
{
    const SUMMARY_OF_NEW_MOVEMENTS_FOR_MERCHANT_WITH_CASHBACK = '89'; // in czech "součtová věta avíza za dávku nových pohybů pro obchodníka s cashback rozšířením"
}