<?php
// prevent loading directly in the browser without framelix context
use Framelix\Framelix\View\Backend\Config\ModuleConfig;

if (!defined("FRAMELIX_MODULE")) {
    die();
}
$form = new \Framelix\Framelix\Form\Form();

$field = new \Framelix\Framelix\Form\Field\Text();
$field->name = "moneyUnit";
$field->maxWidth = 50;
$field->maxLength = 10;
$form->addField($field);

ModuleConfig::addForm($form, '__buhax_configuration_module_general_pagetitle__');