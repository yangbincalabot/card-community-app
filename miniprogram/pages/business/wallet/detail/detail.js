const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');

var app = getApp();
// pages/search/index.js
Page({

    /**
     * 页面的初始数据
     */
    data: {
        currentTab: '0',

        page: 1,
        next_url: api.BusinessWalletDetailUrl,
        logs: []

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
        this.getLogs();
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
        this.data.next_url = api.BusinessWalletDetailUrl;
        this.data.logs = [];
        this.getLogs(() => {
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
        this.getLogs();
    },

    getLogs: function(callback){
        util.get(this.data.next_url).then(res => {
            let logData = res.data.data.logs;
            let logs = [];
            if(this.data.page === 1){
                logs = logData.data;
            }else{
                logs = this.data.logs.concat(logData.data);
            }
            this.setData({
                logs,
                next_url: logData.next_page_url,
                page: logData.current_page + 1
            });
            if(callback && typeof callback === 'function'){
                callback();
            }
        })
    }

})