<?php

namespace Framelix\Buhax\StorableMeta\SystemValue;

use Framelix\Framelix\Form\Field\Select;
use Framelix\Framelix\Lang;
use Framelix\Framelix\Storable\Storable;
use Framelix\Framelix\StorableMeta;

/**
 * SummaryKey Storable Meta
 */
class SummaryKey extends StorableMeta\SystemValue
{
    /**
     * The storable
     * @var \Framelix\Buhax\Storable\SystemValue\SummaryKey
     */
    public Storable $storable;

    /**
     * Initialize this meta
     */
    protected function init(): void
    {
        $this->addDefaultPropertiesAtStart();

        $property = $this->createProperty("key");
        $property->addDefaultField();

        $property = $this->createProperty("name");
        $property->addDefaultField();

        $property = $this->createProperty("outgoingCategory");
        $property->field = new Select();
        $property->field->addOption(
            \Framelix\Buhax\Storable\SystemValue\SummaryKey::SUM_CATEGORY_PLUS,
            Lang::get(
                '__buhax_storable_systemvalue_summarykey_summary_method_' . \Framelix\Buhax\Storable\SystemValue\SummaryKey::SUM_CATEGORY_PLUS . "__"
            )
        );
        $property->field->addOption(
            \Framelix\Buhax\Storable\SystemValue\SummaryKey::SUM_CATEGORY_MINUS,
            Lang::get(
                '__buhax_storable_systemvalue_summarykey_summary_method_' . \Framelix\Buhax\Storable\SystemValue\SummaryKey::SUM_CATEGORY_MINUS . "__"
            )
        );

        $property = $this->createProperty("incomeCategory");
        $property->field = new Select();
        $property->field->addOption(
            \Framelix\Buhax\Storable\SystemValue\SummaryKey::SUM_CATEGORY_PLUS,
            Lang::get(
                '__buhax_storable_systemvalue_summarykey_summary_method_' . \Framelix\Buhax\Storable\SystemValue\SummaryKey::SUM_CATEGORY_PLUS . "__"
            )
        );
        $property->field->addOption(
            \Framelix\Buhax\Storable\SystemValue\SummaryKey::SUM_CATEGORY_MINUS,
            Lang::get(
                '__buhax_storable_systemvalue_summarykey_summary_method_' . \Framelix\Buhax\Storable\SystemValue\SummaryKey::SUM_CATEGORY_MINUS . "__"
            )
        );

        $this->addDefaultPropertiesAtEnd();
    }
}