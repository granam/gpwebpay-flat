<?php
declare(strict_types=1); // on PHP 7+ are standard PHP methods strict to types of given parameters

namespace Granam\GpWebPay\Flat\Sections;

class Currency extends SingleLineFlatSection
{
    const CURRENCY_OF_TRANSACTIONS = '61'; // in czech "uvození měny transakcí"
}