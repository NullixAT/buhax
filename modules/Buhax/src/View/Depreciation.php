<?php

namespace Framelix\Buhax\View;

use Framelix\Buhax\Storable\Outgoing;
use Framelix\Framelix\Form\Field\Hidden;
use Framelix\Framelix\Form\Field\Number;
use Framelix\Framelix\Form\Form;
use Framelix\Framelix\Html\Tabs;
use Framelix\Framelix\Html\Toast;
use Framelix\Framelix\Network\Request;
use Framelix\Framelix\Url;
use Framelix\Framelix\View\Backend\View;

/**
 * Depreciation
 */
class Depreciation extends View
{
    /**
     * Access role
     * @var string|bool
     */
    protected string|bool $accessRole = "admin,depreciation";

    /**
     * The storable
     * @var \Framelix\Buhax\Storable\Depreciation
     */
    private \Framelix\Buhax\Storable\Depreciation $storable;

    /**
     * The storable meta
     * @var \Framelix\Buhax\StorableMeta\Depreciation
     */
    private \Framelix\Buhax\StorableMeta\Depreciation $meta;

    /**
     * On request
     */
    public function onRequest(): void
    {
        $this->storable = \Framelix\Buhax\Storable\Depreciation::getByIdOrNew(Request::getGet('id'));
        $this->meta = new \Framelix\Buhax\StorableMeta\Depreciation($this->storable);
        if (Form::isFormSubmitted('split')) {
            $form = $this->getSplitForm();
            $form->validate();
            $form->setStorableValues($this->storable);
            $this->storable->store();
            Toast::success('__framelix_saved__');
            Url::getBrowserUrl()->setParameter('id', $this->storable)->redirect();
        }
        if (Form::isFormSubmitted($this->meta->getEditFormId())) {
            $form = $this->meta->getEditForm();
            $form->validate();
            $form->setStorableValues($this->storable);
            if (
                $this->storable->years
                && $this->storable->netTotal
                && !Outgoing::getByConditionOne('depreciation = {0}', [$this->storable->id])
            ) {
                $this->storable->setNetSplit();
            }
            $form->storeWithFiles($this->storable);
            Toast::success('__framelix_saved__');
            Url::getBrowserUrl()->setParameter('id', $this->storable)->redirect();
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
                $form->show();
                $this->meta->showSearchAndTableInTabs(\Framelix\Buhax\Storable\Depreciation::getByCondition());
                break;
            case 'years':
                if (!$this->storable->id) {
                    $this->showInvalidUrlError('__framelix_create_or_edit_before_proceed__');
                    return;
                }
                $form = $this->getSplitForm();
                $form->addSubmitButton('save', '__framelix_save__', 'save');
                $form->show();
                break;
            default:
                $tabs = new Tabs();
                $tabs->addTab('create', '__buhax_view_depreciation_create__', new self());
                $tabs->addTab('years', '__buhax_view_depreciation_years__', new self());
                $tabs->show();
        }
    }

    /**
     * Get split form
     * @return Form
     */
    private function getSplitForm(): Form
    {
        $form = new Form();
        $form->id = "split";

        if ($this->storable->netSplit) {
            foreach ($this->storable->netSplit as $key => $row) {
                $field = new Hidden();
                $field->name = "netSplit[$key][year]";
                $field->defaultValue = $row['year'];
                $form->addField($field);

                $field = new Number();
                $field->name = "netSplit[$key][value]";
                $field->label = $row['year'];
                $field->minWidth = 100;
                $field->required = true;
                $field->decimals = 2;
                $field->defaultValue = $row['value'];
                $form->addField($field);
            }
        }

        return $form;
    }
}