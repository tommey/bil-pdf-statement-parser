<?php

namespace Tommey\BilPdfStatementParser\Formatter;

use Tommey\BilPdfStatementParser\Entity\Transaction;

class HtmlTransactionListFormatter extends CsvTransactionListFormatter
{
    /** @var string */
    private $tableProperties;

    /**
     * HtmlTransactionListFormatter constructor.
     *
     * Sets up CSV formatter underneath.
     *
     * @param array $tableProperties Properties for the generated table.
     */
    public function __construct(array $tableProperties = [])
    {
        parent::__construct('</td><td>', '', '</td></tr><tr><td>', '');

        $properties = '';
        foreach ($tableProperties as $property => $value)
        {
            $properties .= " $property=\"$value\"";
        }

        $this->tableProperties = $properties;
    }

    /**
     * Formats transaction list to HTML table representation.
     *
     * @param Transaction[] $transactions
     *
     * @return string
     */
    public function format(array $transactions)
    {
        $body = '<tr><td>' . parent::format($transactions) . '</td></tr>';

        return <<<EOC
            <table$this->tableProperties>
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    $body
                </tbody>
            </table>
EOC;
    }
}
