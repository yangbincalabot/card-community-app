// pages/my/coupon/index/index.js
const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');
Page({

    /**
     * 页面的初始数据
     */
    data: {
        coupons:[],
        have: true
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {

    },

    /**
     * 生命周期函数--监听页面初次渲染完成
     */
    onReady: function () {

    },

    /**
     * 生命周期函数--监听页面显示
     */
    onShow() {
        let that = this;
        that.getCouponsList();
    },


    getCouponsList:function(){
        let that = this;
        // 根据选择的sku_id 获取对应的商品信息，并获取用户收获地址信息
        let _requestUrl = api.CouponsIndexUrl;
        util.post(_requestUrl,{})
            .then(response => {
                let _responseData = response.data.data;
                that.setData({
                    coupons:_responseData,
                });
            });
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


})