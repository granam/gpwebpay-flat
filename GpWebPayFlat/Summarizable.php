<?php
namespace Granam\GpWebPay\Flat;

interface Summarizable
{
    public function getSummary(): float;
}