// pages/passport/getPhone/index.js
const api = require('../../../config/api.js');
const util = require('../../../utils/util.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {

  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let that = this;
    this.getUserInfo();
  },


  getPhoneNumber: function (e) {
    let that = this;
    util.getPhoneNumber(e, that.data.code).then(response => {
      console.log(response);
      if (response) {
        wx.showToast({title: '手机号授权成功', icon: 'none', duration: 800});
        let  _url = wx.getStorageSync('otherToUrl');
        if (!_url) {
          setTimeout(() => {
            wx.navigateBack({
              delta: 1
            })
          },500);
          return false;
        }
        let  _userInfo = that.data.userInfo;
        if (!_userInfo.carte) {
          _url = '/pages/my/card/editCard/index';
        } else {
          wx.removeStorageSync('otherToUrl');
        }
        setTimeout(() => {
          wx.redirectTo({
            url: _url
          });
        },800);
      }
    });

  },

  getUserInfo: function () {
    util.get(api.UserIndexUrl).then(res => {
      console.log(res.data.data);
      this.setData({
        userInfo: res.data.data
      });
    });
  },

  /*
     * 不授权
     */
  notAuthorized: function () {
    wx.showModal({
      title: '确定不进行授权吗？',
      content: '不授权将不可以使用部分功能',
      success: function (res) {
        if (res.confirm) {
          wx.navigateBack({
            delta: 1
          })
        }
      }
    })
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
    this.login();
  },

  login: function () {
    let that = this;
    wx.login({
      success: res => {
        that.data.code = res.code;
      }
    })
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
})