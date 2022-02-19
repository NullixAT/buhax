<?php

namespace Framelix\Buhax\View;

use Framelix\Buhax\Storable\Attachment;
use Framelix\Buhax\Storable\Fixation;
use Framelix\Buhax\Storable\Income;
use Framelix\Buhax\Storable\Invoice;
use Framelix\Buhax\Storable\Outgoing;
use Framelix\Framelix\Date;
use Framelix\Framelix\Db\Mysql;
use Framelix\Framelix\Form\Form;
use Framelix\Framelix\Html\Tabs;
use Framelix\Framelix\Html\Toast;
use Framelix\Framelix\Lang;
use Framelix\Framelix\Network\Request;
use Framelix\Framelix\Url;
use Framelix\Framelix\View\Backend\View;

use function file_get_contents;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

/**
 * Fixations
 */
class Fixations extends View
{
    /**
     * Access role
     * @var string|bool
     */
    protected string|bool $accessRole = "admin,fixation";

    /**
     * The storable
     * @var Fixation
     */
    private Fixation $storable;

    /**
     * The storable meta
     * @var \Framelix\Buhax\StorableMeta\Fixation
     */
    private \Framelix\Buhax\StorableMeta\Fixation $meta;

    /**
     * On request
     */
    public function onRequest(): void
    {
        $this->storable = Fixation::getByIdOrNew(Request::getGet('id'));
        $this->meta = new \Framelix\Buhax\StorableMeta\Fixation($this->storable);
        if (Form::isFormSubmitted($this->meta->getEditFormId())) {
            $fixation = new Fixation();
            $fixation->dateFrom = Date::create(Request::getPost('dateFrom'));
            $fixation->dateTo = Date::create(Request::getPost('dateTo'));

            // just testing generation, if some error happens, it will stop here and throw an exception
            Reports::getReportSheet($fixation->dateFrom, $fixation->dateTo);

            $fixation->store();

            $outgoings = Outgoing::getByCondition(
                'fixation IS NULL && date BETWEEN {0} AND {1}',
                [$fixation->dateFrom, $fixation->dateTo],
                ['+date', '+id']
            );
            Mysql::get()->update(Outgoing::class, ['nr' => null], 'fixation IS NULL');
            $lastOutgoing = Outgoing::getByConditionOne('fixation IS NOT NULL', null, ['-nr']);
            $lastNr = $lastOutgoing->nr ?? 0;
            foreach ($outgoings as $outgoing) {
                $lastNr++;
                $outgoing->nr = $lastNr;
                $outgoing->fixation = $fixation;
                $outgoing->preserveUpdateUserAndTime();
                $outgoing->store();
            }

            $incomes = Income::getByCondition(
                'fixation IS NULL && date BETWEEN {0} AND {1}',
                [$fixation->dateFrom, $fixation->dateTo],
                ['+date', '+id']
            );
            Mysql::get()->update(Income::class, ['nr' => null], 'fixation IS NULL');
            $lastIncome = Income::getByConditionOne('fixation IS NOT NULL', null, ['-nr']);
            $lastNr = $lastIncome->nr ?? 0;
            foreach ($incomes as $income) {
                $lastNr++;
                $income->nr = $lastNr;
                $income->fixation = $fixation;
                $income->preserveUpdateUserAndTime();
                $income->store();
            }

            $invoices = Invoice::getByCondition(
                'fixation IS NULL && date BETWEEN {0} AND {1}',
                [$fixation->dateFrom, $fixation->dateTo]
            );
            foreach ($invoices as $invoice) {
                $invoice->fixation = $fixation;
                $invoice->preserveUpdateUserAndTime();
                $invoice->store();
            }

            $reportSheet = Reports::getReportSheet($fixation->dateFrom, $fixation->dateTo);
            $tmpFile = tempnam(sys_get_temp_dir(), 'buhax-fixation') . ".xlsx";
            $reportSheet->save($tmpFile);

            $attachment = new Attachment();
            $attachment->assignedStorable = $fixation;
            $attachment->filename = "fixation-" . $fixation->dateFrom->getRawTextString(
                ) . "-" . $fixation->dateTo->getRawTextString() . ".xlsx";
            $attachment->store(file_get_contents($tmpFile));
            unlink($tmpFile);

            $fixation->attachment = $attachment;
            $fixation->store();

            Toast::success('__buhax_view_fixations_created__');
            Url::getBrowserUrl()->redirect();
        }
        $this->showContentBasedOnRequestType();
    }

    /**
     * Show content
     */
    public function showContent(): void
    {
        switch ($this->tabId) {
            case 'list':
                $this->meta->getTable(Fixation::getByCondition())->show();
                break;
            case 'create':
                $this->meta->getEditForm()->show();
                break;
            default:
                ?>
                <p><?= Lang::get('__buhax_view_fixations_desc__') ?></p>
                <?php
                $tabs = new Tabs();
                $tabs->addTab('list', '__buhax_view_fixations_list__', new self());
                $tabs->addTab('create', '__buhax_view_fixations_create__', new self());
                $tabs->show();
        }
    }
}