<?php

namespace App\Helpers\System;

use Cache;
use Illuminate\Support\Str;

class DownloadFileHelper
{
    public static function getFileName($file_name, $extension, $options = [])
    {
        $verion = $options['version'] ?? config('app.export_version', '1.0.0');
        $path = config('app.prefix_file_export')  . $file_name . '_' . $verion . ".$extension";
        return $path;
    }
    protected $return = [];

    public function __construct()
    {
    }
    public function setPath($path)
    {
        $this->return['path'] = $path;
        return $this;
    }
    public function setIsFullPath()
    {
        $this->return['is_full_path'] = true;
        return $this;
    }
    public function setFileName($file_name)
    {
        $file_name = str_replace(array("/", "\\", ":", "*", "?", "«", "<", ">", "|"), "-", $file_name);
        $this->return['file_name'] = $file_name;
        return $this;
    }
    public function setDeleteFileAfterSend($value = true)
    {
        $this->return['delete_file_after_send'] = $value;
        return $this;
    }
    public function build()
    {
        //open url api/download/data/$key
        $key = Str::uuid();
        Cache::store('redis')->put('download-' . $key, json_encode($this->return), 60 * 60);
        return $key;
    }
}
