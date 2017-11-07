<?php
declare(strict_types=1); // on PHP 7+ are standard PHP methods strict to types of given parameters

namespace Granam\GpWebPay\Flat\Sections;

class DischargedDebt extends MultiLineFlatSection
{
    const DISCHARGED_DEBT = '23'; // in czech "věta umoření dluhu"
}