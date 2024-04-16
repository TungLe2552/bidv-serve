<?php

namespace App\Module\Export\Writer\Custom;

use App\Helpers\TempDiskHelper;
use App\Module\Export\Writer\BaseWriter;
use File;
use Illuminate\Support\Collection;

class GeoJsonWriter extends BaseWriter
{
    public function write(Collection $datas, $headers, $fileName = 'data', $options = [])
    {
        $full_name_file = TempDiskHelper::setPrefix($fileName . '.json');
        $result = [
            "type" => "FeatureCollection",
            "features" => $datas->map(function ($item) {
                $geometry = $item['geometry'];
                unset($item['geometry']);
                $feature = [
                    "type" => "Feature",
                    "properties" => $item,
                    "geometry" =>  $geometry
                ];
                return $feature;
            })

        ];
        File::put(TempDiskHelper::getPath($full_name_file), json_encode($result));
        return $full_name_file;
    }
}
