<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index');
    $router->get('users', 'UsersController@index');

    $router->get('products', 'ProductsController@index');
    $router->get('products/create', 'ProductsController@create');
    $router->post('products', 'ProductsController@store');
    $router->get('products/{id}/edit', 'ProductsController@edit');
    $router->put('products/{id}', 'ProductsController@update');
    $router->delete('products/{id}', 'ProductsController@destroy');

    // 促销商品
    $router->get('promotion-products', 'PromotionProductsController@index');
    $router->get('promotion-products/create', 'PromotionProductsController@create');
    $router->post('promotion-products', 'PromotionProductsController@store');
    $router->get('promotion-products/{id}/edit', 'PromotionProductsController@edit');
    $router->put('promotion-products/{id}', 'PromotionProductsController@update');
    $router->delete('promotion-products/{id}', 'PromotionProductsController@destroy');

    $router->resource('product-sku-category', ProductSkuCategoryController::class);

    $router->get('orders', 'OrdersController@index')->name('admin.orders.index');
    $router->get('orders/{order}', 'OrdersController@show')->name('admin.orders.show');
    $router->post('orders/{order}/ship', 'OrdersController@ship')->name('admin.orders.ship');
    $router->post('orders/{order}/refund', 'OrdersController@handleRefund')->name('admin.orders.handle_refund');

    $router->get('offline-orders', 'OfflineOrderController@index')->name('admin.offline_orders.index');
    $router->get('offline-orders/{order}', 'OfflineOrderController@show')->name('admin.offline_orders.show');
    $router->post('offline-orders/{order}/confirm-offline-pay', 'OfflineOrderController@confirmOfflinePaid')->name('admin.offline_orders.confirm_pay');
    $router->post('offline-orders/{order}/ship', 'OfflineOrderController@ship')->name('admin.offline_orders.ship');
    $router->post('offline-orders/{order}/refund', 'OfflineOrderController@handleRefund')->name('admin.offline_orders.handle_refund');
    $router->post('offline-orders/{order}/confirm-offline-refund', 'OfflineOrderController@confirmOfflineRefund')->name('admin.offline_orders.confirm_offline_refund');


    $router->get('coupon_codes', 'CouponCodesController@index');
    $router->post('coupon_codes', 'CouponCodesController@store');
    $router->get('coupon_codes/create', 'CouponCodesController@create');
    $router->get('coupon_codes/{id}/edit', 'CouponCodesController@edit');
    $router->put('coupon_codes/{id}', 'CouponCodesController@update');
    $router->delete('coupon_codes/{id}', 'CouponCodesController@destroy');

    $router->get('categories', 'CategoriesController@index');
    $router->get('categories/create', 'CategoriesController@create');
    $router->get('categories/{id}/edit', 'CategoriesController@edit');
    $router->post('categories', 'CategoriesController@store');
    $router->put('categories/{id}', 'CategoriesController@update');
    $router->delete('categories/{id}', 'CategoriesController@destroy');
    $router->get('api/categories', 'CategoriesController@apiIndex');

    $router->get('crowdfunding_products', 'CrowdfundingProductsController@index');
    $router->get('crowdfunding_products/create', 'CrowdfundingProductsController@create');
    $router->post('crowdfunding_products', 'CrowdfundingProductsController@store');
    $router->get('crowdfunding_products/{id}/edit', 'CrowdfundingProductsController@edit');
    $router->put('crowdfunding_products/{id}', 'CrowdfundingProductsController@update');

    $router->get('seckill_products', 'SeckillProductsController@index');
    $router->get('seckill_products/create', 'SeckillProductsController@create');
    $router->post('seckill_products', 'SeckillProductsController@store');
    $router->get('seckill_products/{id}/edit', 'SeckillProductsController@edit');
    $router->put('seckill_products/{id}', 'SeckillProductsController@update');


    // 公共配置
    $router->resource('configure', 'ConfigureController')->only(['index', 'store']);

    //todo 以前的 协会介绍
    $router->resource('introduction', 'IntroductionController')->only(['index', 'store']);

    // 协会管理
    $router->resource('associations', 'AssociationController');


    // 系统公告
    $router->resource('communal', 'CommunalController');

    // 广告位置
    $router->resource('adv-position', 'AdvPositionController');

    // 广告
    $router->get('advert/urls', 'AdvertController@getUrl')->name('advert.url.get');
    $router->resource('advert', 'AdvertController');

    // 活动回顾审核管理
    $router->resource('activity-reviews', 'Activity\ActivityReviewController');

    // banner管理
    $router->get('banner/urls', 'BannerController@getUrl')->name('banner.url.get');
    $router->resource('banner', 'BannerController');

    // 银行卡管理
    $router->resource('bank', 'BankController');

    // 活动管理
    $router->resource('activitys', 'Activity\ActivityController');
    // 会务管理
    $router->resource('meetings', 'Activity\MeetingController');
    // 活动分类管理
    $router->resource('activity-classification', 'Activity\ActivityClassificationController');
    //活动分类
    $router->get('api/acticity-type', 'Activity\ActivityClassificationController@api_acticity_type')->name('admin.activity.classification');
    // 报名管理
    $router->resource('applys', 'Activity\ApplyController');
    $router->post('apply-refund', 'Activity\RefundController@applyRefund')->name('admin.apply_refund.index');
    $router->post('apply-refund/{order}/refund', 'Activity\RefundController@handleRefund')->name('admin.apply_refund.handle_refund');

    // 报名人管理
    $router->resource('relevances', 'Activity\RelevanceController');

    // 提现管理
    $router->resource('withdraw', 'WithdrawController');

    // 代理等级管理
    $router->resource('agent', 'AgentController');
    // 代理审核管理
    $router->resource('apply-agent', 'UserApplyAgentController');
    // 门店管理
    $router->resource('store', 'StoreController');

    // 用户管理
    $router->get('user/recharge/{id}', 'UserController@recharge')->name('user.recharge');
    $router->post('user/recharge/{id}', 'UserController@rechargeUpdate')->name('user.recharge.update');
    $router->delete('user/relation/delete/{id}', 'UserController@deleteRelation')->name('user.relation.delete');
    $router->get('user/{id}/child', 'UserController@child')->name('user.child');
    $router->get('user/balance-logs/{id}', 'UserController@lanceLogs')->name('user.balance.logs');
    $router->resource('user', 'UserController');

    // 名片导入
    $router->resource('imports', 'ImportsController');

    // 关于我们
    $router->resource('about', 'AboutController')->only(['edit', 'update']);

    // 供需管理
    $router->resource('supplys', 'SupplyController');
    // 供需分类
    $router->resource('sd-types', 'SdTypeController');

    // 行业管理
    $router->resource('industries', 'IndustryController')->except(['show']);

    // 活动推荐
    $router->post('check-recommend', 'Activity\ActivityController@checkRecommend')->name('admin.activity.check-recommend');

    // 财务管理
    // 会员开通记录
    $router->get('company-card-log', 'CompanyCardLogController@index');

    // 平台收益
    $router->get('platform_incomes', 'PlatformIncomeController@index');

    // 行业联动
    $router->get('companys/industrys', 'CompanyCardController@getIndustry')->name('admin.industry.get');

    // 公司名片
    $router->resource('companys', 'CompanyCardController');

    // 申请退款
    $router->resource('refunds', 'Activity\RefundController');

    // 协会部门管理
    $router->resource('union-department', 'UnionDepartmentController');

    // 商品管理
    $router->resource('goods', 'GoodsController');
    // 购买记录
    $router->resource('goods-order', 'GoodsOrderController');

    // 会员认证
    $router->resource('memberships', 'MembershipController');
});

/**
 * php artisan admin:make ProductSkuCategoryController --model=App\Models\Product\ProductSkuCategory
 * php artisan admin:make OfflineOrderController --model=App\Models\Order
 * php artisan admin:make RewardController --model=App\Models\Order
 */
