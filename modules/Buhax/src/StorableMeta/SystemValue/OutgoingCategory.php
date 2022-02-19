<?php

namespace Framelix\Buhax\StorableMeta\SystemValue;

use Framelix\Framelix\Form\Field\Number;
use Framelix\Framelix\Storable\Storable;
use Framelix\Framelix\StorableMeta;

/**
 * OutgoingCategory Storable Meta
 */
class OutgoingCategory extends StorableMeta\SystemValue
{
    /**
     * The storable
     * @var \Framelix\Buhax\Storable\SystemValue\OutgoingCategory
     */
    public Storable $storable;

    /**
     * Initialize this meta
     */
    protected function init(): void
    {
        $this->addDefaultPropertiesAtStart();

        $property = $this->createProperty("summaryKeys");
        $property->addDefaultField();

        $property = $this->createProperty("name");
        $property->addDefaultField();

        $property = $this->createProperty("operationalSharePercent");
        $property->addDefaultField();
        /** @var Number $field */
        $field = $property->field;
        $field->min = 0;
        $field->max = 100;

        $this->addDefaultPropertiesAtEnd();
    }
}