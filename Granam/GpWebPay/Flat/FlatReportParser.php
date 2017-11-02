<?php
declare(strict_types=1); // on PHP 7+ are standard PHP methods strict to types of given parameters

namespace Granam\GpWebPay\Flat;

use Granam\Strict\Object\StrictObject;

class FlatReportParser extends StrictObject
{
    const REPORT_IDENTIFIER_CODE = '00'; // in czech "úvodní věta"

    const PID = '01'; // in czech "věta avíza, úroveň účtování IČO"
    const MERCHANT_PLACE = '02'; // in czech "věta avíza, úroveň účtování Obchodní místo"
    const TERMINAL = '03'; // in czech "věta avíza, úroveň účtování Terminál"
    const PID_OF_MERCHANT_WITH_CASHBACK = '04'; // in czech "věta avíza, úroveň účtování IČO pro obchodníka s cashback rozšířením"
    const PLACE_OF_MERCHANT_WITH_CASHBACK = '05'; // in czech "věta avíza, úroveň účtování Obchodní místo pro obchodníka s cashback rozšířením"
    const TERMINAL_OF_MERCHANT_WITH_CASHBACK = '06'; // in czech "věta avíza, úroveň účtování Terminál pro obchodníka s cashback rozšířením"

    const UNCHARGED_TRANSACTIONS = '11'; // in czech "věta nezaúčtovaných transakcí"
    const UNCHARGED_TRANSACTIONS_OF_MERCHANT_WITH_CASHBACK = '13'; // in czech "věta nezaúčtovaných transakcí pro obchodníka s cashback rozšířením"

    const DETAIL_SECTION = '21'; // in czech "věta detailního oddílu"
    const UNDISCHARGED_DEBT = '22'; // in czech "věta neumořeného dluhu"
    const DISCHARGED_DEBT = '23'; // in czech "věta umoření dluhu"
    const E_COMMERCE_DETAIL_SECTION = '24'; // in czech "věta detailního oddílu e-commerce"
    const DETAIL_SECTION_WITH_OPTIONAL_TRANSACTION_ID = '25'; // in czech "věta detailního oddílu s volitelným id trans."
    const DETAIL_SECTION_FOR_MERCHANT_WITH_CASHBACK = '26'; // in czech "věta detailního oddílu pro obchodníka s cashback rozšířením"
    const DETAIL_SECTION_WITH_OPTIONAL_TRANSACTION_ID_FOR_MERCHANT_WITH_CASHBACK = '27'; // in czech "věta detailního oddílu s volitelným id trans. pro obchodníka s cashback rozšířením"

    const DESCRIPTION_OF_ADVICES_AND_DETAILS = '51'; // in czech "popisná věta avíz i detailů"
    const DESCRIPTION_OF_UNDISCHARGED_DEBTS = '52'; // in czech "popisná věta neumořených dluhů"
    const DESCRIPTION_OF_NEW_HOLDS = '53'; // in czech "popisná věta nových holdů"
    const DESCRIPTION_OF_DISCHARGED_DEBTS = '54'; // in czech "popisná věta umoření dluhů"

    const MERCHANT_ADDRESS_CODE = '98';
    const MERCHANT_CURRENCY_CODE = '61';
    const HEADER_CODE = '51';
    const DAILY_SUMMARY_PER_CARD_TYPE_CODE = '03';
    const SUMMARY_PER_DAY_CODE = '81';
    const SUMMARY_PER_DAILY_BATCH_CODE = '85';
    const SUMMARY_PER_CARD_TYPE = '86';
    const UNKNOWN_83_CODE = '83'; // ?
    const PAYMENT_CODE = '24';
    const END_CODE = '99';

    const CELL_DELIMITER = '"';

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

