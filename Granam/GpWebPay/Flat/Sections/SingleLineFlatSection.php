<?php
declare(strict_types=1); // on PHP 7+ are standard PHP methods strict to types of given parameters

namespace Granam\GpWebPay\Flat\Sections;

use Granam\GpWebPay\Flat\Exceptions\CorruptedFlatStructure;
use Granam\Strict\Object\StrictObject;
use Granam\Tools\ValueDescriber;

abstract class SingleLineFlatSection extends StrictObject implements FlatSection
{
    private $values;

    /**
     * @param array $values
     * @throws \Granam\GpWebPay\Flat\Exceptions\CorruptedFlatStructure
     */
    public function __construct(array $values)
    {
        if (count($values) === 0) {
            throw new CorruptedFlatStructure('Values for ' . static::class . ' are empty');
        }
        array_map(function ($value) {
            if (!is_scalar($value)) {
                throw new CorruptedFlatStructure(
                    'Values of a section have to be flat array of scalars, got ' . ValueDescriber::describe($value) . ' for ' . static::class
                );
            }
        }, $values);
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