//index.js
//获取应用实例
const util =  require('../../../../utils/util.js');
const api = require('../../../../config/api.js');
const app = getApp()

Page({
    data: {
        show: false,
        city: '',
        address: '地址',
        homeBanners: [],
        resourceUrl: '',
        teamGoodses: [],
        teamGoods_one: [],
        teamGoods_two: [],
        actives: [],
        goodsTypes: [],
        seckillGoodses: [],
        carouselList: [],
        commodityData: [],
        limitedTimeData: [],
        toView: '',
        heightDate: '',
        indexNav:0,
        navs: [{
            iconPath: 'http://szdbi.oss-cn-shenzhen.aliyuncs.com/jubang/icon-sy-xptj.png',
            name: '会员礼包',
            toPage: '/pages/card/newgoods/list/list'
        }, {
            iconPath: 'http://szdbi.oss-cn-shenzhen.aliyuncs.com/jubang/icon-sy-rxb.png',
            name: '热销榜',
            toPage: '/pages/card/newgoods/list/list'

        }, {
            iconPath: 'http://szdbi.oss-cn-shenzhen.aliyuncs.com/jubang/icon-sy-xsth.png',
            name: '限时特惠',
            toPage: '/pages/card/newgoods/list/list'
        }, {
            iconPath: 'http://szdbi.oss-cn-shenzhen.aliyuncs.com/jubang/icon-sy-pgsp.png',
            name: '拼购商品',
            toPage: '/pages/card/newgoods/list/list'
        },{
            iconPath: 'http://mp.jubang.szdbi.com//upload/GoodsTypePlatform_20201126105025170_8155.png',
            name: '生活家居',
            toPage: '/pages/card/newgoods/list/list'
        },{
            iconPath: 'http://mp.jubang.szdbi.com//upload/GoodsTypePlatform_20201126105039889_2604.png',
            name: '手机数码',
            toPage: '/pages/card/newgoods/list/list'
        },
        {
            iconPath: 'http://mp.jubang.szdbi.com//upload/GoodsTypePlatform_20201126105057695_4154.png',
            name: '家用电器',
            toPage: '/pages/card/newgoods/list/list'
        }, 
        {
            iconPath: 'http://mp.jubang.szdbi.com//upload/GoodsTypePlatform_20201126105114664_50.png',
            name: '电脑办公',
            toPage: '/pages/card/newgoods/list/list'
        }, 
        {
            iconPath: 'http://mp.jubang.szdbi.com//upload/GoodsTypePlatform_20201126105127086_24.png',
            name: '美妆护肤',
            toPage: '/pages/card/newgoods/list/list'
        },     
    ],
        query: {
            current: 1,
            limit: 4,
            keyword: '',
            city: '深圳',
            sort: {
                field: 'default',
                way: 'DESC'
            }
        },
        goodsTypeId: '',
        goods: [],
        goodsTotal: 0,
        cid: 0,
        page: 1,
        next_url: '',
        keywords: '',
    },
    toPage(e) {
        let path = e.currentTarget.dataset.pagePath;
        wx.navigateTo({
            url: path,
        })
    },
    toCurrent(e) {
        let index = e.currentTarget.dataset.index;
        this.setData({
            indexNav: index,
            'query.current': 1,
          //  goods: [],
            goodsTotal: 0
        })
        // this.findGoods(this.data.goodsTypes[this.data.indexNav].id);
    },
    toPageTab() {
        wx.switchTab({
          url: '../liveBroadcast/index',
        })
    },
    toActiveList(e) {
        let id = e.currentTarget.dataset.id
        wx.navigateTo({
            url: '/pages/card/newgoods/list/list?cid=' + this.data.cid,
        })
    },

    //触底事件
    onReachBottom:function() {
        this.loadMore();
    },
    loadMore() {
        if (this.data.goods.length >= this.data.goodsTotal && this.data.goods.length > 0 || this.data.goodsTotal == 0) {
            wx.showToast({
                title: '到底了',
                icon: 'none'
            })
            return
        }
        this.findGoods()
    },

    onLoad: function (options) {
        var _this = this
        wx.getSystemInfo({
            success: function (res) {
                console.log(res.windowHeight)
                _this.setData({
                    heightDate: res.windowHeight * 2 + 70
                })
            },
        })
        let cid = parseInt(options.cid);
        if(cid <= 0){
            wx.navigateBack();
        }

        this.data.next_url = api.GoodsListUrl;
        this.setData({ cid })

        
        this.findHomeBanner(); 
        this.getGoods(() => {
            this.initData();
        });

        let goodsTypes = [
            {id: '', name: '生活家居', iconPath: '', remark: ''},
            {id: '', name: '手机数码', iconPath: '', remark: ''},
            {id: '', name: '家用电器', iconPath: '', remark: ''},
            {id: '', name: '电脑办公', iconPath: '', remark: ''},
            {id: '', name: '美妆护肤', iconPath: '', remark: ''},
            {id: '', name: '礼包', iconPath: '', remark: ''},
        ];
        this.setData({ goodsTypes })

        // pages/index/index?parentCode=btVfQe&recommendUserId=fee1fbb4-f599-4317-81cf-c7e2b21a9571
    },

    onShow: function () {


    },


    // 轮播图
    findHomeBanner() {
        const homeBanners = [
            // "https://mp.jubang.szdbi.com//upload/imageText_20201212190702937_7985.jpg",
            "http://mp.jubang.szdbi.com//upload/imageText_20201204181844383_1795.png",
            "http://mp.jubang.szdbi.com//upload/imageText_20201204183255762_1211.png"
        ];
        this.setData({ homeBanners })
    },


    /**
     * 用户点击右上角分享
     */
    onShareAppMessage: function () {
        var _this = this
        return util.shareAppMessage({


        });
    },
    closeDialog() {
        this.setData({
            show: false
        }, () => {
            wx.showTabBar({
                animation: true,
            })
        })
    },


    onHide: function () {

    },

    initData: function() {
        const actives = [
            {
                id: 'f6e806c9-853c-48c5-b436-47b34d943711',
                mainTitle: '推荐',
                subTitle: '推荐商品',
                iconPath: 'https://mp.jubang.szdbi.com//upload/null_20201214171521604_1093.png',
                sequence: 1,
                remark: '',
                goodsViews: this.data.goods
            },
            {
                id: 'f6e806c9-853c-48c5-b436-47b34d943711',
                mainTitle: '热销',
                subTitle: '热销商品',
                iconPath: 'https://mp.jubang.szdbi.com//upload/null_20201214171521604_1093.png',
                sequence: 1,
                remark: '',
                goodsViews: this.data.goods
            },

        ];
        this.setData({ actives })
    },

    getGoods: function(callback){
        let { cid, keywords} = this.data
        util.get(this.data.next_url, {cid, keywords}).then(res => {
            let goodsData = res.data.data.goods;
            let goods;
            if(this.data.page === 1){
                goods = goodsData.data;
            }else{
                goods = this.data.goods.concat(goodsData.data);
            }
            console.log(goods)
            this.setData({
                goods,
                next_url: goodsData.next_page_url,
                page: goodsData.current_page + 1
            }, () => {
                if(callback && typeof callback === 'function'){
                    callback();
                }
            })
        })
    },

    commodityDetailsJump: function(e) {
        const id = e.currentTarget.dataset.goodsId;
        if(Number(id) > 0)   {
            wx.navigateTo({
              url: '/pages/card/newgoods/detail/detail?id=' + id,
            })
        }
    },

    notOpen() {
        wx.showToast({
          title: '功能暂未开放',
          icon: 'none'
        });
    },

    onSearch: function(){
        wx.navigateTo({
          url: `/pages/card/newgoods/list/list?cid=${this.data.cid}&keywords=${this.data.keywords}`,
        })
    },

    changeKeyword: function(event){
        let keywords = event.detail.value;
        this.data.keywords = keywords;
    }

})