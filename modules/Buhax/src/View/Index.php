<?php

namespace Framelix\Buhax\View;

use Framelix\Framelix\View\Backend\View;

/**
 * Index
 */
class Index extends View
{
    /**
     * Access role
     * @var string|bool
     */
    protected string|bool $accessRole = true;

    /**
     * On request
     */
    public function onRequest(): void
    {
        \Framelix\Framelix\View::getUrl(Outgoings::class)->redirect();
    }

    /**
     * Show the page content
     */
    public function showContent(): void
    {
    }
}