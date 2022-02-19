<?php

namespace Framelix\Buhax\View;

use Framelix\Buhax\Storable\Income;
use Framelix\Buhax\Storable\Invoice;
use Framelix\Framelix\Date;
use Framelix\Framelix\Form\Form;
use Framelix\Framelix\Html\Toast;
use Framelix\Framelix\Network\Request;
use Framelix\Framelix\View\Backend\View;

/**
 * Incomes
 */
class Incomes extends View
{
    /**
     * Access role
     * @var string|bool
     */
    protected string|bool $accessRole = "admin,income";

    /**
     * The storable
     * @var Income
     */
    private Income $storable;

    /**
     * The storable meta
     * @var \Framelix\Buhax\StorableMeta\Income
     */
    private \Framelix\Buhax\StorableMeta\Income $meta;

    /**
     * On request
     */
    public function onRequest(): void
    {
        if ($id = Request::getGet('fromInvoice')) {
            $invoice = Invoice::getById($id);
            if ($invoice) {
                $positions = $invoice->getPositions();
                $position = reset($positions);
                $income = new Income();
                $income->invoice = $invoice;
                $income->incomeCategory = $invoice->incomeCategory;
                $income->date = $invoice->datePaid;
                $income->net = $invoice->net;
                $income->comment = (string)$invoice->invoiceNr;
                if ($position) {
                    $income->comment .= ", " . $position->comment;
                }
                $income->store();
                $invoice->income = $income;
                $invoice->store();
                Toast::success('__buhax_storable_invoice_income_created__');
                $income->getEditUrl()->redirect();
            }
        }
        $this->storable = Income::getByIdOrNew(Request::getGet('id'));
        if (!$this->storable->id) {
            $this->storable->date = Date::create('now');
            if (Request::getGet('copy')) {
                $copyFrom = Income::getById(Request::getGet('copy'));
                if ($copyFrom) {
                    $this->storable->incomeCategory = $copyFrom->incomeCategory;
                    $this->storable->net = $copyFrom->net;
                }
            }
        }
        $this->meta = new \Framelix\Buhax\StorableMeta\Income($this->storable);
        if (Form::isFormSubmitted($this->meta->getEditFormId())) {
            $form = $this->meta->getEditForm();
            $form->validate();
            $form->setStorableValues($this->storable);
            $form->storeWithFiles($this->storable);
            Toast::success('__framelix_saved__');
            $this->getSelfUrl()->redirect();
        }
        $this->showContentBasedOnRequestType();
    }

    /**
     * Show content
     */
    public function showContent(): void
    {
        $form = $this->meta->getEditForm();
        $form->show();

        $this->meta->showSearchAndTableInTabs(Income::getOpenEntries());
    }
}