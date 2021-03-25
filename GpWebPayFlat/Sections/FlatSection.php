<?php declare(strict_types=1);

namespace Granam\GpWebPay\Flat\Sections;

interface FlatSection
{
    /**
     * @return array|string[]
     */
    public function getValues(): array;

    /**
     * @return bool
     */
    public function isUnique(): bool;
}
