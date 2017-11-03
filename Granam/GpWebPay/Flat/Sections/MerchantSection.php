<?php
declare(strict_types=1); // on PHP 7+ are standard PHP methods strict to types of given parameters

namespace Granam\GpWebPay\Flat\Sections;

class MerchantSection extends FlatSection
{
    const PID = '01'; // in czech "věta avíza, úroveň účtování IČO"
    const MERCHANT_PLACE = '02'; // in czech "věta avíza, úroveň účtování Obchodní místo"
    const DAILY_SUMMARY_PER_CARD_TYPE = '03'; // in czech "věta avíza, úroveň účtování Terminál"
    const PID_OF_MERCHANT_WITH_CASHBACK = '04'; // in czech "věta avíza, úroveň účtování IČO pro obchodníka s cashback rozšířením"
    const PLACE_OF_MERCHANT_WITH_CASHBACK = '05'; // in czech "věta avíza, úroveň účtování Obchodní místo pro obchodníka s cashback rozšířením"
    const TERMINAL_OF_MERCHANT_WITH_CASHBACK = '06'; // in czech "věta avíza, úroveň účtování Terminál pro obchodníka s cashback rozšířením"
    const PID_ADDRESS = '98'; // in czech "adresa IČO"

    public function getKnownCodes(): array
    {
        return [
            self::PID,
            self::MERCHANT_PLACE,
            self::DAILY_SUMMARY_PER_CARD_TYPE,
            self::PID_OF_MERCHANT_WITH_CASHBACK,
            self::PLACE_OF_MERCHANT_WITH_CASHBACK,
            self::TERMINAL_OF_MERCHANT_WITH_CASHBACK,
            self::PID_ADDRESS
        ];
    }

}