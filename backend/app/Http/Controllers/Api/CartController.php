<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\AddCartRequest;
use App\Http\Requests\DecreaseCartItemRequest;
use App\Http\Resources\CartResource;
use App\Models\ProductSku;
use App\Services\CartService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CartController extends Controller
{
    protected $cartService;

    // 利用 Laravel 的自动解析功能注入 CartService 类
    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function index(Request $request)
    {
        $cartItems = $this->cartService->get();
        return new CartResource($cartItems);
    }

    public function store(AddCartRequest $request){
        $this->cartService->add($request->input('sku_id'), $request->input('amount'));
        return [];
    }

    public function cartItemDecrement(DecreaseCartItemRequest $request){
        $this->cartService->decrease($request->input('sku_id'));
        return [];
    }

    public function remove(ProductSku $sku, Request $request)
    {
        $sku_ids_arr = $request->get('sku_ids');
        $sku_ids = array_column($sku_ids_arr,'sku_id');
        $this->cartService->remove($sku_ids);
        return [];
    }
}
