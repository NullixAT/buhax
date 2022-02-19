<?php

namespace Framelix\Buhax\View;

use Framelix\Buhax\Storable\Invoice;
use Framelix\Buhax\Storable\InvoicePosition;
use Framelix\Framelix\Form\Form;
use Framelix\Framelix\Html\Toast;
use Framelix\Framelix\Network\Request;
use Framelix\Framelix\View\Backend\View;

/**
 * InvoicePositions
 */
class InvoicePositions extends View
{
    /**
     * Access role
     * @var string|bool
     */
    protected string|bool $accessRole = "admin,invoice-{category}";

    /**
     * The storable
     * @var Invoice
     */
    private Invoice $parent;

    /**
     * The storable
     * @var InvoicePosition
     */
    private InvoicePosition $storable;

    /**
     * The storable meta
     * @var \Framelix\Buhax\StorableMeta\InvoicePosition
     */
    private \Framelix\Buhax\StorableMeta\InvoicePosition $meta;


    /**
     * On request
     */
    public function onRequest(): void
    {
        $this->parent = Invoice::getByIdOrNew(Request::getGet('id'));
        if (!$this->parent->id) {
            $this->showInvalidUrlError('__framelix_create_or_edit_before_proceed__');
        }
        $this->storable = InvoicePosition::getByIdOrNew(Request::getGet('idPosition'));
        if (!$this->storable->id) {
            $this->storable->invoice = $this->parent;
        }
        $this->meta = new \Framelix\Buhax\StorableMeta\InvoicePosition($this->storable);
        if (Form::isFormSubmitted($this->meta->getEditFormId())) {
            $form = $this->meta->getEditForm();
            $form->validate();
            $form->setStorableValues($this->storable);
            if (!$this->storable->id) {
                $positions = $this->parent->getPositions();
                if ($positions) {
                    $lastPosition = end($positions);
                    $this->storable->sort = $lastPosition->sort + 1;
                }
            }
            $this->storable->store();
            Toast::success('__framelix_saved__');
            $this->parent->getEditUrl()->redirect();
        }
        $this->showContentBasedOnRequestType();
    }

    /**
     * Show content
     */
    public function showContent(): void
    {
        $form = $this->meta->getEditForm();
        $form->addLoadUrlButton(
            $this->parent->getPreviewUrl(),
            '__buhax_storable_invoice_preview_label__',
            buttonTooltip: '__buhax_storable_invoice_preview_changed_before__'
        );
        $form->show();

        $this->meta->getTableWithStorableSorting($this->parent->getPositions())->show();
    }
}