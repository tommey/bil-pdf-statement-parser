<?php

namespace Tommey\BilPdfStatementParser\Parser;

use Exception;
use Tommey\BilPdfStatementParser\Entity\Transaction;

class XmlParser
{
    /** @var bool Print debug output */
    private $debug;

    /**
     * XmlParser constructor.
     *
     * @param bool $debug
     */
    public function __construct($debug = false)
    {
        $this->debug = $debug;
    }

    /**
     * @param string $xmlString
     *
     * @return Transaction[]
     *
     * @throws Exception
     */
    public function parse($xmlString)
    {
        $xml = simplexml_load_string($xmlString);

        if ($xml === false)
        {
            throw new Exception("Failed to parse XML string!");
        }

        $state        = State::START;
        $transactions = [];
        /** @var Transaction $transaction */
        $transaction  = null;

        if (empty($xml->page))
        {
            throw new Exception("Missing page tag from XML document!");
        }
        foreach ($xml->page as $page)
        {
            if (empty($page->text))
            {
                throw new Exception("Missing text tag (inside page tag) from XML document!");
            }
            foreach ($page->text as $text)
            {
                /** @var \SimpleXMLElement $text */
                $font    = intval($text->attributes()['font']);
                $content = strval($text);

                switch ($state)
                {
                    case State::HEADER_START:
                        if ($font == 1 && $content == 'Value')
                        {
                            $state = State::HEADER_CONTINUE;
                        }
                        break;

                    case State::HEADER_CONTINUE:
                        if ($font == 1 && $content == 'Amount')
                        {
                            $state = State::HEADER_END;
                        }
                        break;

                    case State::HEADER_END:
                        if ($font == 1)
                        {
                            if (preg_match('/^\d{2}\.\d{2} [A-Z]+.+?$/', $content))
                            {
                                $state = State::TRANSACTION_TYPE;

                                $transaction    = new Transaction();
                                $transactions[] = $transaction;

                                $transaction->setType($content);
                            }
                            else
                            {
                                $state = State::TRANSACTION_DESCRIPTION;

                                $transaction->appendToDescription($content);
                            }
                        }
                        break;

                    case State::TRANSACTION_TYPE:
                        if ($font == 1 && preg_match('/^\d{2}(\.\d{2}){2}$/', $content))
                        {
                            $state = State::TRANSACTION_DATE;

                            $transaction->setDate($content);
                        }
                        break;

                    case State::TRANSACTION_DATE:
                        if ($font == 1 && preg_match('/^(\d+\.)*?\d+\,\d+ (\-|\+)$/', $content))
                        {
                            $state = State::TRANSACTION_AMOUNT;

                            $transaction->setAmount($content);
                        }
                        break;

                    case State::TRANSACTION_AMOUNT:
                        if ($font == 1)
                        {
                            if (preg_match('/^\d{2}\.\d{2} [A-Z]+.+?$/', $content))
                            {
                                $state = State::TRANSACTION_TYPE;

                                $transaction    = new Transaction();
                                $transactions[] = $transaction;

                                $transaction->setType($content);
                            }
                            else
                            {
                                $state = State::TRANSACTION_DESCRIPTION;

                                $transaction->setDescription($content);
                            }
                        }
                        break;

                    case State::TRANSACTION_DESCRIPTION:
                        if ($font == 1)
                        {
                            if (preg_match('/^\d{2}\.\d{2} [A-Z]+.+?$/', $content))
                            {
                                $state = State::TRANSACTION_TYPE;

                                $transaction    = new Transaction();
                                $transactions[] = $transaction;

                                $transaction->setType($content);
                            }
                            else
                            {
                                $transaction->appendToDescription($content);
                            }
                        }
                        else
                        {
                            $state = State::START;
                        }
                        break;

                    default:
                        if ($font == 1 && $content == 'Date Communication')
                        {
                            $state = State::HEADER_START;
                        }
                        break;
                }

                if ($this->debug)
                {
                    $decision = str_pad(State::translate($state), 30, ' ', STR_PAD_RIGHT);

                    echo "$decision $font\t$content\n";
                }
            }
        }

        return $transactions;
    }
}
