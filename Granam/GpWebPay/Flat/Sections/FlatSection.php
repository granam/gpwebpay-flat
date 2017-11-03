<?php
declare(strict_types=1); // on PHP 7+ are standard PHP methods strict to types of given parameters

namespace Granam\GpWebPay\Flat\Sections;

use Granam\Strict\Object\StrictObject;

abstract class FlatSection extends StrictObject
{
    /**
     * @param string $code
     * @return bool
     */
    public function isKnownCode(string $code): bool
    {
        return in_array($code, $this->getKnownCodes(), true);
    }

    /**
     * @return array|string[]
     */
    abstract public function getKnownCodes(): array;

}