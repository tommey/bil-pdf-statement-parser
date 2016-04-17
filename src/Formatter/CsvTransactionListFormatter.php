<?php

namespace Tommey\BilPdfStatementParser\Formatter;

use Tommey\BilPdfStatementParser\Entity\Transaction;

class CsvTransactionListFormatter implements TransactionListFormatterInterface
{
    /** @var string */
    private $cellDelimiter;
    /** @var string */
    private $enclosure;
    /** @var string */
    private $rowDelimiter;
    /** @var string */
    private $escape;

    /**
     * CsvTransactionListFormatter constructor.
     *
     * @param string $cellDelimiter Delimiter for cells.
     * @param string $enclosure     Character to be wrapped around the cell.
     * @param string $rowDelimiter  Delimiter for rows.
     * @param string $escape        Escape character to be used for escaping enclosure characters inside cell data.
     */
    public function __construct($cellDelimiter = ',', $enclosure = '"', $rowDelimiter = "\n", $escape = "\\")
    {
        $this->cellDelimiter = $cellDelimiter;
        $this->enclosure     = $enclosure;
        $this->rowDelimiter  = $rowDelimiter;
        $this->escape        = $escape;
    }

    /**
     * Formats transaction list to string representation.
     *
     * @param Transaction[] $transactions
     *
     * @return string CSV
     */
    public function format(array $transactions)
    {
        if ($this->enclosure === '')
        {
            /** @var Transaction $transaction */
            foreach ($transactions as $key => &$transaction)
            {
                $transaction = implode(
                    $this->cellDelimiter,
                    $transaction->jsonSerialize()
                );
            }
        }
        else
        {
            /** @var Transaction $transaction */
            foreach ($transactions as $key => &$transaction)
            {
                $transaction = implode(
                    $this->cellDelimiter,
                    array_map(
                        [$this, 'enclose'],
                        $transaction->jsonSerialize()
                    )
                );
            }
        }

        return implode($this->rowDelimiter, $transactions);
    }

    /**
     * Wraps value with enclosing characters with escaping it inside the value.
     *
     * @param string $value
     *
     * @return string
     */
    private function enclose($value)
    {
        return $this->enclosure . str_replace($this->enclosure, $this->escape . $this->enclosure, $value) . $this->enclosure;
    }
}
