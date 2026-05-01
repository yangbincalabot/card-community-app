var app = getApp();
const api = require('../../../../../config/api.js');
const util = require('../../../../../utils/util.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    currentTab: '0',
    accountDetail: {},
    log_type: 1,

    current_page: 1, // 当前页数
    last_page: 1, // 最后一页
    logs: [],
    next_page_url: '', // 下一页链接

  },
  //点击切换
  clickTab: function (e) {
    var that = this;
    if (this.data.currentTab === e.currentTarget.dataset.current) {
      return false;
    } else {
      that.setData({
        currentTab: e.currentTarget.dataset.current,
        log_type: e.currentTarget.dataset.log_type,
        logs: []
      });
      this.onShow();
    }
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.getAccountDetail();

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
        next_page_url: api.UserAccountLogsUrl + '?log_type=' + this.data.log_type
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
      next_page_url: api.UserAccountLogsUrl + '?log_type=' + this.data.log_type,
      current_page: 1
    });
    this.getAccountDetail();
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


  getAccountDetail: function () {
      util.get(api.UserAccountDetailUrl).then(res => {
          this.setData({
            accountDetail: res.data.data
          });
      })
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