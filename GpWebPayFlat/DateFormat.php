<?php declare(strict_types=1);

namespace Granam\GpWebPay\Flat;

use Granam\Mail\Download\ToString;
use Granam\Strict\Object\StrictObject;

class DateFormat extends StrictObject implements ToString
{
    /**
     * @var string
     */
    private $dateFormat;

    public function __construct(string $dateFormat)
    {
        $this->dateFormat = $dateFormat;
    }

    /**
     * @return string
     */
    public function getAsString(): string
    {
        return $this->dateFormat;
    }

    public function __toString()
    {
        return $this->getAsString();
    }

    public function format(\DateTimeInterface $dateTime): string
    {
        return $dateTime->format($this->getAsString());
    }

}
