const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');

Page({

  /**
   * 页面的初始数据
   */
  data: {
      lowers: [],
      total: 0,
      current_page: 1, // 当前页数
      last_page: 1, // 最后一页
      next_page_url: '', // 下一页链接
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
      wx.showLoading({
        title: '加载中',
      });
      this.setData({
        next_page_url: api.AgentLowersUrl
      });
      this.getLowers();
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
        next_page_url: api.AgentLowersUrl,
        current_page: 1
      });
      this.getLowers();
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
      this.getLowers();
  },



  getLowers: function () {
      util.get(this.data.next_page_url).then(res => {
          wx.hideLoading();
          let lowers = [];
          if(this.data.current_page > 1){
            lowers = this.data.lowers.concat(res.data.data);
          }else{
            lowers = res.data.data;
          }
          this.setData({
            lowers: lowers,
            next_page_url: res.data.next_page_url,
            last_page: res.data.last_page,
            total: res.data.total

          });
      })

  },
  navigatorToUrl:function (e) {
    let _url = e.currentTarget.dataset.url;
    if(_url){
      wx.navigateTo({
        url: _url
      })
    }
  },
})