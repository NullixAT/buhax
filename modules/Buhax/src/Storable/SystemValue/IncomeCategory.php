<?php

namespace Framelix\Buhax\Storable\SystemValue;

use Framelix\Framelix\Storable\SystemValue;

/**
 * Income Category
 * @property SummaryKey[]|null $summaryKeys
 * @property string $name
 * @property string|null $info
 */
class IncomeCategory extends SystemValue
{
    /**
     * Get html string
     * @return string
     */
    public function getHtmlString(): string
    {
        return $this->name;
    }

    /**
     * Get a human-readable raw text representation of this instace
     * @return string
     */
    public function getRawTextString(): string
    {
        return $this->name;
    }
}