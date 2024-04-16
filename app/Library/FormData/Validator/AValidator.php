<?php

namespace App\Library\FormData\Validator;

use Illuminate\Database\Eloquent\Model;

class AValidator
{
    public function getRules(array $data = [], Model $model = null)
    {
        return [];
    }
    public function getMessages(array $data = [], Model $model = null)
    {
        return [];
    }
}
