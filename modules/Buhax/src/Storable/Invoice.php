<?php

namespace Framelix\Buhax\Storable;

use Framelix\Buhax\Storable\SystemValue\IncomeCategory;
use Framelix\Buhax\Storable\SystemValue\InvoiceCreator;
use Framelix\Buhax\View\Invoices;
use Framelix\Framelix\Date;
use Framelix\Framelix\Db\StorableSchema;
use Framelix\Framelix\Storable\StorableExtended;
use Framelix\Framelix\Url;
use Framelix\Framelix\Utils\NumberUtils;
use Framelix\Framelix\View;

use function sprintf;

/**
 * Invoice
 * @property Attachment|null $attachment
 * @property Attachment[]|null $invoiceCopies
 * @property Fixation|null $fixation
 * @property Income|null $income
 * @property int $category
 * @property int|null $invoiceNr
 * @property Date $date
 * @property Date|null $datePaid
 * @property string|null $performancePeriod
 * @property float $net
 * @property IncomeCategory|null $incomeCategory
 * @property InvoiceCreator $creator
 * @property string|null $receiverVatId
 * @property string $receiver
 * @property string|null $textBeforePosition
 * @property string|null $textAfterPosition
 * @property mixed|null $bankData
 */
class Invoice extends StorableExtended
{
    public const CATEGORY_INVOICE = 1;
    public const CATEGORY_OFFER = 2;

    /**
     * Setup self storable schema
     * @param StorableSchema $selfStorableSchema
     */
    protected static function setupStorableSchema(StorableSchema $selfStorableSchema): void
    {
        parent::setupStorableSchema($selfStorableSchema);
        $moneyProps = ['net'];
        $selfStorableSchema->properties["category"]->length = 2;
        foreach ($moneyProps as $moneyProp) {
            $selfStorableSchema->properties[$moneyProp]->length = 14;
            $selfStorableSchema->properties[$moneyProp]->decimals = 2;
        }
        $textProps = ['textBeforePosition', 'textAfterPosition'];
        foreach ($textProps as $textProp) {
            $selfStorableSchema->properties[$textProp]->databaseType = 'text';
            $selfStorableSchema->properties[$textProp]->length = null;
        }
        $selfStorableSchema->addIndex("date", "index");
        $selfStorableSchema->addIndex("invoiceNr", "unique");
    }

    /**
     * Get preview url
     * @return Url|null
     */
    public function getPreviewUrl(): ?Url
    {
        return $this->getEditUrl()->setParameter('pdf-download', 1)->setParameter('type', 'preview');
    }

    /**
     * Get edit url
     * @return Url|null
     */
    public function getEditUrl(): ?Url
    {
        return View::getUrl(Invoices::class)->setParameter('category', $this->category)->setParameter('id', $this);
    }

    /**
     * Get open entries (not yet fixed)
     * @param int $category
     * @return self[]
     */
    public static function getOpenEntries(int $category): array
    {
        return self::getByCondition('fixation IS NULL && category = {0}', [$category], '+invoiceNr');
    }

    /**
     * Get positions
     * @return InvoicePosition[]
     */
    public function getPositions(): array
    {
        return InvoicePosition::getByCondition('invoice = {0}', [(int)$this->id], ['+sort']);
    }

    /**
     * Is this storable editable
     * @return bool
     */
    public function isEditable(): bool
    {
        return !$this->income && !$this->fixation;
    }

    /**
     * Is this storable deletable
     * @return bool
     */
    public function isDeletable(): bool
    {
        return !$this->income && !$this->fixation;
    }

    /**
     * Get a human-readable raw text representation of this instace
     * @return string
     */
    public function getRawTextString(): string
    {
        return $this->date->getHtmlString() . " | " . NumberUtils::format($this->net, 2);
    }

    /**
     * Get a human-readable html representation of this instace
     * @return string
     */
    public function getHtmlString(): string
    {
        return $this->date->getHtmlString() . " | " . NumberUtils::format($this->net, 2);
    }

    /**
     * Store
     */
    public function store(): void
    {
        if (!$this->id) {
            $this->net = 0.0;
            $nr = 1;
            while (true) {
                $invoiceNr = (int)($this->date->dateTime->format("ymd") . sprintf("%02d", $nr));
                if (!self::getByConditionOne('invoiceNr = {0}', [$invoiceNr])) {
                    $this->invoiceNr = $invoiceNr;
                    break;
                }
                $nr++;
            }
        }
        parent::store();
    }

}