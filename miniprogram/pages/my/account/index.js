const api = require('../../../config/api.js');
const util = require('../../../utils/util.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
      userInfo: {
          money: '0.00'
      }
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    wx.showLoading({
      title: '加载中'
    });
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
    this.getUserBalance();
    wx.removeStorage({
      key: 'IS_SELECT_BANK',
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


  navigatorToUrl:function (e) {
    let _url = e.currentTarget.dataset.url;

    if(_url){
      wx.navigateTo({
        url: _url
      })
    }
  },

  getUserBalance: function () {
      util.get(api.UserBalanceUrl).then(res => {
          this.setData({
            userInfo: res.data.data
          });
          wx.hideLoading();
      });
  }
})