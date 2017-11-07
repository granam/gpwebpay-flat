<?php
declare(strict_types=1); // on PHP 7+ are standard PHP methods strict to types of given parameters

namespace Granam\GpWebPay\Flat\Sections;

class PidOfMerchantWithCashback extends SingleLineFlatSection
{
    const PID_OF_MERCHANT_WITH_CASHBACK = '04'; // in czech "věta avíza, úroveň účtování IČO pro obchodníka s cashback rozšířením"
}