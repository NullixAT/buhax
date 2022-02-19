<?php

namespace Framelix\Buhax\View;

use Framelix\Buhax\InvoicePdf;
use Framelix\Buhax\Storable\Attachment;
use Framelix\Buhax\Storable\Invoice;
use Framelix\Framelix\Date;
use Framelix\Framelix\Form\Field\Select;
use Framelix\Framelix\Form\Field\Text;
use Framelix\Framelix\Form\Field\Textarea;
use Framelix\Framelix\Form\Form;
use Framelix\Framelix\Html\Tabs;
use Framelix\Framelix\Html\Toast;
use Framelix\Framelix\Lang;
use Framelix\Framelix\Network\JsCall;
use Framelix\Framelix\Network\Request;
use Framelix\Framelix\View\Backend\View;

use function str_starts_with;
use function substr;

/**
 * Invoices
 */
class Invoices extends View
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
    private Invoice $storable;

    /**
     * The storable meta
     * @var \Framelix\Buhax\StorableMeta\Invoice
     */
    private \Framelix\Buhax\StorableMeta\Invoice $meta;

    /**
     * Category
     * @var int
     */
    private int $category;

    /**
     * On js call
     * @param JsCall $jsCall
     */
    public static function onJsCall(JsCall $jsCall): void
    {
        switch ($jsCall->action) {
            case 'invoice-pdf-download':
                $invoice = Invoice::getById(Request::getGet('invoice'));
                if (!$invoice) {
                    return;
                }
                $form = new Form();
                $form->submitUrl = \Framelix\Framelix\View::getUrl(__CLASS__)
                    ->setParameter('category', $invoice->category)
                    ->setParameter('id', $invoice);
                $form->id = "download";

                $field = new Select();
                $field->name = 'type';
                $field->chooseOptionLabel = '__buhax_storable_invoice_download_label__';
                $field->required = true;
                if (!$invoice->attachment) {
                    $field->addOption('preview', '__buhax_storable_invoice_download_preview__');
                    $field->addOption('original', '__buhax_storable_invoice_download_original__');
                } else {
                    $field->addOption('original', '__buhax_storable_invoice_download_original__');
                    if ($invoice->category === Invoice::CATEGORY_INVOICE) {
                        $field->addOption('copy', '__buhax_storable_invoice_download_copy__');
                        $field->addOption('custom', '__buhax_storable_invoice_download_custom__');
                    }
                }
                if ($invoice->invoiceCopies) {
                    foreach ($invoice->invoiceCopies as $invoiceCopy) {
                        $field->addOption(
                            'attachment-' . $invoiceCopy,
                            $invoiceCopy->filename . " (" . $invoiceCopy->createTime->getHtmlString() . ")"
                        );
                    }
                }
                $form->addField($field);

                $field = new Text();
                $field->name = 'title';
                $field->label = '__buhax_storable_invoice_title_label__';
                $field->required = true;
                $field->getVisibilityCondition()->equal('type', 'custom');
                $form->addField($field);

                $field = new Textarea();
                $field->name = 'textBeforePosition';
                $field->label = '__buhax_storable_invoice_textbeforeposition_label__';
                $field->getVisibilityCondition()->equal('type', 'custom');
                $form->addField($field);

                $field = new Textarea();
                $field->name = 'textAfterPosition';
                $field->label = '__buhax_storable_invoice_textafterposition_label__';
                $field->getVisibilityCondition()->equal('type', 'custom');
                $form->addField($field);

                $form->addSubmitButton('pdf-download', '__buhax_storable_invoice_download__', 'picture_as_pdf');
                $form->show();
                break;
        }
    }

    /**
     * On request
     */
    public function onRequest(): void
    {
        $this->category = (int)Request::getGet('category');
        if (Request::getGet('copy')) {
            $copyFrom = Invoice::getById(Request::getGet('copy'));
            if ($copyFrom->category ?? null === $this->category) {
                $clone = new Invoice();
                $clone->category = $this->category;
                $clone->date = Date::create('now');
                $clone->performancePeriod = $copyFrom->performancePeriod;
                $clone->net = $copyFrom->net;
                $clone->incomeCategory = $copyFrom->incomeCategory;
                $clone->creator = $copyFrom->creator;
                $clone->receiverVatId = $copyFrom->receiverVatId;
                $clone->receiver = $copyFrom->receiver;
                $clone->textBeforePosition = $copyFrom->textBeforePosition;
                $clone->textAfterPosition = $copyFrom->textAfterPosition;
                $clone->bankData = $copyFrom->bankData;
                $clone->store();
                $positions = $copyFrom->getPositions();
                foreach ($positions as $position) {
                    $positionClone = $position->clone();
                    $positionClone->invoice = $clone;
                    $positionClone->sort = (int)$position->sort;
                    $positionClone->store();
                }
                Toast::success('__buhax_storable_invoice_copied__');
                $clone->getEditUrl()->redirect();
            }
        }
        $this->pageTitle = '__buhax_view_invoice_category_' . $this->category . '__';
        $this->storable = Invoice::getByIdOrNew(Request::getGet('id'));
        if ($this->storable->id && $this->storable->category !== $this->category) {
            $this->storable = new Invoice();
        }
        if (!$this->storable->id) {
            $this->storable->category = $this->category;
            $this->storable->date = Date::create('now');
        } else {
            $this->pageTitle = $this->storable->getRawTextString();
        }

        if (Request::getPost('pdf-download') || Request::getGet('pdf-download')) {
            $type = Request::getPost('type') ?? Request::getGet('type');
            if (str_starts_with($type, 'attachment-')) {
                $index = substr($type, 11);
                if (isset($this->storable->invoiceCopies[$index])) {
                    $this->storable->invoiceCopies[$index]->getDownloadUrl()->redirect();
                }
                $this->showInvalidUrlError();
            }
            if ($type === 'original' && $this->storable->attachment) {
                $this->storable->attachment->getDownloadUrl()->redirect();
            }
            $params = [$this->storable, null, null, null, null];
            if ($type === 'preview') {
                $params[1] = Lang::get('__buhax_storable_invoice_preview_label__');
            }
            if ($type === 'copy') {
                $params[1] = Lang::get('__buhax_storable_invoice_copy_label__');
            }
            if ($type === 'custom') {
                $params[2] = Request::getPost('title');
                $params[3] = Request::getPost('textBeforePosition');
                $params[4] = Request::getPost('textAfterPosition');
            }
            $pdf = InvoicePdf::getPdf(...$params);
            if ($type === 'preview' || $type === 'copy') {
                $pdf->download("invoice-" . $type . "-" . $this->storable->invoiceNr . ".pdf");
            }
            $attachment = new Attachment();
            $attachment->assignedStorable = $this->storable;
            if ($type === 'original') {
                $attachment->filename = "invoice-original-" . $this->storable->invoiceNr . ".pdf";
                $attachment->store($pdf->getDataAsString());
                $this->storable->attachment = $attachment;
                $this->storable->store();
            } else {
                $attachment->filename = "invoice-" . $type
                    . "-" . $this->storable->invoiceNr . ".pdf";
                $attachment->store($pdf->getDataAsString());
                $copies = $this->storable->invoiceCopies ?: [];
                $copies[] = $attachment;
                $this->storable->invoiceCopies = $copies;
                $this->storable->store();
            }
            $attachment->getDownloadUrl()->redirect();
        }

        $this->meta = new \Framelix\Buhax\StorableMeta\Invoice($this->storable);
        $this->meta->parameters['category'] = $this->storable->category;
        if (Form::isFormSubmitted($this->meta->getEditFormId())) {
            $form = $this->meta->getEditForm();
            $form->validate();
            $form->setStorableValues($this->storable);
            $this->storable->store();

            if (Request::getPost('deleteInvoice') && $this->storable->attachment) {
                $this->storable->attachment->delete();
            }
            Toast::success('__framelix_saved__');
            $this->storable->getEditUrl()->redirect();
        }
        $this->showContentBasedOnRequestType();
    }

    /**
     * Show content
     */
    public function showContent(): void
    {
        switch ($this->tabId) {
            case 'create':
                $form = $this->meta->getEditForm();
                if ($this->storable->id) {
                    $form->addLoadUrlButton(
                        $this->storable->getPreviewUrl(),
                        '__buhax_storable_invoice_preview_label__',
                        buttonTooltip: '__buhax_storable_invoice_preview_changed_before__'
                    );
                }
                $form->show();
                $this->meta->showSearchAndTableInTabs(Invoice::getOpenEntries($this->category));
                break;
            default:
                $tabs = new Tabs();
                $tabs->addTab('create', '__buhax_view_invoices_create__', new self());
                $tabs->addTab('positions', '__buhax_storable_invoice_positions_label__', new InvoicePositions());
                $tabs->show();
        }
    }
}