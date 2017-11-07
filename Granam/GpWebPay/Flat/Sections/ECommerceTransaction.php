<?php
declare(strict_types=1); // on PHP 7+ are standard PHP methods strict to types of given parameters

namespace Granam\GpWebPay\Flat\Sections;

class ECommerceTransaction extends MultiLineFlatSection
{
    const E_COMMERCE_TRANSACTION = '24'; // in czech "věta detailního oddílu e-commerce"
}