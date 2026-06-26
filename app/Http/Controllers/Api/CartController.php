<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ECart;
use App\Models\ECartItem;
use App\Support\ApiResponse;

class CartController extends Controller
{
    // Lấy giỏ (sau khi đã login)
    public function index(Request $req)
    {
        $accId = $req->user()->id;
        $cart = ECart::firstOrCreate(['account_id' => $accId], ['session_token' => null]);

        if (!$cart) {
            return response()->json(['status' => false, 'message' => 'Không tìm thấy thông tin giỏ hàng!']);
        }

        // Get cart items with product details and images
        $cartItems = ECartItem::where('cart_id', $cart->id)
            ->with(['product' => function ($query) {
                $query->select('id', 'name'); // Select necessary fields from product
                $query->with('images'); // Eager load images
            }, 'attribute'])
            ->get();

        // Format the response
        $formattedItems = $cartItems->map(function ($item) {
            // Lấy ảnh đầu tiên nếu có (hỗ trợ file_path, url, path)
            $firstImage = null;
            if ($item->product && $item->product->images && $item->product->images->count()) {
                $firstImage = $item->product->images->first();
            }

            $urlProductImagePrimary = null;
            if ($firstImage) {
                $urlProductImagePrimary = $firstImage->file_path ?? null;
            }

            return [
                'id' => $item->id,
                'attribute_id' => $item->attribute->id,
                'attribute_name' => $item->attribute->name,
                'image' => $urlProductImagePrimary,
                'name' => $item->product->name ?? null,
                'old_price' => $item->attribute->price_old ?? null,
                'price' => $item->unit_price,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity
            ];
        });

        // tính tổng quantity của toàn bộ giỏ hàng
        $totalQuantity = ECartItem::where('cart_id', $cart->id)->sum('quantity');

        return ApiResponse::success(['cart_id' => $cart->id, 'items' => $formattedItems, 'totalQuantity' => $totalQuantity]);
    }

    // Merge: nhận payload items từ localStorage (sau login)
    public function mergeLocal(Request $req)
    {
        $data = $req->validate([
            'items'               => 'required|array',
            'items.*.product_id'  => 'required|integer',
            'items.*.attribute_id' => 'nullable|integer',
            'items.*.quantity'         => 'required|numeric|min:0.001',
            'items.*.unit_price'  => 'nullable|integer',
        ]);

        $accId = $req->user()->id;
        $cart  = ECart::firstOrCreate(['account_id' => $accId], ['session_token' => null]);

        foreach ($data['items'] as $it) {
            $attrId = $it['attribute_id'] ?? null;
            // bỏ qua nếu không có thuộc tính
            if (!$attrId) {
                continue;
            }

            $q = ECartItem::where('cart_id', $cart->id)
                ->where('product_id', $it['product_id'])
                ->where('attribute_id', $attrId);

            $row = $q->first();
            $incQuantity = (int) $it['quantity'];
            // trường hợp có rồi thì sẽ thực hiện cộng theo số lượng
            if ($row) {
                $row->quantity = $row->quantity + $incQuantity;
                $row->save();
            } else {
                ECartItem::create([
                    'cart_id'     => $cart->id,
                    'product_id'  => (int)$it['product_id'],
                    'attribute_id' => $attrId,
                    'quantity'         => $incQuantity,
                    'unit_price'  => (int)($it['unit_price'] ?? 0),
                ]);
            }
        }

        return ApiResponse::success(['merged' => true, 'cart_id' => $cart->id]);
    }

    public function update(Request $req, $id)
    {
        $req->validate(['quantity' => 'required|numeric|min:1']);
        $item = ECartItem::findOrFail($id);
        $this->assertOwner($req->user()->id, $item->cart_id);
        $item->quantity = (int) $req->quantity;
        $item->save();
        // tính tổng quantity của toàn bộ giỏ hàng
        $totalQuantity = ECartItem::where('cart_id', $item->cart_id)->sum('quantity');
        return ApiResponse::success(['totalQuantity' => $totalQuantity]);
    }

    public function store(Request $req)
    {
        $data = $req->validate([
            'product_id'   => 'required|integer',
            'attribute_id' => 'nullable|integer',
            'quantity'        => 'required|numeric|min:0.001',
            'price'   => 'nullable|integer',
        ]);

        // thông tin tài khoản đang login
        $accId = $req->user()->id;
        $cart  = ECart::firstOrCreate(['account_id' => $accId], ['session_token' => null]);
        $this->assertOwner($req->user()->id, $cart->id);

        $attrId = $data['attribute_id'] ?? null;
        // bỏ qua nếu không có thuộc tính
        if (!$attrId) {
            return response()->json(['status' => false, 'message' => 'Thiếu thuộc tính sản phẩm!'], 400);
        }

        $q = ECartItem::where('cart_id', $cart->id)
            ->where('product_id', $data['product_id'])
            ->where('attribute_id', $attrId);

        $row = $q->first();
        $incQuantity = (int) $data['quantity'];
        // trường hợp có rồi thì sẽ thực hiện cộng theo số lượng
        if ($row) {
            $row->quantity = $row->quantity + $incQuantity;
            $row->save();
        } else {
            ECartItem::create([
                'cart_id'     => $cart->id,
                'product_id'  => (int)$data['product_id'],
                'attribute_id' => $attrId,
                'quantity'         => $incQuantity,
                'unit_price'  => (int)($data['price'] ?? 0),
            ]);
        }

        // tính tổng quantity của toàn bộ giỏ hàng
        $totalQuantity = ECartItem::where('cart_id', $cart->id)->sum('quantity');

        return ApiResponse::success(['cart_id' => $cart->id, 'totalQuantity' => $totalQuantity]);
    }

    public function destroy(Request $req, $id)
    {
        $item = ECartItem::findOrFail($id);
        $this->assertOwner($req->user()->id, $item->cart_id);
        $item->delete();
        return ApiResponse::success();
    }

    /**
     * Xóa tất cả items khỏi giỏ hàng
     */
    public function destroyAll(Request $req)
    {
        $accId = $req->user()->id;
        $cart = ECart::where('account_id', $accId)->first();

        if (!$cart) {
            return response()->json([
                'status' => true,
                'message' => 'Giỏ hàng đã trống'
            ]);
        }

        // Xóa tất cả items
        ECartItem::where('cart_id', $cart->id)->delete();

        // Xóa tất cả cart
        $cart->delete();

        return ApiResponse::success([
            'message' => 'Đã xóa tất cả sản phẩm khỏi giỏ hàng',
            'items' => [],
            'totalQuantity' => 0
        ]);
    }

    private function assertOwner($accountId, $cartId)
    {
        abort_unless(ECart::where('id', $cartId)->where('account_id', $accountId)->exists(), 403);
    }
}
