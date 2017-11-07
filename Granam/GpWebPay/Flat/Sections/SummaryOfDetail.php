<?php
declare(strict_types=1); // on PHP 7+ are standard PHP methods strict to types of given parameters

namespace Granam\GpWebPay\Flat\Sections;

class SummaryOfDetail extends SingleLineFlatSection
{
    const SUMMARY_OF_DETAIL = '85'; // in czech "součtová věta detailního oddílu"
}