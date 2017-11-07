<?php
declare(strict_types=1); // on PHP 7+ are standard PHP methods strict to types of given parameters

namespace Granam\GpWebPay\Flat\Sections;

class DescriptionOfDischargedDebts extends MultiLineFlatSection
{
    const DESCRIPTION_OF_DISCHARGED_DEBTS = '54'; // in czech "popisná věta umoření dluhů"
}