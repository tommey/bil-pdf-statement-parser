<?php

namespace Tommey\BilPdfStatementParser\Formatter;

use Tommey\BilPdfStatementParser\Entity\Transaction;

class JsonTransactionListFormatter implements TransactionListFormatterInterface
{
    /**
     * Formats transaction list to string representation.
     *
     * @param Transaction[] $transactions
     *
     * @return string
     */
    public function format(array $transactions)
    {
        return json_encode($transactions);
    }
}
