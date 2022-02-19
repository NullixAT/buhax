<?php

namespace Framelix\Buhax\StorableMeta;

use Framelix\Buhax\View\Incomes;
use Framelix\Framelix\Db\LazySearchCondition;
use Framelix\Framelix\Form\Field\Select;
use Framelix\Framelix\Html\QuickSearch;
use Framelix\Framelix\Html\Table;
use Framelix\Framelix\Html\TableCell;
use Framelix\Framelix\Storable\Storable;
use Framelix\Framelix\StorableMeta;
use Framelix\Framelix\View;

use function is_numeric;

/**
 * Income Storable Meta
 */
class Income extends StorableMeta
{
    /**
     * The storable
     * @var \Framelix\Buhax\Storable\Income
     */
    public Storable $storable;

    /**
     * Initialize this meta
     */
    protected function init(): void
    {
        $this->tableDefault->initialSort = ["-receiptNumber", "-date"];
        $this->tableDefault->footerSumColumns = ['net'];

        $this->tableDefault->addColumnFlag('copyIncome', Table::COLUMNFLAG_REMOVE_IF_EMPTY);
        $property = $this->createProperty("copyIncome");
        $property->setVisibility(null, false);
        $property->setVisibility(self::CONTEXT_TABLE, true);
        $property->setLabel('');
        $property->valueCallable = function () {
            $tableCell = new TableCell();
            $tableCell->icon = "content_copy";
            $tableCell->iconTooltip = "__buhax_storable_income_copy__";
            $tableCell->iconColor = "primary";
            $tableCell->iconUrl = View::getUrl(Incomes::class)->setParameter('copy', $this->storable);
            $tableCell->iconUrlBlank = false;
            return $tableCell;
        };

        $this->tableDefault->addColumnFlag('downloadInvoice', Table::COLUMNFLAG_REMOVE_IF_EMPTY);
        $property = $this->createProperty("downloadInvoice");
        $property->setVisibility(null, false);
        $property->setVisibility(self::CONTEXT_TABLE, true);
        $property->setLabel('');
        $property->valueCallable = function () {
            if ($this->storable->invoice) {
                $tableCell = new TableCell();
                $tableCell->icon = "receipt";
                $tableCell->iconTooltip = "__buhax_storable_income_download_invoice__";
                $tableCell->iconColor = "primary";
                $tableCell->iconUrl = $this->storable->invoice->attachment?->getDownloadUrl();
                $tableCell->iconUrlBlank = false;
                return $tableCell;
            }
            return null;
        };

        $this->addDefaultPropertiesAtStart();

        $this->tableDefault->addColumnFlag('attachments', Table::COLUMNFLAG_REMOVE_IF_EMPTY);
        $property = $this->createProperty("attachments");
        $property->addDefaultField();

        $this->tableDefault->addColumnFlag('receiptNumber', Table::COLUMNFLAG_SMALLWIDTH);
        $property = $this->createProperty("receiptNumber");
        $property->valueCallable = function () {
            return $this->storable->getReceiptNumber();
        };

        $property = $this->createProperty("date");
        $property->addDefaultField();

        $property = $this->createProperty("comment");
        $property->addDefaultField();

        $property = $this->createProperty("incomeCategory");
        $property->lazySearchConditionColumns->addColumn("incomeCategory.name", "category");
        $property->setLabel('__buhax_storable_income_incomecategory_label__');
        $property->addDefaultField();

        $property = $this->createProperty("net");
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