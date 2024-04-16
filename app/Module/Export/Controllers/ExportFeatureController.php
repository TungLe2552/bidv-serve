<?php

namespace App\Module\Export\Controllers;

use App\Helpers\Dynamic\DynamicTableHelper;
use App\Helpers\Dynamic\TableHelper;
use App\Helpers\System\DownloadFileHelper;
use App\Http\Controllers\Controller;
use App\Library\QueryBuilder\QueryBuilder;
use App\Models\Base\IrModel;
use App\Models\Base\IrModelField;
use App\Module\Export\Writer\WriterFactory;
use App\Module\Field\FieldFactory;
use Illuminate\Http\Request;
use App\Repositories\LogRepository;

class ExportFeatureController extends Controller
{
    protected $log_repository;
    public function __construct()
    {
        $this->log_repository = LogRepository::getInstance();
    }
    protected function getData(Request $request, IrModel $table)
    {
        $code = $table->code;
        $table_name = $request->get('$table', $code);
        $table_export_name = $request->get('$tableExport', $code);
        $query = TableHelper::getQuery($table, $table_export_name);
        $query = QueryBuilder::for($query, $request)
            ->initFromModelForList($table_name, true)
            ->allowedPagination();
        return $query->get();
    }
    public function export(Request $request, $table_id)
    {
        $table = TableHelper::getTable($table_id);
        $code = $table->code;
        $data = $this->getData($request, $table);

        if ($request->has('$table')) {
            $fields = IrModelField::whereHas('model', function ($query) use ($code) {
                $query->isView();
                $query->where('code', $code . ".view");
            })->byUser()->get();
            if ($fields->count() == 0) {
                $fields = $table->fields;
            }
        } else {
            $fields = $table->fields;
        }
        $fields = $fields->filter(
            function ($item) {
                return !in_array(trim($item['type']), ['bytea']);
            }
        )->map(function ($field) {
            return FieldFactory::get($field);
        });
        $headers = $fields->map(function ($field) {
            return ['value' => $field->getCode(), 'text' => $field->getLabel()];
        })->toArray();
        $data = $data->map(function ($item) use ($fields) {
            $result = [];
            foreach ($fields as $field) {
                try {
                    $result[$field->getCode()] = $field->getValueExport($item);
                } catch (\Throwable $th) {
                }
            }
            return $result;
        });
        $log = $this->log_repository;
        $log->checkLog($code, 'export', function ($table) use ($log) {
            $log->logModel($table, 'export');
        });
        $writer = WriterFactory::getByType('excel');
        $path = $writer->write($data, $headers, 'export', ['title' => $table->name, 'sheet_name' => $table->name]);
        $builder = new DownloadFileHelper;
        $builder->setPath($path);
        $file_name = $table->name;
        $builder->setFileName(DownloadFileHelper::getFileName($file_name, 'xlsx'));
        return $this->responseSuccess($builder->build());
    }
}
