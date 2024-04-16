<?php

namespace App\Services\Contract;

use App\Models\BA\Contract\Contract;
use App\Models\BA\Contract\ContractAppendix;
use App\Models\BA\Contract\ContractAppendixLine;
use App\Models\BA\Contract\ContractAppendixStatus;
use App\Repositories\BaseRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ContractAppendixService
{
    protected $contractRepository;
    protected $contractAppendixRepository;
    protected $contractAppendixLineRepository;
    protected $contractAppendixStatusRepository;
    public function __construct()
    {
        $this->contractRepository = new BaseRepository(Contract::class, [Contract::LOG_NAME]);
        $this->contractAppendixRepository = new BaseRepository(ContractAppendix::class, [ContractAppendix::LOG_NAME]);
        $this->contractAppendixLineRepository = new BaseRepository(ContractAppendixLine::class, [ContractAppendixLine::LOG_NAME]);
        $this->contractAppendixStatusRepository = new BaseRepository(ContractAppendixStatus::class, [ContractAppendixStatus::LOG_NAME]);
    }

    public function listAppendixesByContract($id, $status)
    {
        $data = $this->contractRepository->getModel()::with([
            'contractAppendix' => function ($query) use ($status) {
                $active = $this->contractAppendixStatusRepository->getModel()::where('name', 'Hiệu lực')->first();
                $query->orderByRaw("contract_appendix_status_id = $active->id DESC")->orderBy('end_date', 'desc')->orderBy('appendix_number', 'asc');
            },
            'contractAppendix.contractAppendixStatus',
        ])->find($id);
        return $data;
    }

    public function create(array $data)
    {
        DB::beginTransaction();
        try {
            if (!empty($data['id'])) {
                $isExist = $this->contractAppendixRepository->getModel()::where('id', $data['id'])->exists();
                if ($isExist) {
                    $copiedContractAppendix = $this->contractAppendixRepository->find($data['id']);
                    $statuses = $this->contractAppendixStatusRepository->getModel()::get()->mapWithKeys(function ($item, $key) {
                        return [$item['name'] => $item['id']];
                    });
                    $copiedContractAppendix->update(['contract_appendix_status_id' => $statuses['Hết hạn']]);
                } else {
                    return response()->json([
                        'error' => 'Phụ lục hợp đồng không tồn tại trong hệ thống',
                    ], 404, []);
                }
            }
            if (empty($data['contract_appendix_status_id'])) {
                $statusDefault = ContractAppendixStatus::where('code', ContractAppendixStatus::DEFAULT_STATUS_CODE)->where('active', true)->first();
                if (!empty($statusDefault)) {
                    $data['contract_appendix_status_id'] = $statusDefault->id;
                } else {
                    return response()->json([
                        'error' => 'Giá trị mặc định ' . '( ' . ContractAppendixStatus::DEFAULT_STATUS_CODE . ' ) không đúng',
                    ], 422, []);
                }
            }
            // $contractAppendix = null;
            $contractAppendix = $this->contractAppendixRepository->create([
                'contract_id' => $data['contract_id'],
                'appendix_number' => $data['appendix_number'],
                'signing_date' => $data['signing_date'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'contract_appendix_status_id' => $data['contract_appendix_status_id'],
                'notes' => $data['notes'],
                'created_by' => Auth::user()->id,
            ]);

            if (!empty($data['contract_appendix_lines']) && count($data['contract_appendix_lines']) > 0) {
                $contractAppendixLines = $data['contract_appendix_lines'];

                foreach ($contractAppendixLines as $key => $appendixLine) {
                    $contractAppendixLine = $contractAppendix->contractAppendixLines()->create(array_merge($appendixLine, [
                        'line_number' => $key + 1,
                    ]));
                    if (!empty($appendixLine['production_systems'])) {
                        $contractAppendixLine->productionSystems()->sync($appendixLine['production_systems']);
                    }
                    if (!empty($appendixLine['existed_status_product'])) {
                        $contractAppendixLine->existedStatusProduct()->sync($appendixLine['existed_status_product']);
                    }

                    if (array_key_exists('image', $appendixLine)) {

                        $image = $appendixLine['image'];
                        $this->handleImageData($image, $contractAppendixLine);
                    }
                }
            }
            DB::commit();
            return $contractAppendix;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update($id, array $data)
    {
        DB::beginTransaction();
        try {
            if (empty($data['contract_appendix_status_id'])) {
                $statusDefault = ContractAppendixStatus::where('code', ContractAppendixStatus::DEFAULT_STATUS_CODE)->where('active', true)->first();
                if (!empty($statusDefault)) {
                    $data['contract_appendix_status_id'] = $statusDefault->id;
                } else {
                    return response()->json([
                        'error' => 'Giá trị mặc định ' . '( ' . ContractAppendixStatus::DEFAULT_STATUS_CODE . ' ) không đúng',
                    ], 422, []);
                }
            }
            // $contractAppendix = null;
            $contractAppendix = $this->contractAppendixRepository->update($id, $data);

            if (!empty($data['contract_appendix_lines']) && count($data['contract_appendix_lines']) > 0) {

                $contractAppendixLines = $data['contract_appendix_lines'];

                // $contractAppendixLineIds = $this->contractAppendixLineRepository
                //     ->getModel()::where('contract_appendix_id', $id)
                //     ->pluck('id')
                //     ->toArray();
                // if (!empty($contractAppendixLineIds)) {
                //     foreach ($contractAppendixLineIds as $lineId) {
                //         $lineToBeDeleted = $this->contractAppendixLineRepository->find($lineId);
                //         $lineToBeDeleted->productionSystems()->detach();
                //         $lineToBeDeleted->imageAttachment()->delete();
                //         $lineToBeDeleted->delete();
                //     }
                // }
                // $contractAppendix = $this->contractAppendixRepository->update($id, $data);

                $existingLinesId = [];

                foreach ($contractAppendixLines as $key => $appendixLine) {

                    if (is_string($appendixLine['id'])) {
                        // Tạo mới bản ghi với UUID
                        $contractAppendixLine = $contractAppendix->contractAppendixLines()->create(array_merge($appendixLine, [
                            'line_number' => $key + 1,
                        ]));
                        $existingLinesId[] = $contractAppendixLine->id;
                        if (!empty($appendixLine['production_systems'])) {
                            $contractAppendixLine->productionSystems()->sync($appendixLine['production_systems']);
                        }

                        if (!empty($appendixLine['existed_status_product'])) {
                            // Sử dụng sync để cập nhật lại mối quan hệ
                            $contractAppendixLine->existedStatusProduct()->sync($appendixLine['existed_status_product']);
                        } else {
                            // Nếu không có dữ liệu existed_status_products, bạn có thể xóa tất cả các mối quan hệ hiện tại
                            $contractAppendixLine->existedStatusProduct()->detach();
                        }
                        if (array_key_exists('image', $appendixLine)) {

                            $image = $appendixLine['image'];
                            $this->handleImageData($image, $contractAppendixLine);
                        }
                    } else {
                        // Cập nhật bản ghi với ID không phải là UUID
                        $contractAppendixLine = ContractAppendixLine::where('id', $appendixLine['id'])->first();
                        $existingLinesId[] = $appendixLine['id'];

                        if ($contractAppendixLine) {
                            $contractAppendixLine->update([
                                'item_id' => $appendixLine['item_id'],
                                'name' => $appendixLine['name'],
                                'priority' => $appendixLine['priority'],
                                'unit_id' => $appendixLine['unit_id'],
                                'production_unit_price' => $appendixLine['production_unit_price'],
                                'purchasing_unit_price' => $appendixLine['purchasing_unit_price'],
                                'description' => $appendixLine['description'],
                                'factor' => $appendixLine['factor'],
                            ]);
                            if (!empty($appendixLine['production_systems'])) {
                                $contractAppendixLine->productionSystems()->sync($appendixLine['production_systems']);
                            }

                            if (!empty($appendixLine['existed_status_product'])) {
                                // Sử dụng sync để cập nhật lại mối quan hệ
                                $contractAppendixLine->existedStatusProduct()->sync($appendixLine['existed_status_product']);
                            } else {
                                // Nếu không có dữ liệu existed_status_products, bạn có thể xóa tất cả các mối quan hệ hiện tại
                                $contractAppendixLine->existedStatusProduct()->detach();
                            }
                        }
                    }

                    // $contractAppendixLine = $contractAppendix->contractAppendixLines()->create(array_merge($appendixLine, [
                    //     'line_number' => $key + 1,
                    // ]));
                    // if (!empty($appendixLine['production_systems'])) {
                    //     $contractAppendixLine->productionSystems()->attach($appendixLine['production_systems']);
                    // }
                    // if (array_key_exists('image', $appendixLine)) {
                    //     $image = $appendixLine['image'];
                    //     $this->handleImageData($image, $contractAppendixLine);
                    // }
                }

                // dd($existingLinesId);

                if (count($existingLinesId) > 0) {
                    $contractAppendixLinesDiff = $contractAppendix->contractAppendixLines()
                        ->whereNotIn('id', $existingLinesId)
                        ->get();
                    foreach ($contractAppendixLinesDiff as $lineId) {
                        $lineToBeDeleted = $this->contractAppendixLineRepository->find($lineId->id);
                        $lineToBeDeleted->productionSystems()->detach();
                        $lineToBeDeleted->imageAttachment()->delete();
                        $lineToBeDeleted->existedStatusProduct()->detach();
                        $lineToBeDeleted->delete();
                    }
                }
            } else {
                $contractAppendixLinesAll = $contractAppendix->contractAppendixLines()->get();
                foreach ($contractAppendixLinesAll as $lineId) {
                    $lineToBeDeleted = $this->contractAppendixLineRepository->find($lineId->id);
                    $lineToBeDeleted->productionSystems()->detach();
                    $lineToBeDeleted->imageAttachment()->delete();
                    $lineToBeDeleted->existedStatusProduct()->detach();
                    $lineToBeDeleted->delete();
                }
            }
            DB::commit();
            return $contractAppendix;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $contractAppendixLineIds = $this->contractAppendixLineRepository
                ->getModel()::where('contract_appendix_id', $id)
                ->pluck('id')
                ->toArray();
            if (!empty($contractAppendixLineIds)) {
                foreach ($contractAppendixLineIds as $lineId) {
                    $lineToBeDeleted = $this->contractAppendixLineRepository->find($lineId);
                    $lineToBeDeleted->productionSystems()->detach();
                    $lineToBeDeleted->existedStatusProduct()->detach();
                    $lineToBeDeleted->imageAttachment()->delete();
                    $lineToBeDeleted->delete();
                }
            }
            $deleted = $this->contractAppendixRepository->delete($id);
            DB::commit();
            return $deleted;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function handleImageData($image, $contractAppendixLine)
    {
        if (!empty($image)) {
            $modelName = $contractAppendixLine->getTable();
            $imageName = $modelName . now()->format('Y-m-d');
            $imageSize = null;
            $attachment = [
                'name' => $imageName,
                'datas_fname' => $imageName,
                'description' => $imageName,
                'res_model' => $modelName,
                'res_id' => $contractAppendixLine->id,
                'create_date' => Carbon::now(),
                'create_uid' => request()->user()->id,
                'db_datas' => $image,
                'file_size' => $imageSize,
            ];
            if ($contractAppendixLine->imageAttachment()->exists()) {
                $contractAppendixLine->imageAttachment()->update($attachment);
            } else {
                $contractAppendixLine->imageAttachment()->create($attachment);
            }
        } else {
            $contractAppendixLine->imageAttachment()->delete();
        }
    }
}
