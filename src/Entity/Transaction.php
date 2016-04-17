<?php

namespace Tommey\BilPdfStatementParser\Entity;

use JsonSerializable;

class Transaction implements JsonSerializable
{
    /** @var string Transaction type */
    private $type;
    /** @var string Transaction date in Y.m.d format */
    private $date;
    /** @var float Transaction amount - minus for outgoing, plus for incoming */
    private $amount;
    /** @var string Transaction additional information */
    private $description;

    /**
     * Transaction constructor.
     *
     * @param string $type
     * @param string $date
     * @param float  $amount
     * @param string $description
     */
    public function __construct($type = null, $date = null, $amount = null, $description = null)
    {
        foreach (['type', 'date', 'amount', 'description'] as $field)
        {
            if (null !== $$field)
            {
                $this->{"set$field"}($$field);
            }
        }
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        // Remove date part from the beginning.
        $type = ltrim($type, '0123456789. ');

        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param string $date
     */
    public function setDate($date)
    {
        // Change to Y-m-d from d.m.y.
        $date = preg_replace('/^(\d+)\.(\d+)\.(\d+)$/', '$3-$2-$1', $date);
        // Add century.
        $date = substr($date, 0, 1) >= '7'
            ? "19$date"
            : "20$date";

        $this->date = $date;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float|string $amount
     */
    public function setAmount($amount)
    {
        // Transform string format (<million>.<thousand>.<digit>,<decimal> <sign>) to float (<sign><digit>.<decimal>).
        if (preg_match('/^(\d+\.)*?\d+\,\d+ (\-|\+)$/', $amount))
        {
            list ($number, $sign) = explode(' ', $amount);
            $amount = floatval($sign . str_replace(['.', ','], ['', '.'], $number));
        }

        $this->amount = $amount;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @param string $text Appended with a line break to the description.
     */
    public function appendToDescription($text)
    {
        $this->description .= PHP_EOL . $text;
    }

    /**
     * @return string TSV
     */
    public function __toString()
    {
        return implode(
            "\t",
            [
                $this->type,
                $this->date,
                $this->amount,
                $this->description,
            ]
        );
    }

    /**
     * @return array [type, date, amount, description]
     */
    function jsonSerialize()
    {
        return [
            $this->type,
            $this->date,
            $this->amount,
            $this->description,
        ];
    }
}
