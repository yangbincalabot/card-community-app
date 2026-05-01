// pages/card/group/create/index.js
const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');
Page({
  /**
   * 页面的初始数据
   */
  data: {
    list: [],
    search: '',
    selected_num: 0,
    selectArr: []
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let that = this;
    let _id = options.id;
    if (_id) {
      this.setData({
        id: _id
      })
    }
    that.getList();
  },

  getList: function (_id) {
    let that = this;
    let param = {};
    param.search = that.data.search;
    util.post(api.AttentionChooseUrl, param)
      .then(response => {
        let _data = response.data.data;
        console.log(_data);
        that.setData({
          list: _data
        });
      });
  },

  changeSelected: function (e) {
    let that = this;
    let _info_id = e.currentTarget.dataset.info_id;
    let _id = this.data.id;
    if (_info_id != _id) {
      that.setData({
        id: _info_id
      })
    }
  },

 

  toSubmit: function () {
    let that = this;
    let _id = this.data.id;
    if (!_id) {
      wx.showToast({ title: '请选择用户', icon: 'none', duration: 1000 });
      return false;
    }
    wx.setStorageSync('presenter_id', _id);
    wx.navigateBack({
      delta: 1
    })
  },

  changeSearch: function (e) {
    let that = this;
    let _value = e.detail.value;
    that.setData({
      search: _value
    });
  },

  searchBtn: function (e) {
    let that = this;
    that.getList();
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

  }
});