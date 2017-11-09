<?php
declare(strict_types=1); // on PHP 7+ are standard PHP methods strict to types of given parameters

namespace Granam\GpWebPay\Flat;

use Granam\Mail\Download\ImapEmailAttachmentFetcher;
use Granam\Mail\Download\ImapSearchCriteria;
use Granam\Strict\Object\StrictObject;

class FlatReportParser extends StrictObject
{
    const CELL_DELIMITER = '"';
    const CENTRAL_EUROPEAN_ENCODING = 'ISO-8859-2'; // which means CP1250 in Windows naming
    const CZECH_EMAIL_SUBJECT_DATE_FORMAT = 'd.m.Y';

    /**
     * @param ImapEmailAttachmentFetcher $imapEmailAttachmentFetcher
     * @param \DateTime $emailOfDay
     * @param CzechECommerceTransactionHeaderMapper $eCommerceTransactionHeaderMapper
     * @return FlatContent|null
     * @throws \Granam\GpWebPay\Flat\Exceptions\TooManyFlatAttachmentsFromSingleDay
     * @throws \Granam\GpWebPay\Flat\Exceptions\CanNotReadFlatFile
     * @throws \Granam\GpWebPay\Flat\Exceptions\ReadingContentOfFlatFileFailed
     * @throws \Granam\GpWebPay\Flat\Exceptions\FlatFileIsEmpty
     * @throws \Granam\GpWebPay\Flat\Exceptions\ContentToParseIsEmpty
     * @throws \Granam\GpWebPay\Flat\Exceptions\UnexpectedFlatFormat
     * @throws \Granam\GpWebPay\Flat\Exceptions\CorruptedFlatStructure
     * @throws \Granam\GpWebPay\Flat\Exceptions\UnexpectedCode
     */
    public function createFlatContentFromCzechEmailAttachment(
        ImapEmailAttachmentFetcher $imapEmailAttachmentFetcher,
        \DateTime $emailOfDay,
        CzechECommerceTransactionHeaderMapper $eCommerceTransactionHeaderMapper
    )
    {
        return $this->createFlatContentFromEmailAttachment(
            $imapEmailAttachmentFetcher,
            $emailOfDay,
            new DateFormat(self::CZECH_EMAIL_SUBJECT_DATE_FORMAT),
            self::CENTRAL_EUROPEAN_ENCODING,
            $eCommerceTransactionHeaderMapper
        );
    }

    /**
     * @param ImapEmailAttachmentFetcher $imapEmailAttachmentFetcher
     * @param \DateTime $reportOfDay
     * @param DateFormat $emailSubjectDateFormat
     * @param string $flatAttachmentIsoEncoding
     * @param ECommerceTransactionHeaderMapper $eCommerceTransactionHeaderMapper
     * @return null|FlatContent
     * @throws \Granam\GpWebPay\Flat\Exceptions\TooManyFlatAttachmentsFromSingleDay
     * @throws \Granam\GpWebPay\Flat\Exceptions\CanNotReadFlatFile
     * @throws \Granam\GpWebPay\Flat\Exceptions\ReadingContentOfFlatFileFailed
     * @throws \Granam\GpWebPay\Flat\Exceptions\FlatFileIsEmpty
     * @throws \Granam\GpWebPay\Flat\Exceptions\ContentToParseIsEmpty
     * @throws \Granam\GpWebPay\Flat\Exceptions\UnexpectedFlatFormat
     * @throws \Granam\GpWebPay\Flat\Exceptions\CorruptedFlatStructure
     * @throws \Granam\GpWebPay\Flat\Exceptions\UnexpectedCode
     */
    public function createFlatContentFromEmailAttachment(
        ImapEmailAttachmentFetcher $imapEmailAttachmentFetcher,
        \DateTime $reportOfDay,
        DateFormat $emailSubjectDateFormat,
        string $flatAttachmentIsoEncoding,
        ECommerceTransactionHeaderMapper $eCommerceTransactionHeaderMapper
    )
    {
        // emails from "monday" are send day after
        $emailOfDay = $reportOfDay->modify('+ 1 day');
        $filter = (new ImapSearchCriteria())
            ->filterSubjectContains('OMS - data file ' . $emailSubjectDateFormat->format($emailOfDay))
            ->filterByDate($emailOfDay);
        $attachments = $imapEmailAttachmentFetcher->fetchAttachments($filter);
        $attachments = $this->filterAttachments($attachments, $reportOfDay);
        if (count($attachments) === 0) {
            return null;
        }
        if (count($attachments) > 1) {
            throw new Exceptions\TooManyFlatAttachmentsFromSingleDay(
                'Expected a single email attachment with a FLAT file for date ' . $reportOfDay->format('c')
                . ', got ' . count($attachments) . ' of them: ' . var_export($attachments, true)
            );
        }
        $attachment = reset($attachments);

        return $this->createFlatContentFromFile(
            $attachment['filepath'],
            $flatAttachmentIsoEncoding,
            $eCommerceTransactionHeaderMapper
        );
    }

