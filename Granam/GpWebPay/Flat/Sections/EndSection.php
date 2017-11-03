<?php
declare(strict_types=1); // on PHP 7+ are standard PHP methods strict to types of given parameters

namespace Granam\GpWebPay\Flat\Sections;

class EndSection extends FlatSection
{
    const END = '99'; // in czech "závěrečná věta"

    public function getKnownCodes(): array
    {
        return [self::END];
    }

}