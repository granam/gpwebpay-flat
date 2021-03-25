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
use Granam\TestWithMockery\TestWithMockery;
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
        $emailOfDay = new \DateTimeImmutable('2017-11-09');
        $flatContent = $flatReportParser->createFlatContentFromCzechEmailAttachment(
            $this->createImapEmailAttachmentFetcher($emailOfDay),
            $emailOfDay,
            new CzechECommerceTransactionHeaderMapper()
        );
        self::assertNotNull($flatContent);
        self::assertInstanceOf(FlatContent::class, $flatContent);
    }

    /**
     * @param \DateTimeInterface $emailOfDay
     * @return ImapEmailAttachmentFetcher|MockInterface
     */
    private function createImapEmailAttachmentFetcher(\DateTimeInterface $emailOfDay): ImapEmailAttachmentFetcher
    {
        $imapEmailAttachmentFetcher = $this->mockery(ImapEmailAttachmentFetcher::class);
        $imapEmailAttachmentFetcher->shouldReceive('fetchAttachments')
            ->andReturnUsing(static function (ImapSearchCriteria $imapSearchCriteria) use ($emailOfDay) {
                $reportOfDay = (new \DateTimeImmutable($emailOfDay->format(DATE_ATOM)))->modify('+ 1 day');
                $czechEmailSubjectDateFormat = (new DateFormat(FlatReportParser::CZECH_EMAIL_SUBJECT_DATE_FORMAT));
                $expectedImapSearchCriteria = (new ImapSearchCriteria())
                    ->filterSubjectContains('OMS - data file ' . $czechEmailSubjectDateFormat->format($reportOfDay))
                    ->filterByDate($reportOfDay);
                self::assertEquals($imapSearchCriteria, $expectedImapSearchCriteria);
                return [
                    0 => [
                        'filepath' => __DIR__ . '/data/VDAT-000819-123450001-123450001-20171110.TXT',
                        'original_filename' => 'VDAT-000819-123450001-123450001-20171110.TXT',
                        'name' => 'VDAT-000819-123450001-123450001-20171110.TXT',
                    ],
                ];
            });

        return $imapEmailAttachmentFetcher;
    }

    /**
     * @test
     */
    public function I_can_create_flat_content_from_email_attachment_of_specific_day()
    {
        $imapEmailAttachmentFetcher = $this->createDumbImapEmailAttachmentFetcher();
        $tomorrow = new \DateTime('tomorrow');
        $afterTomorrow = (clone $tomorrow)->modify('+ 1 day');
        self::assertNotEquals($tomorrow, $afterTomorrow);
        $dateFormat = new DateFormat('Y~m~d FOO BAR');
        $imapEmailAttachmentFetcher->shouldReceive('fetchAttachments')
            ->once()
            ->with($this->type(ImapSearchCriteria::class))
            ->andReturnUsing(function (ImapSearchCriteria $imapSearchCriteria) use ($afterTomorrow, $dateFormat) {
                self::assertEquals($afterTomorrow, $imapSearchCriteria->getByDate());
                self::assertMatchesRegularExpression(
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
    private function createDumbImapEmailAttachmentFetcher(): ImapEmailAttachmentFetcher
    {
        return $this->mockery(ImapEmailAttachmentFetcher::class);
    }
}
