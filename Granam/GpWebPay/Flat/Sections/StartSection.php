<?php
declare(strict_types=1); // on PHP 7+ are standard PHP methods strict to types of given parameters

namespace Granam\GpWebPay\Flat\Sections;

class StartSection extends FlatSection
{
    const START = '00'; // in czech "úvodní věta"

    public function getKnownCodes(): array
    {
        return [self::START];
    }

    private function sanitizeHeader(array $header): array
    {
        $orderRef2Ref1Key = array_search('OrderRef2Ref1', $header, true);
        if ($orderRef2Ref1Key === false) {
            return $header;
        }
        $orderRef2Key = array_search('OrderRef2', $header, true);
        if ($orderRef2Key === false) {
            return $header;
        }
        unset($header[$orderRef2Ref1Key]); // removing broken header column

        return $header;
    }
}