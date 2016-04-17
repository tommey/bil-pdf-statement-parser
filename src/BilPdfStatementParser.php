<?php

namespace Tommey\BilPdfStatementParser;

use Exception;
use Tommey\BilPdfStatementParser\Entity\Transaction;
use Tommey\BilPdfStatementParser\Formatter\CsvTransactionListFormatter;
use Tommey\BilPdfStatementParser\Formatter\HtmlTransactionListFormatter;
use Tommey\BilPdfStatementParser\Parser\DirectoryParser;
use Tommey\BilPdfStatementParser\Parser\FileParser;
use Tommey\BilPdfStatementParser\Parser\XmlParser;

class BilPdfStatementParser
{
    /**
     * Parses all PDF files from the given directory and renders HTML table from the extracted transaction list.
     *
     * @param string $directory       Directory to process.
     * @param array  $tableProperties Properties to add to the HTML table.
     * @param bool   $debug           Enable debug mode, components will print out debug messages if enabled.
     *
     * @return string HTML table representation of transactions.
     *
     * @throws Exception   On error.
     */
    public static function createHtmlFromDirectory($directory, array $tableProperties = [], $debug = false)
    {
        $formatter = new HtmlTransactionListFormatter($tableProperties);

        return $formatter->format(self::createTransactionListFromDirectory($directory, $debug));
    }

    /**
     * Parses all PDF files from the given directory and renders CSV from the extracted transaction list.
     *
     * @param string $directory     Directory to process.
     * @param string $cellDelimiter Delimiter for cells.
     * @param string $enclosure     Character to be wrapped around the cell.
     * @param string $rowDelimiter  Delimiter for rows.
     * @param string $escape        Escape character to be used for escaping enclosure characters inside cell data.
     * @param bool   $debug         Enable debug mode, components will print out debug messages if enabled.
     *
     * @return string CSV representation of transactions.
     *
     * @throws Exception On error.
     */
    public static function createCsvFromDirectory(
        $directory,
        $cellDelimiter = ',',
        $enclosure = '"',
        $rowDelimiter = "\n",
        $escape = "\\",
        $debug = false
    ) {
        $formatter = new CsvTransactionListFormatter($cellDelimiter, $enclosure, $rowDelimiter, $escape);
        
        return $formatter->format(self::createTransactionListFromDirectory($directory, $debug));
    }

    /**
     * Parses all PDF files from the given directory and returns transaction list.
     *
     * @param string $directory Directory to process.
     * @param bool   $debug     Enable debug mode, components will print out debug messages if enabled.
     *
     * @return Transaction[]
     */
    public static function createTransactionListFromDirectory($directory, $debug = false)
    {
        return self::createDirectoryParser($debug)->parse($directory);
    }

    /**
     * Creates DirectoryParser.
     * 
     * @param bool $debug Enable debug mode, components will print out debug messages if enabled.
     *
     * @return DirectoryParser
     */
    public static function createDirectoryParser($debug = false)
    {
        return new DirectoryParser(self::createFileParser($debug), $debug);
    }

    /**
     * Creates FileParser.
     *
     * @param bool $debug Enable debug mode, components will print out debug messages if enabled.
     *
     * @return FileParser
     */
    public static function createFileParser($debug = false)
    {
        return new FileParser(new XmlParser($debug), $debug);
    }
}
