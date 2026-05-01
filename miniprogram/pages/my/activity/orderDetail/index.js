// pages/my/activity/orderDetail/index.js
const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    detail:{},
    mainHidden: true
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let that = this;
    let _id = options.id;
    if (!_id) {
      that.prompt();
      return false;
    }
    that.getDetail(_id);
  },

  getDetail: function (_id) {
    let that = this;
    util.post(api.ApplyOrderDetail, {id: _id})
        .then(response => {
          let _data = response.data.data;
          console.log(_data);
          if (!_data || _data.length === 0) {
            that.prompt();
            return false;
          }
          that.setData({
            id: _id,
            detail: _data,
            mainHidden: false
          })
        });
  },

  prompt: function () {
    wx.showToast({ title: '页面不存在', icon: 'none', duration: 1000 });
    setTimeout(function () {
      wx.navigateBack({
        delta: 1
      })
    }, 500);
  },

  cancelOrder: function () {
    let that = this;
    let _id = that.data.id;
    util.post(api.ApplyCancelOrderUrl, { id: _id })
      .then(response => {
        wx.showToast({ title: '操作成功', icon: 'none', duration: 1000 });
        setTimeout(() => {
          that.getDetail(_id);
        }, 500);
      });
  },

  refund: function () {
    let that = this;
    let _id = that.data.id;
    wx.showModal({
      title: '退款',
      content: '您确定退款吗？退款需要审核，请您耐心等待。',
      success: function (res) {
        if (res.confirm) {
          util.post(api.ApplyRefundUrl, {id: _id})
              .then(response => {
                let _data = response.data.data;
                wx.showToast({ title: '操作成功', icon: 'none', duration: 1000 });
                setTimeout(() => {
                  that.getDetail(_id);
                }, 500);
              });
        }
      }
    })
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

})