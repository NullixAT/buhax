<?php

namespace Framelix\Buhax\StorableMeta\SystemValue;

use Framelix\Framelix\Storable\Storable;
use Framelix\Framelix\StorableMeta;

/**
 * IncomeCategory Storable Meta
 */
class IncomeCategory extends StorableMeta\SystemValue
{
    /**
     * The storable
     * @var \Framelix\Buhax\Storable\SystemValue\IncomeCategory
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

        $this->addDefaultPropertiesAtEnd();
    }
}