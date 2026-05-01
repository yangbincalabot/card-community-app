// pages/my/activity/group/index.js
const api = require('../../../../config/api.js');
const util = require('../../../../utils/util.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    list: {},
    bool: false
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let that = this;
    that.getGroup();
  },

  getGroup: function () {
    let that = this;
    let storage_group = wx.getStorageSync('activity_group');
    wx.removeStorageSync('activity_group');
    util.post(api.ActivityGetGroup, {})
      .then(response => {
        let _data = response.data.data;
        new Promise(function (resolve) {
          resolve();
        }).then(() => {
          for (let index in _data) {
            let item = _data[index];
            if (storage_group[item.id]) {
              item.value = storage_group[item.id];
              item.checked = true;
            }
          }
          }).then(() => {
            that.setData({
              list: _data
            })
          })
      });
  },

  changeInput: function (e) {
    let that = this;
    let _list = that.data.list;
    let _value = e.detail.value;
    let _key = e.currentTarget.dataset.key;
    _list[_key].value = _value;
    that.setData({
      'list': _list
    })
  },

  checkboxChange: function (e) {
    let that = this;
    let _key = e.currentTarget.dataset.key;
    let _list = that.data.list;
    let _checked = true;
    if (_list[_key].checked) {
      _checked = false;
    }
    _list[_key].checked = _checked;
    that.setData({
      'list': _list
    })
  },

  formSubmit(e) {
    let that = this;
    let _data = e.detail.value;
    let _list = that.data.list;
    let param = {};
    for (let index in _list) {
      let item = _list[index];
      if (!item.checked) {
        continue;
      }
      let current_id = 'group_' + item.id;
      let current_value = _data[current_id];
      if (!current_value) {
        wx.showToast({ title: '请输入名额限制', icon: 'none', duration: 800 })
        return false;
      }
      param[item.id] = current_value;
    }
    setTimeout(function(){
      wx.setStorageSync('activity_group', param);
      wx.navigateBack({
        delta: 1
      })
    },100)
    
  },

  changeChecked: function () {
    
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


})