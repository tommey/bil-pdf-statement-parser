<?php
/**
 * Example script - shows how to parse and output CSV from Bil.com PDF statement transaction list.
 */

use Tommey\BilPdfStatementParser\BilPdfStatementParser;

require __DIR__ . '/../vendor/autoload.php';

if ($argc < 2)
{
    echo "Missing arguments, usage: {$argv[0]} /path/to/directory/containing/pdf/files (-v)", PHP_EOL;
    exit(1);
}

$directory     = $argv[1];
$cellDelimiter = ',';
$enclosure     = '"';
$rowDelimiter  = "\n";
$escape        = "\\";
$debug         = @$argv[2] == '-v';

try
{
    echo BilPdfStatementParser::createCsvFromDirectory(
        $directory,
        $cellDelimiter,
        $enclosure,
        $rowDelimiter,
        $escape,
        $debug
    ), PHP_EOL;
}
catch (Exception $e)
{
    echo PHP_EOL, '!!! Oh no, something went wrong:', PHP_EOL;
    echo "\t" . $e->getMessage(), PHP_EOL;
    exit(2);
}
