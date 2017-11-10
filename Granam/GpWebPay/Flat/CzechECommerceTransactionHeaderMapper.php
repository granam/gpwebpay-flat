<?php
declare(strict_types=1); // on PHP 7+ are standard PHP methods strict to types of given parameters

namespace Granam\GpWebPay\Flat;

class CzechECommerceTransactionHeaderMapper extends ECommerceTransactionHeaderMapper
{
    public function __construct()
    {
        parent::__construct(
            new DateFormat('d.m.Y'), // Date format
            new \DateTimeZone('Europe/Prague'),
            'Číslo pokladny',
            'Číslo sumáře',
            'Datum transakce',
            'Ref.číslo',
            'Identifikátor transakce ID',
            'Autorizační kód',
            'Částka v Kč',
            'Poplatky v Kč',
            'Částka k úhradě',
            'Druh karty',
            'OrderRef1',
            'OrderRef2'
        );
    }
}