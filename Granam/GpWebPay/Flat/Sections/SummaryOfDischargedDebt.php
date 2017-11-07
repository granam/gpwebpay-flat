<?php
declare(strict_types=1); // on PHP 7+ are standard PHP methods strict to types of given parameters

namespace Granam\GpWebPay\Flat\Sections;

class SummaryOfDischargedDebt extends SingleLineFlatSection
{
    const SUMMARY_OF_DISCHARGED_DEBT = '82'; // in czech "součtová věta avíza umořených dluhů"
}