const api = require('../../../config/api.js');
const util = require('../../../utils/util.js');

Page({

    /**
     * 页面的初始数据
     */
    data: {
        indicatorDots: false,
        autoplay: false,
        interval: 5000,
        duration: 1000,
        showModalStatus: false,
        showModalStatusVip: false,
        goods:{
            images: []
        },
        id: 0,
    },
    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        let id = parseInt(options.id);
        if(id <= 0){
            wx.navigateBack();
        }
        this.data.id = id;
        this.getData();
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

    },

    /**
     * 页面上拉触底事件的处理函数
     */
    onReachBottom: function () {

    },

    /**
     * 用户点击右上角分享
     */
    onShareAppMessage: function () {

    },

    getData: function(){
        let id = this.data.id;
        util.get(api.GoodsShowUrl, {id}).then(res => {
            let goods = res.data.data.goods;
            goods.image = [goods.image];
            this.setData({goods})
        })
    },
    onBuy: function(){

        wx.showLoading({
            title: '提交中',
            mask: true
        });
        let id = this.data.id;
        util.post(api.GoodsOrderUrl, { id }).then(res => {
            wx.hideLoading();
            if (parseFloat(this.data.goods.price) > 0){
                let response = res.data.data;
                if (response.appId) {
                    this.weChatMiNiPay(response);
                } else {
                    wx.navigateTo({
                        url: '/pages/card/goodsPaySuccess/goodsPaySuccess',
                    });
                }
            }else{
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