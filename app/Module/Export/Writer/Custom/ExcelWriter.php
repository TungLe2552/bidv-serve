<?php

namespace App\Module\Export\Writer\Custom;

use App\Exports\DynamicTableExport;
use App\Exports\SimpleExport;
use App\Helpers\TempDiskHelper;
use App\Module\Export\Writer\BaseWriter;
use Excel;

use function Symfony\Component\String\b;

class ExcelWriter extends BaseWriter
{
    public function write($data, $headers, $name = 'data',  $options = [])
    {
        if (count($data) < 1) {
            abort(500, 'Không có dữ liệu');
        }
        $title = $options['title'] ?? $name;
        $sheet_name = $options['sheet_name'] ?? $name;
        $is_simple = $options['is_simple'] ?? false;
        $file_name = $name;
        $full_name_file = TempDiskHelper::setPrefix($file_name . '.xlsx');
        if ($is_simple) {
            Excel::store(new SimpleExport(
                $sheet_name,
                $data,
                $headers,
                function ($data) use ($headers, $options) {
                    if (isset($options['is_origin']) && $options['is_origin']) {
                        return $data;
                    }
                    $item = [];
                    foreach ($headers as $key => $header) {
                        $key_value = $header['value'] ?? $header;
                        $value = \Arr::get($data, $key_value, '');
                        if (is_string($value)) {
                            $value = strip_tags(\Arr::get($data, $key_value, ''));
                        }
                        $item[] = $value;
                    }

                    return $item;
                },
            ), $full_name_file, 'temp');
        } else {

            Excel::store(new DynamicTableExport(
                $sheet_name,
                $data,
                $headers,
                function ($data) use ($headers, $options) {
                    if (isset($options['is_origin']) && $options['is_origin']) {
                        return $data;
                    }
                    $item = [];
                    foreach ($headers as $key => $header) {
                        $key_value = $header['value'] ?? $header;
                        $value = \Arr::get($data, $key_value, '');
                        if (is_string($value)) {
                            $value = strip_tags(\Arr::get($data, $key_value, ''));
                        }
                        $item[] = $value;
                    }

                    return $item;
                },
                $title
            ), $full_name_file, 'temp');
        }
        return $full_name_file;
    }
}
