// pages/card/societyPaySuccess/societyPaySuccess.js
Page({

    /**
     * 页面的初始数据
     */
    data: {
        aid: 0,
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        this.data.aid = options.aid;
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
        wx.removeStorageSync('reaspon');
        wx.removeStorageSync('application');
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

    back: function(){
        if(Number(this.data.aid) > 0) {
            wx.redirectTo({
              url: '/pages/card/applyJoin/index?id=' + this.data.aid,
            });
        }else{
            wx.navigateBack({
                delta: 2
            });
        }
        
    }
})