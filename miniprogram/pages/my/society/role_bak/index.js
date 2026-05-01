// pages/my/society/role/index.js
const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    showModal: false,
    intValue: ''
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    
  },

  addRole: function () {
    this.setData({
      showModal: true
    })
  },

  inputValue: function (e) {
    this.setData({
      intValue: e.detail.value
    })
  },

  moveUp: function (e) {
    let id = e.currentTarget.dataset.id;
    let to_id = e.currentTarget.dataset.to_id;
    if (!id || !to_id) {
      wx.showToast({ title: '数据错误，移动失败', icon: 'none', duration: 1500 });
      return false;
    }
    let _post = {};
    _post.id = id;
    _post.to_id = to_id;
    _post.type = 1;
    util.post(api.RoleAdjustSortUrl, _post).then(res => {
      console.log(res.data.data);
      wx.showToast({ title: '操作成功', duration: 1500 });
      setTimeout(() => {
        this.getRoleList();
      }, 500);
    });

  },

  topUp: function(e) {
    let id = e.currentTarget.dataset.id;
    if (!id) {
      wx.showToast({ title: '数据错误，置顶失败', icon: 'none', duration: 1500 });
      return false;
    }
    let _post = {};
    _post.id = id;
    _post.type = 2;
    util.post(api.RoleAdjustSortUrl, _post).then(res => {
      console.log(res.data.data);
      wx.showToast({ title: '操作成功', duration: 1500 });
      setTimeout(() => {
        this.getRoleList();
      }, 500);
    });
  },

  confirm: function () {
    let _intValue = this.data.intValue;
    if (!_intValue) {
      wx.showToast({title: '请输入协会角色名称',icon: 'none',duration: 1500});
      return false;
    }
    util.post(api.RoleStoreUrl, { name: _intValue }).then(res => {
      console.log(res.data.data);
      wx.showToast({ title: '添加成功', duration: 1500 });
      this.setData({
        showModal: false,
        intValue: ''
      });
      setTimeout(() =>{
        this.getRoleList();
      }, 1000);
    });
  },

  cancel: function () {
    this.setData({
      showModal: false
    })
  },


  getRoleList: function () {
    util.get(api.RoleListUrl, {}, false).then(res => {
      console.log(res.data.data);
      this.setData({
        list: res.data.data
      });
    });
  },

  navigateToUrl: function (e) {
    wx.navigateTo({
      url: e.currentTarget.dataset.url
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
    this.getRoleList();
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

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  }
})