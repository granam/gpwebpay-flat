<?php
declare(strict_types=1); // on PHP 7+ are standard PHP methods strict to types of given parameters

namespace Granam\GpWebPay\Flat\Sections\Exceptions;

use Granam\GpWebPay\Flat\Exceptions\InvalidArgumentException;

class UnexpectedCode extends InvalidArgumentException implements Logic
{

}