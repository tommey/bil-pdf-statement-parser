<?php

namespace Tommey\BilPdfStatementParser\Formatter;

use Tommey\BilPdfStatementParser\Entity\Transaction;

interface TransactionListFormatterInterface
{
    /**
     * Formats transaction list to string representation.
     *
     * @param Transaction[] $transactions
     *
     * @return string
     */
    public function format(array $transactions);
}
