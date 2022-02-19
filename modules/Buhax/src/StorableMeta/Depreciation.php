<?php

namespace Framelix\Buhax\StorableMeta;

use Framelix\Buhax\View\Outgoings;
use Framelix\Framelix\Db\LazySearchCondition;
use Framelix\Framelix\Html\QuickSearch;
use Framelix\Framelix\Html\Table;
use Framelix\Framelix\Html\TableCell;
use Framelix\Framelix\Storable\Storable;
use Framelix\Framelix\StorableMeta;
use Framelix\Framelix\View;

use function date;

/**
 * Depreciation Storable Meta
 */
class Depreciation extends StorableMeta
{
    /**
     * The storable
     * @var \Framelix\Buhax\Storable\Depreciation
     */
    public Storable $storable;

    /**
     * Initialize this meta
     */
    protected function init(): void
    {
        $this->tableDefault->addColumnFlag('createIncome', Table::COLUMNFLAG_REMOVE_IF_EMPTY);

        $property = $this->createProperty("createIncome");
        $property->setVisibility(null, false);
        $property->setVisibility(self::CONTEXT_TABLE, true);
        $property->setLabel('');
        $property->valueCallable = function () {
            $year = (int)date("Y");
            $yearSplitRequired = false;
            if ($this->storable->netSplit) {
                foreach ($this->storable->netSplit as $row) {
                    if ($row['year'] === $year && !\Framelix\Buhax\Storable\Outgoing::getByCondition(
                            'depreciation = {0} && YEAR(date) = {1}',
                            [$this->storable->id, $year]
                        )) {
                        $yearSplitRequired = true;
                        break;
                    }
                }
            }
            if ($yearSplitRequired) {
                $tableCell = new TableCell();
                $tableCell->icon = "paid";
                $tableCell->iconTooltip = "__buhax_storable_depreciation_createoutgoing__";
                $tableCell->iconColor = "primary";
                $tableCell->iconUrl = View::getUrl(Outgoings::class)->setParameter('fromDepreciation', $this->storable);
                $tableCell->iconUrlBlank = true;
                return $tableCell;
            }
            return null;
        };


        $this->addDefaultPropertiesAtStart();

        $property = $this->createProperty("attachments");
        $property->addDefaultField();

        $property = $this->createProperty("date");
        $property->addDefaultField();

        $property = $this->createProperty("comment");
        $property->addDefaultField();

        $property = $this->createProperty("outgoingCategory");
        $property->addDefaultField();

        $property = $this->createProperty("netTotal");
        $property->addDefaultField();

        $property = $this->createProperty("years");
        $property->addDefaultField();

        $property = $this->createProperty("flagDone");
        $property->addDefaultField();

        $this->addDefaultPropertiesAtEnd();
    }

    /**
     * Get the quick search instance
     * @return QuickSearch
     */
    public function getQuickSearch(): QuickSearch
    {
        $quickSearch = parent::getQuickSearch();
        $quickSearch->addOptionToggle("noclosed", "__buhax_search_option_noclosed__", true);
        return $quickSearch;
    }

    /**
     * Get the quick search condition
     * @param array|null $options Option values set by the user in the interface
     * @return LazySearchCondition
     */
    public function getQuickSearchCondition(array $options = null): LazySearchCondition
    {
        $condition = parent::getQuickSearchCondition($options);
        if ($options['noclosed'] ?? false) {
            $condition->prependFixedCondition = "flagDone = 0";
        }
        return $condition;
    }
}