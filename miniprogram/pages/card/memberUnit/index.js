// pages/card/memberUnit/index.js
const api = require('../../../config/api.js');
const util = require('../../../utils/util.js');
const App = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    aid: 0, // 协会Id
    search: '', // 搜索关键词
    roles: {
      'roleCompanyList': [],
    }, // 成员
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    const aid = options.id;
    this.setData({ aid });
    this.getCompanies();
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

  getCompanies: function() {
    const {aid, search} = this.data;
    util.get(api.getSocietySquareCompanies, { aid, search }).then(res => {
      this.setData({
        roles: res.data.data
      })
    });
  },

  bindinput: function(event) {
    const search = event.detail.value;
    this.setData({ search })
  }
})