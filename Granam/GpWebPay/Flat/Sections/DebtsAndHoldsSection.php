<?php
declare(strict_types=1); // on PHP 7+ are standard PHP methods strict to types of given parameters

namespace Granam\GpWebPay\Flat\Sections;

class DebtsAndHoldsSection extends FlatSection
{

    const HEADER_OF_ADVICES_AND_DETAILS = '51'; // in czech "popisná věta avíz i detailů"
    const DESCRIPTION_OF_UNDISCHARGED_DEBTS = '52'; // in czech "popisná věta neumořených dluhů"
    const DESCRIPTION_OF_NEW_HOLDS = '53'; // in czech "popisná věta nových holdů"
    const DESCRIPTION_OF_DISCHARGED_DEBTS = '54'; // in czech "popisná věta umoření dluhů"

    public function getKnownCodes(): array
    {
        return [
            self::HEADER_OF_ADVICES_AND_DETAILS,
            self::DESCRIPTION_OF_UNDISCHARGED_DEBTS,
            self::DESCRIPTION_OF_NEW_HOLDS,
            self::DESCRIPTION_OF_DISCHARGED_DEBTS,
        ];
    }

}