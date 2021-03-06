<?php

namespace Framelix\Buhax\Storable;

use Framelix\Framelix\Html\TableCell;
use Framelix\Framelix\Lang;
use Framelix\Framelix\Storable\StorableFile;
use Framelix\Framelix\Utils\HtmlUtils;

/**
 * Attachment
 */
class Attachment extends StorableFile
{
    /**
     * Folder
     * @var string|null
     */
    public ?string $folder = __DIR__ . "/../../public/uploads";

    /**
     * Is this storable deletable
     * @return bool
     */
    public function isDeletable(): bool
    {
        return true;
    }

    /**
     * Get a value that is explicitely used when displayed inside a html table
     * @return string|TableCell
     */
    public function getHtmlTableValue(): string|TableCell
    {
        $downloadUrl = $this->getDownloadUrl();
        if (!$downloadUrl) {
            return '';
        }
        return '<a href="' . $downloadUrl . '" class="buhax-attachment" title="' . Lang::get(
                '__framelix_download_file__'
            ) . ": " . $this->filename . '">'.HtmlUtils::escape($this->filename).'</a>';
    }
}