    private function filterAttachments(array $attachments, \DateTime $ofDay)
    {
        $filteredAttachments = [];
        foreach ($attachments as $attachment) {
            if (preg_match(
                '~^VDAT-\d+-\d+-\d+-' . preg_quote($ofDay->format('Ymd'), '~') . '\.TXT$~',
                $attachment['name'] ?: $attachment['original_filename'])
            ) {
                $filteredAttachments[] = $attachment;
            }
        }

        return $filteredAttachments;
    }

    /**
     * @param string $czechFileName
     * @param ECommerceTransactionHeaderMapper $eCommerceTransactionHeaderMapper
     * @return FlatContent
     * @throws \Granam\GpWebPay\Flat\Exceptions\CanNotReadFlatFile
     * @throws \Granam\GpWebPay\Flat\Exceptions\ReadingContentOfFlatFileFailed
     * @throws \Granam\GpWebPay\Flat\Exceptions\FlatFileIsEmpty
     * @throws \Granam\GpWebPay\Flat\Exceptions\ContentToParseIsEmpty
     * @throws \Granam\GpWebPay\Flat\Exceptions\UnexpectedFlatFormat
     * @throws \Granam\GpWebPay\Flat\Exceptions\CorruptedFlatStructure
     * @throws \Granam\GpWebPay\Flat\Exceptions\UnexpectedCode
     */
    public function createFlatContentFromCzechFile(
        string $czechFileName,
        ECommerceTransactionHeaderMapper $eCommerceTransactionHeaderMapper
    ): FlatContent
    {
        return new FlatContent($this->parseCzechFile($czechFileName), $eCommerceTransactionHeaderMapper);
    }

    /**
     * @param string $fileName
     * @param string $isoEncoding
     * @param ECommerceTransactionHeaderMapper $eCommerceTransactionHeaderMapper
     * @return FlatContent
     * @throws \Granam\GpWebPay\Flat\Exceptions\CanNotReadFlatFile
     * @throws \Granam\GpWebPay\Flat\Exceptions\ReadingContentOfFlatFileFailed
     * @throws \Granam\GpWebPay\Flat\Exceptions\FlatFileIsEmpty
     * @throws \Granam\GpWebPay\Flat\Exceptions\ContentToParseIsEmpty
     * @throws \Granam\GpWebPay\Flat\Exceptions\UnexpectedFlatFormat
     * @throws \Granam\GpWebPay\Flat\Exceptions\CorruptedFlatStructure
     * @throws \Granam\GpWebPay\Flat\Exceptions\UnexpectedCode
     */
    public function createFlatContentFromFile(
        string $fileName,
        string $isoEncoding,
        ECommerceTransactionHeaderMapper $eCommerceTransactionHeaderMapper
    ): FlatContent
    {
        return new FlatContent($this->parseFile($fileName, $isoEncoding), $eCommerceTransactionHeaderMapper);
    }

    /**
     * @param string $content
     * @return FlatContent
     * @throws \Granam\GpWebPay\Flat\Exceptions\ContentToParseIsEmpty
     * @throws \Granam\GpWebPay\Flat\Exceptions\UnexpectedFlatFormat
     * @throws \Granam\GpWebPay\Flat\Exceptions\CorruptedFlatStructure
     * @throws \Granam\GpWebPay\Flat\Exceptions\UnexpectedCode
     */
    public function createCzechFlatContent(string $content): FlatContent
    {
        return new FlatContent(
            $this->parseValues($content, self::CENTRAL_EUROPEAN_ENCODING),
            new CzechECommerceTransactionHeaderMapper()
        );
    }

    /**
     * @param string $content
     * @param string $isoEncoding
     * @param ECommerceTransactionHeaderMapper $eCommerceTransactionHeaderMapper
     * @return FlatContent
     * @throws \Granam\GpWebPay\Flat\Exceptions\ContentToParseIsEmpty
     * @throws \Granam\GpWebPay\Flat\Exceptions\UnexpectedFlatFormat
     * @throws \Granam\GpWebPay\Flat\Exceptions\CorruptedFlatStructure
     * @throws \Granam\GpWebPay\Flat\Exceptions\UnexpectedCode
     */
    public function createFlatContent(
        string $content,
        string $isoEncoding,
        ECommerceTransactionHeaderMapper $eCommerceTransactionHeaderMapper
    ): FlatContent
    {
        return new FlatContent($this->parseValues($content, $isoEncoding), $eCommerceTransactionHeaderMapper);
    }

    /**
     * @param string $content
     * @return array|string[][][]
     * @throws \Granam\GpWebPay\Flat\Exceptions\ContentToParseIsEmpty
     * @throws \Granam\GpWebPay\Flat\Exceptions\UnexpectedFlatFormat
     */
    public function parseCzechValues(string $content): array
    {
        return $this->parseValues($content, self::CENTRAL_EUROPEAN_ENCODING);
    }

