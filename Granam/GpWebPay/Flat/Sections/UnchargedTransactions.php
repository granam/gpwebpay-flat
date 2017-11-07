<?php
declare(strict_types=1); // on PHP 7+ are standard PHP methods strict to types of given parameters

namespace Granam\GpWebPay\Flat\Sections;

class UnchargedTransactions extends MultiLineFlatSection
{
    const UNCHARGED_TRANSACTIONS = '11'; // in czech "věta nezaúčtovaných transakcí"
}