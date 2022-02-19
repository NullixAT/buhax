<?php

namespace Framelix\Buhax\StorableMeta;

use Framelix\Framelix\Db\LazySearchCondition;
use Framelix\Framelix\Form\Field\Select;
use Framelix\Framelix\Html\QuickSearch;
use Framelix\Framelix\Storable\Storable;
use Framelix\Framelix\StorableMeta;
use Framelix\Framelix\Utils\ArrayUtils;
use Framelix\Framelix\Utils\NumberUtils;

use function is_numeric;

/**
 * Outgoing Storable Meta
 */
class Outgoing extends StorableMeta
{
    /**
     * The storable
     * @var \Framelix\Buhax\Storable\Outgoing
     */
    public Storable $storable;

    /**
     * Initialize this meta
     */
    protected function init(): void
    {
        $this->tableDefault->initialSort = ["-receiptNumber", "-date"];
        $this->tableDefault->footerSumColumns = ['net', 'netOperational'];

        $this->addDefaultPropertiesAtStart();

        $property = $this->createProperty("attachments");
        $property->addDefaultField();
        $property->valueCallable = function () {
            $arr = [];
            if ($this->storable->attachments) {
                $arr = ArrayUtils::merge($arr, $this->storable->attachments);
            }
            if ($this->storable->depreciation->attachments ?? null) {
                $arr = ArrayUtils::merge($arr, $this->storable->depreciation->attachments);
            }
            return $arr;
        };

        $property = $this->createProperty("receiptNumber");
        $property->valueCallable = function () {
            return $this->storable->getReceiptNumber();
        };

        $property = $this->createProperty("date");
        $property->addDefaultField();

        $property = $this->createProperty("comment");
        $property->addDefaultField();

        $property = $this->createProperty("outgoingCategory");
        $property->lazySearchConditionColumns->addColumn("outgoingCategory.name", "category");
        $property->setLabel('__buhax_view_backend_systemvalue_outgoingcategory__');
        $property->addDefaultField();

        $property = $this->createProperty("net");
        $property->addDefaultField();

        $property = $this->createProperty("netOperational");
        $property->valueCallable = function () {
            return NumberUtils::format($this->storable->netOperational, 2);
        };

        $property = $this->createProperty("operationalSharePercent");
        $property->addDefaultField();
        $property->setVisibility(self::CONTEXT_FORM, !!$this->storable->id);
        $property->valueCallable = function () {
            if ($this->context === self::CONTEXT_TABLE) {
                return $this->storable->operationalSharePercent . "%";
            }
            return $this->storable->operationalSharePercent;
        };

        $this->addDefaultPropertiesAtEnd();
    }

    /**
     * Get the quick search instance
     * @return QuickSearch
     */
    public function getQuickSearch(): QuickSearch
    {
        $quickSearch = parent::getQuickSearch();
        $field = new Select();
        $fixations = \Framelix\Buhax\Storable\Fixation::getByCondition(sort: '-dateFrom');
        $field->name = "fixation";
        $field->chooseOptionLabel = '__buhax_search_option_choose_fixation__';
        $field->addOption("nofixation", '__buhax_search_option_nofixation__');
        foreach ($fixations as $fixation) {
            $field->addOption(
                $fixation,
                $fixation->dateFrom->getHtmlString() . " - " . $fixation->dateTo->getHtmlString()
            );
        }
        $quickSearch->addOptionField($field);
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
        if ($options['fixation'] ?? false) {
            if ($options['fixation'] === 'nofixation') {
                $condition->prependFixedCondition = "fixation IS NULL";
            } elseif (is_numeric($options['fixation'])) {
                $condition->prependFixedCondition = "fixation = " . (int)$options['fixation'];
            }
        }
        return $condition;
    }
}