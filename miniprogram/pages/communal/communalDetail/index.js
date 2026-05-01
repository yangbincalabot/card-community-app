const api = require('../../../config/api.js');
const util = require('../../../utils/util.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
      id: null,
      info: {}
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
      wx.showLoading({
        title: '加载中',
      });
      this.setData({
        id: options.id
      });

      this.getCommunalInfo();
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
      this.getCommunalInfo();
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {

  },


  getCommunalInfo: function () {
      util.get(api.CommunalDetailUrl + '?id=' + this.data.id).then(res => {
          wx.hideLoading();
          let data = res.data.data;
          // 图片自适应，因为标签不支持css调整样式，所以改成js
          data.content = data.content.replace(/\<img/gi, '<img style="max-width:100%;height:auto" ');
          this.setData({
              info: data
          });
      });
  }

})