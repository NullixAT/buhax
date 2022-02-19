<?php

namespace Framelix\Buhax;

use Framelix\Buhax\Storable\Invoice;
use Framelix\Framelix\Config;
use Framelix\Framelix\Lang;
use Framelix\Framelix\Utils\NumberUtils;
use Framelix\Pdf\Pdf;

use function count;
use function nl2br;
use function str_replace;

/**
 * InvoicePdf Creator
 *
 */
class InvoicePdf
{
    /**
     * Get pdf instance
     * @param Invoice $invoice
     * @param string|null $overlayMessage
     * @param string|null $title
     * @param string|null $textBeforePositions
     * @param string|null $textAfterPositions
     * @return Pdf
     */
    public static function getPdf(
        Invoice $invoice,
        ?string $overlayMessage = null,
        ?string $title = null,
        ?string $textBeforePositions = null,
        ?string $textAfterPositions = null
    ): Pdf {
        $pdf = new Pdf();
        $pdf->header = function () use ($pdf, $invoice, $title, $overlayMessage) {
            $pdf->tcpdf->SetY(10);
            if ($invoice->creator->invoiceHeader) {
                $pdf->tcpdf->Image(
                    "@" . $invoice->creator->invoiceHeader->getFiledata(),
                    $pdf->tcpdf->getMargins()['left'],
                    15,
                    $pdf->tcpdf->getPageWidth() - $pdf->tcpdf->getMargins()['left'] * 2
                );
            } else {
                $pdf->write(
                    nl2br(
                        $invoice->creator->address . ($invoice->creator->vatId ? Lang::get(
                                '__buhax_storable_systemvalue_invoicecreator_uid_label__'
                            ) . ": " . $invoice->creator->vatId : '')
                    )
                );
            }

            $pdf->tcpdf->SetY(50);
            $pdf->write(nl2br($invoice->receiver));

            $values = [];
            if ($invoice->receiverVatId) {
                $values['__buhax_pdf_invoice_receiver_vatid__'] = $invoice->receiverVatId;
            }
            if ($invoice->category === Invoice::CATEGORY_INVOICE) {
                $values['__buhax_storable_invoice_date_label__'] = $invoice->date->getHtmlString();
                if ($invoice->performancePeriod) {
                    $values['__buhax_storable_invoice_performanceperiod_label__'] = $invoice->performancePeriod;
                }
            } else {
                $values['__buhax_storable_invoice_date_label__'] = $invoice->date->getHtmlString();
            }
            if ($values) {
                $pdf->tcpdf->SetXY(70, 50);
                $table = '<table align="right" width="120mm"><tbody>';
                foreach ($values as $key => $value) {
                    $table .= '<tr>
                    <td width="38%"></td>
                    <td width="30%">' . Lang::get($key) . '</td>
                    <td width="32%">' . $value . '</td>
                    </tr>';
                }
                $table .= '</tbody></table>';
                $pdf->write($table);
            }
            $pdf->tcpdf->SetY(90);
            $pdf->write(
                '<h2>' . ($title ? $title . ": " : '') . Lang::get(
                    '__buhax_pdf_invoice_category_' . $invoice->category . '_title__',
                    ['nr' => $invoice->invoiceNr]
                ) . '</h2><div></div>'
            );
            if ($overlayMessage) {
                $pdf->tcpdf->StartTransform();
                $pdf->tcpdf->Rotate(30);
                for ($i = 0; $i <= 7; $i++) {
                    $pdf->tcpdf->SetXY(40, 30 + ($i * 40));
                    $pdf->write('<span style="font-size: 50px; color:#eee">' . $overlayMessage . '</span>');
                }
                $pdf->tcpdf->StopTransform();
            }
        };
        $pdf->footer = function () use ($pdf, $invoice) {
            if ($invoice->creator->invoiceFooter) {
                $pdf->tcpdf->Image(
                    "@" . $invoice->creator->invoiceFooter->getFiledata(),
                    $pdf->tcpdf->getMargins()['left'],
                    260,
                    $pdf->tcpdf->getPageWidth() - $pdf->tcpdf->getMargins()['left'] * 2
                );
            }
            $pdf->tcpdf->SetY(285);
            $pdf->write(
                '<div style="font-size: 9px; color:#888888;">#' . $invoice->invoiceNr . ' | ' . $pdf->tcpdf->PageNo(
                ) . '/' . $pdf->tcpdf->getAliasNbPages() . '</div>'
            );
        };
        $leftMargin = 20;
        $pdf->tcpdf->SetFontSize(11);
        $pdf->tcpdf->setPageOrientation("P");
        $pdf->tcpdf->SetLeftMargin($leftMargin);
        $pdf->tcpdf->SetRightMargin($leftMargin);
        $pdf->tcpdf->SetTopMargin(100);
        $pdf->tcpdf->SetAutoPageBreak(true, 40);

        $pdf->startStylesheet();
        ?>
        <style>
          th {
            font-weight: bold;
            background-color: #e5e5e5;
            border-bottom: 0.1mm solid black;
          }

          .position-cell-0 {
            border: 0.1mm solid black;
          }
          .position-cell-1 {
            border: 0.1mm solid black;
          }
          .position-cell-2 {
            text-align: right;
            border: 0.1mm solid black;
          }
          .position-cell-3 {
            text-align: right;
            border: 0.1mm solid black;
          }
          .position-footer {
            border: none;
            border-bottom: 0.1mm solid black;
          }
          .position-last-row {
            border-bottom: none;
          }
          .position-last-row {
            font-size: 110%;
            border-bottom: none;
            font-weight: bold;
          }
        </style>
        <?php
        $pdf->endStylesheet();

        $pdf->tcpdf->AddPage();

        $creator = $invoice->creator;

        if ($textBeforePositions) {
            $pdf->write(nl2br($textBeforePositions) . "<br/><br/>");
        }

        if ($invoice->textBeforePosition) {
            $pdf->write(nl2br($invoice->textBeforePosition) . "<br/><br/>");
        }

        $values = [
            [
                '__buhax_storable_invoice_positions_label_count__',
                'Position',
                '__buhax_storable_invoice_positions_label_netsingle__',
                '__buhax_storable_invoice_net_label__'
            ]
        ];
        $positions = $invoice->getPositions();
        foreach ($positions as $position) {
            $values[] = [
                $position->count,
                $position->comment,
                NumberUtils::format($position->netSingle, 2) . " " . Config::get('moneyUnit'),
                NumberUtils::format($position->netSingle * $position->count, 2) . " " . Config::get('moneyUnit')
            ];
        }
        $footerStartAt = count($values) - 1;
        $values[] = [
            '',
            '<b>' . Lang::get('__buhax_storable_invoice_net_label__') . '</b>',
            '',
            NumberUtils::format($invoice->net, 2) . " " . Config::get('moneyUnit')
        ];
        $lastRow = count($values) - 1;

        $html = '<table cellpadding="5">';
        $widths = [10, 50, 20, 20];
        foreach ($values as $key => $row) {
            $cellType = 'td';
            if (!$key) {
                $cellType = 'th';
                $html .= '<thead>';
            }
            $html .= '<tr>';
            $i = 0;
            foreach ($row as $value) {
                $class = 'position-cell-' . $i;
                if ($footerStartAt < $key) {
                    $class .= ' position-footer';
                }
                if ($key === $lastRow) {
                    $class .= ' position-last-row';
                }
                $html .= '<' . $cellType . ' class="' . $class . '" width="' . ($widths[$i]) . '%">' . Lang::get(
                        $value
                    ) . '</' . $cellType . '>';
                $i++;
            }
            $html .= ' </tr>';
            if (!$key) {
                $html .= '</thead><tbody>';
            }
        }
        $html .= '</tbody></table><br/><br/>';
        $pdf->write($html);

        if ($invoice->textAfterPosition) {
            $pdf->write(nl2br($invoice->textAfterPosition) . '<br/><br/>');
        }

        if ($textAfterPositions) {
            $pdf->write(nl2br($textAfterPositions) . "<br/><br/>");
        }

        if ($invoice->datePaid) {
            $pdf->write(Lang::get('__buhax_pdf_invoice_paid__', ['date' => $invoice->datePaid->getHtmlString()]));
        } elseif ($creator->accountName && $creator->iban && $creator->bic) {
            if ($pdf->tcpdf->GetY() + 50 > $pdf->tcpdf->getPageHeight() - $pdf->tcpdf->getMargins()['bottom']) {
                $pdf->tcpdf->AddPage();
            }
            $pdf->write(
                '<br/>' . Lang::get(
                    '__buhax_storable_systemvalue_invoicecreator_accountname_label__'
                ) . ': ' . $creator->accountName . '<br/>IBAN: ' . $creator->iban . '<br/>BIC: ' . $creator->bic . '<br/><br/>' . Lang::get(
                    '__buhax_pdf_invoice_qr__'
                ) . '.<br/>'
            );
            $code = 'BCD
001
1
SCT
' . $creator->bic . '
' . $creator->accountName . '
' . str_replace(" ", "", $creator->iban) . '
EUR' . $invoice->net . '


' . $invoice->invoiceNr;
            $pdf->tcpdf->write2DBarcode($code, 'qrcode', '', '', 30, 30);
        }

        return $pdf;
    }
}