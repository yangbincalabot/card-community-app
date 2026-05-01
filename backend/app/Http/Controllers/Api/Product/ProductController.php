<?php

namespace App\Http\Controllers\Api\Product;

use App\Http\Resources\ProductResource;
use App\Models\Banner;
use App\Models\Category;
use App\Models\CouponCode;
use App\Models\Product;
use App\Models\ProductSku;
use App\Models\User;
use App\Services\BannerService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Product\ProductSkuCategory;

class ProductController extends Controller
{
    public function index(Request $request,BannerService $bannerService,Product $product){
        $banners = $bannerService->get(Banner::PRODUCT_BANNER_TYPE); // 首页轮播

        // 创建一个查询构造器
        $builder = Product::query()->where('on_sale', true);
        // 判断是否有提交 search 参数，如果有就赋值给 $search 变量
        // search 参数用来模糊搜索商品
        if ($search = $request->input('search', '')) {
            $like = '%'.$search.'%';
            // 模糊搜索商品标题、商品详情、SKU 标题、SKU描述
            $builder->where(function ($query) use ($like) {
                $query->where('title', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhereHas('skus', function ($query) use ($like) {
                        $query->where('title', 'like', $like)
                            ->orWhere('description', 'like', $like);
                    });
            });
        }
        // 是否有提交 order 参数，如果有就赋值给 $order 变量
        // order 参数用来控制商品的排序规则
        if ($order = $request->input('order', 'default')) {
            if($order == 'default'){
                $builder->orderBy('id','desc');
            }else{
                // 是否是以 _asc 或者 _desc 结尾
                if (preg_match('/^(.+)_(asc|desc)$/', $order, $m)) {
                    // 如果字符串的开头是这 3 个字符串之一，说明是一个合法的排序值
                    if (in_array($m[1], ['price', 'sold_count', 'created_at'])) {
                        // 根据传入的排序值来构造排序参数
                        $builder->orderBy($m[1], $m[2]);
                    }
                }
            }

        }


        // 是否有提交 category 参数，如果有就赋值给 $category 变量
        // category 参数用来控制商品的类型
        if ($request->input('category_id')) {
            $category = Category::find($request->input('category_id'));
            $category_id = $category->id;
            $builder->where('category_id',$category_id);
        }

        $product_type = Product::TYPE_NORMAL;
        if ($request->input('type')) {
            $product_type = $request->input('type');
            if(in_array($product_type,[Product::TYPE_NORMAL,Product::TYPE_PROMOTION])){
                $builder->where('type',$product_type);
            }else{
                $product_type = Product::TYPE_NORMAL;
            }
        }

        // 根据会员类型返回当前会员类型购买商品的实际价格
        $products = $builder->paginate(4);

        $categoryLists = Category::where('parent_id',1)->select('id','name')->orderBy('sort','desc')->get();
        $result = [
            'products' => $products,
            'filters'  => [
                'search' => $search,
                'order'  => $order,
                'category_id' => $category_id ?? 0,
                'type' => $product_type
            ],
            'category' => $categoryLists,
            'banners' => $banners,
        ];

        return new ProductResource($result);
    }

    public function show(Product $product,Request $request){
        $id = $request->get('id');
        $detail = $product->where('id',$id)->first();
        if(empty($detail)){
            abort(404,'商品不存在');
        }
        // 获取属于当前商品的所有 sku
        $detailSkus = ProductSku::query()->where('product_id',$detail->id)->orderBy('stock','desc')->get();
        // 获取当前商品的所有 sku 类型
        $skus = ProductSkuCategory::query()->where('category_id',1)->get()->toArray();
        foreach ($skus as $key=>$sku){
            foreach ($detailSkus as $detailSku){
                if($detailSku->sku_category_id == $sku['id']){
                    $skus[$key]['sku_arr'][] = $detailSku;
                }
            }
        }

        $defaultCheckedSku = $detailSkus->last();
        $detail->skus_data = $skus;
        $detail->default_checked_sku = $defaultCheckedSku;

        // 获取所有可用的优惠券
        $coupons = CouponCode::where('enabled',true)->with([
            'user_coupons' => function($query){
                if (auth('api')->check()) {
                    // 用户已经登录了...
                    $user_id  = auth('api')->id();
                    $query->where('user_id',$user_id);
                }
            }
        ])->orderBy('value','desc')->get();
        foreach ($coupons as $coupon){
            if($coupon->user_coupons->isEmpty()){
                $coupon->has_get = 2;
            }else{
                $coupon->has_get = 1;
            }
        }
        // 获取当前用户已领取的所有可用的优惠券
        $detail->coupons = $coupons;
        $requestUser = '';
        if (auth('api')->check()) {
            // 用户已经登录了...
            $requestUser  = auth('api')->user();
        }
        $detail->requset_user = $requestUser;
        return new ProductResource($detail);
    }
}
