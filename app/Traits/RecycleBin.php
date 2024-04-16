<?php

namespace App\Traits;

use App\Models\Base\IrModel;
use App\Models\System\FileAttachment;
use Illuminate\Database\Eloquent\Model;
use App\Models\System\Trash;
use App\Models\System\TrashRelationship;
use DB;
use Storage;

/**
 * Trait Trash.
 */
trait RecycleBin
{
    public function createTrash(Model $model, int $type_id)
    {
        $table = $model->getTable();

        $ir_model = IrModel::where('code', $table)->first();
        if (empty($ir_model)) {
            abort(400, 'Chưa được tạo model cho bảng trong cơ sở dữ liệu');
        }

        $trash = Trash::create([
            'name' => $model->name,
            'model_id' => $ir_model->id,
            'object_id' => $model->id,
            'type_id' => $type_id
        ]);
    }

    public function restoreTrash(int $id)
    {
        DB::transaction(
            function () use ($id) {
                $trash = Trash::findOrFail($id);
                $ir_model = IrModel::findOrFail($trash->model_id);

                DB::table($ir_model->code)
                    ->where('id', $trash->object_id)
                    ->update(['deleted_at' => null]);

                $trash->delete();
            }
        );
    }

    private function checkRelation(IrModel $ir_model, array $object)
    {
        $info = [];
        $relations = TrashRelationship::query()
            ->where('model_id', $ir_model->id)
            ->where('is_delete', false)
            ->get();

        foreach ($relations as $relation) {
            $count =  DB::table($relation->relationship)
                ->where($relation->foreign_key, $object["$relation->local_key"])
                ->count();
            if ($count > 0) {
                array_push($info, ["name" => $relation->name, "count" => $count]);
            }
        }

        if (count($info) > 0) {
            return  $info;
        } else {
            return null;
        };
    }

    private function extraDelete(IrModel $ir_model, array $object)
    {
        $relations = TrashRelationship::query()
            ->where('model_id', $ir_model->id)
            ->where('is_delete', true)
            ->where('is_delete_after', false)
            ->get();

        foreach ($relations as $relation) {
            DB::table($relation->relationship)
                ->where($relation->foreign_key, $object["$relation->local_key"])
                ->delete();
        }
    }

    private function extraAfterDelete(IrModel $ir_model, array $object)
    {
        $relations = TrashRelationship::query()
            ->where('model_id', $ir_model->id)
            ->where('is_delete', true)
            ->where('is_delete_after', true)
            ->get();

        foreach ($relations as $relation) {
            DB::table($relation->relationship)
                ->where($relation->foreign_key, $object["$relation->local_key"])
                ->delete();
        }
    }

    private function deleteAttachments(IrModel $ir_model, array $object)
    {

        $files = FileAttachment::where('res_model', $ir_model->code)
            ->where('res_id', $object['id'])
            ->get();
        foreach ($files as $file) {
            if ($file->url !== null) {
                Storage::disk("file")->delete($file->url);
            }
            $file->delete();
        }
    }

    public function destroyTrash(int $id)
    {

        $trash = Trash::findOrFail($id);
        $ir_model = IrModel::findOrFail($trash->model_id);

        $object = DB::table($ir_model->code)
            ->where('id', $trash->object_id)
            ->first();

        $object = json_decode(json_encode($object), true);

        $info = $this->checkRelation($ir_model, $object);

        if (!empty($info)) {
            return $info;
        } else {
            DB::beginTransaction();
            $this->extraDelete($ir_model, $object);
            DB::table($ir_model->code)
                ->where('id', $trash->object_id)
                ->delete();
            $this->extraAfterDelete($ir_model, $object);
            $trash->delete();
            $this->deleteAttachments($ir_model, $object);
            DB::commit();
        }
    }
}
