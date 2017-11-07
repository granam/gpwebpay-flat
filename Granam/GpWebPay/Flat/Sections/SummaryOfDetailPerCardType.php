<?php
declare(strict_types=1); // on PHP 7+ are standard PHP methods strict to types of given parameters

namespace Granam\GpWebPay\Flat\Sections;

class SummaryOfDetailPerCardType extends MultiLineFlatSection
{
    const SUMMARY_OF_DETAIL_PER_CARD_TYPE = '86'; // in czech "součtová věta detailního oddílu za typ karetního produktu"
}