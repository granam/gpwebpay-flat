<?php
namespace Granam\Tests\GpWebPay\Flat;

use Granam\GpWebPay\Flat\CzechECommerceTransactionHeaderMapper;
use Granam\GpWebPay\Flat\DateFormat;
use Granam\GpWebPay\Flat\FlatContent;
use Granam\GpWebPay\Flat\FlatReportParser;
use Granam\Mail\Download\ImapEmailAttachmentFetcher;
use Granam\Mail\Download\ImapReadOnlyConnection;
use Granam\Mail\Download\ImapSearchCriteria;
use Granam\Tests\Mail\Download\ImapEmailAttachmentFetcherTest;
use Granam\Tests\Tools\TestWithMockery;
use Mockery\MockInterface;

class FlatReportParserTest extends TestWithMockery
{
    /**
     * @test
     */
    public function I_can_parse_czech_flat_file()
    {
        $flatReportParser = new FlatReportParser();
        $flatContentFromCzechFile = $flatReportParser->createFlatContentFromCzechFile(
            __DIR__ . '/data/VDAT-000819-123450001-123450001-20171110.TXT',
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
        foreach ($eCommerceTransactions as $transaction) {
            self::assertSame('Europe/Prague', $transaction->getTransactionDate()->getTimezone()->getName());
        }

        $silvesterTransactions = $flatContentFromCzechFile->getECommerceTransactions(new \DateTime('2017-12-31'));
        self::assertNull($silvesterTransactions);

        $mainDayTransactions = $flatContentFromCzechFile->getECommerceTransactions(new \DateTime('2017-11-09'));
        self::assertNotNull($mainDayTransactions);
        self::assertSame(266 - 5, $mainDayTransactions->count());
        self::assertCount(266 - 5, $mainDayTransactions);
        self::assertSame($mainDayTransactions->getSummary(), $mainDayTransactions->getPaidAmountInMerchantCurrencySummary());
        self::assertSame(241585.0 - 5935.0, $mainDayTransactions->getPaidAmountInMerchantCurrencySummary());
        self::assertSame(-1219.88 + 19.68, $mainDayTransactions->getFeesInMerchantCurrencySummary());
        self::assertSame(240365.12 - 5915.32, $mainDayTransactions->getPaidAmountWithoutFeesSummary());

        $beforeMainDayTransactions = $flatContentFromCzechFile->getECommerceTransactions(new \DateTime('2017-11-08'));
        self::assertNotNull($beforeMainDayTransactions);
        self::assertSame(5, $beforeMainDayTransactions->count());
        self::assertCount(5, $beforeMainDayTransactions);
        self::assertSame($beforeMainDayTransactions->getSummary(), $beforeMainDayTransactions->getPaidAmountInMerchantCurrencySummary());
        self::assertSame(5935.0, $beforeMainDayTransactions->getPaidAmountInMerchantCurrencySummary());
        self::assertSame(-19.68, $beforeMainDayTransactions->getFeesInMerchantCurrencySummary());
        self::assertSame(5915.32, $beforeMainDayTransactions->getPaidAmountWithoutFeesSummary());
    }

    /**
     * @test
     */
    public function I_can_get_flat_content_from_czech_email()
    {
        $flatReportParser = new FlatReportParser();
        $flatContent = $flatReportParser->createFlatContentFromCzechEmailAttachment(
            new ImapEmailAttachmentFetcher($this->getImapReadOnlyConnection()),
            new \DateTime('2017-11-09'),
            new CzechECommerceTransactionHeaderMapper()
        );
        self::assertNotNull($flatContent);
        self::assertInstanceOf(FlatContent::class, $flatContent);
    }

    private function getImapReadOnlyConnection(): ImapReadOnlyConnection
    {
        // re-using settings from IMAP attachment library
        $reflection = new \ReflectionClass(ImapEmailAttachmentFetcherTest::class);
        $getImapReadOnlyConnection = $reflection->getMethod('getImapReadOnlyConnection');
        $getImapReadOnlyConnection->setAccessible(true);

        return $getImapReadOnlyConnection->invoke(new ImapEmailAttachmentFetcherTest());
    }

    /**
     * @test
     */
    public function I_can_create_flat_content_from_email_attachment_of_specific_day()
    {
        $imapEmailAttachmentFetcher = $this->createImapEmailAttachmentFetcher();
        $tomorrow = new \DateTime('tomorrow');
        $afterTomorrow = (clone $tomorrow)->modify('+ 1 day');
        self::assertNotEquals($tomorrow, $afterTomorrow);
        $dateFormat = new DateFormat('Y~m~d FOO BAR');
        $imapEmailAttachmentFetcher->shouldReceive('fetchAttachments')
            ->once()
            ->with($this->type(ImapSearchCriteria::class))
            ->andReturnUsing(function (ImapSearchCriteria $imapSearchCriteria) use ($afterTomorrow, $dateFormat) {
                self::assertEquals($afterTomorrow, $imapSearchCriteria->getByDate());
                self::assertRegExp(
                    '~' . preg_quote($afterTomorrow->format($dateFormat->getAsString()), '~') . '$~',
                    $imapSearchCriteria->getSubjectContains()
                );

                return [];
            });
        $flatReportParser = new FlatReportParser();
        $flatContent = $flatReportParser->createFlatContentFromEmailAttachment(
            $imapEmailAttachmentFetcher,
            $tomorrow,
            $dateFormat,
            'UTF-8',
            new CzechECommerceTransactionHeaderMapper()
        );

        self::assertNull($flatContent);
    }

    /**
     * @return ImapEmailAttachmentFetcher|MockInterface
     */
    private function createImapEmailAttachmentFetcher(): ImapEmailAttachmentFetcher
    {
        return $this->mockery(ImapEmailAttachmentFetcher::class);
    }
}