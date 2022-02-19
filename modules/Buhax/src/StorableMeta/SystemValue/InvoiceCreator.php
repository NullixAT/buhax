<?php

namespace Framelix\Buhax\StorableMeta\SystemValue;

use Framelix\Framelix\Form\Field\Bic;
use Framelix\Framelix\Form\Field\File;
use Framelix\Framelix\Form\Field\Iban;
use Framelix\Framelix\Form\Field\Textarea;
use Framelix\Framelix\Storable\Storable;
use Framelix\Framelix\StorableMeta;

/**
 * InvoiceCreator Storable Meta
 */
class InvoiceCreator extends StorableMeta\SystemValue
{
    /**
     * The storable
     * @var \Framelix\Buhax\Storable\SystemValue\InvoiceCreator
     */
    public Storable $storable;

    /**
     * Initialize this meta
     */
    protected function init(): void
    {
        $this->addDefaultPropertiesAtStart();

        $property = $this->createProperty("invoiceHeader");
        $property->field = new File();
        $property->field->allowedFileTypes = ".jpg, .png";

        $property = $this->createProperty("invoiceFooter");
        $property->addDefaultField();

        $property = $this->createProperty("vatId");
        $property->addDefaultField();

        $property = $this->createProperty("address");
        $property->field = new Textarea();

        $property = $this->createProperty("invoiceTextAfterPositions");
        $property->field = new Textarea();
        $property->setVisibility(self::CONTEXT_TABLE, false);

        $property = $this->createProperty("accountName");
        $property->addDefaultField();
        $property->setVisibility(self::CONTEXT_TABLE, false);

        $property = $this->createProperty("iban");
        $property->field = new Iban();
        $property->setVisibility(self::CONTEXT_TABLE, false);

        $property = $this->createProperty("bic");
        $property->field = new Bic();
        $property->setVisibility(self::CONTEXT_TABLE, false);

        $this->addDefaultPropertiesAtEnd();
    }
}