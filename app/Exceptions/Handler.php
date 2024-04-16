<?php

namespace App\Exceptions;

use App\Constants\QueryErrorCode;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
        $this->renderable(function (QueryException $e) {
            if (str_contains($e->getSql(), "delete") && $e->getCode() === QueryErrorCode::FOREIGN_KEY_VIOLATION) {
                return response()->json([
                    'message' => 'Dữ liệu đang sử dụng. Bạn phải xóa các dữ liệu liên quan.',
                    'hint' => $e->getMessage()
                ], 422);
            };
        });
    }
}
