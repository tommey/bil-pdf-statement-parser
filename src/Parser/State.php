<?php

namespace Tommey\BilPdfStatementParser\Parser;

class State
{
    const START                   = 1;
    const HEADER_START            = 10;
    const HEADER_CONTINUE         = 11;
    const HEADER_END              = 12;
    const TRANSACTION_TYPE        = 20;
    const TRANSACTION_DATE        = 21;
    const TRANSACTION_AMOUNT      = 22;
    const TRANSACTION_DESCRIPTION = 23;

    /**
     * @var string[]
     */
    private static $states
        = [
            self::START                   => 'START',
            self::HEADER_START            => 'HEADER_START',
            self::HEADER_CONTINUE         => 'HEADER_CONTINUE',
            self::HEADER_END              => 'HEADER_END',
            self::TRANSACTION_TYPE        => 'TRANSACTION_TYPE',
            self::TRANSACTION_DATE        => 'TRANSACTION_DATE',
            self::TRANSACTION_AMOUNT      => 'TRANSACTION_AMOUNT',
            self::TRANSACTION_DESCRIPTION => 'TRANSACTION_DESCRIPTION',
        ];

    /**
     * Returns string representation for the given state.
     *
     * @param int $state
     *
     * @return string
     */
    public static function translate($state)
    {
        return isset(self::$states[$state])
            ? self::$states[$state]
            : 'UNKNOWN';
    }
}
