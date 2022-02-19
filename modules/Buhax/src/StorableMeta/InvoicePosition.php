<?php

namespace Framelix\Buhax\StorableMeta;

use Framelix\Framelix\Form\Field\Number;
use Framelix\Framelix\Form\Field\Textarea;
use Framelix\Framelix\Storable\Storable;
use Framelix\Framelix\StorableMeta;

/**
 * InvoicePosition Storable Meta
 */
class InvoicePosition extends StorableMeta
{
    /**
     * The storable
     * @var \Framelix\Buhax\Storable\InvoicePosition
     */
    public Storable $storable;

    /**
     * Initialize this meta
     */
    protected function init(): void
    {
        $this->tableDefault->initialSort = ["+sort"];

        $this->addDefaultPropertiesAtStart();

        $property = $this->createProperty("count");
        $property->field = new Number();

        $property = $this->createProperty("comment");
        $property->field = new Textarea();

        $property = $this->createProperty("netSingle");
        $property->field = new Number();
        $property->field->decimals = 2;

        $this->addDefaultPropertiesAtEnd();
    }
}