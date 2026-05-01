// pages/activity/staff/index.js
const api = require('../../../config/api.js');
const util = require('../../../utils/util.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    footerList: [{ icon: 'https://szdbi.oss-cn-shenzhen.aliyuncs.com/mingpian/%E4%BA%8C%E7%BB%B4%E7%A0%81.png', text: '我的名片二维码', url: '/pages/my/cardCode/index' }, { icon: 'https://szdbi.oss-cn-shenzhen.aliyuncs.com/mingpian/%E6%89%AB%E7%A0%81%20(1).png', text: '扫码添加名片', url: '/pages/card/camera/index' }],
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let that = this;
    let _id = options.id;
    that.setData({
      id: _id
    });
    that.getList(_id);
  },

  getList: function (_id) {
    let that = this;
    util.post(api.ActivityApplyList, { id: _id})
        .then(response => {
          let _data = response.data.data;
          console.log(_data)
          let _apply_num = 0;
          let _list = {};
          let _type = 1;
          if (_data.detail && _data.detail.apply && _data.detail.apply.length) {
            _apply_num = _data.detail.apply.length;
            _list = _data.detail.apply;
            _type = _data.detail.type;
          }
          let _self_uid = 0;
          if (_data.user && _data.user.id) {
            _self_uid = _data.user.id;
          }
          that.setData({
            type: _type,
            self_uid: _self_uid,
            list: _list,
            apply_num: _apply_num
          });
          that.getReserveList(_id);
        });
  },

  getReserveList: function (_id) {
    util.post(api.ReserveListUrl, { aid: _id })
      .then(response => {
        let _data = response.data.data;
        let _applyList = this.data.list;
        if (!_applyList || _applyList.length === 0) {
          return false;
        }
        for (let index in _applyList) {
          let item = _applyList[index].carte;
          let _status = true;
          if (_data.indexOf(item.id) < 0) {
            _status = false;
          }
          _applyList[index].selected = _status;
        }
        this.setData({
          list: _applyList
        });
      });
  },

  reserveStore: function (e) {
    let _cid = e.currentTarget.dataset.id;
    let _aid = this.data.id;
    util.post(api.ReserveStoreUrl, { aid: _aid, cid: _cid })
      .then(response => {
        let _data = response.data.data;
        this.getReserveList(_aid);
      });
  },

  navigateToUrl: function (e) {
    let that = this;
    let _url = e.currentTarget.dataset.url;
    if (_url) {
      wx.navigateTo({
        url: _url
      })
    }
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