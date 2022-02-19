<?php

namespace Framelix\Buhax\Storable\SystemValue;

use Framelix\Buhax\Storable\Attachment;
use Framelix\Framelix\Db\StorableSchema;
use Framelix\Framelix\Storable\SystemValue;

/**
 * Invoice Creator
 * @property Attachment|null $invoiceHeader
 * @property Attachment|null $invoiceFooter
 * @property string|null $vatId
 * @property string $address
 * @property string $invoiceTextAfterPositions
 * @property string|null $accountName
 * @property string|null $iban
 * @property string|null $bic
 */
class InvoiceCreator extends SystemValue
{
    /**
     * Setup self storable schema
     * @param StorableSchema $selfStorableSchema
     */
    protected static function setupStorableSchema(StorableSchema $selfStorableSchema): void
    {
        parent::setupStorableSchema($selfStorableSchema);
        $selfStorableSchema->properties['address']->databaseType = 'text';
        $selfStorableSchema->properties['address']->length = null;
        $selfStorableSchema->properties['invoiceTextAfterPositions']->databaseType = 'text';
        $selfStorableSchema->properties['invoiceTextAfterPositions']->length = null;
    }

    /**
     * Get html string
     * @return string
     */
    public function getHtmlString(): string
    {
        return $this->address;
    }
}