<?php
declare(strict_types=1); // on PHP 7+ are standard PHP methods strict to types of given parameters

namespace Granam\GpWebPay\Flat\Sections;

class SummarySection extends FlatSection
{

    const SUMMARY_OF_NEW_MOVEMENTS = '81'; // in czech "součtová věta avíza za dávku nových pohybů"
    const SUMMARY_OF_DISCHARGED_DEBT = '82'; // in czech "součtová věta avíza umořených dluhů"
    const SUMMARY_OF_CHARGED_AMOUNT = '83'; // in czech "součtová věta avíza pro zaúčtovanou částku"
    const SUMMARY_OF_DETAILED_SECTION = '85'; // in czech "součtová věta detailního oddílu"
    const SUMMARY_OF_DETAILED_SECTION_PER_CARD = '86'; // in czech "součtová věta detailního oddílu za typ karetního produktu"
    const SUMMARY_OF_NEW_MOVEMENTS_FOR_MERCHANT_WITH_CASHBACK = '89'; // in czech "součtová věta avíza za dávku nových pohybů pro obchodníka s cashback rozšířením"
    const SUMMARY_OF_DETAILED_SECTION_FOR_MERCHANT_WITH_CASHBACK = '90'; // in czech "součtová věta detailního oddílu pro obchodníka s cashback rozšířením"
    const SUMMARY_OF_DETAILED_SECTION_PER_CARD_FOR_MERCHANT_WITH_CASHBACK = '91'; // in czech "součtová věta detailního oddílu za typ karetního produktu pro obchodníka s cashback rozšířením"

    public function getKnownCodes(): array
    {
        return [
            self::SUMMARY_OF_NEW_MOVEMENTS,
            self::SUMMARY_OF_DISCHARGED_DEBT,
            self::SUMMARY_OF_CHARGED_AMOUNT,
            self::SUMMARY_OF_DETAILED_SECTION,
            self::SUMMARY_OF_DETAILED_SECTION_PER_CARD,
            self::SUMMARY_OF_NEW_MOVEMENTS_FOR_MERCHANT_WITH_CASHBACK,
            self::SUMMARY_OF_DETAILED_SECTION_FOR_MERCHANT_WITH_CASHBACK,
            self::SUMMARY_OF_DETAILED_SECTION_PER_CARD_FOR_MERCHANT_WITH_CASHBACK,
        ];
    }

}