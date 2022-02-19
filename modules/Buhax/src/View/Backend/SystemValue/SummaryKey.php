<?php

namespace Framelix\Buhax\View\Backend\SystemValue;

use Framelix\Framelix\View\Backend\SystemValue;

/**
 * SummaryKey System Value
 */
class SummaryKey extends SystemValue
{
    /**
     * Access role
     * @var string|bool
     */
    protected string|bool $accessRole = "admin";

    /**
     * The storable
     * @var \Framelix\Buhax\Storable\SystemValue\SummaryKey|null
     */
    protected ?\Framelix\Buhax\Storable\SystemValue\SummaryKey $storable;

    /**
     * The meta
     * @var \Framelix\Buhax\StorableMeta\SystemValue\SummaryKey|null
     */
    protected ?\Framelix\Buhax\StorableMeta\SystemValue\SummaryKey $meta;
}