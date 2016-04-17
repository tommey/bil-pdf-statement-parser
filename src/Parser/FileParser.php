<?php

namespace Tommey\BilPdfStatementParser\Parser;

use Exception;
use Tommey\BilPdfStatementParser\Entity\Transaction;

class FileParser
{
    const COMMAND = '/usr/bin/pdftohtml';

    /** @var bool */
    private $debug;
    /**
     * @var XmlParser
     */
    private $parser;

    /**
     * FileParser constructor.
     *
     * @param XmlParser $parser
     * @param bool      $debug
     *
     * @throws Exception   If self::COMMAND not found on the filesystem.
     */
    public function __construct(XmlParser $parser, $debug = false)
    {
        $this->debug  = $debug;
        $this->parser = $parser;

        if (!file_exists(self::COMMAND))
        {
            throw new Exception("Missing command ({self::COMMAND}), it is required for pre-processing the PDF files!");
        }
    }

    /**
     * Parses given PDF file, returns extracted transaction list.
     *
     * @param string $file
     *
     * @return Transaction[]
     *
     * @throws Exception   On invalid file or content.
     */
    public function parse($file)
    {
        if (!file_exists($file))
        {
            throw new Exception("File ($file) not found for parsing!");
        }

        $xml = shell_exec(self::COMMAND . ' -i -stdout -xml ' . escapeshellarg($file));

        if (null === $xml)
        {
            throw new Exception("File ($file) parsing failed, please check if it is a valid PDF file.");
        }

        return $this->parser->parse($xml);
    }
}
