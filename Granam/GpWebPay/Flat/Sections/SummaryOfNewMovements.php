<?php
declare(strict_types=1); // on PHP 7+ are standard PHP methods strict to types of given parameters

namespace Granam\GpWebPay\Flat\Sections;

class SummaryOfNewMovements extends SingleLineFlatSection
{
    const SUMMARY_OF_NEW_MOVEMENTS = '81'; // in czech "součtová věta avíza za dávku nových pohybů"
}