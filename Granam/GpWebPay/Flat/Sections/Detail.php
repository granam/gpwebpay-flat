<?php
declare(strict_types=1); // on PHP 7+ are standard PHP methods strict to types of given parameters

namespace Granam\GpWebPay\Flat\Sections;

class Detail extends MultiLineFlatSection
{
    const DETAIL_SECTION = '21'; // in czech "věta detailního oddílu"
}