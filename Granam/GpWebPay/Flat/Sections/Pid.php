<?php
declare(strict_types=1); // on PHP 7+ are standard PHP methods strict to types of given parameters

namespace Granam\GpWebPay\Flat\Sections;

class Pid extends SingleLineFlatSection
{
    const PID = '01'; // in czech "věta avíza, úroveň účtování IČO"
}