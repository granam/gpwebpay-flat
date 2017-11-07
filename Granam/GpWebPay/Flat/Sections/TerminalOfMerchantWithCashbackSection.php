<?php
declare(strict_types=1); // on PHP 7+ are standard PHP methods strict to types of given parameters

namespace Granam\GpWebPay\Flat\Sections;

class TerminalOfMerchantWithCashbackSection extends MultiLineFlatSection
{
    const TERMINAL_OF_MERCHANT_WITH_CASHBACK = '06'; // in czech "věta avíza, úroveň účtování Terminál pro obchodníka s cashback rozšířením"
}