// pages/my/collection/index.js
const api = require('../../../config/api.js');
const util = require('../../../utils/util.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    ResourceRootUrl: api.ResourceRootUrl,
    currentTab:1,
    supplyList: [],
    supplyData: {},
    activityList: [],
    activityData: {},
  },

  clickTab: function (e) {
    let that = this;
    let _current = e.currentTarget.dataset.current;
    that.setData({
      currentTab: _current
    })
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let that = this;
  },

  getSupplyList: function (_nextUrl) {
    let that = this;
    let _type = 2;
    let _url = api.CollectionSupplyListUrl;
    if (_nextUrl) {
      _url = _nextUrl;
    }
    util.post(_url, { type: _type })
      .then(response => {
        let _bigData = response.data;
        let _data = response.data.data;
        let _list = that.data.supplyList;
        if (_data && _data.length>0) {
          _list = _list.concat(_data);
        }
        console.log(_data);
        that.setData({
          supplyList: _list,
          supplyData: _bigData,
        })
      });
  },

  getActivityList: function (_nextUrl) {
    let that = this;
    let _type = 3;
    let _url = api.CollectionActivityListUrl;
    if (_nextUrl) {
      _url = _nextUrl;
    }
    util.post(_url, { type: _type })
      .then(response => {
        let _bigData = response.data;
        let _data = response.data.data;
        let _list = that.data.activityList;
        if (_data && _data.length>0) {
          _list = _list.concat(_data);
        }
        console.log(_data);
        that.setData({
          activityList: _list,
          activityData: _bigData,
        })
      });
  },

  changeStatus: function (e) {
    let that = this;
    let _info_id = e.currentTarget.dataset.info_id;
    let _type = e.currentTarget.dataset.type;
    util.post(api.CollectionUrl, { type: _type, info_id: _info_id })
        .then(response => {
          let _data = response.data.data;
          console.log(_data);
          wx.showToast({title: '操作成功', icon: 'none', duration: 800});
          setTimeout(function () {
            that.onShow();
          },500);
        });

  },

  navigateToUrl: function (e) {
    let that = this;
    let _url = e.currentTarget.dataset.url;
    wx.navigateTo({
      url: _url
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
    let that = this;
    that.setData({
      supplyList: [],
      supplyData: {},
      activityList: [],
      activityData: {},
    });
    that.getSupplyList();
    that.getActivityList();
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
    let that = this;
    let _currentTab = that.data.currentTab;
    let _nextUrl = that.data.supplyData.next_page_url;
    if (_currentTab == 2) {
      _nextUrl = that.data.activityData.next_page_url;
    }
    if (_nextUrl) {
      if (_currentTab == 2) {
        that.getActivityList(_nextUrl, {});
      } else {
        that.getSupplyList(_nextUrl, {});
      }
    } else {
      console.log('没有内容了'); return false;
    }
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {

  },

})