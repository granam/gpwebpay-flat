<?php declare(strict_types=1);

namespace Granam\GpWebPay\Flat\Sections;

use Granam\GpWebPay\Flat\ECommerceTransactionHeaderMapper;

class HeaderOfSection extends SingleLineFlatSection
{
    const HEADER_OF_SECTION = '51';

    /**
     * @param array $values
     * @param ECommerceTransactionHeaderMapper $commerceTransactionHeaderMapper
     * @throws \Granam\GpWebPay\Flat\Exceptions\CorruptedFlatStructure
     */
    public function __construct(array $values, ECommerceTransactionHeaderMapper $commerceTransactionHeaderMapper)
    {
        $values = $this->sanitizeHeader($values, $commerceTransactionHeaderMapper);
        parent::__construct($values);
    }

    private function sanitizeHeader(array $header, ECommerceTransactionHeaderMapper $commerceTransactionHeaderMapper): array
    {
        $nameOfAuthorizationCode = $commerceTransactionHeaderMapper->getLocalizedAuthorizationCode();
        $authorizationCodeKey = array_search($nameOfAuthorizationCode, $header, true);
        if ($authorizationCodeKey !== false) {
            unset($header[$authorizationCodeKey]);
        }
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
