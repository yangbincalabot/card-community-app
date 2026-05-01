// pages/supply/detail/index.js
const api = require('../../../config/api.js');
const util = require('../../../utils/util.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    detail:{},
    likeStatus: false,
    collectionStatus: false,
    mainHidden: true,
    current: 0
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
    that.getLikeStatus(_id);
  },

  getDetail: function (_id) {
    let that = this;
    util.post(api.SupplyBigDetail, {id: _id})
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
          });
          that.getCollectionStatus(_id);
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

  getLikeStatus: function (_id) {
    let that = this;
    util.post(api.LikeStatusUrl, {type:1, info_id: _id})
        .then(response => {
          let _data = response.data.data;
          if (_data && _data.status === 1) {
            that.setData({
              likeStatus: true
            })
          }

        });
  },

  changeLike: function () {
    let that = this;
    let _info_id = that.data.id;
    let _likes = that.data.detail.likes;
    let _title = '点赞成功';
    if (that.data.likeStatus) {
      _title = '已取消点赞';
      _likes--;
    } else {
      _likes++;
    }
    console.log(_info_id);
    util.post(api.LikeUrl, {type: 1, info_id: _info_id})
        .then(response => {
          wx.showToast({title: _title, icon: 'none', duration: 800});
          that.setData({
            likeStatus: !that.data.likeStatus,
            'detail.likes': _likes
          })
        });
  },

  playPhone: function (e) {
    let that = this;
    let _phone = e.currentTarget.dataset.phone;
    if (!_phone) {
      wx.showToast({title: '改用户未设置设置电话', icon: 'none', duration: 800});
      return false;
    }
    wx.makePhoneCall({
      phoneNumber: _phone
    })
  },

  // 收藏
  clickCollection:function(){
    let that = this;
    let _info_id = that.data.id;
    let _title = '收藏成功';
    if (that.data.collectionStatus) {
      _title = '已取消收藏';
    }
    util.post(api.CollectionUrl, {type: 2, info_id: _info_id})
        .then(response => {
          wx.showToast({title: _title, icon: 'none', duration: 800});
          that.setData({
            collectionStatus: !that.data.collectionStatus
          })
        });
  },

  getCollectionStatus: function (_id) {
    let that = this;
    util.post(api.CollectionGetStatusUrl, {type: 2, info_id: _id})
        .then(response => {
          let _data = response.data.data;
          if (_data && _data.status === 1) {
            that.setData({
              collectionStatus: true
            })
          }
        });
  },

  previewImages(e) {
    var _this = this
    let index = e.currentTarget.dataset.index
    let list = _this.data.current == 0 ? _this.data.detail.images : '';
    let current = ''
    let urls = []
    if (!list) {
      return false;
    }
    list.forEach(function (value, ind, arr) {
      urls.push(value)
      if (ind == index) {
        current = value
      }

    })
    wx.previewImage({
      current: current,
      urls: urls
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