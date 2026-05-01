// pages/my/activity/review/index.js
const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    ResourceRootUrl: api.ResourceRootUrl,
    reviewList: [],
    main_hidden: true,
    none_hidden: false
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    
  },

  // 获取活动回顾
  getActivityReviewList: function () {
    let that = this;
    util.post(api.ActivityReviewMyList, {}).then(res => {
      let _data = res.data.data;
      console.log(_data);
      let _new_data = [];
      let _none_hidden = that.data.none_hidden;
      if (_data && _data.length > 0) {
        _new_data = _data;
        _none_hidden = true;
      }
      that.setData({
        reviewList: _new_data,
        none_hidden: _none_hidden,
        main_hidden: false
      })
    });
  },

  remove: function (event) {
    let that = this;
    let _id = event.currentTarget.dataset.id;
    wx.showModal({
      title: '删除',
      content: '删除后不可撤销，您确定删除该条信息吗？',
      success(res) {
        if (res.confirm) {
          util.post(api.ActivityReviewDelete, { id: _id })
            .then(response => {
              console.log(response.data.data)
              wx.showToast({ title: '删除成功', icon: 'none', duration: 800 });
              that.onShow();
            });
        }
      }
    })
  },

  // 页面跳转
  navigateToUrl: function (event) {
    console.log(event);
    let url = event.currentTarget.dataset.url;
    wx.navigateTo({
      url: url
    });
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
    that.getActivityReviewList();
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