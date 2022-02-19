<?php

namespace Framelix\Buhax\Storable;

use Framelix\Buhax\Storable\SystemValue\OutgoingCategory;
use Framelix\Framelix\Date;
use Framelix\Framelix\Db\StorableSchema;
use Framelix\Framelix\Storable\StorableExtended;
use Framelix\Framelix\Url;
use Framelix\Framelix\View;

use function round;

/**
 * Depreciation
 * @property Attachment[]|null $attachments
 * @property Date $date
 * @property string|null $comment
 * @property OutgoingCategory $outgoingCategory
 * @property float $netTotal
 * @property mixed $netSplit
 * @property int $years
 * @property bool $flagDone
 */
class Depreciation extends StorableExtended
{
    /**
     * Setup self storable schema
     * @param StorableSchema $selfStorableSchema
     */
    protected static function setupStorableSchema(StorableSchema $selfStorableSchema): void
    {
        parent::setupStorableSchema($selfStorableSchema);
        $moneyProps = ['netTotal'];
        foreach ($moneyProps as $moneyProp) {
            $selfStorableSchema->properties[$moneyProp]->length = 14;
            $selfStorableSchema->properties[$moneyProp]->decimals = 2;
        }
        $selfStorableSchema->properties['years']->length = 2;
        $selfStorableSchema->addIndex("date", "index");
    }

    /**
     * Get edit url
     * @return Url|null
     */
    public function getEditUrl(): ?Url
    {
        return View::getUrl(\Framelix\Buhax\View\Depreciation::class)->setParameter('id', $this);
    }

    /**
     * Auto generate the net split for all the years
     */
    public function setNetSplit(): void
    {
        $afa = round($this->netTotal / $this->years, 2);
        $startYear = $this->date->dateTime->getYear();
        $endYear = $startYear + $this->years - 1;
        $rest = $this->netTotal;
        $isSecondHalf = $this->date->dateTime->getMonth() > 6;
        if ($isSecondHalf) {
            $endYear++;
        }
        $netSplit = [];
        for ($year = $startYear; $year <= $endYear; $year++) {
            $afaUse = $afa;
            if ($year === $startYear && $isSecondHalf) {
                $afaUse = $afaUse / 2;
            }
            if ($year === $endYear) {
                $afaUse = $rest;
            }
            $afaUse = round($afaUse, 2);
            if ($afaUse > $rest) {
                $afaUse = $rest;
            }
            $netSplit[] = ['year' => $year, 'value' => $afaUse];
            $rest = round($rest - $afaUse, 2);
        }
        $this->netSplit = $netSplit;
    }

    /**
     * Is this storable deletable
     * @return bool
     */
    public function isDeletable(): bool
    {
        return !$this->flagDone;
    }

    /**
     * Delete from database
     * @param bool $force Force deletion even if isDeletable() is false
     */
    public function delete(bool $force = false): void
    {
        self::deleteMultiple($this->attachments);
        parent::delete($force);
    }


}