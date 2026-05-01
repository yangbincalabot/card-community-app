const api = require('../../../config/api.js');
const util = require('../../../utils/util.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    isHidden: true,
    inOperation: false
  },
  pageData: {
    aid: 0,

  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.pageData.aid = options.id;
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
    this.getUserListUrl();
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

  changeUser: function (event) {
    let _id = event.currentTarget.dataset.id;
    this.setData({
      currentId: _id
    })
    this.realConfirm();
  },

  realConfirm: function() {
    let _id = this.data.currentId;
    if (this.data.currentUser && this.data.currentUser.id == _id) {
      // wx.navigateBack({
      //   delta: 1
      // })
      return false;
    }
    wx.showLoading({
      title: '切换中',
    });
    if (this.data.inOperation) {
      wx.showToast({
        title: '请不要频繁操作',
        icon: 'none',
        duration: 1200
      })
      return false;
    }
    this.setData({
      inOperation: true
    })
    util.get(api.ChangeUserUrl, { id: _id}).then(response => {
      let _data = response.data.data;
      console.log(_data)
      wx.hideLoading();
      wx.setStorageSync('userInfo', _data.user);
      wx.setStorageSync('token', _data.token);
      // wx.navigateBack({
      //   delta: 1
      // })
    })
    setTimeout(() => {
      this.setData({
        inOperation: false
      })
    }, 3000);
  },


  getUserListUrl: function () {
    util.get(api.GetUserListUrl).then(response => {
      let _data = response.data.data;
      console.log(_data)
      this.setData({
        currentId: _data.user.id,
        currentUser: _data.user,
        list: _data.list,
        isHidden: false
      })

    })
  },

  // 提交会员认证
  postMembership: function() {
    if(!this.data.list || this.data.list.length == 0) {
      wx.showToast({
        title: '请先填写名片',
        icon: 'none',
      })
      return false;
    }
    const carte = this.data.currentUser.carte;
    util.post(api.MembershipPostUrl, {aid: this.pageData.aid, carte_id: carte.id}).then(res => {
      wx.showToast({
        title: '提交成功待审核',
      });
      setTimeout(() => wx.navigateBack(), 1500);
    })
  }
})