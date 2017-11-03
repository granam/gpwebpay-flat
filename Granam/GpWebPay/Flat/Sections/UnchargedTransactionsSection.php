<?php
declare(strict_types=1); // on PHP 7+ are standard PHP methods strict to types of given parameters

namespace Granam\GpWebPay\Flat\Sections;

class UnchargedTransactionsSection extends FlatSection
{

    const UNCHARGED_TRANSACTIONS = '11'; // in czech "věta nezaúčtovaných transakcí"
    const UNCHARGED_TRANSACTIONS_OF_MERCHANT_WITH_CASHBACK = '13'; // in czech "věta nezaúčtovaných transakcí pro obchodníka s cashback rozšířením"

    public function getKnownCodes(): array
    {
        return [self::UNCHARGED_TRANSACTIONS, self::UNCHARGED_TRANSACTIONS_OF_MERCHANT_WITH_CASHBACK];
    }

}