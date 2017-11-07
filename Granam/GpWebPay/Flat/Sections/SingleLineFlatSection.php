<?php
declare(strict_types=1); // on PHP 7+ are standard PHP methods strict to types of given parameters

namespace Granam\GpWebPay\Flat\Sections;

use Granam\GpWebPay\Flat\Exceptions\CorruptedFlatStructure;
use Granam\Strict\Object\StrictObject;

abstract class SingleLineFlatSection extends StrictObject implements FlatSection
{
    private $values;

    /**
     * FlatSection constructor.
     * @param array $values
     * @throws \Granam\GpWebPay\Flat\Exceptions\CorruptedFlatStructure
     */
    public function __construct(array $values)
    {
        if (count($values) === 0) {
            throw new CorruptedFlatStructure('Given row is empty');
        }
        $this->values = $values;
    }

    /**
     * @return array|string[]
     */
    public function getValues(): array
    {
        return $this->values;
    }

    public function isUnique(): bool
    {
        return true;
    }

}