    /**
     * @param string $content
     * @param string $contentIsoEncoding
     * @return array|string[][][] [0 => [86 => ['MC Consumer Debit', 4, 600, -16.20, 583.80]], 1 => [51 => ]...]]
     * @throws \Granam\GpWebPay\Flat\Exceptions\ContentToParseIsEmpty
     * @throws \Granam\GpWebPay\Flat\Exceptions\UnexpectedFlatFormat
     */
    public function parseValues(string $content, string $contentIsoEncoding): array
    {
        $content = trim($content);
        if ($content === '') {
            throw new Exceptions\ContentToParseIsEmpty('Nothing to parse . We got empty string');
        }
        $inUtf8 = self::toUtf8($content, $contentIsoEncoding);
        $rows = preg_split('~(\r\n|\n|\r)~', $inUtf8, -1, PREG_SPLIT_NO_EMPTY); // documentation says "it's always \n\r, but we never know..."
        $indexedRows = [];
        foreach ($rows as $stringRow) {
            $row = explode(self::CELL_DELIMITER, $stringRow);
            if (count($row) === 0) {
                continue;
            }
            $code = $row[0];
            if (!ctype_digit($code)) {
                throw new Exceptions\UnexpectedFlatFormat(
                    'Expected numeric code at the beginning of FLAT row, got ' . var_export($code, true)
                );
            }
            unset($row[0]); // removing code from values
            $indexedRows[] = ['code' => $code, 'values' => $row];
        }

        return $indexedRows;
    }

    private static function toUtf8(string $string, string $isoEncoding)
    {
        /** @link https://stackoverflow.com/questions/8233517/what-is-the-difference-between-iconv-and-mb-convert-encoding-in-php# */
        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($string, 'UTF-8', mb_detect_encoding($string, [$isoEncoding, 'UTF-8'], true)); // works same regardless of platform
        }

        // iconv is just a wrapper of C iconv function, therefore it is platform-related
        return iconv(self::getIconvEncodingForPlatform($isoEncoding), 'UTF-8', $string);
    }

    /**
     * @param string $filename
     * @return array
     * @throws \Granam\GpWebPay\Flat\Exceptions\CanNotReadFlatFile
     * @throws \Granam\GpWebPay\Flat\Exceptions\ReadingContentOfFlatFileFailed
     * @throws \Granam\GpWebPay\Flat\Exceptions\FlatFileIsEmpty
     * @throws \Granam\GpWebPay\Flat\Exceptions\UnexpectedFlatFormat
     */
    public function parseCzechFile(string $filename): array
    {
        return $this->parseFile($filename, self::CENTRAL_EUROPEAN_ENCODING);
    }

    /**
     * @param string $filename
     * @param string $fileEncoding
     * @return array|string[][]
     * @throws \Granam\GpWebPay\Flat\Exceptions\CanNotReadFlatFile
     * @throws \Granam\GpWebPay\Flat\Exceptions\ReadingContentOfFlatFileFailed
     * @throws \Granam\GpWebPay\Flat\Exceptions\FlatFileIsEmpty
     * @throws \Granam\GpWebPay\Flat\Exceptions\UnexpectedFlatFormat
     */
    public function parseFile(string $filename, string $fileEncoding): array
    {
        if (!is_readable($filename)) {
            throw new Exceptions\CanNotReadFlatFile(
                "Given FLAT file '{
                $filename}' can not be read . Ensure it exists and can be accessible . "
            );
        }
        $content = file_get_contents($filename);
        if ($content === false) {
            throw new Exceptions\ReadingContentOfFlatFileFailed(
                "Can not fetch content from given FLAT file '{
                $filename}' . "
            );
        }
        $content = trim($content);
        if ($content === '') {
            throw new Exceptions\FlatFileIsEmpty(
                "Given FLAT file '{
                $filename}' does not have any content"
            );
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return $this->parseValues($content, $fileEncoding);
    }

    private static function getIconvEncodingForPlatform(string $isoEncoding)
    {
        if (strtoupper(strpos($isoEncoding, 3)) !== 'ISO' || strtoupper(substr(PHP_OS, 3)) !== 'WIN' /* windows */) {
            return $isoEncoding;
        }
        /** http://php.net/manual/en/function.iconv.php#71192 */
        switch ($isoEncoding) {
            case 'ISO-8859-2' :
                return 'CP1250'; // Eastern European
            case 'ISO-8859-5':
                return 'CP1251'; // Cyrillic
            case 'ISO-8859-1':
                return 'CP1252'; // Western European
            case 'ISO-8859-7':
                return 'CP1253'; // Greek
            case 'ISO-8859-9':
                return 'CP1254'; // Turkish
            case 'ISO-8859-8':
                return 'CP1255'; // Hebrew
            case 'ISO-8859-6':
                return 'CP1256'; // Arabic
            case 'ISO-8859-4':
                return 'CP1257'; // Baltic
            default :
                return $isoEncoding;
        }
    }
}