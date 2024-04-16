<?php

namespace App\Module\Export\Writer\Custom;

use App\Helpers\TempDiskHelper;
use App\Module\Export\Writer\BaseWriter;
use Carbon\Carbon;
use File;
use Storage;

class JsonWriter extends BaseWriter
{
    public function write($datas, $headers, $fileName = 'data', $options = [])
    {
        $full_name_file = TempDiskHelper::setPrefix($fileName . '.json');
        File::put(TempDiskHelper::getPath($full_name_file), json_encode($datas));
        return $full_name_file;
    }
}
