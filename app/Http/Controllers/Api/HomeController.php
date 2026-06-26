<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        $productNews = Product::take(10)->with(['category', 'brand', 'unit', 'images', 'attributes'])->orderBy('created_at', 'desc')->get();
        $productBests = Product::take(10)->with(['category', 'brand', 'unit', 'images', 'attributes'])->inRandomOrder()->get();

        // Lấy 4 sản phẩm có giảm giá tốt nhất (tính theo %) và mới nhất
        $productDealIds = DB::table('e_product_attributes')
            ->join('e_products', 'e_products.id', '=', 'e_product_attributes.product_id')
            ->select('e_products.id')
            ->where('e_product_attributes.price_old', '>', 0)
            ->whereRaw('e_product_attributes.price_new < e_product_attributes.price_old')
            ->groupBy('e_products.id', 'e_products.created_at')
            ->orderByRaw('MAX((e_product_attributes.price_old - e_product_attributes.price_new) / e_product_attributes.price_old) DESC')
            ->orderBy('e_products.created_at', 'DESC')
            ->take(4)
            ->pluck('e_products.id')
            ->toArray();

        if (!empty($productDealIds)) {
            $idsOrdered = implode(',', $productDealIds);
            $productDeals = Product::whereIn('id', $productDealIds)
                ->with(['category', 'brand', 'unit', 'images', 'attributes'])
                ->orderByRaw("FIELD(id, $idsOrdered)")
                ->get();
        } else {
            $productDeals = collect([]);
        }

        // Check favorites if user is authenticated via Sanctum
        if (Auth::guard('sanctum')->check()) {
            $userId = Auth::guard('sanctum')->id();
            $favoriteIds = \App\Models\EFavorite::where('account_id', $userId)->pluck('product_id')->toArray();

            $checkFavorite = function ($product) use ($favoriteIds) {
                $product->is_favorite = in_array($product->id, $favoriteIds);
            };

            $productNews->each($checkFavorite);
            $productBests->each($checkFavorite);
            $productDeals->each($checkFavorite);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'productNews' => $productNews,
                'productBests' => $productBests,
                'productDeals' => $productDeals,
            ]
        ]);
    }
}
