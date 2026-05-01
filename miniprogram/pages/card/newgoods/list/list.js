// pages/commoditybox/commodityList/index.js
const util =  require('../../../../utils/util.js');
const api = require('../../../../config/api.js');
Page({

    /**
     * 页面的初始数据
     */
    data: {
        resourceUrl: '',
        filterIndex: 0,
        goodsTypes: [{id: '18eac1c2-dec3-4079-afba-135402597230', name:  '全部'}],
        query: {
            current: 1,
            limit: 20,
            keyword: '',
            city:'城市',
            sort: {
                field: 'default',
                way: 'DESC'
            }
        },
        
        total: 0,
        twoNav: [{
            'navTitle': '手机数码'
        },
            {
                'navTitle': '食品生鲜'
            },
            {
                'navTitle': '电脑办公'
            },
            {
                'navTitle': '家用电器'
            },
            {
                'navTitle': '家用电器'
            },
            {
                'navTitle': '手机数码'
            },
            {
                'navTitle': '手机数码'
            },
            {
                'navTitle': '手机数码'
            },
        ],
        twoNavIndex: 0,
        priceSize: true,
        goodsTotal: 0,
        cid: 0,
        page: 1,
        next_url: '',
        keywords: '',
        goods: [],
    },
    toShop() {
        wx.switchTab({
            url: '/pages/shoppingCart/index',
        })
    },

  
    // 请求筛选
    filterClick(e) {
        var _this = this
        _this.setData({
            filterIndex: e.currentTarget.dataset.index
        })
        // debugger
        if (e.currentTarget.dataset.index == '2' && _this.data.priceSize) {
            _this.setData({
                priceSize: false,
            })
            _this.data.query.sort = {
                field: 'price',
                way: 'DESC'
            }
            _this.reload()
        } else if (e.currentTarget.dataset.index == '2' && (!_this.data.priceSize)) {
            _this.setData({
                priceSize: true,
            })
            _this.data.query.sort = {
                field: 'price',
                way: 'ASC'
            }
            _this.reload()
        } else if ('0' == e.currentTarget.dataset.index) {
            _this.data.query.sort = {
                field: 'default',
                way: 'DESC'
            }
            _this.reload()
        } else {
            _this.data.query.sort = {
                field: 'sale',
                way: 'DESC'
            }
            _this.reload()
        }
    },
    keywordInput(e) {
        var _this = this
        let keyword = e.detail.value
        _this.data.query.keyword = keyword
    },
    changeClick(e) {
        var _this = this
        let goodsTypeId = e.currentTarget.dataset.goodsTypeId
        _this.setData({
            twoNavIndex: e.currentTarget.dataset.index
        })
        _this.data.query.goodsTypeId = goodsTypeId
        _this.reload()
    },
    commodityDetailsJump(e) {
        let id = e.currentTarget.dataset.goodsId
        if(Number(id) > 0)   {
            wx.navigateTo({
              url: '/pages/card/newgoods/detail/detail?id=' + id,
            })
        }
    },
    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        if (options.parentCode) {
            util.onReady(options);
        }
        var _this = this



        if (options && options.loadType && options.loadType === 'sale') {
            _this.setData({
                filterIndex: 1
            })
            _this.data.query.sort = {
                field: 'sale',
                way: 'DESC'
            }
        }


        let cid = parseInt(options.cid);
        if(cid <= 0){
            wx.navigateBack();
        }
        let keywords = options.keywords || '';
        this.data.next_url = api.GoodsListUrl;
        this.setData({ cid, keywords })
        this.getGoods()

    },


    /**
     * 生命周期函数--监听页面初次渲染完成
     */
    onReady: function () {

    },

    /**
     * 生命周期函数--监听页面显示
     */
    onShow: function () {
        var _this = this

        _this.reload()

    },

    /**
     * 生命周期函数--监听页面隐藏
     */
    onHide: function () {

    },

    /**
     * 生命周期函数--监听页面卸载
     */
    onUnload: function () {

    },

    /**
     * 页面相关事件处理函数--监听用户下拉动作
     */
    onPullDownRefresh: function () {
        wx.stopPullDownRefresh()
        this.reload()
    },

    /**
     * 页面上拉触底事件的处理函数
     */
    onReachBottom: function () {
        this.loadMore()
    },
    loadMore() {
        if (this.data.goods.length >= this.data.total && this.data.goods.length > 0) {
            wx.showToast({
                title: '到底了',
                icon: 'none'
            })
            return
        }
    },
    search() {
        var _this = this
        _this.reload()
    },
    reload() {
        var _this = this
        _this.data.query.current = 1
        _this.data.goods = []
    },
 
    /**
     * 用户点击右上角分享
     */
    onShareAppMessage: function () {
        var _this = this
        return util.shareAppMessage({
            
            
        });
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
    onSearch: function(){
        this.data.page = 1;
        this.data.next_url = api.GoodsListUrl;
        this.data.goods = [];
        this.getGoods();
    },

    changeKeyword: function(event){
        let keywords = event.detail.value;
        this.data.keywords = keywords;
    }
})