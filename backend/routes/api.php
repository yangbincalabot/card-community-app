<?php

use App\Models\User\Attention;

\Overtrue\LaravelUploader\LaravelUploader::routes();

Route::namespace('Api\Passport')->prefix('passport')->group(function () {
    Route::post('login', 'LoginController@login')->name('api.passport.login');

    // 根据code查询openid及用户信息
    Route::post('code-get-info', 'LoginController@codeGetInfo')->name('api.passport.code_get_info');
});

Route::namespace('Api')->group(function () {
    Route::group(['prefix' => 'activity'], function(){
        // 活动详情
        Route::post('activity-detail', 'Activity\ActivityController@show')->name('api.activity.detail');
        // 活动详情页需要的内容接口
        Route::post('activity-big-detail', 'Activity\ActivityController@bigDetail')->name('api.activity.big_detail');
        // 获取活动分类
        Route::post('activity-get-type', 'Activity\ActivityController@getType')->name('api.activity.get_type');
        // 活动列表
        Route::post('activity-get-all-list', 'Activity\ActivityController@getAllList')->name('api.activity.get_all_list');

        // 获取该用户活动报名状态
        Route::post('activity-apply-status', 'Activity\ActivityController@getApplyStatus')->name('api.activity.get_apply_status');
    });

    // 关于我们
    Route::get('about', 'AboutController@getDetail')->name('api.about');
    // 获取配置
    Route::group(['prefix' => 'configure'], function(){
        // 获取协会名称
        Route::get('get-society-name', 'ConfigureController@getSocietyName')->name('api.configure.society_name');
        // 获取开通企业会员费用
        Route::get('get-business-cost', 'ConfigureController@getBusinessCost')->name('api.configure.business_cost');
        // 获取小程序是否处于审核状态
        Route::get('get-is-audit', 'ConfigureController@getIsAudit')->name('api.configure.is_audit');
        // 获取腾讯地图秘钥
        Route::get('get-map-key', 'ConfigureController@getMapApiKey')->name('api.configure.map_key');
        // 是否开启短信
        Route::get('get-sms-switch', 'ConfigureController@getSmsSwitch')->name('api.configure.sms_switch');
    });


    Route::get('index/index', 'IndexController@index')->name('api.index.index'); // 首页
    Route::get('communal/list', 'CommunalController@getCommunals')->name('api.communal.list'); // 公告列表
    Route::get('communal/detail', 'CommunalController@getCommunalDetail')->name('api.communal.detail'); // 公告详情
    Route::get('advert-get', 'AdvertController@getAdver')->name('api.advert.get'); // 获取广告
    Route::get('banner-get', 'BannerController@getBanner')->name('api.banner.get'); // 获取banner
    Route::get('check-login', 'IndexController@checkLogin')->name('api.check-login'); // 检查是否登陆

    // 公司列表
    Route::get('company/list', 'CompanyController@index')->name('api.company.list');
    //  我的公司
    Route::get('company/detail', 'CompanyController@detail')->name('api.user.company.detail');
    // 关联我的公司员工发布的供需
    Route::get('company/supplies', 'CompanyController@companySupply')->name('api.user.company.supplies');



    // 获取收藏状态
    Route::post('get-collection-status', 'User\CollectionController@show')->name('api.collection.show');
    // 获取用户关注状态
    Route::post('get-attention-status', 'User\AttentionController@show')->name('api.attention.show');

    // 获取点赞状态
    Route::post('get-like-status', 'LikeController@show')->name('api.like.show');

    // 获取供需分类
    Route::post('supply/supply-get-type', 'SupplyController@getType')->name('api.supply.get_type');
    // 获取供需列表页
    Route::post('supply/list', 'SupplyController@getList')->name('api.supply.list');
    // 获取详情
    Route::post('supply/big-detail', 'SupplyController@getDetail')->name('api.supply.big_detail');

    // 获取行业
    Route::get('industries', 'IndustryController@index')->name('api.industries');
    // 名片广场
    Route::match(['get', 'post'], 'card-square', 'CardSquareController@index')->name('api.card_square');

    // 协会广场
    Route::match(['get', 'post'], 'society-square', 'SocietySquareController@index')->name('api.society_square');

    Route::get('society-square-companies', 'Business\AssociationsController@companies')->name('api.society_square_companies');

    // 协会详情(旧)
    Route::get('society-detail', 'SocietySquareController@detail')->name('api.society_detail');

    // 协会详情(新)
    Route::get('society-details', 'SocietySquareController@details')->name('api.society_details');

    // 名片公开情况
    Route::post('card-open', 'CardSquareController@checkCollectionCard')->name('api.card_open');

    // 获取省份
    Route::get('provinces', 'AreaController@provinces')->name('api.provinces');

    // 获取省市区
    Route::get('areas', 'AreaController@areas')->name('api.areas');

    // 首页相关内容提示数量
    Route::get('get-nav-data', 'IndexController@getNavData')->name('api.get_nav_data');

    // 名片预约
    Route::post('reserve-list', 'ReserveController@reserveList')->name('api.reserve.reserve_list');

    Route::group(['namespace' => 'Card'], function() {
        // 商家商品列表
        Route::get('goods-list', 'GoodsController@list')->name('api.goods-list');
        Route::get('goods-show', 'GoodsController@show')->name('api.goods-show');
    });

});



