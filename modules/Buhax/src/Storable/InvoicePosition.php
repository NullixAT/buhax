<?php

namespace Framelix\Buhax\Storable;

use Framelix\Framelix\Db\StorableSchema;
use Framelix\Framelix\Storable\StorableExtended;
use Framelix\Framelix\Url;

/**
 * Invoice Position
 * @property Invoice $invoice
 * @property int $count
 * @property float $netSingle
 * @property string|null $comment
 * @property int $sort
 */
class InvoicePosition extends StorableExtended
{
    /**
     * Setup self storable schema
     * @param StorableSchema $selfStorableSchema
     */
    protected static function setupStorableSchema(StorableSchema $selfStorableSchema): void
    {
        parent::setupStorableSchema($selfStorableSchema);
        $moneyProps = ['netSingle'];
        foreach ($moneyProps as $moneyProp) {
            $selfStorableSchema->properties[$moneyProp]->length = 14;
            $selfStorableSchema->properties[$moneyProp]->decimals = 2;
            $selfStorableSchema->properties['comment']->databaseType = 'text';
            $selfStorableSchema->properties['comment']->length = null;
        }
    }

    /**
     * Is this storable deletable
     * @return bool
     */
    public function isDeletable(): bool
    {
        return $this->invoice->isDeletable();
    }

    /**
     * This function is called when the database has been updated after a store() or delete() call
     */
    protected function onDatabaseUpdated(): void
    {
        $this->updateInvoiceNet();
    }

    /**
     * Update invoice net total
     */
    private function updateInvoiceNet(): void
    {
        // update invoice net total
        $this->invoice->net = (float)$this->getDb()->fetchOne(
            "
            SELECT ROUND(SUM(count * netSingle), 2) 
            FROM `" . __CLASS__ . "`
            WHERE invoice = $this->invoice            
        "
        );
        $this->invoice->store();
    }

    /**
     * Get edit url
     * @return Url|null
     */
    public function getEditUrl(): ?Url
    {
        return $this->invoice->getEditUrl()->setParameter('idPosition', $this);
    }


}