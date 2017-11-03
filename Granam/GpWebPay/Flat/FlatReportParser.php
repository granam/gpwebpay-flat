<?php
declare(strict_types=1); // on PHP 7+ are standard PHP methods strict to types of given parameters

namespace Granam\GpWebPay\Flat;

use Granam\GpWebPay\Flat\Sections\CurrencySection;
use Granam\GpWebPay\Flat\Sections\DebtsAndHoldsSection;
use Granam\GpWebPay\Flat\Sections\DetailsOfDebtsAndTransactionsSection;
use Granam\GpWebPay\Flat\Sections\EndSection;
use Granam\GpWebPay\Flat\Sections\FlatSection;
use Granam\GpWebPay\Flat\Sections\MerchantSection;
use Granam\GpWebPay\Flat\Sections\StartSection;
use Granam\GpWebPay\Flat\Sections\SummarySection;
use Granam\GpWebPay\Flat\Sections\UnchargedTransactionsSection;
use Granam\Strict\Object\StrictObject;

class FlatReportParser extends StrictObject
{
    const CELL_DELIMITER = '"';

    public static function createDefault(): FlatReportParser
    {
        return new static(
            new StartSection(),
            [
                new CurrencySection(),
                new DebtsAndHoldsSection(),
                new DetailsOfDebtsAndTransactionsSection(),
                new MerchantSection(),
                new SummarySection(),
                new UnchargedTransactionsSection(),
            ],
            new EndSection()
        );
    }

    /** @var StartSection */
    private $startSection;
    /** @var array */
    private $bodySections;
    /** @var EndSection */
    private $endSection;

    /**
     * FlatReportParser constructor.
     * @param StartSection $startSection
     * @param array|FlatSection[] $bodySections
     * @param EndSection $endSection
     */
    public function __construct(StartSection $startSection, array $bodySections, EndSection $endSection)
    {
        $this->startSection = $startSection;
        $this->bodySections = $bodySections;
        $this->endSection = $endSection;
    }

    /**
     * @param array $parsedContent
     * @param ReportedPaymentKeysMapper $reportedPaymentKeysMapper
     * @return array|ReportedPayment[]
     */
    public function createPayments(array $parsedContent, ReportedPaymentKeysMapper $reportedPaymentKeysMapper): array
    {
        return array_map(
            function ($paymentValues) use ($reportedPaymentKeysMapper) {
                return new ReportedPayment($paymentValues, $reportedPaymentKeysMapper);
            },
            $parsedContent
        );
    }

    const CENTRAL_EUROPEAN_ENCODING = 'ISO-8859-2'; // which means CP1250 in Windows naming

    /**
     * @param string $content
     * @return array|string[][][]
     * @throws \Granam\GpWebPay\Flat\Exceptions\ContentToParseIsEmpty
     * @throws \Granam\GpWebPay\Flat\Exceptions\UnexpectedFlatFormat
     */
    public function parseCzechContent(string $content): array
    {
        return $this->parseContent($content, self::CENTRAL_EUROPEAN_ENCODING);
    }

    /**
     * @param string $content
     * @param string $isoEncoding
     * @return array|string[][][] [86 => [0 => ['MC Consumer Debit', 4, 600, -16.20, 583.80], 1 => ...]]
     * @throws \Granam\GpWebPay\Flat\Exceptions\ContentToParseIsEmpty
     * @throws \Granam\GpWebPay\Flat\Exceptions\UnexpectedFlatFormat
     */
    public function parseContent(string $content, string $isoEncoding): array
    {
        $byCodeIndexedRows = $this->parseRawContent($content, $isoEncoding);
        $firstRow = reset($byCodeIndexedRows);
        $firstCode = key($byCodeIndexedRows);
        if (!$this->startSection->isKnownCode($firstCode)) {
            throw new Exceptions\CorruptedFlatStructure(
                'Expected '
            );
        }
    }

    /**
     * @param string $content
     * @param string $contentIsoEncoding
     * @return array|string[][][] [86 => [0 => ['MC Consumer Debit', 4, 600, -16.20, 583.80], 1 => ...]]
     * @throws \Granam\GpWebPay\Flat\Exceptions\ContentToParseIsEmpty
     * @throws \Granam\GpWebPay\Flat\Exceptions\UnexpectedFlatFormat
     */
    private function parseRawContent(string $content, string $contentIsoEncoding): array
    {
        $content = trim($content);
        if ($content === '') {
            throw new Exceptions\ContentToParseIsEmpty('Nothing to parse. We got empty string');
        }
        $inUtf8 = self::toUtf8($content, $contentIsoEncoding);
        $rows = preg_split('(\n\r|\n|\r)$', $inUtf8); // documentation says "it's always \n\r, but we never know..."
        $byCodeRows = [];
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
            unset($row[0]); // remove code from the row
            $rowWithoutCode = array_values($row); // just to get sequential numeric indexes
            $byCodeRows[$code][] = $rowWithoutCode;
        }

        return $byCodeRows;
    }

    private static function toUtf8(string $string, string $isoEncoding)
    {
        /** @link https://stackoverflow.com/questions/8233517/what-is-the-difference-between-iconv-and-mb-convert-encoding-in-php# */
        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($string, $isoEncoding, 'UTF-8'); // works same regardless of platform
        }

        // iconv is just a wrapper of C iconv function, therefore it is platform-related
        return iconv(self::getIconvEncodingForPlatform($isoEncoding), 'UTF-8', $string);
    }

    /**
     * @param array $row
     * @param array $header
     * @return array
     * @throws \Granam\GpWebPay\Flat\Exceptions\ColumnsDoesNotMatchToHeader
     */
    private function indexByHeader(array $row, array $header): array
    {
        if (count($header) !== count($row)) {
            throw new Exceptions\ColumnsDoesNotMatchToHeader(
                'Count of columns of row ' . var_export($row, true) . ' does not match expected count of columns'
                . ' according to preceding header ' . var_export($header, true)
            );
        }

        return array_combine($header /* used as keys */, $row /* provides values */);
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
                $filename}' can not be read. Ensure it exists and can be accessible."
            );
        }
        $content = file_get_contents($filename);
        if ($content === false) {
            throw new Exceptions\ReadingContentOfFlatFileFailed(
                "Can not fetch content from given FLAT file '{
                $filename}'."
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
        return $this->parseContent($content, $fileEncoding);
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