Route::namespace('Api')->middleware('auth:api')->group(function () {
    // 加入购物车
    Route::post('cart/add', 'CartController@store')->name('api.cart.store');
    // 购物车列表
    Route::post('cart/index', 'CartController@index')->name('api.cart.index');
    // 删除购物车数据
    Route::post('cart/remove', 'CartController@remove')->name('api.cart.remove');
    // 减少购物车商品数量
    Route::post('cart/decrease', 'CartController@cartItemDecrement')->name('api.cart.decrease');
    // 购物车结算
    Route::post('settlement/index', 'SettlementController@index')->name('api.settlement.index');
    Route::post('settlement/buy-now', 'SettlementController@buyNow')->name('api.settlement.buy_now');

    // 优惠券
    Route::post('coupons/get', 'CouponsController@getCoupons')->name('api.coupons.get');
    Route::post('coupons/index', 'CouponsController@index')->name('api.coupons.index');

    // SettlementController
    // 订单列表
    Route::post('order/index', 'OrderController@index')->name('api.order.index');
    // 退款订单列表
    Route::post('order/refund-index', 'OrderController@refundOrders')->name('api.order.refund_index');
    // 提交订单
    Route::post('order/create', 'OrderController@store')->name('api.order.store');
    // 改变订单状态
    Route::post('order/status-change', 'OrderController@store')->name('api.order.store');
    Route::post('order/show', 'OrderController@show')->name('api.order.show');
    Route::post('order/received', 'OrderController@received')->name('api.order.received');
    Route::post('order/refund-apply', 'OrderController@applyRefund')->name('api.order.refund_apply');

    // 选择支付方式时查询订单信息
    Route::post('order/detail-by-pay-type', 'OrderController@orderDetailBySelectedPayType')->name('api.order.detail_by_pay_type');
    // 选择线下支付时查询订单信息
    Route::post('order/detail-by-offline', 'OrderController@orderDetailByOffline')->name('api.order.detail_by_offline');
    Route::post('order/offline-remind', 'OrderController@confirmPaid')->name('api.order.offline_remind');
    Route::post('order/detail-by-success-pay', 'OrderController@orderDetailBySuccessPay')->name('api.order.detail_by_success_pay');

    Route::post('order/express', 'OrderController@express')->name('api.order.express');
    Route::post('order/express-type', 'OrderController@expressType')->name('api.order.express_type');

    // 支付
    Route::post('pay/offline-confirm-pay', 'PayController@offlineConfirmPay')->name('api.order.offline_confirm_pay');
    Route::post('pay/wechat-mini-pay', 'PayController@weChatMiNiPay')->name('api.order.wechat_mini_pay');

    // 发送短信
    Route::post('code/sms', 'SendCodeController@smsCode')->name('api.code.sms');

    // 名片详情
    Route::get('card-detail', 'CardSquareController@detail')->name('api.card_detail');
    // 获取收到的名片
    Route::get('get-offline-card', 'CardSquareController@getOfflineBusinessCard')->name('api.get_offline_card');
    // 批量修改标签
    Route::post('change-tags', 'CardSquareController@changeTags')->name('api.change-tags');

    // 定制搜索
    Route::get('get-custom-search', 'User\UserScreenController@index')->name('api.custom.search');
    Route::post('store-custom-search', 'User\UserScreenController@store')->name('api.custom.store');

    // 申请加入协会
    Route::post('add-society', 'User\ApplicationSocietyController@applicationSociety')->name('api.application.society');

    // 检查加入状态
    Route::post('check-society', 'User\ApplicationSocietyController@applicationCheck')->name('api.application.check');

    // 余额支付协会费用
    Route::post('application-balance', 'User\ApplicationSocietyController@balancePay')->name('api.application.balance');
    // 微信支付协会费用
    Route::post('application-wechat', 'User\ApplicationSocietyController@wechatPay')->name('api.application.wechat');


    // 名片
    Route::group(['namespace' => 'Card'], function (){
        Route::group(['prefix' => 'card'], function(){
            // 扫描小程序名片码
            Route::post('scan-code', 'CardCodeController@scanCode')->name('api.card.scan_code');
            // 名片夹主业收藏的名片
            Route::post('index', 'IndexController@index')->name('api.card.index');
            // 名片夹主业其它信息
            Route::post('other-detail', 'IndexController@other')->name('api.card.other');
            // 名片夹协会成员列表
            Route::post('society-list', 'IndexController@societyList')->name('api.card.society_list');
            // 名片夹公司成员列表
            Route::post('company-list', 'IndexController@companyList')->name('api.card.company_list');
            // 收到的名片列表
            Route::get('receives', 'ReceiveCarteController@index')->name('api.card.receive');
            Route::post('receive-detail', "ReceiveCarteController@detail")->name('api.card.receive_detail');
            Route::post('receives-tag', 'ReceiveCarteController@changeTag')->name('api.card.receive_tag');
            Route::post('receive-status', 'ReceiveCarteController@checkReceiveStatus')->name('api.card.receive_status');
            // 同意或拒绝收到的名片
            Route::post('by-adding', 'ReceiveCarteController@byAdding')->name('api.card.by_adding');

            // 传递名片
            Route::post('send-card', 'SendCardController@send')->name('api.card.send');

            Route::post('wechat-pay', 'PaymentController@createCompanyCardCharge')->name('api.company_card.wechat_pay'); // 微信支付

            Route::post('scan-card', 'CardCodeController@scanCard')->name('api.scan.card'); // 扫描名片
            Route::post('scan-card-save', 'CardCodeController@scanCardSave')->name('api.scan.save'); // 扫描名片添加默认名片信息

            Route::post('create-card', 'CreateCardController@create')->name('api.create_carte.card'); // 手动添加名片
            Route::get('create-card-info', 'CreateCardController@info')->name('api.create_carte.info'); // 手动创建名片的信息

            // 个人收藏列表 * 特别关注
            Route::post('special-list', 'IndexController@specialList')->name('api.card.special_list');
            // 联系过列表
            Route::post('contact-list', 'IndexController@contactList')->name('api.card.contact_list');
            // 通话过列表
            Route::post('talk-list', 'IndexController@talkList')->name('api.card.talk_list');

	    // 收藏列表
            Route::get('getCollection', 'IndexController@getCollection')->name('api.card.collection.list');
        });



        // 商家商品列表
        Route::post('goods-order', 'GoodsController@order')->name('api.goods-order');

    });


    // 活动模块
    Route::group(['prefix' => 'activity'], function(){
        // 创建活动回顾
        Route::post('activity-review-create', 'Activity\ActivityReviewController@create')->name('api.activity_review.create');
        // 更新活动回顾
        Route::post('activity-review-update', 'Activity\ActivityReviewController@update')->name('api.activity_review.update');
        // 我的活动回顾列表
        Route::post('activity-review-my-list', 'Activity\ActivityReviewController@myList')->name('api.activity_review.my_list');
        // 删除活动回顾
        Route::post('activity-review-delete', 'Activity\ActivityReviewController@destroy')->name('api.activity_review.delete');


        // 我的创建的活动列表
        Route::post('activity-my-list', 'Activity\ActivityController@myList')->name('api.activity.my_list');
        // 我加入的活动列表
        Route::post('activity-join-list', 'Activity\ActivityController@joinList')->name('api.activity.join_list');
        // 创建活动
        Route::post('activity-create', 'Activity\ActivityController@create')->name('api.activity.create');
        // 更新活动
        Route::post('activity-update', 'Activity\ActivityController@update')->name('api.activity.update');
        // 删除活动
        Route::post('activity-delete', 'Activity\ActivityController@destroy')->name('api.activity.destroy');
        // 改变上下架状态
        Route::post('activity-change-shelves', 'Activity\ActivityController@changeShelves')->name('api.activity.change_shelves');
        // 报名名单
        Route::post('activity-apply-list', 'Activity\ActivityController@applyList')->name('api.activity.apply_list');


        // 创建报名
        Route::post('apply-create', 'Activity\ActivityApplyController@create')->name('api.activity_apply.apply_create');
        // 我的预约报名
        Route::post('apply-my-list', 'Activity\ActivityApplyController@myList')->name('api.activity_apply.my_list');
        // 预约报名详情
        Route::post('apply-detail', 'Activity\ActivityApplyController@show')->name('api.activity_apply.show');
        // 名单详情
        Route::post('apply-list-details', 'Activity\ActivityApplyController@listDetails')->name('api.activity_apply.list_details');
        // 报名退款
        Route::post('apply-refund', 'Activity\ActivityApplyController@applyRefund')->name('api.activity_apply.apply_refund');

        // 获取报名信息进行报名
        Route::post('apply-big-detail', 'Activity\ActivityApplyController@getBigDetail')->name('api.activity_apply.get_big_detail');
        // 报名订单信息
        Route::post('apply-order-detail', 'Activity\ActivityApplyController@orderDetail')->name('api.activity_apply.order_detail');

        // 活动微信支付
        Route::post('apply-wechat-pay', 'Activity\PaymentController@wechatPay')->name('api.apply.wechat_pay'); // 活动微信支付
        // 活动余额支付
        Route::post('apply-balance-pay', 'Activity\PaymentController@balancePay')->name('api.apply.balance_pay'); // 活动余额支付

        // 免费活动报名
        Route::post('apply-free-apply', 'Activity\ActivityApplyController@freeApply')->name('api.apply.free_apply');

        // 用户主动取消订单
        Route::post('apply-cancel-order', 'Activity\ActivityApplyController@cancelOrder')->name('api.apply.cancel_order');
    });

    // 关注相关操作
    Route::group(['prefix' => 'attention'], function(){
        // 用户关注
        Route::post('store', 'User\AttentionController@store')->name('api.attention.store');
        // 选择人员
        Route::post('choose', 'User\AttentionController@choose')->name('api.attention.choose');
        // 获取选中的承办人
        Route::post('undertake-data', 'User\AttentionController@getUndertakeData')->name('api.attention.undertake_data');
        // 设置星级
        Route::post('set-stars', 'User\AttentionController@setStars')->name('api.attention.set_stars');

        // 设置/取消特别关注
        Route::post('set-special', 'User\AttentionController@setSpecial')->name('api.attention.set_special');
        // 设置联系过
        Route::post('set-contact', 'User\AttentionController@setContact')->name('api.attention.set_contact');
        // 设置通话过
        Route::post('set-talk', 'User\AttentionController@setTalk')->name('api.attention.set_talk');
    });

    // 收藏相关操作
    Route::post('collection', 'User\CollectionController@store')->name('api.collection.store');
    // 我的供需收藏列表
    Route::post('supply-collection-list', 'User\CollectionController@getSupplyList')->name('api.collection.get_supply_list');
    // 我的活动收藏列表
    Route::post('activity-collection-list', 'User\CollectionController@getActivityList')->name('api.collection.get_activity_list');

    // 点赞相关操作
    Route::post('like', 'LikeController@store')->name('api.like.store');

    // 银行卡列表
    Route::get('banks', 'BankController@index')->name('api.banks');

    // 代理列表
    Route::get('agents', 'AgentController@getAgents')->name('api.agent.index');



    // 代理中心
    Route::get('agent/info', 'AgentController@getAgentInfo')->name('api.agent.info');
    // 下级代理
    Route::get('agent/list', 'AgentController@getMyLowers')->name('api.agent.lowers');
    // 下级代理详情
    Route::get('agent/detail', 'AgentController@getLowerDetail')->name('api.agent.detail');
    Route::get('agent/detail/log', 'AgentController@getLowerDetailLog')->name('api.agent.detail.log');

    // 用户门店详情
    Route::get('user-store-detail', 'StoreController@getUserStoreDetail')->name('api.user-store.detail');
    // 门店联系信息修改
    Route::post('user-store-update', 'StoreController@updateStore')->name('api.user-store.update');

    // 验证身份证
    Route::get('check-id-card', 'IdCardController@checkIdCard')->name('api.check.id-card');


    // 活动规格
    Route::group(['prefix' => 'specification'], function(){
        // 添加
        Route::post('add', 'Activity\SpecificationController@create')->name('api.specification.create');
        // 更新
        Route::post('edit', 'Activity\SpecificationController@update')->name('api.specification.update');
        // 查询
        Route::post('detail', 'Activity\SpecificationController@show')->name('api.specification.show');
        // 删除
        Route::post('delete', 'Activity\SpecificationController@delete')->name('api.specification.delete');
        // 活动创建时，多个规格展示
        Route::post('get-list', 'Activity\SpecificationController@getList')->name('api.specification.get_list');
    });

    // 会务议程
    Route::group(['prefix' => 'agenda'], function(){
        // 添加
        Route::post('add', 'Activity\AgendaController@create')->name('api.agenda.create');
        // 更新
        Route::post('edit', 'Activity\AgendaController@update')->name('api.agenda.update');
        // 查询
        Route::post('detail', 'Activity\AgendaController@show')->name('api.agenda.show');
        // 删除
        Route::post('delete', 'Activity\AgendaController@delete')->name('api.agenda.delete');
        // 活动创建时，多个议程展示
        Route::post('get-list', 'Activity\AgendaController@getList')->name('api.agenda.get_list');
    });

    // 会后花絮
    Route::group(['prefix' => 'tricks'], function(){
        // 添加
        Route::post('add', 'Activity\TricksController@create')->name('api.tricks.create');
        // 更新
        Route::post('edit', 'Activity\TricksController@update')->name('api.tricks.update');
        // 查询
        Route::post('detail', 'Activity\TricksController@show')->name('api.tricks.show');
        // 删除
        Route::post('delete', 'Activity\TricksController@delete')->name('api.tricks.delete');
    });

    // 供需
    Route::group(['prefix' => 'supply'], function(){
        // 添加
        Route::post('add', 'SupplyController@create')->name('api.supply.create');
        // 更新
        Route::post('edit', 'SupplyController@update')->name('api.supply.update');
        // 查询
        Route::post('detail', 'SupplyController@show')->name('api.supply.show');
        // 删除
        Route::post('delete', 'SupplyController@delete')->name('api.supply.delete');
        // 获取自己发布的供需列表页
        Route::post('my-list', 'SupplyController@getMyList')->name('api.supply.my_list');
    });

    // 群组
    Route::group(['prefix' => 'group'], function(){
        // 创建&&更新
        Route::post('create', 'User\AttentionController@groupCreate')->name('api.attention.group_create');
        // 删除
        Route::post('delete', 'User\AttentionController@groupDelete')->name('api.attention.group_delete');
        // 群组主业列表
        Route::post('list', 'User\AttentionController@groupList')->name('api.attention.group_list');
        // 群组详情列表
        Route::post('detail-list', 'User\AttentionController@groupDetailList')->name('api.attention.group_detail_list');
        // 群组名片展示列表
        Route::post('carte-list', 'User\AttentionController@groupCarteList')->name('api.attention.group_carte_list');
        // 群组详情
        Route::post('show', 'User\AttentionController@groupShow')->name('api.attention.group_show');
        // 将群成员移除群组
        Route::post('remove', 'User\AttentionController@groupRemove')->name('api.attention.group_remove');
        // 临时缓存群组列表
        Route::post('temporary-list', 'User\AttentionController@temporaryList')->name('api.attention.temporary_list');
    });

    Route::group(['namespace' => 'User', 'prefix' => 'user'], function (){
        // 银行卡模块
        Route::group(['prefix' => 'bank'], function(){
            // 列表
            Route::get('index', 'UserBankController@index')->name('api.bank.index');
            // 添加
            Route::post('add', 'UserBankController@add')->name('api.bank.add');
            // 详情
            Route::get('detail', 'UserBankController@detail')->name('api.bank.detail');
            // 删除
            Route::delete('delete', 'UserBankController@delete')->name('api.bank.delete');
            // 编辑
            Route::put('update', 'UserBankController@update')->name('api.bank.update');
        });

        // 用户设置
        Route::group(['prefix' => 'setting'], function (){
            Route::put('cash-password', 'UserSettingController@setCashPassword')->name('set.cash_password');
        });

        // 个人中心企业名片
        Route::group(['prefix' => 'company-card'], function(){
            Route::get('info', 'CompanyCardController@getCompanyCardInfo')->name('api.company_card.info'); // 获取企业名片信息
            Route::put('update', 'CompanyCardController@updateCompanyCard')->name('api.company_card.update'); // 编辑企业名片
            Route::get('binds', 'CompanyBindController@list')->name('api.company_card.binds'); // 企业绑定申请记录
            Route::put('bind-operate', 'CompanyBindController@bindOperate')->name('api.company_card.operate'); // 审核操作
            Route::get('bind-check', 'CompanyBindController@checkUserBind')->name('api.company_card.check'); // 检查用户最后绑定情况
        });

        // 个人名片
        Route::group(['prefix' => 'carte'], function (){
            Route::get('info', 'CarteController@getCarteInfo')->name('api.carte.info'); // 获取名片信息
            Route::put('update', 'CarteController@updateCarte')->name('api.carte.update'); // 编辑名片
        });

        // 部门
        Route::group(['prefix' => 'department'], function (){
            // 获取所有部门
            Route::get('index', 'DepartmentController@index')->name('api.department.index');

            // 添加部门
            Route::post('store', 'DepartmentController@store')->name('api.department.store');
            // 绑定
            Route::post('bind', 'DepartmentController@bind')->name('api.department.bind');
            // 部门列表，带分页
            Route::get('list', 'DepartmentController@list')->name('api.department.list');
            Route::get('detail', 'DepartmentController@detail')->name('api.department.detail');
            Route::patch('update', 'DepartmentController@update')->name('api.department.update');
            Route::delete('delete', 'DepartmentController@delete')->name('api.department.delete');
            // 查看绑定部门信息
            Route::get('bindOff', 'DepartmentController@bindOff')->name('api.department.bind-off');
        });

        //  我的公司
       // Route::get('company/index', 'UserCompanyController@index')->name('api.user.company.index');
        // 关联我的公司员工发布的供需
       // Route::get('company/supplies', 'UserCompanyController@companySupply')->name('api.user.company.supplies');

        // 谁看过我
        Route::get('visited', 'UserVisitedController@index')->name('api.user.visited');







        // 添加收货地址
        Route::post('address/add', 'UserAddressController@add')->name('api.user-address.add');
        // 收货地址列表
        Route::get('address/index', 'UserAddressController@index')->name('api.user-address.index');
        // 修改收货地址
        Route::post('address/update', 'UserAddressController@update')->name('api.user-address.update');

        // 地址详情
        Route::get('address/show', 'UserAddressController@show')->name('api.user-address.edit');
        // 删除地址
        Route::post('address/delete', 'UserAddressController@delete')->name('api.user-address.delete');

        // 会员资金
        Route::get('balance', 'UserController@balance')->name('api.user.balance');

        // 申请提现
        Route::post('withdraw/add', 'WithdrawController@add')->name('api.user.withdraw.add');

        // 提现列表
        Route::get('withdraw/index', 'WithdrawController@index')->name('api.user.withdraw.index');

        // 会员中心
        Route::get('index', 'UserController@index');

        Route::get('other-carte', 'UserController@createCarteInfo')->name('api.other.carte');
        Route::get('my-recommend', 'UserController@myRecommend')->name('user.my-recommend');

        // 代理申请
        Route::post('apply-agent-add', 'UserApplyAgentController@add')->name('api.apply-agent.add');

        Route::get('apply-agent-check', 'UserApplyAgentController@checkHasStay')->name('api.apply-agent.check');


        // 用户关系绑定
        Route::post('user-relation-store', 'UserRelationController@store')->name('api.user-relation.store');

        // 账户详细概览
        Route::get('account/detail', 'UserAccountController@detail')->name('api.user-account.detail');

        //  账户流水
        Route::get('account/logs', 'UserAccountController@logs')->name('api.user-account.logs');

        // 授权手机号
        Route::post('get-phone', 'UserController@getPhone')->name('api.user.get_phone');

        // 名片码
        Route::post('get-qrcode', 'UserController@getQrcode')->name('api.user.get_qrcode');


        // 用户订单
        Route::get('orders', 'OrderController@index')->name('api.user.orders');

        // 获取当前用户的用户列表
        Route::get('user-list', 'UserController@getUserList')->name('api.user.user_list');

        // 切换用户
        Route::get('change-user', 'UserController@changeUser')->name('api.user.change_user');

        // 添加新用户
        Route::put('add-user-carte', 'UserController@addUserCarte')->name('api.user.add_user_carte');

        // 只获取手机号
        Route::post('get-only-phone', 'UserController@getOnlyPhone')->name('api.user.get_only_phone');

        // 浏览协会足迹
        Route::post('footprint', 'UserController@footPrint')->name('api.user.footprint');
        Route::get('footprint-list', 'UserController@footPrintList')->name('api.user.footprint.list');
    });

    // 活动提醒订阅通知
    Route::post('send-activity-sub-msg', 'SubNewsController@sendActivitySubMsg')->name('api.sub-news.send_activity_sub_msg');
    // 完善信息订阅通知
    Route::post('send-perfect-sub-msg', 'SubNewsController@sendPerfectSubMsg')->name('api.sub-news.send_perfect_sub_msg');

    // 根据id查看名片信息
    Route::post('get-carte-news', 'User\CarteController@getCarteNews')->name('api.carte.get_carte_news');

    // 名片预约
    Route::post('reserve-store', 'ReserveController@store')->name('api.reserve.reserve_store');

    // 分享名片增加一次被分享次数
    Route::post('add-carte-share-num', 'User\CarteController@addShareNum')->name('api.carte.add_share_num');

    // 名片页统计
    Route::post('carte-statistical', 'User\CarteController@carteStatistical')->name('api.carte.carte_statistical');

    // 重置新增访客数
    Route::post('reset-new-visits', 'User\CarteController@resetNewVisits')->name('api.carte.reset_new_visits');


    // 商家
    Route::group(['middleware' => 'business'], function() {

        // 商品管理
        Route::group(['prefix' => 'business', 'namespace' => 'Business'], function (){
            Route::get('goods-index', 'GoodsController@index')->name('goods.index');
            Route::post('goods-add', 'GoodsController@add')->name('goods.add');
            Route::post('goods-update', 'GoodsController@update')->name('goods.update');
            Route::post('goods-delete', 'GoodsController@delete')->name('goods.delete');
            Route::get('goods-show', 'GoodsController@show')->name('goods.show');
            Route::get('order-list', 'OrderController@index')->name('order.index');
            Route::get('wallet-index', 'WalletController@index')->name('wallet.index');
            Route::get('wallet-detail', 'WalletController@detail')->name('wallet.detail');
            Route::get('user-index', 'UserController@index')->name('business.user.index');
        });

        // 商家协会管理
        Route::group(['prefix' => 'association', 'namespace' => 'Business'], function (){
            Route::get('index', 'AssociationsController@index')->name('api.association.index');
            Route::post('create', 'AssociationsController@create')->name('api.association.create');
            Route::get('show', 'AssociationsController@show')->name('api.association.show');
            Route::post('update', 'AssociationsController@update')->name('api.association.update');
            Route::post('delete', 'AssociationsController@delete')->name('api.association.delete');
            Route::get('application', 'AssociationsController@application')->name('api.application.list');
            Route::post('application', 'AssociationsController@verify');
            Route::get('select-association', 'AssociationsController@selectAssociation');
            Route::get('sub-audit', 'AssociationsController@subAudit');
            Route::post('sub-audit-verify', 'AssociationsController@subAuditVerify');

            Route::get('info', 'AssociationsController@info'); // 协会信息
        });

        // 协会角色及公司管理
        Route::group(['prefix' => 'role'], function() {
            // 添加/更新公司角色
            Route::post('store', 'CompanyRoleController@store')->name('role.store');
            // 角色列表(个人中心)
            Route::get('role-list', 'CompanyRoleController@roleList')->name('role.role_list');
            // 角色公司列表
            Route::get('role-company', 'CompanyRoleController@roleCompany')->name('role.role_list');
            // 未选中的公司
            Route::get('no-selectd-company', 'CompanyRoleController@noSelectdCompany')->name('role.no_selectd_company');
            // 协会角色调整排序
            Route::post('role-adjust-sort', 'CompanyRoleController@roleAdjustSort')->name('role.role_adjust_sort');
            // 协会公司顺序调整
            Route::post('company-adjust-sort', 'CompanyRoleController@companyAdjustSort')->name('role.company_adjust_sort');
            // 给某个公司添加某个角色
            Route::post('add-company-role', 'CompanyRoleController@addCompanyRole')->name('role.add_company_role');
            // 删除某个公司对应的角色
            Route::post('del-company-role', 'CompanyRoleController@delCompanyRole')->name('role.del_company_role');
        });


    });



    // 发送彩信
    Route::get('send-mms', 'MmsController@sendMms')->name('api.mms.send_mms');
    // 发送短信通知
    Route::get('send-notice', 'SendNoticeController@sendNotice')->name('api.mms.send_notice');


    // 提交会员认证
    Route::post('membership-post', 'User\MembershipController@post')->name('api.membership.post');
});
