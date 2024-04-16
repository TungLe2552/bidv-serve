<?php

namespace App\Helpers\System;

use App\Traits\Uuid;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ImportFileHelper
{
    protected $return = [];

    public function setPath($path)
    {
        $this->return['path'] = $path;
        return $this;
    }
    public function build()
    {
        $key = (string) Str::uuid();
        Cache::put('import-' . $key, $this->return, 600);
        return [
            'key' => $key
        ];
    }
}
