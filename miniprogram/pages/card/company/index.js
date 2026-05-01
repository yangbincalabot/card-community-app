// pages/card/company/index.js
const api = require('../../../config/api.js');
const util = require('../../../utils/util.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    list:[],
      userInfo:{},
      isShow: false,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.getUserDetail();
  },


  getList: function () {
    let that = this;
    util.post(api.CardCompanyList, {})
        .then(response => {
          let _data = response.data.data;
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
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
      this.getList();
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
    getUserDetail: function(){
      util.get(api.UserIndexUrl, {}, false).then(response => {
          this.setData({
              userInfo: response.data.data,
              isShow: true,
          })
      })
    }
})