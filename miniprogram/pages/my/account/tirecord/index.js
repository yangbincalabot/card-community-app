const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    current_page: 1, // 当前页数
    last_page: 1, // 最后一页
    withdraws: [],
    next_page_url: '', // 下一页链接
    total_withdraw: 0

  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    wx.showLoading({
      title: '加载中',
    });
    this.setData({
      next_page_url: api.UserWithdrawListUrl
    });
    this.getUserWithdraws();
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
    this.setData({
      next_page_url: api.UserWithdrawListUrl,
      current_page: 1
    });
    this.getUserWithdraws();
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {
      let current_page = this.data.current_page + 1;
      this.setData({
        current_page: current_page
      });
      if(this.data.current_page > this.data.last_page){
        return;
      }
      this.getUserWithdraws();
  },


  getUserWithdraws: function () {
    util.get(this.data.next_page_url).then(res => {
      let response_withdraws = res.data.data.withdraws;
      let total_withdraw = res.data.data.total_withdraw;
      let withdraws = [];
      if(this.data.current_page > 1){
        withdraws = this.data.withdraws.concat(response_withdraws.data);
      }else{
        withdraws = response_withdraws.data;
      }
      this.setData({
        withdraws: withdraws,
        next_page_url: response_withdraws.next_page_url,
        last_page: response_withdraws.last_page,
        total_withdraw: total_withdraw
      });
      wx.hideLoading();
    });
  }
})