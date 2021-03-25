<?php declare(strict_types=1);

namespace Granam\GpWebPay\Flat\Sections;

use Granam\GpWebPay\Flat\Exceptions\CorruptedFlatStructure;
use Granam\Strict\Object\StrictObject;
use Granam\Tools\ValueDescriber;

abstract class MultiLineFlatSection extends StrictObject implements FlatSection
{

    /** @var array|string[] */
    private $values = [];
    /** @var HeaderOfSection|null */
    private $headerOfSection;

    /**
     * @param array $values
     * @param HeaderOfSection|null $headerOfSection
     * @throws \Granam\GpWebPay\Flat\Exceptions\CorruptedFlatStructure
     */
    public function __construct(array $values, ?HeaderOfSection $headerOfSection = null)
    {
        $this->headerOfSection = $headerOfSection;
        $this->addValues($values);
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
            if (!is_scalar($value)) {
                throw new CorruptedFlatStructure(
                    'Values of a section have to be flat array of scalars, got ' . ValueDescriber::describe($value) . ' for ' . static::class
                );
            }
        }, $values);
        if ($this->headerOfSection === null) {
            $this->values[] = $values;
            return;
        }
        $headerValues = $this->headerOfSection->getValues();
        if (count($headerValues) !== count($values)) {
            throw new CorruptedFlatStructure(
                'Count of values for ' . static::class . ' is ' . count($values) . ', but header expected ' . count($headerValues) . ' values'
                . ";\n values: '" . implode(',', $values) . "'"
                . ";\n header: '" . implode(',', $headerValues) . "'"
            );
        }
        $mappedValues = [];
        reset($headerValues);
        foreach ($values as $value) {
            $key = current($headerValues);
            $mappedValues[$key] = $value;
            next($headerValues);
        }

        $this->values[] = $mappedValues;
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
    public function getHeaderOfSection():? HeaderOfSection
    {
        return $this->headerOfSection;
    }

    public function isUnique(): bool
    {
        return false;
    }
}
