<?php

namespace Framelix\Buhax\Backend;

use Framelix\Buhax\View\Depreciation;
use Framelix\Buhax\View\Fixations;
use Framelix\Buhax\View\Incomes;
use Framelix\Buhax\View\Invoices;
use Framelix\Buhax\View\Outgoings;
use Framelix\Buhax\View\Reports;

/**
 * Backend sidebar
 */
class Sidebar extends \Framelix\Framelix\Backend\Sidebar
{
    /**
     * Show the navigation content
     */
    public function showContent(): void
    {
        $this->addLink(Outgoings::class, icon: "chevron_left");
        $this->addLink(Incomes::class, icon: "chevron_right");
        $this->addLink(
            Invoices::class,
            '__buhax_view_invoice_category_1__',
            icon: "receipt",
            urlParameters: ['category' => 1]
        );
        $this->addLink(
            Invoices::class,
            '__buhax_view_invoice_category_2__',
            icon: "description",
            urlParameters: ['category' => 2]
        );
        $this->addLink(Depreciation::class, icon: "receipt_long");
        $this->addLink(Fixations::class, icon: "push_pin");
        $this->addLink(Reports::class, icon: "leaderboard");
        $this->showHtmlForLinkData();
    }
}