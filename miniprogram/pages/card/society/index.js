// pages/card/society/index.js
const api = require('../../../config/api.js');
const util = require('../../../utils/util.js');
const App = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    viewInto: '',
    search: '',
    aid: 0,
  },


  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {
    let that = this;
      let aid = parseInt(options.aid);
      this.setData({aid})
      const eventChannel = this.getOpenerEventChannel();
      eventChannel.on('society', data => {
          if (data && data.society_name) {
              wx.setNavigationBarTitle({ title: data.society_name });
          }
      })
    that.getList();
  },

  getList: function() {
    let that = this;
    let _search = that.data.search;
    let aid = this.data.aid;
    util.post(api.CardSocietyList, {
        search: _search,
        aid,
      })
      .then(response => {
        let _data = response.data.data;
        console.log(_data)
        that.setData({
          roleCompanyList: _data.roleCompanyList,
          generalList: _data.generalList
        })
   
      });
  },

  changeSearch: function(e) {
    let that = this;
    let _value = e.detail.value;
    that.setData({
      search: _value
    });
  },

  searchBtn: function(e) {
    let that = this;
    that.setData({
      list: [],
      bigData: []
    });
    that.getList();
  },

  // 页面跳转
  navigateToUrl: function(event) {
    let url = event.currentTarget.dataset.url;
    if (url && url !== '#') {
      wx.navigateTo({
        url: url
      });
    }
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function() {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function() {


  },
  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function() {

  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function() {

  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function() {
    let that = this;
    that.getList();
    setTimeout(function() {
      wx.stopPullDownRefresh();
    }, 1000);
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function() {

  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function() {

  }
});