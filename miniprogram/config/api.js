const ApiRootUrl = 'https://yf.youfun.shop/api/';
const DomainRootUrl = 'https://yf.youfun.shop/';
// const ApiRootUrl = 'http://172.23.87.244:8112/api/';
// const DomainRootUrl = 'http://172.23.87.244:8112/';
// const ApiRootUrl = 'https://frps.qiangxk.com/api/';
// const DomainRootUrl = 'https://frps.qiangxk.com/';
// const ApiRootUrl = 'http://mingpian.test/api/';
// const DomainRootUrl = 'http://mingpian.test/';

module.exports = {
    ResourceRootUrl: DomainRootUrl, //首页数据接口
    LoginUrl: ApiRootUrl + 'passport/login', // 微信登录
    CodeGetInfoUrl: ApiRootUrl + 'passport/code-get-info', // 微信登录
    IndexUrl: ApiRootUrl + 'index/index', // 首页数据接口
    FilesUpload: ApiRootUrl + 'files/upload', // 文件上传
    ProductIndexUrl: ApiRootUrl + 'product', // 商品首页数据接口
    ProductDetailUrl: ApiRootUrl + 'product/show', // 商品详情数据接口

    CartAddUrl: ApiRootUrl + 'cart/add', // 加入购物车
    CartIndexUrl: ApiRootUrl + 'cart/index', // 购物车列表
    CartRemoveUrl: ApiRootUrl + 'cart/remove', // 购物车列表删除
    CartItemDecrementUrl: ApiRootUrl + 'cart/decrease', // 购物车列表减一

    SettlementUrl: ApiRootUrl + 'settlement/index', // 结算
    SettlementNowBuyUrl: ApiRootUrl + 'settlement/buy-now', // 结算


    OrderListsUrl: ApiRootUrl + 'order/index', // 订单列表
    OrderRefundListsUrl: ApiRootUrl + 'order/refund-index', // 提交订单
    OrderCreateUrl: ApiRootUrl + 'order/create', // 提交订单
    OrderDetailUrl: ApiRootUrl + 'order/show', // 订单详情

    OrderPayTypeDetailUrl: ApiRootUrl + 'order/detail-by-pay-type', // 选择支付方式
    OrderOfflineDetailUrl: ApiRootUrl + 'order/detail-by-offline', // 线下支付获取订单详情
    OrderOfflineRemindUrl: ApiRootUrl + 'order/offline-remind', // 线下支付通知平台
    OrderSuccessPayDetailUrl: ApiRootUrl + 'order/detail-by-success-pay', // 线下支付通知平台

    OrderStatusChangeUrl: ApiRootUrl + 'order/status-change', // 订单详情
    OrderReceivedChangeUrl: ApiRootUrl + 'order/received', // 确认收货

    OrderApplyRefundChangeUrl: ApiRootUrl + 'order/refund-apply', // 确认收货
    OrderExpressUrl: ApiRootUrl + 'order/express', // 物流查询
    OrderExpressTypeUrl: ApiRootUrl + 'order/express-type', // 物流查询


    WeChatMiNiPayUrl: ApiRootUrl + 'pay/wechat-mini-pay', // 小程序支付
    ApplyWechatPayUrl: ApiRootUrl + 'activity/apply-wechat-pay', // 活动报名小程序支付
    ApplyBalancePayUrl: ApiRootUrl + 'activity/apply-balance-pay', // 活动报名余额支付
    CompanyCardMiNiPayUrl: ApiRootUrl + 'card/wechat-pay', // 企业会员小程序支付,


    freeApplyUrl: ApiRootUrl + 'activity/apply-free-apply', // 免费报名


    CommunalListUrl: ApiRootUrl + 'communal/list', // 公告列表
    CommunalDetailUrl: ApiRootUrl + 'communal/detail', // 公告列表
    CheckLoginUrl: ApiRootUrl + 'check-login', // 检查是否登陆

    AdvertGetUrl: ApiRootUrl + 'advert-get', // 获取指定位置的广告
    BannerGetUrl: ApiRootUrl + 'banner-get', // 获取指定类型的banner
    BanksUrl: ApiRootUrl + 'banks', // 获取银行卡列表
    BankAddUrl: ApiRootUrl + 'user/bank/add', // 添加银行卡
    UserBankUrl: ApiRootUrl + 'user/bank/index', // 用户银行卡列表
    BankDetailUrl: ApiRootUrl + 'user/bank/detail', // 用户银行卡信息
    BankDeleteUrl: ApiRootUrl + 'user/bank/delete',  // 用户银行卡删除
    BankUpdateUrl: ApiRootUrl + 'user/bank/update', // 用户银行卡修改


    UserAddressAddUrl: ApiRootUrl + 'user/address/add', // 添加收货地址
    UserAddressListUrl: ApiRootUrl + 'user/address/index', // 收货地址列表
    UserAddressUpdateUrl: ApiRootUrl + 'user/address/update', // 编辑收货地址
    UserAddressInfoUrl: ApiRootUrl + 'user/address/show', // 收货地址信息
    UserAddressDeleteUrl: ApiRootUrl + 'user/address/delete', // 删除收货地址

    GetUserListUrl: ApiRootUrl + 'user/user-list', // 获取当前用户的用户列表
    ChangeUserUrl: ApiRootUrl + 'user/change-user', // 切换用户
    AddUserCarteUrl: ApiRootUrl + 'user/add-user-carte', // 添加新用户
    GetOnlyPhoneUrl: ApiRootUrl + 'user/get-only-phone', // 只获取手机号

    UserBalanceUrl: ApiRootUrl + 'user/balance', // 钱包
    CollectionUrl: ApiRootUrl + 'collection', // 收藏
    CollectionGetStatusUrl: ApiRootUrl + 'get-collection-status',// 获取收藏状态
    CollectionSupplyListUrl: ApiRootUrl + 'supply-collection-list',// 获取供需收藏列表
    CollectionActivityListUrl: ApiRootUrl + 'activity-collection-list',// 获取活动收藏列表

    AttentionStoreUrl: ApiRootUrl + 'attention/store', // 关注
    GetAttentionStatusUrl: ApiRootUrl + 'get-attention-status',// 获取关注状态

    UserWithdrawAddUrl: ApiRootUrl + 'user/withdraw/add', // 申请提现
    UserWithdrawListUrl: ApiRootUrl + 'user/withdraw/index', // 提现列表,
    UserIndexUrl: ApiRootUrl + 'user/index', // 会员中心
    UserRecomendUrl: ApiRootUrl + 'user/my-recommend', // 推荐概览
    GetCompanyListUrl: ApiRootUrl + 'company/list', // 公司列表
    GetCompanyDetailUrl: ApiRootUrl + 'company/detail', // 获取企业详情
    GetSocietyDetailUrl: ApiRootUrl + 'society-detail', // 获取协会详情(旧)
    ApplicationSocietyUrl: ApiRootUrl + 'add-society', // 申请加入协会
    GetSosietyDetailsUrl: ApiRootUrl + 'society-details', // 获取协会详情(新)
    ApplicationSocietyCheckUrl: ApiRootUrl + 'check-society', // 检查加入状态
    ApplicationBalanceUrl: ApiRootUrl + 'application-balance', // 余额支付协会费用
    ApplicationWechatUrl: ApiRootUrl + 'application-wechat', // 微信支付协会费用

    getCompanyDetailSuppliesUrl: ApiRootUrl + 'company/supplies', // 企业的供需列表
    getCardSquareListUrl: ApiRootUrl + 'card-square', // 名片广场
    getSocietyquareListUrl: ApiRootUrl + 'society-square', // 协会广场
    getSocietySquareCompanies: ApiRootUrl + 'society-square-companies', // 协会广场，会员单位
    getCardDetailUrl: ApiRootUrl + 'card-detail', // 名片详情
    GetOfflineCardUrl: ApiRootUrl + 'get-offline-card', // 获取收到的名片
    ChangeTagsUrl: ApiRootUrl + 'change-tags', // 批量修改标签

    MembershipPostUrl: ApiRootUrl + 'membership-post', // 提交会员认证

    GetCustomSearch: ApiRootUrl + 'get-custom-search', // 获取定制搜索
    StoreCustomSearch: ApiRootUrl + 'store-custom-search', // 保存搜索定制

    SendCardUrl: ApiRootUrl + 'card/send-card', // 传递名片
    CardOpenDetail: ApiRootUrl + 'card-open', // 名片联系情况（公开和不公开）
    GetVisitedMeUrl: ApiRootUrl + 'user/visited', // 谁看过我
    GetProvincesUrl: ApiRootUrl + 'provinces', // 获取所有省份
    GetAreasUrl: ApiRootUrl + 'areas', // 获取所有省市区
    ScanCardUrl: ApiRootUrl + 'card/scan-card', // 扫描名片
    ScanCardSaveUrl: ApiRootUrl + 'card/scan-card-save', // 扫描名片添加默认名片数据

    GetPhoneUrl: ApiRootUrl + 'user/get-phone', // 微信授权手机号

    GetSocietyNameUrl: ApiRootUrl + 'configure/get-society-name', // 获取协会名称
    GetBusinessCostUrl: ApiRootUrl + 'configure/get-business-cost', // 获取开通企业会员费用
    GetIsAuditUrl: ApiRootUrl + 'configure/get-is-audit', // 获取开通小程序是否处于审核状态
    GetMapKeyUrl: ApiRootUrl + 'configure/get-map-key', // 获取腾讯地图秘钥
    GetSmsSwitchUrl: ApiRootUrl + 'configure/get-sms-switch', // 获取短信状态
    SendSmsCode: ApiRootUrl + 'code/sms', // 发送验证码短信
    SetCashPasswordUrl: ApiRootUrl + 'user/setting/cash-password', // 设置支付密码

    GetIndustriesUrl: ApiRootUrl + 'industries', // 获取行业数据
    GetCompanyCardInfoUrl: ApiRootUrl + 'user/company-card/info', // 获取个人中心里企业名片详情
    UpdateCompanyCardUrl: ApiRootUrl + 'user/company-card/update', // 个人中心编辑企业名片
    GetCarteInfoUrl: ApiRootUrl + 'user/carte/info', // 个人中心里获取名片详情
    UpdateCarteUrl: ApiRootUrl + 'user/carte/update', // 个人中心编辑名片
    getUserCenterCompanyUrl: ApiRootUrl + 'user/company/index', // 个人中心我的公司
    getUserCenterCompanySuppliesUrl: ApiRootUrl + 'user/company/supplies', // 关联我的公司下员工的供需列表
    getCompanyBindsUrl: ApiRootUrl + 'user/company-card/binds', // 企业绑定申请记录
    CompanyBindOperateUrl: ApiRootUrl + 'user/company-card/bind-operate', // 绑定操作
    getUserLastCompanyBindUrl: ApiRootUrl + 'user/company-card/bind-check', // 用户最后绑定情况



    QrcodeGetUrl: ApiRootUrl + 'user/get-qrcode', // 获取名片码
    ResolveCodeUrl: ApiRootUrl + 'card/scan-code', // 解析名片码
    GetReceiveCardUrl: ApiRootUrl + 'card/receives', // 收到的名片列表
    GetReceiveCardDetailUrl: ApiRootUrl + 'card/receive-detail', // 收到的名片详情
    UpdateReceiveCarUrl: ApiRootUrl + 'card/receives-tag', // 修改标记内容
    CheckReceiveStatus: ApiRootUrl + 'card/receive-status', // 检查发送名片状态
    CreateOtherCarte: ApiRootUrl + 'card/create-card', // 手动创建名片
    GetOtherCarteInfo: ApiRootUrl + 'user/other-carte',
    GetCreateCarteInfo: ApiRootUrl + 'card/create-card-info', // 获取手动创建名片的信息
    ByAddingUrl: ApiRootUrl + 'card/by-adding', // 同意名片申请

    UserRelationStoreUrl: ApiRootUrl + 'user/user-relation-store', // 用户关系绑定
    AgentInfoUrl: ApiRootUrl + 'agent/info', // 代理中心
    AgentLowersUrl: ApiRootUrl + 'agent/list', // 我的下级列表
    AgentLowerDetail: ApiRootUrl + 'agent/detail', // 下级详情
    AgentLowerLog: ApiRootUrl + 'agent/detail/log', // 代理奖励流水
    UserStoreDetailUrl: ApiRootUrl + 'user-store-detail', // 代理门店详情
    UserStoreUpdateUrl: ApiRootUrl + 'user-store-update', //代理门店修改,
    UserAccountDetailUrl: ApiRootUrl + 'user/account/detail', // 账户概览
    UserAccountLogsUrl: ApiRootUrl + 'user/account/logs', // 账号流水
    AboutUrl: ApiRootUrl + 'about', // 关于我们
    CheckIdCardUrl: ApiRootUrl + 'check-id-card', // 身份证验证





    ActivityReviewCreate: ApiRootUrl + 'activity/activity-review-create', // 创建活动回顾
    ActivityReviewUpdate: ApiRootUrl + 'activity/activity-review-update', // 更新活动回顾
    ActivityReviewDetail: ApiRootUrl + 'activity/activity-review-detail', // 活动回顾详情
    ActivityReviewMyList: ApiRootUrl + 'activity/activity-review-my-list', // 我的活动回顾列表
    ActivityReviewDelete: ApiRootUrl + 'activity/activity-review-delete', // 删除活动回顾
    ActivityReviewList: ApiRootUrl + 'activity/activity-review-list', // 活动回顾列表

    ActivityGetMyList: ApiRootUrl + 'activity/activity-my-list', // 我的活动列表
    ActivityGetJoinList: ApiRootUrl + 'activity/activity-join-list', // 我的活动列表
    ActivityDelete: ApiRootUrl + 'activity/activity-delete', // 删除活动
    ActivityCreate: ApiRootUrl + 'activity/activity-create', // 创建活动
    ActivityUpdate: ApiRootUrl + 'activity/activity-update', // 更新活动
    ActivityChangeShelves: ApiRootUrl + 'activity/activity-change-shelves', // 更新活动上下架状态
    ActivityDetail: ApiRootUrl + 'activity/activity-detail', // 活动单条信息查询
    ActivityBigDetail: ApiRootUrl + 'activity/activity-big-detail', // 活动详情页所有内容
    ActivityAllList: ApiRootUrl + 'activity/activity-get-all-list', // 活动列表

    ActivityApplyList: ApiRootUrl + 'activity/activity-apply-list', // 报名名单

    ApplyCreate: ApiRootUrl + 'activity/apply-create', // 创建报名

    ApplyListDetails: ApiRootUrl + 'activity/apply-list-details', // 名单详情

    ApplyBigDetail: ApiRootUrl + 'activity/apply-big-detail', // 获取报名信息

    ApplyOrderDetail: ApiRootUrl + 'activity/apply-order-detail', // 报名订单信息

    ApplyRefundUrl: ApiRootUrl + 'activity/apply-refund', // 报名退款

    ApplyCancelOrderUrl: ApiRootUrl + 'activity/apply-cancel-order', // 用户主动取消订单

    ApplyStatusUrl: ApiRootUrl + 'activity/activity-apply-status', // 未登录和已登录用户同时查询，所以不进入权限接口


    CouponsGetUrl: ApiRootUrl + 'coupons/get', // 领取优惠券
    CouponsIndexUrl: ApiRootUrl + 'coupons/index', // 优惠券列表


    SpecificationAdd: ApiRootUrl + 'specification/add', // 活动规格添加
    SpecificationEdit: ApiRootUrl + 'specification/edit', // 活动规格更新
    SpecificationDetail: ApiRootUrl + 'specification/detail', // 活动规格详情
    SpecificationDelete: ApiRootUrl + 'specification/delete', // 活动规格删除
    SpecificationGetList: ApiRootUrl + 'specification/get-list', // 活动多个规格展示


    AgendaAdd: ApiRootUrl + 'agenda/add', // 会务议程添加
    AgendaEdit: ApiRootUrl + 'agenda/edit', // 会务议程更新
    AgendaDetail: ApiRootUrl + 'agenda/detail', // 会务议程详情
    AgendaDelete: ApiRootUrl + 'agenda/delete', // 会务议程删除
    AgendaGetList: ApiRootUrl + 'agenda/get-list', // 活动多个会务议程展示

    TricksAdd: ApiRootUrl + 'tricks/add', // 会后花絮添加
    TricksEdit: ApiRootUrl + 'tricks/edit', // 会后花絮更新
    TricksDetail: ApiRootUrl + 'tricks/detail', // 会后花絮详情
    TricksDelete: ApiRootUrl + 'tricks/delete', // 会后花絮删除

    SupplyAdd: ApiRootUrl + 'supply/add', // 供需创建
    SupplyEdit: ApiRootUrl + 'supply/edit', // 供需更新
    SupplyDetail: ApiRootUrl + 'supply/detail', // 供需详情
    SupplyDelete: ApiRootUrl + 'supply/delete', // 供需删除
    SupplyMyList: ApiRootUrl + 'supply/my-list', // 自己发布的供需列表页
    SupplyList: ApiRootUrl + 'supply/list', // 供需列表页
    SupplyType: ApiRootUrl + 'supply/supply-get-type', // 供需分类
    SupplyBigDetail: ApiRootUrl + 'supply/big-detail', // 供需详情

    CardIndex: ApiRootUrl + 'card/index', // 名片夹主业数据
    CardOtherDetail: ApiRootUrl + 'card/other-detail', // 名片夹其他数据
    CardSocietyList: ApiRootUrl + 'card/society-list', // 名片夹协会成员列表页
    CardCompanyList: ApiRootUrl + 'card/company-list', // 名片夹公司成员列表页
    
    CardCollectionListUrl: ApiRootUrl + 'card/getCollection', // 名片夹数据

    CardSpecialList: ApiRootUrl + 'card/special-list', // 个人收藏列表 * 特别关注
    CardContactList: ApiRootUrl + 'card/contact-list', // 联系过列表
    CardTalkList: ApiRootUrl + 'card/talk-list', // 通话过列表

    LikeStatusUrl: ApiRootUrl + 'get-like-status', // 获取点赞状态
    LikeUrl: ApiRootUrl + 'like', // 用户点赞

    GroupCreateUrl: ApiRootUrl + 'group/create', // 群组创建&&更新
    GroupDeleteUrl: ApiRootUrl + 'group/delete', // 群组删除
    GroupListUrl: ApiRootUrl + 'group/list', // 群组主业列表
    GroupDetailListUrl: ApiRootUrl + 'group/detail-list', // 群组详情列表
    GroupCreateListUrl: ApiRootUrl + 'group/carte-list', // 群组名片展示列表
    GroupShowUrl: ApiRootUrl + 'group/show', // 群组详情
    GroupRemoveUrl: ApiRootUrl + 'group/remove', // 将群成员移除群组
    GroupTemporaryListUrl: ApiRootUrl + 'group/temporary-list', // 临时缓存群组列表


    AttentionChooseUrl: ApiRootUrl + 'attention/choose', // 选择人员

    UndertakeDataUrl: ApiRootUrl + 'attention/undertake-data', // 选择人员

    SetStarsUrl: ApiRootUrl + 'attention/set-stars', // 设置星级
    SetSpecialUrl: ApiRootUrl + 'attention/set-special', // 设置/取消特别关注
    SetContactUrl: ApiRootUrl + 'attention/set-contact', // 设置联系过
    SetTalkUrl: ApiRootUrl + 'attention/set-talk', // 设置通话过

    SendActivitySubMsgUrl: ApiRootUrl + 'send-activity-sub-msg', // 活动提醒订阅通知
    SendPerfectSubMsgUrl: ApiRootUrl + 'send-perfect-sub-msg', // 完善信息订阅通知

    GetNavDataUrl: ApiRootUrl + 'get-nav-data', // 首页相关内容提示数量

    GetCarteNewsUrl: ApiRootUrl + 'get-carte-news', // 首页相关内容提示数量

    ReserveStoreUrl: ApiRootUrl + 'reserve-store', // 名片预约
    ReserveListUrl: ApiRootUrl + 'reserve-list', // 名片预约

    addCarteShareNumUrl: ApiRootUrl + 'add-carte-share-num', // 分享名片增加一次被分享次数

    GetCarteStatisticalUrl: ApiRootUrl + 'carte-statistical', // 名片页统计

    ResetNewVisitsUrl: ApiRootUrl + 'reset-new-visits', // 重置新增访客数

    DepartmentIndexUrl: ApiRootUrl + 'user/department/index', // 获取所有部门
    DepartmentStoreUrl: ApiRootUrl + 'user/department/store', // 添加列表
    DepartmentBindUrl: ApiRootUrl + 'user/department/bind', // 绑定部门
    DepartmentListUrl: ApiRootUrl + 'user/department/list', // 部门列表，带分页
    DepartmentDetailUrl: ApiRootUrl + 'user/department/detail', // 部门详情
    DepartmentUpdateUrl: ApiRootUrl + 'user/department/update', //编辑部门
    DepartmentDeleteUrl: ApiRootUrl + 'user/department/delete', // 删除部门
    DepartmentBindOffUrl: ApiRootUrl + 'user/department/bindOff', // 绑定信息


    UserOrdersUrl: ApiRootUrl + 'user/orders', // 用户订单列表


    BusinessGoodsIndexUrl: ApiRootUrl + 'business/goods-index', // 商家中心商家列表
    BusinessGoodsAddUrl: ApiRootUrl + 'business/goods-add', // 添加商品
    BusinessGoodsUpdateUrl: ApiRootUrl + 'business/goods-update', // 修改商品
    BusinessGoodsDeleteUrl: ApiRootUrl + 'business/goods-delete', // 删除商品
    BusinessGoodsShowUrl: ApiRootUrl + 'business/goods-show', // 商品详情
    BusinessOrderUrl: ApiRootUrl + 'business/order-list', // 商家中心订单管理
    BusinessWalletUrl: ApiRootUrl + 'business/wallet-index', // 商家中心商家钱包
    BusinessWalletDetailUrl: ApiRootUrl + 'business/wallet-detail', // 商家中心商家收益详情
    BusinessUserUrl: ApiRootUrl + 'business/user-index', // 商家中心用户管理

    BusinessAssociationIndexUrl: ApiRootUrl + 'association/index', // 协会列表
    BusinessAssociationCreateUrl: ApiRootUrl + 'association/create', // 创建协会
    BusinessAssociationShowUrl: ApiRootUrl + 'association/show', // 显示基本信息，编辑页面使用update
    BusinessAssociationUpdateUrl: ApiRootUrl + 'association/update', // 编辑协会
    BusinessassociationsDeleteUrl: ApiRootUrl + 'association/delete', // 删除协会
    AssociationsApplicationUrl: ApiRootUrl + 'association/application', // 协会申请记录
    AssociationsVerifyUrl: ApiRootUrl + 'association/application', // 协会申请记录
    SelectAssociationUrl: ApiRootUrl + 'association/select-association', // 选择上级列表

    AssociationSubAuditUrl: ApiRootUrl + 'association/sub-audit', // 下级审核记录
    AssociationSubAuditVrifyUrl: ApiRootUrl + 'association/sub-audit-verify', // 下级审核
    AssociationInfoUrl: ApiRootUrl + 'association/info', // 协会信息（申请入会）
    FootPrintUrl: ApiRootUrl + 'user/footprint', // 保存协会足迹
    FootPrintListUrl: ApiRootUrl + 'user/footprint-list', // 足迹列表


  RoleStoreUrl: ApiRootUrl + 'role/store', // 协会角色及公司管理
  RoleListUrl: ApiRootUrl + 'role/role-list', // 角色列表
  RoleCompanyUrl: ApiRootUrl + 'role/role-company', // 角色公司列表
  RoleNoSelectdCompanyUrl: ApiRootUrl + 'role/no-selectd-company', // 未选中的公司
  RoleAdjustSortUrl: ApiRootUrl + 'role/role-adjust-sort', // 协会角色调整排序
  RoleCompanyAdjustSortUrl: ApiRootUrl + 'role/company-adjust-sort', // 协会公司顺序调整
  RoleAddCompanyRoleUrl: ApiRootUrl + 'role/add-company-role', // 给某个公司添加某个角色
  RoleDelCompanyRoleUrl: ApiRootUrl + 'role/del-company-role', // 删除某个公司对应的角色
  CompanyAssociationInfoUrl: ApiRootUrl + 'role/roles', // 协会角色列表（申请入会）


    GoodsListUrl: ApiRootUrl + 'goods-list', // 公司商品列表
    GoodsShowUrl: ApiRootUrl + 'goods-show', // 公司商品详情
    GoodsOrderUrl: ApiRootUrl + 'goods-order', // 公司商品下单

    SendMmsUrl: ApiRootUrl + 'send-mms', // 发送彩信
    SendNoticeUrl: ApiRootUrl + 'send-notice', // 发送通知短信
};