<?php
declare(strict_types=1); // on PHP 7+ are standard PHP methods strict to types of given parameters

namespace Granam\GpWebPay\Flat\Sections;

class HeaderOfSection extends SingleLineFlatSection
{
    const HEADER_OF_ADVICES_AND_DETAILS = '51';

    public function __construct(array $values)
    {
        $values = $this->sanitizeHeader($values);
        parent::__construct($values);
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

    public function isUnique(): bool
    {
        return false; // there can be more than a single header, each per section
    }

}