<?php

namespace App\Support;

/**
 * CSV / Tally XML export helpers — ported from lib/exportFormatters.js
 */
class ExportFormatters
{
    public static function toCSV(array $rows, array $columns): string
    {
        $header = implode(',', array_map(fn ($c) => '"' . $c['label'] . '"', $columns));
        $lines = [];
        foreach ($rows as $row) {
            $cells = [];
            foreach ($columns as $c) {
                $val = isset($c['fn']) ? ($c['fn'])($row) : (data_get($row, $c['key']) ?? '');
                $str = str_replace('"', '""', (string) $val);
                $cells[] = '"' . $str . '"';
            }
            $lines[] = implode(',', $cells);
        }
        return implode("\n", array_merge([$header], $lines));
    }

    public static function toTallyXML(array $orders): string
    {
        $entries = [];
        foreach ($orders as $o) {
            $date = str_replace('/', '', date('d/m/Y', strtotime(data_get($o, 'order_date') ?? data_get($o, 'created_at'))));
            $narration = data_get($o, 'order_number') ?? data_get($o, 'bill_number') ?? data_get($o, 'id');
            $amount = data_get($o, 'total');
            $party = data_get($o, 'customer.name') ?? data_get($o, 'customer_name') ?? '';
            $partyLine = $party !== '' ? "<PARTYLEDGERNAME>{$party}</PARTYLEDGERNAME>" : '';
            $entries[] = "
  <VOUCHER VCHTYPE=\"Sales\" ACTION=\"Create\">
    <DATE>{$date}</DATE>
    <NARRATION>{$narration}</NARRATION>
    <AMOUNT>{$amount}</AMOUNT>
    {$partyLine}
  </VOUCHER>";
        }
        $body = implode("\n", $entries);

        return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<ENVELOPE>
  <HEADER>
    <TALLYREQUEST>Import Data</TALLYREQUEST>
  </HEADER>
  <BODY>
    <IMPORTDATA>
      <REQUESTDESC>
        <REPORTNAME>Vouchers</REPORTNAME>
      </REQUESTDESC>
      <REQUESTDATA>
        <TALLYMESSAGE xmlns:UDF=\"TallyUDF\">
          {$body}
        </TALLYMESSAGE>
      </REQUESTDATA>
    </IMPORTDATA>
  </BODY>
</ENVELOPE>";
    }

    public static function columns(): array
    {
        $date = fn ($key) => fn ($r) => date('d/m/Y', strtotime(data_get($r, $key) ?? data_get($r, 'created_at')));

        return [
            'orders' => [
                ['label' => 'Order #', 'key' => 'order_number'],
                ['label' => 'Date', 'fn' => $date('order_date')],
                ['label' => 'Customer', 'fn' => fn ($r) => data_get($r, 'customer.name') ?? ''],
                ['label' => 'Phone', 'fn' => fn ($r) => data_get($r, 'customer.phone') ?? ''],
                ['label' => 'Items', 'fn' => fn ($r) => count(data_get($r, 'items') ?? [])],
                ['label' => 'Subtotal', 'key' => 'subtotal'],
                ['label' => 'Tax', 'key' => 'tax'],
                ['label' => 'Total', 'key' => 'total'],
                ['label' => 'Notes', 'key' => 'notes'],
            ],
            'frameBills' => [
                ['label' => 'Bill #', 'key' => 'bill_number'],
                ['label' => 'Date', 'fn' => $date('bill_date')],
                ['label' => 'Customer', 'fn' => fn ($r) => data_get($r, 'customer.name') ?? data_get($r, 'customer_name') ?? ''],
                ['label' => 'Contact', 'fn' => fn ($r) => data_get($r, 'customer.phone') ?? data_get($r, 'customer_contact') ?? ''],
                ['label' => 'Subtotal', 'key' => 'subtotal'],
                ['label' => 'GST Rate', 'key' => 'gst_rate'],
                ['label' => 'Tax', 'key' => 'tax'],
                ['label' => 'Discount', 'key' => 'discount_amount'],
                ['label' => 'Total', 'key' => 'total'],
            ],
            'eyeCheckup' => [
                ['label' => 'Bill #', 'key' => 'bill_number'],
                ['label' => 'Date', 'fn' => $date('bill_date')],
                ['label' => 'Patient', 'fn' => fn ($r) => data_get($r, 'customer_name') ?? ''],
                ['label' => 'Contact', 'fn' => fn ($r) => data_get($r, 'customer_contact') ?? ''],
                ['label' => 'Left Eye', 'key' => 'left_eye'],
                ['label' => 'Right Eye', 'key' => 'right_eye'],
                ['label' => 'Addition', 'key' => 'addition'],
                ['label' => 'Frame', 'key' => 'frame_amount'],
                ['label' => 'Glass', 'key' => 'glass_amount'],
                ['label' => 'Advance', 'key' => 'advance_amount'],
                ['label' => 'Total', 'key' => 'total'],
                ['label' => 'Balance Due', 'key' => 'balance_due'],
            ],
            'customers' => [
                ['label' => 'Name', 'key' => 'name'],
                ['label' => 'Email', 'key' => 'email'],
                ['label' => 'Phone', 'key' => 'phone'],
                ['label' => 'Address', 'key' => 'address'],
                ['label' => 'Created', 'fn' => fn ($r) => date('d/m/Y', strtotime(data_get($r, 'created_at')))],
            ],
        ];
    }
}
