<?php

namespace Framelix\Buhax\Storable\SystemValue;

use Framelix\Buhax\Storable\Income;
use Framelix\Buhax\Storable\Outgoing;
use Framelix\Framelix\Storable\SystemValue;

/**
 * Summary Key
 * @property string $key
 * @property string $name
 * @property int $outgoingCategory
 * @property int $incomeCategory
 */
class SummaryKey extends SystemValue
{
    public const SUM_CATEGORY_PLUS = 1;
    public const SUM_CATEGORY_MINUS = 2;

    /**
     * Get html string
     * @return string
     */
    public function getHtmlString(): string
    {
        return $this->key . " - " . $this->name;
    }

    /**
     * Get a human-readable raw text representation of this instace
     * @return string
     */
    public function getRawTextString(): string
    {
        return $this->key;
    }

    /**
     * Get summable net amount for given storable
     * @param Income|Outgoing $storable
     * @return float
     */
    public function getSummableNet(Income|Outgoing $storable): float
    {
        if ($storable instanceof Outgoing) {
            $net = $storable->netOperational;
            if ($this->outgoingCategory === self::SUM_CATEGORY_MINUS) {
                $net *= -1;
            }
            return $net;
        }
        if ($storable instanceof Income) {
            $net = $storable->net;
            if ($this->incomeCategory === self::SUM_CATEGORY_MINUS) {
                $net *= -1;
            }
            return $net;
        }
    }
}