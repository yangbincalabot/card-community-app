const api = require('../../../config/api.js');
const util = require('../../../utils/util.js');


Page({

    /**
     * 页面的初始数据
     */
    data: {
        orders: [],
        page: 1,
        next_url: api.BusinessUserUrl,
        total: 0,
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        this.getOrders();
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
        this.data.page = 1;
        this.data.next_url = api.BusinessUserUrl;
        this.data.orders = [];
        this.getOrders(() => {
            wx.stopPullDownRefresh();
        })
    },

    /**
     * 页面上拉触底事件的处理函数
     */
    onReachBottom: function () {
        if(!this.data.next_url){
            return;
        }
        this.getOrders();
    },

    getOrders: function(callback){
        util.get(this.data.next_url).then(res => {
            let orderData = res.data.data.orders;
            let orders = [];
            if(this.data.page === 1){
                orders = orderData.data;
            }else{
                orders = this.data.orders.concat(orderData.data);
            }
            this.setData({
                orders,
                total: orderData.total,
                next_url: orderData.next_page_url,
                page: orderData.current_page + 1
            });
            if(callback && typeof callback === 'function'){
                callback();
            }
        })
    }
})