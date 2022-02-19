<?php

namespace Framelix\Buhax\View;

use Framelix\Buhax\Storable\Outgoing;
use Framelix\Framelix\Date;
use Framelix\Framelix\Form\Form;
use Framelix\Framelix\Html\Toast;
use Framelix\Framelix\Network\Request;
use Framelix\Framelix\View\Backend\View;

use function date;

/**
 * Outgoings
 */
class Outgoings extends View
{
    /**
     * Access role
     * @var string|bool
     */
    protected string|bool $accessRole = "admin,outgoing";

    /**
     * The storable
     * @var Outgoing
     */
    private Outgoing $storable;

    /**
     * The storable meta
     * @var \Framelix\Buhax\StorableMeta\Outgoing
     */
    private \Framelix\Buhax\StorableMeta\Outgoing $meta;

    /**
     * On request
     */
    public function onRequest(): void
    {
        if ($id = Request::getGet('fromDepreciation')) {
            $depriciation = \Framelix\Buhax\Storable\Depreciation::getById($id);
            if ($depriciation) {
                $netSplit = $depriciation->netSplit;
                $year = (int)date("Y");
                foreach ($netSplit as $key => $row) {
                    if ($row['year'] === $year && !isset($row['outgoing'])) {
                        $outgoing = new Outgoing();
                        $outgoing->depreciation = $depriciation;
                        $outgoing->outgoingCategory = $depriciation->outgoingCategory;
                        $outgoing->date = Date::create("$year-12-31");
                        $outgoing->net = (float)$row['value'];
                        $outgoing->store();
                        Toast::success('__buhax_storable_outgoing_created_from_depreciation__');
                        $outgoing->getEditUrl()->redirect();
                    }
                }
            }
        }
        $this->storable = Outgoing::getByIdOrNew(Request::getGet('id'));
        if (!$this->storable->id) {
            $this->storable->date = Date::create('now');
        }
        $this->meta = new \Framelix\Buhax\StorableMeta\Outgoing($this->storable);
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

        $this->meta->showSearchAndTableInTabs(Outgoing::getOpenEntries());
    }
}