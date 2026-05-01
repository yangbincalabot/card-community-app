// pages/reviewList/index/index.js
const api = require('../../../config/api.js');
const util = require('../../../utils/util.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    ResourceRootUrl: api.ResourceRootUrl,
    responseData:[],
    listsData: []
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.getActivityReviewList();
  },

  // 获取活动回顾
  getActivityReviewList: function () {
    let that = this;
    util.post(api.ActivityReviewList, {}).then(response => {
      let _responseData = response.data;
      let _listsData = [];
      if (_responseData.data && _responseData.data.length > 0) {
        _listsData = _responseData.data;
      }
      let _oldListData = that.data.listsData;
      let _newListData = _oldListData.concat(_listsData);
      console.log(_newListData);
      that.setData({
        responseData: _responseData,
        listsData: _newListData
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
    let that = this;
    let _nextUrl = that.data.responseData.next_page_url;
    if (_nextUrl) {
      that.getList(_nextUrl, {});
    } else {
      console.log('没有内容了'); return;
    }
  },

 
})