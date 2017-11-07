<?php
declare(strict_types=1); // on PHP 7+ are standard PHP methods strict to types of given parameters

namespace Granam\GpWebPay\Flat\Sections;

class DescriptionOfUndischargedDebts extends MultiLineFlatSection
{
    const DESCRIPTION_OF_UNDISCHARGED_DEBTS = '52'; // in czech "popisná věta neumořených dluhů"
}