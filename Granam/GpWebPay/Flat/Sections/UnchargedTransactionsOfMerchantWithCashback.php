<?php
declare(strict_types=1); // on PHP 7+ are standard PHP methods strict to types of given parameters

namespace Granam\GpWebPay\Flat\Sections;

class UnchargedTransactionsOfMerchantWithCashback extends MultiLineFlatSection
{
    const UNCHARGED_TRANSACTIONS_OF_MERCHANT_WITH_CASHBACK = '13'; // in czech "věta nezaúčtovaných transakcí pro obchodníka s cashback rozšířením"
}