<?php

namespace Framelix\Buhax\View\SystemValue;

use Framelix\Framelix\View\Backend\SystemValue;

/**
 * OutgoingCategory System Value
 */
class OutgoingCategory extends SystemValue
{
    /**
     * Access role
     * @var string|bool
     */
    protected string|bool $accessRole = "admin";

    /**
     * The storable
     * @var \Framelix\Buhax\Storable\SystemValue\OutgoingCategory|null
     */
    protected ?\Framelix\Buhax\Storable\SystemValue\OutgoingCategory $storable;

    /**
     * The meta
     * @var \Framelix\Buhax\StorableMeta\SystemValue\OutgoingCategory|null
     */
    protected ?\Framelix\Buhax\StorableMeta\SystemValue\OutgoingCategory $meta;
}