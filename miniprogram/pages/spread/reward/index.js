const api = require('../../../config/api.js');
const util = require('../../../utils/util.js');
Page({

    /**
     * 页面的初始数据
     */
    data: {
        userInfo: {}
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
    onShow: function () {
        wx.showLoading({
            title: '加载中',
        });
        this.getUserRecommend();
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
        return {
            title: '邀请好友得好礼',
            path: 'pages/spread/index/index?scene=user_id@' + this.data.userInfo.id
        }
    },
    getUserInfo: function () {
        util.get(api.UserIndexUrl).then(res => {
            this.setData({
                userInfo: res.data.data
            });
        });
    },
    // 页面跳转
    navigateToUrl: function (event) {
        console.log(event);
        let url = event.currentTarget.dataset.url;
        wx.navigateTo({
            url: url
        });
    },
    getUserRecommend: function () {
        util.get(api.UserRecomendUrl).then(res => {
            wx.hideLoading();
            this.setData({
                userInfo: res.data.data
            });
            console.log(this.data.userInfo.id);
        })
    }
})