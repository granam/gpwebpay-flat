<?php
declare(strict_types=1); // on PHP 7+ are standard PHP methods strict to types of given parameters

namespace Granam\GpWebPay\Flat\Sections;

class UndischargedDebt extends MultiLineFlatSection
{
    const UNDISCHARGED_DEBT = '22'; // in czech "věta neumořeného dluhu"
}