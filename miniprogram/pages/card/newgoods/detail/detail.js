// pages/commoditybox/commodityDetails/index.js
const util = require('../../../../utils/util.js');
const api = require('../../../../config/api.js');
Page({

    /**
     * 页面的初始数据
     */
    data: {
        priceDesc: [{
            title: '协会商城价:',
            desc: '协会商城价为商品的销售价，是您最终决定是否购买商品的依据。'
        },   {
            title: '异常问题:',
            desc: '商品促销信息以商品详情页“促销”栏中的信息为准；商品的具体售价以订单结算页价格为准；如您发现商品售价有异常，建议购买前先联系 协会商城客服咨询。'
        }],
        loadMore: true,
        videoIndexs: [],
        imgIndexs: [],
        bannerCurrent: 0,
        currentMedio: 0,

        theVideoId: '',
        isPlay: false,

        indicatorDots: true,
        autoplay: true,
        interval: 4000,
        duration: 500,
        bannerIndex: 1,

        attaches: [],
        favoriteId: '',
        goodsComments: [],
        total: 0,
        goodsPrices: [],
        resourceUrl: '',
        goodsId: '',
        listJinData: ['1斤', '2斤', '3斤', '4斤', '5斤', '6斤', '7斤', '8斤', '9斤'],
        jinIndex: 0,
        goods: '',
        limitedTimeData: [],
        member: {},
        // levelMap: {},
        goods: {
            images: []
        },
        id: 0,
    },
    loadMore() {
        this.setData({
            loadMore: false,
        })
    },


    commodityDetailsJump(e) {
        let goodsId = e.currentTarget.dataset.goodsId
        wx.redirectTo({
            url: '/pages/commoditybox/commodityDetails/index?goodsId=' + goodsId,
        })
    },

    saveShopcart(e) {
        let priceId = e.currentTarget.dataset.priceId
        var _this = this;
        util.my_ajax({
            url: '/shopcart/saveShopcart',
            data: {
                priceId: priceId,
                amount: 1
            },
            contentType: 'application/json',
            methods: 'post',
            success(response) {
                wx.showToast({
                    title: '已加入购物车',
                });
            }
        });
    },
    bindchange(e) {
        console.log(e)
        var _this = this
        let index = e.detail.current
        let currentMedio = _this.data.attaches[index].type1 == 'video' ? 0 : 1
        if ("autoplay" == e.detail.source) {
            this.setData({
                bannerIndex: index + 1,
                bannerCurrent: index,
                currentMedio: currentMedio,
            })

        }
        if ('touch' == e.detail.source) {
            this.setData({
                bannerIndex: index + 1,
                bannerCurrent: index,
                currentMedio: currentMedio,
            })
        }
    },
    toRefund() {
        wx.navigateTo({
            url: '../refund/index',
        })
    },

    changebg(e) {
        var _this = this
        _this.setData({
            jinIndex: e.currentTarget.dataset.index
        })
    },
    toCart() {
        wx.showToast({
          title: '功能暂未开放',
          icon: 'none'
        });
        return;
        wx.switchTab({
            url: '/pages/shoppingCart/index',
        })
    },
    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        let id = parseInt(options.id);
        if (id <= 0) {
            wx.navigateBack();
        }
        this.data.id = id;
        this.getData();
    },
    /**
     * 生命周期函数--监听页面显示
     */
    onShow: function () {

    },


    /**
     * 生命周期函数--监听页面隐藏
     */
    onHide: function () {

    },
    clickShare: function () {
        let _this = this
        _this.setData({
            show: true
        })
    },
    /**
     * 用户点击右上角分享
     */
    onShareAppMessage: function () {
        var _this = this
        return util.shareAppMessage({

        });
    },

    getPosters: function () {
        this.setData({
            postersShow: true
        })
    },
    postersClose: function () {
        this.setData({
            postersShow: false
        })
    },
    close: function () {
        this.setData({
            show: false
        })
    },

    getData: function () {
        let id = this.data.id;
        util.get(api.GoodsShowUrl, {
            id
        }).then(res => {
            let goods = res.data.data.goods;
            goods.image = [goods.image];
            this.setData({
                goods
            })
        })
    },
    onBuy: function () {

        wx.showLoading({
            title: '提交中',
            mask: true
        });
        let id = this.data.id;
        util.post(api.GoodsOrderUrl, {
            id
        }).then(res => {
            wx.hideLoading();
            if (parseFloat(this.data.goods.price) > 0) {
                let response = res.data.data;
                if (response.appId) {
                    this.weChatMiNiPay(response);
                } else {
                    wx.navigateTo({
                        url: '/pages/card/goodsPaySuccess/goodsPaySuccess',
                    });
                }
            } else {
                wx.navigateTo({
                    url: '/pages/card/goodsPaySuccess/goodsPaySuccess',
                });
            }
        });

    },


    // 微信支付
    weChatMiNiPay: function (charge) {
        wx.requestPayment({
            "timeStamp": charge.timeStamp,
            "nonceStr": charge.nonceStr,
            "package": charge.package,
            "signType": charge.signType,
            "paySign": charge.paySign,
            success: res => {
                if (res.errMsg == 'requestPayment:ok') {
                    wx.navigateTo({
                        url: '/pages/card/goodsPaySuccess/goodsPaySuccess',
                    })

                } else {
                    wx.showModal({
                        content: '调用支付失败！',
                        showCancel: false
                    })
                }
            },
            fail: err => {
                console.log(err);
                if (err.errMsg == 'requestPayment:fail cancel') {

                } else {
                    wx.showModal({
                        content: '调用支付失败！',
                        showCancel: false
                    })
                }
            }
        })
    },

})