// pages/card/group/index/index.js
const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');
Page({

    /**
     * 页面的初始数据
     */
    data: {
        list:[],
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        let that = this;
    },

    /**
     * 生命周期函数--监听页面初次渲染完成
     */
    onReady: function () {

    },

    getList: function () {
        let that = this;
        let _search = that.data.search;
        util.post(api.GroupListUrl, {search: _search})
            .then(response => {
                let _data = response.data.data;
                console.log(_data);
                that.setData({
                    list: _data
                })
            });
    },

    // 页面跳转
    navigateToUrl: function (event) {
        let url = event.currentTarget.dataset.url;
        if(url && url !== '#'){
            wx.navigateTo({
                url: url
            });
        }
    },

    /**
     * 生命周期函数--监听页面显示
     */
    onShow: function () {
        let that = this;
        that.getList();
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

    }
});