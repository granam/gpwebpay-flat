<?php
declare(strict_types=1); // on PHP 7+ are standard PHP methods strict to types of given parameters

namespace Granam\GpWebPay\Flat\Sections;

class DetailWithOptionalTransactionId extends MultiLineFlatSection
{
    const DETAIL_WITH_OPTIONAL_TRANSACTION_ID = '25'; // in czech "věta detailního oddílu s volitelným id trans."
}