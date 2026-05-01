const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
      service_agreement: ''
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
      wx.showLoading({
        title: '加载中',
      });
      this.getServiceAgreement();
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
    getServiceAgreement: function () {
        util.get(api.GetConfigureUrl, {name: 'AGREEMENT_CONTENT'}).then((res) => {
            let data = res.data.data;
            // 图片自适应，因为标签不支持css调整样式，所以改成js
            data.AGREEMENT_CONTENT = data.AGREEMENT_CONTENT.replace(/\<img/gi, '<img style="max-width:100%;height:auto" ');
            this.setData({
                service_agreement: data.AGREEMENT_CONTENT
            });
            wx.hideLoading();
        })
    }

})