<?php

namespace Framelix\Buhax\View\Backend\SystemValue;

use Framelix\Framelix\View\Backend\SystemValue;

/**
 * IncomeCategory System Value
 */
class IncomeCategory extends SystemValue
{
    /**
     * Access role
     * @var string|bool
     */
    protected string|bool $accessRole = "admin";

    /**
     * The storable
     * @var \Framelix\Buhax\Storable\SystemValue\IncomeCategory|null
     */
    protected ?\Framelix\Buhax\Storable\SystemValue\IncomeCategory $storable;

    /**
     * The meta
     * @var \Framelix\Buhax\StorableMeta\SystemValue\IncomeCategory|null
     */
    protected ?\Framelix\Buhax\StorableMeta\SystemValue\IncomeCategory $meta;
}