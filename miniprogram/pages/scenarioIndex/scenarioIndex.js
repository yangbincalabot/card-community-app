// pages/scenarioIndex/scenarioIndex.js
const api = require('../../config/api.js');
const util = require('../../utils/util.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    isHidden: true,
    inOperation: false,
    list: []
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.getUserListUrl();
  },

  setHomeUrl() {
    let _url = "/pages/scenarioIndex/scenarioIndex";
    if (!wx.getStorageSync('HOMEURL') || wx.getStorageSync('HOMEURL') != _url) {
      wx.setStorageSync('HOMEURL', _url);
    }
  },

  navigateToUrl: function (event) {
    let url = event.currentTarget.dataset.url;
    if (url && url !== '#') {
      wx.navigateTo({
        url: url
      });
    }
  },

  // 页面跳转
  redirectToUrl: function (event) {
    let url = event.currentTarget.dataset.url;
    if (url && url !== '#') {
      wx.redirectTo({
        url: url
      });
    }
  },

  goChangeCard: function (event) {
    let _id = event.currentTarget.dataset.id;
    let _cid = event.currentTarget.dataset.cid;
    let _url = "/pages/card/myCard/index?id=" + _cid;
    if (this.data.currentUser && this.data.currentUser.id == _id) {
      wx.navigateTo({
        url: _url
      });
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
    util.get(api.ChangeUserUrl, { id: _id }).then(response => {
      let _data = response.data.data;
      console.log(_data)
      wx.hideLoading();
      this.setData({
        inOperation: false
      })
      wx.setStorageSync('userInfo', _data.user);
      wx.setStorageSync('token', _data.token);
      wx.navigateTo({
        url: _url
      });
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
      if (_data.list && _data.list.length > 0) {
        this.setHomeUrl();
      } else {
        wx.showToast({
          title: '请先创建名片',
          icon: 'none',
          duration: 2000
        })
        setTimeout(() => {
          wx.navigateTo({
            url: '/pages/my/card/editCard/index',
          })
        }, 2000);
        return false;
      }
      this.setData({
        currentId: _data.user.id,
        currentUser: _data.user,
        list: _data.list,
        // isHidden: false
      })
      
    })
    setTimeout(() =>{
      this.setData({
        isHidden: false
      })
    }, 200);

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
    if (!this.data.isHidden && this.data.list.length == 0) {
      this.getUserListUrl();
    }
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