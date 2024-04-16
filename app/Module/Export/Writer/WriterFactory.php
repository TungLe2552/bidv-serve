<?php

namespace App\Module\Export\Writer;

use App\Module\Export\Writer\Custom\CsvWriter;
use App\Module\Export\Writer\Custom\ExcelWriter;
use App\Module\Export\Writer\Custom\GeoJsonWriter;
use App\Module\Export\Writer\Custom\JsonWriter;
use App\Module\Export\Writer\Custom\ShapefileOgr2ogr;
use App\Module\Export\Writer\Custom\ShapefileWriter;
use ErrorException;

class WriterFactory
{
    public static function getByType($ext)
    {
        switch ($ext) {
            case 'excel':
                return new ExcelWriter();
            case 'csv':
                return new CsvWriter();
            case 'json':
                return new JsonWriter();
            case 'geojson':
                return new GeoJsonWriter();
            case 'shapefile':
                return new ShapefileWriter();
            case 'shapefileOgr2Ogr':
                return new ShapefileOgr2ogr();
            default:
                throw new ErrorException('Not support: ' . $ext);
        }
    }
}
