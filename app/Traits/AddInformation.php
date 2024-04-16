<?php

namespace App\Traits;

use App\Models\Base\IrModel;
use App\Models\System\AdditionalInformation;

trait AddInformation
{
    public function setInformation(array $data = [], $object_id)
    {
        foreach ($data as $key => $value) {
            $add_info = AdditionalInformation::updateOrCreate([
                'object_id' => $object_id,
                'field_id' => $key
            ], [
                'object_id' => $object_id,
                'field_id' => $key,
                'value' => $value
            ]);
        }
    }

    public function getInformation(string $table, $object_id)
    {
        $ir_model = IrModel::query()->where('code', $table)->first();
        $additionalFields =  $ir_model->additionalFields()->pluck('id');

        $data = AdditionalInformation::query()
            ->where('object_id', $object_id)
            ->whereIn('field_id', $additionalFields)
            ->get();
        $add_info = $data->pluck('value', 'field_id');
        return $add_info;
    }
}
