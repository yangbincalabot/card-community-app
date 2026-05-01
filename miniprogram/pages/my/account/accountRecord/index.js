var app = getApp();
const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');

Page({

  /**
   * 页面的初始数据
   */
  data: {
    current_page: 1, // 当前页数
    last_page: 1, // 最后一页
    logs: [],
    next_page_url: '', // 下一页链接
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {

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
    this.setData({
      next_page_url: api.UserAccountLogsUrl
    });
    this.getAccountLogs();
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
      next_page_url: api.UserAccountLogsUrl,
      current_page: 1
    });
    this.getAccountLogs();
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
    this.getAccountLogs();
  },


  getAccountLogs: function () {
    util.get(this.data.next_page_url).then(res => {
      let logs = [];
      if(this.data.current_page > 1){
        logs = this.data.logs.concat(res.data.data);
      }else{
        logs = res.data.data;
      }
      this.setData({
        logs: logs,
        next_page_url: res.data.next_page_url,
        last_page: res.data.last_page
      })
    });
  }
})