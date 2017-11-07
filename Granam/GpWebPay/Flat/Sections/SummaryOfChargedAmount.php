<?php
declare(strict_types=1); // on PHP 7+ are standard PHP methods strict to types of given parameters

namespace Granam\GpWebPay\Flat\Sections;

class SummaryOfChargedAmount extends SingleLineFlatSection
{
    const SUMMARY_OF_CHARGED_AMOUNT = '83'; // in czech "součtová věta avíza pro zaúčtovanou částku"
}