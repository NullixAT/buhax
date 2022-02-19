<?php

namespace Framelix\Buhax\View\Backend\SystemValue;

use Framelix\Framelix\View\Backend\SystemValue;

/**
 * InvoiceCreator System Value
 */
class InvoiceCreator extends SystemValue
{
    /**
     * Access role
     * @var string|bool
     */
    protected string|bool $accessRole = "admin";

    /**
     * The storable
     * @var \Framelix\Buhax\Storable\SystemValue\InvoiceCreator|null
     */
    protected ?\Framelix\Buhax\Storable\SystemValue\InvoiceCreator $storable;

    /**
     * The meta
     * @var \Framelix\Buhax\StorableMeta\SystemValue\InvoiceCreator|null
     */
    protected ?\Framelix\Buhax\StorableMeta\SystemValue\InvoiceCreator $meta;
}