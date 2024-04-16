<?php

namespace App\Module\Export\Writer\Custom;

use App\Exports\DynamicTableExport;
use App\Module\Export\Writer\BaseWriter;
use Carbon\Carbon;
use Excel;

class CsvWriter extends BaseWriter
{
    public function write($datas, $headers, $fileName = 'data', $options = [])
    {
        $full_name_file = $fileName . '-' . Carbon::now()->format('YmdHs') . '.csv';
        Excel::store(new DynamicTableExport(
            $fileName,
            $datas,
            $headers,
            function ($data) use ($headers) {
                $item = [];
                foreach ($headers as $key => $header) {
                    $header = $header['value'] ?? $header;
                    $item[] = \Arr::get($data, $header, '');
                }
                return $item;
            },
            []
        ), $full_name_file, 'temp', \Maatwebsite\Excel\Excel::CSV);
        return $full_name_file;
    }
}
