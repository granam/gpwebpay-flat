<?php
declare(strict_types=1); // on PHP 7+ are standard PHP methods strict to types of given parameters

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