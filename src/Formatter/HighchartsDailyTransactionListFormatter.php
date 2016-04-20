<?php

namespace Tommey\BilPdfStatementParser\Formatter;

use Tommey\BilPdfStatementParser\Entity\Transaction;

class HighchartsDailyTransactionListFormatter implements TransactionListFormatterInterface
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
        $daily = [];
        foreach ($transactions as $transaction)
        {
            list($type, $date, $amount, $description) = $transaction->jsonSerialize();

            // Format date to milliseconds.
            $date = strtotime($date . '12:00:00') . '000';

            if (!isset($daily[$date]))
            {
                $daily[$date] = $amount;
            }
            else
            {
                $daily[$date] += $amount;
            }
        }

        ksort($daily);

        $balance = [];
        $total   = 0;
        foreach ($daily as $day => $amount)
        {
            $total += $amount;

            $balance[$day] = $total;
        }

        return json_encode(
            [
                'xData'    => array_keys($daily),
                'datasets' => [
                    [
                        'name'          => 'Daily balance',
                        'data'          => array_values($balance),
                        'unit'          => 'EUR',
                        'type'          => 'line',
                        'valueDecimals' => 2
                    ],
                    [
                        'name'          => 'Daily transactions',
                        'data'          => array_values($daily),
                        'unit'          => 'EUR',
                        'type'          => 'column',
                        'valueDecimals' => 2
                    ]
                ]
            ]
        );
    }
}
