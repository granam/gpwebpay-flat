<?php
declare(strict_types=1); // on PHP 7+ are standard PHP methods strict to types of given parameters

namespace Granam\GpWebPay\Flat\Sections;

class PidAddressSection extends SingleLineFlatSection
{
    const PID_ADDRESS = '98'; // in czech "adresa IČO"
}