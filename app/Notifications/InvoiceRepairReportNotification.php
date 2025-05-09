<?php

namespace App\Notifications;

use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceRepairReportNotification extends Notification
{
    use Queueable;

    protected $reportData;

    public function __construct($reportData)
    {
        $this->reportData = $reportData;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $pdfFilePath = $this->generatePdfAndSave($this->reportData);

        return (new MailMessage)
                    ->subject('Invoice Repair Report')
                    ->line('Find the attached invoice repair report.')
                    ->attach($pdfFilePath, [
                        'as' => 'InvoiceRepairReport.pdf',
                        'mime' => 'application/pdf',
                    ]);
    }

    protected function generatePdfAndSave($reportData)
    {
        $pdfContent = $this->formatDataForPdf($reportData);

        $pdf = PDF::loadHTML($pdfContent);
        $filePath = storage_path('app/public/InvoiceRepairReport.pdf');
        $pdf->save($filePath);

        return $filePath;
    }

    protected function formatDataForPdf($reportData)
    {
        $htmlContent = '
        <html>
        <head>
            <style>
                body {
                    font-family: "Helvetica", sans-serif;
                    font-size: 10pt;
                    color: #333;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                }
                th, td {
                    border: 1px solid #ddd;
                    padding: 8px;
                    text-align: left;
                }
                th {
                    background-color: #f2f2f2;
                }
            </style>
        </head>
        <body>
        <h2>Invoice Repair Report</h2>
        <table>
            <thead>
                <tr>
                    <th>Transaction ID</th>
                    <th>Transaction Date</th>
                    <th>Old Invoice Number</th>
                    <th>New Invoice Number</th>
                    <th>Specified Date</th>
                </tr>
            </thead>
            <tbody>';

        foreach ($reportData as $item) {
            $htmlContent .= "
            <tr>
                <td>{$item['transaction_id']}</td>
                <td>{$item['transaction_date']}</td>
                <td>{$item['old_invoice_no']}</td>
                <td>{$item['new_invoice_no']}</td>
                <td>{$item['specified_date']}</td>
            </tr>";
        }

        $htmlContent .= '
            </tbody>
        </table>
        </body>
        </html>';

        return $htmlContent;
    }
}