    /**
     * @param string $content
     * @param string $contentEncoding
     * @return array|string[][][] [86 => [0 => ['MC Consumer Debit', 4, 600, -16.20, 583.80], 1 => ...]]
     * @throws \Granam\GpWebPay\Flat\Exceptions\ContentToParseIsEmpty
     * @throws \Granam\GpWebPay\Flat\Exceptions\UnexpectedFlatFormat
     * @throws \Granam\GpWebPay\Flat\Exceptions\ColumnsDoesNotMatchToHeader
     */
    public function parseContent(string $content, string $contentEncoding): array
    {
        $content = trim($content);
        if ($content === '') {
            throw new Exceptions\ContentToParseIsEmpty('Nothing to parse. We got empty string');
        }
        $inUtf8 = self::toUtf8($content, $contentEncoding);
        $rows = preg_split('(\n\r|\n|\r)$', $inUtf8); // documentation says "it's always \n\r, but we never know..."
        $byCodeRows = [];
        $currentHeader = [];
        $codeRightAfterHeader = false;
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
            if ($code === self::HEADER_CODE) {
                $rowWithoutCode = $this->sanitizeHeader($rowWithoutCode); // sadly there is an error in one of headers
                $currentHeader = $rowWithoutCode;
            } elseif ($currentHeader) {
                if ($codeRightAfterHeader === false) {
                    $codeRightAfterHeader = $code;
                } elseif ($codeRightAfterHeader === $code) { // code does not change so the header is for current row as well
                    $rowWithoutCode = $this->indexByByHeader($rowWithoutCode, $currentHeader); // ['Číslo pokladny' => 951703, ...]
                } else { // header and same code chain are no more valid, let's reset them
                    $currentHeader = [];
                    $codeRightAfterHeader = false;
                }
            }
            $byCodeRows[$code][] = $rowWithoutCode;
        }

        return $byCodeRows;
    }

    private function sanitizeHeader(array $header): array
    {
        $orderRef2Ref1Key = array_search('OrderRef2Ref1', $header, true);
        if ($orderRef2Ref1Key === false) {
            return $header;
        }
        $orderRef2Key = array_search('OrderRef2', $header, true);
        if ($orderRef2Key === false) {
            return $header;
        }
        unset($header[$orderRef2Ref1Key]); // removing broken header column

        return $header;
    }

    /**
     * @param array $row
     * @param array $header
     * @return array
     * @throws \Granam\GpWebPay\Flat\Exceptions\ColumnsDoesNotMatchToHeader
     */
    private function indexByByHeader(array $row, array $header): array
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

    private static function toUtf8(string $string, string $sourceEncoding)
    {
        /** @link https://stackoverflow.com/questions/8233517/what-is-the-difference-between-iconv-and-mb-convert-encoding-in-php# */
        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($string, $sourceEncoding, 'UTF - 8'); // works same regardless of platform
        }

        // iconv is just a wrapper of C iconv function, therefore it is platform-related
        return iconv(self::getIconvEncodingForPlatform($sourceEncoding), 'UTF - 8', $string);
    }

    private static function getIconvEncodingForPlatform(string $isoEncoding)
    {
        if (strtoupper(strpos($isoEncoding, 3)) !== 'ISO' || strtoupper(substr(PHP_OS, 3)) !== 'WIN' /* windows */) {
            return $isoEncoding;
        }
        /** http://php.net/manual/en/function.iconv.php#71192 */
        switch ($isoEncoding) {
            case 'ISO - 8859 - 2' :
                return 'CP1250'; // Eastern European
            case 'ISO - 8859 - 5':
                return 'CP1251'; // Cyrillic
            case 'ISO - 8859 - 1':
                return 'CP1252'; // Western European
            case 'ISO - 8859 - 7':
                return 'CP1253'; // Greek
            case 'ISO - 8859 - 9':
                return 'CP1254'; // Turkish
            case 'ISO - 8859 - 8':
                return 'CP1255'; // Hebrew
            case 'ISO - 8859 - 6':
                return 'CP1256'; // Arabic
            case 'ISO - 8859 - 4':
                return 'CP1257'; // Baltic
            default :
                return $isoEncoding;
        }
    }
}