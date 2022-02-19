<?php

namespace Framelix\Buhax\Storable\SystemValue;

use Framelix\Framelix\Db\StorableSchema;
use Framelix\Framelix\Storable\SystemValue;

/**
 * Outgoing Category
 * @property SummaryKey[]|null $summaryKeys
 * @property string $name
 * @property string|null $info
 * @property int $operationalSharePercent
 */
class OutgoingCategory extends SystemValue
{
    /**
     * Setup self storable schema
     * @param StorableSchema $selfStorableSchema
     */
    protected static function setupStorableSchema(StorableSchema $selfStorableSchema): void
    {
        parent::setupStorableSchema($selfStorableSchema);
        $selfStorableSchema->properties['operationalSharePercent']->length = 3;
    }

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