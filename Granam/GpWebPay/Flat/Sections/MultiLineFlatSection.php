<?php
declare(strict_types=1); // on PHP 7+ are standard PHP methods strict to types of given parameters

namespace Granam\GpWebPay\Flat\Sections;

use Granam\GpWebPay\Flat\Exceptions\CorruptedFlatStructure;
use Granam\Strict\Object\StrictObject;
use Granam\Tools\ValueDescriber;

abstract class MultiLineFlatSection extends StrictObject implements FlatSection
{

    /** @var array|string[] */
    private $values = [];
    /** @var HeaderOfSection|null */
    private $header;

    /**
     * MultiLineFlatSection constructor.
     * @param array $values
     * @param HeaderOfSection|null $header
     * @throws \Granam\GpWebPay\Flat\Exceptions\CorruptedFlatStructure
     */
    public function __construct(array $values, ?HeaderOfSection $header = null)
    {
        $this->addValues($values);
        $this->header = $header;
    }

    /**
     * @param array $values
     * @throws \Granam\GpWebPay\Flat\Exceptions\CorruptedFlatStructure
     */
    public function addValues(array $values)
    {
        if (count($values) === 0) {
            throw new CorruptedFlatStructure('Values for ' . static::class . ' are empty');
        }
        array_map(function ($value) {
            if ($value !== null && !is_scalar($value)) {
                throw new CorruptedFlatStructure(
                    'Values of a section have to be flat array, got ' . ValueDescriber::describe($value) . ' for ' . static::class
                );
            }
        }, $values);
        $this->values[] = $values;
    }

    /**
     * @return array
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * @return HeaderOfSection|null
     */
    public function getHeader():? HeaderOfSection
    {
        return $this->header;
    }

    public function isUnique(): bool
    {
        return false;
    }
}