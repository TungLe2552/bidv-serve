<?php

namespace App\Http\Resources;

use App\Traits\Model\ConvertByteaToBaseInResponse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\Resources\Json\ResourceCollection;

class Items extends ResourceCollection
{
    use ConvertByteaToBaseInResponse;
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if ($this->resource instanceof LengthAwarePaginator) {
            $this->collection = $this->ConvertByteaToBaseInResponse(null, $this->collection);

            $result = [
                'list' => $this->collection,
                'pagination' => [
                    'count' => $this->count(),
                    'hasMoreItems' => $this->hasMorePages(),
                    'page' => $this->currentPage(),
                    'total' => $this->total(),
                    'totalPage' => $this->lastPage(),
                    'itemsPerPage' => (float) $this->perPage(),
                ],
            ];
        } else if ($this->resource instanceof Paginator) {
            $this->collection = $this->ConvertByteaToBaseInResponse(null, $this->collection);

            $result = [
                'list' => $this->collection,
                'pagination' => [
                    'count' => $this->count(),
                    'hasMoreItems' => $this->hasMorePages(),
                    'page' => $this->currentPage(),
                    'itemsPerPage' => (float) $this->perPage(),
                ],
            ];
        } else {
            $this->collection = $this->ConvertByteaToBaseInResponse(null, $this->collection);
            $result = $this->collection;
        }

        return $result;
    }
}
