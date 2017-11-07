<?php
namespace Granam\Tests\GpWebPay\Flat;

use Granam\GpWebPay\Flat\CzechECommerceTransactionHeaderMapper;
use Granam\GpWebPay\Flat\FlatContent;
use Granam\GpWebPay\Flat\FlatReportParser;
use PHPUnit\Framework\TestCase;

class FlatReportParserTest extends TestCase
{
    /**
     * @test
     */
    public function I_can_parse_czech_flat_file()
    {
        $flatReportParser = new FlatReportParser();
        $flatContentFromCzechFile = $flatReportParser->createFlatContentFromCzechFile(
            __DIR__ . '/../../../Documentations/cs/Vzor FLAT.txt',
            new CzechECommerceTransactionHeaderMapper()
        );
        self::assertInstanceOf(FlatContent::class, $flatContentFromCzechFile);
        $eCommerceTransactions = $flatContentFromCzechFile->getECommerceTransactions();
        self::assertNotNull($eCommerceTransactions);
        self::assertSame(266, $eCommerceTransactions->count());
        self::assertCount(266, $eCommerceTransactions);
        self::assertSame($eCommerceTransactions->getSummary(), $eCommerceTransactions->getPaidAmountInMerchantCurrencySummary());
        self::assertSame(241585.0, $eCommerceTransactions->getPaidAmountInMerchantCurrencySummary());
        self::assertSame(-1219.88, $eCommerceTransactions->getFeesInMerchantCurrencySummary());
        self::assertSame(240365.12, $eCommerceTransactions->getPaidAmountWithoutFeesSummary());
    }
}