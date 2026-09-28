<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\EyeCheckupBill;
use App\Models\ErpExportLog;
use App\Models\FrameBill;
use App\Models\Order;
use App\Support\ExportFormatters;
use Illuminate\Http\Request;

class ErpExportController extends Controller
{
    private const VALID_TYPES = ['orders', 'frameBills', 'eyeCheckup', 'customers'];
    private const VALID_FORMATS = ['csv', 'tally_xml'];

    public function export(Request $request)
    {
        $type = $request->query('type', 'orders');
        $format = $request->query('format', 'csv');
        $from = $request->query('from');
        $to = $request->query('to');

        if (! in_array($type, self::VALID_TYPES, true)) {
            return response()->json(['message' => 'Invalid export type'], 400);
        }
        if (! in_array($format, self::VALID_FORMATS, true)) {
            return response()->json(['message' => 'Invalid format'], 400);
        }
        $isoDate = '/^\d{4}-\d{2}-\d{2}$/';
        if (($from && ! preg_match($isoDate, $from)) || ($to && ! preg_match($isoDate, $to))) {
            return response()->json(['message' => 'Dates must be YYYY-MM-DD'], 400);
        }

        $hasRange = $from && $to;
        $filename = "{$type}_" . date('Y-m-d');

        $applyDateFilter = function ($query) use ($hasRange, $from, $to) {
            if ($hasRange) {
                $query->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);
            }

            return $query;
        };

        $rows = collect();
        if ($type === 'orders') {
            $rows = $applyDateFilter(Order::query())->with(['customer', 'items'])->orderByDesc('created_at')->get();
        } elseif ($type === 'frameBills') {
            $rows = $applyDateFilter(FrameBill::query())->with(['customer', 'items'])->orderByDesc('created_at')->get();
        } elseif ($type === 'eyeCheckup') {
            $rows = $applyDateFilter(EyeCheckupBill::query())->orderByDesc('created_at')->get();
        } elseif ($type === 'customers') {
            $rows = $applyDateFilter(Customer::query())->orderByDesc('created_at')->get();
        }

        $outExt = $format === 'tally_xml' ? 'xml' : 'csv';
        ErpExportLog::create([
            'export_type' => $type,
            'format' => $format,
            'row_count' => $rows->count(),
            'filename' => "{$filename}.{$outExt}",
            'status' => 'success',
        ]);

        $rowsArray = $rows->toArray();

        if ($format === 'tally_xml') {
            return response(ExportFormatters::toTallyXML($rowsArray), 200, [
                'Content-Type' => 'application/xml; charset=utf-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}.xml\"",
            ]);
        }

        $columns = ExportFormatters::columns()[$type] ?? [];

        return response(ExportFormatters::toCSV($rowsArray, $columns), 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ]);
    }

    public function logs(Request $request)
    {
        $logs = ErpExportLog::query()->orderByDesc('created_at')->take(50)->get();

        return response()->json(['logs' => $logs]);
    }
}
