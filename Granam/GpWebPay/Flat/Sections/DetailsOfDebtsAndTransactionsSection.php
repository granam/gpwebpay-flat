<?php
declare(strict_types=1); // on PHP 7+ are standard PHP methods strict to types of given parameters

namespace Granam\GpWebPay\Flat\Sections;

class DetailsOfDebtsAndTransactionsSection extends FlatSection
{

    const DETAIL_SECTION = '21'; // in czech "věta detailního oddílu"
    const UNDISCHARGED_DEBT = '22'; // in czech "věta neumořeného dluhu"
    const DISCHARGED_DEBT = '23'; // in czech "věta umoření dluhu"
    const E_COMMERCE_DETAIL_SECTION = '24'; // in czech "věta detailního oddílu e-commerce"
    const DETAIL_SECTION_WITH_OPTIONAL_TRANSACTION_ID = '25'; // in czech "věta detailního oddílu s volitelným id trans."
    const DETAIL_SECTION_FOR_MERCHANT_WITH_CASHBACK = '26'; // in czech "věta detailního oddílu pro obchodníka s cashback rozšířením"
    const DETAIL_SECTION_WITH_OPTIONAL_TRANSACTION_ID_FOR_MERCHANT_WITH_CASHBACK = '27'; // in czech "věta detailního oddílu s volitelným id trans. pro obchodníka s cashback rozšířením"

    public function getKnownCodes(): array
    {
        return [
            self::DETAIL_SECTION,
            self::UNDISCHARGED_DEBT,
            self::DISCHARGED_DEBT,
            self::E_COMMERCE_DETAIL_SECTION,
            self::DETAIL_SECTION_WITH_OPTIONAL_TRANSACTION_ID,
            self::DETAIL_SECTION_FOR_MERCHANT_WITH_CASHBACK,
            self::DETAIL_SECTION_WITH_OPTIONAL_TRANSACTION_ID_FOR_MERCHANT_WITH_CASHBACK,
        ];
    }

}