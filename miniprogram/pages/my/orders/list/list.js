const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');

var app = getApp();
Page({

    /**
     * 页面的初始数据
     */
    data: {
        currentTab: '0',

        page: 1,
        next_url: api.UserOrdersUrl,
        orders: [],

    },
    //点击切换
    clickTab: function (e) {
        var that = this;
        if (this.data.currentTab === e.target.dataset.current) {
            return false;
        } else {
            that.setData({
                currentTab: e.target.dataset.current,
            })
        }
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
        this.data.next_url = api.UserOrdersUrl;
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
            let ordersData = res.data.data.orders;
            let orders = [];
            if(this.data.page === 1){
                orders = ordersData.data;
            }else{
                orders = this.data.orders.concat(ordersData.data);
            }
            this.setData({
                page: ordersData.current_page + 1,
                orders,
                next_url: ordersData.next_url,
            })
        })
    },
    phoneCall: function(event){
        let phone = event.currentTarget.dataset.phone;
        if(phone){
            wx.makePhoneCall({
                phoneNumber: phone,
            })
        }
    }
})