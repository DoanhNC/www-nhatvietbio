<?php
// routes/api.php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{
    CartController
};

// Cart (sau login)
Route::prefix('web/cart')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        //thực hiện merge giỏ localStorage lên server
        Route::post('/merge-local', [CartController::class, 'mergeLocal']);
        //lấy danh sách giỏ hàng
        Route::get('/',             [CartController::class, 'index']);
        //thêm sản phẩm trong giao diện giỏ hàng (sau login)
        Route::put('/items/{id}',   [CartController::class, 'update']);
        //thêm sản phẩm vào giỏ hàng trong giao diện chi tiết sản phẩm
        Route::post('/add',         [CartController::class, 'store']);
        //xóa sản phẩm khỏi giỏ hàng
        Route::delete('/items/{id}', [CartController::class, 'destroy']);
        Route::delete('/clear', [CartController::class, 'destroyAll']);
    });
});
