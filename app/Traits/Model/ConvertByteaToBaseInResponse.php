<?php

namespace App\Traits\Model;

/**
 * Trait ResponseType.
 */
trait ConvertByteaToBaseInResponse
{

    public function ConvertByteaToBaseInResponse($data, $datas = null)
    {
        $imageFields = ['image', 'image_small', 'image_medium', 'model_image', 'model_image_medium', 'model_image_small'];
        if (!empty($data)) {
            foreach ($imageFields as $fieldName) {
                if (isset($data[$fieldName]) && is_resource($data[$fieldName]) && get_resource_type($data[$fieldName]) === 'stream') {
                    $my_bytea = stream_get_contents($data[$fieldName]);
                    $data[$fieldName] = $my_bytea;
                }
            }
            return $data;
        } elseif (!empty($datas) || count($datas) > 0) {
            foreach ($datas as $item) {
                foreach ($imageFields as $field) {
                    if (is_object($item)) {
                        if (isset($item->{$field}) && is_resource($item->{$field}) && get_resource_type($item->{$field}) === 'stream') {
                            $item->{$field} = stream_get_contents($item->{$field});
                        }
                    } else {
                        if (isset($item[$field]) && is_resource($item[$field]) && get_resource_type($item[$field]) === 'stream') {
                            $item[$field] = stream_get_contents($item[$field]);
                        }
                    }
                }
            }
            return $datas;
        }
    }
}
