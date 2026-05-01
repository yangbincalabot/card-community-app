// pages/my/activity/applyList/index.js
const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');
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
    let _id = options.id;
    that.setData({
      id: _id
    });
    that.getList(_id);
  },

  getList: function (_id) {
    let that = this;
    util.post(api.ActivityApplyList, { id: _id})
      .then(response => {
        let _data = response.data.data;
        console.log(_data);
        let _apply_num = 0;
        let _list = {};
        let _detail = _data.detail;
        if (_detail && _detail.apply && _detail.apply.length) {
          _apply_num = _detail.apply.length;
         
        }
        _list = _detail;
        that.setData({
          list: _list,
          apply_num: _apply_num
        })
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


})