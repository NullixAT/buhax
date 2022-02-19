<?php

namespace Framelix\Buhax\StorableMeta;

use Framelix\Framelix\Date;
use Framelix\Framelix\Form\Field\Select;
use Framelix\Framelix\Html\TableCell;
use Framelix\Framelix\Storable\Storable;
use Framelix\Framelix\StorableMeta;

use function array_reverse;

/**
 * Fixation Storable Meta
 */
class Fixation extends StorableMeta
{
    /**
     * The storable
     * @var \Framelix\Buhax\Storable\Fixation
     */
    public Storable $storable;

    /**
     * Initialize this meta
     */
    protected function init(): void
    {
        $this->tableDefault->initialSort = ["-dateFrom"];

        $property = $this->createProperty("pdf");
        $property->setVisibility(null, false);
        $property->setVisibility(self::CONTEXT_TABLE, true);
        $property->setLabel("");
        $property->valueCallable = function () {
            $tableCell = new TableCell();
            $tableCell->icon = "download";
            $tableCell->iconUrl = $this->storable->attachment?->getDownloadUrl() ?? null;
            $tableCell->iconTooltip = "__framelix_download_file__";
            return $tableCell;
        };

        $this->addDefaultPropertiesAtStart();


        $minMax = \Framelix\Buhax\Storable\Fixation::getNextFixationDateRange();
        $range = Date::rangeDays($minMax[0], $minMax[1]);
        $property = $this->createProperty("dateFrom");
        $property->field = new Select();
        $property->field->searchable = true;
        foreach ($range as $date) {
            $property->field->addOption($date->getDbValue(), $date);
        }

        $property = $this->createProperty("dateTo");
        $property->field = new Select();
        $property->field->searchable = true;
        $range = array_reverse($range);
        foreach ($range as $date) {
            $property->field->addOption($date->getDbValue(), $date);
        }


        $this->addDefaultPropertiesAtEnd();
    }
}