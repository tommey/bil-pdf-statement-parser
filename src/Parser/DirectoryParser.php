<?php

namespace Tommey\BilPdfStatementParser\Parser;

use DirectoryIterator;
use Exception;
use Tommey\BilPdfStatementParser\Entity\Transaction;

class DirectoryParser
{
    /** @var bool */
    private $debug;
    /**
     * @var FileParser
     */
    private $parser;

    /**
     * DirectoryParser constructor.
     *
     * @param FileParser $parser
     * @param bool       $debug
     */
    public function __construct(FileParser $parser, $debug = false)
    {
        $this->debug  = $debug;
        $this->parser = $parser;
    }

    /**
     * Parses all PDF files inside directory and returns the extracted transaction list.
     *
     * @param string $directory
     *
     * @return Transaction[]
     *
     * @throws Exception   On invalid directory, file, content.
     */
    public function parse($directory)
    {
        if (!is_dir($directory))
        {
            throw new Exception("Directory ($directory) not found for parsing!");
        }

        $iterator     = new DirectoryIterator($directory);
        $transactions = [];
        foreach ($iterator as $file)
        {
            $path = "$directory/$file";
            if ($this->debug)
            {
                echo "Processing file: $path";
            }
            if ($file->getExtension() == 'pdf')
            {
                $transactions = array_merge($transactions, $this->parser->parse($path));

                if ($this->debug)
                {
                    echo " - done\n";
                }
            }
            elseif ($this->debug)
            {
                echo " - skipped\n";
            }
        }

        return $transactions;
    }
}
